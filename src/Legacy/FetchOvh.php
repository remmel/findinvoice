<?php
/**
 * User: remmel
 * Date: 13/07/18
 * Time: 16:17
 */

namespace App\Legacy;


use Ovh\Api;

class FetchOvh {
    const APPLICATION_KEY = 'aa'; //findinvoice
    const APPLICATION_SECRET = 'aa';
    const CONSUMER_KEY = 'xxxxxxxxxx';

    //https://api.ovh.com/console/#/auth/credential#POST
    //https://api.ovh.com/g934.first_step_with_api
    //https://eu.api.ovh.com/createApp/
    public function invoicesId($month, $existingInvoiceIds) {

       //go here to create key

        /**
         * Instanciate an OVH Client.
         * You can generate new credentials with full access to your account on
         * the token creation page
         */
        $ovh = new Api( self::APPLICATION_KEY,  // Application Key
            self::APPLICATION_SECRET,  // Application Secret
            'ovh-eu',      // Endpoint of API OVH Europe (List of available endpoints)
            self::CONSUMER_KEY); // Consumer Key

        $result = $ovh->post('/auth/credential', array(
            'accessRules' => '[{"method":"GET","path":"/me/bill"}]', // Required: Access required for your application (type: auth.AccessRule[])
            'redirection' => '', // Where you want to redirect the user after sucessfull authentication (type: string)
        ));

//        $result = $ovh->get('/me/bill', array(
//            'date.from' => '2018-06-01', // Filter the value of date property (>=) (type: datetime)
//            'date.to' => '2018-07-15', // Filter the value of date property (<=) (type: datetime)
//        ));

        print_r( $result );
    }
}