<?php
class MOC_Log_Color {
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

	public static function colorize($message, $colorize = false) {
		if (0 !== preg_match_all('#::([a-z]+)::#sim', $message, $colors)) {
			for ($i = 0; $i < sizeof($colors[0]); $i++) {
				if ($colorize) {
					$message = str_replace($colors[0][$i], constant('MOC_Log_Color::COLOR_' . strtoupper($colors[1][$i])), $message);
				} else {
					$message = str_replace($colors[0][$i], '', $message);
				}
			}
		}
		return $message;
	}
}