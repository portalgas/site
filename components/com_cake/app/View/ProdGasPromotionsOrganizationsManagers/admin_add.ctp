<?php
/*
echo "<pre>";
print_r($promotionResults);
echo "</pre>";
*/
$this->Html->addCrumb(__('Home'),array('controller' => 'Pages', 'action' => 'home'));
$this->Html->addCrumb(__('List ProdGasPromotions New'), array('controller' => 'ProdGasPromotions', 'action' => 'index_new'));
$this->Html->addCrumb(__('Add Order ProdGasPromotion'));
echo $this->Html->getCrumbList(array('class'=>'crumbs'));

echo '<div class="orders form">';

echo $this->Form->create('ProdGasPromotionsOrganizationsManager',array('id' => 'formGas'));
echo '<fieldset>';

echo '<legend>'.__('Add Order ProdGasPromotion').'</legend>';
	
echo $this->element('boxProdGasPromotion', array('results' => $promotionResults));

echo '<div class="tabs">';
echo '<ul>';
echo '<li><a href="#tabs-0"><span>'.__('Dati ordine').'</span></a></li>';
echo '<li><a href="#tabs-1"><span>'.__('Nota referente').'</span></a></li>';
if($user->organization['Organization']['payToDelivery']=='ON' || $user->organization['Organization']['payToDelivery']=='ON-POST')
	echo '<li><a href="#tabs-2"><span>'.__('Dopo l\'arrivo della merce').'</span></a></li>';
else
if($user->organization['Organization']['payToDelivery']=='POST')
	echo '<li><a href="#tabs-2"><span>'.__('Dopo la consegna').'</span></a></li>';
echo '</ul>';

echo '<div id="tabs-0">';
	
	/*
	 * consegna
	 */
	echo $this->element('boxOrdersDelivery', array('modalita' => 'ADD', 'isManagerDelivery' => $isManagerDelivery));
	echo $this->Html->div('clearfix','');
	
	echo $this->Form->input('data_inizio',array('type' => 'text','size'=>'30','label' => __('Data inizio'), 'value' => $data_inizio, 'required'=>'false'));
	echo $this->Ajax->datepicker('ProdGasPromotionsOrganizationsManagerDataInizio',array('dateFormat' => 'DD, d MM yy','altField' => '#ProdGasPromotionsOrganizationsManagerDataInizioDb', 'altFormat' => 'yy-mm-dd'));
	echo '<input type="hidden" id="ProdGasPromotionsOrganizationsManagerDataInizioDb" name="data[ProdGasPromotionsOrganizationsManager][data_inizio_db]" value="'.$data_inizio_db.'" />';
	
	echo $this->Form->input('data_fine',array('type' => 'text','size'=>'30','label' => __('Data fine'), 'value' => $data_fine, 'required'=>'false'));
	echo $this->Ajax->datepicker('ProdGasPromotionsOrganizationsManagerDataFine',array('dateFormat' => 'DD, d MM yy','altField' => '#ProdGasPromotionsOrganizationsManagerDataFineDb', 'altFormat' => 'yy-mm-dd'));
	echo '<input type="hidden" id="ProdGasPromotionsOrganizationsManagerDataFineDb" name="data[ProdGasPromotionsOrganizationsManager][data_fine_db]" value="'.$data_fine_db.'" />';
	
	/* 
	 * data chiusura ordine promozione
	 */
	echo '<div class="input text ">';
	echo '<label>'.__('Data fine max promotion').'</label>';
	echo $this->Time->i18nFormat($promotionResults['ProdGasPromotion']['prod_gas_promotion_data_fine'],"%A, %e %B %Y");
	echo '<input type="hidden" name="data[ProdGasPromotionsOrganizationsManager][prod_gas_promotion_data_fine]" value="'.$promotionResults['ProdGasPromotion']['prod_gas_promotion_data_fine'].'" />';
	echo '</div>';	
	
	echo $this->Form->input('nota', array('type' => 'text', 'after' => '<img width="150" class="print_screen" id="print_screen_order_nota" src="'.Configure::read('App.img.cake').'/print_screen_order_nota.jpg" title="" border="0" />'));

	echo $this->Html->div('clearfix','');
	
echo '</div>';
echo '<div id="tabs-1">';
	
    echo '<div id="mail_order_open_Y">';
    echo $this->Form->input('mail_open_testo', array('type' => 'textarea', 'after' => '<img width="100" class="print_screen" id="print_screen_mail_open_testo" src="'.Configure::read('App.img.cake').'/print_screen_mail_open_testo.jpg" title="" border="0" />'));	
    echo $this->element('legendaOrdersSendMail', array('modalita' => 'ADD'));
	echo $this->element('legendaOrderTestoMailFrontEnd');
    echo '</div>';
    
    echo '<div style="display:none;" id="mail_order_open_N">'.__('msg_mail_order_open_N').'</div>';
    echo '<div style="display:none;" id="mail_order_close_Y"></div>';
    echo '<div style="display:none;" id="mail_order_close_N">'.__('msg_mail_order_close_N').'</div>';
    
echo '</div>';


if($user->organization['Organization']['payToDelivery']=='ON' || $user->organization['Organization']['payToDelivery']=='POST' || $user->organization['Organization']['payToDelivery']=='ON-POST') {
	echo '<div id="tabs-2">';
	
		echo $this->element('boxOrdersTypeGest', array('modalita' => 'ADD', 'value'=>$this->Form->value('Order.typeGest')));
		
		echo $this->Html->div('clearfix','');
		
		echo '<div class="input">';
		echo '<label>'.__('HasTrasport').'</label>';
		echo '<div style="width:75%;float: right;">';
		echo '<div class="action actionTrasport"></div>';
		if($promotionResults['ProdGasPromotionsOrganization']['hasTrasport']=='Y')
			echo ' ('.$promotionResults['ProdGasPromotionsOrganization']['trasport_e'].')';
		else
			echo $this->App->traslateEnum($promotionResults['ProdGasPromotionsOrganization']['hasTrasport']);
		echo '</div>';
		echo '</div>';
	
		echo '<div class="input">';
		echo '<label>'.__('HasCostMore').'</label>';
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

echo '</fieldset>';

echo '<input type="hidden" value="'.$promotionResults['ProdGasPromotion']['supplier_id'].'" name="data[ProdGasPromotionsOrganizationsManager][supplier_id]" />';
echo '<input type="hidden" value="'.$promotionResults['ProdGasPromotion']['id'].'" name="data[ProdGasPromotionsOrganizationsManager][prod_gas_promotion_id]" />'; 
echo '<input type="hidden" value="'.$promotionResults['ProdGasPromotionsOrganization']['hasTrasport'].'" name="data[ProdGasPromotionsOrganizationsManager][hasTrasport]" />';
echo '<input type="hidden" value="'.$promotionResults['ProdGasPromotionsOrganization']['hasCostMore'].'" name="data[ProdGasPromotionsOrganizationsManager][hasCostMore]" />';
echo $this->Form->end(__('Submit'));

echo '</div>';

echo '<div class="actions">';
echo '<h3>'.__('Actions').'</h3>';
echo '<ul>';
echo '<li>'.$this->Html->link(__('List ProdGasPromotions'), array('controller' => 'ProdGasPromotions', 'action' => 'index_new'),array('class'=>'action actionReload')).'</li>';
echo '</ul>';
echo '</div>';

echo $this->element('print_screen_order');
echo $this->element('send_mail_popup');
?>

<script type="text/javascript">
var mail_order_open = true; 
 
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
                    var orderDataInizioDb = jQuery('#ProdGasPromotionsOrganizationsManagerDataInizioDb').val();
                    if(orderDataInizioDb=='' || orderDataInizioDb==undefined) 
                            testo = "Devi indicare la data di apertura dell'ordine";
                    else {
                            var resultCompare = compare_date_today(orderDataInizioDb);

                            var testo = "";
                            if(resultCompare=='<') testo = "<b>Non</b> verr&agrave; inviata alcuna <b>mail</b> ai gasisti perch&egrave; la data di apertura dell'ordine &egrave; <b>antecedente</b> alla data odierna";
                            else
                                    if(resultCompare=='=') testo = "Verr&agrave; inviata la <b>mail</b> ai gasisti per notificare dell'apertura dell'ordine <b>questa notte</b>";
                            else
                                    if(resultCompare=='>') testo = "Verr&agrave; inviata la <b>mail</b> ai gasisti per notificare dell'apertura dell'ordine il <b>giorno stesso</b> dell'apertura dell'ordine"; 
                    }


                    jQuery("#dialog").html(testo);
		}
	});

jQuery(document).ready(function() {

	jQuery(function() {
		jQuery( ".tabs" ).tabs({
			event: "click"
		});
	});	
	
	
	var battute = <?php echo Configure::read('OrderNotaMaxLen');?>;
	
	jQuery("input[name='data[ProdGasPromotionsOrganizationsManager][nota]']").after("<p style='float:right' class='avviso'>Hai ancora <strong>"+ (battute - jQuery("input[name='data[ProdGasPromotionsOrganizationsManager][nota]']").val().length)+"</strong> caratteri disponibili</p>");

	jQuery("input[name='data[ProdGasPromotionsOrganizationsManager][nota]']").keyup(function() {
		if(jQuery(this).val().length > battute) {
			$(this).val($(this).val().substr(0, battute));
		}
		$(this).parent().find('p.avviso').html("Hai ancora <strong>"+ (battute - $(this).val().length)+"</strong> caratteri disponibili");
	});
	
	jQuery('#formGas').submit(function() {
	
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
		
		var prodGasPromotionDataInizioDb = jQuery('#ProdGasPromotionsOrganizationsManagerDataInizioDb').val();
		if(prodGasPromotionDataInizioDb=='' || prodGasPromotionDataInizioDb==undefined) {
			jQuery('.tabs').tabs('option', 'active',0);
			alert("Devi indicare la data di apertura dell'ordine");
			return false;
		}	
		
		var ProdGasPromotionsOrganizationsManagerDataFineDb = jQuery('#ProdGasPromotionsOrganizationsManagerDataFineDb').val();
		if(ProdGasPromotionsOrganizationsManagerDataFineDb=='' || ProdGasPromotionsOrganizationsManagerDataFineDb==undefined) {
			jQuery('.tabs').tabs('option', 'active',0);
			alert("Devi indicare la data di chiusura dell'ordine");
			return false;
		}	
				
		if(dialogueIsSubmitting) return true;
		else {
                    if(mail_order_open)	{
                        dialogMessage.dialog('open');
                        return false;
                    }
                    else
                        return true;
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