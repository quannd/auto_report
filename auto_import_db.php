<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
//require_once 'MY_ControllerAdmin.php';
//require_once '../core/MY_Controller.php';

/**
 * Description of schedule
 *
 * @author dhearts
 */
include('dbconnect.php');
connect_db();
$_post_args = array();
$_get_args = array();
$_post_args = getAllPostParams();
/* $result = Array(
    "retval" => false,
    "error_code" => 0,
    "error_msg" => "Restricted zone"
); */
$json = array();
init_result($json);
//if (post('method_id') == 1) {
if (post('file_path') != NULL) {
    import_db();
} else {
    // echo json_encode($result);
	echo_result($json, -2, 'Restricted zone!');
}

//class auto_import_db {
//
//    //put your code here
//	private $_get_args = array();
//    private $_post_args = array();
//
//    public function __construct() {
//        parent::__construct();
//        // add for get method Mieng add
//        // _get_args = array_merge(_get_args, uri->ruri_to_assoc());
//        _post_args = getAllPostParams();
//		include_once("dbconnect.php");
//	}
/**
 * Default view
 * @since 2012/02/01
 */
function index() {
   /*  $result = Array(
        "retval" => false,
        "error_code" => 0,
        "error_msg" => "Restricted zone"
    ); */
    if (post('file_path') != NULL) {
        import_db();
    } else {
        // echo json_encode($result);
		echo_result($json, -2, 'Restricted zone!');
    }
// exit;
}

function import_db() {
//        $file_path, $table, $column = "", $set_value = ""
//        if (checkLogin() == false) {
//            return;
//        }
//        $file_path, $table, $column, $set_value
//        $file_path = '/root/report/salesreport_201503.csv';
//        $date = date('Ym');
//        $date = date("F j, Y", strtotime( '-1 days' ));
    $file_path = post_unencrypt('file_path');
    $delimiter = post_unencrypt('delimiter');
    $table = post_unencrypt('table');
    $column = post_unencrypt('column');
    $set_value = post_unencrypt('set_value');
    $increment = post_unencrypt('increment');

    if ($delimiter == NULL)
        $delimiter = ',';
    else
        $delimiter = urldecode($delimiter);
//        $date = date("Ym", strtotime('-1 days'));
//        $file_path = "/var/report/". $file_name ."_". $date .".csv";
//        $file_path = $file_path . $date . ".csv";
    $file_path = urldecode($file_path);
    $table = urldecode($table);
    $column = urldecode($column);
    $set_value = urldecode($set_value);
    if ($increment != NULL) {
        $increment = urldecode($increment);
        $set_value = str_replace('ABCDEFGH', '+', $set_value);
    }
//        $table = 'google_paid_bill';
//        $column = 't_order_number,t_order_charged_date,t_order_charged_time,t_finance_state,t_device,t_product_title,t_product_id,t_product_type,t_sku_id,t_currency,@t_item_price,t_tax,@t_charged_amount,t_buyer_city,t_buyer_state,t_postal_code,t_country';
//        $set_value = "SET t_item_price = REPLACE(@t_item_price,',',''), t_charged_amount = REPLACE(@t_charged_amount,',','')";
    $error = array();
    try {
        if ($increment != NULL) {
            $sql = $increment;
            query($sql, $error);
        }
        // $file_path = str_replace('.', "\\.", $file_path);
        $sql = "LOAD DATA LOCAL INFILE '" . $file_path . "' 
                 IGNORE INTO TABLE {$table}  
                 FIELDS TERMINATED BY '{$delimiter}' 
                 ENCLOSED BY '\"' LINES TERMINATED BY '\\n' 
                 IGNORE 1 LINES ({$column}) 
                {$set_value}";
 // echo $sql;
        $isIntOK = query($sql, $error);
        if ($isIntOK) {
            /* $result = Array(
                "retval" => TRUE,
                "error_code" => 0,
                "data" => 'OK'
            );
            echo json_encode($result); */
			echo_result($json, 0, 'OK');
        } else {
            /* $result = Array(
                "retval" => FALSE,
                "error_code" => 1,
                "error_msg" => $error['error']
            );
            echo json_encode($result); */
			echo_result($json, 1, $error['error']);
            writeLogSql($error['error'], $sql, 'auto_import_db.php');
        }
    } catch (Exception $ex) {
        /* $result = Array(
            "retval" => FALSE,
            "error_code" => -1,
            "error_msg" => "DB Exception! Error SQL Import DB! " . $ex->getMessage()
        ); 
        echo json_encode($result);*/
		echo_result($json, -1, "DB Exception! Error SQL Import DB! " . $ex->getMessage());
        writeLogSql($ex->getMessage(), $sql, 'auto_import_db.php');
    }
}

function post($key = NULL) {
    global $_post_args;
    if ($key === NULL) {
//           return _post_args_crypt;
        return $_post_args;
    }
//        return array_key_exists($key, _post_args_crypt) ? _post_args_crypt[$key] : false;
    return array_key_exists($key, $_post_args) ? $_post_args[$key] : FALSE;
}

//QuanND 2014-05-14 unencrypt post
function post_unencrypt($key = NULL) {
    global $_post_args;
    if ($key === NULL) {
        return $_post_args;
    }
    return array_key_exists($key, $_post_args) ? $_post_args[$key] : FALSE;
}

function getAllPostParams() {
    return $_POST;
}

/**
 *
 * @param <type> $paramName
 * @return <type> 
 */
function getPostParam($key) {
    if (isset($_POST[$key])) {
        return $_POST[$key];
    }
    return null;
}

/**
 *
 * @param <type> $key
 * @return <type> 
 */
function getParam($key) {
    if (isset($_GET[$key])) {
        return $_GET[$key];
    }
}
?>
 