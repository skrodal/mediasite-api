<?php

	namespace Mediasite\Api;

	use Mediasite\Auth\Dataporten;
	use Mediasite\Database\MySQLConnection;

	/**
	 * Implements APIs GET routes.
	 *
	 * @author  Simon Skrødal
	 * @since   28/01/2016
	 */
	class MySQL {
		private $mySQLConnection, $dataporten;

		function __construct(Dataporten $dp) {
			$this->mySQLConnection = new MySQLConnection();
			$this->dataporten      = $dp;
		}

		/**
		 * Sorted list of all orgs with registered storage. Note: May not be subscribers any more!
		 *
		 * @return bool|\mysqli_result
		 */
		public function orgsList() {
			$table    = $this->mySQLConnection->getOrgStorageTableName();
			$response = $this->mySQLConnection->query("SELECT DISTINCT org FROM $table ORDER BY org ASC");
			// This query returns data of structure "org":"uio", "org":"uninett" - we don't need the "org" bit..
			$orgNames = array();
			foreach($response as $org) {
				$orgNames[] = $org["org"];
			}
			// Done!
			return $orgNames;
		}

		/**
		 * Get latest storage record per org (sorted by org).
		 *
		 * @return array
		 */
		public function orgsLatestDiskUsage() {
			$table = $this->mySQLConnection->getOrgStorageTableName();
			// Last distinct orgs (hence last timestamp)
			$response = $this->mySQLConnection->query(
				"SELECT * FROM $table " .
				"WHERE id IN (SELECT MAX(id) FROM $table GROUP BY org) " .
				"ORDER BY org ASC"
			);
			$orgs = array();
			foreach($response as $org) {
				$orgs[] = $org;
			}
			// Done!
			return $orgs;
		}

		/**
		 * Total disk usage right now.
		 *
		 * @return array
		 */
		public function totalDiskusageMiB() {
			// Get the latest storage records for each org
			$orgs = $this->orgsLatestDiskUsage();
			//
			$total_mib = 0;

			foreach($orgs as $org){
				$total_mib += $org['storage_mib'];
			}

			return $total_mib;
		}
		// TODO: dailyAvgDiskusageMib($year, $month).
		public function totalAvgDiskusageMiB($year){
			$table    = $this->mySQLConnection->getOrgStorageTableName();
			// Complete dump of all records from $year
			$response = $this->mySQLConnection->query("SELECT timestamp, storage_mib FROM $table WHERE YEAR(timestamp) = $year");

			$curDate = null;
			$days = 0;
			$dateStorage = 0;
			$totalStorage = 0;
			foreach($response as $storage) {
				if($curDate !== date("Ymd", strtotime($storage['timestamp']))){
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
			return $totalStorage/$days;
		}

		/**
		 * All storage entries for a single org.
		 *
		 * Filtered on year and (optionally) month.
		 *
		 * @return array
		 */
		public function orgDiskusage($org, $year, $month = NULL) {
			$table    = $this->mySQLConnection->getOrgStorageTableName();
			if(is_null($month)) {
				$response = $this->mySQLConnection->query("SELECT * FROM $table WHERE org = '$org' AND YEAR(timestamp) = $year");
			} else {
				$response = $this->mySQLConnection->query("SELECT * FROM $table WHERE org = '$org' AND YEAR(timestamp) = $year AND MONTH(timestamp) = $month");
			}
			
			$orgStorageRecords = array();
			foreach($response as $record) {
				$orgStorageRecords[] = $record;
			}
			// Done!
			return $orgStorageRecords;
		}
	}
