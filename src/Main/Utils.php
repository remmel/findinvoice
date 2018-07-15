<?php
/**
 * User: remmel
 * Date: 02/05/18
 * Time: 01:31
 */

namespace Main;


class Utils {
    /**
     * Curl wrapper
     * @param $options array eg [CURLOPT_URL => $url]
     * @return string content
     * @throws \Exception
     */
    public static function curl($options) {
        $ch = curl_init();
        if (empty($ch)) {
            throw new \Exception("curl_init ERROR : cURL doesn't seems to be available");
        }
        curl_setopt_array($ch, $options);
        $content = curl_exec($ch);
        if (curl_errno($ch)) {
            throw new \Exception("curl_exec ERROR " . curl_errno($ch) . " : " . curl_error($ch) . '. Url: ' . $options[CURLOPT_URL]);
        }
        curl_close($ch);

        if (strlen($content) == 0) {
            throw new \Exception("no data fetched");
        }

        return $content;
    }

    public static function contains($haystack, $needle) {
        return strpos($haystack, $needle) !== false;
    }

    public static function startsWith($haystack, $needle) {
        return (strcasecmp(substr($haystack, 0, strlen($needle)), $needle) === 0);
    }

    /**
     * Removes special characters and underscore to be able to get a nice filename
     * Underscore is removed because used by that tool to codify invoices
     */
    public static function cleanNameToFilename($s){
        return str_replace(['/', '_',], ['-', '-'], $s);
    }
}