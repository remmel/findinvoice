<?php
/**
 * User: remmel
 * Date: 22/10/18
 * Time: 20:31
 */

namespace App\Repository;


use App\Entity\Transaction;
use App\Legacy\Utils;

class TransactionRepository {
    private $db = '/home/remmel/remy.mellet@gmail.com/Comparabus/administrative/accounting/achat/db/transactions.csv';

    /**
     * @return Transaction[]
     */
    public function findAll() {
        $transactions = Utils::file_get_contents_csv_header($this->db);

        $oTransactions =  [];
        foreach ($transactions as $t) {
            $oT = new Transaction();
            $oT->id = $t->id;
            $oT->amount = $t->amount;
            $oT->description = $t->description;
            $oT->date = $t->date;
            $oTransactions[] = $oT;
        }
        return $oTransactions;
    }

    public function merge($transactions, $newTransactions) {
        $end = end($transactions);
        $i = $end->id;
        foreach ($newTransactions as $nt) {
            if($end->date < $nt->date) {
                $i++;
                $nt->id = $i;
                $transactions[] = $nt;
            }
        }
        Utils::file_put_contents_csv_header($transactions, $this->db);
    }
}