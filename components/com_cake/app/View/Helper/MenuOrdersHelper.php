<?php
class MenuOrdersHelper extends AppHelper {
        
     var $helpers = array('Html', 'App');

     public function drawWrapper($order_id, $options = array()) {

		$html = "";
		
		if(!isset($order_id) || empty($order_id)) 
			return $html;
		
		/*
		 * OPTIONS
		 * di default e' close
		 */
		$optOpenCloseClassCss = '';
		if(isset($options['openCloseClassCss']))
			$optOpenCloseClassCss = $options['openCloseClassCss'];
		
		/*
		 * nav-min per gestione ridotta
		 */
		$html .= '<nav class="nav-min-disabled navbar navbar-fixed-left navbar-minimal animate '.$optOpenCloseClassCss.'" role="navigation">';
		$html .= '	<div class="navbar-toggler animate">';
		$html .= '		<span class="menu-icon"></span>';
		$html .= '	</div>';

		$html .= '	<ul id="order-sotto-menu-'.$order_id.'" class="navbar-menu animat order-sotto-menu-uniquee">';
		$html .= '	</ul>';
		
		$html .= '</nav>';
		
		$html .= '<script type="text/javascript">';
		$html .= 'viewOrderSottoMenuBootstrap('.$order_id.');';
		$html .= '</script>';
		
		if($optOpenCloseClassCss=='open') {
			$html .= '<style>';
			$html .= '.cakeContainer div.contentMenuLaterale {padding-left:320px;}';
			$html .= '</style>';			
		}	
		
		
		return $html;
	}
	
    public function drawContent($results, $desOrder, $orderActions, $orderStates, $options = array()) {

		$html = "";
		
		if(empty($results['Order'])) 
			return $html;
		
		/*
		 * OPTIONS
		 * di default e' close
		 */
		$optLinkListOrders = false;
		if(isset($options['linkListOrders']))
			$optLinkListOrders = $options['linkListOrders'];
		
		$urlBase = Configure::read('App.server').'/administrator/index.php?option=com_cake&';

		if(!empty($desOrder)) {
			$html .= '<h2>';
			// if($desOrder['des_id']==$user->des_id) 
				$html .= $this->Html->link(__('DesOrder'), array('controller' => 'DesOrdersOrganizations', 'action' => 'index', null, 'des_order_id='.$desOrder['DesOrder']['id']), array('style' => 'color:#fff;', 'title' => ""));
			//else
			//	$html .=  __('DesOrder');
			$html .= $this->App->drawDesOrdersStateDiv($desOrder['DesOrder']);
			$html .= '<br />'; 
			$html .= '<small style="color:#fff;">'.__($desOrder['DesOrder']['state_code'].'-label').'</small>';		
			$html .= '</h2>';
		}

        if($optLinkListOrders)
			$html .= '<li>'.$this->Html->link('<span class="desc animate"> '.__('List Orders').' </span><span style="float: right;height: 32px;width: 32px;display: inline-block;" class="action actionReload"></span>', array('controller' => 'Orders', 'action' => 'index'), ['class' => 'animate', 'escape' => false]).'</li>';
/*
        $html .= '<li>';
        $html .= '<a class="animate">';
        $html .= '<span class="desc animate"> '.__('Importo totale ordine').' </span>';
        $html .= '<span style="float: right;display: inline-block;">'.number_format($results['Order']['tot_importo'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'&nbsp;&euro;</span></a>';
        $html .= '</li>';
*/
		foreach($orderActions as $orderAction) {
			//debug($orderAction);exit;
			$label = __($orderAction['OrdersAction']['label']);
			/*
			 * dettaglio importo trasport, cost_more, cost_less
			 */
			if(!empty($orderAction['OrdersAction']['label_more']))
				$label = $label.' ('.$results['Order'][$orderAction['OrdersAction']['label_more']].' €)';
			
			$html .=  '		<li>';

			if(!empty($orderAction['OrdersAction']['neo_url']))
				$html .= '<a title="'.$orderAction['OrdersAction']['title'].'" href="'.$orderAction['OrdersAction']['neo_url'].'"><span class="desc animate"> '.$label .' </span><span style="float: right;height: 32px;width: 32px;display: inline-block;" class="'.$orderAction['OrdersAction']['css_class'].'"></span></a>';
			else
				$html .=  $this->Html->link('<span class="desc animate"> '.$label .' </span><span style="float: right;height: 32px;width: 32px;display: inline-block;" class="'.$orderAction['OrdersAction']['css_class'].'"></span>',  
								  $urlBase.$orderAction['OrdersAction']['url'], 
								  ['class' => 'animate', 'escape' => false]);
			$html .=  '		</li>';
			
		}

		
		/*
		 * gestione O R D E R S - S T A T E S
		 */	
		$html .= '<div class="clearfix"></div>';
		$html .= '<div class="menuOrderStatoTitle">Ciclo dell\'ordine</div>';
	
		$html .= '<ul class="menuLateraleItems">';
		foreach($orderStates as $orderState) {
			
			$html .= "\r\n";
			
			
			if($results['Order']['state_code'] == $orderState['TemplatesOrdersState']['state_code']) {
				$html .= '<li class="statoCurrent">';
				$html .= '<a title="'.__($orderState['TemplatesOrdersState']['state_code'].'-intro').'" ';
				$html .= '	 class="bgRight orderStato'.$orderState['TemplatesOrdersState']['state_code'].'" ';
				$html .= '	 style="text-decoration:none;font-weight:bold;cursor:pointer;color:#fff;" ';
				
				/*
				 * eventuale azione successiva 
				 */
				if(!empty($orderState['TemplatesOrdersState']['action_controller']) && !empty($orderState['TemplatesOrdersState']['action_action'])) 
					$html .= 'href="'.$urlBase.'controller='.$orderState['TemplatesOrdersState']['action_controller'].'&action='.$orderState['TemplatesOrdersState']['action_action'].'&delivery_id='.$results['Order']['delivery_id'].'&order_id='.$results['Order']['id'].'" ';	
				
				$html .= '>';
				$html .= __($orderState['TemplatesOrdersState']['state_code'].'-label');
				
				if(!empty($orderState['TemplatesOrdersState']['action_controller']) && !empty($orderState['TemplatesOrdersState']['action_action']))
					$html .= '<br />'.__($orderState['TemplatesOrdersState']['state_code'].'-action');
				
				$html .= '</a>';
				$html .= '</li>';	
			} 
			else {
				$html .= '<li class="statoNotCurrent">';
				$html .= '<a title="'.__($orderState['TemplatesOrdersState']['state_code'].'-intro').'" ';
				$html .= '	 class="bgRight orderStato'.$orderState['TemplatesOrdersState']['state_code'].'" ';
				$html .= '	 style="text-decoration:none;font-weight:normal;cursor:default;color:#fff;" ';	
				$html .= '>';
				$html .= __($orderState['TemplatesOrdersState']['state_code'].'-label');
				$html .= '</a>';
				$html .= '</li>';			
			}

		} // end foreach($orderStates as $orderState)
		$html .= '</ul>';

		return $html;
	}
}
?>