<?php
/*
* richiamato da 
* View/Article/admin_edit.ctp
* View/Article/admin_update_cart.ctp
*/

$msg = '';
$msg .= 'L\'Articolo che desideri modificare &egrave; associato';

if(isset($articlesStoreroomsResults)) {
	if(count($articlesStoreroomsResults)==1) $msg .= ' alla seguente consegna';
	else $msg .= ' alle seguenti consegne';
	$msg .= '</div>';
	
	$msg .= '<table cellspacing="0" cellpadding="0"><tbody>';
	$msg .= '<tr>';
	$msg .= '<th>'.__('Delivery').'</th>';
	$msg .= '<th>'.__('StateDelivery').'</th>';
	$msg .= '</tr>';
	$msg .= '</tbody>';
	
	foreach ($articlesStoreroomsResults as $i => $articlesStoreroom) {
		
		$msg .= '<tr>';
		$msg .= '<td>';
		$msg .= $articlesStoreroom['Delivery']['luogo'].' '.$this->Time->i18nFormat($articlesStoreroom['Delivery']['data'],"%A %e %B %Y");
		$msg .= '</td>';
		$msg .= '<td>';
		
		$msg .= '</td>';	
		$msg .= '</tr>';	
	}
	$msg .= '</table>';
}
echo $this->element('boxMsg',array('class_msg' => 'message', 'msg' => $msg));
?>