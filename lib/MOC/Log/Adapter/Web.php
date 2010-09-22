<?php
class MOC_Log_Adapter_Web extends MOC_Log_Adapter_Abstract {
	protected static $logs = array();

    public function add($severity, $message, $ext_key = null, $additional_info = null) {
    	self::$logs[] = compact('severity', 'message', 'ext_key', 'additional_info');
	}

	public function display() {
		$str = "<table><tr><th>Severity</th><th>Message</th><th>Ext</th></tr>";
		foreach (self::$logs as $log) {
			switch ($log['severity']) {
	            case LOG_DEBUG:
	                $message = 'DEBUG';
	                break;
	            case LOG_INFO:
	                $message = 'INFO';
	                break;
	            case LOG_NOTICE:
	                $message = 'NOTICE';
	                break;
	            case LOG_WARNING:
	                $message = 'WARNING';
	                break;
	            case LOG_ALERT:
	                $message = 'ALERT';
	                break;
	            case LOG_ERR:
	                $message = 'ERROR';
	                break;
	        }
			$str .= '<tr><td>' . $message . '</td><td>' . $log['message'] . '</td><td>' . $log['ext_key'] . '</td></tr>';
		}
		$str .= '</table>';

		echo $str;
	}
}