<?php
$this->Html->addCrumb(__('Home'),array('controller' => 'Pages', 'action' => 'home'));
$this->Html->addCrumb(__('List Orders'), array('controller' => 'Orders', 'action' => 'index'));
$this->Html->addCrumb('Scegli l\'ordine');
echo $this->Html->getCrumbList(array('class'=>'crumbs'));
?>
<script type="text/javascript">
function choiceDelivery() {
	var delivery_id = $('#delivery_id').val();
	if(delivery_id!='') {
		$('#formGas').submit();
	}	
}
function choiceOrder() {
	var delivery_id = $('#delivery_id').val();
	var order_id = $('#order_id').val();
	if((delivery_id!='' || delivery_id!=undefined) && (order_id!='' && order_id!=undefined)) {
		$('#formGas').submit();
	}	
}
<?php 
if(!empty($order_id) && $order_valido) {
	echo '$(document).ready(function() { ';
	echo 'choiceOrder()';
	echo '});';
}	
?>
</script>
		
<div class="orders">
	<h2 class="ico-orders">
		<?php echo __('Orders');?>
	</h2>

	
	<?php echo $this->Form->create('ArticlesOrder',array('id' => 'formGas'));?>
		<fieldset>
		
		<div id="deliveries">
		<?php
			$options = array('id'=>'delivery_id', 'onChange' => 'javascript:choiceDelivery(this);');
		    if(!empty($delivery_id))
		    	$options += array('default' => $delivery_id);
		    else
		    	$options += array('empty' => Configure::read('option.empty'));
		    
		    echo $this->Form->input('delivery_id',$options);
		?>
		</div>
				
		<div id="orders-result">
		<?php
			if($delivery_id>0) {
				if(empty($orders)) 
					echo $this->element('boxMsg',array('class_msg' => 'message', 'msg' => "Non ci sono ordini ai quali si possono modificare gli articoli associati."));
				else {
					$options =  array('id'=>'order_id', 'onChange' => 'javascript:choiceOrder(this);');
					if(!empty($order_id))						$options += array('default' => $order_id);					else						$options += array('empty' => Configure::read('option.empty'));					
					echo $this->Form->input('order_id',$options);
				}
			}
		?>	
		</div>
		
		<?php 
		if(!$order_valido) {
			echo '<div class="message" id="flashMessage">All\'ordine non si possono modificare gli articoli associati</div>';
			echo '<table cellpadding = "0" cellspacing = "0">';
			echo '<tr>';
			echo '	<th style="border-radius:5px;"><span style="font-weight: normal;">Lo stato dell\'ordine è</span> '.$this->App->drawOrdersStateDiv($results).'&nbsp;'.__($results['Order']['state_code'].'-label').'</th>';
			echo '</tr>';
			echo '</table>';
		}
		?>
		</fieldset>
	<?php echo $this->Form->end();?>
		
</div>