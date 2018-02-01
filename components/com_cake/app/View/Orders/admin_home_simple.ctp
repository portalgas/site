<?php
$urlBase = Configure::read('App.server').'/administrator/index.php?option=com_cake&';

if($popup=='N') {
	
	if($results['Delivery']['sys']=='N')
		$label_crumb = __('Order home').': '.__('Supplier').' <b>'.$results['SuppliersOrganization']['name'].'</b> '.__('piecesToDelivery').' <b>'.$results['Delivery']['luogoData'].'</b>';
	else
		$label_crumb = __('Order home').': '.__('Supplier').' <b>'.$results['SuppliersOrganization']['name'].'</b> '.__('piecesToDelivery').' <b>'.$results['Delivery']['luogo'].'</b>';
	
	$this->Html->addCrumb(__('Home'),array('controller' => 'Pages', 'action' => 'home'));
	$this->Html->addCrumb(__('List Orders'),array('controller' => 'Orders', 'action' => 'index'));
	$this->Html->addCrumb($label_crumb);
	echo $this->Html->getCrumbList(array('class'=>'crumbs'));
	
	echo '<div id="introHelp" style="background-color:#cccccc;border-radius:3px;overflow:hidden;">';
}
else {
?>
	<div class="cakeContainer">
		<div class="popupWrap" style="display:block;opacity:1;">
			<div class="popup popupHelpOnline popupBig">
				<div class="popup_header">      
				 
				     <a class="button-close"></a>      
				 		
						<div id="introHelp" style="overflow:hidden;display:block">
<?php
}	
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
		echo '<a title="'.__($orderAction['OrdersAction']['label']).'" href="'.$urlBase.$orderAction['OrdersAction']['url'].'">';
		echo '<img alt="'.__($orderAction['OrdersAction']['label']).'" src="'.Configure::read('App.img.cake').'/help-online/'.$orderAction['OrdersAction']['img'].'" />';
		echo '</a>';
		echo '</li>';
	}	
}
echo '</ul>';

if($popup=='N') {
	echo '</div>';
}
else{
	echo '</div>';
	echo '</div>';
	echo '</div>';
	echo '</div>';
	echo '</div>';
	echo '</div>';
}
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