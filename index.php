<?php

require_once __DIR__ . '/vendor/autoload.php';
if (file_exists(__DIR__ . '/parameters.php')) require_once __DIR__ . '/parameters.php';
else require_once __DIR__ . '/parameters.dumb.php';
session_start();

use Main\Bankin;
use Main\Main;

$bankin = new Bankin();
$main = new Main();


//has account
if (!isset($_SESSION['email']) && !isset($_SESSION['password'])) {
    header('Location: user.php');
}

$month = $main->selectedMonth($_GET['month']);
$months = $main->listMonths();

$main->handleUpload($month);

$transactions = Main::reconciliation($bankin, $month);

?>

<script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>

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
    <table border="1">
        <thead>
        <td>id</td>
        <td>date</td>
        <td>description</td>
        <td>amount</td>
        <td>currency_code</td>
        <td>upload</td>
        <td>doc</td>
        </thead>
        <tbody>
        <?php foreach ($transactions as $t) { ?>
            <tr>
                <td><?= $t->id ?></td>
                <td><?= $t->date ?></td>
                <td><?= $t->description ?></td>
                <td bgcolor="<?= $t->amount > 0 ? "green" : "red" ?>"><?= $t->amount ?></td>
                <td><?= $t->currency ?></td>
                <td><?= $t->upload ?></td>
                <td>
                    <?php if ($t->doc) { ?>
                        <a target="_blank" href="<?= $t->doclink ?>"><?= $t->doc ?></a>
                    <?php } else { ?>
                        <form method="post" enctype="multipart/form-data" class="upload">
                            <input type="file" name="receipt"/>
                            <input type="hidden" name="fn" value="<?= $t->upload ?>"/>
                            <input type="text" name="comment" value=""/>
                            <input type="submit" name="submit"/>
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
