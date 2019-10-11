<?php
if(!empty($msg)) {
	if(!isset($class_msg))  $class_msg = 'message';  // messsage success notice
	
	switch($class_msg) {
		case "messsage":
		case "info":
			$class_msg = 'info';
		break;
		case "success":
			$class_msg = 'success';
		break;
		case "notice":
		case "danger":
			$class_msg = 'danger';
		break;
		default:
			$class_msg = 'info';
		break;
	}
	
	echo '<p>'; 
	echo '<div class="alert alert-'.$class_msg.' alert-dismissable">';
	echo ' <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>';
	if(!isset($msg))
		echo __('msg_search_not_result');
	else
		echo $msg;
	echo '</div>';
	echo '</p>';
}
?>