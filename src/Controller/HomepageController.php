<?php

namespace App\Controller;

use App\Entity\Transaction;
use App\Legacy\DbBankin;
use App\Legacy\FileAdapterFilesystem;
use App\Legacy\FileAdapterGoogleDrive;
use App\Legacy\Main;
use App\Legacy\Utils;
use App\Repository\TransactionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class HomepageController extends AbstractController {
    private $bank;
    private $fileAdapter;
    private $main;

    public function __construct() {
        $this->bank = new DbBankin();
        $this->fileAdapter = new FileAdapterFilesystem();

//        $session = new Session();
//        if(!$session->isStarted()) $session->start();
//        $accesstoken = $session->get('access_token');
//        $this->fileAdapter = new FileAdapterGoogleDrive($accesstoken);

        $this->main = new Main($this->fileAdapter);
    }

    /**
     * @Route("/", name="bank", methods={"GET"})
     */
    public function index() {
        $month = Main::selectedMonth($_GET['month']);
        list($transactions, $orphanFiles) = $this->main->reconciliation($this->bank, $month);
        return $this->render('homepage/index.html.twig', [
            'months' => Main::listMonths(),
            'month' => $month,
            'transactions' => $transactions
        ]);
    }

    /**
     * @Route("/", name="bank_post", methods={"POST"})
     */
    public function upload() {
        $month = Main::selectedMonth($_GET['month']);
        $action = isset($_REQUEST['action'])?$_REQUEST['action']:'';
        if($action === 'upload') {
            $this->main->handleUpload($month);
        } elseif ($action === 'delete') {
            $this->fileAdapter->remove($_POST['id']);
        }
        return $this->redirectToRoute('bank', ['month' => $month->format('Y-m')]);
    }

    /**
     * @Route("/viewlocalfile.php")
     */
    public function view() {
        $id = $_GET['id'];
        $path = $_ENV['FILEADAPTER_FS_FOLDER'].$id;

        //for security reason : to avoid being able to see all file of system
        if(Utils::contains($id, '..')) die('cannot contain ".." char');
        if(!file_exists($path)) die("file $path doesnt exist");

        $contentType = mime_content_type($path);

        header('Content-Type: '.$contentType);
        echo file_get_contents($path);
    }

    /**
     * @Route("/invoices", name="invoices")
     */
    public function invoices() {
        $month = Main::selectedMonth($_GET['month']);
//        $this->fileAdapter->authenticateIfNeeded();
        $files = $this->fileAdapter->files($month);

        return $this->render('homepage/invoices.html.twig', [
            'months' => Main::listMonths(),
            'month' => $month,
            'files' => $files
        ]);
    }

    /**
     * @Route("/sync/bank", name="sync_bank", methods={"GET"})
     */
    public function sync() {
        return $this->render('homepage/syncbank.html.twig');
    }

    /**
     * @Route("/sync/bank", name="sync_bank_post", methods={"POST"})
     */
    public function syncPost() {
        if (isset($_FILES['file'])) {
            $tmp_name = $_FILES['file']['tmp_name'];
            $path_info = pathinfo($_FILES['file']['name']);
            $ext = $path_info['extension'];

            if ($ext == 'CSV') {
                $tmp = tempnam(sys_get_temp_dir(), 'tmp_upload.csv');
                $content = file_get_contents($tmp_name);
                $content = utf8_encode($content);
                file_put_contents($tmp, $content);
                $rows = Utils::file_get_contents_csv($tmp, ';');
                unlink($tmp);

                for ($i = 0; $i < 11; $i++) //skip first 10 lines (credit agricole)
                    $header = array_shift($rows);
                $assoc = Utils::arrayToAssoc($rows, $header);

                $transactionsImported = [];
                foreach ($assoc as $t) {
                    if (!isset($t->Libellé) || !isset($t->{'Crédit Euros'})) continue;
                    $ot = new Transaction();
                    $d = \DateTime::createFromFormat('d/m/Y', $t->Date);
                    if (!$d) {
                        die('Err Convert date :' . $t->Date);
                    }
                    $ot->date = $d->format('Y-m-d');
                    $ot->description = trim($t->Libellé);

                    $credit = (float)str_replace(',', '.', $t->{'Crédit Euros'});
                    $debit = (float)str_replace(',', '.', $t->{'Débit Euros'});

                    $ot->amount = $credit + $debit * -1;
                    $transactionsImported[] = $ot;
                }
                $transactionsImported = array_reverse($transactionsImported);

                $transRepo = new TransactionRepository();
                $transactionsDb = $transRepo->findAll();

                $transRepo->merge($transactionsDb, $transactionsImported);

                return $this->redirectToRoute('sync_bank');
            }
        }
    }
}
