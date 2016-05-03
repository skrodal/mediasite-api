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

// ---------------------- DEFINE ROUTES ----------------------

	/**
	 * GET all REST routes
	 */
	$router->map('GET', '/', function () {
		global $router;
		// TODO: Show only routes available according to scope
		Response::result(array('status' => true, 'data' => $router->getRoutes()));
	}, 'All available routes.');

	// SERVICE ROUTES (scope basic)
	// (Update: NOT true! Basic Scope is not transferred in HTTP_X_DATAPORTEN_SCOPES, hence client needs at least one custom scope.)
	// See GK in dataporten.class...
	$router->addRoutes([
		array('GET', '/service/diskusage/', function () {
			global $mediasite;
			Response::result(array('status' => true, 'data' => $mediasite->mysqlGet()->totalDiskusage()));
		}, 'Diskusage total (Scope: public).'),
	]);


	// ADMIN ROUTES - if scope allows
	// isSuperAdmin added 15.10.2015 - need to be tested and considered carefully. Should we leave the clients to decide who is SuperAdmin, or
	// hardcode in API, judging by 'uninett.no' in username (I prefer the latter)? The client can actually call this API to find out if user has role(s)
	// super or org or user. simon@uninett.no should get:
	// { roles : [super, org, user] }

	if($dataporten->hasOauthScopeAdmin() && $dataporten->isSuperAdmin()) {
		$router->addRoutes([
			array('GET', '/admin/orgs/', function () {
				global $mediasite;
				Response::result(array('status' => true, 'data' => $mediasite->mysqlGet()->orgs()));
			}, 'List of all orgs with registered storage regardless of subscription status (Scope: admin).'),
		]);
	}

	if($dataporten->hasOauthScopeAdmin() && $dataporten->isSuperAdmin()) {
		$router->addRoutes([
			array('GET', '/admin/orgs/diskusage/', function () {
				global $mediasite;
				Response::result(array('status' => true, 'data' => $mediasite->mysqlGet()->orgsDiskusage()));
			}, 'Diskusage history for all orgs (Scope: admin).'),
			// TODO: Add POST service to receive daily updates and store to MongoDB
			//array('POST', '/admin/orgs/diskusage/', function(){ global $mediasite; Response::result(array('status' => true, 'data' => $mediasite->mongoGet()->orgsDiskusage())); }, 'Service diskusage history for all orgs (Scope: admin).'),
		]);
	}

	// ORG ROUTES if scope allows
	// TODO:
	//  At present, the client talks to Kind to check if logged on user is OrgAdmin.
	//  This is not ideal, the check should happen in this API, which can call Kind and verify!
	//  FC team says there is no easy way at present for one API GK to speak to another one... (OCT 2015)
	if($dataporten->hasOauthScopeAdmin() || $dataporten->hasOauthScopeOrg()) { // TODO: Implement isOrgAdmin :: && ($dataporten->isOrgAdmin() || $dataporten->isSuperAdmin())) {
		$router->addRoutes([
			array('GET', '/org/[org:orgId]/diskusage/', function ($orgId) {
				global $mediasite;
				verifyOrgAccess($orgId);
				Response::result(array('status' => true, 'data' => $mediasite->mysqlGet()->orgDiskusage($orgId)));
			}, 'Org diskusage history (Scope: admin/org).'),
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
