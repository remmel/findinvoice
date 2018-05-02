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
}