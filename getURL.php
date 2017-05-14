<?php
function setCookiesUrl($cookie_array){
    // bool setcookie ( string $name [, string $value [, int $expirationDate = 0 [, string $path [, string $domain [, bool $secure = false [, bool $httpOnly = false ]]]]]] )
    for($i = 0; $i < count($cookie_array); $i++){
    // $expirationDate = isset($cookie_array[$i]['expirationDate']) ? $cookie_array[$i]['expirationDate'] : '';
    // if($expirationDate !== '')
    // echo time() .' : ' .  MONTH_TIME_SECOND;
    setcookie ($cookie_array[$i]['name'], $cookie_array[$i]['value'], time() + 3600, $cookie_array[$i]['path'], $cookie_array[$i]['domain'], $cookie_array[$i]['secure'], $cookie_array[$i]['httpOnly']); 
    // echo $cookie_array[$i]['name'] . ', ' . $cookie_array[$i]['value'] ;
    // . ', ' . time() + MONTH_TIME_SECOND . ', ' . $cookie_array[$i]['path'] . ', ' . $cookie_array[$i]['domain'] . ', ' . $cookie_array[$i]['secure'] . ', ' . $cookie_array[$i]['httpOnly'];
    }
}
function setCookiesItem($cookie_string, $item_cookie){
    $cookie_array = explode(";", $cookie_string);
    $cookie_array = array_filter($cookie_array, 'trim');
    $cookie = explode("=", $item_cookie, 2);// maximum array = 2 item
    for($i = 0; $i < count($cookie_array); $i++){
        $cookie_item = explode("=", $cookie_array[$i], 2);
        if($cookie_item[0] === $cookie[0]){
            $cookie_item[1] = $cookie[1];
        }
    }
}
function getUrlAppota($cookie, $url, $start_date, $end_date, $curl, $type = APPOTA_EARNING) {
    $header[0] = "Accept: text/xml,application/xml,application/xhtml+xml, text/html;q=0.9,text/plain;q=0.8,image/png,*/*;q=0.5";
    $header[] = "Origin: https://developer.appota.com";
    $header[] = "X-DevTools-Emulate-Network-Conditions-Client-Id: 8589CE8E-40DB-467E-A407-AD78F6A90A69";
    $header[] = "X-FirePHP-Version: 0.0.6";
    $header[] = "Content-Type: application/x-www-form-urlencoded";
    if ($type == APPOTA_EARNING) {
        $header[] = "Referer: https://developer.appota.com/sales-report-app_ppd-page_2.html";
    } else {
        $header[] = "Referer: https://developer.appota.com/sales-report-earnings.html";
    }
    $header[] = "Accept-Encoding: gzip, deflate";
    $header[] = "Accept-Language: vi,en-US;q=0.8,en;q=0.6";
    curl_setopt($curl, CURLOPT_COOKIE, $cookie);

    $header[] = "Cache-Control: no-cache";
    $header[] = "Postman-Token: b820de11-43f5-6e5e-3316-15d193dcb688";
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (X11; U; Linux x86_64; en-US) AppleWebKit/534.3 (KHTML, like Gecko) Ubuntu/10.04 Chromium/6.0.472.53 Chrome/6.0.472.53 Safari/534.3');
    curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
    curl_setopt($curl, CURLOPT_ENCODING, 'gzip,deflate');
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true); // very important to set it to true, otherwise the content will be not be

    curl_setopt($curl, CURLOPT_AUTOREFERER, true); // This make sure will follow redirects
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true); // This too
    curl_setopt($curl, CURLOPT_MAXREDIRS, 20);

    curl_setopt($curl, CURLOPT_POST, true); // 	saved to string	
    if ($type < APPOTA_DOWNLOAD) {
        curl_setopt($curl, CURLOPT_POSTFIELDS, "currency=''&end={$end_date}&offset=1&pagemode=day&start={$start_date}");
    } else {
        curl_setopt($curl, CURLOPT_POSTFIELDS, "currency=''&end_time={$end_date}&offset=1&pageMode=day&start_time={$start_date}");
    }

    /*
     * XXX: This is not a "fix" for your problem, this is a work-around.  You 
     * should fix your local CAs 
     */
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    //curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 1);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
    //curl_setopt($curl, CURLOPT_CAINFO, getcwd() . '\google.crt');
    curl_setopt($curl, CURLOPT_CAINFO, getcwd() . '\appota.crt');

    $html = curl_exec($curl); // execute the curl command
    if ($html === false) {
        // die('error: ' . curl_error($curl));
        $json = array();
        echo_result($json, -100, 'Error: ' . curl_error($curl));
        exit();
    }
    // curl_close($curl);
    $response = curl_getinfo($curl);
    if($response['http_code'] == 403){
        // Access Forbidden
        return -1; 
    }else{
        return $html;
    }
}

function getUrlRefresh($cookie, $curl, $type = APPOTA_EARNING) {
    $url = 'https://developer.appota.com/ajax/refresh_perpage/0';
    $header[] = "X-Requested-With: XMLHttpRequest";
    if ($type == APPOTA_EARNING) {
        curl_setopt($curl, CURLOPT_REFERER, "Referer: https://developer.appota.com/sales-report-app_ppd-page_2.html");
    } else {
        curl_setopt($curl, CURLOPT_REFERER, "Referer: https://developer.appota.com/sales-report.html");
    }
    curl_setopt($curl, CURLOPT_COOKIE, $cookie);
    
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (X11; U; Linux x86_64; en-US) AppleWebKit/534.3 (KHTML, like Gecko) Ubuntu/10.04 Chromium/6.0.472.53 Chrome/6.0.472.53 Safari/534.3');
    curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
    curl_setopt($curl, CURLOPT_ENCODING, 'gzip,deflate,sdch');
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true); // very important to set it to true, otherwise the content will be not be
    
    curl_setopt($curl, CURLOPT_AUTOREFERER, true); // This make sure will follow redirects
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true); // This too
    curl_setopt($curl, CURLOPT_MAXREDIRS, 20);
    
    curl_setopt($curl, CURLOPT_POST, true); // 	saved to string	
    curl_setopt($curl, CURLOPT_COOKIESESSION, true);
    //curl_setopt($curl, CURLOPT_COOKIEJAR, 'cooker.txt');  //could be empty, but cause problems on some hosts
    //curl_setopt($curl, CURLOPT_COOKIEFILE, 'cooker.txt');  //could be empty, but cause problems on some hosts
    
    /*
     * XXX: This is not a "fix" for your problem, this is a work-around.  You 
     * should fix your local CAs 
     */
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    //curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 1);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
    //curl_setopt($curl, CURLOPT_CAINFO, getcwd() . '\google.crt');
    curl_setopt($curl, CURLOPT_CAINFO, getcwd() . '\appota.crt');
    
    $html = curl_exec($curl); // execute the curl command
    if ($html === false) {
        // die('error: ' . curl_error($curl));
        $json = array();
        echo_result($json, -100, 'Error: ' . curl_error($curl));
        exit();
    }
    // curl_close($curl);
    return $html;
}
function getAdmobReport($cookie, $url, $start_date, $end_date, $curl) {
    $header[0] = "X-GWT-Module-Base: https://apps.admob.com/home/resources/";
    $header[] = "X-GWT-Permutation: D6C0091AC960A9D6F2896CBF64711FA9";
    $header[] = "X-DevTools-Emulate-Network-Conditions-Client-Id: D4DA3341-D5BE-4134-9771-5430F6B602C6";
    $header[] = "X-FirePHP-Version: 0.0.6";
    $header[] = "Origin: https://apps.admob.com";
    $header[] = "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8";
    $header[] = "Content-Type: application/x-www-form-urlencoded";
    $header[] = "Referer: http://apps.admob.com/?pli=1";
    $header[] = "Accept-Encoding: gzip, deflate";
    $header[] = "Accept-Language: vi,en-US;q=0.8,en;q=0.6";
    
    $header[] = "Cache-Control: no-cache";
    $header[] = "Postman-Token: 2e7ff163-d754-c080-f23d-f92b151e8b71";
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_COOKIE, $cookie);
    curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2272.118 Safari/537.36');
    curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
    
    curl_setopt($curl, CURLOPT_AUTOREFERER, true); // This make sure will follow redirects
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true); // This too
    curl_setopt($curl, CURLOPT_HEADER, false); // This verbose option for extracting the headers
    
    curl_setopt($curl, CURLOPT_MAXREDIRS, 20);
    
    curl_setopt($curl, CURLOPT_ENCODING, 'gzip,deflate');
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true); // very important to set it to true, otherwise the content will be not be
    curl_setopt($curl, CURLOPT_POST, true); // 	saved to string
    curl_setopt($curl, CURLOPT_POSTFIELDS, 'cc=USD&rs=%7B%222%22%3A%5B%7B%221%22%3A8%2C%222%22%3A' . $start_date . '%2C%223%22%3A' . $end_date . '%7D%5D%2C%223%22%3A%5B1%2C4%2C8%2C6%5D%2C%224%22%3A%22USD%22%7D&t=ADMOB_NETWORK');
    // rs='{"2":[{"1":8,"2":20140611,"3":20150408}],"3":[1,4,8,6],"4":"USD"}';
    // curl_setopt($curl, CURLOPT_COOKIESESSION, true);
    //curl_setopt($curl, CURLOPT_COOKIEJAR, 'cooker.txt');  //could be empty, but cause problems on some hosts
    //curl_setopt($curl, CURLOPT_COOKIEFILE, 'cooker.txt');  //could be empty, but cause problems on some hosts
    
    /*
     * XXX: This is not a "fix" for your problem, this is a work-around.  You 
     * should fix your local CAs 
     */
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    // curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 1);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
    curl_setopt($curl, CURLOPT_CAINFO, getcwd() . '\admob.cer');
    
    $html = curl_exec($curl); // execute the curl command
    if ($html === false) {
        // die('error: ' . curl_error($curl));
        $json = array();
        echo_result($json, -100, 'Error: ' . curl_error($curl));
        exit();
    }
    $response = curl_getinfo($curl);
    if($response['http_code'] == 403){
        // Access Forbidden
        return -1; 
    }else{
        return $html;
    }
}
function getAdmobLogin($cookie, $curl, $header_output = true) {
    // $url = 'https://accounts.google.com/ServiceLoginAuth';
    $header[0] = "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8";
    $header[] = "Origin: https://accounts.google.com";
    $header[] = "Referer: https://accounts.google.com/ServiceLogin?sacu=1&continue=https%3A%2F%2Fapps.admob.com%2F&hl=en&service=admob";
    $header[] = "Accept-Encoding: gzip, deflate";
    $header[] = "Accept-Language: vi,en-US;q=0.8,en;q=0.6";
    $header[] = "Content-Type: application/x-www-form-urlencoded";
    $header[] = "X-Client-Data: CJK2yQEIpbbJAQiptskBCMG2yQEIoInKAQj+lsoB";
    $header[] = "X-Chrome-Connected: id=115551332072172836416,mode=0,enable_account_consistency=false";
    // $cookie = 'S=photos_html=z46TJErV7ijkiu4TNn1Phw; GALX=v1XyAVNGf6c; GMAIL_RTT=252; ACCOUNT_CHOOSER=AFx_qI48sItXTpUijZsgVdmGpmOMBj3iwK9lFGrEK_rBGAV95Cq2Vq7ZeV-LD5AaaIWoz5nQ3ycuEiBGYPHdWO4hftzq1-sCA6f6wv4f6g3d8-oa5wfpFQbXtw2G_LFj9z9PS_AS1FtY; PREF=ID=4d293da704a536d8:U=61a2b43a317068e8:FF=0:LR=lang_en|lang_vi:LD=en:TM=1428990166:LM=1429770986:GM=1:SG=1:S=aCoZE9bXhKWU0SU7; NID=67=YfIpUPUJ1mL09OeZ7yy1p6_492eCy12EO6xASvaKZra-p03wrTsSd2Wc-YwAoH4U5UU-LCCRXu839x7v8KzI3f-E9as0j9CRll6O4USb2EIq4JgByM4ydNoId1ADeBBxKHQ0oULzB0XrkEExVqovGv8lDqilosmM9kdDAXQIXrELIZ1AT6vn1A; GoogleAccountsLocale_session=en; GAPS=1:5BSMa7JN3YdXgR-dunghldcXb6ym4g:TPIFpsf8YgNY-Dlb';
    curl_setopt($curl, CURLOPT_COOKIE, $cookie);
    
    $header[] = "Cache-Control: no-cache";
    $header[] = "Postman-Token: 86ad4be8-32bd-9018-8845-934aab49b7e3";
    // curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2272.118 Safari/537.36');
    curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 0);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
    curl_setopt($curl, CURLOPT_AUTOREFERER, true); // This make sure will follow redirects
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true); // This too
    curl_setopt($curl, CURLOPT_HEADER, $header_output); // This verbose option for extracting the headers
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, "Email=info.dhearts&GALX=v1XyAVNGf6c&Passwd=08dank08&_utf8=%e2%98%83&bgresponse=%21REdCMCuPEs4HPa9ECTQYEzRSAbQCAAAAaVIAAAAOKgEi3uzr7sHz42LnqVrHAhVtgTjqWedPj8KZhjSXoAVy8QHutJ_NgBn3WlLGJI2WG7LDxr7jOmViJ1_pqIoq2tiQVlOp_jNfr66OPr_fnNihgwIWm7CYyCsEgzkzWKMpVYwC8ppVVhsok0DWtrGTxixKnt7yWLNF2N1fiTgXL-WFJL9EJLSy7pWe0rE-aCIrLuU-vjrD9hG8QSW6M1U9HPPaelxpobYR3dO47IKCtG_JijKrzZR3T1XWPp84AfkPUSkkMykIjwe4Xf7IOPuA6_A_sNKK1eFJ11Zj4q_ErRukkGa5cdBkleAsYOD3p_aOsMgbW_OL1Pde3H8G6lgtzmbKGXj2AA3FxY18euUqywCMf7hqLel1kmJcVbwD1X4HPKYpyZQ&checkConnection=youtube%3a121%3a1&checkedDomains=youtube&continue=https%3a%2f%2fapps.admob.com%2f&dnConn=&hl=en&pstMsg=1&rmShown=1&sacu=1&service=admob&signIn=Sign+in");
    curl_setopt($curl, CURLOPT_MAXREDIRS, 20); //The maximum amount of HTTP redirections to follow. Use this option alongside CURLOPT_FOLLOWLOCATION.
    
    curl_setopt($curl, CURLOPT_ENCODING, 'gzip,deflate');
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    // curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 1);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
    curl_setopt($curl, CURLOPT_CAINFO, getcwd() . '\google.crt');
    
    $html = curl_exec($curl); // execute the curl command
    $response = curl_getinfo($curl);
    if ($html === false) {
        // die('error: ' . curl_error($curl));
        $json = array();
        echo_result($json, -100, 'Error: ' . curl_error($curl));
        exit();
    }
    // get cookie
    // preg_match_all('/^Set-Cookie:\s*([^\r\n]*)/mi', $html, $ms);
    preg_match_all('/^Set-Cookie:\s*([^;]*)/mi', $html, $ms);
    
    $cookie = '';
    // $cookies_array = array();
    // foreach ($ms[1] as $m) {
    for ($i = 0; $i < count($ms[1]); $i++) {
        // list($name, $value) = explode('=', $m, 2);
        list($name, $value) = explode('=', $ms[1][$i], 2);
        // $cookies_array[][$name] = $value;
        $cookie .= $name . "=" . $value . ";";
    }
    // print_r($cookies);
    // return array($html, $response, $cookies_array, $cookie);
    if($response['http_code'] == 403){
        // Access Forbidden
        return -1; 
    }else{
        return array($html, $cookie);
    }
}
function getAdmobNextLogin($cookie, $curl, $referer, $accept = '', $header_extract = true) {
    $header[] = "Accept: " . ($accept === '' ? "text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8" : $accept);
    // $header[] = "Origin: https://itunesconnect.apple.com";
    $header[] = "X-Client-Data: CJK2yQEIpbbJAQiptskBCMG2yQEIoInKAQj+lsoB";
    $header[] = "X-Chrome-Connected: id=115551332072172836416,mode=0,enable_account_consistency=false";
    $header[] = "Referer: ". $referer;
    $header[] = "Accept-Encoding: gzip, deflate, sdch";
    $header[] = "Accept-Language: vi,en-US;q=0.8,en;q=0.6";
    // $header[] = "Content-Type: application/x-www-form-urlencoded";
    curl_setopt($curl, CURLOPT_COOKIE, $cookie);
    
    $header[] = "Cache-Control: no-cache";
    $header[] = "Postman-Token: 820de11-43f5-6e5e-3316-15d193dcb688";
    // curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2272.118 Safari/537.36');
    curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 0);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
    curl_setopt($curl, CURLOPT_AUTOREFERER, true); // This make sure will follow redirects
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true); // This too
    curl_setopt($curl, CURLOPT_HEADER, $header_extract); // This verbose option for extracting the headers
    curl_setopt($curl, CURLOPT_MAXREDIRS, 20); //The maximum amount of HTTP redirections to follow. Use this option alongside CURLOPT_FOLLOWLOCATION.
    
    curl_setopt($curl, CURLOPT_ENCODING, 'gzip,deflate,sdch');
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    // curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 1);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
    curl_setopt($curl, CURLOPT_CAINFO, getcwd() . '\google.crt');
    
    $html = curl_exec($curl); // execute the curl command
    $response = curl_getinfo($curl);
    if ($html === false) {
        // die('error: ' . curl_error($curl));
        $json = array();
        echo_result($json, -100, 'Error: ' . curl_error($curl));
        exit();
    }
    // get cookie
    // preg_match_all('/^Set-Cookie:\s*([^\r\n]*)/mi', $html, $ms);
    preg_match_all('/^Set-Cookie:\s*([^;]*)/mi', $html, $ms);
    
    $cookie = '';
    // $cookies_array = array();
    foreach ($ms[1] as $m) {
        list($name, $value) = explode('=', $m, 2);
        // $cookies_array[][$name] = $value;
        $cookie .= $name . "=" . $value . "; ";
    }
    // print_r($cookies);
    // return array($html, $response, $cookies_array, $cookie);
    if($response['http_code'] == 403){
        // Access Forbidden
        return -1; 
        }else{
        return array($html, $cookie);
    }
}
function getAdmobwithLogin($cookie, $url, $s_date, $e_date, $curl){
    $result = getAdmobReport($cookie, $url, $s_date, $e_date, $curl);
    if($result == -1){
        // Access Forbidden
        $url = 'https://accounts.google.com/ServiceLoginAuth';
        // $url = 'https://itunesconnect.apple.com/WebObjects/iTunesConnect.woa/wo/2.0.1.13.3.15.2.1.1.3.1.1';
        $cookie_init = 'S=photos_html=z46TJErV7ijkiu4TNn1Phw; GALX=v1XyAVNGf6c; GMAIL_RTT=252; ACCOUNT_CHOOSER=AFx_qI48sItXTpUijZsgVdmGpmOMBj3iwK9lFGrEK_rBGAV95Cq2Vq7ZeV-LD5AaaIWoz5nQ3ycuEiBGYPHdWO4hftzq1-sCA6f6wv4f6g3d8-oa5wfpFQbXtw2G_LFj9z9PS_AS1FtY; PREF=ID=4d293da704a536d8:U=61a2b43a317068e8:FF=0:LR=lang_en|lang_vi:LD=en:TM=1428990166:LM=1429770986:GM=1:SG=1:S=aCoZE9bXhKWU0SU7; GoogleAccountsLocale_session=en; ';
        
        $curl = curl_init($url);
        // curl_setopt($curl, CURLOPT_URL, $url);
        $login_result = getAdmobLogin($cookie_init, $curl);
        $cookie_login = $login_result[1];
        $cookie_login = $cookie_init . $cookie_login;
        echo $cookie_login;
        $url = 'https://accounts.google.com/CheckCookie?hl=en&checkedDomains=youtube&checkConnection=youtube%3A121%3A1&pstMsg=1&chtml=LoginDoneHtml&service=admob&continue=https%3A%2F%2Fapps.admob.com%2F&gidl=CAA';
        $referer = 'https://accounts.google.com/ServiceLogin?sacu=1&continue=https%3A%2F%2Fapps.admob.com%2F&hl=en&service=admob';
        // $curl = curl_init($url. '#routing');
        curl_setopt($curl, CURLOPT_URL, $url. '#routing');
        $login_result = getAdmobNextLogin($cookie_login, $curl, $referer);
        $cookie_admob = $login_result[1];
        echo '<br>' . $cookie_admob;
        $cookie_admob = trim($cookie_login . ' ' . $cookie_admob);
        $url = 'https://accounts.google.com/JsRemoteLog?module=iframe_set_sid&type=INFO&msg=Iframe%20error%20on%20setting%20SID&r=7687';
        // $url = 'https://iadworkbench.apple.com/app/selfservice/01E0FDE8FDDCE58F424EDC774D0B2172.cache.html';
        // https://iadworkbench.apple.com/app/api/v1/startup
        // $url = 'https://iadworkbench.apple.com/app/service/startup?requestId=GUEST_1429257514356_0__1429257514389__routing';
        $referer = 'https://accounts.google.com/CheckCookie?hl=en&checkedDomains=youtube&checkConnection=youtube%3A121%3A1&pstMsg=1&chtml=LoginDoneHtml&service=admob&continue=https%3A%2F%2Fapps.admob.com%2F&gidl=CAA';
        // $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_URL, $url); 
        $accept = "*/*";
        $login_result = getAdmobNextLogin($cookie_admob, $curl, $referer, $accept);
        // $cookie_time = 'GAPS=1:ZVuz_CQbkkRWQCiBcWU-u6fiXtpP5g:M_roT_aLBNQRaWmp;';
        $cookie_time = $login_result[1];
        echo '<br>' . $cookie_time;
        $cookie = $cookie_admob . ' ' . $cookie;
        // echo '<br>' . $login_result[0];
        // $result = getAdmobReport($cookie, $url, $s_date, $e_date, $curl);
        $url = 'https://apps.admob.com/monetize-reports';
        $result = getAdmobReport($cookie, $url, $s_date, $e_date, $curl);
        // $cookie_dat = $request->getCookie('JSESSIONID');
        // var_dump($cookie_dat);
        if($result == -1){
            $json = array();
            echo_result($json, -1, 'Access Forbidden!');
            exit ();
        }else{
            $sql = "UPDATE m_link SET cookie = '{$cookie}' WHERE link_title = 'Admob'";
            $sqlret = query($sql, $error);
            // echo $sqlret ? "OK" : "Error: " .$error['error'];
            return $result;
        }
        }else{
        return $result;
    }
}
// https://iad.apple.com/itcportal/generatecsv?pageName=app_homepage&dashboardType=publisher&publisherId=1062982&dateRange=customDateRange&searchTerms=Search%20Apps&adminFlag=true&fromDate=03/31/15&toDate=04/09/15&dataType=byName
function getIAdReport($cookie, $start_date, $end_date = '', $curl, $dateRange = 'oneDay', $dataType = 'byName') {
    // $dataType = 
    // By App: 	byName
    // By Country: 	byCountry
    // By Dates:	byDates
    $url = 'https://iad.apple.com/itcportal/generatecsv?pageName=app_homepage&dashboardType=publisher&publisherId=1062982&dateRange=' . $dateRange . '&searchTerms=Search%20Apps&adminFlag=false&fromDate=' . $start_date . ($end_date == '' ? '&toDate' : '&toDate=' . $end_date) . '&dataType=' . $dataType;
    $header[] = "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8";
    $header[] = "Referer: https://iad.apple.com/itcportal/itcportal/4295441D95BA0D185EFE4E1CE88B5375.cache.html";
    $header[] = "Accept-Encoding: gzip, deflate, sdch";
    $header[] = "Accept-Language: vi,en-US;q=0.8,en;q=0.6";
    
    
    curl_setopt($curl, CURLOPT_COOKIE, $cookie);
    
    
    $header[] = "Cache-Control: no-cache";
    $header[] = "Postman-Token: 817a778-7071-649f-d541-839c6fb3151f";
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2272.118 Safari/537.36');
    curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 0);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
    curl_setopt($curl, CURLOPT_AUTOREFERER, true); // This make sure will follow redirects
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true); // This too
    curl_setopt($curl,CURLOPT_HEADER, false); // This verbose option for extracting the headers
    
    
    curl_setopt($curl, CURLOPT_MAXREDIRS, 20); //The maximum amount of HTTP redirections to follow. Use this option alongside CURLOPT_FOLLOWLOCATION.
    
    
    curl_setopt($curl, CURLOPT_ENCODING, 'gzip,deflate,sdch');
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    
    
    // curl_setopt($curl, CURLOPT_COOKIESESSION, true);
    //curl_setopt($curl, CURLOPT_COOKIEJAR, 'cooker.txt');  //could be empty, but cause problems on some hosts
    //curl_setopt($curl, CURLOPT_COOKIEFILE, 'cooker.txt');  //could be empty, but cause problems on some hosts
    
    
    /*
     * XXX: This is not a "fix" for your problem, this is a work-around.  You 
     * should fix your local CAs 
     */
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
    
    curl_setopt($curl, CURLOPT_CAINFO, getcwd() . '\iad.cer');
    
    
    $html = curl_exec($curl); // execute the curl command
    $response = curl_getinfo($curl);
    if ($html === false) {
        // die('error: ' . curl_error($curl));
        $json = array();
        echo_result($json, -100, 'Error: ' . curl_error($curl));
        exit();
    }
    if($response['http_code'] == 403){
        // Access Forbidden
        return -1; 
    }else{
        return $html;
    }
}
function getIAdLogin($curl, $header_output = true) {
    // $url = 'https://itunesconnect.apple.com/WebObjects/iTunesConnect.woa/wo/0.0.1.13.3.15.2.1.1.3.1.1';
    $header[] = "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8";
    $header[] = "Origin: https://itunesconnect.apple.com";
    $header[] = "Referer: https://itunesconnect.apple.com/WebObjects/iTunesConnect.woa";
    $header[] = "Accept-Encoding: gzip, deflate, sdch";
    $header[] = "Accept-Language: vi,en-US;q=0.8,en;q=0.6";
    
    $header[] = "Content-Type: application/x-www-form-urlencoded";
    // curl_setopt($curl, CURLOPT_COOKIE, $cookie);
    
    $header[] = "Cache-Control: no-cache";
    $header[] = "Postman-Token: 817b773-7071-649f-d541-839c6fb3151f";
    // curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2272.118 Safari/537.36');
    curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 0);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
    curl_setopt($curl, CURLOPT_AUTOREFERER, true); // This make sure will follow redirects
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true); // This too
    curl_setopt($curl, CURLOPT_HEADER, $header_output); // This verbose option for extracting the headers
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, "1.Continue.x=0&1.Continue.y=0&inframe=0&theAccountName=app@d-hearts.com&theAccountPW=abcd&theAuxValue");
    curl_setopt($curl, CURLOPT_MAXREDIRS, 20); //The maximum amount of HTTP redirections to follow. Use this option alongside CURLOPT_FOLLOWLOCATION.
    
    curl_setopt($curl, CURLOPT_ENCODING, 'gzip,deflate,sdch');
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    // curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 1);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
    curl_setopt($curl, CURLOPT_CAINFO, getcwd() . '\apple.cer');
    
    $html = curl_exec($curl); // execute the curl command
    $response = curl_getinfo($curl);
    if ($html === false) {
        // die('error: ' . curl_error($curl));
        $json = array();
        echo_result($json, -100, 'Error: ' . curl_error($curl));
        exit();
    }
    // get cookie
    // preg_match_all('/^Set-Cookie:\s*([^\r\n]*)/mi', $html, $ms);
    preg_match_all('/^Set-Cookie:\s*([^;]*)/mi', $html, $ms);
    
    $cookie = '';
    // $cookies_array = array();
    // foreach ($ms[1] as $m) {
    for ($i = 1; $i < count($ms[1]); $i++) {
        // list($name, $value) = explode('=', $m, 2);
        list($name, $value) = explode('=', $ms[1][$i], 2);
        // $cookies_array[][$name] = $value;
        $cookie .= $name . "=" . $value . ";";
    }
    print_r($cookie);
    // return array($html, $response, $cookies_array, $cookie);
    if($response['http_code'] == 403){
        // Access Forbidden
        return -1; 
    }else{
        return array($html, $cookie);
    }
}
function getIAdNextLogin($cookie, $curl, $referer, $accept = '', $header_extract = true) {
    $header[] = "Accept: " . ($accept === '' ? "text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8" : $accept);
    $header[] = "Origin: https://itunesconnect.apple.com";
    $header[] = "Referer: ". $referer;
    $header[] = "Accept-Encoding: gzip, deflate, sdch";
    $header[] = "Accept-Language: vi,en-US;q=0.8,en;q=0.6";
    
    $header[] = "Content-Type: application/x-www-form-urlencoded";
    curl_setopt($curl, CURLOPT_COOKIE, $cookie);
    
    $header[] = "Cache-Control: no-cache";
    $header[] = "Postman-Token: 820de11-43f5-6e5e-3316-15d193dcb688";
    // curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2272.118 Safari/537.36');
    curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 0);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
    curl_setopt($curl, CURLOPT_AUTOREFERER, true); // This make sure will follow redirects
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true); // This too
    curl_setopt($curl, CURLOPT_HEADER, $header_extract); // This verbose option for extracting the headers
    curl_setopt($curl, CURLOPT_MAXREDIRS, 20); //The maximum amount of HTTP redirections to follow. Use this option alongside CURLOPT_FOLLOWLOCATION.
    
    curl_setopt($curl, CURLOPT_ENCODING, 'gzip,deflate,sdch');
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    // curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 1);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
    curl_setopt($curl, CURLOPT_CAINFO, getcwd() . '\apple.cer');
    
    $html = curl_exec($curl); // execute the curl command
    $response = curl_getinfo($curl);
    if ($html === false) {
        // die('error: ' . curl_error($curl));
        $json = array();
        echo_result($json, -100, 'Error: ' . curl_error($curl));
        exit();
    }
    // get cookie
    // preg_match_all('/^Set-Cookie:\s*([^\r\n]*)/mi', $html, $ms);
    preg_match_all('/^Set-Cookie:\s*([^;]*)/mi', $html, $ms);
    
    $cookie = '';
    // $cookies_array = array();
    foreach ($ms[1] as $m) {
        list($name, $value) = explode('=', $m, 2);
        // $cookies_array[][$name] = $value;
        $cookie .= $name . "=" . $value . "; ";
    }
    print_r($cookie);
    // return array($html, $response, $cookies_array, $cookie);
    if($response['http_code'] == 403){
        // Access Forbidden
        return -1; 
    }else{
        return array($html, $cookie);
    }
}
function getIADwithLogin($cookie, $s_date, $e_date, $curl, $dateRange, $dataType){
    $result = getIAdReport($cookie, $s_date, $e_date, $curl, $dateRange, $dataType);
    if($result == -1){
        // Access Forbidden
        $url = 'https://itunesconnect.apple.com/WebObjects/iTunesConnect.woa/wo/0.0.1.13.3.13.3.2.1.1.3.1.1';
        // 0.0.1.13.3.15.2.1.1.3.1.1';
        // 2.0.1.13.3.15.2.1.1.3.1.1';
        $curl = curl_init($url);
        // curl_setopt($curl, CURLOPT_URL, $url);
        $login_result = getIAdLogin($curl);
        $cookie_login = $login_result[1];
        // echo $cookie_login;
        $url = 'https://iadworkbench.apple.com/app/?page=routing';
        $referer = 'https://itunesconnect.apple.com/WebObjects/iTunesConnect.woa/ra/ng/';
        // $curl = curl_init($url. '#routing');
        curl_setopt($curl, CURLOPT_URL, $url. '#routing');
        $login_result = getIAdNextLogin($cookie_login, $curl, $referer);
        $cookie_iad_wb = $login_result[1];
        $cookie = trim($cookie_login . ' ' . $cookie_iad_wb);
        // echo $cookie;
        /* 	$url = 'https://iadworkbench.apple.com/app/api/v1/startup';
            // $url = 'https://iadworkbench.apple.com/app/selfservice/01E0FDE8FDDCE58F424EDC774D0B2172.cache.html';
            // https://iadworkbench.apple.com/app/api/v1/startup
            // $url = 'https://iadworkbench.apple.com/app/service/startup?requestId=GUEST_1429257514356_0__1429257514389__routing';
            $referer = 'https://iadworkbench.apple.com/app/?page=routing';
            // $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_URL, $url); */
        // $accept = "application/json, text/plain, */*";
        /* $login_result = getIAdNextLogin($cookie_iad_wb, $curl, $referer, $accept, false);
        $cookie_routing = $login_result[2];*/
        $result = getIAdReport($cookie, $s_date, $e_date, $curl, $dateRange, $dataType);
        // $cookie_dat = $request->getCookie('JSESSIONID');
        // var_dump($cookie_dat);
        if($result == -1){
            $json = array();
            echo_result($json, -1, 'Access Forbidden!');
            exit ();
        }else{
            $sql = "UPDATE m_link SET cookie = '{$cookie}' WHERE link_type = 4 OR link_type = 5";
            $sqlret = query($sql, $error);
            // echo $sqlret ? "OK" : "Error: " .$error['error'];
            return $result;
        }
    }else{
        return $result;
    }
}
function getIOS_SaleReport($cookie, $curl, $url, $crsf){
	$header[] = "CSRF: ". $crsf;
	$header[] = "X-Requested-With: XMLHttpRequest, OWASP CSRFGuard Project";
    $header[] = "Accept: */*";
	$header[] = 'x-userviewport: {"w":1422,"h":990}';
	$header[] = "x-userdate: ". date('Y-m-d') . "T00:00:00+07:00";
    // $header[] = "Origin: https://itunesconnect.apple.com";
	$header[] = "Referer: https://reportingitc2.apple.com/reports.html";
    $header[] = "Accept-Encoding: gzip, deflate, sdch";
    $header[] = "Accept-Language: vi,en-US;q=0.8,en;q=0.6";
    
    // $header[] = "Content-Type: application/x-www-form-urlencoded";
    curl_setopt($curl, CURLOPT_COOKIE, $cookie);
    
    $header[] = "Cache-Control: no-cache";
    $header[] = "Postman-Token: 817a773-7071-649f-d541-839c6fb3151f";
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2272.118 Safari/537.36');
    curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 0);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
    curl_setopt($curl, CURLOPT_AUTOREFERER, true); // This make sure will follow redirects
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true); // This too
    curl_setopt($curl, CURLOPT_HEADER, false); // This verbose option for extracting the headers
    curl_setopt($curl, CURLOPT_MAXREDIRS, 20); //The maximum amount of HTTP redirections to follow. Use this option alongside CURLOPT_FOLLOWLOCATION.
    
    // curl_setopt($curl, CURLOPT_ENCODING, 'gzip,deflate,sdch');
    curl_setopt($curl, CURLOPT_ENCODING, 'agzip');
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    // curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 1);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
    // curl_setopt($curl, CURLOPT_CAINFO, getcwd() . '\apple.cer');
    
    $html = curl_exec($curl); // execute the curl command
    $response = curl_getinfo($curl);
    if ($html === false) {
        // die('error: ' . curl_error($curl));
        $json = array();
        echo_result($json, -100, 'Error: ' . curl_error($curl));
        exit();
    }
    if($response['http_code'] == 403){
        // Access Forbidden
        return -1; 
	}else{
        return $html;
    }
}

function getISALESwithLogin($cookie, $curl, $urlS, $crsf){
    $result = getIOS_SaleReport($cookie, $curl, $urlS, $crsf);
    if($result == -1){
        // Access Forbidden
        $url = 'https://itunesconnect.apple.com/WebObjects/iTunesConnect.woa/wo/0.0.1.13.3.13.3.2.1.1.3.1.1';
        // 0.0.1.13.3.15.2.1.1.3.1.1';
        // 2.0.1.13.3.15.2.1.1.3.1.1';
        $curl = curl_init($url);
        // curl_setopt($curl, CURLOPT_URL, $url);
        $login_result = getIAdLogin($curl);
        $cookie_login = $login_result[1];
        // echo $cookie_login;
        $url = 'https://iadworkbench.apple.com/app/?page=routing';
        $referer = 'https://itunesconnect.apple.com/WebObjects/iTunesConnect.woa/ra/ng/';
        curl_setopt($curl, CURLOPT_URL, $url. '#routing');
        $login_result = getIAdNextLogin($cookie_login, $curl, $referer);
        $cookie_iad_wb = $login_result[1];
        $cookie = trim($cookie_login . ' ' . $cookie_iad_wb);
        $curl = curl_init();
		$result = getIOS_SaleReport($cookie, $curl, $urlS, $crsf);

        if($result == -1){
            $json = array();
            echo_result($json, -1, 'Access Forbidden!');
            exit ();
		}else{
            $sql = "UPDATE m_link SET cookie = '{$cookie}' WHERE link_type = 4 OR link_type = 5";
            $sqlret = query($sql, $error);
            // echo $sqlret ? "OK" : "Error: " .$error['error'];
            return $result;
        }
	}else{
		return $result;
    }
}

function getHtml($cookie, $curl, $url, $accept = '', $referer = ''){
   
    $header[] = "Accept: " . ($accept === '' ? "text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8" : $accept);
    // $header[] = "Origin: https://itunesconnect.apple.com";
    if($referer != '') {
        $header[] = "Referer: ". $referer;
    }
    $header[] = "Accept-Encoding: gzip, deflate, sdch";
    $header[] = "Accept-Language: vi,en-US;q=0.8,en;q=0.6";
    
    // $header[] = "Content-Type: application/x-www-form-urlencoded";
    curl_setopt($curl, CURLOPT_COOKIE, $cookie);
    
    $header[] = "Cache-Control: no-cache";
    $header[] = "Postman-Token: 817a773-7071-649f-d541-839c6fb3151f";
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2272.118 Safari/537.36');
    curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 0);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
    curl_setopt($curl, CURLOPT_AUTOREFERER, true); // This make sure will follow redirects
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true); // This too
    curl_setopt($curl, CURLOPT_HEADER, false); // This verbose option for extracting the headers
    curl_setopt($curl, CURLOPT_MAXREDIRS, 20); //The maximum amount of HTTP redirections to follow. Use this option alongside CURLOPT_FOLLOWLOCATION.
    
    curl_setopt($curl, CURLOPT_ENCODING, 'gzip,deflate,sdch');
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    // curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 1);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
    // curl_setopt($curl, CURLOPT_CAINFO, getcwd() . '\apple.cer');
    
    $html = curl_exec($curl); // execute the curl command
    $response = curl_getinfo($curl);
    if ($html === false) {
        // die('error: ' . curl_error($curl));
        $json = array();
        echo_result($json, -100, 'Error: ' . curl_error($curl));
        exit();
    }
    if($response['http_code'] == 403){
        // Access Forbidden
        return -1; 
    }else{
        return $html;
    }
}
function getGAInfo($start_date, $end_date, $curl) {
    $url = 'https://www.google.com/analytics/web/getPage?_u.date00=' . $start_date . '&_u.date01=' . $end_date . '&id=app-visitors-overview&ds=a45763820w77165603p79746173&cid=overview%2CreportHeader%2CtimestampMessage&hl=ja&authuser=0';
    $header[0] = "X-GAFE4-XSRF-TOKEN: AFB3sibiFlR111IV3AGFNFRbY9gKzcCCkmiC-1NbRrQH9FOShqAD1CPJsiaWJSmvorIBrfJiy0B1k8scXSGLAgpQ8xrin3F5lw";
    $header[] = "Origin: https://www.google.com";
    $header[] = "Content-Type: application/x-www-form-urlencoded";
    $header[] = "X-Client-Data: CJK2yQEIpbbJAQiptskBCMG2yQEIoInKAQieksoB";
    $header[] = "Referer: https://www.google.com/analytics/web/";
    $header[] = "Accept: */*";
    // $header[] = "Content-Type: application/javascript; charset=UTF-8";
    $header[] = "Content-Type: application/json";
    // $header[] = "Referer: https://apps.admob.com/?pli=1";
    $header[] = "Referer: http://apps.admob.com/?pli=1";
    $header[] = "Accept-Encoding: gzip, deflate";
    $header[] = "Accept-Language: vi,en-US;q=0.8,en;q=0.6";
    $header[] = "Cookie: _ga=GA1.2-2.181652795.1428586517; GoogleAccountsLocale_session=en; GMAIL_RTT=260; PREF=ID=b93b28018cd3daf3:U=14fd2232b58b0aa2:FF=0:LD=en:TM=1428977910:LM=1431654288:GM=1:S=roYBJjKSg23Qm9qu; SID=DQAAAO8AAAD4NjJcJDEsqaRdZ6-9sM_U_saezQHUQHam5ExiZSvAiPrK50rBfQq5OO47t5Eqc9qO-IL3WxbdoI0KK2T5QcnyIR036o2aw9twco-HcYaxceOu70XvwqM6EyC9CGsRAMuNFuVWKaclFtHosCwj8VvCIbXp6-zl5pl0yL7FsKWYsHIBaeT4plm85i-ovo93g8L7LrFLWeSqsv_ucOSQMzHMOsW3kI3gL-j_YlH7PrDze_48ZmDc0RL_tkjY9ffBBHbDkn9HNlz8kPXvf4PWxdl_7nAjPyv6yLYiawxU4LN-0QtJFfbc16cZh06R6aTHDFY; HSID=ALVHu41Pdccln_dMO; SSID=Au_Hpetqc0PDVp8vu; APISID=8_6nZwmpFBiOuRAZ/AeT_JTkgZ5oqJuiNJ; SAPISID=47Ce8hbXEEVkUUfi/AbCk1d-smk_CQwNtG; NID=67=pZ2dZ1jX5qBLGd3DXh0i-8wfzuzSXi_Jhr4qUEjz6OgYMuTZ68C3HgikaGmrt_j7XMA2XzdnE5dbBnDLuKqeFupA8s-f5dzTfgrqYc12tyg7ZUO9R8YFtN1xvnvqSndiyfqcXUtJR0zuKaYoZA6M482pBe4; _ga=GA1.1.181652795.1428586517; S=billing-ui-v3=0QGxTumUV1p45-VYCJelGg:billing-ui-v3-efe=0QGxTumUV1p45-VYCJelGg";
    
    $header[] = "Cache-Control: no-cache";
    $header[] = "Postman-Token: 651552e8-2b7f-8161-29a5-267e09990055";
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2272.118 Safari/537.36');
    curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
    
    curl_setopt($curl, CURLOPT_AUTOREFERER, true); // This make sure will follow redirects
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true); // This too
    // curl_setopt($curl,CURLOPT_HEADER,true); // THis verbose option for extracting the headers
    
    curl_setopt($curl, CURLOPT_ENCODING, 'gzip,deflate');
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true); // very important to set it to true, otherwise the content will be not be
    curl_setopt($curl, CURLOPT_POST, true); // 	saved to string
    curl_setopt($curl, CURLOPT_POSTFIELDS, 'token=AFB3sibiFlR111IV3AGFNFRbY9gKzcCCkmiC-1NbRrQH9FOShqAD1CPJsiaWJSmvorIBrfJiy0B1k8scXSGLAgpQ8xrin3F5lw');
    curl_setopt($curl, CURLOPT_COOKIESESSION, true);
    //curl_setopt($curl, CURLOPT_COOKIEJAR, 'cooker.txt');  //could be empty, but cause problems on some hosts
    //curl_setopt($curl, CURLOPT_COOKIEFILE, 'cooker.txt');  //could be empty, but cause problems on some hosts
    
    /*
     * XXX: This is not a "fix" for your problem, this is a work-around.  You 
     * should fix your local CAs 
     */
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    // curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 1);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
    //curl_setopt($curl, CURLOPT_CAINFO, getcwd() . '\google.crt');
    //curl_setopt($curl, CURLOPT_CAINFO, getcwd() . '\appota.crt');
    curl_setopt($curl, CURLOPT_CAINFO, getcwd() . '\admob.cer');
    
    $html = curl_exec($curl); // execute the curl command
    if ($html === false) {
        // die('error: ' . curl_error($curl));
        $json = array();
        echo_result($json, -100, 'Error: ' . curl_error($curl));
        exit();
    }
    $response = curl_getinfo($curl);
    if($response['http_code'] == 403){
        // Access Forbidden
        return -1; 
    }else{
        return $html;
    }
    return $html;
}       
function getProxyURL($url, $referer = ''){
    $result = getPage(
    '127.0.0.1:8080',// Dung 1 proxy h?p l?
    $url,//'http://www.google.com/search?q=sinhvienit.net'
    $referer,//'http://www.google.com/'
    'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.9.0.8) Gecko/2009032609 Firefox/3.0.8',
    '',
    15);
    
    if (empty($result['ERR'])) {
          // Nếu quá trình CURL ko có lỗi nào
        // Xuất kết quả get đc
        echo $result['EXE'];
    } else {
         // Xảy ra lỗi ?
        // Xuất lỗi
        echo 'Error: '.$result['ERR'];
    }
}
function getPage($proxy, $url, $referer = '', $agent, $header, $timeout) {
    $proxyinfo=explode(':',$proxy);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HEADER, $header);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_PROXY, $proxyinfo[0]);
    curl_setopt($ch, CURLOPT_PROXYPORT, $proxyinfo[1]);
    curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, 0);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
    curl_setopt($ch, CURLOPT_REFERER, $referer);
    curl_setopt($ch, CURLOPT_USERAGENT, $agent);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
    
    $result['EXE'] = curl_exec($ch);
    $result['INF'] = curl_getinfo($ch);
    $result['ERR'] = curl_error($ch);
    
    curl_close($ch);
    return $result;
}   