<?php
	/**
	 *
	 *
	 * @author  Simon Skrødal
	 * @since   28/01/2016
	 */
	namespace Mediasite\Auth;

	use Mediasite\Conf\Config;
	use Mediasite\Utils\Response;

	class Dataporten {

		protected $config;

		function __construct() {
			$this->config = file_get_contents(Config::get('auth')['dataporten']);
			// Sanity
			if($this->config === false) {
				Response::error(404, ' Not Found: Dataporten config.');
			}
			// Dataporten username and pass
			$this->config = json_decode($this->config, true);
			// Exits on OPTION call
			$this->_checkCORS();
			// Exits on incorrect API credentials
			$this->_checkGateKeeperCredentials();
			// We also need a token (for later calls)
			if(!isset($_SERVER["HTTP_X_DATAPORTEN_TOKEN"])) {
				Response::error(401, 'Unauthorized (missing token)');
			}
			// Make sure we have a scope
			// (NOTE: 'basic' scope is implicit and not listed in HTTP_X_DATAPORTEN_SCOPES.
			// This means that client MUST have access to at least ONE extra custom scope).
			if(!isset($_SERVER["HTTP_X_DATAPORTEN_SCOPES"])) {
				Response::error(401, 'Unauthorized (missing scope)');
			}
			// Check that we got a username
			if(!isset($_SERVER["HTTP_X_DATAPORTEN_USERID_SEC"])) {
				Response::error(401, 'Unauthorized (user not found)');
			}
		}


		##				SCOPES				##

		//

		private function _checkCORS() {
			// Access-Control headers are received during OPTIONS requests
			if(strcasecmp($_SERVER['REQUEST_METHOD'], "OPTIONS") === 0) {
				Response::result('CORS OK :-)');
			}
		}

		private function _getUserGroups(){
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, 'https://groups-api.dataporten.no/groups/me/groups');
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			// Set headers
			curl_setopt($ch, CURLOPT_HTTPHEADER, [
					"Authorization: Bearer " . $_SERVER["HTTP_X_DATAPORTEN_TOKEN"],
					"Content-Type: application/json",
				]
			);
			// Send the request
			$userGroups = curl_exec($ch);
			//
			if(!$userGroups) {
				Response::error(401, 'Unauthorized (failed to retrieve groups)');
			}
			curl_close($ch);
			// All good
			return json_decode($userGroups);
		}

		//

		/**
		 * Gets the feide username (if present) from the Gatekeeper via HTTP_X_DATAPORTEN_USERID_SEC.
		 *
		 * It should only return a single string, 'feide:user@org.no', but future development might introduce
		 * a comma-separated or array representation of more than one username
		 * (e.g. "openid:user@org.no, feide:user@org.no")
		 *
		 * This function takes care of all of these cases.
		 */
		private function _getFeideUsername() {
			$userIdSec = NULL;
			// Get the username(s)
			$userid = $_SERVER["HTTP_X_DATAPORTEN_USERID_SEC"];
			// Future proofing...
			if(!is_array($userid)) {
				// If not already an array, make it so. If it is not a comma separated list, we'll get a single array item.
				$userid = explode(',', $userid);
			}
			// Fish for a Feide username
			foreach($userid as $key => $value) {
				if(strpos($value, 'feide:') !== false) {
					$value     = explode(':', $value);
					$userIdSec = $value[1];
				}
			}
			// No Feide...
			if(!isset($userIdSec)) {
				Response::error(401, 'Unauthorized (user not found)');
			}

			// 'username@org.no'
			return $userIdSec;
		}

		##				SCOPES				##
		// 

		private function _checkGateKeeperCredentials() {
			if(empty($_SERVER["PHP_AUTH_USER"]) || empty($_SERVER["PHP_AUTH_PW"])) {
				Response::error(401, 'Unauthorized (Missing API Gatekeeper Credentials)');
			}
			// Gatekeeper. user/pwd is passed along by the Dataporten Gatekeeper and must matched that of the registered API:
			if((strcmp($_SERVER["PHP_AUTH_USER"], $this->config['user']) !== 0) ||
				(strcmp($_SERVER["PHP_AUTH_PW"], $this->config['passwd']) !== 0)
			) {
				// The status code will be set in the header
				Response::error(401, 'Unauthorized (Incorrect API Gatekeeper Credentials)');
			}
		}

		// Feide username
		public function hasOauthScopeAdmin() {
			return $this->_hasDataportenScope("admin");
		}

		// org.no

		private function _hasDataportenScope($scope) {
			// Get the scope(s)
			$scopes = $_SERVER["HTTP_X_DATAPORTEN_SCOPES"];
			// Make array
			$scopes = explode(',', $scopes);

			// True/false
			return in_array($scope, $scopes);
		}

		public function hasOauthScopeOrg() {
			return $this->_hasDataportenScope("org");
		}

		public function isSuperAdmin() {
			return strcasecmp($this->userOrg(), "uninett.no") === 0;
		}

		public function userOrg() {
			$userOrg = explode('@', $this->userName());

			return $userOrg[1];
		}

		public function userName() {
			return $this->_getFeideUsername();
		}

		// Check if user is member of group $this->config['group_id'],
		public function hasGroupAccess(){
			$userGroups = $this->_getUserGroups();

			return $userGroups;


			// TODO:
			$this->config['group_id'];
			$this->config['group_invite'];
		}

	}