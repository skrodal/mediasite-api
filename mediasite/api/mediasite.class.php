<?php
	namespace Mediasite\Api;

	use Mediasite\Api\Scopes\Admin;
	use Mediasite\Api\Scopes\Basic;
	use Mediasite\Api\Scopes\Org;
	use Mediasite\Auth\Dataporten;
	use Mediasite\Database\MySQLConnection;


	/**
	 * Provides access to all involved classes.
	 *
	 * @author  Simon Skrødal
	 * @since   28/01/2016
	 */
	class Mediasite {

		private $dataporten, $mySQLConnection;
		private $admin, $org, $basic;

		function __construct(Dataporten $dp) {
			$this->dataporten      = $dp;
			$this->mySQLConnection = new MySQLConnection();
			$this->admin           = new Admin($this->dataporten, $this->mySQLConnection);
			$this->org             = new Org($this->dataporten, $this->mySQLConnection);
			$this->basic           = new Basic($this->dataporten, $this->mySQLConnection);
		}

		public function basic() {
			return $this->basic;
		}

		public function org() {
			return $this->org;
		}

		public function admin() {
			return $this->admin;
		}

		public function dp() {
			return $this->dataporten;
		}
	}


