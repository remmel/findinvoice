<?php
/**
 * User: remmel
 * Date: 12/07/18
 * Time: 18:33
 */


function startsWith($haystack, $needle) {
    // search backwards starting from haystack length characters from the end
    return $needle === "" || strrpos($haystack, $needle, -strlen($haystack)) !== FALSE;
}


$root = '/home/remmel/remy.mellet@gmail.com/Comparabus/administrative/accounting/achat/';

    $filesByFolders = [];
    $transactionsByDate = [];

    $folders = scandir($root);

    foreach ($folders as $folder) {
        if(startsWith($folder, '.')) continue;

        $files = scandir($root.$folder);

        $filesByFolders[$folder] = [];
        $transactionsByDate[$folder] = [];
        foreach ($files as $f) {
            if(startsWith($f, '.')) continue;
            if(!startsWith($f, '2018-')) continue;
            $filesByFolders[$folder][] = $f;

            $parts = explode('_', $f);
            $transactionsByDate[$folder][] = [
                'id' => rand(0, 1000000000),
                'date' => $parts[0],
                'description' => $parts[1],
                'amount' => (float)$parts[2],
                'currency' => 'EUR'
            ];
        }
    }

//    print_r(json_encode($filesByFolders, JSON_PRETTY_PRINT));
    print_r(json_encode($transactionsByDate, JSON_PRETTY_PRINT));


