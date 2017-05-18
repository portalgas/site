<?php
if(!isset($class_msg))  $class_msg = 'message';  // message success notice

/*
 * msg per desktop/table
 */
echo '<div id="system-message-container" class="hidden-xs">';
echo '<dl id="system-message">';
echo '<dt class="'.$class_msg.'">Message</dt>';
echo '<dd class="'.$class_msg.' message">';
echo '<ul><li>'.$msg.'</li></ul>';
echo '</dd>';
echo '</dl>';
echo '</div>';


/*
 * msg per smartphone
 */
switch ($class_msg) {
    case "message":
        $class_msg = "info";
        break;
    case "notice":
        $class_msg = "warning";
        break;
    case "success":
        $class_msg = "success";
        break;
}

echo '<div class="hidden-sm hidden-md hidden-lg">';
echo '<div class="alert alert-'.$class_msg.'" role="alert"><a data-dismiss="alert" class="close" href="#">×</a><strong>'.$msg.'</div>';
echo '</div>';
?>
