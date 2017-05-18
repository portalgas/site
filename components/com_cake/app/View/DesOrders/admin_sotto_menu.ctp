<?php
/*
 * per i TEST
$results['DesOrder']['state_code'] = 'OPEN';
*/

$urlBase = Configure::read('App.server').'/administrator/index.php?option=com_cake&';

	if(!empty($des_supplier_id))
		echo '<h3 style="font-weight: bold;">'.__('DesOrder').'</h3>';
		
	echo "\r\n"; 
	echo '<ul class="menuLateraleItems">';
	echo "\r\n";

	foreach($desOrderActions as $desOrderAction) {
	
		$label = __($desOrderAction['DesOrdersAction']['label']);
		
		/*
		 * dettaglio importo trasport, cost_more, cost_less
		 */
		if(!empty($desOrderAction['DesOrdersAction']['label_more']))
			$label = $label.' ('.$results['DesOrder'][$desOrderAction['DesOrdersAction']['label_more']].' €)';
			
		echo "\r\n";
		echo '<li>';
		if(empty($desOrderAction['DesOrdersAction']['url']))
			echo '<div style="font-weight:bold;color:#003D4C;" title="'.__($desOrderAction['DesOrdersAction']['label']).'" class="'.$position_img.' '.$desOrderAction['DesOrdersAction']['css_class'].'" >'.$label.'</div>';
		else
			echo $this->Html->link($label,
					$urlBase.$desOrderAction['DesOrdersAction']['url'],
					array('class' => $position_img.' '.$desOrderAction['DesOrdersAction']['css_class'],
							'title' => __($desOrderAction['DesOrdersAction']['label']))
			);
		echo '</li>';
	} // end for foreach($desOrderActions as $desOrderAction)		
	
	echo '</ul>';
	
	/*
	 * gestione O R D E R S - S T A T E S
	 */	
	echo '<div class="clearfix"></div>';
	echo '<h3>Ciclo dell\'ordine</h3>';
	
	echo '<ul class="menuLateraleItems">';
	foreach($desOrderStates as $desOrderState) {
		
		echo "\r\n";
		
		
		if($results['DesOrder']['state_code'] == $desOrderState['TemplatesDesOrdersState']['state_code']) {
			echo '<li class="statoCurrent">';
			echo '<a title="'.__($desOrderState['TemplatesDesOrdersState']['state_code'].'-intro').'" ';
			echo '	 class="'.$position_img.' ';
			echo '	 orderStato'.$desOrderState['TemplatesDesOrdersState']['state_code'].'" ';
			echo '	 style="text-decoration:none;font-weight:bold;cursor:pointer;"';
			
			/*
			 * eventuale azione successiva 
			 */
			if(!empty($desOrderState['TemplatesDesOrdersState']['action_controller']) && !empty($desOrderState['TemplatesDesOrdersState']['action_action'])) 
				echo 'href="'.$urlBase.'controller='.$desOrderState['TemplatesDesOrdersState']['action_controller'].'&action='.$desOrderState['TemplatesDesOrdersState']['action_action'].'&des_order_id='.$results['DesOrder']['id'].'" ';	
			
			echo '>';
			echo __($desOrderState['TemplatesDesOrdersState']['state_code'].'-label');
			
			if(!empty($desOrderState['TemplatesDesOrdersState']['action_controller']) && !empty($desOrderState['TemplatesDesOrdersState']['action_action']))
				echo '<br />'.__($desOrderState['TemplatesDesOrdersState']['state_code'].'-action');
			
			echo '</a>';
			echo '</li>';	
		} 
		else {
			echo '<li class="statoNotCurrent">';
			echo '<a title="'.__($desOrderState['TemplatesDesOrdersState']['state_code'].'-intro').'" ';
			echo '	 class="'.$position_img.' ';
			echo '	 orderStato'.$desOrderState['TemplatesDesOrdersState']['state_code'].'" ';
			echo '	 style="text-decoration:none;font-weight:normal;cursor:default;';	
			echo '">';
			echo __($desOrderState['TemplatesDesOrdersState']['state_code'].'-label');
			echo '</a>';
			echo '</li>';			
		}

	} // end foreach($desOrderStates as $desOrderState)
	echo '</ul>';
?>