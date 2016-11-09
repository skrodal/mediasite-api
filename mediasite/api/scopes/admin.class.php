<?php
	namespace Mediasite\Api\Scopes;

	use Mediasite\Auth\Dataporten;
	use Mediasite\Conf\Config;
	use Mediasite\Database\MySQLConnection;
	use Mediasite\Utils\Utils;

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
			$this->mySQLConnection = $conn;
			$this->dataporten      = $dp;
			$this->orgStorageTable = $this->mySQLConnection->getOrgStorageTableName();
		}

		/**
		 * List of current storage usage per org (sorted by org).
		 *
		 * @return array
		 */
		public function orgsLatestDiskUsage() {
			$cacheKey = 'admin.orgs.diskusage.list';
			if(!Utils::loadFromCache($cacheKey)){
				// Last distinct orgs (hence last timestamp)
				$response = $this->mySQLConnection->query(
					"SELECT org, storage_mib FROM $this->orgStorageTable " .
					"WHERE id IN (SELECT MAX(id) FROM $this->orgStorageTable GROUP BY org) " .
					"ORDER BY org ASC"
				);
				$orgs     = array();
				foreach($response as $org) {
					$orgs[] = $org;
				}
				Utils::storeToCache($cacheKey, $orgs);
			}
			return Config::get('cache')['enable'] ? Utils::loadFromCache($cacheKey) : $orgs;
		}

		/**
		 * List of average storage per org for a given year.
		 *
		 * @param null $year
		 *
		 * @return array
		 */
		public function orgsDiskusageAvg($year = NULL) {
			if(is_null($year)) {
				$year = date("Y");
			}
			$cacheKey = 'admin.orgs.diskusage.avg.list.' . $year;
			if(!Utils::loadFromCache($cacheKey)){
				// Complete dump of all records from $year
				$response = $this->mySQLConnection->query("SELECT org, storage_mib FROM $this->orgStorageTable WHERE YEAR(timestamp) = $year ORDER BY org ASC");
				//
				$orgsListTemp = [];
				$orgsList     = [];
				//
				foreach($response as $record) {
					// Set or accumulate for each org
					if(!isset($orgsListTemp[$record['org']])) {
						$orgsListTemp[$record['org']]['total_mib']     = $record['storage_mib'];
						$orgsListTemp[$record['org']]['total_records'] = 1;
					} else {
						$orgsListTemp[$record['org']]['total_mib'] += $record['storage_mib'];
						$orgsListTemp[$record['org']]['total_records']++;
					}
				}
				//
				foreach($orgsListTemp as $org => $orgObj) {
					$orgsList[$org] = $orgObj['total_mib'] / $orgObj['total_records'];
				}
				//
				Utils::storeToCache($cacheKey, $orgsList);
			}
			//
			return Config::get('cache')['enable'] ? Utils::loadFromCache($cacheKey) : $orgsList;
		}
	}