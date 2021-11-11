<?php 
exit;

require_once ('/var/www/portalgas/google/autoload.php');

$debug = false;

session_name('PortAlgas');
session_start();


$calendar_id = 'uphuu5m4egkslr5k4ibubkn13s@group.calendar.google.com';  // Gas La Cavagnetta (TO)@portalgas.it@gmail.com 
 
// $client_id Client ID 
$service_client_id = '317847689931-95cgtaogot4bnmt70audi0d30mq76fp4.apps.googleusercontent.com';
$client_id = '317847689931-ltnq3244cit3mojunmtee3cqtacc4tdh.apps.googleusercontent.com';

//$service_account_name Email Address
$service_account_name = '317847689931-95cgtaogot4bnmt70audi0d30mq76fp4@developer.gserviceaccount.com';

$key_file_location = '/var/www/portalgas/cert/portalgas-5b553a227069.p12'; 
$privateKey = file_get_contents($key_file_location);

if (strpos($client_id, "googleusercontent") == false
    || !strlen($service_account_name)
    || !strlen($key_file_location)) {
  echo missingServiceAccountDetailsWarning();
  exit;
}

 
$client = new Google_Client();
$client->setApplicationName("PortAlGas");
//$client->setAccessType('offline'); // online default: offline (restituisce refresh_token)

					 
$scopes = array(
		'https://www.googleapis.com/auth/calendar',
		'https://www.googleapis.com/auth/calendar.readonly'
		);
 
$auth_credentials = new Google_Auth_AssertionCredentials($service_account_name, $scopes,  $privateKey);
$auth_credentials->sub = "portalgas.it@gmail.com";
if($debug) {
	echo "<pre>auth_credentials ";
	print_r($auth_credentials); 
	echo "</pre>";
}

$client->setAssertionCredentials($auth_credentials);
if ($client->getAuth()->isAccessTokenExpired()) {
  $client->getAuth()->refreshTokenWithAssertion($auth_credentials);
}

$_SESSION['access_token'] = $client->getAccessToken();

if($debug) {
	echo "<pre>SESSION ";
	print_r($_SESSION); 
	echo "</pre>";
}

	
/*
if($debug) {
	echo "<pre>CLIENT ";
	print_r($client); 
	echo "</pre>";
}
*/

		$service = new Google_Service_Calendar($client);

		if($debug) {
			echo "<pre>SERVICE ";
			print_r($service); 
			echo "</pre>";
		}		
  
		$event = new Google_Service_Calendar_Event();
		$event->setDescription("Descrizione<ul><li>111</li><li>111</li><li>111</li><li>111</li><li>111</li><li>111</li><li>111</li><li>111</li><li>111</li><li>111</li><li>111</li><li>111</li><li>111</li><li>111</li><li>111</li><li>111</li><li>111</li><li>111</li><li>111</li><li>111</li><li>111</li><li>111</li><li>111</li><li>111</li><li>111</li><li>111</li><li>111</li></ul> ");
		$event->setSummary('Consegna Cartiera 3');
		$event->setLocation('Torino');
		
		$start = new Google_Service_Calendar_EventDateTime();
		//$start->setDateTime('2015-05-16T10:00:00.000-07:00');
		$start->setDate('2015-05-20');
		$event->setStart($start);
		
		$end = new Google_Service_Calendar_EventDateTime();
		//$end->setDateTime('2015-05-17T10:25:00.000-07:00');
		$end->setDate('2015-05-20');
		$event->setEnd($end);
				
		$createdEvent = $service->events->insert($calendar_id, $event);  // 'primary'

		echo '<br />eventId '.$createdEvent->getId();
?>