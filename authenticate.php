<?php

include_once "templates/base.php";
session_start();

//set_include_path("../src/" . PATH_SEPARATOR . get_include_path());
require_once 'Google/Client.php';
require_once 'Google/Service/Drive.php';

/************************************************
  ATTENTION: Fill in these values! Make sure
  the redirect URI is to this page, e.g:
  http://localhost:8080/fileupload.php
 ************************************************/
$client_id = '341466217311-su43erq97fhimv1q4lsc8lin0fqtp8a6.apps.googleusercontent.com';
$client_secret = 'Wx84T1fTeqHtPma0UZSJust5';
$redirect_uri = 'http://localhost/Britton-Price/britton-price-service-docs/authenticate.php';
//$redirect_uri = 'http://britton-price-service-docs.appspot.com/authenticate.php';
//$refresh_token = '1/cF37-FAADr8GpqA4FdQGt7SJMXiwB2uyWFPgwWlL4dQ';
$refresh_token = '1/Mkr3xvKjSWS4J-QxwMW2RVa3Dk2wmrsrt1hc7VuYUws';

$client = new Google_Client();
$client->setClientId($client_id);
$client->setClientSecret($client_secret);
$client->setRedirectUri($redirect_uri);
//$client->addScope(["https://www.googleapis.com/auth/drive.file", "https://www.googleapis.com/auth/drive.readonly"]);
$client->addScope(["https://www.googleapis.com/auth/drive"]);		// Requires full scope in order to download files created from server
$client->setAccessType("offline");
$client->setApprovalPrompt("auto");		// ("force")				// NOTE: Change to 'force' to obtain a new refresh token

$authUrl = $client->createAuthUrl();
$json = array();

function cors() {
  if (isset($_SERVER['HTTP_ORIGIN'])) {
	if ($_SERVER['HTTP_ORIGIN'] == "http://clientserver.com") {
	  header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
	  header("Access-Control-Allow-Credentials: true");
	  header("Access-Control-Allow-Methods: GET,PUT");
	  header("Access-Control-Allow-Headers: X-Requested-With");
	}
  }
}

if (isset($_GET['code'])) { 
  try {
	$client->authenticate($_GET['code']);
	$_SESSION['upload_token'] = $client->getAccessToken();
	$redirect = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];
	header('Location: ' . filter_var($redirect, FILTER_SANITIZE_URL));
  }
  catch (Google_Auth_Exception $e) {
	var_dump("The following error has occurred: (" . $e->getCode() . ")  " . $e->getMessage() . "\n");
  }
} else { 
  if (isset($_SESSION['upload_token']) && $_SESSION['upload_token']) {
	try {
	  $client->setAccessToken($_SESSION['upload_token']);
	  if ($client->isAccessTokenExpired()) {
		unset($_SESSION['upload_token']);
	  } else {
		$json = json_decode($_SESSION['upload_token'], true);
		// Return Access Token back to client	NOTE: Comment out the next three lines when obtaining a new refresh token
		header("response:" . $_SESSION['upload_token']);
		cors();
		exit;
	  }
	}
	catch (Google_Auth_Exception $e) {
	  var_dump("The following error has occurred: (" . $e->getCode() . ")  " . $e->getMessage() . "\n");
	}
  }
}
if (isset($_GET['refresh'])) {
  // Get new access token from refresh token
  try {
	$client->refreshToken($refresh_token);
	$_SESSION['upload_token'] = $client->getAccessToken();
	$redirect = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];
	header('Location: ' . filter_var($redirect, FILTER_SANITIZE_URL));
	cors();
  }
  catch (Google_Auth_Exception $e) {
	var_dump("The following error has occurred: (" . $e->getCode() . ")  " . $e->getMessage() . "\n");
  }
}

echo pageHeader("Get Google Authorisation", "images/Logo_on_blue_background.JPG");
if (
  $client_id != '341466217311-su43erq97fhimv1q4lsc8lin0fqtp8a6.apps.googleusercontent.com'
  || $client_secret != 'Wx84T1fTeqHtPma0UZSJust5'
  || $redirect_uri != 'http://localhost/clientserver/authenticate.php') {
  //|| $redirect_uri != 'http://clientserver/authenticate.php') {
  echo $redirect_uri;
  echo missingClientSecretsWarning();
}
?>
<div class="box">
	<center>
	  <div class="request">
		<?php if (isset($authUrl)): ?>
		  <div><a href=<?php echo $authUrl; ?> style="font-size: 14px; font-weight: bold;">Get new refresh token</a></div>
		  <div style="padding: 10px 0px 0px 0px;"><a href="authenticate.php?refresh=true" style="font-size: 14px; font-weight: bold;">Obtain new access token using refresh token.</a></div>
		<?php endif; ?>
	  </div>
      <div style="padding: 10px 0px 0px 0px;">
		
	</center>
</div>

<div style="padding: 50px 0px 0px 0px;">
  <div style="width: 10%; display: inline; float: left">Access Token: </div>
  <div id=accessToken style="display: inline;"><?php if (isset($json["access_token"])) {echo $json["access_token"];} ?></div>
</div>
<div style="padding: 10px 0px 0px 0px;">
  <div style="width: 10%; display: inline; float: left">Token Type: </div>
  <div id=tokenType style="display: inline;"><?php if (isset($json["token_type"])) {echo $json["token_type"];} ?></div>
</div>
<div style="padding: 10px 0px 0px 0px;">
  <div style="width: 10%; display: inline; float: left">Expires In: </div>
  <div id=tokenExpires style="display: inline;"><?php if (isset($json["expires_in"])) {echo $json["expires_in"];} ?></div>
</div>
<div style="padding: 10px 0px 0px 0px;">
  <div style="width: 10%; display: inline; float: left">Created: </div>
  <div id=createdWhen style="display: inline;"><?php if (isset($json["created"])) {echo $json["created"];} ?></div>
</div>
<div style="padding: 10px 0px 0px 0px;">
  <div style="width: 10%; display: inline; float: left">Refresh Token: </div>
  <div id=refreshToken style="display: inline;">
    <?php if (isset($json["refresh_token"])) {
			echo $json["refresh_token"];
		  } else {
		    echo $refresh_token;
		  }
	?>
  </div>
 </div>
