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

	class MySQLConnection {

		private $connection = false;
		private $config;

		public function __construct() {
			$this->config = json_decode(file_get_contents(Config::get('auth')['mediasite_mysql']), true);
			if($this->config === false) {
				Response::error(404, 'Not Found: MySQL config.');
			}
			// MySQL connection
			$this->connection = $this->getConnection();
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
				Response::error(500, 'DB connection failed.');
			}

			return $mysqli;
		}

		public function done() {
			if($this->connection !== false) {
				$this->connection->close();
			}
		}

		/**
		 * Name of table that holds storage stats.
		 *
		 * Stored in config to scale in case more tables are added in the future.
		 */
		public function getOrgStorageTableName() {
			return $this->config['org_storage_table'];
		}

		/**
		 * @param $query
		 *
		 * @return array
		 */
		public function query($query) {
			// Run query
			$response = $this->connection->query($query);
			// On error
			if(!$response) {
				Utils::log('MySQL Query Error: ' . $this->connection->error);
				Response::error(500, 'DB query failed.');
			}
			// For storing returned rows
			$rows = array();
			while($r = $response->fetch_assoc()) {
				$rows[] = $r;
			}
			// Tidy
			$response->close();

			//
			return $rows;
		}
	}