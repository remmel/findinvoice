<?php
/**
 * User: remmel
 * Date: 30/09/18
 * Time: 12:49
 */

namespace App\Legacy;


use App\Repository\TransactionRepository;

/**
 * Using internal DB
 */
class DbBankin implements IBank {

    /**
     * Returns the list of transaction for a specific month
     * @param \DateTime $month
     * @return Transaction[]
     */
    public function transactions(?\DateTime $month) {
        $transactionRepo = new TransactionRepository();

        $transactionsDb = $transactionRepo->findAll();

        $transactionsView = [];
        foreach ($transactionsDb as $tDb) {
            if($month && substr($tDb->date, 0, 7) != $month->format('Y-m')) continue;
            $tView = new Transaction();
            $tView->id = $tDb->id;
            $tView->date = $tDb->date;
            $tView->description = $tDb->description;
            $tView->amount = $tDb->amount;
            $transactionsView[] = $tView;
        }
        return $transactionsView;
    }
}