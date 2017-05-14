<?php

include('dbconnect.php');
try {
    $output = json_decode($_GET['output'], true);
//    $output = $_POST['output'];
//str_replace($output, $replace, $subject);
//    print_r($output);

    $gameId = $_GET['game_id'];
    $storeId = $_GET['store'];
//let's create the query
    for ($i = 0; $i < count($output); $i++) {
        $date = explode('/', $output[$i]['date']);
        $insert_query = "insert into appota_download (" .
                "game_id," .
                "date," .
                "store," .
                "download_number" .
                ") values (" .
                "'" . $gameId . "', " .
                "'" . $date[2] . "-" . $date[1] . "-" . $date[0] . "', " .
                "'" . $storeId . "', " .
                "'" . str_replace(",", "", $output[$i]['download_num']) . "')";
//        "'" . str_replace(",", "", $_GET['screenView']) . "'," .
//        "'" . str_replace(",", "", $_GET['screenSession']) . "'," .
//        "'" . str_replace(",", "", $_GET['averageSessionTime']) . "'," .
//        "'" . str_replace(",", "", $_GET['newSessionRate']) . "' )";
//let's run the query
        $result = mysql_query($insert_query) or die(mysql_error());
    }
    echo "OK <a href = 'index.php</a>";
} catch (ErrorException $e) {
    echo $e;
}
?>
