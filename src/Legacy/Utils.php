<?php
/**
 * User: remmel
 * Date: 02/05/18
 * Time: 01:31
 */

namespace App\Legacy;


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
    public static function cleanNameToFilename($s) {
        return str_replace(['/', '_',], ['-', '-'], $s);
    }


    /**
     * Get the csv file as array. To skip header use array_shift($rows)
     * use str_getcsv if the parameter is string (data)
     */
    public static function file_get_contents_csv($path, $delimiter = ',') {
        $rows = [];
        if (($handle = fopen($path, "r")) !== FALSE) {
            while (($data = fgetcsv($handle, null, $delimiter)) !== FALSE) {
                $rows[] = $data;
            }
            fclose($handle);
        }
        return $rows;
    }

    /**
     * Transforms a php array in csv file
     * The dirty code, helps  the data 'a b' is encoded as '"a b"', remove uncessary quote
     */
    public static function file_put_contents_csv(array $rows, $path, $delimiter = ',') {
        $fp = fopen($path, 'w');
        foreach ($rows as $row) {
            fputcsv($fp, (array)$row, $delimiter);
        }
        fclose($fp);
    }

    /**
     * Get the csv file as list of object (stdclass).
     * Need to have a header to be able to get properties.
     */
    public static function file_get_contents_csv_header($path, $delimiter = ',') {
        $rows = self::file_get_contents_csv($path, $delimiter);
        $header = array_shift($rows);
        return self::arrayToAssoc($rows, $header);
    }

    public static function file_put_contents_csv_header(array $objects, $path, $delimiter = ',') {
        $keys = array_keys((array)$objects[0]);
        self::file_put_contents_csv(array_merge([$keys], $objects), $path, $delimiter);
    }

    /**
     * @param $rows array[]
     * @param $header array
     * @return array[]
     */
    public static function arrayToAssoc($rows, $header): array {
        $orows = [];
        foreach ($rows as $row) {
            $obj = new \stdClass();
            foreach ($row as $k => $v) {
                $obj->{$header[$k]} = $v;
            }
            $orows[] = $obj;
        }
        return $orows;
    }

    static public function ini_encode($value, $has_sections = FALSE) {
        $content = "";
        if ($has_sections) {
            foreach ($value as $key => $elem) {
                $content .= "[" . $key . "]\n";
                foreach ($elem as $key2 => $elem2) {
                    if (is_array($elem2)) {
                        for ($i = 0; $i < count($elem2); $i++) {
                            $content .= $key2 . "[] = " . $elem2[$i] . "\n";
                        }
                    } else if ($elem2 == "") $content .= $key2 . " = \n";
                    else $content .= $key2 . " = " . $elem2 . "\n";
                }
            }
        } else {
            foreach ($value as $key => $elem) {
                if (is_array($elem)) {
                    for ($i = 0; $i < count($elem); $i++) {
                        $content .= $key . "[] = \"" . $elem[$i] . "\"\n";
                    }
                } else if ($elem == "") $content .= $key . " = \n";
                else $content .= $key . " = \"" . $elem . "\"\n";
            }
        }
        return $content;
    }
}