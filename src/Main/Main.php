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
            $file = pathinfo($file, PATHINFO_FILENAME);
            $parts = explode('_', $file);
            $key = $parts[0] . '_' . $parts[2];
            $assocFiles[$key] = $file;
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

            $key = $t->date . '_' . abs($t->amount);

            if (isset($assocFiles[$key])) {
                $filename = $assocFiles[$key];
                $t->doc = $filename;
                $t->doclink = 'file:' . $filesFolder . $assocFiles[$key];
            }

            $transactions[] = $t;
        }
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
            $commentPart = isset($_POST['comment']) ? '_' . $_POST['comment'] : '';
            $destination = $dir . $_POST['fn'] . $commentPart . '.' . $path_info['extension'];


            if (!is_writable($dir))
                throw new \Exception('not writable: ' . $dir);

            if (move_uploaded_file($receipt['tmp_name'], $destination)) {
            } else {
                die('error uploading the file' . var_dump($_FILES + $_POST));
            }
        }
    }

    public static function filename($t, $info = '') {
        $desc = $t->raw_description;
        $desc = str_replace(['Virement Web ', 'Virement ', 'Paiement Par Carte ', 'Prelevmnt '], ['', '', '', ''], $desc);
        $words = explode(' ', $desc);
        $desc = $words[0] . '-' . $words[1];
        return $t->date . '_' . $desc . '_' . abs($t->amount) . ($info ? ('_' . $info) : '');
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
}