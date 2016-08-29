<?php
	/**
	 *
	 *
	 * @author Simon Skrødal
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

		/**
		 *
		 * @param      $entryName
		 * @param      $data
		 * @param bool $TTL
		 */
		public static function storeToCache($entryName, $data, $TTL = false){
			$TTL = $TTL ? $TTL : Config::get('cache')['TTL'];
			apc_store('MEDIASITE_API.' . $entryName, $data, $TTL);
		}

		/**
		 * Returns data from cache if available, or false otherwise.
		 *
		 * @param $entryName
		 * @return bool
		 */
		public static function loadFromCache($entryName){
			if(!apc_exists('MEDIASITE_API.' . $entryName)) {
				return false;
			}
			// Pull data from cache
			return apc_fetch('MEDIASITE_API.' . $entryName);
		}

		public static function flushCache(){
			apc_clear_cache();
			apc_clear_cache('user');
		}

	}