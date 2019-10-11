<?php
$this->App->d($promotionResults);
$this->App->d($this->request->data);

$this->Html->addCrumb(__('Home'), ['controller' => 'Pages', 'action' => 'home']);
$this->Html->addCrumb(__('List ProdGasPromotions'), array('controller' => 'ProdGasPromotions', 'action' => 'index'));
$this->Html->addCrumb(__('Order home'),array('controller'=>'Orders','action'=>'home', null, 'order_id='.$order_id));
$this->Html->addCrumb(__('Edit Order ProdGasPromotion'));
echo $this->Html->getCrumbList(array('class'=>'crumbs'));

echo '<div class="orders form">';

echo $this->Form->create('ProdGasPromotionsOrganizationsManager',array('id' => 'formGas'));
echo '<fieldset>';

echo '<legend>'.__('Edit Order ProdGasPromotion').'</legend>';
	
echo $this->element('boxProdGasPromotion', ['results' => $promotionResults, 'prodGasArticlesPromotionShow' => false]);

echo '<div class="tabs">';
echo '<ul class="nav nav-tabs">'; // nav-tabs nav-pills
echo '<li class="active"><a href="#tabs-0" data-toggle="tab">'.__('Dati ordine').'</a></li>';
echo '<li><a href="#tabs-1" data-toggle="tab">'.__('Note Referente').'</a></li>';

if($user->organization['Template']['payToDelivery']=='ON' || $user->organization['Template']['payToDelivery']=='ON-POST')
	echo '<li><a href="#tabs-2" data-toggle="tab">'.__('Dopo l\'arrivo della merce').'</a></li>';
else
if($user->organization['Template']['payToDelivery']=='POST')
	echo '<li><a href="#tabs-2" data-toggle="tab">'.__('Dopo la consegna').'</a></li>';
echo '</ul>';

echo '<div class="tab-content">';
echo '<div class="tab-pane fade active in" id="tabs-0">';
	
	/*
	 * consegna
	 */
	echo $this->element('boxOrdersDelivery', array('modalita' => 'EDIT', 'isManagerDelivery' => $isManagerDelivery));
	echo $this->Html->div('clearfix','');
	
	echo $this->App->drawDate('ProdGasPromotionsOrganizationsManager', 'data_inizio', __('DataInizio'), $promotionResults['ProdGasPromotion']['data_inizio']);
	
	if(!empty($this->request->data['Order']['data_fine_validation']) && $this->request->data['Order']['data_fine_validation']!=Configure::read('DB.field.date.empty')) {
		
		echo $this->Form->input('data_fine_validation',array('type' => 'text','size'=>'30','label' => "Riaperto l'ordine fino a", 'value' => $this->Time->i18nFormat($this->Form->value('Order.data_fine_validation'),"%A, %e %B %Y"), 'required' => 'false'));
		echo $this->Ajax->datepicker('ProdGasPromotionsOrganizationsManagerDataFineValidation',array('dateFormat' => 'DD, d MM yy','altField' => '#ProdGasPromotionsOrganizationsManagerDataFineValidationDb', 'altFormat' => 'yy-mm-dd'));
		echo '<input type="hidden" id="ProdGasPromotionsOrganizationsManagerDataFineValidationDb" name="data[ProdGasPromotionsOrganizationsManager][data_fine_validation_db]" value="'.$this->Form->value('Order.data_fine_validation').'" />';
			
		echo '<input type="hidden" id="OrderDataFineDb" name="data[Order][data_fine_db]" value="'.$this->Form->value('Order.data_fine').'" />';
	}
	else {	
		echo $this->App->drawDate('ProdGasPromotionsOrganizationsManager', 'data_fine', __('DataFine'), $promotionResults['ProdGasPromotion']['data_fine']);
	}	
		
	if($this->request->data['Order']['data_incoming_order']!=Configure::read('DB.field.date.empty')) {
		echo '<div class="input text required">';
		echo '<label>'.__('DataIncomingOrder').'</label> ';
		echo $this->Time->i18nFormat($this->Form->value('Order.data_incoming_order'),"%A, %e %B %Y");
		echo '</div>';
	}
		
	/* 
	 * data chiusura ordine promozione
	 */
	echo '<div class="input text  alert alert-warning">';
	echo '<label>'.__('DataFineMaxPromotion').'</label> ';
	echo $this->Time->i18nFormat($promotionResults['ProdGasPromotion']['prod_gas_promotion_data_fine'],"%A, %e %B %Y");
	echo '<input type="hidden" name="data[ProdGasPromotionsOrganizationsManager][prod_gas_promotion_data_fine]" value="'.$promotionResults['ProdGasPromotion']['prod_gas_promotion_data_fine'].'" />';
	echo '</div>';	
	
	echo $this->Form->input('nota', array('type' => 'text', 'after' => '<img width="150" class="print_screen" id="print_screen_order_nota" src="'.Configure::read('App.img.cake').'/print_screen_order_nota.jpg" title="" border="0" />'));

	echo $this->Html->div('clearfix','');
	
echo '</div>';
echo '<div class="tab-pane fade" id="tabs-1">';
	
    echo '<div id="mail_order_open_Y">';
    echo $this->Form->input('mail_open_testo', array('type' => 'textarea', 'after' => '<img width="100" class="print_screen" id="print_screen_mail_open_testo" src="'.Configure::read('App.img.cake').'/print_screen_mail_open_testo.jpg" title="" border="0" />'));	
    echo $this->element('legendaOrdersSendMail', array('modalita' => 'ADD'));
	echo $this->element('legendaOrderTestoMailFrontEnd');
    echo '</div>';
    
    echo '<div style="display:none;" id="mail_order_open_N">'.__('msg_mail_order_open_N').'</div>';
    echo '<div style="display:none;" id="mail_order_close_Y"></div>';
    echo '<div style="display:none;" id="mail_order_close_N">'.__('msg_mail_order_close_N').'</div>';
    
echo '</div>';


if($user->organization['Template']['payToDelivery']=='ON' || $user->organization['Template']['payToDelivery']=='POST' || $user->organization['Template']['payToDelivery']=='ON-POST') {
	echo '<div class="tab-pane fade" id="tabs-2">';
	
		echo $this->element('boxOrdersTypeGest', array('modalita' => 'ADD', 'value'=>$this->Form->value('Order.typeGest')));
		
		echo $this->Html->div('clearfix','');
		
		echo '<div class="input">';
		echo '<label>'.__('HasTrasport').'</label> ';
		echo '<div style="width:75%;float: right;">';
		echo '<div class="action actionTrasport"></div>';
		if($promotionResults['ProdGasPromotionsOrganization']['hasTrasport']=='Y')
			echo ' ('.$promotionResults['ProdGasPromotionsOrganization']['trasport_e'].')';
		else
			echo $this->App->traslateEnum($promotionResults['ProdGasPromotionsOrganization']['hasTrasport']);
		echo '</div>';
		echo '</div>';
	
		echo '<div class="input">';
		echo '<label>'.__('HasCostMore').'</label> ';
		echo '<div style="width:75%;float: right;">';
		echo '<div class="action actionCostMore"></div>';
		if($promotionResults['ProdGasPromotionsOrganization']['hasCostMore']=='Y')
			echo ' ('.$promotionResults['ProdGasPromotionsOrganization']['cost_more_e'].')';
		else
			echo $this->App->traslateEnum($promotionResults['ProdGasPromotionsOrganization']['hasCostMore']);
		echo '</div>';
		echo '</div>';
					
	echo '</div>';
}
echo '</div>'; // end class tab-content
echo '</fieldset>';

echo '<input type="hidden" value="'.$this->request->data['Order']['id'].'" name="data[ProdGasPromotionsOrganizationsManager][order_id]" />';
echo '<input type="hidden" value="'.$this->request->data['Order']['delivery_id'].'" name="data[ProdGasPromotionsOrganizationsManager][delivery_id]" />';
echo '<input type="hidden" value="'.$promotionResults['ProdGasPromotion']['supplier_id'].'" name="data[ProdGasPromotionsOrganizationsManager][supplier_id]" />';
echo '<input type="hidden" value="'.$promotionResults['ProdGasPromotion']['id'].'" name="data[ProdGasPromotionsOrganizationsManager][prod_gas_promotion_id]" />'; 
echo '<input type="hidden" value="'.$promotionResults['ProdGasPromotionsOrganization']['hasTrasport'].'" name="data[ProdGasPromotionsOrganizationsManager][hasTrasport]" />';
echo '<input type="hidden" value="'.$promotionResults['ProdGasPromotionsOrganization']['hasCostMore'].'" name="data[ProdGasPromotionsOrganizationsManager][hasCostMore]" />';
echo $this->Form->end(__('Submit'));

echo '</div>';

echo '<div class="actions">';
echo '<h3>'.__('Actions').'</h3>';
echo '<ul>';
echo '<li>'.$this->Html->link(__('List ProdGasPromotions'), array('controller' => 'ProdGasPromotionsOrganizationsManagers', 'action' => 'index_new'),array('class'=>'action actionReload')).'</li>';
echo '<li>'.$this->Html->link(__('Order home'), array('controller' => 'Orders', 'action' => 'home', $order_id),array('class'=>'action actionWorkflow')).'</li>';
echo '</ul>';
echo '</div>';

echo $this->element('print_screen_order');
echo $this->element('send_mail_popup');
?>

<div class="modal fade" id="modalOrderMailMsg" role="dialog">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal">&times;</button>
				<h4 class="modal-title">Invio mail all'apertura dell'ordine</h4>
			</div>
			<div class="modal-body">
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-sm btn-primary" data-dismiss="modal"><?php echo __('Close');?></button>
			</div>
		</div>
	</div>		
</div>	

<script type="text/javascript">
var mail_order_open = true; /* la function suppliersOrganizationDetails ctrl se il produttore scelto invia mail all'apertura */
var dialogueIsSubmitting = false;

$(document).ready(function() {

	var battute = <?php echo Configure::read('OrderNotaMaxLen');?>;
	
	$("input[name='data[ProdGasPromotionsOrganizationsManager][nota]']").after("<p style='float:right' class='avviso'>Hai ancora <strong>"+ (battute - $("input[name='data[ProdGasPromotionsOrganizationsManager][nota]']").val().length)+"</strong> caratteri disponibili</p>");

	$("input[name='data[ProdGasPromotionsOrganizationsManager][nota]']").keyup(function() {
		if($(this).val().length > battute) {
			$(this).val($(this).val().substr(0, battute));
		}
		$(this).parent().find('p.avviso').html("Hai ancora <strong>"+ (battute - $(this).val().length)+"</strong> caratteri disponibili");
	});
	
	$('#formGas').submit(function() {
	
		var typeDelivery = $("input[name='typeDelivery']:checked").val();
		if(typeDelivery==undefined || typeDelivery!='to_defined') {
			var delivery_id = $('#delivery_id').val();
			if(delivery_id=='' || delivery_id==undefined) {
				alert("<?php echo __('jsAlertDeliveryRequired');?>");
				$('.tabs li:eq(0) a').tab('show');
				$('#delivery_id').focus();
				return false;
			}	    
		}
		
		var prodGasPromotionDataInizioDb = $('#ProdGasPromotionsOrganizationsManagerDataInizioDb').val();
		if(prodGasPromotionDataInizioDb=='' || prodGasPromotionDataInizioDb==undefined) {
			$('.tabs li:eq(0) a').tab('show');
			alert("Devi indicare la data di apertura dell'ordine");
			return false;
		}	
		
		var ProdGasPromotionsOrganizationsManagerDataFineDb = $('#ProdGasPromotionsOrganizationsManagerDataFineDb').val();
		if(ProdGasPromotionsOrganizationsManagerDataFineDb=='' || ProdGasPromotionsOrganizationsManagerDataFineDb==undefined) {
			$('.tabs li:eq(0) a').tab('show');
			alert("Devi indicare la data di chiusura dell'ordine");
			return false;
		}	
				
		if(dialogueIsSubmitting) return true;
		else {
			if(mail_order_open)	{
				$("#modalOrderMailMsg").modal();
				return false;
			}
			else
				return true;
		}
	});

	$("#modalOrderMailMsg").on("shown.bs.modal", function () {

		var testo = "";
		var orderDataInizioDb = $('#OrderDataInizioDb').val();
		if(orderDataInizioDb=='' || orderDataInizioDb==undefined) 
				testo = "Devi indicare la data di apertura dell'ordine";
		else {
				var resultCompare = compare_date_today(orderDataInizioDb);

				if(resultCompare=='<') testo = "<b>Non</b> verr&agrave; inviata alcuna <b>mail</b> ai gasisti perch&egrave; la data di apertura dell'ordine &egrave; <b>antecedente</b> alla data odierna";
				else
						if(resultCompare=='=') testo = "Verr&agrave; inviata la <b>mail</b> ai gasisti per notificare dell'apertura dell'ordine <b>questa notte</b>";
				else
						if(resultCompare=='>') testo = "Verr&agrave; inviata la <b>mail</b> ai gasisti per notificare dell'apertura dell'ordine il <b>giorno stesso</b> dell'apertura dell'ordine"; 
		}

		$("#modalOrderMailMsg .modal-body").html(testo);	
	});
		
	$("#modalOrderMailMsg").on("hide.bs.modal", function () {
		dialogueIsSubmitting = true;
		$('#formGas').submit();                
	});
	
	$('.sendMail').click(function() {
		
		var pass_org_id = $(this).attr('pass_org_id');
		var pass_id = $(this).attr('pass_id');
		var pass_entity = $(this).attr('pass_entity');

		/*
		console.log("pass_org_id "+pass_org_id);
		console.log("pass_id "+pass_id);
		console.log("pass_entity "+pass_entity);
		*/
		$('#pass_org_id').val(pass_org_id);
		$('#pass_id').val(pass_id);
		$('#pass_entity').val(pass_entity);
		
		$("#dialog-send_mail").modal("show");

		return false;	
	});
});
</script>