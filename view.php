<?php
error_reporting(E_ALL); ini_set('display_errors', 1); 
require_once('/docs/lib/include/pdo_connect.php');
$db = ConnectPDO('ill');

if (isset($_REQUEST['status']) && $_REQUEST['status'] == "done_printing" && isset($_REQUEST['id'])) {
    try {
        $stmt = $db->prepare('UPDATE requests SET printed = 1 WHERE id=?');
        $stmt->execute(array($_REQUEST['id']));
    }
    catch(PDOException $e) {
        echo $e->getMessage();
    }
}

if (isset($_REQUEST['id'])) {
    $stmt = $db->prepare('SELECT * FROM requests WHERE id=?');
    $stmt->execute(array($_REQUEST['id']));
    $data = $stmt->fetch(PDO::FETCH_ASSOC);
}
foreach ($data as $k=>$v) {
    $data[$k] = preg_replace('/~nl~/','<br>',$v);
    $data[$k] = urldecode($v);
}

//print_r($data);
extract($data);


if ($material_type=="book") {
    $view_type = "Book";
    $form = BookForm($data);
}

elseif ($material_type == "chapter") {
    $view_type = "Book Chapter";
    $form = BookForm($data,"chapter");
}

elseif ($material_type== "article"|| $material_type="journal") {
    $view_type = "Journal Article";
    $form = ArticleForm($data);
}
?>
<head>
<style>
table#top { width: 100% }
td#header { vertical-align: top }
td#staff_use { text-align: left; float:right } 
</style>
</head>
<body><table id="top">
<tr>
                      <td id="header"><h1><?= $view_type; ?> Request  &num;<?= $id; ?></h1>
<?
$linkback = 'Return to: <a href="index.php">unprinted requests</a> or <a href="index.php?view=printed">already-printed requests</a>';
if ($printed == 1) {
    print '<p>Records show that this form has already been printed. <br>'.$linkback.'</p>'.PHP_EOL;
}
else {
    print '<p><a href="?status=done_printing&id='.$id.'">Printed: Remove from queue.</a><br>'.$linkback.'</p>'.PHP_EOL;
}
?>
</td><td id="staff_use"><?= StaffUse($material_type); ?></td><tr></table>
<?= PersonData($data); ?>
<hr>
<?= $form; ?>
<?

function StaffUse($mat_type) {
    $staff_use = <<<END
IL:&nbsp;&nbsp;_________________<P align = "right">
Lenders______,______,______,______,______<P align = "right">
Date Received:&nbsp;&nbsp;______ from______ <P align = "right">
END;
    if ($mat_type == "book") {
        $staff_use .= "Due Date: _________________";
    }
    return $staff_use;
}


function PersonData($data) {
    extract($data);
    $request_date = date("M d, Y", strtotime($request_date));
    return <<<END
<table>
    <tr><td>Date:</strong> $request_date</strong></td></tr>
    <tr><td>Name:<strong> $name</strong> </td/></tr>
<tr><td>Affil:<strong> $rank</strong></td>
    <td>Dept:<strong> $department</strong></td></tr>
<tr><td>Phone:<strong> $phone</strong></td>
    <td>Box:<strong> $campus_box</strong></td>
    <td>Username:<strong> <a href=\"mailto:$email\">$email</a></strong></td></tr>
</table>
END;
}

function BookForm($data, $chapter=""){ 
    extract($data);
    if ($chapter == "chapter") {
        $chapter_info = "<tr><td>Chapter: <strong>$chapter_title (pp. $chapter_pages)</strong></td></tr>\n";
        $chapter_info.= "<tr><td>Chapter Author: <strong>$chapter_author</strong></td></tr>";
    }
    else { $chapter_info = ''; }

return <<<END
Checked OhioLINK? <strong>$available</strong> <BR>
<P>

<table cellpadding=3 cellspacing=3 border=0>
<tr><td>Author:<strong>$author</strong></td/></tr>
<tr><td>Title:<strong>$title_book</strong></td></tr>
$chapter_info
<tr><td colspan=3>
   <table cellpadding=10><tr>
    <td>Date:<strong> $date</strong> </td>
    <td>Edition:<strong> $edition</strong></td>
    <td>Publisher:<strong> $publisher</strong></td></tr>
   </table>
</td>
<tr><td>Other Edition OK?<strong> $diffedition</strong> </td></tr></table>

<P>
WORLDCAT #:<strong> $worldcat</strong><BR>
OCLC #:<strong> $oclc</strong><BR>
ISBN:<strong> $isbn</strong><BR>
LCCN:<strong> $lccn</strong> <BR>
ED#:<strong> $eric</strong>

<P>
Where this book can be found?<BR><strong> $sites</strong>
<P>
From what source did you learn about this book?<BR><strong> $source</strong>
</body>
</html>
END;
}

function ArticleForm($data) {
    extract($data);
    return <<<END
<table cellpadding=3 cellspacing=3 border=0>
<tr><td>Verified unavailablity in print or online: <strong>$available</strong></td>

<tr><td>Author of Article: <strong>$author</strong></td>
<tr><td>Title of Article: <strong>$title_article</strong></td>
<tr><td>Journal: <strong>$title_journal</strong></td></tr>
</table>

<table width=100% cellpadding=3 cellspacing=3 border=0>
<tr><td>Vol: <strong> $volume</strong></td>
    <td>Number: <strong>$number</strong></td>
    <td>Date: <strong> $date</strong></td>
    <td>Pages: <strong>$pages</strong></td></tr>
<tr><td>ISSN: <strong>$issn</strong></td></tr>
</table>


<P>
From what source did you learn about this article?<strong><BR>
$source</strong>
END;
}
?>
