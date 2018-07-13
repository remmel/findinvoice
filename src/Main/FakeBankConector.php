<?php
/**
 * User: remmel
 * Date: 12/07/18
 * Time: 17:40
 */

namespace Main;


class FakeBankConector implements IBank {

    /**
     * Returns the list of transaction for a specific month
     * @param \DateTime $month
     * @return Transaction[]
     */
    public function transactions(\DateTime $month) {
        $data = json_decode(file_get_contents(__DIR__ . '/fakebank.json'), true);

        $montStr = $month->format('Y-m');

        $oTransactions = [];
        if (isset($data[$montStr])) {
            foreach ($data[$montStr] as $tArr) {
                $t = new Transaction();
                $t->id = $tArr['id'];
                $t->amount = $tArr['amount'];
                $t->description = $tArr['description'];
                $t->currency = $tArr['currency'];
                $t->date = $tArr['date'];

                $oTransactions[] = $t;
            }
        } else {
            $t = new Transaction();
            $t->id = 1;
            $t->amount = 10;
            $t->description = 'fake transaction (fakebank.json)';
            $t->currency = 'EUR';
            $t->date = $montStr.'-01';

            $oTransactions[] = $t;
        }
        return $oTransactions;
    }
}