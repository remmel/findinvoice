<?php
/**
 * User: remmel
 * Date: 27/10/18
 * Time: 17:21
 */

namespace App\Service\Bank;


use App\Entity\Transaction;
use App\Legacy\Utils;

class WeboobBankConnector {

    public function __construct($bank, $login, $password) {
        $this->bank = $bank;
        $this->password = $password;
        $this->login = $login;
    }

    /**
     * Returns the list of transaction from a specific date
     * @param \DateTime $month
     * @return Transaction[]
     */
    public function transactions(?\DateTime $fromDate) {
        $f = '/home/remmel/.config/weboob/backends';
        $arr = explode('/', $this->bank);
        $module = $arr[0];
        $website = $arr[1];
        $login = $this->login;
        //check that login 6 characters

        $id = $module.'_'.$login;

        $content = Utils::ini_encode([
            $id => [
                '_module' => $module,
                'website' => $website,
                'login' => $login,
                'password' => $this->password
            ]
        ], true);
        file_put_contents($f, $content);
        $json = shell_exec("boobank history $login@$id -n 9999999 -f json"); //2014-01-30
        $data = json_decode($json);
        $transactions = [];
        foreach ($data as $d) {
            $t = new Transaction();
            $t->date = $d->date;
            $t->description = trim($d->raw);
            $t->amount = (float)$d->amount;
            $transactions[] = $t;
        }
        return array_reverse($transactions);
    }


    public static function getBanks() {
        //https://github.com/franek/weboob/blob/master/modules/cragr/backend.py
        $content = file_get_contents(__DIR__ . '/weboob_banks.json');
        return json_decode($content, true);
    }
}
