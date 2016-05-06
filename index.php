<?php
	/**
	 * Accepts following scopes:
	 *    - admin
	 *    - org
	 *
	 * @author  Simon Skrødal
	 * @since   28/01/2016
	 */

	namespace Mediasite;

	###	   LOAD DEPENDENCIES	###
	require_once('mediasite/autoload.php');

	use Mediasite\Api\Mediasite;
	use Mediasite\Auth\Dataporten;
	use Mediasite\Conf\Config;
	use Mediasite\Utils\Response;
	use Mediasite\Vendor\Router;

	// Gatekeeper and provider of useful info
	$dataporten = new Dataporten();
	// Provides an interface to classes implementing Dataporten and routes
	$mediasite = new Mediasite($dataporten);

	### 	  ALTO ROUTER 		###
	$router = new Router();
	$router->setBasePath(Config::get('router')['api_base_path']);

##########################################################################
# PUBLIC (BASIC SCOPE) ROUTE DEFINITIONS
##########################################################################

	$info = "All available routes (scope: public).";
	$router->map('GET', '/', function () {
		global $router;
		// TODO: Show only routes available according to scope
		Response::result(array(
			'status' => true,
			'data'   => $router->getRoutes()
		));
	}, $info);


	$info = "List of orgs/folders with storage. No values are inluded, folder names only (scope: public).";
	$router->addRoutes([
		array('GET', '/service/orgs/', function () {
			global $mediasite;
			Response::result(array(
				'status' => true,
				'data'   => $mediasite->basic()->orgsList(),
			));
		}, $info),
	]);

	$info = "List of latest folder storage records. No folder names, values only (scope: public).";
	$router->addRoutes([
		array('GET', '/service/diskusage/list/', function () {
			global $mediasite;
			Response::result(array(
				'status' => true,
				'data'   => $mediasite->basic()->storageList(),
				'info'   => 'MiB'
			));
		}, $info),
	]);
	
	$info = "Total disk usage in MiB right now (scope: public).";
	$router->addRoutes([
		array('GET', '/service/diskusage/total/', function () {
			global $mediasite;
			Response::result(array(
				'status' => true,
				'data'   => $mediasite->basic()->totalDiskusageMiB(),
				'info'   => 'MiB'
			));
		}, $info),
	]);
	
	$info = "Average total disk usage this year in MiB (scope: public).";
	$router->addRoutes([
		array('GET', '/service/diskusage/avg/', function () {
			global $mediasite;
			Response::result(array(
				'status' => true,
				'data'   => $mediasite->basic()->totalAvgDiskusageMiB(),
				'info'   => 'MiB'
			));
		}, $info),
	]);

	$info = "Average total disk usage for [year] in MiB (scope: public).";
	$router->addRoutes([
		array('GET', '/service/diskusage/avg/[i:year]/', function ($year) {
			global $mediasite;
			Response::result(array(
				'status' => true,
				'data'   => $mediasite->basic()->totalAvgDiskusageMiB($year),
				'info'   => 'MiB'
			));
		}, $info),
	]);


	$info = "Home org total diskusage as of last record (scope: public).";
	$router->addRoutes([
		array('GET', '/me/diskusage/total/', function () {
			global $mediasite;
			Response::result(array(
				'status' => true,
				'data'   => $mediasite->basic()->homeOrgDiskusageTotal(),
				'info'   => 'MiB'
			));
		}, $info),
	]);


##########################################################################
# ORG ROUTE DEFINITIONS
##########################################################################

	//  At present, the client talks to Kind to check if logged on user is OrgAdmin.
	//  Consider for this API to talk to ecampus-kind directly instead
	if($dataporten->hasOauthScopeAdmin() || $dataporten->hasOauthScopeOrg()) { // TODO: Implement isOrgAdmin :: && ($dataporten->isOrgAdmin() || $dataporten->isSuperAdmin())) {
		
		$info = "Org diskusage history for the current year (scope: admin/org).";
		$router->addRoutes([
			array('GET', '/org/[a:org]/diskusage/list/', function ($org) {
				global $mediasite;
				verifyOrgAccess($org);
				Response::result(array(
					'status' => true,
					'data'   => $mediasite->org()->orgDiskusage($org, date("Y")),
					'info'   => 'Storage records (MiB) for the current year for org ' . $org . '.'
				));
			}, $info),
		]);

		$info = "Org diskusage history for requested year (scope: admin/org).";
		$router->addRoutes([
			array('GET', '/org/[a:org]/diskusage/list/[i:year]/', function ($org, $year) {
				global $mediasite;
				verifyOrgAccess($org);
				Response::result(array(
					'status' => true,
					'data'   => $mediasite->org()->orgDiskusage($org, $year),
					'info'   => 'Storage records (MiB) for ' . $year . ' for org ' . $org . '.'
				));
			}, $info),
		]);

		$info = "Org diskusage history for requested year and month (scope: admin/org).";
		$router->addRoutes([
			array('GET', '/org/[a:org]/diskusage/list/[i:year]/[i:month]/', function ($org, $year, $month) {
				global $mediasite;
				verifyOrgAccess($org);
				Response::result(array(
					'status' => true,
					'data'   => $mediasite->org()->orgDiskusage($org, $year, $month),
					'info'   => 'Storage records (MiB) for month ' . $month . ' of '. $year . ' for org ' . $org . '.'
				));
			}, $info),
		]);

		$info = "Org average diskusage for current year (scope: admin/org).";
		$router->addRoutes([
			array('GET', '/org/[a:org]/diskusage/avg/', function ($org) {
				global $mediasite;
				verifyOrgAccess($org);
				Response::result(array(
					'status' => true,
					'data'   => $mediasite->org()->orgDiskusageAvg($org),
					'info'   => 'MiB'
				));
			}, $info),
		]);

		$info = "Org average diskusage for requested year (scope: admin/org).";
		$router->addRoutes([
			array('GET', '/org/[a:org]/diskusage/avg/[i:year]/', function ($org, $year) {
				global $mediasite;
				verifyOrgAccess($org);
				Response::result(array(
					'status' => true,
					'data'   => $mediasite->org()->orgDiskusageAvg($org, $year),
					'info'   => 'Average storage (MiB) consumed in ' . $year . ' for org ' . $org . '.'
				));
			}, $info),
		]);

	}

##########################################################################
# ADMIN ROUTE DEFINITIONS
##########################################################################

	// isSuperAdmin added 15.10.2015 - need to be tested and considered carefully. Should we leave the clients to decide who is SuperAdmin, or
	// hardcode in API, judging by 'uninett.no' in username (I prefer the latter)? The client can actually call this API to find out if user has role(s)
	// super or org or user. simon@uninett.no should get:
	// { roles : [super, org, user] }

	if($dataporten->hasOauthScopeAdmin() && $dataporten->isSuperAdmin()) {

		$info = "Dev route to inspect headers (scope: admin).";
		$router->map('GET', '/dev/headers/', function () {
			Response::result(array(
				'status' => true,
				'data'   => $_SERVER
			));
		}, $info);

		$info = "Latest storage record per org, in MiB (scope: admin).";
		$router->addRoutes([
			array('GET', '/admin/orgs/diskusage/list/', function () {
				global $mediasite;
				Response::result(array(
					'status' => true,
					'data'   => $mediasite->admin()->orgsLatestDiskUsage(),
					'info'   => 'MiB'
				));
			}, $info),
		]);

		$info = "Average storage per org for current year, in MiB (scope: admin).";
		$router->addRoutes([
			array('GET', '/admin/orgs/diskusage/avg/list/', function () {
				global $mediasite;
				Response::result(array(
					'status' => true,
					'data'   => $mediasite->admin()->orgsDiskusageAvg(),
					'info'   => 'MiB'
				));
			}, $info),
		]);

		$info = "Average storage per org for selected year, in MiB (scope: admin).";
		$router->addRoutes([
			array('GET', '/admin/orgs/diskusage/avg/list/[i:year]/', function ($year) {
				global $mediasite;
				Response::result(array(
					'status' => true,
					'data'   => $mediasite->admin()->orgsDiskusageAvg($year),
					'info'   => 'MiB'
				));
			}, $info),
		]);
	}


	// ---------------------- MATCH AND EXECUTE REQUESTED ROUTE ----------------------

	$match = $router->match();

	if($match && is_callable($match['target'])) {
		sanitizeInput();
		call_user_func_array($match['target'], $match['params']);
	} else {
		Response::error(404, $_SERVER["SERVER_PROTOCOL"] . " The requested resource route could not be found.");
	}
	// ---------------------- /.MATCH AND EXECUTE REQUESTED ROUTE ----------------------


	// -------------------- UTILS -------------------- //

	/**
	 * http://stackoverflow.com/questions/4861053/php-sanitize-values-of-a-array/4861211#4861211
	 */
	function sanitizeInput() {
		$_GET  = filter_input_array(INPUT_GET, FILTER_SANITIZE_STRING);
		$_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
	}

	/**
	 * Prevent orgAdmin to request data for other orgs than what he belongs to.
	 *
	 * @param      $orgName
	 * @param null $userName
	 */
	function verifyOrgAccess($orgName, $userName = NULL) {
		global $dataporten;

		// Restrictions apply, unless you're superadmin...
		if(!$dataporten->isSuperAdmin()) {
			// If requested org data is not for home org
			if(strcasecmp($orgName, $dataporten->userOrg()) !== 0) {
				Response::error(401, $_SERVER["SERVER_PROTOCOL"] . ' 401 Unauthorized (request mismatch org/user). ');
			}
			// If request involves a user account
			if(isset($userName)) {
				// Must be user from home org
				if(!strstr($userName, $orgName)) {
					Response::error(401, $_SERVER["SERVER_PROTOCOL"] . ' 401 Unauthorized (request mismatch org/user). ');
				}
			}
		}
	}

	// -------------------- ./UTILS -------------------- //
