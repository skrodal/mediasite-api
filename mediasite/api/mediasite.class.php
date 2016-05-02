<?php
	namespace Mediasite\Api;
	use Mediasite\Auth\Dataporten;


	/**
	 * Provides access to all involved classes.
	 *
	 * @author Simon Skrødal
	 * @since   28/01/2016
	 */
	class Mediasite {

		private $dataporten;
		private $mediasiteMySQLGet;

		function __construct(Dataporten $dp) {
			$this->dataporten         = $dp;
			$this->mediasiteMySQLGet  = new MediasiteMySQLGet($this->dataporten);
		}

		public function mysqlGet(){
			return $this->mediasiteMySQLGet;
		}

		public function dp(){
			return $this->dataporten;
		}
	}