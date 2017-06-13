<?
require_once('/docs/lib/include/pdo_connect.php');
$db = ConnectPDO('ill');

$table = "archive";
$handle = @fopen("ill.import", "r");
//$handle = @fopen("4228", "r");
if ($handle) {
    while (($buffer = fgets($handle, 4096)) !== false) {
        ParseAndSubmit($buffer, $db, $table);
    }
    if (!feof($handle)) {
        echo "Error: unexpected fgets() fail\n";
    }
    fclose($handle);
}

function GetFields($db, $table) {
    $fields = $lengths = array();
    $stmt = $db->prepare("SHOW COLUMNS FROM `".$table."`");
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as $row) {
        $fieldname =  $row['Field'];
        array_push($fields, $fieldname);
        if (preg_match('/varchar\((\d+)\)/',$row['Type'], $m)) {
            $lengths[$fieldname] = intval($m[1]);
        }
    }
    return (array($fields, $lengths));
}

function ParseAndSubmit($line, $db, $table) {
    $arr = GetFields($db, $table);
    $fields = $arr[0];
    $lengths = $arr[1];
    list($id,$material_type) = preg_split('/\|/', $line);

    if ($material_type=="book") {
        if ($id >= 8699) {
            list ($id,$material_type, $request_date, $firstname, $lastname, $barcode, $rank, $deptbox, $phone, $username, $available, $author, $title_book, $chapter_title,$chapter_author,$chapter_pages, $date, $edition, $publisher, $diffedition, $worldcat,$oclc,$isbn,$lccn,$eric,$sites,$source) = preg_split('/\|/', $line);
        }
        elseif ($id >= 8695) {
            list ($id,$material_type, $request_date, $firstname, $lastname, $barcode, $rank, $deptbox, $phone, $username, $available, $author, $title_book, $chapter_title,$chapter_pages, $date, $edition, $publisher, $diffedition, $worldcat,$oclc,$isbn,$lccn,$eric,$sites,$source) = preg_split('/\|/', $line);
        }
        else  {
            list ($id,$material_type, $request_date, $firstname, $lastname, $barcode, $rank, $deptbox, $phone, $username, $available, $author, $title_book, $date, $edition, $publisher, $diffedition, $worldcat,$oclc,$isbn,$lccn,$eric,$sites,$source) = preg_split('/\|/', $line);
        }
    }

    elseif ($material_type == "journal") {
        if ($id < 4349) {
            list ($id,$material_type, $request_date, $firstname, $lastname, $barcode, $rank, $deptbox, $phone, $username, $available, $author, $title_article, $title_journal, $volume, $number, $date, $pages, $issn, $source) = preg_split('/\|/', $line);
        }
        else {
            list ($id,$material_type, $request_date, $firstname, $lastname, $barcode, $rank, $deptbox, $phone, $username, $available, $author, $title_article, $title_journal, $volume, $number, $date, $pages, $issn, $source) = preg_split('/\|/', $line);
        }
    }
    
    $name = "$firstname $lastname";
    $email = $username . '@wittenberg.edu';
    if (preg_match('/[0-9]+/',$deptbox)) {
        $campus_box = $deptbox;
    }
    else { $department = $deptbox; } 

    $request_date = date("Y-m-d H:i:s", strtotime($request_date));

    //    print_r($fields);
    $submit_fields = $tokens = $values = array();
    foreach ($fields as $field) { 
        $v = $$field;
        if(isset($v) && ($v != '')) {
            $rows .= "<li>".$field.": ".$v."</li>";
            array_push($submit_fields,"`$field`"); //ill database field name
            array_push($tokens, '?');
            $v = utf8_encode($v);
            if (is_int($lengths[$field])) {
                $v = substr($v,0, $lengths[$field]);
            }
            array_push($values,$v);
        }
    }
    try {
        $query = 'INSERT INTO '.$table.'('. join($submit_fields,',') .') VALUES('. join($tokens, ', ') .')';
        //    print $query;
        $stmt = $db->prepare($query);
        $stmt->execute($values);
        //    print "<table>$rows</table>";
    }
    catch (PDOException $e) {
        print "<p>ERROR: $e->getMessage()</p>";
        print "$rows";
        print_r($e);
        print '<hr>';
    }
    
}