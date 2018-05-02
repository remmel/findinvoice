<?php

require_once __DIR__ . '/vendor/autoload.php';
if(file_exists(__DIR__ . '/parameters.php')) require_once __DIR__ . '/parameters.php';
else require_once __DIR__ . '/parameters.dumb.php';


use Main\Bankin;
use Main\Main;

$bankin = new Bankin();
$main = new Main();

$accounts = $bankin->accounts();
$accountId = $_GET['accountId'];

$start    = (new DateTime('2015-01-01'))->modify('first day of this month');
$currentMonth      = (new DateTime())->modify('first day of this month');
$interval = DateInterval::createFromDateString('1 month');

$queryMonth = $_GET['month'];
$month = $queryMonth ? new DateTime($queryMonth) : $currentMonth;
$months = new DatePeriod($start, $interval, $currentMonth);

$main->handleUpload($month);

if ($accountId) {
    $transactions = Main::reconciliation($bankin, $accountId, $month);
}

?>

<form method="get">
    <select name="accountId" onchange="this.form.submit()">
        <option value="">- select account-</option>
        <?php foreach ($accounts as $a) { ?>

            <option value="<?= $a->id ?>" <?= $a->id == $accountId ? 'selected' : '' ?>>
                #<?= $a->id ?> <?= $a->name ?> <?= $a->balance ?> <?= $a->currency_code ?>
            </option>
        <?php } ?>
    </select>

    <select name="month" onchange="this.form.submit()">
        <?php foreach ($months as $m) { ?>
            <option value="<?= $m->format('Y-m-d') ?>" <?= $m->format('Y-m-d') == $month->format('Y-m-d') ? 'selected' : '' ?>>
                <?= $m->format('Y-m-d') ?>
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
                    <td><?=$t->id?></td>
                    <td><?=$t->date?></td>
                    <td><?=$t->description?></td>
                    <td bgcolor="<?=$t->amount>0?"green":"red"?>"><?=$t->amount?></td>
                    <td><?=$t->currency?></td>
                    <td><?=$t->upload?></td>
                    <td>
                        <?php if ($t->doc) { ?>
                            <a target="_blank" href="<?= $t->doclink ?>"><?= $t->doc ?></a>
                        <?php } else { ?>
                            <form method="post" enctype="multipart/form-data">
                                <input type="file" name="receipt" />
                                <input type="hidden" name="fn" value="<?=$t->upload?>" />
                                <input type="text" name="comment" value="" />
                                <input type="submit" name="submit" />
                            </form>
                        <?php } ?>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
<?php } ?>

