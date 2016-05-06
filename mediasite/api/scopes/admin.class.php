<?php
	namespace Mediasite\Api\Scopes;

	use Mediasite\Auth\Dataporten;
	use Mediasite\Database\MySQLConnection;

	/**
	 * Implements APIs GET routes for the ADMIN scope.
	 *
	 * @author  Simon Skrødal
	 * @since   28/01/2016
	 */
	class Admin {
		private $mySQLConnection, $dataporten;
		private $orgStorageTable;

		function __construct(Dataporten $dp, mySQLConnection $conn) {
			$this->mySQLConnection = new MySQLConnection();
			$this->dataporten      = $dp;
			$this->orgStorageTable = $this->mySQLConnection->getOrgStorageTableName();
		}

		/**
		 * Get snapshot of latest storage record per org (sorted by org).
		 *
		 * @return array
		 */
		public function orgsLatestDiskUsage() {
			// Last distinct orgs (hence last timestamp)
			$response = $this->mySQLConnection->query(
				"SELECT * FROM $this->orgStorageTable " .
				"WHERE id IN (SELECT MAX(id) FROM $this->orgStorageTable GROUP BY org) " .
				"ORDER BY org ASC"
			);
			$orgs = array();
			foreach($response as $org) {
				$orgs[] = $org;
			}
			// Done!
			return $orgs;
		}
	}