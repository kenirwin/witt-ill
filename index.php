<?php
if (isset($_REQUEST['view']) && ($_REQUEST['view'] == 'printed')) {
    $view = "Already Printed";
    $link = '<a href="?view=unprinted">Switch to unprinted requests</a>';
    $data = 1;;
}
else {
    $view = "Not Yet Printed";
    $link = '<a href="?view=printed">Switch to printed requests</a>';
    $data = 0;
}
?>

<head>
<style>
body { font-family: Arial, Helvetica, sans-serif }
</style>
<style>
@import "https://cdn.datatables.net/1.10.15/css/jquery.dataTables.min.css";
</style>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
<script type="text/javascript" language="javascript" src="https://cdn.datatables.net/1.10.15/js/jquery.dataTables.min.js"></script>

    <script type="text/javascript" charset="utf-8">
    $(document).ready(function() {
        $('#requests').DataTable( {
                "ajax": {
                    "url": 'server_processing.php',
                     "data": function (d) {
                         d.sPrinted = '<?= $data; ?>';
                     }
                     },
                     "order": [[ 0, "desc"]],
                     "pageLength": 100,
                    } );
$('#requests tbody').on('click', 'tr', function() {
var id = $(this).find(':first-child').text();
$(location).attr('href','view.php?id=' + id);
});
    } );
</script>
</head>

<h1>ILL Pickup Window: <?= $view; ?></h1>
<p><?= $link; ?></p>
<table id="requests" class="display" cellspacing="0" width="100%">
    <thead>
    <th>ID</th>
<th>Request Date</th>
<th>JotForm ID</th>
<th>Material Type</th>
<th>Name</th>
    </thead>
</table>
