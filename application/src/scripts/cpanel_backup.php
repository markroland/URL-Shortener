<?php
/*
    http://www.justin-cook.com/wp/2006/12/27/automatic-cpanel-backup-domain-mysql-with-cron-php/
    PHP script to allow periodic cPanel backups automatically, optionally to a remote FTP server.
*/

// Set error reporting
error_reporting(E_ERROR);

// Load credentials
$json_cpanel_credentials = file_get_contents(realpath(__DIR__ . '/../../data/credentials') . '/cpanel.json');
$cpanel_credentials = json_decode($json_cpanel_credentials);

$json_remote_server = file_get_contents(realpath(__DIR__ . '/../../data/credentials') . '/backup-server.json');
$remote_server = json_decode($json_remote_server);

// Info for server to be backed up
$cpuser = $cpanel_credentials->CPANEL_USERNAME;
$cppass = $cpanel_credentials->CPANEL_PASSWORD;
$domain = $cpanel_credentials->CPANEL_DOMAIN;
$skin   = $cpanel_credentials->CPANEL_SKIN;

// Info for server to receive backup
define('BACKUP_HOST', $remote_server->BACKUP_SERVER_HOST);
define('BACKUP_DIR', $remote_server->BACKUP_SERVER_PATH);
define('BACKUP_USERNAME', $remote_server->BACKUP_SERVER_USERNAME);
define('BACKUP_PASSWORD', $remote_server->BACKUP_SERVER_PASSWORD);

// Select transfer mode: "ftp" for active, "passiveftp" for passive, "scp" for secure ftp
$ftpmode = 'scp';

// Notification information - Email address to send results
$notifyemail = $cpanel_credentials->CPANEL_NOTIFY_EMAIL;

// Secure or non-secure mode - Set to 1 for SSL (requires SSL support), otherwise will use standard HTTP
$secure = 1;

// Set to 1 to have web page result appear in your cron log
$debug = 0;

// *********** NO CONFIGURATION ITEMS BELOW THIS LINE *********

// Set URL and Port
if($secure){
    $url = "ssl://".$domain;
    $port = 2083;
} else {
    $url = $domain;
    $port = 2082;
}

// Set Port
switch( $ftpmode ){
    case 'ftp':
        $ftp_port = 21;
        break;
    case 'scp';
        $ftp_port = 22;
        break;
    default:
        $ftp_port = 22;
}

// Open socket connection
if( !($socket = fsockopen($url,$port)) ){
    echo "Failed to open socket connection Bailing out!\n";
    exit;
}

// Encode authentication string
$authstr = $cpuser . ":" . $cppass;
$pass = base64_encode($authstr);

// Build query string
$params = sprintf(
    "dest=%s&email=%s&server=%s&user=%s&pass=%s&rdir=%s&port=%d&submit=%s",
    $ftpmode,
    $notifyemail,
    BACKUP_HOST,
    BACKUP_USERNAME,
    BACKUP_PASSWORD,
    urlencode(BACKUP_DIR),
    $ftp_port,
    'Generate Backup'
);

// Make POST to cPanel
fputs($socket, "POST /frontend/" . $skin . "/backup/dofullbackup.html?" . $params . " HTTP/1.0\r\n");
fputs($socket, "Host: $domain\r\n");
fputs($socket, "Authorization: Basic $pass\r\n");
fputs($socket, "Connection: Close\r\n");
fputs($socket, "\r\n");

// Grab response even if we don't do anything with it.
while(!feof($socket)){
    $response = fgets($socket,4096);
    if($debug){
        echo $response;
    }
}

// Close socket
fclose($socket);

?>