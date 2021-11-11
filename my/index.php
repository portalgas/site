<?php
if( setcookie( 'allowAdminAccess', 'p0rtAlg4s', time()+600*240, '/' ) ){
	header('Location: /administrator');
}
else {
	die('Errore nel salvare il cookie');
}    
?>