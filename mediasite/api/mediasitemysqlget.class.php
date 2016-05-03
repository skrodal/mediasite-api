<?php

	namespace Mediasite\Api;
	
	use Mediasite\Auth\Dataporten;
	use Mediasite\Database\MediasiteMySQLConnection;
	use Mediasite\Utils\Response;
	use Mediasite\Utils\Utils;

	/**
	 * Implements APIs GET routes.
	 *
	 * @author Simon Skrødal
	 * @since   28/01/2016
	 */
	class MediasiteMySQLGet {
		private $mediasiteMySQLConnection, $dataporten;

		function __construct(Dataporten $dp) {
			$this->mediasiteMySQLConnection = new MediasiteMySQLConnection();
			$this->dataporten               = $dp;
		}

		/**
		 * Sorted list of all orgs with registered storage. Note: May not be subscribers any more!
		 *
		 * @return bool|\mysqli_result
		 */
		public function orgsList(){
			$response = $this->mediasiteMySQLConnection->query("SELECT DISTINCT org FROM " . $this->mediasiteMySQLConnection->getOrgStorageTableName());
			// This query returns data of structure "org":"uio", "org":"uninett" - we don't need the "org" bit..
			$orgNames = array();
			foreach($response as $org) {
				$orgNames[] = $org["org"];
			}
			sort($orgNames);
			return $orgNames;
		}

		public function orgsLatestDiskUsage(){
			$table = $this->mediasiteMySQLConnection->getOrgStorageTableName();
			// Last distinct orgs (hence last timestamp)
			$response = $this->mediasiteMySQLConnection->query(
				"SELECT *" .
					" FROM " . $table .
					" WHERE id IN (SELECT MAX (id) FROM ". $table . " GROUP BY org)");

			$orgs = array();
			foreach($response as $org) {
				$orgs[] = $org;
			}
			sort($orgs);
			return $orgs;
		}

		// Total only
		public function totalDiskusage() {
			$orgs = $this->mediasiteMySQLConnection->query('SELECT DISTINCT org FROM org_storage');
			return $orgs;
			/*
				$orgs = $this->mediasiteMySQLConnection->findAll('org');
				//
				$total_mib = 0;
				foreach($orgs as $org) {
					if(!empty($org['storage'])) {
						// Latest entry is most current
						$length = sizeof($org['storage']) - 1;
						$total_mib += (float)$org['storage'][$length]['size_mib'];
					}
				}
				return $total_mib;
			*/
		}

		// Storage entries for a single org
		public function orgDiskusage($org) {
			/*
				$criteria              = ['org' => $org];
				$response['total_mib'] = 0;
				$response['storage']   = $this->mediasiteMySQLConnection->findOne('org', $criteria)['storage'];

				if(!empty($response['storage'])) {
					// Latest entry is most current
					$length                = sizeof($response['storage']) - 1;
					$response['total_mib'] = (float)$response['storage'][$length]['size_mib'];
				}

				return $response;
			*/
		}

