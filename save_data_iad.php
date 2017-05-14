<?php

require('dbconnect.php');
connect_db();
include('getURL.php');
include('download_helper.php');
// $url = $_GET['url'];
//        ==nul? 'http://localhost/gapi/AppotaDevelopers.html' : $_GET('url');
// $url = 'https://apps.admob.com/monetize-reports';
$method = $_GET['method'];
$dataType = $_GET['dataType'];
// $cookie = $_GET['cookie'];
// $game_id = $_GET['game_id'];
// $store = $_GET['store'];
$start_date = $_GET['start_date'];
$end_date = $_GET['end_date'];
$curl = curl_init();
$date_s = date_create_from_format('Y/m/j', $start_date);
$date_e = date_create_from_format('Y/m/j', $end_date);
$s_date = $date_s->format("m/d/y");
$e_date = $date_e->format("m/d/y");
$diff = date_diff($date_s, $date_e);
$increment = $diff->format("%R%a");

if ($increment < 0) {
    echo_result($json, -3, 'Error Start Date > End Date! ' . $s_date . " > " . $e_date);
    exit();
}
$dateRange = 'oneDay';
$store_type = 'IAD';
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

if ($increment > 0) {
    $dateRange = 'customDateRange';
}
if ($dataType == '') {
    $dataType = 'byName';
}
if ($method == 3) {
    // Load Save Data Daily
    // At this Point Set Maximum execution time to Over Limited of 120 seconds
    ini_set('max_execution_time', 300);
    // set_time_limit(300);
    // ini_set('max_execution_time', 0); //no limit 
    $dateRange = 'oneDay';
    for ($i = 0; $i <= $increment; $i++) {
        $s_date = $date_s->format("m/d/y");
        /* $result = getIAdReport($cookie, $s_date, $s_date, $curl, $dateRange, $dataType);
        $data = $result[0];
        $info = $result[1];
        if($info['http_code'] == 403){
            exit ("Access Forbidden");
        } */
        
        $data = getIADwithLogin($cookie, $s_date, $s_date, $curl, $dateRange, $dataType);
        
        $data = mb_convert_encoding($data, "UTF-8", 'UTF-16LE');
        $data = str_replace('","', "\t", $data);
        $data = str_replace('"', '', $data);
        $isIntOK = retrieve_data(1, $data, $date_s->format("Y/m/d"), '', $dataType);
        date_add($date_s, date_interval_create_from_date_string("1 day"));
        if (!$isIntOK || $isIntOK == -1){
            exit('Error Retrieve Data');
        }else if ($isIntOK && $i < $increment) {
            sleep(30);
        }else if ($isIntOK == 2){
        // No Data
            continue;
        }
    }
} else {
    // 03/31/15&toDate=04/09/15`x
	/* $json = file_get_contents('cookie/iad_cookie.json');
	$cookie_array = json_decode($json, true);
	$cookie = '';
	foreach ($cookie_array as $value){
		$cookie .= $value['name'] . '=' . $value['value'] . ';';
	} */
    $data = getIADwithLogin($cookie, $s_date, $e_date, $curl, $dateRange, $dataType);
    // echo $data;
    // echo "<pre>"; print_r($data);
    // var_dump($data);
    // $data[]
    // var_dump($info);
    // $http_header = explode("\n", $data);
    // var_dump($http_header);
    $data = mb_convert_encoding($data, "UTF-8", 'UTF-16LE');
    $data = str_replace('","', "\t", $data);
    $data = str_replace('"', '', $data);
    // echo $s_date . '<br>' . $e_date . '<br>' . $dateRange . '<br>' . $dataType . '<br>' . $cookie;
    // $data = trim($data,'"'); 
    if ($method == 2) {
        force_download("iad_report.csv", $data);
        // echo "\xEF\xBB\xBF";
        // echo '<html><head><meta http-equiv="Content-Type" content="text/csv; charset=utf-16le"/></html></head>'.$csvData;
    } else {
        // retrieve_data($method, $game_id, $store, $html, $start_date, $end_date);
        retrieve_data($method, $data, $start_date, $end_date, $dataType);
    }
}
/* function setCookiesUrl($cookie_array){
      // bool setcookie ( string $name [, string $value [, int $expirationDate = 0 [, string $path [, string $domain [, bool $secure = false [, bool $httpOnly = false ]]]]]] )
      for($i = 0; $i < count($cookie_array); $i++){
        // $expirationDate = isset($cookie_array[$i]['expirationDate']) ? $cookie_array[$i]['expirationDate'] : '';
        // if($expirationDate !== '')
        // echo time() .' : ' .  MONTH_TIME_SECOND;
        setcookie ($cookie_array[$i]['name'], $cookie_array[$i]['value'], time() + 3600, $cookie_array[$i]['path'], $cookie_array[$i]['domain'], $cookie_array[$i]['secure'], $cookie_array[$i]['httpOnly']); 
        // echo $cookie_array[$i]['name'] . ', ' . $cookie_array[$i]['value'] ;
        // . ', ' . time() + MONTH_TIME_SECOND . ', ' . $cookie_array[$i]['path'] . ', ' . $cookie_array[$i]['domain'] . ', ' . $cookie_array[$i]['secure'] . ', ' . $cookie_array[$i]['httpOnly'];
      }
} */
function retrieve_data($method, $csv, $start_date, $end_date, $dataType) {
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
			$row = 1;
            $j++;
            if ($j == 0) {
                $item = explode(",", $line);
                echo '<tr>';
                echo '<th valign="center" width="50px" align="center">' . "STT" . '</th>';
                foreach ($item as $value) {
                    echo '<th valign="center" align="center" class = "col-' . $row++ . '">' . $value . '</th>';
                }
                echo '</tr>';
            } else {
                $item = explode("\t", $line);
                echo '<tr>';
                echo '<td valign="center"  align="center" class = "col-0">' . $j . '</td>';
                foreach ($item as $value) {
                    echo '<td valign="center" align="center" class = "col-' . $row++ . '">' . $value . '</td>';
                }
                echo '</tr>';
            }
        }
        echo '</tbody>';
        echo '</table>';
        return True;
    } else if ($method == 1) {
        $data = str_replace('$', '', $csv);
        $data = explode("\n", $data);

        //trim all lines contained in the array.
        $data = array_filter($data, 'trim');
        if (count($data) > 1) {
            $count = count(explode("\t", $data[0]));
            $k = 0;
            for ($i = 1; $i < count($data); $i++) {
                $item = explode("\t", $data[$i]);
                $j = 0;
                if ($dataType == 'byName') {
                    if ($item[3] == 0 && $item[4] == 0) {
                        continue;
                    }
                    $output[$k]['ad_date'] = $start_date;
                    $output[$k]['ad_app_name'] = $item[$j++];
                } else if ($dataType == 'byCountry') {
                    $output[$k]['ad_date'] = $start_date;
                    $output[$k]['ad_country'] = $item[$j++];
                } else if ($dataType == 'byDates') {
                    $dt = date_parse_from_format('D, M d, Y', $item[$j++]);
                    $output[$k]['ad_date'] = $dt["year"] . '-' . $dt["month"] . '-' . $dt["day"];
                }
                $output[$k]['ad_revenue'] = $item[$j++];
                $output[$k]['ad_ecpm'] = $item[$j++];
                $output[$k]['ad_requests'] = $item[$j++];
                $output[$k]['ad_impressions'] = $item[$j++];

                $output[$k]['ad_fill_rate'] = $item[$j++];
                if ($dataType !== 'byCountry') {
                    $output[$k]['ad_iad_fill_rate'] = $item[$j++];
                }
                $output[$k]['ad_ttr'] = $item[$j++];
                $k++;
            }
            if ($k == 0) {
                return 2;
            }
            return save_all_data($output, $dataType);
        } else {
            // No Data
            return 2;
        }
    }
}

function save_all_data($output, $dataType) {
    $N = count($output);
    if ($N > 0) {
        try {
            $reverse = false;
            if ($dataType == 'byName') {
                $table = 'ios_ads_app_report_daily';
                $colum = 'ad_app_name';
            } else if ($dataType == 'byCountry') {
                $table = 'ios_ads_country_report_daily';
                $colum = 'ad_country';
            } else if ($dataType == 'byDates') {
                $table = 'ios_ads_report_daily';
                $colum = '';
                $reverse = true;
            }
            $insert_query = "INSERT IGNORE INTO " . $table . " (ad_date,";
            $insert_query .= ($colum != '' ? $colum . ',' : '');
            $insert_query .= "ad_revenue, ad_ecpm, ad_requests, ad_impressions, ad_fill_rate,";
            $insert_query .= ($colum !== 'ad_country' ? 'ad_iad_fill_rate,' : '');
            $insert_query .= "ad_ttr) VALUES ('";
            // global $connect;
            if(!$reverse){
                for ($i = 0 ; $i < $N; $i++) {
                    $sql = $output[$i]['ad_date'] . "','";
                    $sql .= ($colum != '' ? $output[$i][$colum] . "','" : '');
                    $sql .= $output[$i]['ad_revenue'] . "','" .
                            $output[$i]['ad_ecpm'] . "','" .
                            str_replace(',', '', $output[$i]['ad_requests']) . "','" .
                            str_replace(',', '', $output[$i]['ad_impressions']) . "','" .
                            $output[$i]['ad_fill_rate'] . "','";
                    $sql .= ($colum !== 'ad_country' ? $output[$i]['ad_iad_fill_rate'] . "','" : '');
                    $sql .= $output[$i]['ad_ttr'] . "')";
                    $result = query($insert_query . $sql, $error);
                }
            }else{
                for ($i = $N - 1 ; $i >= 0; $i--) {
                    $sql = $output[$i]['ad_date'] . "','";
                    $sql .= ($colum != '' ? $output[$i][$colum] . "','" : '');
                    $sql .= $output[$i]['ad_revenue'] . "','" .
                    $output[$i]['ad_ecpm'] . "','" .
                    str_replace(',', '', $output[$i]['ad_requests']) . "','" .
                    str_replace(',', '', $output[$i]['ad_impressions']) . "','" .
                    $output[$i]['ad_fill_rate'] . "','";
                    $sql .= ($colum !== 'ad_country' ? $output[$i]['ad_iad_fill_rate'] . "','" : '');
                    $sql .= $output[$i]['ad_ttr'] . "')";
                    $result = query($insert_query . $sql, $error);
                }
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
        // No Data
        return 2;
    }
}

?>    