<?php

# FileName="Connection_php_mysql.htm"
# Type="MYSQL"
# HTTP="true"
// $hostname_db = "localhost";
/*
 * ------------------------------------------------------
 *  Load the framework constants
 * ------------------------------------------------------
 */
define('APPPATH', '');
define('BASEPATH', DIRECTORY_SEPARATOR === '/' ? dirname(__FILE__) . '/': '');
define('ENVIRONMENT', 'development');
// define('ENVIRONMENT', 'production');
if (defined('ENVIRONMENT') AND file_exists(APPPATH.'config/'.ENVIRONMENT.'/constants.php'))
{
    require(APPPATH.'config/'.ENVIRONMENT.'/constants.php');
}
else
{
    require(APPPATH.'config/constants.php');
}
include('Common.php');
@include('catch_fatal_error.php');
function connect_db() {
    if (!isset($GLOBALS['connect']) || $GLOBALS['connect'] == NULL) {
        // $hostname_db = "192.168.0.170";
        $hostname_db = "localhost";
        // $database_db = "monster_test";
        $database_db = "statistics";
        $username_db = "mmd";
        $password_db = "Mmdd`1!@";

        global $connect;
        $connect = mysqli_init();
        if (!$connect) {
            die('mysqli_init failed');
        }
        if (!mysqli_options($connect, MYSQLI_OPT_LOCAL_INFILE, true)) {
            die('Setting MYSQLI_OPT_LOCAL_INFILE failed');
        }
        // else{
        // echo "Ok to connect to MySQL!" ;
        // }
        // $connect = mysql_connect($hostname_db, $username_db, $password_db) or trigger_error(mysql_error(),E_USER_ERROR); 
        // mysql_select_db($database_db, $connect);
        $ret = mysqli_real_connect($connect, $hostname_db, $username_db, $password_db, $database_db);
        // $connect = mysqli_connect($hostname_db, $username_db, $password_db, $database_db);
        if (!$ret) {
            die('Connect Error (' . mysqli_connect_errno() . ') ' . mysqli_connect_error());
        }
        // var_dump($connect);
        // mysqli_set_charset($connect,'utf8');
        mysqli_set_charset($connect, 'utf8');
        // $GLOBALS['connect'] = $connect;
        $GLOBALS['connect'] = $connect;
        /* $connect = mysqli_connect($hostname_db, $username_db, $password_db, $database_db);
          // Check connection
          //  or trigger_error(mysqli_error($connect),E_USER_ERROR);
          if (mysqli_connect_errno())
          {
          echo "Failed to connect to MySQL: " . mysqli_connect_error();
          }else{
          echo "Ok to connect to MySQL!" ;
          } */
        // mysqli_select_db($connect, $database_db);
        // Set autocommit to off
        mysqli_autocommit($connect, FALSE);
    }
}

function select($query, &$result) {
    try {
        global $connect;
        $mysqli_result = mysqli_query($connect, $query);
        // or die(mysqli_error($connect));
        if ($mysqli_result) {
             while($row = $mysqli_result->fetch_array()){
              $tmp_result[] = $row;
            }
            $result = $tmp_result;
           /*// Fetch all for PHP>=5.4
            $result = mysqli_fetch_all($mysqli_result, MYSQLI_BOTH); */

            // Free result set
            mysqli_free_result($mysqli_result);
            // Commit transaction
            mysqli_commit($connect);
            return 1;
        } else {
           /*  if (SHOW_SQL_ERROR){
                // echo mysqli_error($connect);
                $json = array();
                echo_result($json, -2, mysqli_error($connect));
            } */
            return 0;
        }
    } catch (ErrorException $e) {
        if (SHOW_SQL_ERROR){
            // echo $e->getMessage();
            $json = array();
            echo_result($json, -2, $e->getMessage());
        }
        mysqli_close($connect);
        log_message('Error', " \t " . basename(__FILE__) . " : \t" . $e->getMessage());
        return -1;
    }
}

function query($query, &$error = NULL) {
    try {
        global $connect;
        $mysqli_result = mysqli_query($connect, $query);
        // or die(mysqli_error($connect));
        if ($mysqli_result) {
            /* // Fetch all
              $result = mysqli_fetch_all($mysqli_result, MYSQLI_BOTH);
              if(is_array($result) && count($result) > 0){
              // Free result set
              mysqli_free_result($mysqli_result);
              } */
            // Commit transaction
            mysqli_commit($connect);
            return 1;
        } else {
            $error['error'] = mysqli_error($connect);
            /* if (SHOW_SQL_ERROR){
                // echo $error['error'];
                $json = array();
                echo_result($json, -2, $error['error']);
            } */
            return 0;
        }
    } catch (ErrorException $e) {
        $error['error'] = $e->getMessage();
        if (SHOW_SQL_ERROR){
            // echo $error['error'];
            $json = array();
            echo_result($json, -1, $error['error']);
        }
        mysqli_close($connect);
        log_message('Error', " \t " . basename(__FILE__) . " : \t" . $e->getMessage());
        return -1;
    }
}
function getStoreId($storeType, &$result) {
    try {
            if (empty($storeType))
            return 0;
            $query = "SELECT store_id FROM m_store
            WHERE name like '{$storeType}%'";
            return select($query, $result);
        } catch (ErrorException $e) {
            log_message('Error', " \t " . basename(__FILE__) . " : \t" . $e->getMessage());
    }
}

function getGameId($gameType, &$result) {
    try {
            if (empty($gameType))
            return 0;
            $query = "SELECT game_id FROM m_game
            WHERE name like '{$gameType}%'";
            return select($query, $result);
        } catch (ErrorException $e) {
            log_message('Error', " \t " . basename(__FILE__) . " : \t" . $e->getMessage());
    }
}

function getLink(&$result, $store_name, $report_type = '') {
    try {
            if (empty($store_name))
            return 0;
            $query = "SELECT link_type, link_url, cookie FROM m_link 
            WHERE link_title like '{$store_name}%' ";
            if($report_type !== ''){
                $query .= " AND link_title like '%{$report_type}%'";
            }
            return select($query, $result);
        } catch (ErrorException $e) {
            log_message('Error', " \t " . basename(__FILE__) . " : \t" . $e->getMessage());
    }
}
?>     