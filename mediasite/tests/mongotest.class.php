<?php
	/**
	 *
	 *
	 * @author Simon Skrødal
	 * @since   28/01/2016
	 */

	namespace Mediasite\Tests;

	use Mediasite\Database\MediasiteMySQLConnection;

	class MongoTest {

		function __construct() {

		}

		public function memoryTest() {
			$result = [];
			$old = memory_get_usage();
			$relayMongoConnection = new MediasiteMySQLConnection();
			$new = memory_get_usage();
			array_push($result,  "Memory after collection: " . ($new - $old));

			$old = memory_get_usage();
			$arr =$relayMongoConnection->findOne('presentations', ['username' => 'simon@uninett.no']);
			$new = memory_get_usage();
			array_push($result, "Memory after findOne: " . ($new - $old));

			$old = memory_get_usage();
			$cursor = $relayMongoConnection->findAll('presentations');
			$arr = iterator_to_array($cursor);
			$new = memory_get_usage();
			array_push($result, "Memory after find: " . ($new - $old));

			$cursor->reset();
			return $result;
		}



	}