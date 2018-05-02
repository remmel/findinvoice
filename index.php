<?php

require_once __DIR__ . '/vendor/autoload.php';
if (file_exists(__DIR__ . '/parameters.php')) require_once __DIR__ . '/parameters.php';
else require_once __DIR__ . '/parameters.dumb.php';
session_start();

use Main\Bankin;
use Main\Main;

$bankin = new Bankin();
$main = new Main();


$action = isset($_POST['action'])?$_POST['action']:'';

//has account
if (!isset($_SESSION['email']) && !isset($_SESSION['password'])) {
    header('Location: user.php');
}

$month = $main->selectedMonth($_GET['month']);
$months = $main->listMonths();
if($action == 'upload') {
    $main->handleUpload($month);
} elseif ($action == 'delete') {
    $main->removeFile($month, $_POST['fn']);
}

$transactions = Main::reconciliation($bankin, $month);
?>

<script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">

<form method="get">
    Month : <select name="month" onchange="this.form.submit()">
        <?php foreach ($months as $m) { ?>
            <option value="<?= $m ?>" <?= $m == $month->format('Y-m') ? 'selected' : '' ?>>
                <?= $m ?>
            </option>
        <?php } ?>
        {% endfor %}
    </select>
</form>

<?php if ($transactions) { ?>
    <table class="table">
        <thead>
        <tr>
            <th>id</th>
            <th>date</th>
            <th>description</th>
            <th>amount</th>
            <th>currency</th>
            <th>doc</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($transactions as $t) { ?>
            <tr>
                <td><?= $t->id ?></td>
                <td style="display: overflow:hidden; white-space: nowrap;"><?= $t->date ?></td>
                <td><?= $t->description ?></td>
                <td bgcolor="<?= $t->amount > 0 ? "green" : "red" ?>"><?= $t->amount ?></td>
                <td><?= $t->currency ?></td>
                <td>
                    <?php if ($t->doc) { ?>
                        <a target="_blank" href="<?= $t->doclink ?>"><?= $t->doc ?></a>
                        <form method="post" style="display: inline-block" >
                            <input type="hidden" name="fn" value="<?= $t->doc ?>"/>
                            <button type="submit" name="action" value="delete">
                                <span>&times;</span>
                            </button>
                        </form>
                    <?php } else { ?>
                        <form method="post" enctype="multipart/form-data" class="upload">
                            <input type="file" name="receipt"/>
                            <input type="hidden" name="fn" value="<?= $t->upload ?>"/>
                            <input type="text" name="comment" value=""/>
                            <button type="submit" name="submit" name="action" value="upload">Upload</button></form>
                        </form>
                    <?php } ?>
                </td>
            </tr>
        <?php } ?>
        </tbody>
    </table>
<?php } ?>

<script>
    //as a default comment put the name of the file without path and extension
    $('.upload input[name=receipt]').change(function (e) {
        var fn = this.value;
        fn = fn.substring(fn.lastIndexOf('\\') + 1, fn.lastIndexOf('.'));
        $(this).parent().find("input[name=comment]").val(fn);
    });
</script>
