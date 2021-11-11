<?php
if($results['ProdDelivery']['isVisibleBackOffice']=='Y') {
	echo "\r\n"; 
	echo '<ul class="menuLateraleItems">';
	echo "\r\n";

	for($i=0; $i < count($actionsProdDeliveries); $i++) { 	 
	
		echo "\r\n";
		echo '<li>';
		if(empty($actionsProdDeliveries[$i]['url'])) 
			echo '<div style="font-weight:bold;color:#003D4C;" title="'.$actionsProdDeliveries[$i]['label'].'" class="'.$position_img.' '.$actionsProdDeliveries[$i]['css_class'].'" >'.$actionsProdDeliveries[$i]['label'].'</div>';
		else 
			echo $this->Html->link($actionsProdDeliveries[$i]['label'],
								   $actionsProdDeliveries[$i]['url'],
								   array('class' => $position_img.' '.$actionsProdDeliveries[$i]['css_class'],
								   		 'title' => $actionsProdDeliveries[$i]['label'])
								   	);
		echo '</li>';
	} // end for		
	
	echo '</ul>';
		
	/*
	 * gestione P R O D - D E L I V E R I E S - S T A T E S
	 */	
	if($results['ProdDelivery']['prod_delivery_state_id']>=Configure::read('OPEN')) {
	   
	    $urlBase = Configure::read('App.server').'/administrator/index.php?option=com_cake&';
	   
		echo '<div class="clearfix"></div>';
		echo '<h3>Stato</h3>';
		
		echo '<ul class="menuLateraleItems">';
		foreach ($prodDeliveriesStates as $prodDeliveriesState) {
			echo "\r\n";
			echo '<li style="';
			if($results['ProdDelivery']['prod_delivery_state_id']!=$prodDeliveriesState['ProdDeliveriesState']['id'])  {
				echo 'opacity: 0.5;';
				echo '">';
				echo '<a title="'.$prodDeliveriesState['ProdDeliveriesState']['intro'].'" class="'.$position_img.' orderStato'.$prodDeliveriesState['ProdDeliveriesState']['code'].'" style="text-decoration:none;cursor:default;">';
				echo $prodDeliveriesState['ProdDeliveriesState']['label'];
				echo '</a>';
			}
			else {
				echo '" class="statoCurrent">';
				echo '<a title="'.$prodDeliveriesState['ProdDeliveriesState']['intro'].'" class="'.$position_img.' orderStato'.$prodDeliveriesState['ProdDeliveriesState']['code'].'" style="text-decoration:none;cursor:default;"><b>';
				echo $prodDeliveriesState['ProdDeliveriesState']['label'];
				echo '</b></a>';
			}
			echo '</li>';
		}
		echo '</ul>';
	} // end if($results['ProdDelivery']['prod_delivery_state_id']>=Configure::read('OPEN'))
} // end if($results['ProdDelivery']['isVisibleBackOffice']=='Y') 
?>