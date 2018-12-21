<?php

namespace App\Controller;

use App\Legacy\DbBankin;
use App\Legacy\FileAdapterFilesystem;
use App\Legacy\Main;
use App\Legacy\Utils;
use App\Repository\TransactionRepository;
use App\Service\Bank\CsvBankConnector;
use App\Service\Bank\WeboobBankConnector;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class HomepageController extends AbstractController {
    private $bank;
    private $fileAdapter;
    private $main;
    private $tRepo;

    public function __construct() {
        $this->bank = new DbBankin();
        $this->fileAdapter = new FileAdapterFilesystem();

        $this->tRepo = new TransactionRepository();

//        $session = new Session();
//        if(!$session->isStarted()) $session->start();
//        $accesstoken = $session->get('access_token');
//        $this->fileAdapter = new FileAdapterGoogleDrive($accesstoken);

        $this->main = new Main($this->fileAdapter);
    }

    /**
     * @Route("/", name="bank", methods={"GET"})
     */
    public function index(Request $r) {
        $month = $this->getMonth($r->query->get('month'));
        $q = $r->query->get('q');
//        $transactionsDb = $this->tRepo->findAll();

        list($transactions, $orphanFiles) = $this->main->reconciliation($this->bank, $month, $q);
        return $this->render('homepage/index.html.twig', [
            'months' => Main::listMonths(),
            'month' => $month,
            'transactions' => array_reverse($transactions)
        ]);
    }


    private function findInvoices(array $transaction) {


    }

    /**
     * Process the month query. If don't exists use the current month
     * @param $queryMonth iso date eg : 2018-12
     * @return DateTime
     */
    public function getMonth($queryMonth) {
        $currentMonth = null; //(new DateTime())->modify('first day of this month');
        return isset($queryMonth) ? new DateTime($queryMonth) : $currentMonth;
    }

    /**
     * @Route("/", name="bank_post", methods={"POST"})
     */
    public function upload(Request $r) {
        $month = $this->getMonth($r->query->get('month'));
        $action = $r->request->get('action');
        if ($action === 'upload') {
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
        $path = $_ENV['FILEADAPTER_FS_FOLDER'] . $id;

        //for security reason : to avoid being able to see all file of system
        if (Utils::contains($id, '..')) die('cannot contain ".." char');
        if (!file_exists($path)) die("file $path doesnt exist");

        $contentType = mime_content_type($path);

        header('Content-Type: ' . $contentType);
        echo file_get_contents($path);
    }

    /**
     * @Route("/invoices", name="invoices")
     */
    public function invoices(Request $r) {
        $month = $this->getMonth($r->query->get('month'));
//        $this->fileAdapter->authenticateIfNeeded();
        $files = $this->fileAdapter->files($month);

        return $this->render('homepage/invoices.html.twig', [
            'months' => Main::listMonths(),
            'month' => $month,
            'files' => $files
        ]);
    }

    /**
     * @Route("/sync/bank/manual", name="sync_bank", methods={"GET"})
     */
    public function sync() {
        return $this->render('homepage/syncbank_import.html.twig');
    }

    /**
     * @Route("/sync/bank/manual", name="sync_bank_post", methods={"POST"})
     */
    public function syncPost() {
        if (isset($_FILES['file'])) {
            $tmp_name = $_FILES['file']['tmp_name'];
            $path_info = pathinfo($_FILES['file']['name']);
            $ext = $path_info['extension'];

            if ($ext == 'CSV') {
                $b = new CsvBankConnector('creditagricole', $tmp_name);
                $transactionsImported = $b->transactions();

                $transRepo = new TransactionRepository();
                $transactionsDb = $transRepo->findAll();

                $transRepo->merge($transactionsDb, $transactionsImported);

                return $this->redirectToRoute('sync_bank');
            }
        }
    }

    /**
     * @Route("/sync/bank/auto/weboob", name="sync_bank_auto")
     */
    public function syncAuto(Request $r) {

        $banks = WeboobBankConnector::getBanks();
        $transactionsImported = [];

        if ($r->isMethod('post')) {
            $login = $r->request->get('login');
            $passord = $r->request->get('password');
            $bank = $r->request->get('bank');

            $weboobConnector = new WeboobBankConnector($bank, $login,$passord);

            $transactionsImported = $weboobConnector->transactions(null);

            $transRepo = new TransactionRepository();
            $transactionsDb = $transRepo->findAll();

            $transRepo->merge($transactionsDb, $transactionsImported);
        }

        return $this->render('homepage/syncbank_weboob_config.html.twig', [
            'banks' => $banks,
            'transactions' => $transactionsImported
        ]);
    }
}
