<?php

if(!isset($class_msg))  $class_msg = 'info';
	
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

echo '<div class="container-fluid text-center">';
echo '<div class="row">';
echo '<div class="col-xs-12 col-sm-10 col-md-10">';

echo '<div class="alert alert-'.$class_msg.' alert-dismissable">';
echo ' <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>';
if(!isset($msg))
	echo __('msg_prodgas_organization_manager_found');
else
	echo $msg;
echo '</div>';

echo '</div>';
echo '<div class="col-xs-12 col-sm-2 col-md-2">';

echo $this->Html->link(__('List ProdGasPromotions HomePage'), ['controller' => 'ProdGasPromotionsOrganizationsManagers', 'action' => 'index_new'], ['class' => 'btn btn-'.$class_msg,'title' => __('List ProdGasPromotions HomePage')]);

echo '</div>';
echo '</div>';
echo '</div>';
?>