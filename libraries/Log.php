<?php 
/**
 * CodeIgniter
 *
 * An open source application development framework for PHP 5.1.6 or newer
 *
 * @package		CodeIgniter
 * @author		ExpressionEngine Dev Team
 * @copyright	Copyright (c) 2008 - 2014, EllisLab, Inc.
 * @license		http://codeigniter.com/user_guide/license.html
 * @link		http://codeigniter.com
 * @since		Version 1.0
 * @filesource
 */

// ------------------------------------------------------------------------

/**
 * Logging Class
 *
 * @package		CodeIgniter
 * @subpackage	Libraries
 * @category	Logging
 * @author		ExpressionEngine Dev Team
 * @link		http://codeigniter.com/user_guide/general/errors.html
 */
class CI_Log {

    protected $_log_path;
    protected $_threshold	= 1;
    protected $_date_fmt	= LOG_DATE_FORMAT;
    protected $_enabled	= TRUE;
    protected $_levels	= array('ERROR' => '1', 'DEBUG' => '2',  'INFO' => '3', 'ALL' => '4');

    /**
     * Constructor
     */
    public function __construct()
    {
 // If we're on a Unix server
        $this->_log_path = (DIRECTORY_SEPARATOR === '/' ? BASEPATH . LOG_FOLDER : LOG_FOLDER);

        if ( ! is_dir($this->_log_path) OR ! is_really_writable($this->_log_path))
        {
            $this->_enabled = FALSE;
        }

        if (is_numeric(LOG_ERROR_LEVEL))
        {
            $this->_threshold = LOG_ERROR_LEVEL;
        }

        // if ($config['log_date_format'] != '')
        // {
        // $this->_date_fmt = LOG_DATE_FORMAT;
        // }
    }

    // --------------------------------------------------------------------

    /**
     * Write Log File
     *
     * Generally this function will be called using the global log_message() function
     *
     * @param	string	the error level
     * @param	string	the error message
     * @param	bool	whether the error is a native PHP error
     * @return	bool
     */
    public function write_log($level = 'error', $msg, $php_error = FALSE)
    {
        if ($this->_enabled === FALSE)
        {
            return FALSE;
        }

        $level = strtoupper($level);

        if ( ! isset($this->_levels[$level]) OR ($this->_levels[$level] > $this->_threshold))
        {
            return FALSE;
        }

        $filepath = $this->_log_path . "log_php_" . date('Y_m_d') . ".txt";

        if ( ! $fp = @fopen($filepath, FOPEN_WRITE_CREATE))
        {
            return FALSE;
        }

        $message = date($this->_date_fmt) . "\t : " . $level . " : \t" . $msg . "\n";

		flock($fp, LOCK_EX);
		fwrite($fp, $message);
		flock($fp, LOCK_UN);
		fclose($fp);
		
		@chmod($filepath, FILE_WRITE_MODE);
        return TRUE;
    }

}
// END Log Class

/* End of file Log.php */
/* Location: ./system/libraries/Log.php */
?> 