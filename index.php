<?php

require_once __DIR__ . '/vendor/autoload.php';
require_once file_exists(__DIR__ . '/parameters.php') ? __DIR__ . '/parameters.php' : __DIR__ . '/parameters.dumb.php';
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 'On');

use Main\Bankin;
use Main\Main;


//bank
$bank = new Bankin();
//is not logged
if (!isset($_SESSION['email']) && !isset($_SESSION['password'])) {
    header('Location: user.php');
}
//$bank = new \Main\FakeBankConector();

//filesystem
$fileAdapter = new \Main\FileAdapterGoogleDrive(DOCUMENTS_FOLDER_GDRIVE, $_SESSION['access_token']);
$fileAdapter->authenticateIfNeeded();

//$fileAdapter = new \Main\FileAdapterFilesystem(DOCUMENTS_FOLDER);

$main = new Main($fileAdapter);



$action = isset($_REQUEST['action'])?$_REQUEST['action']:'';

$month = $main->selectedMonth($_GET['month']);
$months = $main->listMonths();
if($action === 'upload') {
    $main->handleUpload($month);
} elseif ($action === 'delete') {
    $fileAdapter->remove($_POST['id']);
}


if($action === "positivetransactions") {
    $transactions = $main->filterPositiveTransactions($bank, $month);
} else {
    list($transactions, $orphanFiles) = $main->reconciliation($bank, $month);
}

?>

<?php include "tpl_header.html" ?>

<?php include "tpl_nav.php" ?>

<?php if ($transactions) { ?>
    <table class="table">
        <thead>
        <tr>
            <th>Date</th>
            <th>Description</th>
            <th>Amount</th>
            <th>File</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($transactions as $t) { ?>
            <tr>
                <td style="display: overflow:hidden; white-space: nowrap;"><?= $t->date ?></td>
                <td><?= $t->description ?></td>
                <td class="<?= $t->amount > 0 ? "font-green" : "font-red" ?>"><?= sprintf("%.2f", $t->amount) ?></td>
                <td class="<?= $t->file ? "" : "bg-red" ?>">
                    <?php if ($t->file) { ?>
                        <a target="_blank" href="<?= $t->file->viewlink ?>"><?= $t->file->name ?></a>
                        <form method="post" style="display: inline-block" >
                            <input type="hidden" name="id" value="<?= $t->file->id ?>"/>
                            <button type="submit" name="action" value="delete">
                                <span>&times;</span>
                            </button>
                        </form>
                    <?php } else { ?>
                        <?php if ($t->helplink) { ?>
                            <?php if (\Main\Utils::startsWith($t->helplink,'http')) { ?>
                                <a target="_blank" href="<?= $t->helplink ?>">Help</a>
                            <?php }else{ ?>
                                <button type="button" class="btn btn-primary fetch" data-provider="<?= $t->helplink ?>" data-amount="<?=$t->amount?>" data-date="<?=$t->date?>">🔎 Fetch</button>
                                <select class="fetch" data-provider="<?= $t->helplink ?>"></select>
                            <?php } ?>
                        <?php } ?>
                        <form method="post" enctype="multipart/form-data" class="upload">
                            <input type="file" name="receipt"/><br />
                            <input type="hidden" name="receipt_tmppath"/><br />
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


<textarea cols="50" id="copy-tab" style="display: none">
<?php foreach ($transactions as $t) { ?><?=$t->date."\t".$t->description."\t".$t->amount."\n"?><?php } ?>
</textarea>


<script>
    $('#btn-copy-clipboard').click(function (e) {
        $textarea = $('#copy-tab');
        $textarea.show().select();
        document.execCommand('copy');
        $textarea.hide();
        alert('copied into clipboard');
    });

</script>

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

<script>
    $("button.fetch").click(function () {
        var $btn = $(this);
        var provider = $btn.data('provider');
        $btn.addClass('spinner');

        var params = {
            'id': $btn.data('provider'),
            'amount': $btn.data('amount'),
            'date': $btn.data('date')
        };

        $.getJSON('/fetch.php', params , function (data) {
            $btn.removeClass('spinner');
            var sel = $btn.siblings('select');
            sel.append($("<option>").attr('value', null).text('-- select --'));
            $.each(data, function (k, v) {
                sel.append($("<option>").attr('value', k).text(v));
            });
        });
    });

    $("select.fetch").change(function () {
        var $select = $(this);
        var provider = $select.data('provider');

        $select.addClass("spinner");
        $.getJSON('/fetch.php?id=' + provider + '&invoice=' + this.value, function (data) {
            $select.removeClass('spinner');
            var $form = $select.siblings('form');
            $form.find('input[name=comment]').val(data.fn);
            $form.find('input[name=receipt_tmppath]').val(data.tmppath);
        });
    });
</script>


