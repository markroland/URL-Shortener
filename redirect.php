<?php
/*
	URL Shortener

	Mark Roland

	9/28/2011 - Created
	2/21/2012 - Updated to support query strings
	3/6/2012 - Updated to use "set_referrer" parameter for JavaScript-based redirects
	3/17/2013 - Updated for blog publication
*/

// Set error reporting for debugging
// error_reporting(E_ALL);
ini_set('display_errors', FALSE);

// Connect to database. This is included from a file outside of the public_html directory for security.
// The included file should contain something like:
/*
$db = mysql_connect('localhost', 'database_username', 'database_password');
mysql_select_db('database_name',$db);
//*/
require('../../../../includes/db_connections/db_login.inc');

// Set default destination
$destination_url = '';

// Include urlShortener class
include('urlShortener/urlShortener.class.php');

// Create new class object
$Shortener = new urlShortener;

// Look up requested shortcut
$shortcut_info = $Shortener->get_shortcut($_GET['shortcut']);

// Return a "404 Not Found" HTTP Response if the requested shortcut was not found
if( empty($shortcut_info) ){
	header('HTTP/1.1 404 Not Found');
	exit;
}

// Set destination based on database results
$destination_url = $shortcut_info['destination_url'];

// Replace query string parameters in destination_url with query string parameters from page request
if($_GET['qsa']){

	// Split destination url into parts
	$dest_url_parts = parse_url($destination_url);

	// Merge/replace destination URL's query string with values in request
	$request_query_kvp = $_GET;
	unset($request_query_kvp['qsa']);
	unset($request_query_kvp['shortcut']);
	parse_str($dest_url_parts['query'], $destination_query_kvp);
	$new_query_parts = array_merge($destination_query_kvp, $request_query_kvp);
	$dest_url_parts['query'] = http_build_query($new_query_parts);

	// Rebuild URL
	$destination_url = $dest_url_parts['scheme'].'://'.$dest_url_parts['host'].
		$dest_url_parts['path'].( !empty($dest_url_parts['query']) ? '?'.$dest_url_parts['query'] : '' ).$dest_url_parts['fragment'];
}

// Track request
$Shortener->track_shortcut($shortcut_info['shortcut_id'], $_SERVER['REMOTE_ADDR'], $_GET['ref']);

// Use a HTTP redirect if the "set_referrer" is set to zero
if( $shortcut_info['set_referrer'] == 0 ){
	header('HTTP/1.1 302 Found');
	header('Location: '.$destination_url);
}

// Use a browser-based redirect (not server-based) so that the HTTP referrer can be properly
// set for destination-URLs that require the domain to be the domain of this script's location.
echo '<!DOCTYPE HTML>
<html>
<head>
<meta charset=utf-8>'."\n\n";

// Use a JavaScript redirect for non Internet-Explorer Browsers.
echo "\t".'<!--[if !IE]><!-->'."\n";
echo "\t".'<script type="text/javascript">window.location.href="'.$destination_url.'";</script>'."\n";
echo "\t".'<!--<![endif]-->'."\n\n";

echo '</head>'."\n";
echo '<body>'."\n";

// For Non Internet Explorer Browsers: Provide a clickable link as a fallback alternative for browsers without JavaScript
echo "\t".'<!--[if !IE]><!-->'."\n";
echo "\t".'<noscript><a href="'.$destination_url.'">Click here to continue to '.$destination_url.'</a></noscript>'."\n";
echo "\t".'<!--<![endif]-->'."\n\n";

// For Internet Explorer Browsers: Provide a clickable link. Use JavaScript to "autoclick" the link and redirect.
echo "\t".'<!--[if IE]>'."\n";
echo "\t".'<a href="'.$destination_url.'" id="forwarding_url">Click here to continue to '.$destination_url.'</a>'."\n";
echo "\t".'<script type="text/javascript">document.getElementById(\'forwarding_url\').click();</script>'."\n";
echo "\t".'<![endif]-->'."\n\n";

echo '</body>
</html>';

?>