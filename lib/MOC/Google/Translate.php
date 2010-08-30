<?php
/**
 * Google translate helper
 *
 * @author Christian Winther <cwin@mocsystems.com>
 * @since 27.08.2010
 * @version 1.0
 */
class MOC_Google_Translate {

	public static $hit_rate = 25;

	public static $sleep_time = 2;

	public static $API_KEY;

	public static $DEFAULT_SOURCE_LANGUAGE = 'da';

	protected static $hits = 0;

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
   protected static function query($term, $to, $from = '') {
 		self::$hits++;
		if ((self::$hits % self::$hit_rate) === 0) {
			MOC_Log::add(LOG_NOTICE, sprintf('Google translate throttle, sleeping for %d second', self::$sleep_time));
			sleep(self::$sleep_time);
		}

		if (empty(self::$curl_handle)) {
			MOC_Log::add(LOG_NOTICE, 'No curl handle available, creating one');
			self::$curl_handle = curl_init('http://ajax.googleapis.com/ajax/services/language/translate');
			curl_setopt(self::$curl_handle, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt(self::$curl_handle, CURLOPT_FORBID_REUSE, 0);
			curl_setopt(self::$curl_handle, CURLOPT_FRESH_CONNECT, 0);
			curl_setopt(self::$curl_handle, CURLOPT_MAXCONNECTS, (self::$hit_rate * 5));
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
				if ($content['responseDetails'] === 'could not reliably detect source language') {
					return self::query($term, $to, self::$DEFAULT_SOURCE_LANGUAGE);
				}
				// Fatal error: Google bailed on us :(
				throw new MOC_Exception($content['responseDetails']);
		}
	}
}