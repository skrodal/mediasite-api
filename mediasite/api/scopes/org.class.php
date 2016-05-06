<?php
	namespace Mediasite\Api\Scopes;
	
	use Mediasite\Auth\Dataporten;
	use Mediasite\Database\MySQLConnection;

	/**
	 * Implements APIs GET routes for the ORG scope.
	 *
	 * @author  Simon Skrødal
	 * @since   28/01/2016
	 */
	class Org {
		private $mySQLConnection, $dataporten;
		private $orgStorageTable;

		function __construct(Dataporten $dp, mySQLConnection $conn) {
			$this->mySQLConnection = new MySQLConnection();
			$this->dataporten      = $dp;
			$this->orgStorageTable = $this->mySQLConnection->getOrgStorageTableName();
		}

	}