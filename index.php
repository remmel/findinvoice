<?php

require_once __DIR__ . '/vendor/autoload.php';
require_once file_exists(__DIR__ . '/parameters.php') ? __DIR__ . '/parameters.php' : __DIR__ . '/parameters.dumb.php';
session_start();

use Main\Bankin;
use Main\Main;

$bankin = new Bankin();
$main = new Main();


$action = isset($_POST['action'])?$_POST['action']:'';

//is not logged
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

<?php include "tpl_header.html" ?>
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
            <th>date</th>
            <th>description</th>
            <th>amount</th>
            <th>doc</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($transactions as $t) { ?>
            <tr>
                <td style="display: overflow:hidden; white-space: nowrap;"><?= $t->date ?></td>
                <td><?= $t->description ?></td>
                <td bgcolor="<?= $t->amount > 0 ? "green" : "red" ?>"><?= $t->amount ?></td>
                <td bgcolor="<?= $t->doc ? "" : "red" ?>">
                    <?php if ($t->doc) { ?>
                        <a target="_blank" href="<?= $t->doclink ?>"><?= $t->doc ?></a>
                        <form method="post" style="display: inline-block" >
                            <input type="hidden" name="fn" value="<?= $t->doc ?>"/>
                            <button type="submit" name="action" value="delete">
                                <span>&times;</span>
                            </button>
                        </form>
                    <?php } else { ?>
                        <?php if ($t->helplink) { ?>
                            <a target="_blank" href="<?= $t->helplink ?>">Help</a>
                        <?php } ?>
                        <form method="post" enctype="multipart/form-data" class="upload">

                            <input type="file" name="receipt"/><br />
                            <div class="input-group mb-3" style="width: 300px">
                                <input type="text" class="form-control" placeholder="comment" name="comment" value="">
                                <div class="input-group-append">
                                    <button type="submit" class="btn btn-outline-secondary" type="button">✔</button>
                                </div>
                            </div>

                            <input type="hidden" name="fn" value="<?= $t->upload ?>"/>
                            <input type="hidden" name="action" value="upload"/>
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
        $comment =  $(this).parent().find("input[name=comment]");
        $comment.val(fn);
        $comment.select();
    });
</script>
