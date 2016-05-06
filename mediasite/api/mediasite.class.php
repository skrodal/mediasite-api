<?php
	namespace Mediasite\Api;

	use Mediasite\Auth\Dataporten;


	/**
	 * Provides access to all involved classes.
	 *
	 * @author  Simon Skrødal
	 * @since   28/01/2016
	 */
	class Mediasite {

		private $dataporten, $mySQLConnection;
		private $admin, $org, $public;

		function __construct(Dataporten $dp) {
			$this->dataporten      = $dp;
			$this->mySQLConnection = new MySQLConnection();
			$this->admin           = new Admin($this->dataporten, $this->mySQLConnection);
			$this->org             = new Org($this->dataporten, $this->mySQLConnection);
			$this->basic           = new Basic($this->dataporten, $this->mySQLConnection);
		}

		// Route requests to correct scope (tidy)
		public function scope($scope) {
			switch($scope) {
				case "admin":
					return $this->admin;
					break;
				case "org":
					return $this->org;
					break;
				case "basic":
					return $this->basic;
					break;
				default:
					return null;
			}
		}

		public function dp() {
			return $this->dataporten;
		}
	}


