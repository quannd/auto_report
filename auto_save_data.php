<?php

require('dbconnect.php');
connect_db();
include('getURL.php');
if (!function_exists('gzdecode')) {
	function gzdecode($data)
	{
		$len = strlen($data);
		if ($len < 18 || strcmp(substr($data,0,2),"\x1f\x8b")) {
			return null;  // Not GZIP format (See RFC 1952)
		}
		return gzinflate(substr($data,10,-8));
	} 
}
// include('download_helper.php');
// $url = $_GET['url'];
// $type = $_GET['type'];
$report_title = isset($_GET['report_title']) ? $_GET['report_title'] : '';
// $game_type = $_GET['game_type'];
$store_type = $_GET['store_type'];
$start_date = $_GET['start_date'];
$end_date = $_GET['end_date'];
$method = $_GET['method'];
// $cookie = $_GET['cookie'];
// $store = Appota
// $report_title = PPD; Earning; Download
$json = array();
init_result($json);
if (getLink($result, $store_type, $report_title)) {
    if (count($result) > 0) {
        $type = $result[0]['link_type'];
        $url = $result[0]['link_url'];
        $cookie = $result[0]['cookie'];
    } else {
        echo_result($json, -1, 'Cannot Select Store & Report Link!');
        exit();
    }
} else {
    echo_result($json, -1, 'Cannot Select m_link Data!');
    exit();
}

$curl = curl_init();
// Load Save Data Daily
// At this Point Set Maximum execution time to Over Limited of 120 seconds
ini_set('max_execution_time', 300); // 300s = 5minute
// date = "Y/m/d"
$date_s = date_create_from_format('Y/m/j', urldecode($start_date));
$date_e = date_create_from_format('Y/m/j', urldecode($end_date));
$diff = date_diff($date_s, $date_e);
$increment = $diff->format("%R%a");

if ($increment < 0) {
    echo_result($json, -3, 'Error Start Date > End Date! ' . $s_date . " > " . $e_date);
    exit();
}
// SAVE DATA DAILY!
// APPOTA DATA
if ($type <= APPOTA_DOWNLOAD) {
   /*  if (getGameId($game_type, $result)) {
        if (count($result) > 0) {
            $game_id = $result[0]['game_id'];
        } else {
            echo_result($json, -1, 'Cannot Select Game Type!');
            exit();
        }
    } else {
        echo_result($json, -1, 'Cannot Select m_game Data!');
        exit();
    } */
    if (getStoreId($store_type, $result)) {
        if (count($result) > 0) {
            $store_id = $result[0]['store_id'];
        } else {
            echo_result($json, -1, 'Cannot Select Store Type!');
            exit();
        }
    } else {
        echo_result($json, -1, 'Cannot Select m_store Data!');
        exit();
    }

    $pageCount = 0;
    // date format = urlencode("m/d/Y")
    $s_date = urlencode($date_s->format("m/d/Y"));
    $html = getUrlAppota($cookie, $url, $s_date, $s_date, $curl, $type);
    $pageCount = getPageCount($type, $html);
    while ($pageCount > 1) {
        getUrlRefresh($cookie, $curl, $type);
        $html = getUrlAppota($cookie, $url, $s_date, $s_date, $curl, $type);
        $pageCount = getPageCount($type, $html);
        if ($pageCount > 1)
            sleep(8);
    }
    $isIntOK = retrieve_data($type, $store_id, $html, $date_s->format("Y/m/d"));
    // echo $isIntOK; 
    if (!$isIntOK || $isIntOK == -1){
        echo_result($json, -2, 'Error Retrieve Data!');
        exit();
    }
    // curl_close($curl);
    for ($i = 1; $i <= $increment; $i++) {
        sleep(30);
        date_add($date_s, date_interval_create_from_date_string("1 day"));
        $s_date = urlencode($date_s->format("m/d/Y"));
        // $curl = curl_init();
        $html = getUrlAppota($cookie, $url, $s_date, $s_date, $curl, $type);
        $isIntOK = retrieve_data($type, $store_id, $html, $date_s->format("Y/m/d"));
        if (!$isIntOK || $isIntOK == -1){
            echo_result($json, -2, 'Error Retrieve Data!');
            exit();
        }else if ($isIntOK == 2){
            // No Data
            continue;
        }
    // curl_close($curl);
    }
    echo_result($json, 0, 'OK');
// ADMOB DATA
}else if ($type == ADMOB) {
    // date format = "Ymd"
    $s_date = $date_s->format("Ymd");
    $e_date = $date_e->format("Ymd");
    $data = getAdmobReport($cookie, $url, $s_date, $e_date, $curl);
    if($data == -1){
        // Access Forbidden
        echo_result($json, -1, 'Access Forbidden!');
        exit();
    }
    $data = mb_convert_encoding($data, "UTF-8", 'UTF-16LE');
    $isIntOK = retrieve_data_admob($data);
    if (!$isIntOK || $isIntOK == -1){
        echo_result($json, -2, 'Error Retrieve Data!');
    }else{
        echo_result($json, 0, 'OK');
    }

// IAD DATA
} else if ($type == IAD) {
    // date format = "m/d/Y"
    $s_date = $date_s->format("m/d/y");
    $e_date = $date_e->format("m/d/y");
    $dateRange = 'oneDay';
    if ($increment > 0) {
        $dateRange = 'customDateRange';
    }
    $dtype = array('byName', 'byCountry', 'byDates');
    for ($i = 0; $i <= $increment; $i++) {
        $s_date = $date_s->format("m/d/y");
        foreach ($dtype as $dataType) {
            // $data = getIAdReport($cookie, $s_date, $s_date, $curl, $dateRange, $dataType);
            $data = getIADwithLogin($cookie, $s_date, $s_date, $curl, $dateRange, $dataType);
            $data = mb_convert_encoding($data, "UTF-8", 'UTF-16LE');
            $data = str_replace('","', "\t", $data);
            $data = str_replace('"', '', $data);
            $isIntOK = retrieve_data_iad($data, $date_s->format("Y/m/d"), $dataType);
            if ($isIntOK  && $i < $increment) {
                sleep(3);
            } else if (!$isIntOK || $isIntOK == -1){
                echo_result($json, $isIntOK, 'Error Retrieve Data!');
                exit();
            }else if ($isIntOK == 2){
            // No Data
                continue;
            }
        }
        date_add($date_s, date_interval_create_from_date_string("1 day"));
        if ($isIntOK  && $i < $increment) {
            sleep(10);
        } else if (!$isIntOK || $isIntOK == -1){
            echo_result($json, $isIntOK, 'Error Retrieve Data!');
            exit();
        }else if ($isIntOK == 2){
        // No Data
            continue;
        }
    }
    echo_result($json, 0, 'OK');
}else if ($type == ISALES) {
    $inc = explode('=',$url);
	$inc = $inc[1];
	set_time_limit(0); // unlimited max execution time
	$path = BASEPATH . 'report/ios/salesreport/';
	$crsf = '59RR-1FTV-A4SA-JL8L-F7QJ-OVIZ-6Z4H-GYKA-VUYP-KZNE-XOQY-CTXM-YDR4-6QUC-ZV4H-N2CX-Z3A5-YNN1-3OI1-01GS-2OGF-7KK0-T0RB-ASW8-GZCR-Y69O-US6H-H5VB-4WXP-LPBX-IZ3Q-8MBL';
	
	// curl_setopt($curl, CURLOPT_FILE, $file_handle);
	
	if ($method == 2) {
		for ($i = 0; $i <= $increment; $i++) {
			$s_date = $date_s->format("Y/m/d");
			$urlS = 'https://reportingitc2.apple.com/api/report?vendorID=85428199&reportType=2A&endDate='. urlencode($s_date) .'&vendorType=1&CSRF=' . $crsf. '&_=' . $inc++;
			// S_D_85428199_20150602.txt.gz
			// $prefixName = 'S_D_85428199_';
			$d = $date_s->format("Ymd");
			
			$file_name = $path . 'S_D_85428199_' . $d . '.csv';
			// $file_zip_name = $file_name . ".gz";
			// $file_handle = @fopen($file_zip_name, 'w');
			$file_gzip_content = getISALESwithLogin($cookie, $curl, $urlS, $crsf);
			if($file_gzip_content == -1){
				echo_result($json, -1, 'Access Forbidden!');
				exit ();
			}else{
				if(!check_gzip_data($file_gzip_content))
					continue;
				$data = gzdecode($file_gzip_content);
				
				// write_file($file_name, $file_content);
				// $data = file_get_contents("{$file_name}");
				if(!$data){
					echo_result($json, -6, 'Error Uncompress Data!');
					echo $file_gzip_content;
					exit ();
				}else{
					$isIntOK = retrieve_data_isales($data, $s_date);
					date_add($date_s, date_interval_create_from_date_string("1 day"));
					if ($isIntOK  && $i < $increment) {
						sleep(10);
					} else if (!$isIntOK || $isIntOK == -1){
						echo_result($json, $isIntOK, 'Error Retrieve Data!');
						exit();
					}else if ($isIntOK == 2){
						// No Data
						continue;
					}
				}
			}
		}
	}else {
		$s_date = $date_s->format("Y/m/d");
		$urlS = 'https://reportingitc2.apple.com/api/report?vendorID=85428199&reportType=2A&endDate='. urlencode($s_date) .'&vendorType=1&CSRF=' . $crsf. '&_=' . $inc++;
		// S_D_85428199_20150602.txt.gz
		// $prefixName = 'S_D_85428199_';
		$d = $date_s->format("Ymd");
		
		$file_name = $path . 'S_D_85428199_' . $d . '.csv';
		// $file_zip_name = $file_name . ".gz";
		// $file_handle = @fopen($file_zip_name, 'w');
		$file_gzip_content = getISALESwithLogin($cookie, $curl, $urlS, $crsf);
		if($file_gzip_content == -1){
			echo_result($json, -1, 'Access Forbidden!');
			exit ();
		}else{
			if(!check_gzip_data($file_gzip_content)){
				echo_result($json, 0, 'No Data!');
				exit ();
			}
			$data = gzdecode($file_gzip_content);
			if(!$data){
				echo_result($json, -6, 'Error Uncompress Data!');
				exit ();
			}else{
				if ($method == 0) {
					include('download_helper.php');
				// $mime_type = 'application/a-gzip;charset=UTF-8';
					force_download('S_D_85428199_' . $d . '.csv', $data/* , $mime_type */);
				}else{
					echo $data;
				}
			}
		}
	}
    echo_result($json, 0, 'OK');
}
function check_gzip_data($data){
	$len = strlen($data);
	if ($len < 18 || strcmp(substr($data,0,2),"\x1f\x8b")) {
		return false;  // Not GZIP format (See RFC 1952)
	}
	else{
		return true;
	}
}
function write_file($file_name, $file_content){
	$file_handle = fopen($file_name, 'w');
	// $somecontent = "Add this to the file\n";// Let's make sure the file exists and is writable first.
	if (is_writable($file_name)) {
		// The file pointer is at the bottom of the file hence
		// that's where $somecontent will go when we fwrite() it.
		if (!$file_handle) {
			echo_result($json, -3, 'Cannot open file ($file_name)');
			exit();
		}// Write $somecontent to our opened file.
		if (fwrite($file_handle, $file_content) === FALSE) {
			echo_result($json, -4, 'Cannot write to file ($file_name)');
			exit;
		}
		// echo "Success, wrote ($file_content) to file ($file_name)";
	} else {
		echo_result($json, -4, 'The file $file_name is not writable');
		exit;
	}
	fclose($file_handle);
}

function retrieve_data($type, $store_id, $html, $date) {
    $d = new DOMDocument();
    @$d->loadHTML($html);
    $x = new DOMXPath($d);
    $output = array();
    switch_type($type, $x, $output);
    if(count($output) > 0){
    // SAVE DOWNLOAD DATA
        if ($type == APPOTA_DOWNLOAD) {
            return save_data_download($store_id, $date, $output);
        } else
    // SAVE EARNING DATA DAILY
        if ($type == APPOTA_EARNING) {
            return save_data_earning($store_id, $date, $output);
        } else
        // SAVE PPD DATA DAILY
        if ($type == APPOTA_PPD) {
            return -1;
        }
    }else{
        // No Data
        return 2;
    }
}

// SAVE IAD DATA DAILY
function retrieve_data_iad($csv, $start_date, $dataType) {
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
        return save_all_data_iad($output, $dataType);
    } else {
        // No Data
        return 2;
    }
}

function retrieve_data_admob($csv) {
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
        save_all_data_admob($output);
    } else {
        // echo "No Data!";
        return 2;
    }
}
function retrieve_data_isales($csv, $date) {
    $data = explode("\n", $csv);
    
    //trim all lines contained in the array.
    $data = array_filter($data, 'trim');
    if (count($data) > 1) {
        $count = count(explode("\t", $data[0]));
        for ($i = 1; $i < count($data); $i++) {
            $item = explode("\t", $data[$i]);
            $j = 0;
            $k = $i - 1;
            $output[$k]['st_provider'] = $item[$j++];
            $output[$k]['st_provider_country'] = $item[$j++];
            $output[$k]['st_sku'] = $item[$j++];
            $output[$k]['st_developer'] = $item[$j++];
            $output[$k]['st_product_title'] = $item[$j++];
            $output[$k]['st_product_version'] = $item[$j++];
            $output[$k]['st_product_type_id'] = $item[$j++];
            $output[$k]['st_units'] = $item[$j++];
            $output[$k]['st_developer_proceeds'] = $item[$j++];
            $output[$k]['st_begin_date'] = $item[$j++];
            $output[$k]['st_end_date'] = $item[$j++];
            $output[$k]['st_buyer_currency'] = $item[$j++];
            $output[$k]['st_country'] = $item[$j++];
            $output[$k]['st_currency_buy'] = $item[$j++];
            $output[$k]['st_apple_id'] = $item[$j++];
            $output[$k]['st_item_price'] = $item[$j++];
            $output[$k]['st_promo_code'] = $item[$j++];
            $output[$k]['st_parent_id'] = $item[$j++];
            $output[$k]['st_subscription'] = $item[$j++];
            $output[$k]['st_period'] = $item[$j++];
            $output[$k]['st_category'] = $item[$j++];
            $output[$k]['st_cmb'] = $item[$j++];
        }
        return save_all_data_isales($output, $date);
    } else {
        // echo "No Data!";
        return 2;
    }
}
function getPageCount($type, $html) {
    $d = new DOMDocument();
    @$d->loadHTML($html);
    $x = new DOMXPath($d);
    $totalCount = 0;
    $count = 0;
    if ($type == APPOTA_DOWNLOAD) {
        $report_content = $x->query('//div[@id="country1"]');
    } else if ($type == APPOTA_PPD) {
        $report_content = $x->query('//div[@id="country2"]');
    } else if ($type == APPOTA_EARNING) {
        $report_content = $x->query('//div[@id="country3"]');
    }
    foreach ($report_content as $container) {
        $arr = $container->getElementsByTagName("a");
        foreach ($arr as $item) {
            $totalCount++;
            if (strlen($item->nodeValue) > 1)
                $count++;
        }
    }
    return ($totalCount - $count);
}

function switch_type($type, $x, &$output) {
    $output = null;
    $name = null;
    if ($type == APPOTA_DOWNLOAD) {
        $report_content = $x->query('//div[@id="country1"]');
        foreach ($report_content as $container) {
            $arr = $container->getElementsByTagName("a");
            foreach ($arr as $item) {
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
    } else if ($type == APPOTA_PPD) {
        $report_content = $x->query('//div[@id="country2"]');
        foreach ($report_content as $container) {
            $arr = $container->getElementsByTagName("a");
            foreach ($arr as $item) {
                if (strlen($item->nodeValue) > 1)
                    $name[] = trim($item->nodeValue);
            }
        }
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
    } else if ($type == APPOTA_EARNING) {
        $report_content = $x->query('//div[@id="country3"]');
        foreach ($report_content as $container) {
            $arr = $container->getElementsByTagName("a");
            foreach ($arr as $item) {
                if (strlen($item->nodeValue) > 1)
                    $name[] = trim($item->nodeValue);
            }
        }
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
    }
}

// function save_data_download($game_id, $store_id, $name, $date, $download_num, $os, $trend ) {
function save_data_download($store_id, $date, $output) {
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
            // $date = explode('/', $date);
            // global $connect;
            for ($i = 0; $i < count($output); $i++) {
                $game_id = check_game_id($output[$i]['name']);
                $insert_query = $prefix_query .
                        "'" . $game_id[0]['game_id'] . "', " .
                        "'" . $output[$i]['name'] . "', " .
                        // "'" . $date[2] . "-" . $date[0] . "-" . $date[1] . "', " .
                        "'" . $date . "', " .
                        "'" . $store_id . "', " .
                        "'" . $output[$i]['os'] . "', " .
                        "'" . str_replace(",", "", $output[$i]['download_num']) . "', " .
                        "'" . $output[$i]['trend'] . "')";
                // $result = mysqli_query($connect, $insert_query);
                // or die(mysqli_error($connect));
                $result = query($insert_query, $error);
            }
            // echo "OK: " . $insert_query . "</br>";
            if ($result){
                // echo "OK";
            }
            else {
                // echo $error['error'];
                set_result($json, -2, get_err_msg($json) . "\n" . $error['error']);
                writeLogSql($error['error'], $insert_query, 'auto_save_data.php');
            }
            return $result;
        } catch (ErrorException $e) {
            // if (DISPLAY_ERRORS)
                // echo $e;
                set_result($json, -1, $e->getMessage());
            writeLogSql($e->getMessage(), $insert_query, 'auto_save_data.php');
            return -1;
        }
    }else {
        return 2;
    }
}

// function save_data_earning($game_id, $store_id, $name, $date, $os, $sales_type, $money_vnd, $money_usd) {
function save_data_earning($store_id, $date, $output) {
    if (count($output) > 0) {
        try {
            // $date = explode('/', $date);
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
                $game_id = check_game_id($output[$i]['name']);
                $insert_query = $prefix_query .
                        "'" . $store_id . "', " .
                        "'" . $game_id[0]['game_id'] . "', " .
                        "'" . $output[$i]['name'] . "', " .
                        // "'" . $date[2] . "-" . $date[0] . "-" . $date[1] . "', " .
                        "'" . $date . "', " .
                        "'" . $output[$i]['os'] . "', " .
                        "'" . $output[$i]['sales_type'] . "', " .
                        "'" . str_replace(",", "", $output[$i]['money_vnd']) . "', " .
                        "'" . str_replace(",", "", $output[$i]['money_usd']) . "')";
                // $result = mysqli_query($connect, $insert_query);
                // or die(mysqli_error($connect));
                $result = query($insert_query, $error);
            }
            // echo "OK: " . $insert_query . "</br>";
            if ($result){
                // echo "OK";
            }
            else
            {
                // echo $error['error'];
                set_result($json, -2, get_err_msg($json) . "\n" . $error['error']);
                writeLogSql($error['error'], $insert_query, 'auto_save_data.php'  . '::' . 'save_data_earning');
            }
            return $result;
        } catch (ErrorException $e) {
            // if (DISPLAY_ERRORS)
                // echo $e;
            set_result($json, -1, $e->getMessage());
            writeLogSql($e->getMessage(), $insert_query, 'auto_save_data.php' . '::' . 'save_data_earning');
            return -1;
        }
    } else {
        return 2;
    }
}

function check_game_id($name) {
    try {
             $select_query = "Select game_id From m_game 
                                Where name = '{$name}'"; 	
             $isIntOK = select($select_query, $result);
             if ($isIntOK){
                 // echo "OK";
             }
             else
             {
                 writeLogSql('error', $select_query, 'auto_save_data.php'  . '::' . 'check_game_id');
             }
         return $result;
        } catch (ErrorException $e) {
             // if (DISPLAY_ERRORS)
             // echo $e;
             set_result($json, -1, $e->getMessage());
             writeLogSql($e->getMessage(), $select_query, 'auto_save_data.php' . '::' . 'check_game_id');
             return -1;
        }
}
function save_all_data_iad($output, $dataType) {
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
            if ($result){
                // echo "OK";
            }
            else
            {
                // echo $error['error'];
                set_result($json, -2, get_err_msg($json) . "\n" . $error['error']);
                writeLogSql($error['error'], $insert_query . $sql, 'auto_save_data.php' . '::' . 'save_all_data_iad');
            }
            return $result;
        } catch (ErrorException $e) {
            // if (DISPLAY_ERRORS)
                // echo $e;
            set_result($json, -1, $e->getMessage());
            writeLogSql($e->getMessage(), $insert_query . $sql, 'auto_save_data.php' . '::' . 'save_all_data_iad');
            return -1;
        }
    } else {
        return 2;
    }
}

function save_all_data_admob($output) {
    if (count($output) > 0) {
        try {
            // global $connect;
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
                // $result = mysqli_query($connect, $insert_query);
                // or die(mysqli_error($connect));
                $result = query($insert_query, $error);
            }
            if ($result){
                // echo "OK";
            }
            else
            {
                // echo $error['error'];
                set_result($json, -2, get_err_msg($json) . "\n" . $error['error']);
                writeLogSql($error['error'], $insert_query, 'auto_save_data.php' . '::' . 'save_all_data_admob');
            }
            return $result;
        } catch (ErrorException $e) {
            // if (DISPLAY_ERRORS)
                // echo $e;
            set_result($json, -1, $e->getMessage());
            writeLogSql($e->getMessage(), $insert_query, 'auto_save_data.php' . '::' . 'save_all_data_admob');
            return -1;
        }
    } else {
        return 2;
    }
}

function save_all_data_isales($output, $date) {
    if (count($output) > 0) {
        try {
            // global $connect;
            $increment = 1;
            $prefix_query = "INSERT IGNORE INTO ios_sales_trend_report (st_unique_index,st_provider,st_provider_country,st_sku,st_developer,st_product_title,st_product_version,st_product_type_id,st_units,st_developer_proceeds,st_begin_date,st_end_date,st_buyer_currency,st_country,st_currency_buy,st_apple_id,st_item_price,st_promo_code,st_parent_id,st_subscription,st_period,st_category,st_cmb)
            VALUES ('";
            
            for ($i = 0; $i < count($output); $i++) {
                $insert_query = $prefix_query .
				$increment++ . "','" .
                $output[$i]['st_provider'] . "','" .
                $output[$i]['st_provider_country'] . "','" .
                $output[$i]['st_sku'] . "','" .
                $output[$i]['st_developer'] . "','" .
                $output[$i]['st_product_title'] . "','" .
                $output[$i]['st_product_version'] . "','" .
                $output[$i]['st_product_type_id'] . "','" .
                $output[$i]['st_units'] . "','" .
                str_replace(',', '', $output[$i]['st_developer_proceeds']) . "','" .
                $date . "','" .
                $date . "','" .
                $output[$i]['st_buyer_currency'] . "','" .
                $output[$i]['st_country'] . "','" .
                $output[$i]['st_currency_buy'] . "','" .
                $output[$i]['st_apple_id'] . "','" .
                str_replace(',', '', $output[$i]['st_item_price']) . "','" .
                $output[$i]['st_promo_code'] . "','" .
                $output[$i]['st_parent_id'] . "','" .
                $output[$i]['st_subscription'] . "','" .
                $output[$i]['st_period'] . "','" .
                $output[$i]['st_category'] . "','" .
                $output[$i]['st_cmb'] . "')";

                // $result = mysqli_query($connect, $insert_query);
                // or die(mysqli_error($connect));
                $result = query($insert_query, $error);
				if ($result){
					// echo "OK";
				}
				else
				{
					// echo $error['error'];
					set_result($json, -2, get_err_msg($json) . "\n" . $error['error']);
					writeLogSql($error['error'], $insert_query, 'auto_save_data.php' . '::' . 'save_all_data_isales');
				}
            }
            return $result;
		} catch (ErrorException $e) {
			// if (DISPLAY_ERRORS)
			// echo $e;
			set_result($json, -1, $e->getMessage());
			writeLogSql($e->getMessage(), $insert_query, 'auto_save_data.php' . '::' . 'save_all_data_isales');
			return -1;
		}
    } else {
        return 2;
    }
}
// Flush Output Buffer
ob_end_flush();
?>
                