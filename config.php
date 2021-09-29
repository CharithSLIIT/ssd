<?php 
// OAuth & site configuration 
define('OAUTH_CLIENT_ID', '924418338610-q860hij5jmqcaiujbfcephh4h3ubk2g9.apps.googleusercontent.com');  
define('OAUTH_CLIENT_SECRET', 'iIHRQGfWnT9kiMxRh5TOuY4e');  
define('BASE_URL', 'http://localhost/ssd/'); 
define('REDIRECT_URL', BASE_URL.'upload.php'); 

// Include google client libraries 
require_once 'google-api-php-client/autoload.php';  
require_once 'google-api-php-client/Client.php'; 
require_once 'google-api-php-client/Service/YouTube.php'; 

if(!session_id()) session_start(); 
 
$client = new Google_Client(); 
$client->setClientId(OAUTH_CLIENT_ID); 
$client->setClientSecret(OAUTH_CLIENT_SECRET); 
$client->setScopes('https://www.googleapis.com/auth/youtube'); 
$client->setRedirectUri(REDIRECT_URL); 
 
// Define an object that will be used to make all API requests. 
$youtube = new Google_Service_YouTube($client); 
?>