<?php

/**
 * Front Controller for URL Shortening service
 *
 * PHP version >= 5.6
 *
 * Copyright 2017 Mark Roland
 *
 * @category  Markroland.com
 * @package   Markroland.com
 * @author    Mark Roland <code@markroland.com>
 * @copyright 2017 Mark Roland
 * @version   GIT: $Id$
 * @link      https://github.com/markroland/URL-Shortener
 */

// Set error reporting
// ini_set('error_reporting', E_ALL);
// ini_set('display_errors', false);

// Define a shutdown function to handle Fatal Errors
register_shutdown_function(
    function () {
        $error = error_get_last();
        if( isset($error['type']) ){
            $ignore = E_WARNING | E_NOTICE | E_USER_WARNING | E_USER_NOTICE | E_STRICT | E_DEPRECATED | E_USER_DEPRECATED;
            if (($error['type'] & $ignore) == 0) {
                /*
                error_log(
                    date('r') . ' - Fatal Error of level ' . $error['type']
                        . ' in ' . $error['file'] . " line " . $error['line'] . ": "
                        . $error['message'] . PHP_EOL
                );
                //*/
                if( !headers_sent() ){
                    header('HTTP/1.1 500 Internal Server Error');
                    // print($error['message']);
                    include realpath(__DIR__ . '/../views') . '/error.html.php';
                }
                exit(1);
            }
        }
    }
);


// Load Composer autoloader
include realpath(__DIR__ . '/../../vendor') . '/autoload.php';

$credentials_path = realpath(__DIR__ . '/../../data/credentials');

if (file_exists($credentials_path . '/' . $_SERVER['HTTP_HOST'])) {
    $credentials_path = $credentials_path . '/' . $_SERVER['HTTP_HOST'];
}

// Connect to database
$json_db_credentials = file_get_contents($credentials_path . '/mysql-user.json');
$db_credentials = json_decode($json_db_credentials);
try {
    $pdo_connection = new \PDO(
        'mysql:host=' . $db_credentials->DB_HOST . ';dbname=' . $db_credentials->DB_NAME . ';charset=' . $db_credentials->DB_CHARSET,
        $db_credentials->DB_USER,
        $db_credentials->DB_PASSWORD,
        $pdo_options
    );
    $pdo_connection->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    $pdo_connection->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log(date('r') . ' - ' . $e->getMessage() . ' in ' . __FILE__ . ':' . __LINE__ . PHP_EOL);
}

// Handle loss of database connection
if (!isset($pdo_connection)) {
    header('HTTP/1.1 503 Service Unavailable');
    include realpath(__DIR__ . '/../views') . '/db-disconnect.html.php';
    exit;
}

// Load remote API key
$json_api_key = file_get_contents($credentials_path . '/api-key.json');
$api_key_object = json_decode($json_api_key);
$api_key = $api_key_object->API_KEY;

// Set default destination
$destination_url = '';

// Parse request
$parsed_url = parse_url('http' . ($_SERVER['HTTPS'] == 'on' ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
$query_vars = parse_str($parse_url['query']);

// Create new shortener object
$UrlShortener = new \markroland\urlShortener($pdo_connection);

// Handle API request
if (preg_match('@/api/create_short_url.json@', $_SERVER['REQUEST_URI'])) {

    // Initialize response
    $response = array(
        'method' => 'create_short_url',
        'status' => 'fail'
    );

    // Authenticate
    if (strcasecmp($_REQUEST['api_key'], $api_key) == 0) {

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {

            // Check if shortcut exists
            $shortcut_exists = null;
            try {
                $shortcut_exists = $UrlShortener->get_shortcut($_POST['shortcut']);
            } catch (\Exception $e) {
                print($e->getMessage());
            }

            // Attempt to create shortcut
            if (empty($shortcut_exists)) {

                $shortcut_result = null;
                try {
                    $shortcut_result = $UrlShortener->create_short_url(
                        $_POST['shortcut'],
                        $_POST['destination'],
                        $_POST['set_referrer'] ? $_POST['set_referrer'] : 0,
                        $_POST['client_id'] ? $_POST['client_id'] : 0
                    );
                } catch (\Exception $e) {
                    print($e->getMessage());
                }

                if ($shortcut_result) {
                    $response['status'] = 'success';
                }

            } else {
                $response['code'] = 3;
                $response['error'] = 'Shortcut is unavailable';
            }
        }

    } else {

        // Fails Authentication
        $response['code'] = 1;
        $response['error'] = 'Invalid API authentication';
        $response['status'] = 'fail';

    }

    header('Content-Type: application/json; charset=utf-8');
    print(json_encode($response));
    exit;
}

elseif (preg_match('@/api/get_shortcuts.json@', $_SERVER['REQUEST_URI'])) {

    // Initialize response
    $response = array(
        'method' => 'get_shortcuts',
        'status' => 'fail'
    );

    // Authenticate
    if (strcasecmp($_REQUEST['api_key'], $api_key) == 0) {

        if ($_SERVER['REQUEST_METHOD'] == 'GET') {

            // Get shortcuts
            if (is_numeric($_GET['client_id'])) {
                $response['data'] = $UrlShortener->get_shortcuts($_GET['client_id']);
            } else {
                $response['data'] = $UrlShortener->get_shortcuts();
            }
            $response['status'] = 'success';
        }

    }else{

        // Fails Authentication
        $response['code'] = 1;
        $response['error'] = 'Invalid API authentication';
        $response['status'] = 'fail';

    }

    header('Content-Type: application/json; charset=utf-8');
    print(json_encode($response));
    exit;
}

elseif (preg_match('@/api/short_url.json@', $_SERVER['REQUEST_URI'])) {

    // Initialize response
    $response = array(
        'method' => 'short_url',
        'status' => 'fail'
    );

    // Authenticate
    if ($_SERVER['PHP_AUTH_USER'] == $api_key) {

        if ($_SERVER['REQUEST_METHOD'] == 'PUT') {

            // Handle JSON data received via PUT.
            $PUT_data = '';
            if ($_SERVER['CONTENT_TYPE'] == 'application/x-www-form-urlencoded') {
                parse_str(file_get_contents("php://input"), $PUT_data);
            }
            if ($_SERVER['CONTENT_TYPE'] == 'application/json') {
                $PUT_data = json_decode(file_get_contents("php://input"), JSON_OBJECT_AS_ARRAY);
            }

            // Perform update
            $result = null;
            try {
                $result = $UrlShortener->update_short_url(
                    $PUT_data['shortcut'],
                    $PUT_data['destination'],
                    $PUT_data['set_referrer'] ? $PUT_data['set_referrer'] : 0,
                    $PUT_data['client_id'] ? $PUT_data['client_id'] : 0
                );
            } catch (\Exception $e) {
                print($e->getMessage());
            }

            // Set response
            if ($result) {
                $response['status'] = 'success';
            } else {
                $response['code'] = 3;
                $response['error'] = 'Update Failed';
            }
        }

    } else {

        // Fails Authentication
        $response['code'] = 1;
        $response['error'] = 'Invalid API authentication';
        $response['status'] = 'fail';

    }

    header('Content-Type: application/json; charset=utf-8');
    print(json_encode($response));
    exit;
}

else {

    // Look up redirection

    $requested_shortcut = ltrim($parsed_url['path'], '/');

    // Display error if no destination URL set
    if (empty($requested_shortcut)) {
        include realpath(__DIR__ . '/../views') . '/home.html.php';
        exit;
    }

    // Get shortcuts
    try {
        $shortcut_info = $UrlShortener->get_shortcut($requested_shortcut);
    } catch (\Exception $e) {
        print($e->getMessage());
    }

    // Set destination based on database results
    if ($shortcut_info) {

        // Set destination
        $destination_url = $shortcut_info['destination_url'];

        // Replace query string parameters in destination_url with query string parameters from page request
        if ($_GET['qsa']) {

            // Split destination url into parts
            $dest_url_parts = parse_url($destination_url);

            // Replace query string parameters
            // Ignore query string parameters involved in executing this script
            parse_str($dest_url_parts['query'], $query_kvp);
            foreach( (array)$query_kvp as $key=>$val){
                if( array_key_exists($key, $_GET) && $key != 'qsa' && $key != 'shortcut') {
                    $new_query_string = $key.'='.$_GET[ $key ].'&';
                } else {
                    $new_query_string = $key.'='.$val.'&';
                }
            }
            $dest_url_parts['query'] = rtrim($new_query_string, '&');

            // Rebuild URL
            $destination_url = $dest_url_parts['scheme'] . '://' . $dest_url_parts['host']
                . $dest_url_parts['path']
                . '?' . $dest_url_parts['query']
                . $dest_url_parts['fragment'];
        }

        // Track if there was a hit
        try {
            $UrlShortener->track_shortcut_request(
                $shortcut_info['shortcut_id'],
                $_SERVER['REMOTE_ADDR'],
                $_GET['ref'] ? $_GET['ref'] : ''
            );
        } catch (\Exception $e) {
            print($e->getMessage());
        }
    }

    // Use a browser-based redirect (not server-based) so that the HTTP referrer can be properly
    // set for destination-URLs that require the domain to be the domain of this script's location.
    if ($shortcut_info['set_referrer']) {
        include realpath(__DIR__ . '/../views') . '/index.html.php';
        exit;
    }

    // Display error if no destination URL set
    if (empty($destination_url)) {
        header('HTTP/1.1 404 Not Found');
        include realpath(__DIR__ . '/../views') . '/error.html.php';
        exit;
    }

    // Redirect request
    header('Location: ' . $destination_url);
    exit;
}

// Display error if script hasn't exited by this point
include realpath(__DIR__ . '/../views') . '/error.html.php';
