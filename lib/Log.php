<?php
require_once BASEPATH.'config/constants.php';

/**
 * Logging Class
 * @subpackage  Libraries
 * @category    Logging
 * @link
 */

class Log {

    var $log_path = '../logs/';
    var $_threshold = 4;
    var $_date_fmt  = 'Y-m-d H:i:s';
    var $_enabled   = TRUE;
    var $_levels    = array('ERROR' => '1', 'DEBUG' => '2',  'INFO' => '3', 'ALL' => '4');

    /**
     * Constructor
     *
     * @access  public
     */
    function Log()
    {
        if( defined('LOG_PATH') )
        {
            $this->log_path = LOG_PATH;
        }
        if ( ! is_dir($this->log_path))
        {
            $this->_enabled = FALSE;
        }
        if ( defined('LOG_DATE_FORMAT') )
        {
            $this->_date_fmt = LOG_DATE_FORMAT;
        }
    }
    /**
     * Write Log File
     *
     * Generally this function will be called using the global log_message() function
     *
     * @access  public
     * @param   string  the error level
     * @param   string  the error message
     * @param   bool    whether the error is a native PHP error
     * @return  bool
     */
    function message($level = 'error', $msg, $php_error = FALSE)
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
        $filepath = $this->log_path.'log-'.date('Y-m-d').'.log';
        $message  = '';
        if ( ! $fp = @fopen($filepath, FOPEN_WRITE_CREATE))
        {
            return FALSE;
        }
        $message .= $level.' '.(($level == 'INFO') ? ' -' : '-').' '.date($this->_date_fmt). ' --> '.$msg."\n";
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
