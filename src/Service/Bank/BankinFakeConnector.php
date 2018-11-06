<?php
/**
 * User: remmel
 * Date: 30/09/18
 * Time: 12:49
 */

namespace App\Service\Bank;


use App\Legacy\Utils;
use App\Legacy\IBank;

class BankinFakeConnector implements IBank {

    private $file;

    public function __construct($file) {
        $this->file = $file;
    }

    /**
     * Returns the list of transaction for a specific month
     * @param \DateTime $month
     * @return Transaction[]
     */
    public function transactions(\DateTime $month) {
        //__DIR__ . '/tmp_exportbank/tmp_bankin.csv'
        $rows = Utils::file_get_contents_csv_header($this->file);

        $oTransactions = [];
        foreach ($rows as $row) {
            if(substr($row->date, 0, 7) != $month->format('Y-m')) continue;
            $t = new Transaction();
            $t->id = $row->id;
            $t->date = $row->date;
            $t->description = $row->raw_description;
            $t->amount = $row->amount;
            $t->currency = $row->currency_code;
            $oTransactions[] = $t;
        }
        return $oTransactions;
    }
}