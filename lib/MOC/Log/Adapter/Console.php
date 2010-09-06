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
				$message = sprintf('::WHITE::[::NONE::::BOLD::DEBUG::BOLD::::WHITE::]::NONE::  %s', $message);
				break;
			case LOG_INFO:
				$message = sprintf('::WHITE::[::BLUE::::BOLD::INFO::WHITE::]::NONE::   %s', $message);
				break;
			case LOG_NOTICE:
				$message = sprintf('::WHITE::[::GREEN::::BOLD::NOTICE::WHITE::]::NONE:: %s', $message);
				break;
			case LOG_WARNING:
				$message = sprintf('::WHITE::[::YELLOW::::BOLD::WARNING::WHITE::]::NONE:: %s', $message);
				break;
			case LOG_ALERT:
				$message = sprintf('::WHITE::[::RED::::BOLD::ALERT::WHITE::]::NONE:: %s', $message);
				break;
			case LOG_ERR:
				$message = sprintf('::WHITE::[::RED::::BOLD::ERROR::WHITE::]::NONE:: %s', $message);
				break;
		}

		$colorize = $this->options['colorize'];
		$message = MOC_Log_Color::colorize($message, $colorize);

		echo $message . "\n";
		unset($message);
	}
}
