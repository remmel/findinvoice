<?php
/**
 * User: remmel
 * Date: 02/05/18
 * Time: 10:36
 */

namespace Main;


use DateInterval;
use DatePeriod;
use DateTime;

class Main {
    const HELP = [
        'Amazon Payments' => 'https://www.amazon.fr/gp/css/order-history/ref=nav_youraccount_orders',
        'Ovh' => 'https://www.ovh.com/manager/dedicated/index.html#/billing/history',
        'Google *svcsapps' => 'https://mail.google.com/mail/u/1/#search/from%3Apayments-noreply%40google.com',
        'Google*cloud' => 'https://console.cloud.google.com/billing/',
        'Google *adws210617042' => 'https://adwords.google.fr/um/identity?dst=/um/Billing/Home#th',
        'Facebk *','https://business.facebook.com/ads/manager/billing/transactions/',
//        'facebook ad perso'	=> 'https://www.facebook.com/ads/manager/billing/transactions/',
        'Microsoft *bing Ads Msbill' =>	'https://azure.bingads.microsoft.com/cc/Billing/History',
        'Mgp*5euros' =>	'https://5euros.com/achats/factures',
        'Free Mobile' => 'https://mail.google.com/mail/u/1/#advanced-search/from=freemobile%40free-mobile.fr&subject=Facture',
        'Adobe Stock' => 'https://accounts.adobe.com/plans/',
        'Bap Link' => 'https://poissonniers.espace.link/gestion/factures/recurrentes',
        'Lespace - Poissonnie' => 'https://poissonniers.espace.link/gestion/depot-de-garantie',
        'Fiverr' => 'https://mail.google.com/mail/u/1/#search/from%3A(invoices%40fiverr.com)',
        'Scaleway' => 'https://cloud.scaleway.com/#/billing'
    ];

//    /** @var Bankin */
//    protected $bankin;
//    public function __construct() {
//        $this->bankin = new Bankin();
//    }

    /**
     * Matchs bank row with receipt
     * Currenlty the key to link bank row with receipt is the DATE_AMOUNT.
     * TODO Handle if same amount twice the same day.
     */
    public function reconciliation(Bankin $bankin, \DateTime $month) {
        $btransactions = $bankin->transactions($_SESSION['email'], $_SESSION['password'], $month);
        $files = self::files($month);
        $filesFolder = self::getFolder($month);
        $assocFiles = [];
        foreach ($files as $file) {
            $fileWithoutExt = pathinfo($file, PATHINFO_FILENAME);
            $parts = explode('_', $fileWithoutExt);
            $key = $parts[0] . '_' . $parts[2];
            if(!isset($assocFiles[$key])) $assocFiles[$key] = [];
            $assocFiles[$key][] = $file;
        }

        /** @var Transaction[] $transactions */
        $transactions = [];
        foreach ($btransactions as $bt) {
            $t = new Transaction();
            $t->id = $bt->id;
            $t->date = $bt->date;
            $t->description = $bt->raw_description;
            $t->amount = $bt->amount;
            $t->currency = $bt->currency_code;
            $t->upload = self::filename($bt);

            $key = $t->date . '_' . number_format(abs($t->amount), 2,'.', '');

            if (isset($assocFiles[$key]) && count($assocFiles[$key]) > 0) {
                $filename = array_shift($assocFiles[$key]);
                $t->doc = $filename;
                $t->doclink = 'file:' . $filesFolder . $assocFiles[$key];
            } else {
                //if not document uploaded, display some help to find that doc
                $t->helplink = self::findHelp($bt->raw_description);
            }

            $transactions[] = $t;
        }

        print_r($assocFiles);

        return $transactions;
    }

    /**
     * If a file is uploaded, add it to the folder
     */
    public function handleUpload(\DateTime $month) {
        $dir = self::getFolder($month);

        if (isset($_FILES['receipt'])) {
            $receipt = $_FILES['receipt'];

            $path_info = pathinfo($receipt['name']);
            $commentPart = isset($_POST['comment']) ? '_' . Utils::cleanNameToFilename($_POST['comment']) : '';
            $fn = $_POST['fn'];
            $destination = $dir . $fn . $commentPart . '.' . $path_info['extension'];

            if (!is_writable($dir))
                throw new \Exception('not writable: ' . $dir);

            if (move_uploaded_file($receipt['tmp_name'], $destination)) {
            } else {
                var_dump($_FILES + $_POST);
                die('error uploading the file: '.$receipt['tmp_name'].' -> '.$destination);
            }
        }
    }

    public function removeFile(\DateTime $month, $filename){
        $dir = self::getFolder($month);
        unlink($dir.$filename);
    }

    public static function filename($t, $info = '') {
        $desc = $t->raw_description;
        $desc = str_replace(['Virement Web ', 'Virement ', 'Paiement Par Carte ', 'Prelevmnt '], ['', '', '', ''], $desc);
        $words = explode(' ', $desc);
        $desc = Utils::cleanNameToFilename($words[0] . '-' . $words[1]);
        return $t->date . '_' . $desc . '_' . number_format(abs($t->amount), 2,'.', '') . ($info && strlen($info)>0 ? ('_' . $info) : '');
    }

    /**
     * Give the folder where the receipt is.
     * TODO handle multiple structure strategy (everything in same folder, different month folder name)
     */
    public static function getFolder(\DateTime $date) {
        $d = $date->format('Y-m'); //201804
        return DOCUMENTS_FOLDER . "/$d/";
    }

    /**
     * List files
     */
    public static function files(\DateTime $date) {
        $folder = self::getFolder($date);
        return scandir($folder);
    }

    /**
     * Process the month query. If don't exists use the current month
     * @param $queryMonth iso date eg : 2018-12
     * @return DateTime
     */
    public function selectedMonth(&$queryMonth) {
        $currentMonth = (new DateTime())->modify('first day of this month');
        return isset($queryMonth) ? new DateTime($queryMonth) : $currentMonth;
    }

    /**
     * Returns the list of month as ISO string (YYYY-MM)
     * @return string[]
     */
    public function listMonths() {
        $start = (new DateTime(FIRST_MONTH . '-01'))->modify('first day of this month');
        $currentMonth = (new DateTime())->modify('first day of this month');
        $interval = DateInterval::createFromDateString('1 month');
        $months = new DatePeriod($start, $interval, $currentMonth);

        $labels = [];
        foreach ($months as $m) {
            $labels[] = $m->format('Y-m');
        }
        return $labels;
    }

    public function findHelp($description) {
        foreach (self::HELP as $words => $link) {
            if(Utils::contains($description, $words)) {
                return $link;
            }
        }
        return null;
    }
}