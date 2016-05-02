<?php

	/**
	 *
	 *
	 * @author Simon Skrødal
	 * @since   28/01/2016
	 */
	namespace Mediasite\Conf;

	class Config {
		protected static $config = array();

		public static function get($name, $default = null)
		{
			return isset(self::$config[$name]) ? self::$config[$name] : $default;
		}

		public static function add($parameters = array())
		{
			self::$config = array_merge(self::$config, $parameters);
		}
	}