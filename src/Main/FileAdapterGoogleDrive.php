<?php
/**
 * User: remmel
 * Date: 20/06/18
 * Time: 18:53
 */

namespace Main;


use Google_Client;
use Google_Service_Drive;
use Google_Service_Drive_DriveFile;

class FileAdapterGoogleDrive implements IFileAdapter {

    protected $rootFolder;
    protected $accessToken;

    public function __construct($rootFolder, $accessToken) {
        $this->rootFolder = $rootFolder;
        $this->accessToken = $accessToken;
    }

    protected function getSubfolder(\DateTime $date) {
        $month = $date->format('Y-m');

        $client = new Google_Client();
        $client->setAuthConfig('client_secrets.json');
        $client->addScope(Google_Service_Drive::DRIVE_METADATA_READONLY);

        $client->setAccessToken($this->accessToken);
        $drive = new Google_Service_Drive($client);

        $gfiles = $drive->files->listFiles([
            "q" => "'$this->rootFolder' in parents and trashed=false",
            'pageSize' => 1000
        ]);

        $folderIdMonth = null;

        foreach ($gfiles as $f)
            if ($f->name == $month)
                return $f->id;

        throw new \Exception("folder $month not found in parent folder $this->rootFolder");
    }

    /**
     * @inheritdoc
     *
     * List files from Google Drive Account
     * 1) Search for folder with specific month (eg named '2018-05') in folder defined in parameters
     * 2) List files into that subfolder
     */
    public function files(\DateTime $date) {
        $client = new Google_Client();
        $client->setAuthConfig('client_secrets.json');
        $client->addScope(Google_Service_Drive::DRIVE_METADATA_READONLY);

        $client->setAccessToken($this->accessToken);
        $drive = new Google_Service_Drive($client);

        $folderIdMonth = $this->getSubfolder($date);

        $gfiles = $drive->files->listFiles([
            "q" => "'$folderIdMonth' in parents and trashed=false",
            'pageSize' => 1000
        ]);

        $oFiles = [];
        foreach ($gfiles as $f) {
            $oF = new File();
            $oF->name = $f->name;
            $oF->id = $f->id;
            $oF->viewlink = 'https://drive.google.com/open?id=' . $f->id;

            $oFiles[] = $oF;
        }
        return $oFiles;
    }

    /**
     * Upload a new file the subfolder
     * @param \DateTime $month
     * @param $tmp path of tmp file uploaded on server
     * @param $newName new name of the file eg (2018-06-34_GoogleSuite_345.00_june-4pax.pdf)
     * @return mixed
     */
    public function upload(\DateTime $month, $tmp, $newName) {
        $client = new Google_Client();
        $client->setAuthConfig('client_secrets.json');
        $client->addScope(Google_Service_Drive::DRIVE);

        $client->setAccessToken($this->accessToken);
        $drive = new Google_Service_Drive($client);

        $folderIdMonth = $this->getSubfolder($month);

        $fileMetadata = new Google_Service_Drive_DriveFile([
            'name' => $newName,
            'parents' => [$folderIdMonth]
        ]);
        $content = file_get_contents($tmp);
        $mimetype = mime_content_type($tmp);
        $file = $drive->files->create($fileMetadata, array(
            'data' => $content,
            'mimeType' => $mimetype,
            'uploadType' => 'multipart',
            'fields' => 'id'));
//        printf("File ID: %s\n", $file->id);
    }

    /**
     * Remove a file
     */
    public function remove($fId) {
        $client = new Google_Client();
        $client->setAuthConfig('client_secrets.json');
        $client->addScope(Google_Service_Drive::DRIVE);
        $client->setAccessType('offline');
        $client->setAccessToken($this->accessToken);
        $drive = new Google_Service_Drive($client);
        $drive->files->delete($fId);
    }
}