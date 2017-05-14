<?php

require('dbconnect.php');
connect_db();

include('getURL.php');
// $url = $_GET['url'];
//        ==nul? 'http://localhost/gapi/AppotaDevelopers.html' : $_GET('url');
//$url = 'http://localhost/gapi/AppotaDevelopers.html';
// $type = $_GET['type'];
$link_title = $_GET['link_title'];
$method = $_GET['method'];
$game_id = $_GET['game_id'];
$store = $_GET['store'];
$start_date = $_GET['start_date'];
$end_date = $_GET['end_date'];
// $cookie = $_GET['cookie'];

$curl = curl_init();

$store_type = 'IAD';
if (getLink($result, $link_title)) {
    if (count($result) > 0) {
        $type = $result[0]['link_type'];
        $url = $result[0]['link_url'];
        $cookie = $result[0]['cookie'];
    } else {
        exit('Cannot Select Store & Report Link!');
    }
} else {
    exit('Cannot Select m_link Data!');
}

// $url = "https://developer.appota.com/ajax/refresh_perpage/0";
$date_s = date_create_from_format('m/j/Y', urldecode($start_date));
$date_e = date_create_from_format('m/j/Y', urldecode($end_date));
$s_date = $date_s->format("Y-m-d");
$e_date = $date_e->format("Y-m-d");
$diff = date_diff($date_s, $date_e);
$increment = $diff->format("%R%a");
if ($increment < 0) {
    echo_result($json, -3, 'Error Start Date > End Date! ' . $s_date . " > " . $e_date);
    exit();
}
if ($method == 2) {
    // Load Save Data Daily
    // At this Point Set Maximum execution time to Over Limited of 120 seconds
    ini_set('max_execution_time', 300); // 300s = 5minute

    $html = getUrlAppota($cookie, $url, $start_date, $start_date, $curl, $type);
    $d = new DOMDocument();
    @$d->loadHTML($html);
    $x = new DOMXPath($d);
    $output = array();
    $pageCount = switch_type($type, $x, $output);
    if ($pageCount > 1) {
        getUrlRefresh($cookie, $curl, $type);
        $html = getUrlAppota($cookie, $url, $start_date, $start_date, $curl, $type);
    }
    $isIntOK = retrieve_data($method, $type, $game_id, $store, $html, $start_date);
    for ($i = 1; $i <= $increment; $i++) {
        if (!$isIntOK || $isIntOK == -1){
            exit('Error Retrieve Data');
        }else if ($isIntOK && $i < $increment) {
            sleep(30);
        }/* else if ($isIntOK == 2){
            // No Data
            continue;
        } */
        date_add($date_s, date_interval_create_from_date_string("1 day"));
        $s_date = urlencode($date_s->format("m/d/Y"));
        $html = getUrlAppota($cookie, $url, $s_date, $s_date, $curl, $type);
        $isIntOK = retrieve_data($method, $type, $game_id, $store, $html, $s_date);
    }
} else {
    $html = getUrlAppota($cookie, $url, $start_date, $end_date, $curl, $type);
    echo $html;
    $d = new DOMDocument();
    @$d->loadHTML($html);
    $x = new DOMXPath($d);
    $output = array();
    $pageCount = switch_type($type, $x, $output);
    if ($pageCount > 1) {
        getUrlRefresh($cookie, $curl, $type);
        $html = getUrlAppota($cookie, $url, $start_date, $end_date, $curl, $type);
    }
    retrieve_data($method, $type, $game_id, $store, $html, $start_date);
}
/* curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  // curl_setopt($ch, CURLOPT_URL, 'http://localhost/gapi/AppotaDevelopers.html');
  curl_setopt($ch, CURLOPT_URL, $url);
  $res = curl_exec($ch);

  if ($res === false) {
  die('error: ' . curl_error($ch));
  }

  curl_close($ch); */

function retrieve_data($method, $type, $game_id, $store, $html, $start_date) {
    $d = new DOMDocument();
    @$d->loadHTML($html);
    $x = new DOMXPath($d);
    $output = array();
    switch_type($type, $x, $output);
    if(count($output) > 0){
        // SAVE DOWNLOAD DATA
        if ($method == 1) {
            if ($type >= 2) {
                $date = urldecode($start_date);
                return save_data_download($game_id, $store, $date, $output);
            } else {
                return save_all_data($game_id, $store, $date, $output);
            }
        } else
        // LOAD DATA
        if ($method == 0) {
            $date = urldecode($start_date);
            load_data($game_id, $store, $date, $output, $type);
        } else
        // SAVE EARNING DATA
        if ($method == 3) {
            if ($type == 1) {
                $date = urldecode($start_date);
                load_data($game_id, $store, $date, $output, $type);
                return save_data_earning($game_id, $store, $date, $output);
            } else {
                
            }
        } else
        // SAVE EARNING DATA DAILY
        if ($method == 2) {
            if ($type == 1) {
                $date = urldecode($start_date);
                load_data($game_id, $store, $date, $output, $type);
                return save_data_earning($game_id, $store, $date, $output);
            } else {
                
            }
        }
    }else{
        // No Data
        return 2;
    }
}

function switch_type($type, $x, &$output) {
    $output = null;
    $name = null;
    if ($type >= 2) {
        $articles = $x->query('//div[@id="country1"]');
        $totalCount = 0;
        foreach ($articles as $container) {
            $arr = $container->getElementsByTagName("a");
            foreach ($arr as $item) {
                $totalCount++;
                if (strlen($item->nodeValue) > 1)
                    $name[] = trim($item->nodeValue);
            }
        }
        $temp = $x->query("//td[@class = 'col-1']");
        $k = 0;
        for ($i = 0; $i < count($name); $i++) {
            $trend = trim(str_replace("%", '', $temp->item(3 * $i + 2)->textContent));
            if ($temp->item(3 * $i + 1)->textContent == 0 && ($trend == 0 || $trend == -100)) {
                continue;
            }
            $output[$k]['name'] = $name[$i];
            $output[$k]['os'] = $temp->item(3 * $i)->textContent;
            $output[$k]['download_num'] = $temp->item(3 * $i + 1)->textContent;
            $output[$k]['trend'] = $temp->item(3 * $i + 2)->textContent;
            $k++;
        }
        return ($totalCount - count($name));
    } else if ($type == 0) {
        $report_content = $x->query('//div[@id="country2"]');
        $totalCount = 0;
        foreach ($report_content as $container) {
            $arr = $container->getElementsByTagName("a");
            foreach ($arr as $item) {
                $totalCount++;
                if (strlen($item->nodeValue) > 1)
                    $name[] = trim($item->nodeValue);
            }
        }
        // echo count($name);
        $temp = $x->query("//td[@class = 'col-1']");
        $tempOs = $x->query("//td[@class = 'col-0']");
        $k = 0;
        for ($i = 0; $i < count($name); $i++) {
            if ($temp->item(4 * $i)->textContent == 0) {
                continue;
            }
            $output[$k]['name'] = $name[$i];
            $output[$k]['os'] = $tempOs->item(2 * $i + 1)->textContent;
            $output[$k]['download_num'] = $temp->item(4 * $i)->textContent;
            $output[$k]['buy_num'] = $temp->item(4 * $i + 1)->textContent;
            $output[$k]['money_tym'] = $temp->item(4 * $i + 2)->textContent;
            $output[$k]['money_vnd'] = $temp->item(4 * $i + 3)->textContent;
            $k++;
        }
        // for ($i = 0; $i
    } else if ($type == 1) {
        $report_content = $x->query('//div[@id="country3"]');
        $totalCount = 0;
        foreach ($report_content as $container) {
            $arr = $container->getElementsByTagName("a");
            foreach ($arr as $item) {
                $totalCount++;
                if (strlen($item->nodeValue) > 1)
                    $name[] = trim($item->nodeValue);
            }
        }
        // echo count($name);
        $temp = $x->query("//td[@class = 'col-1']");
        $tempOs = $x->query("//td[@class = 'col-0']");
        $k = 0;
        for ($i = 0; $i < count($name); $i++) {
            if ($temp->item(3 * $i + 1)->textContent == 0 && $temp->item(3 * $i + 2)->textContent == 0) {
                continue;
            }
            $output[$k]['name'] = $name[$i];
            $output[$k]['os'] = $tempOs->item(2 * $i + 1)->textContent;
            $output[$k]['sales_type'] = $temp->item(3 * $i)->textContent;
            $output[$k]['money_vnd'] = $temp->item(3 * $i + 1)->textContent;
            $output[$k]['money_usd'] = $temp->item(3 * $i + 2)->textContent;
            $k++;
        }
        // for ($i = 0; $i < count($output); $i++) {
        // }
        return ($totalCount - count($name));
    }
}

function load_data($game_id, $store, $date, $output, $type = 1) {
    if (count($output) > 0) {
		$row = 1;
        echo '<table width="600px" class="tbForm" border="1" cellspacing="0" cellpadding="0">';
        echo '<tbody>';
        echo '<tr>';
        echo '<td valign="center" align="center" width="30px">STT</td>';
        echo '<td valign="center" width="100px" align="center" class = "col-' . $row++ . '">Date</td>';
        echo '<td valign="center" width="200px" align="center" class = "col-' . $row++ . '">Name</td>';
        echo '<th valign="center" width="90px"  scope="col" class = "col-' . $row++ . '">OS</th>';
        if ($type == 1) {
            echo '<th valign="center" width="40px" scope="col" class = "col-' . $row++ . '">Type</th>';
            echo '<th valign="center" width="120px" scope="col" class = "col-' . $row++ . '">VND</th>';
            echo '<th valign="center" width="50px" scope="col" class = "col-' . $row++ . '">USD</th>';
        } else if ($type == 0) {
            echo '<th valign="center" width="100px" scope="col" class = "col-' . $row++ . '">Số lượt tải</th>';
            echo '<th valign="center" width="10px" scope="col" class = "col-' . $row++ . '">Số lượt mua</th>';
            echo '<th valign="center" width="40px" scope="col" class = "col-' . $row++ . '">Doanh thu (TYM)</th>';
            echo '<th valign="center" width="60px" scope="col" class = "col-' . $row++ . '">Doanh thu (VND)</th>';
        } else if ($type == 2) {
            echo '<th valign="center" width="100px" scope="col" class = "col-' . $row++ . '">Số lượt tải</th>';
            echo '<th valign="center" width="100px" scope="col" class = "col-' . $row++ . '">Xu hướng</th>';
        }
        echo '</tr>';
        $j = 1;
        for ($i = 0; $i < count($output); $i++) {
            $row = 1;
			echo '<tr>';
            echo '<td valign="center" align="center">' . $j++ . '</td>';
            echo '<td valign="center" align="center" class = "col-' . $row++ . '">' . $date . '</td>';
            foreach ($output[$i] as $value) {
                echo '<td valign="center" align="center" class = "col-' . $row++ . '">' . $value . '</td>';
            }
            echo '</tr>';
        }
        echo '</tbody>';
        echo '</table>';
    }
}

// function save_data_download($game_id, $store, $name, $date, $download_num, $os, $trend ) {
function save_data_download($game_id, $store, $date, $output) {
    if (count($output) > 0) {
        try {
            $prefix_query = "INSERT IGNORE INTO appota_download (" .
                    "game_id," .
                    "name," .
                    "date," .
                    "store," .
                    "os," .
                    "download_number," .
                    "trend" .
                    ") values (";
            $date = explode('/', $date);
            // global $connect;
            for ($i = 0; $i < count($output); $i++) {
                $insert_query = $prefix_query .
                        "'" . $game_id . "', " .
                        "'" . $output[$i]['name'] . "', " .
                        "'" . $date[2] . "-" . $date[0] . "-" . $date[1] . "', " .
                        "'" . $store . "', " .
                        "'" . $output[$i]['os'] . "', " .
                        "'" . str_replace(",", "", $output[$i]['download_num']) . "', " .
                        "'" . $output[$i]['trend'] . "')";
                // $result = mysqli_query($connect, $insert_query);
                // or die(mysqli_error($connect));
                $result = query($insert_query, $error);
            }
            // echo "OK: " . $insert_query . "</br>";
            if ($result)
                echo "OK";
            else
                echo $error['error'];
            return $result;
        } catch (ErrorException $e) {
            echo $e;
            return -1;
        }
    } else {
        return 2;
    }
}

// function save_data_earning($game_id, $store, $name, $date, $os, $sales_type, $money_vnd, $money_usd) {
function save_data_earning($game_id, $store, $date, $output) {
    if (count($output) > 0) {
        try {
            $date = explode('/', $date);
            $prefix_query = "INSERT IGNORE INTO appota_earning_daily (" .
                    "store," .
                    "game_id," .
                    "name," .
                    "date," .
                    "os," .
                    "sales_type," .
                    "money_vnd," .
                    "money_usd" .
                    ") values (";
            // global $connect;
            for ($i = 0; $i < count($output); $i++) {
                $insert_query = $prefix_query .
                        "'" . $store . "', " .
                        "'" . $game_id . "', " .
                        "'" . $output[$i]['name'] . "', " .
                        "'" . $date[2] . "-" . $date[0] . "-" . $date[1] . "', " .
                        "'" . $output[$i]['os'] . "', " .
                        "'" . $output[$i]['sales_type'] . "', " .
                        "'" . str_replace(",", "", $output[$i]['money_vnd']) . "', " .
                        "'" . str_replace(",", "", $output[$i]['money_usd']) . "')";
                // $result = mysqli_query($connect, $insert_query);
                // or die(mysqli_error($connect));
                $result = query($insert_query, $error);
            }
            // echo "OK: " . $insert_query . "</br>";
            if ($result)
                echo "OK";
            else
                echo $error['error'];
            return $result;
        } catch (ErrorException $e) {
            echo $e;
            return -1;
        }
    } else {
        return 2;
    }
}

function save_all_data($game_id, $store, $date, $output) {
    if (count($output) > 0) {
        try {
            // global $connect;
            $date = explode('/', $date);
            $prefix_query = "INSERT IGNORE INTO appota_download (" .
                    "game_id," .
                    "date," .
                    "store," .
                    "download_number" .
                    ") values (";
            for ($i = 0; $i < count($output); $i++) {
                $insert_query = $prefix_query .
                        "'" . $game_id . "', " .
                        "'" . $date[2] . "-" . $date[0] . "-" . $date[1] . "', " .
                        "'" . $store . "', " .
                        "'" . str_replace(",", "", $output[$i]['download_num']) . "')";
                // $result = mysqli_query($connect, $insert_query);
                // or die(mysqli_error($connect));
                $result = query($insert_query, $error);
            }
            if ($result)
                echo "OK";
            else
                echo $error['error'];
            return $result;
        } catch (ErrorException $e) {
            echo $e;
            return -1;
        }
    } else {
        return 2;
    }
}
?>
   
