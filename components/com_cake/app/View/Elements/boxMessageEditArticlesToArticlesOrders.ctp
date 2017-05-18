<?php
/* 
* richiamato da 
* View/ArticlesOrder/...................ctp
* View/ArticlesOrder/...................ctp
*/

$msg = '';
echo 'L\'Articolo che desideri modificare &egrave; associato';

if(isset($articlesOrdersResults)) {
	if(count($articlesOrdersResults)==1) $msg .= ' al seguente ordine';
	else $msg .= ' ai seguenti ordini';
	$msg .= '</div>';
	
	$msg .= '<table cellspacing="0" cellpadding="0"><tbody>';
	$msg .= '<tr>';
	$msg .= '<th>'.__('Delivery').'</th>';
	$msg .= '<th>Periodo ordine</th>';
	$msg .= '<th>'.__('StateOrder').'</th>';
	$msg .= '</tr>';
	$msg .= '</tbody>';
	
	foreach ($articlesOrdersResults as $i => $articlesOrder) {
		
		$msg .= '<tr>';
		$msg .= '<td>';
		$msg .= $articlesOrder['Delivery']['luogo'].' '.$this->Time->i18nFormat($articlesOrder['Delivery']['data'],"%A %e %B %Y");
		$msg .= '</td>';
		$msg .= '<td>';
		$msg .= 'Da '.$this->Time->i18nFormat($articlesOrder['Order']['data_inizio'],"%A %e %B %Y");
		$msg .= '<br />a '.$this->Time->i18nFormat($articlesOrder['Order']['data_fine'],"%A %e %B %Y");
		$msg .= '</td>';
		$msg .= '<td>';	
		$msg .= $this->App->utilsCommons->getOrderTime($articlesOrder['Order']);
		$msg .= '</td>';	
		$msg .= '</tr>';	
	}
	$msg .= '</table>';
}

echo $this->element('boxMsg',array('class_msg' => 'message', 'msg' => $msg));
?>