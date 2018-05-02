<?php
/**
 * User: remmel
 * Date: 02/05/18
 * Time: 01:27
 */

namespace Main;


class Bankin {
    protected $token = null;

    //todo put into cache
    protected function getToken() {
        if(!$this->token) {
            $this->token = $this->authenticate();
        }
        return $this->token;
    }

    /**
     * Register user
     */
    public function addUser() {
        $content = Utils::curl([
            CURLOPT_URL => "https://sync.bankin.com/v2/users?".http_build_query([
                    'email' => BANKIN_USER_EMAIL,
                    'password' => BANKIN_USER_PASSWORD,
                    'client_id' => BANKIN_API_CLIENT_ID,
                    'client_secret' => BANKIN_API_SECRET,
                ]),
            CURLOPT_POST=> 1,
            CURLOPT_RETURNTRANSFER=> 1,
            CURLOPT_HTTPHEADER => ["Bankin-Version: 2016-01-18"]
        ]);
        echo $content;
    }

    /**
     * Authenticate user / Get token
     */
    public function authenticate() {
        $content = Utils::curl([
            CURLOPT_URL => "https://sync.bankin.com/v2/authenticate?".http_build_query([
                    'email' => BANKIN_USER_EMAIL,
                    'password' => BANKIN_USER_PASSWORD,
                    'client_id' => BANKIN_API_CLIENT_ID,
                    'client_secret' => BANKIN_API_SECRET,
                ]),
            CURLOPT_POST=> 1,
            CURLOPT_RETURNTRANSFER=> 1,
            CURLOPT_HTTPHEADER => ["Bankin-Version: 2016-01-18"]
        ]);
        $obj = json_decode($content);
        return $obj->access_token;
    }

    /**
     * Get url to add new bank account & credentials
     */
    public function addUrl() {
        //curl "https://sync.bankin.com/v2/items/add/url?client_id=21ca2fbcb0094f789ff2c835d4c22425&client_secret=kFybYHDCUZuIAOA2GJvq3onySLykrYbdA7JYUJVAs7Xl8Ss5lsJg5U9DQJeVhHJv" \
//-H 'Authorization: Bearer 3b4df61feeac9a6b2546793e075a1d4d47ca903c-f8f593c2-b776-49cb-83c6-afeaff22efaf' \
    }

    public function accounts() {
        $token = $this->getToken();
        $content = Utils::curl([
            CURLOPT_URL => "https://sync.bankin.com/v2/accounts?" . http_build_query([
                    'limit' => 10,
                    'client_id' => BANKIN_API_CLIENT_ID,
                    'client_secret' => BANKIN_API_SECRET,
                ]),
            CURLOPT_RETURNTRANSFER=> 1,
            CURLOPT_HTTPHEADER => ["Bankin-Version: 2016-01-18", "Authorization: Bearer $token"]
        ]);
        if(!strpos($content, 'resources')) die($content);
        return json_decode($content)->resources;
    }

    public function transactions($accountId, \DateTime $month) {
        $token = $this->getToken();
        $content = Utils::curl([
            CURLOPT_URL => "https://sync.bankin.com/v2/transactions?" . http_build_query([
                    'since' => $month->format('Y-m-d'),
                    'until' => $month->format('Y-m-t'),
                    'limit' => 500,
                    'client_id' => BANKIN_API_CLIENT_ID,
                    'client_secret' => BANKIN_API_SECRET,
                ]),
            CURLOPT_RETURNTRANSFER=> 1,
            CURLOPT_HTTPHEADER => ["Bankin-Version: 2016-01-18", "Authorization: Bearer $token"]
        ]);
        return array_reverse(json_decode($content)->resources);
    }
}