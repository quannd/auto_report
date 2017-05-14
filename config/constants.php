<?php

define('APPOTA_PPD', 0);
define('APPOTA_EARNING', 1);
define('APPOTA_DOWNLOAD', 2);
define('ADMOB', 3);
define('IAD', 4);
define('ISALES', 5); //IOS Sales & Trends Report
define('MONTH_TIME_SECOND', 24*3600*31);
/*
    |--------------------------------------------------------------------------
    | Error Logging Threshold
    |--------------------------------------------------------------------------
    |
    | If you have enabled error logging, you can set an error threshold to
    | determine what gets logged. Threshold options are:
    | You can enable error logging by setting a threshold over zero. The
    | threshold determines what gets logged. Threshold options are:
    |
    |	0 = Disables logging, Error logging TURNED OFF
    |	1 = Error Messages (including PHP errors)
    |	2 = Debug Messages
    |	3 = Informational Messages
    |	4 = All Messages
    |
    | For a live site you'll usually only enable Errors (1) to be logged otherwise
    | your log files will fill up very fast.
    |
 */
define('LOG_FOLDER', 'log/');
define('LOG_DATE_FORMAT', "Y-m-d H:i:s");
// LOG_ERROR_LEVEL > 2 => NOT SHOW EVERY DATABASE SQL QUERY ERROR
define('LOG_ERROR_LEVEL', '4');

/*
 * ---------------------------------------------------------------
 * ERROR REPORTING
 * ---------------------------------------------------------------
    *
 * Different environments will require different levels of error reporting.
 * By default development will show errors but testing and live will hide them.
 */

define('E_FATAL',  E_ERROR | E_USER_ERROR | E_CORE_ERROR | 
        E_COMPILE_ERROR | E_RECOVERABLE_ERROR);
if (defined('ENVIRONMENT')) {
    switch (ENVIRONMENT) {
        case 'development':
        error_reporting(E_ALL);
        //Custom error handling vars
        if (defined('LOG_ERROR_LEVEL')){
            switch (LOG_ERROR_LEVEL) {
                case 0: 
                define('E_FATAL',  '');
                define('ERROR_REPORTING', '');
                define('DISPLAY_ERRORS', FALSE);
				define('SHOW_SQL_ERROR', FALSE);
                break;
                
                case 1: 
                define('ERROR_REPORTING', E_FATAL | E_STRICT);
                define('DISPLAY_ERRORS', TRUE);
				define('SHOW_SQL_ERROR', TRUE);
                break;
                
                case 2: 
                define('ERROR_REPORTING',  E_FATAL | E_STRICT | E_NOTICE);
                define('DISPLAY_ERRORS', TRUE);
				define('SHOW_SQL_ERROR', TRUE);
                break;
                
                case 3:
                define('ERROR_REPORTING', E_FATAL | E_STRICT | E_PARSE | E_NOTICE | E_WARNING);
                define('DISPLAY_ERRORS', TRUE);
				define('SHOW_SQL_ERROR', FALSE);
                break;
                
                default:
                define('ERROR_REPORTING', E_ALL);
                define('DISPLAY_ERRORS', TRUE);
				define('SHOW_SQL_ERROR', FALSE);
                break;
            }
        }
        define('LOG_ERRORS', TRUE);
        break;
        
        case 'testing':
        case 'production':
        error_reporting(0);
        define('SHOW_SQL_ERROR', FALSE);
        //Custom error handling vars
        if (defined('LOG_ERROR_LEVEL')){
            switch (LOG_ERROR_LEVEL) {
                case 0: 
                define('E_FATAL',  '');
                break;
                
                case 1: 
                break;
                
                case 2: 
                break;
                
                default:
                break;
            }
        }
        define('ERROR_REPORTING', E_FATAL | E_STRICT);
        define('DISPLAY_ERRORS', FALSE);
        define('LOG_ERRORS', TRUE);
        break;
        
        default:
        exit('The application environment is not set correctly.');
    }
}
/*
|--------------------------------------------------------------------------
| File and Directory Modes
|--------------------------------------------------------------------------
|
| These prefs are used when checking and setting modes when working
| with the file system.  The defaults are fine on servers with proper
| security, but you may wish (or even need) to change the values in
| certain environments (Apache running a separate process for each
| user, PHP under CGI with Apache suEXEC, etc.).  Octal values should
| always be used to set the mode correctly.
|
*/
define('FILE_READ_MODE', 0644);
define('FILE_WRITE_MODE', 0666);
define('DIR_READ_MODE', 0755);
define('DIR_WRITE_MODE', 0777);

/*
|--------------------------------------------------------------------------
| File Stream Modes
|--------------------------------------------------------------------------
|
| These modes are used when working with fopen()/popen()
|
*/

define('FOPEN_READ',							'rb');
define('FOPEN_READ_WRITE',						'r+b');
define('FOPEN_WRITE_CREATE_DESTRUCTIVE',		'wb'); // truncates existing file data, use with care
define('FOPEN_READ_WRITE_CREATE_DESTRUCTIVE',	'w+b'); // truncates existing file data, use with care
define('FOPEN_WRITE_CREATE',					'ab');
define('FOPEN_READ_WRITE_CREATE',				'a+b');
define('FOPEN_WRITE_CREATE_STRICT',				'xb');
define('FOPEN_READ_WRITE_CREATE_STRICT',		'x+b');


/* End of file constants.php */
/* Location: ./application/config/constants.php */