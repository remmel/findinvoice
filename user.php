<?php

require_once __DIR__ . '/vendor/autoload.php';
require_once file_exists(__DIR__ . '/parameters.php') ? __DIR__ . '/parameters.php' : __DIR__ . '/parameters.dumb.php';
session_start();

use Main\Bankin;

$bankin = new Bankin();

$email = isset($_GET['email']) ? $_GET['email'] : '';
$password = isset($_GET['password']) ? $_GET['password'] : '';
$action = isset($_GET['action']) ? $_GET['action'] : '';

//if (!isset($_SESSION['email']) && !isset($_SESSION['password'])) {
//
//} else {

if ($action === 'create') {
    $addUser = $bankin->addUser($email, $password);
    echo "<br/>AddUser. ";
    if (isset($addUser->message)) echo "<em>$addUser->message / $addUser->type</em>";

    if (isset($addUser->uuid) || (isset($addUser->type) && $addUser->type === 'conflict')) {
        echo "<br/>Authenticate. ";
        $auth = $bankin->authenticate($email, $password);
        if (isset($auth->type)) {
            echo "<em>$auth->message / $auth->type</em>";
        } else {
            echo "<br/>Get bankin url to connect with bank. ";
            $url = $bankin->addUrl($email, $password);
            if (isset($url->message)) echo "<em>$url->message / $url->type</em>";

            $_SESSION['email'] = $email;
            $_SESSION['password'] = $password;
        }
    } else {

    }
} else if ($action === 'login') {
    $auth = $bankin->authenticate($email, $password);
    if (isset($auth->type)) {
        echo 'cannot login: ' . $auth->message . ' / ' . $auth->type;
    } else {
        echo 'login successfully';
        $_SESSION['email'] = $email;
        $_SESSION['password'] = $password;
        header('Location: /');
    }
} else if ($action === 'logout') {
    unset($_SESSION['email']);
    unset($_SESSION['password']);
}

?>
<?php include "tpl_header.html" ?>

<?php if (isset($url->redirect_url)) { ?>
    <a href="<?= $url->redirect_url ?>">Connect bank account</a>. Then go back to home: <a href="/">Home</a>
<?php } ?>

Login or Create account to Bankin.<br />
Bankin is a tool to be able to extract report from your bank account
<form action="?action=create" method="GET">
    <div class="form-group">
        <label>Email</label>
        <input type="email" name="email" class="form-control" value="<?= $email ?>">
    </div>
    <div class="form-group">
        <label>Password</label>
        <input type="password" name="password" class="form-control" value="<?= $password ?>">
        <small class="form-text text-muted">Password to use that tool</small>
    </div>
    </div>
    <button type="submit" class="btn btn-primary" name="action" value="create">Add User</button>
    <button type="submit" class="btn btn-primary" name="action" value="login">Login</button>
</form>