<style>
.cakeContainer ul, .cakeContainer li {
    font-size: 16px;
    list-style: inherit;
} 
</style>
<?php
$this->Html->addCrumb(__('Home'),array('controller' => 'Pages', 'action' => 'home'));
$this->Html->addCrumb(__('List Orders'), array('controller' => 'Orders', 'action' => 'index'));
$this->Html->addCrumb(__('Add Easy Order'));
echo $this->Html->getCrumbList(array('class'=>'crumbs'));

echo '<div class="contentMenuLaterale">';

echo $this->Form->create('Order', array('id' => 'formGas'));
echo '<fieldset>';
echo '<legend>'.__('Add Easy Order').'</legend>';


/*
 * messaggio di spiegazione
 */
echo '<div class="legenda legenda-ico-info" style="width:94%">';
echo "<h3>Qui potrai creare con un click il tuo ordine:</h3>";
echo '<ul>';
echo "<li>Scegli il <b>produttore</b></li>";
echo "<li>Scegli la data della <b>consegna</b></li>";
echo "<li>Scegli quando dovrà essere <b>aperto</b> l'ordine (i gasisti potranno effettuare gli <b>acquisti</b>)</li>";
echo "<li>Scegli quando dovrà <b>chiudersi</b> l'ordine</li>";
echo "<li>PortAlGas invierà ai gasisti una <b>mail</b> per notificare l'apertura dell'ordine: se lo desireri aggiungi un <b>testo</b> per personalizzare il messaggio</li>";
echo "<li>PortAlGas associerà in automatico tutti gli <b>articoli</b> attivi all'ordine</li>";
echo "<li>Una volta creato potrai sempre <b>modificarlo</b></li>";
echo '</ul>';
echo '</div>';
echo $this->Html->div('clearfix','');

	/*
	 * Supplier
	 */
	$options = array('id' => 'supplier_organization_id', 
					 'data-placeholder' => 'Scegli un produttore',
					 'options' => $ACLsuppliersOrganization, 
					 'default' => $supplier_organization_id, 
					 'required' => 'false', 
					 'after' => '<div class="col-md-2" id="suppliers_organization_details"></div>');
	if(count($ACLsuppliersOrganization) > Configure::read('HtmlSelectWithSearchNum')) 
		$options += array('class'=> 'form-control selectpicker', 'data-live-search' => true); 
	else
	if(count($ACLsuppliersOrganization) > 1)
		$options += array('empty' => Configure::read('option.empty'),
						  'class' => 'form-control'); 
	echo '<div class="row">';
	echo '<div class="col-md-12">';
	echo $this->Form->input('supplier_organization_id', $options);
	echo '</div>';
	echo '</div>';		
	
	/*
	 * consegna
	 */
	echo $this->element('boxOrdersDelivery', array('modalita' => 'ADD', 'isManagerDelivery' => $isManagerDelivery));
	echo $this->Html->div('clearfix','');
	
	echo $this->Form->input('data_inizio',array('type' => 'text','size'=>'30','label' => __('Data inizio'), 'value' => $data_inizio, 'required'=>'false', 'class' => 'form-control'));
	echo $this->Ajax->datepicker('OrderDataInizio',array('dateFormat' => 'DD, d MM yy','altField' => '#OrderDataInizioDb', 'altFormat' => 'yy-mm-dd'));
	echo '<input type="hidden" id="OrderDataInizioDb" name="data[Order][data_inizio_db]" value="'.$data_inizio_db.'" />';
	
	echo $this->Form->input('data_fine',array('type' => 'text','size'=>'30','label' => __('Data fine'), 'value' => $data_fine, 'required'=>'false', 'class' => 'form-control'));
	echo $this->Ajax->datepicker('OrderDataFine',array('dateFormat' => 'DD, d MM yy','altField' => '#OrderDataFineDb', 'altFormat' => 'yy-mm-dd'));
	echo '<input type="hidden" id="OrderDataFineDb" name="data[Order][data_fine_db]" value="'.$data_fine_db.'" />';
	
	echo $this->Form->input('mail_open_testo', array('class' => 'form-control', 'after' => '<img width="100" class="print_screen" id="print_screen_mail_open_testo" src="'.Configure::read('App.img.cake').'/print_screen_mail_open_testo.jpg" title="" border="0" />'));
	

echo '<input type="hidden" name="data[Order][organization_id]" value="'.$this->Form->value('Order.organization_id').'" />'; // serve per ModelOrder::date_comparison_to_delivery
echo '<input type="hidden" name="data[Order][des_supplier_id]" value="0" />';

echo '<input type="hidden" name="data[Order][qta_massima_um]" value="" />';
echo '<input type="hidden" name="data[Order][qta_massima]" value="0" />';
echo '<input type="hidden" name="data[Order][importo_massimo]" value="0" />';
echo '<input type="hidden" name="data[Order][typeGest]" value="" />';
echo '<input type="hidden" name="data[Order][hasTrasport]" value="N" />';
echo '<input type="hidden" name="data[Order][hasCostMore]" value="N" />';
echo '<input type="hidden" name="data[Order][hasCostLess]" value="N" />';
echo '</fieldset>';

echo $this->Form->end(__('Submit'));

echo '</div>';


$links = [];
$links[] = $this->Html->link('<span class="desc animate"> '.__('List Orders').' </span><span class="fa fa-reply"></span>', array('controller' => 'Orders', 'action' => 'index'), ['class' => 'animate', 'escape' => false]);
echo $this->Menu->draw($links);

echo $this->element('print_screen_order');
echo $this->element('send_mail_popup');
?>

<script type="text/javascript">
function suppliersOrganizationDetails(supplier_organization_id) {
	if(supplier_organization_id!=undefined && supplier_organization_id!=0 && supplier_organization_id!='') {
		var url = "/administrator/index.php?option=com_cake&controller=Ajax&action=suppliersOrganizationDetails&supplier_organization_id="+supplier_organization_id+"&des_order_id=0&format=notmpl";
		var idDivTarget = 'suppliers_organization_details';
		ajaxCallBox(url, idDivTarget);		
	}
}	
 
var dialogueIsSubmitting = false;

var dialogMessage = jQuery('<div id="dialog"></div>')
  	 .html('')
     .dialog({
		autoOpen: false,
		width: 450,
		modal: true,
		draggable:false,
		resizable:false,
		title: "Invio mail all'apertura dell'ordine",
	    buttons: {
	      "Ok": function () {
	      	dialogueIsSubmitting = true;
	        jQuery(this).dialog("close");
	        jQuery('#formGas').submit();
	      }
	    },
 		open: function() {
 			var orderDataInizioDb = jQuery('#OrderDataInizioDb').val();
 			if(orderDataInizioDb=='' || orderDataInizioDb==undefined) 
 	 			testo = "Devi indicare la data di apertura dell'ordine";
 	 		else {
	 			var resultCompare = compare_date_today(orderDataInizioDb);
	
	 			var testo = "";
	 			if(resultCompare=='<') testo = "<b>Non</b> verrà inviata alcuna <b>mail</b> ai gasisti perchè la data di apertura dell'ordine è <b>antecedente</b> alla data odierna";
	 			else
	 				if(resultCompare=='=') testo = "Verrà inviata la <b>mail</b> ai gasisti per notificare dell'apertura dell'ordine <b>questa notte</b>";
	 			else
	 				if(resultCompare=='>') testo = "Verrà inviata la <b>mail</b> ai gasisti per notificare dell'apertura dell'ordine il <b>giorno stesso</b> dell'apertura dell'ordine"; 
 			}

				
 			jQuery("#dialog").html(testo);
		}
	});

jQuery(document).ready(function() {
	
	jQuery('#supplier_organization_id').change(function() {
		var supplier_organization_id = jQuery(this).val();
		suppliersOrganizationDetails(supplier_organization_id);
	});
	
	jQuery('#formGas').submit(function() {
	
		var supplier_organization_id = jQuery('#supplier_organization_id').val();
		if(supplier_organization_id=='' || supplier_organization_id==undefined) {
			alert("<?php echo __('jsAlertSupplierRequired');?>");
			jQuery('.tabs').tabs('option', 'active',0);
			jQuery('#supplier_organization_id').focus();
			return false;
		}

		var typeDelivery = jQuery("input[name='typeDelivery']:checked").val();
		if(typeDelivery==undefined || typeDelivery!='to_defined') {
			var delivery_id = jQuery('#delivery_id').val();
			if(delivery_id=='' || delivery_id==undefined) {
				alert("<?php echo __('jsAlertDeliveryRequired');?>");
				jQuery('.tabs').tabs('option', 'active',0);
				jQuery('#delivery_id').focus();
				return false;
			}	    
		}
		
		var orderDataInizioDb = jQuery('#OrderDataInizioDb').val();
		if(orderDataInizioDb=='' || orderDataInizioDb==undefined) {
			jQuery('.tabs').tabs('option', 'active',0);
			alert("Devi indicare la data di apertura dell'ordine");
			return false;
		}	
		
		var OrderDataFineDb = jQuery('#OrderDataFineDb').val();
		if(OrderDataFineDb=='' || OrderDataFineDb==undefined) {
			jQuery('.tabs').tabs('option', 'active',0);
			alert("Devi indicare la data di chiusura dell'ordine");
			return false;
		}	
				
		if(dialogueIsSubmitting) return true;
		else {
			dialogMessage.dialog('open');
			return false;
		}
	});

	jQuery('.sendMail').click(function() {
		
		var pass_org_id = jQuery(this).attr('pass_org_id');
		var pass_id = jQuery(this).attr('pass_id');
		var pass_entity = jQuery(this).attr('pass_entity');

		/*
		console.log("pass_org_id "+pass_org_id);
		console.log("pass_id "+pass_id);
		console.log("pass_entity "+pass_entity);
		*/
		jQuery('#pass_org_id').val(pass_org_id);
		jQuery('#pass_id').val(pass_id);
		jQuery('#pass_entity').val(pass_entity);
		
		jQuery("#dialog-send_mail").dialog("open");

		return false;	
	});
	
});
</script>