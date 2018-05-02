<?php

require_once __DIR__ . '/vendor/autoload.php';
if (file_exists(__DIR__ . '/parameters.php')) require_once __DIR__ . '/parameters.php';
else require_once __DIR__ . '/parameters.dumb.php';
session_start();

use Main\Bankin;
use Main\Main;

$bankin = new Bankin();

$email = isset($_GET['email']) ? $_GET['email'] : '';
$password = isset($_GET['password']) ? $_GET['password'] : '';

//if (!isset($_SESSION['email']) && !isset($_SESSION['password'])) {
//
//} else {

if (isset($_GET['action_create'])) {
    $addUser = $bankin->addUser($email, $password);
    echo "<br/>AddUser. ";
    if(isset($addUser->message)) echo "<em>$addUser->message / $addUser->type</em>";

    if (isset($addUser->uuid) || (isset($addUser->type) && $addUser->type == 'conflict')) {
        echo "<br/>Authenticate. ";
        $auth = $bankin->authenticate($email, $password);
        if(isset($auth->type)) {
            echo "<em>$auth->message / $auth->type</em>";
        }
        else {
            echo "<br/>Get bankin url to connect with bank. ";
            $url = $bankin->addUrl($email, $password);
            if(isset($url->message)) echo "<em>$url->message / $url->type</em>";

            $_SESSION['email'] = $email;
            $_SESSION['password'] = $password;
        }
    } else {

    }
} else if (isset($_GET['action_login'])) {
    $auth = $bankin->authenticate($email, $password);
    if (isset($auth->type)) {
        echo 'cannot login: '.$auth->message.' / '.  $auth->type;
    }
    else {
        echo 'login successfully';
        $_SESSION['email'] = $email;
        $_SESSION['password'] = $password;
        header('Location: /');
    }
}

?>

<?php if (isset($url->redirect_url)) { ?>
    <a href="<?= $url->redirect_url ?>">Connect bank account</a>. Then go back to home: <a href="/">Home</a>
<?php } ?>

<form action="?action=create" method="GET">
    email: <input type="email" name="email" value="<?= $email ?>"><br/>
    password: <input type="password" name="password" value="<?= $password ?>"><br/>
    <input type="submit" name="action_create" value="Create User">
    <input type="submit" name="action_login" value="Login">
</form>
