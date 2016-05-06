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

		function __construct(Dataporten $dp, mySQLConnection $conn) {
			$this->mySQLConnection = new MySQLConnection();
			$this->dataporten      = $dp;
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
