<?php
/**
 * Google translate helper
 *
 * @author Christian Winther <cwin@mocsystems.com>
 * @since 27.08.2010
 * @version 1.0
 */
class MOC_Google_Translate {

	/**
	 * Amount of curl requests until the script sleeps
	 * every \$throttle_sleep seconds
	 *
	 * @var integer
	 */
	public static $throttle_rate = 25;

	/**
	 * Number of seconds the script sleeps each time
	 * the \$throttle_rate has been reached
	 *
	 * @var integer
	 */
	public static $throttle_sleep = 2;

	/**
	 * List of ips to "randomly" switch between
	 * each request.
	 * If left empty, this feature will not be used, and
	 * cURL will just select the default interface
	 *
	 * @var array
	 */
	public static $ips = array();

	/**
	 * A list of custom cURL options
	 *
	 * @var array
	 */
	public static $CURL_OPTIONS = array();

	/**
	 * An optional google translate API key
	 *
	 * Will help you improve query rates
	 *
	 * @var string
	 */
	public static $API_KEY;

	/**
	 * In case google cannot guess the source languages,
	 * the default source language will be used in place
	 *
	 * @var string
	 */
	public static $DEFAULT_SOURCE_LANGUAGE = 'da';

	/**
	 * Number of queries send to google translate
	 *
 	 * @var integer
	 */
	protected static $hits = 0;

	/**
	 * A cached cURL handle
	 *
	 * @var object
	 */
	protected static $curl_handle;

	/**
	 * Translate a term to one or more languages
	 *
	 * @param string 		$term 	The text to translate
	 * @param string|array 	$to 	One or multiple languages to translate the term into
	 * @param string 		$from   The language the term is written in, leave empty for google to detect
	 * @return string|array 		Return format depends on $to data type
	 */
	public static function lookup($term, $to, $from = '') {
		$multiple = is_array($to);

		if (!is_array($to)) {
			$to = array($to);
		}

		$list = array();
		foreach ($to as $target) {
			$list[$target] = self::query($term, $target, $from);
		}

		return $multiple ? $list : array_pop($list);
	}

	/**
	 * The query builder for google translate API
	 *
 	 * @param string 		$term 	The text to translate
	 * @param string	 	$to 	The language to translate the term into
	 * @param string 		$from   The language the term is written in, leave empty for google to detect
	 * @return string		 		Return format depends on $to data type
	 */
   public static function query($term, $to, $from = '') {
 		self::$hits++;
		if ((self::$hits % self::$throttle_rate) === 0) {
			MOC_Log::add(LOG_NOTICE, sprintf('Google translate throttle, sleeping for %d second(s)', self::$throttle_sleep));
			sleep(self::$throttle_sleep);
		}

		if (empty(self::$curl_handle)) {
			MOC_Log::add(LOG_NOTICE, 'No curl handle available, creating one');
			self::$curl_handle = curl_init('http://ajax.googleapis.com/ajax/services/language/translate');
			curl_setopt(self::$curl_handle, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt(self::$curl_handle, CURLOPT_FORBID_REUSE, 0);
			curl_setopt(self::$curl_handle, CURLOPT_FRESH_CONNECT, 0);
			curl_setopt(self::$curl_handle, CURLOPT_MAXCONNECTS, (self::$throttle_rate * 5));
		}

		if (!empty(self::$ips)) {
			$ip = self::$ips[array_rand(self::$ips)];
			MOC_Log::add(LOG_NOTICE, sprintf('Google translate - using cURL interface "%s"', $ip));
			curl_setopt(self::$curl_handle, CURLOPT_INTERFACE, $ip);
		}

		if (!empty(self::$CURL_OPTIONS)) {
			curl_setopt_array(self::$curl_handle, self::$CURL_OPTIONS);
		}

		// We need to use POST to avoid long query strings!
		curl_setopt(self::$curl_handle, CURLOPT_POSTFIELDS, array(
			'v'			=> '1.0',
			'key'		=> self::$API_KEY,
			'q' 		=> utf8_encode($term),
			'langpair' 	=> sprintf('%s|%s', $from, $to)
		));

		$content = curl_exec(self::$curl_handle);
   		$content = json_decode($content, true);

		if (!is_array($content)) {
			var_dump($content);
			throw new MOC_Exception('Google Translate did not return valid json: ' . $content);
		}

		switch ($content['responseStatus']) {
			case 200:
				return utf8_decode($content['responseData']['translatedText']);
		    default:
				// Retry with DEFAULT_SOURCE_LANGUAGE if google can't guess language
				if (empty($from) && ($content['responseDetails'] === 'could not reliably detect source language')) {
					return self::query($term, $to, self::$DEFAULT_SOURCE_LANGUAGE);
				}
				// Fatal error: Google bailed on us :(
				throw new MOC_Exception($content['responseDetails']);
		}
	}
}