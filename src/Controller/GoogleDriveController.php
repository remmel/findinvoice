<?php
/**
 * User: remmel
 * Date: 23/10/18
 * Time: 16:45
 */

namespace App\Controller;


use App\Legacy\FileAdapterGoogleDrive;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class GoogleDriveController extends AbstractController {
    /**
     * @Route("/oauth2callback.php", name="google_callback", methods={"GET"})
     */
    public function index() {
        if (isset($_GET['code'])) {
            $adapter = new FileAdapterGoogleDrive(null);
            $this->get('session')->set('access_token', $adapter->authenticateCallback($_GET['code']));
            header('Location: /');
            die("");
        } else {
            throw new \Exception('missing code');
        }
    }

    /**
     * @Route("/revoke", name="google_revoke", methods={"GET"})
     */
    public function revoke() {
        $adapter = new FileAdapterGoogleDrive($this->get('session')->get('access_token'));
        $ret = $adapter->revoke();
        $this->get('session')->clear();
        die('revoked');
    }
}