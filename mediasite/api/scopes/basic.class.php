<?php
	namespace Mediasite\Api\Scopes;
	
	use Mediasite\Auth\Dataporten;
	use Mediasite\Database\MySQLConnection;

	/**
	 * Implements APIs GET routes for the BASIC scope.
	 *
	 * @author  Simon Skrødal
	 * @since   06/05/2016
	 */
	class Basic {
		private $mySQLConnection, $dataporten;
		private $orgStorageTable;

		function __construct(Dataporten $dp, mySQLConnection $conn) {
			$this->mySQLConnection = new MySQLConnection();
			$this->dataporten      = $dp;
			$this->orgStorageTable = $this->mySQLConnection->getOrgStorageTableName();
		}

		/**
		 * Sorted list of all orgs (folders) on disk. Note: Folders may belong to non-subscribing/merged orgs.
		 *
		 * @return bool|\mysqli_result
		 */
		public function orgsList() {
			$response = $this->mySQLConnection->query("SELECT DISTINCT org FROM $this->orgStorageTable ORDER BY org ASC");
			// This query returns data of structure "org":"uio", "org":"uninett" - we don't need the "org" bit..
			$orgNames = array();
			foreach($response as $org) {
				$orgNames[] = $org["org"];
			}

			// Done!
			return $orgNames;
		}

		/**
		 * Total disk usage right now.
		 *
		 * @return array
		 */
		public function totalDiskusageMiB() {
			// Get the latest storage records for each org
			$storageList = $this->storageList();
			$total_mib   = 0;
			foreach($storageList as $record) {
				$total_mib += $record['storage_mib'];
			}

			return $total_mib;
		}

		/**
		 * Sorted list of latest storage numbers (Anonymous data - NO orgs are included)
		 *
		 * @return bool|\mysqli_result
		 */
		public function storageList() {
			// Last distinct orgs (hence last timestamp)
			$response = $this->mySQLConnection->query(
				"SELECT storage_mib FROM $this->orgStorageTable " .
				"WHERE id IN (SELECT MAX(id) FROM $this->orgStorageTable GROUP BY org) " .
				"ORDER BY storage_mib ASC"
			);
			$storage  = array();
			foreach($response as $storage_mib) {
				$storage[] = $storage_mib;
			}

			// Done!
			return $storage;
		}

		/**
		 * Average diskusage for a given year (this year default)
		 *
		 * @param $year
		 *
		 * @return float
		 */
		public function totalAvgDiskusageMiB($year = NULL) {
			if(is_null($year)) {
				$year = date("Y");
			}
			// Complete dump of all records from $year
			$response = $this->mySQLConnection->query("SELECT timestamp, storage_mib FROM $this->orgStorageTable WHERE YEAR(timestamp) = $year");

			$curDate      = NULL;
			$days         = 0;
			$dateStorage  = 0;
			$totalStorage = 0;
			foreach($response as $storage) {
				if($curDate !== date("Ymd", strtotime($storage['timestamp']))) {
					$curDate = date("Ymd", strtotime($storage['timestamp']));
					$days++;
					$totalStorage += $dateStorage;
					$dateStorage = 0;
				}
				$dateStorage += $storage['storage_mib'];
			}
			// Remember to include last day
			$totalStorage += $dateStorage;
			//
			return $totalStorage / $days;
		}


	}