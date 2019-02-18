<?php
/**
 * User: remmel
 * Date: 30/10/18
 * Time: 17:10
 */

namespace App\Service\Bank;


use App\Entity\Transaction;

class OfxBankConnector {
    protected $file;

    public function __construct($file) {
        $this->file = $file;
    }

    /**
     * @return Transaction[]
     */
    public function transactions() {
        $xml = $this->sgmlToXml(file_get_contents($this->file));
        $root = new \SimpleXMLElement($xml);
        //xmlrpc_

        $transactionsImported = [];
        foreach ($root->BANKMSGSRSV1->STMTTRNRS->STMTRS->BANKTRANLIST->STMTTRN as $t) {
            $ot = new Transaction();
            $d = \DateTime::createFromFormat('Ymd', $t->DTPOSTED);
            if (!$d) {
                die('Err Convert date :' . $t->Date);
            }
            $ot->date = $d->format('Y-m-d');
            $ot->description = trim($t->NAME);
            $memo = trim((string)$t->MEMO);
            if ($memo) $ot->description .= $memo;
            $ot->amount = (float)$t->TRNAMT;
            $transactionsImported[] = $ot;
        }
        return array_reverse($transactionsImported);
    }

    /**
     * 1) Get headers
     * 2) Close unclosed tags - Dirty way
     * 2.1) List tag name which use close tag
     * 2.2) Close tags which are not in that list
     */
    function sgmlToXml($ofxContent) {
        $ofxContent = iconv('Windows-1252', 'UTF-8', $ofxContent); //mb_convert_encoding($ofxContent, 'UTF-8', 'Windows-1252');
        $ofxContent = str_replace(["\r\n", "\r"], "\n", $ofxContent);

        //1) Get Header TODO

        $sgmlStart = stripos($ofxContent, '<OFX>');
        $ofxSgml = trim(substr($ofxContent, $sgmlStart));

        //2) closeUnclosedXmlTags
        $sgmlRows = explode("\n", $ofxSgml);
        $closed = [];
        foreach ($sgmlRows as $row) {
            if(preg_match("/<\/(.*)>/", $row, $matches)) {
                $closed[] = $matches[1];
            }
        }

        $buffer = '';
        foreach ($sgmlRows as $row) {
            if(preg_match("/<(\w*)>(.*)/", $row, $matches)) {
                $tag = $matches[1];
                if(!in_array($tag, $closed)) {
                    $content = htmlspecialchars($matches[2]);
                    $buffer.= "<$tag>$content</$tag>\n";
                } else {
                    $buffer.= $row."\n";
                }
            } else {
                $buffer.= $row."\n";
            }
        }
        return $buffer;
    }
}