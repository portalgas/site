<?php
if(!isset($class_msg))  $class_msg = 'message';  // messsage success notice

echo '<div class="box-message">';
	echo '<div class="'.$class_msg.'" id="flashMessage">';
	if(!isset($msg))
		echo __('msg_search_not_result');
	else
		echo $msg;
	echo '</div>';
echo '</div>';
?>
