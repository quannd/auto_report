<?php
register_shutdown_function('fatal_handler');

set_error_handler('handler');

// Set Output Buffer Enable for Error Catch Process
ob_start();

//Function to catch no user error handler function errors...
function fatal_handler(){

    $error = error_get_last();

    if($error && ($error['type'] & E_FATAL)){
        handler($error['type'], $error['message'], $error['file'], $error['line']);
    }

}

function handler( $errno, $errstr, $errfile, $errline ) {

    switch ($errno){

        case E_ERROR: // 1 //
            $typestr = 'E_ERROR'; break;
        case E_WARNING: // 2 //
            $typestr = 'E_WARNING'; break;
        case E_PARSE: // 4 //
            $typestr = 'E_PARSE'; break;
        case E_NOTICE: // 8 //
            $typestr = 'E_NOTICE'; break;
        case E_CORE_ERROR: // 16 //
            $typestr = 'E_CORE_ERROR'; break;
        case E_CORE_WARNING: // 32 //
            $typestr = 'E_CORE_WARNING'; break;
        case E_COMPILE_ERROR: // 64 //
            $typestr = 'E_COMPILE_ERROR'; break;
        case E_CORE_WARNING: // 128 //
            $typestr = 'E_COMPILE_WARNING'; break;
        case E_USER_ERROR: // 256 //
            $typestr = 'E_USER_ERROR'; break;
        case E_USER_WARNING: // 512 //
            $typestr = 'E_USER_WARNING'; break;
        case E_USER_NOTICE: // 1024 //
            $typestr = 'E_USER_NOTICE'; break;
        case E_STRICT: // 2048 //
            $typestr = 'E_STRICT'; break;
        case E_RECOVERABLE_ERROR: // 4096 //
            $typestr = 'E_RECOVERABLE_ERROR'; break;
        case E_DEPRECATED: // 8192 //
            $typestr = 'E_DEPRECATED'; break;
        case E_USER_DEPRECATED: // 16384 //
            $typestr = 'E_USER_DEPRECATED'; break;

    }

    $message_html = '<b>'.$typestr.': </b>'.$errstr.' in <b>'.$errfile.'</b> on line <b>'.$errline.'</b><br/>';
    $message = $typestr." : \t" . $errfile . ' on line '. $errline . " : \t". $errstr;

    if(($errno & E_FATAL) && ENVIRONMENT === 'production'){

        header('Location: 500.html');
        header('Status: 500 Internal Server Error');

    }

    if(!($errno & ERROR_REPORTING))
        return;

    /* if(DISPLAY_ERRORS){
        // printf('%s', $message_html);
        $json = array();
        echo_result($json, -1, $message);
    } */

    //Logging error on php file error log...
    if(LOG_ERRORS){
        // error_log(strip_tags($message), 0);
        // log_message('error', $message);
        writeLogPhp($message, 'ERROR');
        // error_log("ERROR LOG MAIL TEST", 1, "quan.nguyenduc@gmail.com", "Subject: Foo\nFrom: ERROR LOG MAIL TEST\n");
    }

}
function writeLogSql($error, $sql = "", $title = '') {
    if (@is_dir(LOG_FOLDER) == false) {
        @mkdir(LOG_FOLDER, "0777");
    }
     // If we're on a Unix server
    $pathFile = (DIRECTORY_SEPARATOR === '/' ? BASEPATH . LOG_FOLDER : LOG_FOLDER) . "sqlList_" . date('Y_m_d') . ".txt";
    $ourFileHandle = @fopen($pathFile, 'a+');
    @fwrite($ourFileHandle, date(LOG_DATE_FORMAT) . "\t : " . $title . " : \t" . $error . "\n");
    if ($sql !== "") {
        @fwrite($ourFileHandle, date(LOG_DATE_FORMAT) . "\t : " . "SQL Query : \t" . $sql . "\n");
    }
    @fclose($ourFileHandle);
}
function writeLogPhp($error, $title = '') {
    if (@is_dir(LOG_FOLDER) == false) {
        @mkdir(LOG_FOLDER, "0777");
    }
     // If we're on a Unix server
    $filepath = (DIRECTORY_SEPARATOR === '/' ? BASEPATH . LOG_FOLDER : LOG_FOLDER) . "log_php_" . date('Y_m_d') . ".txt";
    $message = date(LOG_DATE_FORMAT) . "\t : " . $title . " : \t" . $error . "\n";
    if ( ! $fp = @fopen($filepath, FOPEN_WRITE_CREATE))
    {
        return FALSE;
    }
    
    flock($fp, LOCK_EX);
    fwrite($fp, $message);
    flock($fp, LOCK_UN);
    fclose($fp);
    
    @chmod($filepath, FILE_WRITE_MODE);
}
// ------------------------------------------------------------------------

/**
 * Error Logging Interface
    *
 * We use this as a simple mechanism to access the logging
 * class and send messages to be logged.
    *
 * @access	public
 * @return	void
 */
if (!function_exists('log_message')) {
    
    function log_message($level = 'error', $message, $php_error = FALSE) {
        static $_log;
        
        if (LOG_ERROR_LEVEL == 0) {
            return;
        }
        
        $_log = & load_class('Log');
        $_log->write_log($level, $message, $php_error);
    }
    
}
// ------------------------------------------------------------------------

/**
 * Class registry
    *
 * This function acts as a singleton.  If the requested class does not
 * exist it is instantiated and set to a static variable.  If it has
 * previously been instantiated the variable is returned.
    *
 * @access	public
 * @param	string	the class name being requested
 * @param	string	the directory where the class should be found
 * @param	string	the class name prefix
 * @return	object
 */
if (!function_exists('load_class')) {
    
    function &load_class($class, $directory = 'libraries', $prefix = 'CI_') {
        static $_classes = array();
        
        // Does the class exist?  If so, we're done...
        if (isset($_classes[$class])) {
            return $_classes[$class];
        }
        
        $name = FALSE;
        
        // Look for the class first in the local application/libraries folder
        // then in the native system/libraries folder
        foreach (array(APPPATH, BASEPATH) as $path) {
            if (file_exists($path . $directory . '/' . $class . '.php')) {
                $name = $prefix . $class;
                
                if (class_exists($name) === FALSE) {
                    require($path . $directory . '/' . $class . '.php');
                }
                
                break;
            }
        }
        
        /*    // Is the request a class extension?  If so we load it too
            if (file_exists(APPPATH.$directory.'/'.config_item('subclass_prefix').$class.'.php'))
            {
            $name = config_item('subclass_prefix').$class;
            
            if (class_exists($name) === FALSE)
            {
            require(APPPATH.$directory.'/'.config_item('subclass_prefix').$class.'.php');
            }
        } */
     
        // Did we find the class?
        if ($name === FALSE) {
            // Note: We use exit() rather then show_error() in order to avoid a
            // self-referencing loop with the Excptions class
            exit('Unable to locate the specified class: ' . $class . '.php');
        }
        
        // Keep track of what we just loaded
        is_loaded($class);
        
        $_classes[$class] = new $name();
        return $_classes[$class];
    }
    
}

// --------------------------------------------------------------------

/**
 * Keeps track of which libraries have been loaded.  This function is
 * called by the load_class() function above
    *
 * @access	public
 * @return	array
 */
if (!function_exists('is_loaded')) {
    
    function &is_loaded($class = '') {
        static $_is_loaded = array();
        
        if ($class != '') {
            $_is_loaded[strtolower($class)] = $class;
        }
        
        return $_is_loaded;
    }
    
}

// ------------------------------------------------------------------------
/**
 * Tests for file writability
    *
 * is_writable() returns TRUE on Windows servers when you really can't write to
 * the file, based on the read-only attribute.  is_writable() is also unreliable
 * on Unix servers if safe_mode is on.
    *
 * @access	private
 * @return	void
 */
if ( ! function_exists('is_really_writable'))
{
    function is_really_writable($file)
    {
        // If we're on a Unix server with safe_mode off we call is_writable
        if (DIRECTORY_SEPARATOR == '/' AND @ini_get("safe_mode") == FALSE)
        {
            return is_writable($file);
        }
        
        // For windows servers and safe_mode "on" installations we'll actually
        // write a file then read it.  Bah...
        if (is_dir($file))
        {
            $file = rtrim($file, '/').'/'.md5(mt_rand(1,100).mt_rand(1,100));
            
            if (($fp = @fopen($file, FOPEN_WRITE_CREATE)) === FALSE)
            {
                return FALSE;
            }
            
            fclose($fp);
            @chmod($file, DIR_WRITE_MODE);
            @unlink($file);
            return TRUE;
        }
        elseif ( ! is_file($file) OR ($fp = @fopen($file, FOPEN_WRITE_CREATE)) === FALSE)
        {
            return FALSE;
        }
        
        fclose($fp);
        return TRUE;
    }
}
// ------------------------------------------------------------------------
?>  