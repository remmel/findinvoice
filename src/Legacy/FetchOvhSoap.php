<?php
/**
 * User: remmel
 * Date: 13/07/18
 * Time: 16:17
 */

namespace App\Legacy;


use SoapClient;

class FetchOvhSoap {
    public function invoicesId(\DateTime $date, float $amount) {
        $soap = new SoapClient("https://www.ovh.com/soapi/soapi-re-1.63.wsdl");
        $session = $soap->login(FETCH_OVHSOAP_NIC, FETCH_OVHSOAP_PASSWORD, 'fr', true);
        $invoices = $soap->billingInvoiceList($session);
        $ins = [];

        //order by proximity
        foreach ($invoices as $i) {
            $i->proximity = (int)$date->diff(new \DateTime($i->date), true)->format("%a");
            if($i->totalPriceWithVat == abs($amount)) {
                $i->proximity-=5; //calculate diff price instead
            }
        }
        usort($invoices, function($a,$b) {
            return $a->proximity > $b->proximity;
        });

        foreach ($invoices as $i) {
            $ins[$i->billnum] = substr($i->date, 0, 10) . ' - ' . sprintf("%.2f", $i->totalPriceWithVat) . '€ - ' . $i->billnum;
        }

        $soap->logout($session);

        return $ins;
    }

    public function download($id) {
        $soap = new SoapClient("https://www.ovh.com/soapi/soapi-re-1.63.wsdl");
        $session = $soap->login(FETCH_OVHSOAP_NIC, FETCH_OVHSOAP_PASSWORD, 'fr', true);
        $invoice = $soap->billingInvoiceInfo($session, $id);
        $soap->logout($session);
        $fn = "https://www.ovh.com/cgi-bin/order/facture.pdf?reference=$id&passwd=$invoice->password";
        return $this->curl($fn);
    }


    public function curl($url) {
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_HEADER, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        if (($response = curl_exec($curl)) !== false) {
            if (curl_getinfo($curl, CURLINFO_HTTP_CODE) == '200') {
                $reDispo = '/^Content-Disposition: .*?filename=(?<f>[^\s]+|\x22[^\x22]+\x22)\x3B?.*$/m';
                if (preg_match($reDispo, $response, $mDispo)) {
                    $filename = trim($mDispo['f'], ' ";');
                }
            }
        }

        curl_close($curl);
        $destination = tempnam(sys_get_temp_dir(), 'ovhsoap_') . '_' . $filename;
        $file = fopen($destination, "w+");
        fwrite($file, $response);
        fclose($file);

        $fnwithoutExt = preg_replace('/\\.[^.\\s]{3,4}$/', '', $filename);;

        return [$destination, $fnwithoutExt];
    }
}