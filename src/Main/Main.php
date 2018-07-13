<?php
/**
 * User: remmel
 * Date: 02/05/18
 * Time: 10:36
 */

namespace Main;


use DateInterval;
use DatePeriod;
use DateTime;

class Main {
    const HELP = [
        'Amazon Payments' => 'https://www.amazon.fr/gp/css/order-history/ref=nav_youraccount_orders',
        'Ovh' => 'https://www.ovh.com/manager/dedicated/index.html#/billing/history',
        'Google *svcsapps' => 'https://mail.google.com/mail/u/1/#search/from%3Apayments-noreply%40google.com',
        'Google*cloud' => 'https://console.cloud.google.com/billing/',
        'Google *adws210617042' => 'https://adwords.google.fr/um/identity?dst=/um/Billing/Home#th',
        'Facebk *' => 'https://business.facebook.com/ads/manager/billing/transactions/',
//        'facebook ad perso'	=> 'https://www.facebook.com/ads/manager/billing/transactions/',
        'Microsoft *bing Ads Msbill' =>	'https://azure.bingads.microsoft.com/cc/Billing/History',
        'Mgp*5euros' =>	'https://5euros.com/achats/factures',
        'Free Mobile' => 'https://mail.google.com/mail/u/1/#advanced-search/from=freemobile%40free-mobile.fr&subject=Facture',
        'Adobe Stock' => 'https://accounts.adobe.com/plans/',
        'Bap Link' => 'https://poissonniers.espace.link/gestion/factures/recurrentes',
        'Lespace - Poissonnie' => 'https://poissonniers.espace.link/gestion/depot-de-garantie',
        'Fiverr' => 'https://mail.google.com/mail/u/1/#search/from%3A(invoices%40fiverr.com)',
        'Scaleway' => 'https://cloud.scaleway.com/#/billing'
    ];

    /** @var IFileAdapter */
    protected $fileAdapter;

    public function __construct(IFileAdapter $fa) {
        $this->fileAdapter = $fa;
    }

    public function filterPositiveTransactions(IBank $bank, \DateTime $month) {
        $transactions = $bank->transactions($month);
        foreach ($transactions as $k => $t) {
            if($t->amount < 0)
                unset($transactions[$k]);
        }
        return $transactions;
    }

    /**
     * Matchs bank row with receipt
     * Currenlty the key to link bank row with receipt is the DATE_AMOUNT.
     * TODO Handle if same amount twice the same day.
     */
    public function reconciliation(IBank $bank, \DateTime $month) {
        $files = $this->fileAdapter->files($month);
        $assocFiles = [];
        foreach ($files as $f) {
            $nameNoExt = pathinfo($f->name, PATHINFO_FILENAME);
            $parts = explode('_', $nameNoExt);
            $key = $parts[0] . '_' . $parts[2];
            if(!isset($assocFiles[$key])) $assocFiles[$key] = [];
            $assocFiles[$key][] = $f;
        }

        $transactions = $bank->transactions($month);
        //add info for each transaction
        foreach ($transactions as $t) {
            $t->upload = self::filename($t);
            $key = $t->date . '_' . number_format(abs($t->amount), 2,'.', '');

            if (isset($assocFiles[$key]) && count($assocFiles[$key]) > 0) {
                $f = array_shift($assocFiles[$key]);
                $t->file = $f;
            } else {
                //if not document uploaded, display some help to find that doc
                $t->helplink = self::findHelp($t->description);
            }
        }

        //files not linked to any transaction. Happend if added manually into folder
        $orphanFiles = [];
        foreach ($assocFiles as $fileByKey) {
            foreach ($fileByKey as $f) {
                $orphanFiles[] = $f;
            }
        }

        return [$transactions, $orphanFiles];
    }

    /**
     * If a file is uploaded, add it to the folder
     */
    public function handleUpload(\DateTime $month) {


        if (isset($_FILES['receipt'])) {
            $receipt = $_FILES['receipt'];

            $path_info = pathinfo($receipt['name']);
            $commentPart = isset($_POST['comment']) ? '_' . Utils::cleanNameToFilename($_POST['comment']) : '';
            $fn = $_POST['fn'];
            $newName = $fn . $commentPart . '.' . $path_info['extension'];

            $this->fileAdapter->upload($month, $receipt['tmp_name'], $newName);
        }
    }

    public static function filename(Transaction $t, $info = '') {
        $desc = $t->description;
        $desc = str_replace(['Virement Web ', 'Virement ', 'Paiement Par Carte ', 'Prelevmnt '], ['', '', '', ''], $desc);
        $words = explode(' ', $desc);
        $desc = Utils::cleanNameToFilename($words[0] . '-' . $words[1]);
        return $t->date . '_' . $desc . '_' . number_format(abs($t->amount), 2,'.', '') . ($info && strlen($info)>0 ? ('_' . $info) : '');
    }

    /**
     * Process the month query. If don't exists use the current month
     * @param $queryMonth iso date eg : 2018-12
     * @return DateTime
     */
    public function selectedMonth(&$queryMonth) {
        $currentMonth = (new DateTime())->modify('first day of this month');
        return isset($queryMonth) ? new DateTime($queryMonth) : $currentMonth;
    }

    /**
     * Returns the list of month as ISO string (YYYY-MM)
     * @return string[]
     */
    public function listMonths() {
        $start = (new DateTime(FIRST_MONTH . '-01'))->modify('first day of this month');
        $currentMonth = (new DateTime())->modify('first day of this month');
        $interval = DateInterval::createFromDateString('1 month');
        $months = new DatePeriod($start, $interval, $currentMonth);

        $labels = [];
        foreach ($months as $m) {
            $labels[] = $m->format('Y-m');
        }
        return array_reverse($labels);
    }

    public function findHelp($description) {
        foreach (self::HELP as $words => $link) {
            if(Utils::contains($description, $words)) {
                return $link;
            }
        }

        return null;
    }
}