<?php
/*
 * per i TEST
$results['Order']['state_code'] = 'WAIT-PROCESSED-TESORIERE';
*/

$urlBase = Configure::read('App.server').'/administrator/index.php?option=com_cake&';

if(!empty($des_order_id)) {
	echo '<h2>';
	if($desOrdersResults['DesOrder']['des_id']==$user->des_id)
		echo $this->Html->link(__('DesOrder'), array('controller' => 'DesOrdersOrganizations', 'action' => 'index', null, 'des_order_id='.$des_order_id), array('title' => ""));
	else
	echo __('DesOrder');
	echo $this->App->drawDesOrdersStateDiv($desOrdersResults['DesOrder']);
	echo '<br />';
	echo '<small style="color:#000;">'.__($desOrdersResults['DesOrder']['state_code'].'-label').'</small>';
	echo '</h2>';
}

if(!empty($results['Order']['gas_group_id'])) {
	echo '<h2>';
	echo __('Gas Group Orders').' '.$gasGroup['GasGroup']['name'];
	echo '</h2>';
}

/*
 * menu Orders::index $position_img=='bgLeft'
 */
echo "\r\n";
echo '<ul class="menuLateraleItems">';
echo "\r\n";


foreach($orderActions as $orderAction) {

    $label = strip_tags(__($orderAction['OrdersAction']['label']));

	/*
	 * dettaglio importo trasport, cost_more, cost_less
	 */
	if(!empty($orderAction['OrdersAction']['label_more'])) 
		$label = $label.' ('.$results['Order'][$orderAction['OrdersAction']['label_more']].' €)';

	$title = strip_tags(__($orderAction['OrdersAction']['label']));

	echo "\r\n";
	echo '<li>';
	if(empty($orderAction['OrdersAction']['url']))
		echo '<div style="font-weight:bold;color:#003D4C;" title="'.__($orderAction['OrdersAction']['label']).'" class="'.$position_img.' '.$orderAction['OrdersAction']['css_class'].'" >'.$label.'</div>';
	else
		echo $this->Html->link($label,
				$urlBase.$orderAction['OrdersAction']['url'],
				array('class' => $position_img.' '.$orderAction['OrdersAction']['css_class'],
						'title' => $title)
		);
	echo '</li>';
} // end for foreach($orderActions as $orderAction)

echo '</ul>';

/*
 * gestione O R D E R S - S T A T E S
 */
echo '<div class="clearfix"></div>';
echo '<h3>Ciclo dell\'ordine</h3>';

echo '<ul class="menuLateraleItems">';
foreach($orderStates as $orderState) {

	echo "\r\n";

	if($results['Order']['state_code'] == $orderState['TemplatesOrdersState']['state_code']) {
		echo '<li class="statoCurrent">';
		echo '<a title="'.__($orderState['TemplatesOrdersState']['state_code'].'-intro').'" ';
		echo '	 class="'.$position_img.' ';
		echo '	 orderStato'.$orderState['TemplatesOrdersState']['state_code'].'" ';
		echo '	 style="text-decoration:none;font-weight:bold;cursor:pointer;"';

		/*
		 * eventuale azione successiva
		 */
		if(!empty($orderState['TemplatesOrdersState']['action_controller']) && !empty($orderState['TemplatesOrdersState']['action_action']))
			echo 'href="'.$urlBase.'controller='.$orderState['TemplatesOrdersState']['action_controller'].'&action='.$orderState['TemplatesOrdersState']['action_action'].'&delivery_id='.$results['Order']['delivery_id'].'&order_id='.$results['Order']['id'].'" ';

		echo '>';
		echo __($orderState['TemplatesOrdersState']['state_code'].'-label');

		if(!empty($orderState['TemplatesOrdersState']['action_controller']) && !empty($orderState['TemplatesOrdersState']['action_action']))
			echo '<br />'.__($orderState['TemplatesOrdersState']['state_code'].'-action');

		echo '</a>';
		echo '</li>';
	}
	else {
		echo '<li class="statoNotCurrent">';
		echo '<a title="'.__($orderState['TemplatesOrdersState']['state_code'].'-intro').'" ';
		echo '	 class="'.$position_img.' ';
		echo '	 orderStato'.$orderState['TemplatesOrdersState']['state_code'].'" ';
		echo '	 style="text-decoration:none;font-weight:normal;cursor:default;';
		echo '">';
		echo __($orderState['TemplatesOrdersState']['state_code'].'-label');
		echo '</a>';
		echo '</li>';
	}

} // end foreach($orderStates as $orderState)
echo '</ul>';
?>