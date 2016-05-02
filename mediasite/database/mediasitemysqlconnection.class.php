<?php

	/**
	 *
	 *
	 * @author Simon Skrødal
	 * @since   28/01/2016
	 */

	namespace Mediasite\Database;

	use MongoClient;
	use MongoConnectionException;
	use MongoCursorException;
	use Mediasite\Utils\Response;
	use Mediasite\Conf\Config;
	use Mediasite\Utils\Utils;


	class MediasiteMySQLConnection {
		// Mongo
		private $connection, $db;
		//
		private $config;

		public function __construct() {
			// Get connection conf
			$this->config = $this->getConfig();
			// MongoClient
			$this->connection = $this->getConnection();
			// Set Client DB
			$this->db = $this->connection->selectDB( $this->config['db'] );
		}

		public function find($collection, $criteria){
			$response = [];
			try {
				// Get cursor
				$cursor = $this->db->selectCollection($collection)->find($criteria);
				// Iterate the cursor
				foreach($cursor as $document) {
					// Push document (array) into response array
					array_push($response, $document);
				}
				// Close the cursor (apparently recommended)
				$cursor->reset();
				return $response;
			} catch (MongoCursorException $e){
				Response::error(500, $_SERVER["SERVER_PROTOCOL"] . ' DB cursor error (MongoDB).');
			}
		}

		public function count($collection, $criteria){
			return $this->db->selectCollection($collection)->find($criteria)->count();
		}

		private function getConnection(){
			try {
				return new MongoClient("mongodb://" . $this->config['user'] . ":" . $this->config['pass'] . "@127.0.0.1/" . $this->config['db']);
			} catch (MongoConnectionException $e){
				Utils::log($e->getMessage());
				Response::error(500, $_SERVER["SERVER_PROTOCOL"] . ' DB connection failed (MySQL).');
			}
		}

		private function getConfig(){
			$this->config = file_get_contents(Config::get('auth')['mediasite_mysql']);
			// Sanity
			if($this->config === false) { Response::error(404, $_SERVER["SERVER_PROTOCOL"] . ' Not Found: MySQL config.'); }
			// Connect username and pass
			return json_decode($this->config, true);
		}

	}