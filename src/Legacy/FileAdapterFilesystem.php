<?php
/**
 * User: remmel
 * Date: 20/06/18
 * Time: 18:53
 */

namespace App\Legacy;


class FileAdapterFilesystem implements IFileAdapter {

    protected $rootFolder = null;

    public function __construct() {
        //TODO inject variable using SF
        $this->rootFolder = $_ENV['FILEADAPTER_FS_FOLDER'];
    }

    /**
     * @inheritdoc
     * @return File[]
     */
    public function files(\DateTime $date) : array {
        $folder = self::getSubfolder($date);

        if(!file_exists($folder)) return [];
        $files = scandir($folder);

        $relativeFolder = substr($folder, strlen($this->rootFolder));

        $oFiles = [];
        foreach ($files as $f) {
            if ($f == '.' || $f == '..') continue;
            $oFile = new File();
            $oFile->name = $f;
            $oFile->id = $relativeFolder . $f;
            $oFile->viewlink = '/viewlocalfile.php?id=' . urlencode($oFile->id);

            $oFiles[] = $oFile;
        }
        return $oFiles;
    }

    /**
     * @inheritdoc
     */
    public function upload(\DateTime $month, $tmp, $newName) {
        $dir = self::getSubfolder($month);

        if(!file_exists($dir)) {
            mkdir($dir);
        }

        $destination = $dir.$newName;

        if (!is_writable($dir))
            throw new \Exception('not writable: ' . $dir);

        if (move_uploaded_file($tmp, $destination)) {
        } else {
//            var_dump($_FILES + $_POST);
            throw new \Exception('error uploading the file: '.$tmp.' -> '.$destination);
        }
    }

    /**
     * Give the folder where the receipt is.
     * TODO handle multiple structure strategy (everything in same folder, different month folder name)
     */
    protected function getSubfolder(\DateTime $date) {
        $d = $date->format('Y-m'); //201804
        return $this->rootFolder . "/$d/";
    }

    /**
     * Remove a file
     */
    public function remove($id) {
        unlink($this->rootFolder.$id);
    }
}