<?php 
exit;

require_once ('/var/www/portalgas/google/autoload.php');

$debug = false;

session_name('PortAlgas');
session_start();

$client_id = '317847689931-ltnq3244cit3mojunmtee3cqtacc4tdh.apps.googleusercontent.com';
$client_secret = 'DDXUm8LlWwZo-HOIlIS7BN-0';
$email = '317847689931-ltnq3244cit3mojunmtee3cqtacc4tdh@developer.gserviceaccount.com';
$api_key = 'AIzaSyAv17GOXb6FwIzeKXmzSVFJ32FYEgGMthI';
$calendar_id = 'uphuu5m4egkslr5k4ibubkn13s@group.calendar.google.com';  // Gas La Cavagnetta (TO)@portalgas.it@gmail.com 
 
$client = new Google_Client();
$client->setAccessType('offline'); // online default: offline (restituisce refresh_token)
$client->setClientId($client_id);
$client->setClientSecret($client_secret);
$client->setRedirectUri('http://www.portalgas.it/gtest.php');  // "http://".$_SERVER["HTTP_HOST"].$_SERVER['PHP_SELF'];
$client->setApplicationName("PortAlGas");

/*
$client->setScopes(array('https://www.googleapis.com/auth/userinfo.email',
						 'https://www.googleapis.com/auth/userinfo.profile',
						 'https://www.googleapis.com/auth/plus.me',
						 'https://www.googleapis.com/auth/plus.login')); 
*/					 
$scopes = array(
		'https://www.googleapis.com/auth/prediction',
		'https://www.googleapis.com/auth/calendar',
		'https://www.googleapis.com/auth/calendar.readonly'
		);
$client->setScopes($scopes);
  
  
if (isset($_REQUEST['logout'])) {
  unset($_SESSION['access_token']);
	die('Logged out.');
}

if($debug) {
	echo "<pre>REQUEST GET ";
	print_r($_GET); 
	echo "</pre>";  
}
if (isset($_GET['code'])) { // we received the positive auth callback, get the token and store it in session
    $client->authenticate($_GET['code']);
    $_SESSION['access_token'] = $client->getAccessToken();
	
   $redirect = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];
   header('Location: ' . filter_var($redirect, FILTER_SANITIZE_URL));
}

	
if($debug) {
	echo "<pre>SESSION ";
	print_r($_SESSION); 
	echo "</pre>";
}
if (isset($_SESSION['access_token']) && $_SESSION['access_token']) { // extract token from session and configure client
    $client->setAccessToken($_SESSION['access_token']);
}
else {
  $authUrl = $client->createAuthUrl();
}

if($debug) {
	echo "<pre>CLIENT ";
	print_r($client); 
	echo "</pre>";
}

if (!$client->getAccessToken()) { // auth call to google

	if($debug) 
		echo "<h1>!$client->getAccessToken()</h1>";
	
    $authUrl = $client->createAuthUrl();
    header("Location: ".$authUrl);
    die;
}
else {
	echo "<h2>".$client->getAccessToken()."</h2>";
	echo "<h2>".$client->getRefreshToken()."</h2>";
 
	if($client->isAccessTokenExpired())
		echo "<br />Token isAccessTokenExpired => SCADUTO";
	else
		echo "<br />Token NOT isAccessTokenExpired => VALIDO";

	 $client->refreshToken($client->getRefreshToken());
	
		$service = new Google_Service_Calendar($client);

		if($debug) {
			echo "<pre>SERVICE ";
			print_r($service); 
			echo "</pre>";
		}		
		//$calList = $service->calendarList->listCalendarList();
		//print "<h1>Calendar List</h1><pre>" . print_r($calList, true) . "</pre>";
  
		$event = new Google_Service_Calendar_Event();
		$event->setDescription("Descrizione");
		$event->setSummary('Consegna Cartiera');
		$event->setLocation('Turin');
		
		$start = new Google_Service_Calendar_EventDateTime();
		$start->setDateTime('2015-05-03T10:00:00.000-07:00');
		$event->setStart($start);
		
		$end = new Google_Service_Calendar_EventDateTime();
		$end->setDateTime('2015-05-04T10:25:00.000-07:00');
		$event->setEnd($end);
		
		/*
		$attendee1 = new Google_Service_Calendar_EventAttendee();
		$attendee1->setEmail('francesco.actis@gmail.com');
		$attendees = array($attendee1,
						   // ...
						  );
		$event->attendees = $attendees;
		*/
	
	$acl = $service->acl->listAcl($calendar_id); // 'primary'
	foreach ($acl->getItems() as $rule) {
	  echo '<br />rule '.$rule->getId() . ': ' . $rule->getRole();
	}
	/*
	$scope = new Google_Service_Calendar_AclRuleScope();
	$scope->setType('user');
	$scope->setValue('francesco.actis@gmail.com');

	$rule = new Google_Service_Calendar_AclRule();
	$rule->setRole('owner'); // reader 
	$rule->setScope($scope);

	$createdRule  = $service->acl->insert($calendar_id, $rule); // 'primary'
	echo '<br />createdRule Id '.$createdRule->getId();
	*/		

	$createdEvent = $service->events->insert($calendar_id, $event);  // 'primary'

	echo '<br />eventId '.$createdEvent->getId();
exit;


  // $service implements the client interface, has to be set before auth call
  $plus   = new Google_PlusService($client);
  $oauth2 = new Google_Oauth2Service($client);
		
  $user = $oauth2->userinfo->get();
  $me   = $plus->people->get('me');

	if($debug) {
		echo "<pre>";
		print_r($user);
		echo "</pre>";
		echo "<pre>";
		print_r($me);
		echo "</pre>";
	}

 // These fields are currently filtered through the PHP sanitize filters.
  // See http://www.php.net/manual/en/filter.filters.sanitize.php


  $url = filter_var($me['url'], FILTER_VALIDATE_URL);

  $img = filter_var($me['image']['url'], FILTER_VALIDATE_URL);

  $name = filter_var($me['displayName'], FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH);

  $personMarkup = "<a rel='me' href='$url'>$name</a><div><img src='$img'></div>";

  $emails = $me['emails'][0]['value'];
  
  $optParams = array('maxResults' => 10);
  $activities = $plus->activities->listActivities('me', 'public', $optParams);

  $activityMarkup = '';
  foreach($activities['items'] as $activity) {
    $url = filter_var($activity['url'], FILTER_VALIDATE_URL);
    $title = filter_var($activity['title'], FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH);
    $content = filter_var($activity['object']['content'], FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH);
    var_dump($content);
    // exit;
    $activityMarkup .= "<div class='activity'><a href='$url'>$title</a><div>$content</div></div>";
  }

  $people = $plus->people->listPeople('me', 'visible', $optParams);
  if($debug) {
	  echo "<pre>";
	  print_r($people);
	  echo "</pre>";  
  }
  
  /* Creation of a single event
	$event = new Google_Event();
	$event->setSummary($event_name);            
	$event->setLocation('');
	*/
} 
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
</head>
<body>
<header><h1>Google+ App</h1></header>
<div class="box">

<?php if(isset($personMarkup)): ?>
<div class="me"><?php print $personMarkup ?></div>
<?php endif ?>


<?php if(isset($emails)): ?>
<div class="me"><?php print $emails ?></div>
<?php endif ?>


<?php if(isset($activityMarkup)): ?>
<div class="activities">Your Activities: <?php print $activityMarkup ?></div>
<?php endif ?>

<?php
foreach($people['items'] as $person) {
	echo '<hr>';
	echo '<br /><a href="'.$person['url'].'" target="_blank">';
	echo $person['displayName'];
	echo '<img src="'.$person['image']['url'].'" />';
	echo '</a>';
}
?>
<br />
<br />
<?php
  if(isset($authUrl)) {
    print "<a class='login' href='$authUrl'>Connect Me!</a>";
  } else {
   print "<a class='logout' href='?logout'>Logout</a>";
  }
?>
</div>
</body>
</html>