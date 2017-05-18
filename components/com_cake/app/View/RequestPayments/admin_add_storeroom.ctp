<?php
$this->Html->addCrumb(__('Home'),array('controller' => 'Pages', 'action' => 'home'));
if($isReferenteTesoriere)  {
	$this->Html->addCrumb(__('List Orders'), array('controller' => 'Orders', 'action' => 'index'));
	if(isset($order_id))
		$this->Html->addCrumb(__('Order home'),array('controller'=>'Orders','action'=>'home', null, 'order_id='.$order_id));
}
else {
	if(!isset($delivery_id)) $delivery_id = 0;
		$this->Html->addCrumb(__('Tesoriere'),array('controller' => 'Tesoriere', 'action' => 'home', $delivery_id));
}
$this->Html->addCrumb(__('List Request Payments'), array('controller' => 'RequestPayments', 'action' => 'index'));
$this->Html->addCrumb(__('Add Request Payments Storeroom'));
echo $this->Html->getCrumbList(array('class'=>'crumbs'));
?>
<div class="requestPayment form">

	<h2 class="ico-pay">
		<?php echo __('Add Request Payments Storeroom')." alla richiesta numero ".$requestPaymentResults['RequestPayment']['num'];?>
	</h2>

<?php 
if(empty($deliveries))
	echo $this->element('boxMsg',array('class_msg' => 'message', 'msg' => "Non ci sono consegne alle quali si può richiedere il pagamento degli articoli in dispensa."));
else {
?>

	<?php echo $this->Form->create('FilterRequestPayment',array('id'=>'formGasFilter','type'=>'get'));?>
		<fieldset class="filter">
			<legend><?php echo __('Filter RequestPayment'); ?></legend>
			<table>
				<tr>
					<td>
						<?php echo $this->Form->input('delivery_id',array('label' => false,'options' => $deliveries,'empty' => 'Filtra per consegne','name'=>'FilterRequestPaymentDeliveryId','default'=>$FilterRequestPaymentDeliveryId,'escape' => false)); ?>
					</td>
					<td>
						<?php echo $this->Form->reset('Reset', array('value' => 'Reimposta','class' => 'reset')); ?>
					</td>
					<td>
						<?php echo $this->Form->end(array('label' => __('Filter'), 'class' => 'filter', 'div' => array('class' => 'submit filter', 'style' => 'display:none'))); ?>
					</td>
				</tr>	
			</table>
		</fieldset>					
			
<?php	
if(!empty($results)) {
	echo $this->Form->create('RequestPayment',array('id' => 'formGas'));
	?>
	<fieldset>
	
	<div class="requestPayment">
	<?php	
		$delivery_id_old = 0;
		$user_id_old = 0;
		$count=0;
		foreach ($results as $i => $result): 
	
			if($result['Storeroom']['delivery_id']!=$delivery_id_old) {
				$count=1;
				if($i>0) echo '</table>';
				
				echo '<table cellpadding="0" cellspacing="0">';
				echo '<tr>';
				echo '<th>'.__('N').'</th>';
				echo '<th>'.__('Supplier').'</th>';
				echo '<th>'.__('Name').'</th>';
				echo '<th>'.__('Conf').'</th>';
				echo '<th>'.__('PrezzoUnita').'</th>';
				echo '<th>'.__('Prezzo/UM').'</th>';
				echo '<th>'.__('qta').'</th>';
				echo '<th>'.__('Importo').'</th>';
				echo '<th>'.__('Created').'</th>';
				echo '</tr>';
			}
			else 
				$count++;
	
		
			if($result['Storeroom']['user_id']!=$user_id_old) {
				echo '<tr>';
				echo '<td colspan="9" class="trGroup">Utente: '.$result['User']['name'];
				echo '</td>';
				echo '</tr>';
			}
		?>
		<tr>
			<td><?php echo ($count); ?></td>
			<td><?php 
				echo $result['SuppliersOrganization']['SuppliersOrganization']['name']; ?></td>
			<td><?php 
				if($result['Storeroom']['stato']=='LOCK') echo '<span class="stato_lock"></span> ';
				echo $result['Storeroom']['name']; ?></td>
			<td><?php echo $this->App->getArticleConf($result['Article']['qta'], $result['Article']['um']);?></td>
			<td><?php echo $result['Storeroom']['prezzo_e'];?></td>
			<td><?php echo $this->App->getArticlePrezzoUM($result['Storeroom']['prezzo'], $result['Article']['qta'], $result['Article']['um'], $result['Article']['um_riferimento']);?></td>
			<td><?php echo $result['Storeroom']['qta'];?></td>
			<td><?php echo $this->App->getArticleImporto($result['Storeroom']['prezzo'], $result['Storeroom']['qta']);?></td>
			<td style="white-space: nowrap;"><?php echo $this->App->formatDateCreatedModifier($result['Storeroom']['created']); ?></td>
		</tr>
	<?php 
		$delivery_id_old=$result['Storeroom']['delivery_id'];
		$user_id_old=$result['Storeroom']['user_id'];
		endforeach; ?>
		</table>	
	</div>

	</fieldset>
	<?php 
	echo $this->Form->hidden('delivery_id',array('value' => $result['Storeroom']['delivery_id']));
	echo $this->Form->hidden('request_payment_id',array('value' => $requestPaymentResults['RequestPayment']['id']));
	echo $this->Form->end(__('Add Request Payments Storeroom')." alla richiesta numero ".$requestPaymentResults['RequestPayment']['num']);
} // end if(!empty($results))
else 
if($resultsFound=='N') 
	echo $this->element('boxMsg',array('class_msg' => 'message resultsNotFonud', 'msg' => __('msg_search_not_result')));

} // end if(empty($deliveries))	
	?>
</div>

<div class="actions">
	<?php include(Configure::read('App.root').Configure::read('App.component.base').'/View/RequestPayments/admin_sotto_menu.ctp');?>		
</div>


<script type="text/javascript">
<?php
if($isReferenteTesoriere) 
	echo 'viewReferenteTesoriereSottoMenu("0", "bgLeft");';
else
	echo 'viewTesoriereSottoMenu("0", "bgLeft");';
?>

jQuery(document).ready(function() {
	<?php 
	/*
	 * devo ripulire il campo hidden che inizia per page perche' dopo la prima pagina sbaglia la ricerca con filtri
	 */
	?>
	jQuery('.filter').click(function() {
		jQuery("input[name^='page']").val('');
	});
	
});		
</script>


<style type="text/css">
.cakeContainer div.form, .cakeContainer div.index, .cakeContainer div.view {
	padding-left: 5px;
    width: 74%;
}
.cakeContainer div.actions {
    width: 25%;
}
</style>