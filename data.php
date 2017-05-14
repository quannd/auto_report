<html><head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
<title></title>
<meta name="description" content="">
<meta name="viewport" content="width=device-width, initial-scale=1"> </head></html>
<?php
/* session_start();
  $result = setcookie("JSESSIONID", "C363E973BF94F76F46927E3ECB6093CE", time() + 24*3600, '/','');
  // $result = setrawcookie('ci_session', $monter_admin_CK, $time, '/', "192.168.0.88"); "183.91.4.175"
  if ($result)
  echo "Set Cookie Successfully!";
  else
  echo "Set Cookie UnSuccessfully!!!";
  // $_SESSION['username'] = 'demo';
  // Set-Cookie:  JSESSIONID=C363E973BF94F76F46927E3ECB6093CE; Path=/
  //$S = 'billing-merchant-ui=TL8GAxR9W0S0uy1RWr24dg:billing-ui-v3-efe=hZm1keEauopyoHYr4dlT5Q:checkout-gadget=p50DvpUl7VQ:static_files=VQEAImTwvt8:payments=sf5DtMGspxX7pEIjsWwccQ:billing-ui-v3=hZm1keEauopyoHYr4dlT5Q:billing-ui=5Im1qMgt2RPGi38RlKuHYw:billing-ui-efe=5Im1qMgt2RPGi38RlKuHYw';
  //    bool setcookie ( string $name [, string $value [, int $expire = 0 [, string $path [, string $domain
  ////setrawcookie(...)
  //setcookie('ci_session', $monter_admin_CK, $time, '/', "192.168.0.88");
  //    [, bool $secure = false [, bool $httponly = false ]]]]]] );
  //setcookie('S', $S, $time, '/', ".google.com", true, true);
  //$_COOKIE['S'] = $S;
  //echo $time;
  //destroy_cookie_session();
  //destroy_cookie(); */
include('dbconnect.php');
connect_db();
global $connect;
$connect = $GLOBALS['connect'];

// var_dump($connect);
function destroy_cookie_session() {
    setcookie("S", "", time() - 3600);
    print_r($_COOKIE);
    $_SESSION = array();
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]
        );
    }
    session_destroy();
}

function destroy_cookie() {
    $cookiesSet = array_keys($_COOKIE);
    for ($x = 0; $x < count($cookiesSet); $x++)
        setcookie($cookiesSet[$x], "", time() - 1);
}

function import_file_to_db() {
    // $path="/var/report/google/salesreport/";
    $path = '';
    $DATE = date("Ym", strtotime('-1 days'));
    $File_path = "{$path}salesreport_{$DATE}.csv";
    $Table = "google_paid_bill";
    $Column = "t_order_number,t_order_charged_date,t_order_charged_time,t_finance_state,t_device,t_product_title,t_product_id,t_product_type,t_sku_id,t_currency,@t_item_price,t_tax,@t_charged_amount,t_buyer_city,t_buyer_state,t_postal_code,t_country";

    $Set_value = "SET t_item_price = REPLACE(@t_item_price,',',''), t_charged_amount = REPLACE(@t_charged_amount,',','')";
    $POST_DATA = "file_path={$File_path}&table={$Table}&column={$Column}&set_value={$Set_value}";
    $data = array(
        'file_path' => $File_path,
        'table' => $Table,
        'column' => $Column,
        'set_value' => $Set_value
    );
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    // curl_setopt($ch, CURLOPT_URL, 'http://localhost/gapi/AppotaDevelopers.html');
    $url = 'http://localhost/bao_cao/auto_import_db.php';
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    $res = curl_exec($ch);

    if ($res === false) {
        die('error: ' . curl_error($ch));
    }
    curl_close($ch);
    echo $res;
}
?>

<form>
    <li>
        <label><b>Store</b></label>
        <select name="store" id="store">
            <option value="">Select a Store:</option>

            <?php
            // include('dbconnect.php');
            $query = "SELECT store_id, name FROM m_store";
            $result = mysqli_query($connect, $query);
            if (!$result) {
                die('Câu truy vấn bị sai');
            }
            while ($row = mysqli_fetch_assoc($result)) {
                $select = ($row['store_id'] == "2" ? "2' selected" : $row["store_id"] . "'");
                echo "<option value='" . $select . " >" . $row['name'] . "</option>";
            }
            ?>
        </select> 
    </li>
    <li>
        <label><b>Game</b></label>
        <select name="game_id" id="game_id">
            <option value="">Select a Game:</option>

            <?php
            // include('dbconnect.php');
            $query = "SELECT game_id, name FROM m_game";
            $result = mysqli_query($connect, $query);
            while ($row = mysqli_fetch_array($result)) {
                $select = ($row['game_id'] == "22" ? "22' selected" : $row["game_id"] . "'");
                echo "<option value='" . $select . "  >" . $row['name'] . "</option>";
            }
            ?>
        </select> 

    </li>
  <!--  <li>
        <label><b>Save By Date (All)</b></label>
        <select name="date_type" id="date_type">
            <option value="0" selected>Save All Date</option>
            <option value="1">Not Available</option>
        </select>
    </li> -->
    <li>
        <label>Start</label>
        <select name="start_year" id="start_year" >
            <option value="2012" selected>2012</option>
            <option value="2013">2013</option>
            <option value="2014">2014</option>
            <option value="2015">2015</option>
            <option value="2016">2016</option>
            <option value="2017">2017</option>
            <option value="2018">2018</option>
            <option value="2019">2019</option>
            <option value="2020">2020</option>
        </select>
        年
        <select name="start_month" id="start_month">
            <option value="1" selected>01</option>
            <option value="2">02</option>
            <option value="3">03</option>
            <option value="4">04</option>
            <option value="5">05</option>
            <option value="6">06</option>
            <option value="7">07</option>
            <option value="8">08</option>
            <option value="9">09</option>
            <option value="10">10</option>
            <option value="11">11</option>
            <option value="12">12</option>
        </select>
        月
        <select name="start_day" id="start_day">
            <option value="1" selected>01</option>
            <option value="2">02</option>
            <option value="3">03</option>
            <option value="4">04</option>
            <option value="5">05</option>
            <option value="6">06</option>
            <option value="7">07</option>
            <option value="8">08</option>
            <option value="9">09</option>
            <option value="10">10</option>
            <option value="11">11</option>
            <option value="12">12</option>
            <option value="13">13</option>
            <option value="14">14</option>
            <option value="15">15</option>
            <option value="16">16</option>
            <option value="17">17</option>
            <option value="18">18</option>
            <option value="19">19</option>
            <option value="20">20</option>
            <option value="21">21</option>
            <option value="22">22</option>
            <option value="23">23</option>
            <option value="24">24</option>
            <option value="25">25</option>
            <option value="26">26</option>
            <option value="27">27</option>
            <option value="28">28</option>
            <option value="29">29</option>
            <option value="30">30</option>
            <option value="31">31</option>
        </select>
        日
        <label>End</label>
        <select name="end_year" id="end_year" >
            <option value="2012" selected>2012</option>
            <option value="2013">2013</option>
            <option value="2014">2014</option>
            <option value="2015">2015</option>
            <option value="2016">2016</option>
            <option value="2017">2017</option>
            <option value="2018">2018</option>
            <option value="2019">2019</option>
            <option value="2020">2020</option>
        </select>
        年
        <select name="end_month" id="end_month">
            <option value="1" selected>01</option>
            <option value="2">02</option>
            <option value="3">03</option>
            <option value="4">04</option>
            <option value="5">05</option>
            <option value="6">06</option>
            <option value="7">07</option>
            <option value="8">08</option>
            <option value="9">09</option>
            <option value="10">10</option>
            <option value="11">11</option>
            <option value="12">12</option>
        </select>
        月
        <select name="end_day" id="end_day">
            <option value="1" selected>01</option>
            <option value="2">02</option>
            <option value="3">03</option>
            <option value="4">04</option>
            <option value="5">05</option>
            <option value="6">06</option>
            <option value="7">07</option>
            <option value="8">08</option>
            <option value="9">09</option>
            <option value="10">10</option>
            <option value="11">11</option>
            <option value="12">12</option>
            <option value="13">13</option>
            <option value="14">14</option>
            <option value="15">15</option>
            <option value="16">16</option>
            <option value="17">17</option>
            <option value="18">18</option>
            <option value="19">19</option>
            <option value="20">20</option>
            <option value="21">21</option>
            <option value="22">22</option>
            <option value="23">23</option>
            <option value="24">24</option>
            <option value="25">25</option>
            <option value="26">26</option>
            <option value="27">27</option>
            <option value="28">28</option>
            <option value="29">29</option>
            <option value="30">30</option>
            <option value="31">31</option>
        </select>
        日
    </li>
    <li>	URL


        <select name="url" id="url" name="url" onchange='Hide_Element(this.selectedOptions[0].index, appota_data, data_admob, data_iad, data_ios)'>
            <?php
            // include('dbconnect.php');
            $query = "SELECT link_id, link_title, link_name, cookie FROM m_link";
            $result = mysqli_query($connect, $query);
            while ($row = mysqli_fetch_assoc($result)) {
                // $select = ($row['link_id'] == "2" ? $row["link_url"] . " selected" : $row["link_url"]);
                // echo "<option value=" . $select . " cookie='" . urlencode($row['cookie']) . "' >" . $row['link_name'] . "</option>";
                $select = $row['link_title'] == "Appota_Earning" ? " selected" : '';
                echo "<option value='" . $row['link_title'] . "'" . $select . " >" . $row['link_name'] . "</option>";
            }
            ?>
        </select>  
    </li>
</form>
<div  id = 'appota_data' style="display: block">
    <a href='#' onclick="LoadData(0)"> Load Appota Data! </a>
    <br><a href='#' onclick="LoadData(1)"> Save Appota Download Report! </a>
    <br><a href='#' onclick="LoadData(3)"> Save Appota Earning Report! </a>
    <br><a href='#' onclick="LoadData(2)"><strong>  Save Appota Earning Report Daily! </strong> </a>
</div>
<div  id = 'data_admob' style="display: none">
    <a href='#' onclick="LoadDataAdmob(0)"> Load Data Admob! </a>
    <br><a href='#' onclick="DownLoadDataAdmob()"> DownLoad Admob CSV! </a>
    <br><a href='#' onclick="LoadDataAdmob(1)"> Save All Admob! </a>

</div>
<div  id = 'data_iad' style="display: none">
    <!--$dataType = 
    0:	By App: 	byName
    1:	By Country: 	byCountry
    2:	By Dates:	byDates -->
    <a href='#' onclick="LoadDataIAD(0, 'byName')"> Load IAd Data By App! </a>
    <br><a href='#' onclick="LoadDataIAD(0, 'byCountry')"> Load IAd Data By Country! </a>
    <br><a href='#' onclick="LoadDataIAD(0, 'byDates')"> Load IAd Data By Dates! </a>
    <br><a href='#' onclick="DownLoadDataIAD('byName')"> DownLoad CSV IAd Data By App! </a>
    <br><a href='#' onclick="DownLoadDataIAD('byCountry')"> DownLoad CSV IAd Data By Country! </a>
    <br><a href='#' onclick="DownLoadDataIAD('byDates')"> DownLoad CSV IAd Data By Dates! </a>
    <br><a href='#' onclick="LoadDataIAD(1, 'byName')"> Save IAd Data By App! </a>
    <br><a href='#' onclick="LoadDataIAD(1, 'byCountry')"> Save IAd Data By Country! </a>
    <br><a href='#' onclick="LoadDataIAD(1, 'byDates')"> Save IAd Data By Dates! </a>
    <br><a href='#' onclick="LoadDataIAD(3, 'byName')"><strong> Save IAd Data By App Report Daily! </strong></a>
    <br><a href='#' onclick="LoadDataIAD(3, 'byCountry')"><strong> Save IAd Data By Country Report Daily! </strong></a>
</div>
<div  id = 'data_ios' style="display: none">
    <a href='#' onclick="LoadDataIOS(0)"> Download CSV IOS Sale & Trend Data! </a>
    <br><a href='#' onclick="LoadDataIOS(1)"> Show IOS Sale & Trend Data Report! </a>
    <br><a href='#' onclick="LoadDataIOS(2)"><strong>  Save IOS Sale & Trend Data Report Daily! </strong> </a>
</div>
<div  id = 'load_ga' >
    <a href='#' onclick="LoadDataAdmob(3)"> Load GA User Summary! </a>
</div>
<div  id = 'save' ></div>
<!--<a href='https://developer.appota.com/sales-report-earnings-page_1.html'>https://developer.appota.com/sales-report-earnings-page_1.html</a><br>

<a href='https://developer.appota.com/sales-report-earnings-page_2.html'>https://developer.appota.com/sales-report-earnings-page_2.html</a><br>
<a href='https://developer.appota.com/sales-report-detail-earnings-143702.html'>Monster Dungeon IOS</a><br>
<a href="https://developer.appota.com/sales-report-app_iap_log-127368-day.html">Monster Dungeons Android</a><br> 
<div  id = 'admob_header' >
    <a href='header.html' > Admob Header! </a>
</div>-->
<script type="text/javascript" src="javascripts/trim.js"></script>
<!--<script type="text/javascript" src="javascripts/yui3.18.1/build/yui/yui-min.js"></script>
 <script type="text/javascript" src="javascripts/yui2.8.2/yahoo/yahoo-min.js"></script> 
<script type="text/javascript" src="javascripts/json_encode.js"></script>
<script type="text/javascript" src="javascripts/yui2.8.2/json/json-min.js"></script>-->
<script type="text/javascript">
    function LoadData(method)
    {
        // var Dom = YAHOO.util.Dom,
        // Json = YAHOO.lang.JSON;
        // var cookie = document.getElementById("url");
        // cookie = cookie[cookie.selectedIndex].getAttribute('cookie');
        var storeId = document.getElementById('store').value;
        var gameId = document.getElementById('game_id').value;
        // var date_type = document.getElementById('date_type').value;
        // var url = document.getElementById("url").value;
        var link_title = document.getElementById("url").value;
        var type = document.getElementById("url").selectedOptions[0].index;
        // $x('//select[@id="start_year"]/option[@selected="selected"]/@value');
        var start_year = document.getElementById("start_year").value;
        var start_month = document.getElementById("start_month").value;
        var start_day = document.getElementById("start_day").value;
        var end_year = document.getElementById("end_year").value;
        var end_month = document.getElementById("end_month").value;
        var end_day = document.getElementById("end_day").value;
        var start_date = start_month + "%2F" + start_day + "%2F" + start_year;
        var end_date = end_month + "%2F" + end_day + "%2F" + end_year;
        if (window.XMLHttpRequest)
        {// code for IE7+, Firefox, Chrome, Opera, Safari
            xmlhttp = new XMLHttpRequest();
        }
        else
        {// code for IE6, IE5
            xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
        }
        xmlhttp.onreadystatechange = function()
        {
            if (xmlhttp.readyState == 4 && xmlhttp.status == 200)
            {
                document.getElementById("save").innerHTML = xmlhttp.responseText;
            } else {
                document.getElementById("save").innerHTML = 'Loading...';
            }
        }
        // xmlhttp.open("GET", "save_data.php?store=" + storeId + "&method=" + method + "&game_id=" + gameId + "&start_date=" + start_date + "&end_date=" + end_date + "&url=" + url + "&type=" + type + '&cookie=' + cookie, true);
        xmlhttp.open("GET", "save_data.php?store=" + storeId + "&method=" + method + "&game_id=" + gameId + "&start_date=" + start_date + "&end_date=" + end_date + "&link_title=" + link_title + "&type=" + type, true);
        xmlhttp.send();
    }
    function Hide_Element(index, appota_data, data_admob, data_iad, data_ios)
    {
        if (index <= <?php echo APPOTA_DOWNLOAD; ?>) {
            appota_data.style.display = "block";
            data_admob.style.display = "none";
            data_iad.style.display = "none";
			data_ios.style.display = "none";
        } else if (index == <?php echo ADMOB; ?>) {
            appota_data.style.display = "none";
            data_admob.style.display = "block";
            data_iad.style.display = "none";
			data_ios.style.display = "none";
        } else if (index == <?php echo IAD; ?>) {
            appota_data.style.display = "none";
            data_admob.style.display = "none";
            data_iad.style.display = "block";
			data_ios.style.display = "none";
        } else if (index == <?php echo ISALES; ?>) {
			appota_data.style.display = "none";
			data_admob.style.display = "none";
			data_iad.style.display = "none";
			data_ios.style.display = "block";
		}
    }
     function json_convert(abc){
        abc = abc.substr(abc.indexOf('{'));
        abc = trim(abc);
        abc = trim(abc, "}");
        abc = trim(abc, "{");
        var strArray = abc.split("}}{");
        for(value in strArray){
            strArray[value] = "{"+strArray[value]+"}}";
        }
        // var t = json_encode(strArray);
        return JSON.stringify(strArray);
     }
    function LoadDataAdmob(method)
    {
        var start_year = document.getElementById("start_year").value;
        var start_month = document.getElementById("start_month").value;
        var start_day = document.getElementById("start_day").value;
        var end_year = document.getElementById("end_year").value;
        var end_month = document.getElementById("end_month").value;
        var end_day = document.getElementById("end_day").value;
        var start_date = start_year + "/" + start_month + "/" + start_day;
        var end_date = end_year + "/" + end_month + "/" + end_day;

        if (window.XMLHttpRequest)
        {// code for IE7+, Firefox, Chrome, Opera, Safari
            xmlhttp = new XMLHttpRequest();
        }
        else
        {// code for IE6, IE5
            xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
        }
//        document.getElementById("save").innerHTML = "saving...";
        xmlhttp.onreadystatechange = function()
        {
            if (xmlhttp.readyState == 4 && xmlhttp.status == 200)
            {
                document.getElementById("save").innerHTML = xmlhttp.responseText;
            } else {
                document.getElementById("save").innerHTML = 'Loading...';
            }
        }

        // xmlhttp.open("GET", "save_data_admob.php?store=" + storeId + "&method=" + method + "&game_id=" + gameId + "&start_date=" + start_date + "&end_date=" + end_date, true);
        xmlhttp.open("GET", "save_data_admob.php?method=" + method + "&start_date=" + start_date + "&end_date=" + end_date, true);
        xmlhttp.send();
    }
    function DownLoadDataAdmob()
    {
        var start_year = document.getElementById("start_year").value;
        var start_month = document.getElementById("start_month").value;
        var start_day = document.getElementById("start_day").value;
        var end_year = document.getElementById("end_year").value;
        var end_month = document.getElementById("end_month").value;
        var end_day = document.getElementById("end_day").value;
        var start_date = start_year + "/" + start_month + "/" + start_day;
        var end_date = end_year + "/" + end_month + "/" + end_day;

        open("save_data_admob.php?method=" + 2 + "&start_date=" + start_date + "&end_date=" + end_date, '_parent');
    }
    function LoadDataIAD(method, dataType)
    {
        // var cookie = document.getElementById("url");
        // cookie = cookie[cookie.selectedIndex].getAttribute('cookie');
        var start_year = document.getElementById("start_year").value;
        var start_month = document.getElementById("start_month").value;
        var start_day = document.getElementById("start_day").value;
        var end_year = document.getElementById("end_year").value;
        var end_month = document.getElementById("end_month").value;
        var end_day = document.getElementById("end_day").value;
        var start_date = start_year + "/" + start_month + "/" + start_day;
        var end_date = end_year + "/" + end_month + "/" + end_day;

        if (window.XMLHttpRequest)
        {// code for IE7+, Firefox, Chrome, Opera, Safari
            xmlhttp = new XMLHttpRequest();
        }
        else
        {// code for IE6, IE5
            xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
        }
        xmlhttp.onreadystatechange = function()
        {
            if (xmlhttp.readyState == 4 && xmlhttp.status == 200)
            {
                document.getElementById("save").innerHTML = xmlhttp.responseText;
            } else {
                document.getElementById("save").innerHTML = 'Loading...';
            }
        }
        // xmlhttp.open("GET", "save_data_iad.php?method=" + method + "&start_date=" + start_date + "&end_date=" + end_date + "&dataType=" + dataType + '&cookie=' + cookie, true);
        xmlhttp.open("GET", "save_data_iad.php?method=" + method + "&start_date=" + start_date + "&end_date=" + end_date + "&dataType=" + dataType, true);
        xmlhttp.send();
    }
    function DownLoadDataIAD(dataType)
    {
        // var cookie = document.getElementById("url");
        // cookie = cookie[cookie.selectedIndex].getAttribute('cookie');
        var start_year = document.getElementById("start_year").value;
        var start_month = document.getElementById("start_month").value;
        var start_day = document.getElementById("start_day").value;
        var end_year = document.getElementById("end_year").value;
        var end_month = document.getElementById("end_month").value;
        var end_day = document.getElementById("end_day").value;
        var start_date = start_year + "/" + start_month + "/" + start_day;
        var end_date = end_year + "/" + end_month + "/" + end_day;

        // open("save_data_iad.php?method=" + 2 + "&start_date=" + start_date + "&end_date=" + end_date + "&dataType=" + dataType + '&cookie=' + cookie, '_parent');
        open("save_data_iad.php?method=" + 2 + "&start_date=" + start_date + "&end_date=" + end_date + "&dataType=" + dataType, '_parent');
    }
	function LoadDataIOS(method)
    {
        // var Dom = YAHOO.util.Dom,
        // Json = YAHOO.lang.JSON;
        // var cookie = document.getElementById("url");
        // cookie = cookie[cookie.selectedIndex].getAttribute('cookie');
        // var storeId = document.getElementById('store').value;
        // var gameId = document.getElementById('game_id').value;
        // var date_type = document.getElementById('date_type').value;
        // var url = document.getElementById("url").value;
        var link_title = document.getElementById("url").value;
        var type = document.getElementById("url").selectedOptions[0].index;
        // $x('//select[@id="start_year"]/option[@selected="selected"]/@value');
        var start_year = document.getElementById("start_year").value;
        var start_month = document.getElementById("start_month").value;
        var start_day = document.getElementById("start_day").value;
        var end_year = document.getElementById("end_year").value;
        var end_month = document.getElementById("end_month").value;
        var end_day = document.getElementById("end_day").value;
        var start_date = start_year + "%2F" + start_month + "%2F" + start_day;
        var end_date = end_year + "%2F" + end_month + "%2F" + end_day;
        if (window.XMLHttpRequest)
        {// code for IE7+, Firefox, Chrome, Opera, Safari
            xmlhttp = new XMLHttpRequest();
        }
        else
        {// code for IE6, IE5
            xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
        }
        xmlhttp.onreadystatechange = function()
        {
            if (xmlhttp.readyState == 4 && xmlhttp.status == 200)
            {
                document.getElementById("save").innerHTML = xmlhttp.responseText;
            } else {
                document.getElementById("save").innerHTML = 'Loading...';
            }
        }
        xmlhttp.open("GET", "auto_save_data.php?method=" + method + "&start_date=" + start_date + "&end_date=" + end_date + "&store_type=" + link_title + "&type=" + type, true);
        xmlhttp.send();
    }
/* 	var getCookies = function(){
		var pairs = document.cookie.split(";");
		var cookies = {};
		for (var i=0; i<pairs.length; i++){
			var pair = pairs[i].split("=");
			cookies[pair[0]] = unescape(pair[1]);
		}
		return cookies;
	}
	var myCookies = getCookies();
	alert(myCookies.secret); // "do not tell you"   */
</script>

