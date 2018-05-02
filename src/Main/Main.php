<?php
/**
 * User: remmel
 * Date: 02/05/18
 * Time: 10:36
 */

namespace Main;


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
    public function reconciliation(Bankin $bankin, $accountId, \DateTime $month) {
        $btransactions = $bankin->transactions($accountId, $month);
        $files = self::files($month);
        $filesFolder = self::getFolder($month);
        $assocFiles = [];
        foreach ($files as $file) {
            $parts = explode('_', $file);
            $key = $parts[0].'_'.$parts[2];

            $assocFiles[$key] = $file;

            // TODO handle when multiple invoice with same amount and same day
//            if(!isset($assocFiles[$key])) $assocFiles[$key] = [];
//            $assocFiles[$key][] = $file;
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

            $key = $t->date.'_'.abs($t->amount);

            if(isset($assocFiles[$key])) {
                $filename = $assocFiles[$key];
                $t->doc = $filename;
                $t->doclink = 'file:'.$filesFolder.$assocFiles[$key];
//                foreach ($assocFiles[$key])
            }

            $transactions[] = $t;
        }
        return $transactions;
    }

    /**
     * If a file is uploaded, add it to the folder
     */
    public function handleUpload(\DateTime $month){
        $dir = self::getFolder($month);

        if(isset($_FILES['receipt'])) {
            $receipt = $_FILES['receipt'];
            $fichero_subido = $dir . basename($_FILES['fichero_usuario']['name']);

            $destination = $fichero_subido.$_POST['fn']; //TODO add extension
            if(!is_writable($dir))
                throw new \Exception('not writable: '.$dir);
            if (move_uploaded_file($receipt['tmp_name'], $destination)) {
            } else {
                die('error uploading the file'.var_dump($_FILES+$_POST));
            }
        }
    }

    public static function filename($t, $info = '') {
        $desc = $t->raw_description;
        $desc = str_replace(['Virement Web ', 'Virement ','Paiement Par Carte ', 'Prelevmnt '], ['','','', ''], $desc);
        $words = explode(' ', $desc);
        $desc = $words[0].'-'.$words[1];
        return $t->date.'_'.$desc.'_'.abs($t->amount).'_'.$info;
    }

    /**
     * Give the folder where the receipt is.
     * TODO handle multiple structure strategy (everything in same folder, different month folder name)
     */
    public static function getFolder(\DateTime $date){
        $d = $date->format('Y-m'); //201804
        return DOCUMENTS_FOLDER."/$d/";
    }

    /**
     * List files
     */
    public static function files(\DateTime $date) {
        $folder = self::getFolder($date);
        return scandir($folder);
    }
}