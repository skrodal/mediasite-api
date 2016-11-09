<?php
	namespace Mediasite\Api\Scopes;

	use Mediasite\Auth\Dataporten;
	use Mediasite\Database\MySQLConnection;
	use Mediasite\Utils\Utils;

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
			$this->mySQLConnection = $conn;
			$this->dataporten      = $dp;
			$this->orgStorageTable = $this->mySQLConnection->getOrgStorageTableName();
		}

		/**
		 * All storage entries for a single org.
		 *
		 * Filtered on year and (optionally) month.
		 *
		 * @param      $org
		 * @param      $year
		 * @param null $month
		 *
		 * @return bool|mixed
		 */
		public function orgDiskusage($org, $year, $month = NULL) {
			$orgStorageRecords = array();
			$cacheKey = 'org.' . $org . '.diskusage.list.' . $year;
			//
			if(is_null($month)) {
				// Whole year
				if(!Utils::loadFromCache($cacheKey)) {
					$response = $this->mySQLConnection->query("SELECT storage_mib, timestamp FROM $this->orgStorageTable WHERE org = '$org' AND YEAR(timestamp) = $year");
					foreach($response as $record) {
						settype($record['storage_mib'], "integer");
						$orgStorageRecords[] = $record;
					}
					Utils::storeToCache($cacheKey, $orgStorageRecords);
				}
			} else {
				$cacheKey = $cacheKey . '.' . $month;
				// Month only
				if(!Utils::loadFromCache($cacheKey)) {
					$response = $this->mySQLConnection->query("SELECT storage_mib, timestamp FROM $this->orgStorageTable WHERE org = '$org' AND YEAR(timestamp) = $year AND MONTH(timestamp) = $month");
					foreach($response as $record) {
						settype($record['storage_mib'], "integer");
						$orgStorageRecords[] = $record;
					}
					Utils::storeToCache($cacheKey, $orgStorageRecords);
				}
			}
			return Config::get('cache')['enable'] ? Utils::loadFromCache($cacheKey) : $orgStorageRecords;
		}

		/**
		 * @param      $org
		 * @param null $year
		 *
		 * @return bool|mixed
		 */
		public function orgDiskusageAvg($org, $year = NULL) {
			if(is_null($year)) {
				$year = date("Y");
			}
			$cacheKey = 'org.' . $org . '.diskusage.avg.' . $year;
			$avg = 0;

			if(!Utils::loadFromCache($cacheKey)) {
				// All records for org from selected year
				$response = $this->mySQLConnection->query("SELECT storage_mib FROM $this->orgStorageTable WHERE org = '$org' AND YEAR(timestamp) = $year");
				//
				$totalStorage = 0;
				foreach($response as $storage) {
					$totalStorage += $storage['storage_mib'];
				}
				$avg = $totalStorage / count( $response );
				Utils::storeToCache($cacheKey, $avg);
			}
			return Config::get('cache')['enable'] ? Utils::loadFromCache($cacheKey) : $avg;
		}

	}

