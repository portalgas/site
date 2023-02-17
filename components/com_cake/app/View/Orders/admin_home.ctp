<?php
$urlBase = Configure::read('App.server').'/administrator/index.php?option=com_cake&';

if(empty($des_order_id))
	$crum_label_home = __('Order home');
else
	$crum_label_home = __('Order home DES');

if($results['Delivery']['sys']=='N')
	$crum_label = $crum_label_home.': '.__('Supplier').' <b>'.$results['SuppliersOrganization']['name'].'</b> '.__('piecesToDelivery').' <b>'.$results['Delivery']['luogoData'].'</b>';
else
	$crum_label = $crum_label_home.': '.__('Supplier').' <b>'.$results['SuppliersOrganization']['name'].'</b> '.__('piecesToDelivery').' <b>'.$results['Delivery']['luogo'].'</b>';

	$this->Html->addCrumb(__('Home'), ['controller' => 'Pages', 'action' => 'home']);
	$this->Html->addCrumb(__('List Orders'),array('controller' => 'Orders', 'action' => 'index'));
	$this->Html->addCrumb($crum_label);
	echo $this->Html->getCrumbList(array('class'=>'crumbs'));
		
	echo '<div class="contentMenuLaterale">';
	
	if(!empty($des_order_id))
		echo $this->element('boxDesOrder', array('results' => $desOrdersResults));

	if(!empty($promotionResults))
		echo $this->element('boxProdGasPromotion', array('results' => $promotionResults));

	echo "<div style='clear:both;'></div>";
			
	echo '<div id="introHelp" style="background-color:#cccccc;border-radius:3px;overflow:hidden;">';

	echo '<table cellpadding = "0" cellspacing = "0">';
	echo '<tr>';
	echo '	<th style="border-radius:5px;">'.$this->App->drawOrdersStateDiv($results).'&nbsp;'.__($results['Order']['state_code'].'-label').'</th>';
	echo '</tr>';
	echo '</table>';


echo '<ul class="helpOnline">';


if(!empty($raggruppamentoOrderActions))  {
	
	/*
	 * gestione con il raggruppamento
	 */
	foreach($raggruppamentoOrderActions as $raggruppamentoOrderAction) {
		
		if($raggruppamentoOrderAction['tot_figli']>1) {
			echo "\r\n";
			echo '<li id="'.$raggruppamentoOrderAction['controller'].'">';
			echo '<a title="'.__($raggruppamentoOrderAction['label']).'">';
			echo '<img alt="'.__($raggruppamentoOrderAction['label']).'" src="'.Configure::read('App.img.cake').'/help-online/'.$raggruppamentoOrderAction['img'].'" />';
			echo '</a>';
			
			echo '<ul class="helpOnline" id="'.$raggruppamentoOrderAction['controller'].'Content" style="display:none;">';
		}
			
		foreach($orderActions as $orderAction) {
		
			if($orderAction['OrdersAction']['controller']==$raggruppamentoOrderAction['controller']) {
				echo "\r\n";
				echo '<li>';
				if(!empty($orderAction['OrdersAction']['neo_url']))
					echo '<a title="'.__($orderAction['OrdersAction']['label']).'" href="'.$orderAction['OrdersAction']['neo_url'].'">';
				else
					echo '<a title="'.__($orderAction['OrdersAction']['label']).'" href="'.$urlBase.$orderAction['OrdersAction']['url'].'">';

				echo '<img alt="'.__($orderAction['OrdersAction']['label']).'" src="'.Configure::read('App.img.cake').'/help-online/'.$orderAction['OrdersAction']['img'].'" />';
				echo '</a>';
				echo '</li>';
			}
		}
		
		if($raggruppamentoOrderAction['tot_figli']>1) {
			echo '</li>';
			echo '</ul>';
		}	
		
	}	
}
else {
	/*
	 * gestione SENZA il raggruppamento
	*/
	foreach($orderActions as $orderAction) {	
		echo "\r\n";
		echo '<li>';
		if(!empty($orderAction['OrdersAction']['neo_url']))
			echo '<a title="'.__($orderAction['OrdersAction']['label']).'" href="'.$orderAction['OrdersAction']['neo_url'].'">';
		else
			echo '<a title="'.__($orderAction['OrdersAction']['label']).'" href="'.$urlBase.$orderAction['OrdersAction']['url'].'">';
		echo '<img alt="'.__($orderAction['OrdersAction']['label']).'" src="'.Configure::read('App.img.cake').'/help-online/'.$orderAction['OrdersAction']['img'].'" />';
		echo '</a>';
		echo '</li>';
	}	
}
echo '</ul>';

echo '</div>';
echo '</div>';

$options = [];
$options['openCloseClassCss'] = 'open';
echo $this->MenuOrders->drawWrapper($results['Order']['id'], $options);
?>
<script type="text/javascript">
$(document).ready(function() {
<?php 
foreach($raggruppamentoOrderActions as $raggruppamentoOrderAction) {

	echo 'var '.$raggruppamentoOrderAction['controller'].' = false;';
	echo "\r\n";
?>
	$('#<?php echo $raggruppamentoOrderAction['controller'];?> > a').click(function() {
		allLiSiblings = $(this).parent().siblings();
		if(<?php echo $raggruppamentoOrderAction['controller'];?>) {
			$(this).css('opacity','1');
			$(allLiSiblings).css('display','block');
			$('#<?php echo $raggruppamentoOrderAction['controller'];?>Content').fadeOut();
			<?php echo $raggruppamentoOrderAction['controller'];?> = false;
		}
		else {
			$(this).css('opacity','0.3');
			$(allLiSiblings).css('display','none');
			$('#<?php echo $raggruppamentoOrderAction['controller'];?>Content').fadeIn();
			<?php echo $raggruppamentoOrderAction['controller'];?> = true;
		}
	});


	<?php 
	}
	?>
});
</script>