<?php
$this->Html->addCrumb(__('Home'), ['controller' => 'Pages', 'action' => 'home']);
$this->Html->addCrumb(__('List Orders'),array('controller' => 'Orders', 'action' => 'index'));
if(isset($order_id) && !empty($order_id))
	$this->Html->addCrumb(__('Order home'),array('controller'=>'Orders','action'=>'home', null, 'order_id='.$order_id));
$this->Html->addCrumb(__('Validation Carts'));
echo $this->Html->getCrumbList(array('class'=>'crumbs'));
?>
<script type="text/javascript">
var debugLocal = false;
var delivery_id = <?php echo $delivery_id; // se arrivo da Orders/admin_index.ctp e' valorizzato ?>;
var order_id = <?php echo $order_id; // se arrivo da Orders/admin_index.ctp e' valorizzato ?>;
var call_action = '<?php echo $this->action; // in base alla pagina chiamante, setto il MSG in admin_box_permission?>';

function choiceOrderPermission() {
	var div_contenitore = 'order-permission';
	showHideBox(div_contenitore,call_child=true); 
	
	var delivery_id = $('#delivery_id').val();
	var order_id    = $('#order_id').val(); /* estraggo info di delivery_id e supplier_id */

	AjaxCallToArticlesResult(delivery_id, order_id); /* chiamata Ajax l'elenco carts non validi */ 	
}

/*
 *  chiamata Ajax per elenco articoli aggregati 
 */
function AjaxCallToArticlesResult(delivery_id, order_id, articlesOptions) {
	var url = "/administrator/index.php?option=com_cake&controller=AjaxGasCodes&action=box_validation_carts&delivery_id="+delivery_id+"&order_id="+order_id+"&format=notmpl";
	var idDivTarget = 'articles-result';
	ajaxCallBox(url, idDivTarget);
}
</script>


<h2 class="ico-validation-carts">
	<?php echo __('Validation Carts');?>
	<div class="actions-img">
		<ul>
			<li><?php echo $this->Html->link(__('Order home'), array('controller' => 'Orders', 'action' => 'home', null, 'order_id='.$order_id),array('class' => 'action actionWorkflow','title' => __('Order home'))); ?></li>
		</ul>
	</div>
</h2>

<div class="contentMenuLaterale">
<?php 
echo $this->Form->create('Carts',array('id' => 'formGas', 'action' => 'todefined'));
echo $this->element('boxDesOrder', array('results' => $desOrdersResults, 'summaryDesOrderResults' => $summaryDesOrderResults));
?>
	<fieldset>
	
	<?php 
	echo $this->element('boxOrder',array('results' => $results));
	?>	
			
	<div id="articles-result" style="display:none;min-height:50px;"></div>
	
	</fieldset>
<?php 

echo $this->Form->end();

echo '</div>';

$options = [];
echo $this->MenuOrders->drawWrapper($order_id, $options);

/*
 * legenda profilata
*/
echo $this->App->drawLegenda($user, $orderStatesToLegenda);
?>