<?php
/*
	URL Shortener API

	Author: Mark Roland

	Log:
		9/30/2011 - Created
		3/17/2013 - Updated
*/

// Set error reporting for debugging
// error_reporting(E_ALL);
ini_set('display_errors', FALSE);

// Debugging
// print_r($_GET);
// print_r($_POST);

// Connect to database. This is included from a file outside of the public_html directory for security.
// The included file should contain something like:
/*
$db = mysql_connect('localhost', 'database_username', 'database_password');
mysql_select_db('database_name',$db);
//*/
require('../../../../includes/db_connections/db_login.inc');

/* API Response Message Codes
Error Code,Description
1,Invalid API authentication
2,The method you requested does not exist
3,Shortcut is unavailable
*/

/* HTTP Status Codes
// header("Status: 200");
200 OK: everything went awesome.
304 Not Modified: there was no new data to return.
400 Bad Request: your request is invalid, and we'll return an error message that tells you why. This is the status code returned if you've exceeded the rate limit (see below).
401 Not Authorized: either you need to provide authentication credentials, or the credentials provided aren't valid.
403 Forbidden: we understand your request, but are refusing to fulfill it.	An accompanying error message should explain why.
404 Not Found: either you're requesting an invalid URI or the resource in question doesn't exist (ex: no such user).
500 Internal Server Error: we did something wrong.	Please post to the group about it and the Twitter team will investigate.
502 Bad Gateway: returned if Twitter is down or being upgraded.
503 Service Unavailable: the Twitter servers are up, but are overloaded with requests.	Try again later.
*/

// Include shortener class
require('urlShortener/urlShortener.class.php');

// Create new shortener object
$Shortener = new urlShortener;

// Initialize response
$response = array();
$response['http_response'] = '200 OK';

// Set a flag to indicate if the requested method exists
$found_method_flag = 0;

// Create a new short url
if( strcasecmp($_GET['method'],'create_shortcut') == 0 ){

	// Set flag
	$found_method_flag = 1;

	// Initialize Response
	$response = array(
		'method' => $_GET['method'],
		'status' => 'fail'
	);

	// Save shortcut if fields are provided 
	if( empty($_POST['destination']) || empty($_POST['shortcut'])){

		$response['code'] = 3;
		$response['message'] = 'Destination URL and Shortcut are Required';
		// $response['http_response'] = '400 Bad Request';

	}else{

		// Attempt to create shortcut
		$shortcut_info = $Shortener->get_shortcut($_POST['shortcut']);
		if( empty($shortcut_info) ){

			if( $Shortener->create_shortcut($_POST['shortcut'], $_POST['destination'], $_POST['set_referrer']) ){
				$response['code'] = 4;
				$response['message'] = 'Shortcut created successfully';
				$response['status'] = 'success';
				$response['data'] = $Shortener->get_shortcut($_POST['shortcut']);
			}

		}else{
			$response['code'] = 3;
			$response['message'] = 'Shortcut is unavailable';
			// $response['http_response'] = '400 Bad Request';
		}

	}

}

elseif( strcasecmp($_GET['method'],'get_shortcuts') == 0 ){

	// Set flag
	$found_method_flag = 1;

	// Initialize Response
	$response = array(
		'method' => 'get_shortcuts',
		'status' => 'fail'
	);

	// Attempt to create shortcut
	$response['data'] = $Shortener->get_shortcuts();
	$response['status'] = 'success';

}

// Return an error message if no valid $_GET['method'] was found
if(!$found_method_flag){
	$response['message'] = 'The method you requested, '.$_GET['method'].', does not exist';
	$response['code'] = 2;
	$response['http_response'] = '404 Not Found';
}

// Return Response
header('HTTP/1.1 '.$response['http_response']);
header('Content-Type: application/json; charset=utf-8');
print( json_encode($response) );

?>