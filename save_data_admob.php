<?php

require('dbconnect.php');
connect_db();

include('getURL.php');
include('download_helper.php');
// $url = $_GET['url'];
//        ==nul? 'http://localhost/gapi/AppotaDevelopers.html' : $_GET('url');
//$url = 'http://localhost/gapi/AppotaDevelopers.html';
// $url = 'http://localhost/bao_cao/AdMob_7d.html';
// $url = 'https://apps.admob.com/home/service/dashboard';
$url = 'https://apps.admob.com/monetize-reports';
// $type = $_GET['type'];
$method = $_GET['method'];
// $game_id = $_GET['game_id'];
// $store = $_GET['store'];
$start_date = $_GET['start_date'];
$end_date = $_GET['end_date'];

$curl = curl_init();
$store_type = 'Admob';
if (getLink($result, $store_type)) {
    if (count($result) > 0) {
        // $type = $result[0]['link_type'];
        // $url = $result[0]['link_url'];
        $cookie = $result[0]['cookie'];
    } else {
        exit('Cannot Select Store & Report Link!');
    }
} else {
    exit('Cannot Select m_link Data!');
}

$date_s = date_create_from_format('Y/m/j', $start_date);
$date_e = date_create_from_format('Y/m/j', $end_date);
$s_date = $date_s->format("Ymd");
$e_date = $date_e->format("Ymd");
$diff = date_diff($date_s, $date_e);
$increment = $diff->format("%R%a");
if ($increment < 0) {
    echo_result($json, -3, 'Error Start Date > End Date! ' . $s_date . " > " . $e_date);
    exit();
}
if ($method == 3) {
    // echo $s_date.$e_date;
    $jsonUsr = getGAInfo($s_date, $e_date, $curl);
    // var_dump($jsonUsr);
    $jsoninfo = json_decode($jsonUsr, true);
    $json = $jsoninfo['components'][1]['sparkline']['metricGroup'];
    echo json_encode($json);
} else {

    // $request = new Yaf_Request_Http();
    // $data = getAdmobReport($cookie, $url, $s_date, $e_date, $curl);
    $data = getAdmobwithLogin($cookie, $url, $s_date, $e_date, $curl);
    // $cookie_dat = $request->getCookie('ADMOB');
    // var_dump($cookie_dat);
    // mb_language("Japanese");
    // mb_internal_encoding("EUC-JP");
    // mb_detect_order("ASCII,JIS,UTF-8,EUC-JP,SJIS");
    // $data = mb_convert_encoding($csvData, "UTF-8", mb_detect_encoding($csvData));
    $data = mb_convert_encoding($data, "UTF-8", 'UTF-16LE');
    // echo str_replace(array('?','??'),'', $data);
    // echo $data;
    // $mime = "Content-Type: text/csv";
    // force_download("admob.csv", $data, 'text/csv; charset=utf-16le');
    // force_download("admob.csv", $data, 'application/csv');
    if ($method == 2) {
        force_download("admob.csv", $data);
        // echo "\xEF\xBB\xBF";
        // echo '<html><head><meta http-equiv="Content-Type" content="text/csv; charset=utf-16le"/></html></head>'.$csvData;
    } else {
        // retrieve_data($method, $game_id, $store, $html, $start_date, $end_date);
        retrieve_data($method, $data);
    }
}

function retrieve_data($method, $csv) {
    if ($method == 0) {
        //explode all separate lines into an array
        $data = explode("\n", $csv);

        //trim all lines contained in the array.
        $data = array_filter($data, 'trim');

        //loop through the lines
        echo '<table  class="tbForm" border="1" cellspacing="0" cellpadding="5">';
        echo '<tbody>';
        $j = -1;
        foreach ($data as $line) {
            $j++;
            $item = explode("\t", $line);
			$row = 1;
            if ($j == 0) {
                echo '<tr>';
                echo '<th valign="center" width="50px" align="center" class = "col-0">' . "No." . '</th>';
                foreach ($item as $value) {
                    echo '<th valign="center" align="center" class = "col-' . $row++ . '">' . $value . '</th>';
                }
                echo '</tr>';
            } else {
                echo '<tr>';
                echo '<td valign="center" align="center" class = "col-0">' . $j . '</td>';
                foreach ($item as $value) {
                    echo '<td valign="center" align="center" class = "col-' . $row ++ . '">' . $value . '</td>';
                }
                echo '</tr>';
            }
        }
        echo '</tbody>';
        echo '</table>';
    } else if ($method == 1) {
        $data = explode("\n", $csv);

        //trim all lines contained in the array.
        $data = array_filter($data, 'trim');
        if (count($data) > 1) {
            $count = count(explode("\t", $data[0]));
            for ($i = 1; $i < count($data); $i++) {
                $item = explode("\t", $data[$i]);
                $j = 0;
                $k = $i - 1;
                $output[$k]['ad_date'] = $item[$j++];
                $output[$k]['ad_app_name'] = $item[$j++];
                $output[$k]['ad_os'] = $item[$j++];
                $output[$k]['ad_country'] = $item[$j++];
                $output[$k]['ad_requests'] = $item[$j++];
                $output[$k]['ad_impressions'] = $item[$j++];
                $output[$k]['ad_fill_rate'] = $item[$j++];
                $output[$k]['ad_clicks'] = $item[$j++];
                $output[$k]['ad_impression_ctr'] = $item[$j++];
                $output[$k]['ad_requests_rpm'] = $item[$j++];
                $output[$k]['ad_impression_rpm'] = $item[$j++];
                $output[$k]['ad_earnings'] = $item[$j++];
            }
            return save_all_data($output);
        } else {
            echo "No Data!";
            return 2;
        }
    }
}

function save_all_data($output) {
    if (count($output) > 0) {
        try {
            global $connect;
            $prefix_query = "INSERT IGNORE INTO admob_app_report_daily (ad_date, ad_app_name, ad_os, ad_country, ad_requests, ad_impressions, ad_fill_rate, ad_clicks, ad_impression_ctr, ad_requests_rpm, ad_impression_rpm, ad_earnings)
            VALUES ('";
            for ($i = 0; $i < count($output); $i++) {
                $insert_query = $prefix_query .
                        $output[$i]['ad_date'] . "','" .
                        $output[$i]['ad_app_name'] . "','" .
                        $output[$i]['ad_os'] . "','" .
                        $output[$i]['ad_country'] . "','" .
                        $output[$i]['ad_requests'] . "','" .
                        $output[$i]['ad_impressions'] . "','" .
                        $output[$i]['ad_fill_rate'] . "','" .
                        $output[$i]['ad_clicks'] . "','" .
                        $output[$i]['ad_impression_ctr'] . "','" .
                        $output[$i]['ad_requests_rpm'] . "','" .
                        $output[$i]['ad_impression_rpm'] . "','" .
                        $output[$i]['ad_earnings'] . "')";
                // $result = mysqli_query($connect, $insert_query) or die(mysqli_error($connect));
                $result = query($insert_query, $error);
            }
            if ($result)
                echo "OK";
            else
                echo $error['error'];
            return $result;
        } catch (ErrorException $e) {
            echo $e;
        }
    } else {
        return 2;
    }
}

//        $date = explode('/', $output[$i]['date']);
//        echo '<tr>' . $output[$i]['date'] . "=>" . $output[$i]['download_num'] . ";" . PHP_EOL;
//        echo '<tr>' . $output[$i]['date'] . "=>" . $output[$i]['download_num'] . ";" . PHP_EOL;
?>
    