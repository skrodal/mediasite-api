<?php
	/**
	 *
	 * @author  Simon Skrødal
	 * @since   28/01/2016
	 */

	use Mediasite\Conf\Config;

	//
	$config_root = '/var/www/etc/mediasite/';

	Config::add(
		[
			'router' => [
				// Remember to update .htacces as well:
				'api_base_path' => '/api/mediasite'
			],
			'auth'   => [
				'dataporten'      => $config_root . 'dataporten_config.js',
				'mediasite_mysql' => $config_root . 'mysql_config.js',
			],
			'utils'  => [
				'debug' => false
			],
			'cache'  => [
				'enable' => true,
			    'TTL' => 3600
			]
		]);

