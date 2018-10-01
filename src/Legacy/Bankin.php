<?php
/**
 * User: remmel
 * Date: 02/05/18
 * Time: 01:27
 */

namespace App\Legacy;


class Bankin implements IBank {
    protected $token = null;
    protected $email = null;
    protected $password = null;

    //todo put into cache
    protected function getToken($email, $password) {
        if (!$this->token) {
            $auth = $this->authenticate($email, $password);
            $this->token = $auth->access_token;
        }
        return $this->token;
    }

    public function __construct() {
        $this->email = $_SESSION['email'];
        $this->password = $_SESSION['password'];
    }

    /**
     * Register user
     */
    public function addUser($email, $password) {
        $content = Utils::curl([
            CURLOPT_URL => "https://sync.bankin.com/v2/users?" . http_build_query([
                    'email' => $email,
                    'password' => $password,
                    'client_id' => BANKIN_API_CLIENT_ID,
                    'client_secret' => BANKIN_API_SECRET,
                ]),
            CURLOPT_POST => 1,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_HTTPHEADER => ["Bankin-Version: 2016-01-18"]
        ]);
        return json_decode($content);
    }


    /**
     * Authenticate user / Get token
     */
    public function authenticate($email, $password) {
        $content = Utils::curl([
            CURLOPT_URL => "https://sync.bankin.com/v2/authenticate?" . http_build_query([
                    'email' => $email,
                    'password' => $password,
                    'client_id' => BANKIN_API_CLIENT_ID,
                    'client_secret' => BANKIN_API_SECRET,
                ]),
            CURLOPT_POST => 1,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_HTTPHEADER => ["Bankin-Version: 2016-01-18"]
        ]);
        return json_decode($content);
    }

    /**
     * Get url to add new bank account & credentials
     */
    public function addUrl($client, $password) {
        $token = $this->getToken($client, $password);
        $content = Utils::curl([
            CURLOPT_URL => "https://sync.bankin.com/v2/items/add/url?" . http_build_query([
                    'client_id' => BANKIN_API_CLIENT_ID,
                    'client_secret' => BANKIN_API_SECRET,
                ]),
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_HTTPHEADER => ["Bankin-Version: 2016-01-18", "Authorization: Bearer $token"]
        ]);
        return json_decode($content);
        //curl "https://sync.bankin.com/v2/items/add/url?client_id=21ca2fbcb0094f789ff2c835d4c22425&client_secret=kFybYHDCUZuIAOA2GJvq3onySLykrYbdA7JYUJVAs7Xl8Ss5lsJg5U9DQJeVhHJv" \
//-H 'Authorization: Bearer 3b4df61feeac9a6b2546793e075a1d4d47ca903c-f8f593c2-b776-49cb-83c6-afeaff22efaf' \
    }

    public function accounts($client, $password) {
        $token = $this->getToken($client, $password);
        $content = Utils::curl([
            CURLOPT_URL => "https://sync.bankin.com/v2/accounts?" . http_build_query([
                    'limit' => 10,
                    'client_id' => BANKIN_API_CLIENT_ID,
                    'client_secret' => BANKIN_API_SECRET,
                ]),
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_HTTPHEADER => ["Bankin-Version: 2016-01-18", "Authorization: Bearer $token"]
        ]);
        if (!strpos($content, 'resources')) die($content);
        return json_decode($content)->resources;
    }

    /**
     * @inheritdoc
     */
    public function transactions(\DateTime $month) {
        $token = $this->getToken($this->email, $this->password);
        $content = Utils::curl([
            CURLOPT_URL => "https://sync.bankin.com/v2/transactions?" . http_build_query([
                    'since' => $month->format('Y-m-d'),
                    'until' => $month->format('Y-m-t'),
                    'limit' => 500,
                    'client_id' => BANKIN_API_CLIENT_ID,
                    'client_secret' => BANKIN_API_SECRET,
                ]),
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_HTTPHEADER => ["Bankin-Version: 2016-01-18", "Authorization: Bearer $token"]
        ]);
        $btransactions = array_reverse(json_decode($content)->resources);

        $oTransactions = [];
        foreach ($btransactions as $bt) {
            $t = new Transaction();
            $t->id = $bt->id;
            $t->date = $bt->date;
            $t->description = $bt->raw_description;
            $t->amount = $bt->amount;
            $t->currency = $bt->currency_code;

            $oTransactions[] = $t;
        }
        return $oTransactions;
    }

    private function callTransaction($token, $pagination = null) {
        $url = "https://sync.bankin.com/v2/transactions?limit=500&";
        if($pagination) {
            $url = "https://sync.bankin.com".$pagination->next_uri.'&';
        }
        $content = Utils::curl([
            CURLOPT_URL =>  $url . http_build_query([
                    'client_id' => BANKIN_API_CLIENT_ID,
                    'client_secret' => BANKIN_API_SECRET,
                ]),
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_HTTPHEADER => ["Bankin-Version: 2016-01-18", "Authorization: Bearer $token"]
        ]);
        return json_decode($content);
    }

    public function exportBankin(){
        $token = $this->getToken($this->email, $this->password);
        $transactionsCsv = [];
        $result = null;
        do {
            $result = $this->callTransaction($token, $result&&$result->next_uri?$result->pagination:null);
            foreach ($result->resources as $t) {
                $transactionsCsv[] = [
                    'id' => $t->id,
                    'resource_type' => $t->resource_type,
                    'description' => $t->description,
                    'raw_description' => $t->raw_description,
                    'amount' => $t->amount,
                    'date' => $t->date,
                    'update_at' => $t->update_at,
                    'currency_code' => $t->currency_code,
                    'is_deleted' => $t->is_deleted,
                    'category_id' => $t->category->id,
                    'account_id' => $t->account->id
                ];
            }
//            Utils::file_put_contents_csv_header($transactionsCsv, __DIR__ . '/tmp_bankin_export.csv');
        } while($result->pagination);

        Utils::file_put_contents_csv_header($transactionsCsv, __DIR__ . '/tmp_bankin_export.csv');
    }
}