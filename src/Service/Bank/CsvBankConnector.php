<?php
/**
 * User: remmel
 * Date: 30/10/18
 * Time: 17:10
 */

namespace App\Service\Bank;


use App\Legacy\Transaction;
use App\Legacy\Utils;

class CsvBankConnector {
    protected $file;

    public function __construct($bank, $file) {
        $this->file = $file;
    }

    /**
     * @return Transaction[]
     */
    public function transactions() {
        $content = file_get_contents($this->file);
        $content = utf8_encode($content);
        $tmp = tempnam(sys_get_temp_dir(), 'tmp_upload.csv');
        file_put_contents($tmp, $content);
        $rows = Utils::file_get_contents_csv($tmp, ';');
        unlink($tmp);

        for ($i = 0; $i < 11; $i++) //skip first 10 lines (credit agricole)
            $header = array_shift($rows);
        $assoc = Utils::arrayToAssoc($rows, $header);

        $transactionsImported = [];
        foreach ($assoc as $t) {
            if (!isset($t->Libellé) || !isset($t->{'Crédit Euros'})) continue;
            $ot = new Transaction();
            $d = \DateTime::createFromFormat('d/m/Y', $t->Date);
            if (!$d) {
                die('Err Convert date :' . $t->Date);
            }
            $ot->date = $d->format('Y-m-d');
            $ot->description = trim($t->Libellé);

            $credit = (float)str_replace(',', '.', $t->{'Crédit Euros'});
            $debit = (float)str_replace(',', '.', $t->{'Débit Euros'});

            $ot->amount = $credit + $debit * -1;
            $transactionsImported[] = $ot;
        }
        return array_reverse($transactionsImported);
    }
}