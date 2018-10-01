<?php
/**
 * User: remmel
 * Date: 30/09/18
 * Time: 12:54
 */

namespace App\Legacy;


class UtilsTest extends \PHPUnit\Framework\TestCase {
    public function testcsv() {
        $path = "/tmp/utiltestcsv.csv";
        $array = [["name", "age"], ["Remy", 29], ["Lesly", 33], ["Yen", null], [null, 12], ["a,b", "99"], ["a b", "éèàâ"]];
        Utils::file_put_contents_csv($array, $path);
        $content = file_get_contents($path);
        $this->assertEquals("name,age\nRemy,29\nLesly,33\nYen,\n,12\n\"a,b\",99\n\"a b\",éèàâ\n", $content);
        $arrayLoaded = Utils::file_get_contents_csv($path);
        $this->assertEquals($array, $arrayLoaded);
    }

    public function testcsvHeader() {
        $path = "/tmp/utiltestcsvheader.csv";
        $array = [["name" => "Remy", "age" => 29], ["name" => "Lesly", "age" => 33], ["name" => "Yen", "age" => null], ["name" => null, "age" => 12], ["name" => "a,b", "age" => "99"], ["name" => "a b", "age" => "éèàâ"]];
        Utils::file_put_contents_csv_header($array, $path);
        $content = file_get_contents($path);
        $this->assertEquals("name,age\nRemy,29\nLesly,33\nYen,\n,12\n\"a,b\",99\n\"a b\",éèàâ\n", $content);
        $arrayLoaded = Utils::file_get_contents_csv_header($path);

        $this->assertEquals("Remy", $arrayLoaded[0]->name);
        $this->assertEquals("éèàâ", $arrayLoaded[5]->age);
    }
}
