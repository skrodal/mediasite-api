<?php
	/**
	 *
	 *
	 * @author  Simon Skrødal
	 * @since   28/01/2016
	 */
	namespace Mediasite\Utils;

	use Mediasite\Conf\Config;

	class Utils {
		public static function log($text) {
			if(Config::get('utils')['debug']) {
				$trace  = debug_backtrace();
				$caller = $trace[1];
				error_log($caller['class'] . $caller['type'] . $caller['function'] . '::' . $caller['line'] . ': ' . $text);
			}
		}
	}