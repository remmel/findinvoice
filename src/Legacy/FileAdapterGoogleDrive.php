<?php
/**
 * User: remmel
 * Date: 20/06/18
 * Time: 18:53
 */

namespace App\Legacy;


use Google_Client;
use Google_Service_Drive;
use Google_Service_Drive_DriveFile;

/**
 * Google API Guide : https://developers.google.com/api-client-library/php/start/get_started
 */
class FileAdapterGoogleDrive implements IFileAdapter {

    protected $rootFolder;
    protected $accessToken;
    protected $client;
    protected $drive;

    public function __construct($accessToken) {
        //TODO inject variable using SF
        $this->rootFolder = $_ENV['FILEADAPTER_GDRIVE_FOLDER'];
        $this->accessToken = $accessToken;

        $this->client = new Google_Client();
        $this->client->setAuthConfig('../config/client_secrets.json');
//        $this->client->addScope(Google_Service_Drive::DRIVE_METADATA_READONLY);
        $this->client->setAccessType('offline');
        $this->client->setRedirectUri('http://' . $_SERVER['HTTP_HOST'] . '/oauth2callback.php');
        if ($this->accessToken)
            $this->client->setAccessToken($this->accessToken);
        $this->drive = new Google_Service_Drive($this->client);
    }

    public function authenticateIfNeeded() {

        //1st conection
        if ($this->accessToken === null) {
            $redirect_uri = 'http://' . $_SERVER['HTTP_HOST'] . '/oauth2callback.php';
            header('Location: ' . filter_var($redirect_uri, FILTER_SANITIZE_URL));


            $this->client->setRedirectUri('http://' . $_SERVER['HTTP_HOST'] . '/oauth2callback.php');
            $this->client->addScope([
                Google_Service_Drive::DRIVE
            ]);

            header('Location: ' . filter_var($this->client->createAuthUrl(), FILTER_SANITIZE_URL));
            die("");
        } else {
            if ($this->client->isAccessTokenExpired()) {

                die("expired");

//                throw new \Exception("TODO handle when token is expired");
//              print_r($accessToken);
                $to = $this->client->refreshToken($this->accessToken);
            }

        }

    }

    public function authenticateCallback($code) {
        $this->client->authenticate($code);
        return $this->client->getAccessToken();
    }

    public function revoke() {
        return $this->client->revokeToken();
    }

    protected function getSubfolder(\DateTime $date) {
        $month = $date->format('Y-m');

        $gfiles = $this->drive->files->listFiles([
            "q" => "'$this->rootFolder' in parents and trashed=false",
            'pageSize' => 1000
        ]);

        $folderIdMonth = null;

        foreach ($gfiles as $f)
            if ($f->name == $month)
                return $f->id;

        //mandatory?
        $this->client->addScope(Google_Service_Drive::DRIVE);

        $file = $this->drive->files->create(
            new Google_Service_Drive_DriveFile([
                'name' => $month,
                'parents' => [$this->rootFolder],
                'mimeType' => 'application/vnd.google-apps.folder'
            ]), [
            'uploadType' => 'multipart',
            'fields' => 'id',
        ]);
        return $file->id;
    }

    /**
     * @inheritdoc
     *
     * List files from Google Drive Account
     * 1) Search for folder with specific month (eg named '2018-05') in folder defined in parameters
     * 2) List files into that subfolder
     */
    public function files(\DateTime $date) {
        $folderIdMonth = $this->getSubfolder($date);

        $gfiles = $this->drive->files->listFiles([
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
     * @param string $tmp path of tmp file uploaded on server
     * @param string $newName new name of the file eg (2018-06-34_GoogleSuite_345.00_june-4pax.pdf)
     * @return mixed
     */
    public function upload(\DateTime $month, $tmp, $newName) {
        $folderIdMonth = $this->getSubfolder($month);

        $this->client->addScope(Google_Service_Drive::DRIVE);

        $fileMetadata = new Google_Service_Drive_DriveFile([
            'name' => $newName,
            'parents' => [$folderIdMonth]
        ]);

        if (!file_exists($tmp)) throw new \Exception("file $tmp missing");
        $content = file_get_contents($tmp);

        $mimetype = mime_content_type($tmp);
        $file = $this->drive->files->create($fileMetadata, array(
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
        $this->client->addScope(Google_Service_Drive::DRIVE);
        $this->drive->files->delete($fId);
    }
}