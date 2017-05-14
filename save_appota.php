<?php

include('dbconnect.php');
$gameId = $_GET['game_id'];
$storeId = $_GET['store'];
//let's create the query
try {
$date = explode('/', $_GET['date']);
$insert_query = "insert into appota_download (" .
        "game_id," .
        "date," .
        "store," .
        "download_number" .
        ") values (" .
        "'" . $_GET['game_id'] . "', " .
        "'" . $date[2] . "-" . $date[1] . "-" . $date[0] . "', " .
        "'" . $_GET['store'] . "', " .
        "'" . str_replace(",", "", $_GET['download_num']) . "')";
//        "'" . str_replace(",", "", $_GET['screenView']) . "'," .
//        "'" . str_replace(",", "", $_GET['screenSession']) . "'," .
//        "'" . str_replace(",", "", $_GET['averageSessionTime']) . "'," .
//        "'" . str_replace(",", "", $_GET['newSessionRate']) . "' )";
//let's run the query
$result = mysql_query($insert_query) or die(mysql_error());
//if (mysql_num_rows($result) > 0)
echo "OK <a href = 'index.php</a>";
} catch (ErrorException $e) {
    echo $e;
}
?>
