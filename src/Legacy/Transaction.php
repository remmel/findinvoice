<?php
/**
 * User: remmel
 * Date: 02/05/18
 * Time: 10:36
 */

namespace App\Legacy;


class Transaction extends \App\Entity\Transaction {

    public $currency;
    public $upload;

    /** @var File */
    public $file;

    /** @var string Link to easily find the invoice */
    public $helplink;
}