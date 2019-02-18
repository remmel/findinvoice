<?php
/**
 * User: remmel
 * Date: 11/02/19
 * Time: 08:43
 */

namespace App\Service\Bank;

use PHPUnit\Framework\TestCase;

class OfxBankConnectorTest extends TestCase {
    public function testTransactions() {
        $this->assertTrue(true);
        $connect = new OfxBankConnector(__DIR__ . '/CA20190208.ofx');
        $transactions = $connect->transactions();
        $t = end($transactions);
        $this->assertEquals('OPER CB HORS UE  N°308203', $t->description);
    }
}
