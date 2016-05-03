<?php

	/**
	 *
	 *
	 * @author  Simon Skrødal
	 * @since   28/01/2016
	 */

	namespace Mediasite\Database;


	use Mediasite\Conf\Config;
	use Mediasite\Utils\Response;
	use Mediasite\Utils\Utils;
	use mysqli;

	class MediasiteMySQLConnection {

		private $connection, $db;
		private $config;

		public function __construct() {
			// Get connection conf
			$this->config = $this->getConfig();
			// MySQL connection
			$this->connection = $this->getConnection();
		}

		/**
		 * @return mixed
		 */
		private function getConfig() {
			$this->config = file_get_contents(Config::get('auth')['mediasite_mysql']);
			// Sanity
			if($this->config === false) {
				Response::error(404, $_SERVER["SERVER_PROTOCOL"] . ' Not Found: MySQL config.');
			}
			//
			return json_decode($this->config, true);
		}

		/**
		 * Get a client connection to the DB
		 *
		 * @return mysqli
		 */
		private function getConnection() {
			$mysqli = new mysqli($this->config['host'], $this->config['user'], $this->config['pass'], $this->config['db']);
			// If error code set
			if($mysqli->connect_errno) {
				Utils::log('MySQL Connect Error: ' . $mysqli->connect_error);   // Returns a string description of the last connect error
				Response::error(500, $_SERVER["SERVER_PROTOCOL"] . ' DB connection failed (MySQL).');
			}
			return $mysqli;
		}

		/**
		 * Name of table that holds storage stats.
		 *
		 * Stored in config to scale in case more tables are added in the future.
		 */
		public function getOrgStorageTableName(){
			return $this->config['org_storage_table'];
		}

		/**
		 * Handles queries
		 *
		 * @param $query
		 *
		 * @return bool|\mysqli_result
		 */
		public function query($query) {
			$response = $this->connection->query($query);
			// On error
			if(!$response) {
				Utils::log('MySQL Query Error: ' . $mysqli->error);
				Response::error(500, $_SERVER["SERVER_PROTOCOL"] . ' DB query failed (MySQL).');
			}

			return $response;
		}
	}