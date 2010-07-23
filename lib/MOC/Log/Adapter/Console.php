<?php
/**
 * MOC Log Adepter Console
 * 
 * @author Christian Winther <cwin@mocsystems.com>
 * @version $Revision$
 * @modifiedby $LastChangedBy$
 * @lastmodified $Date$
 */
class MOC_Log_Adapter_Console extends MOC_Log_Adapter_Abstract {

    const COLOR_NONE = "\033[00m";

    const COLOR_BOLD = "\033[01m";

    const COLOR_UNDERSCORE = "\033[04m";

    const COLOR_BLINK = "\033[05m";

    const COLOR_REVERSE = "\033[07m";

    const COLOR_CONCEALED = "\033[08m";

    const COLOR_BLACK = "\033[30m";

    const COLOR_RED = "\033[31m";

    const COLOR_GREEN = "\033[32m";

    const COLOR_YELLOW = "\033[33m";

    const COLOR_BLUE = "\033[1;34m";

    const COLOR_MAGENTA = "\033[35m";

    const COLOR_CYAN = "\033[36m";

    const COLOR_WHITE = "\033[37m";

    const COLOR_GRAY = "\033[1;30m";

	public function initialize() {
		// Make sure to end output buffer!
		ob_end_flush();
		
		if (!array_key_exists('colorize', $this->options)) {
			// We are running from cron (env doesn't have a SSH_CLIENT value)
			if (!isset($_SERVER['SSH_CLIENT'])) {
				$colorize = false;
			} else {
				$Opts = new Zend_Console_Getopt(array(
		 			'moc_log_colorize|log_colorize=s' => 'Should logs output be colorized'
				));
				$colorize = MOC_Misc::evaluateBoolean($Opts->getOption('moc_log_colorize'));
			}
			
			$this->options['colorize'] = $colorize;
		}
	}

    /**
     * Add a new log event
     * 
     * @param integer $severity LOG_* or MOC_LOG constants
     * @param string $message
     * @param string $ext_key
     * @param mixed $additional_info String or variable that can be serialized
     */
    public function add($severity, $message, $ext_key = null, $additional_info = null) {
        switch ($severity) {
            case LOG_DEBUG:
                $message = sprintf('[::GRAY::DEBUG::NONE::] %s', $message);
                break;
            case LOG_INFO:
                $message = sprintf('[::BLUE::INFO::NONE::] %s', $message);
                break;
            case LOG_NOTICE:
                $message = sprintf('[::GREEN::NOTICE::NONE::] %s', $message);
                break;
            case LOG_WARNING:
                $message = sprintf('[::YELLOW::WARNING::NONE::] %s', $message);
                break;
            case LOG_ALERT:
                $message = sprintf('[::RED::ALERT::NONE::] %s', $message);
                break;
            case LOG_ERR:
                $message = sprintf('[::RED::ERROR::NONE::] %s', $message);
                break;
        }

		$colorize = $this->options['colorize'];

        if (0 !== preg_match_all('#::([a-z]+)::#sim', $message, $colors)) {
            for ($i = 0; $i < sizeof($colors[0]); $i++) {
				if ($colorize) {
                	$message = str_replace($colors[0][$i], constant('MOC_Log_Adapter_Console::COLOR_' . strtoupper($colors[1][$i])), $message);
            	} else {
					$message = str_replace($colors[0][$i], '', $message);
				}
			}
        }

        echo $message . "\n";
    }
}
