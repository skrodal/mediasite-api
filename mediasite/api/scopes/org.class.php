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

		/**
		 * All storage entries for a single org.
		 *
		 * Filtered on year and (optionally) month.
		 *
		 * @return array
		 */
		public function orgDiskusage($org, $year, $month = NULL) {
			if(is_null($month)) {
				$response = $this->mySQLConnection->query("SELECT storage_mib, timestamp FROM $this->orgStorageTable WHERE org = '$org' AND YEAR(timestamp) = $year");
			} else {
				$response = $this->mySQLConnection->query("SELECT storage_mib, timestamp FROM $this->orgStorageTable WHERE org = '$org' AND YEAR(timestamp) = $year AND MONTH(timestamp) = $month");
			}

			$orgStorageRecords = array();
			foreach($response as $record) {
				settype($record['storage_mib'], "integer");
				$orgStorageRecords[] = $record;
			}

			return $orgStorageRecords;
		}

		public function orgDiskusageAvg($org, $year = NULL) {
			if(is_null($year)) {
				$year = date("Y");
			}
			// All records for org from selected year
			$response = $this->mySQLConnection->query("SELECT storage_mib FROM $this->orgStorageTable WHERE org = '$org' AND YEAR(timestamp) = $year");
			//
			$totalStorage = 0;
			foreach($response as $storage) {
				$totalStorage += $storage['storage_mib'];
			}
			//
			return $totalStorage / count( $response );
		}

	}

