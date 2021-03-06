<?php
/**
 * User: remmel
 * Date: 02/05/18
 * Time: 10:36
 */

namespace App\Legacy;


use DateInterval;
use DatePeriod;
use DateTime;

class Main {
    const HELP = [
        'Amazon Payments' => 'https://www.amazon.fr/gp/css/order-history/ref=nav_youraccount_orders',
        'Ovh' => 'https://www.ovh.com/manager/dedicated/index.html#/billing/history',
//        'Ovh' => FetchOvhSoap::class,
        'Google *svcsapps' => 'https://mail.google.com/mail/u/1/#search/from%3Apayments-noreply%40google.com',
        'Google*cloud' => 'https://console.cloud.google.com/billing/',
        'Google *ads' => 'https://ads.google.com/aw/billing/summary',
        'Facebk *' => 'https://business.facebook.com/ads/manager/billing/transactions/',
//        'facebook ad perso'	=> 'https://www.facebook.com/ads/manager/billing/transactions/',
        'Microsoft *bing Ads Msbill' =>	'https://azure.bingads.microsoft.com/cc/Billing/History',
        'Mgp*5euros' =>	'https://5euros.com/achats/factures',
        'Free Mobile' => 'https://mail.google.com/mail/u/1/#advanced-search/from=freemobile%40free-mobile.fr&subject=Facture',
        'Adobe Stock' => 'https://accounts.adobe.com/plans/',
        'Bap Link' => 'https://poissonniers.espace.link/gestion/factures/recurrentes',
        'Lespace - Poissonnie' => 'https://poissonniers.espace.link/gestion/depot-de-garantie',
        'Fiverr' => 'https://mail.google.com/mail/u/1/#search/from%3A(invoices%40fiverr.com)',
        'Scaleway' => 'https://cloud.scaleway.com/#/billing',
        'Cdiscount' => 'https://clients.cdiscount.com/Order/OrdersTracking.html',
        'Google *gsuite' => 'https://admin.google.com/comparabus.com/AdminHome', //payments-noreply@google.com
        'LE COMPOSE PRO' => 'https://www.credit-agricole.fr/ca-paris/professionnel/operations/documents/edocuments.html',
        'AIRASIA' => 'https://taxinvoice.airasia.com/'
    ];

    const FIRST_MONTH = '2017-07';

    /** @var IFileAdapter */
    protected $fileAdapter;

    public function __construct(IFileAdapter $fa) {
        $this->fileAdapter = $fa;
    }

    public function filterPositiveTransactions(IBank $bank, \DateTime $month) {
        $transactions = $bank->transactions($month);
        foreach ($transactions as $k => $t) {
            if($t->amount < 0)
                unset($transactions[$k]);
        }
        return $transactions;
    }

    /**
     * Matchs bank row with receipt
     * Currenlty the key to link bank row with receipt is the DATE_AMOUNT.
     * TODO Handle if same amount twice the same day.
     */
    public function reconciliation(IBank $bank, ?\DateTime $month, ?string $q) {
        $files = $this->fileAdapter->files($month);
        $assocFiles = [];
        foreach ($files as $f) {
            if(Utils::startsWith($f->name, '.')) continue; //ignore file beginning with dot
            $nameNoExt = pathinfo($f->name, PATHINFO_FILENAME);
            $parts = explode('_', $nameNoExt);
            if(count($parts) < 3) continue;
            $key = $parts[0] . '_' . $parts[2];
            if(!isset($assocFiles[$key])) $assocFiles[$key] = [];
            $assocFiles[$key][] = $f;
        }

        $transactions = $bank->transactions($month);
        //add info for each transaction
        foreach ($transactions as $id => $t) {
            if($q && strpos(strtolower($t->description), strtolower($q)) === false) unset($transactions[$id]);
            $t->upload = self::filename($t);
            $key = $t->date . '_' . number_format(abs($t->amount), 2,'.', '');

            if (isset($assocFiles[$key]) && count($assocFiles[$key]) > 0) {
                $f = array_shift($assocFiles[$key]);
                $t->file = $f;
            } else {
                //if not document uploaded, display some help to find that doc
                $t->helplink = self::findHelp($t->description);
            }
        }

        //files not linked to any transaction. Happend if added manually into folder
        $orphanFiles = [];
        foreach ($assocFiles as $fileByKey) {
            foreach ($fileByKey as $f) {
                $orphanFiles[] = $f;
            }
        }

        return [$transactions, $orphanFiles];
    }

    /**
     * If a file is uploaded, add it to the folder
     */
    public function handleUpload(\DateTime $month) {
        if(!empty($_POST['receipt_tmppath'])) {
            $receiptTmpPath = $_POST['receipt_tmppath'];
            $path_info = pathinfo($receiptTmpPath);
            $ext = $path_info['extension'];
        } else if (isset($_FILES['receipt'])) {
            if($_FILES['receipt']['error']) {
                die('Error uploading file : '.$this->errorMessage($_FILES['receipt']['error']));
            }
            $receiptTmpPath = $_FILES['receipt']['tmp_name'];
            $path_info = pathinfo($_FILES['receipt']['name']);
            $ext = $path_info['extension'];
        }

        if ($receiptTmpPath) {
            $commentPart = isset($_POST['comment']) ? '_' . Utils::cleanNameToFilename($_POST['comment']) : '';
            $fn = $_POST['fn'];
            $newName = $fn . $commentPart . '.' . $ext;
        	$this->fileAdapter->upload($month, $receiptTmpPath, $newName);
        }
    }

    private function errorMessage(int $code) {
        switch ($code) {
            case UPLOAD_ERR_INI_SIZE:
                $message = "The uploaded file exceeds the upload_max_filesize directive in php.ini";
                break;
            case UPLOAD_ERR_FORM_SIZE:
                $message = "The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form";
                break;
            case UPLOAD_ERR_PARTIAL:
                $message = "The uploaded file was only partially uploaded";
                break;
            case UPLOAD_ERR_NO_FILE:
                $message = "No file was uploaded";
                break;
            case UPLOAD_ERR_NO_TMP_DIR:
                $message = "Missing a temporary folder";
                break;
            case UPLOAD_ERR_CANT_WRITE:
                $message = "Failed to write file to disk";
                break;
            case UPLOAD_ERR_EXTENSION:
                $message = "File upload stopped by extension";
                break;

            default:
                $message = "Unknown upload error";
                break;
        }
        return $message;
    }

    public static function filename(Transaction $t, $info = '') {
        $desc = $t->description;
        $desc = str_replace(['Virement Web ', 'Virement ', 'Paiement Par Carte ', 'Prelevmnt '], ['', '', '', ''], $desc);
        $words = explode(' ', $desc);
        $desc = isset($words[1]) ? Utils::cleanNameToFilename($words[0] . '-' . $words[1]) : $desc;
        return $t->date . '_' . $desc . '_' . number_format(abs($t->amount), 2,'.', '') . ($info && strlen($info)>0 ? ('_' . $info) : '');
    }

    /**
     * Returns the list of month as ISO string (YYYY-MM)
     * @return string[]
     */
    public static function listMonths() {
        $start = (new DateTime(self::FIRST_MONTH . '-01'))->modify('first day of this month');
        $currentMonth = (new DateTime())->modify('first day of this month');
        $interval = DateInterval::createFromDateString('1 month');
        $months = new DatePeriod($start, $interval, $currentMonth);

        $labels = [];
        foreach ($months as $m) {
            $labels[] = $m->format('Y-m');
        }
        return array_reverse($labels);
    }

    public static function findHelp($description) {
        foreach (self::HELP as $words => $link) {
            if(Utils::contains(strtolower($description), strtolower($words))) {
                return $link;
            }
        }

        return null;
    }
}