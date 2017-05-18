<?php
$this->Html->addCrumb(__('Home'),array('controller' => 'Pages', 'action' => 'home'));
$this->Html->addCrumb(__('List Orders'), array('controller' => 'Orders', 'action' => 'index'));
if(empty($des_order_id))
	$this->Html->addCrumb(__('Add Order'));
else
	$this->Html->addCrumb(__('Add DesOrder'));
echo $this->Html->getCrumbList(array('class'=>'crumbs'));

echo '<div class="contentMenuLaterale">';

echo $this->Form->create('Order',array('id' => 'formGas'));
echo '<fieldset>';

if(empty($des_order_id))
	echo '<legend>'.__('Add Order').'</legend>';
else {
	echo '<legend>'.__('Add DesOrder').'</legend>';
	echo $this->element('boxDesOrder', array('results' => $desOrdersResults));	
}	
	
	
echo '<div class="tabs">';
echo '<ul class="nav nav-tabs">'; // nav-tabs nav-pills
echo '<li class="active"><a href="#tabs-0" data-toggle="tab">'.__('Dati ordine').'</a></li>';
echo '<li><a href="#tabs-1" data-toggle="tab">'.__('Note Referente').'</a></li>';
echo '<li><a href="#tabs-2" data-toggle="tab">'.__('Per gli utenti').'</a></li>';
if(empty($des_order_id))
	echo '<li><a href="#tabs-3" data-toggle="tab">'.__('Durante l\'ordine').'</span></a></li>';
if($user->organization['Organization']['payToDelivery']=='ON' || $user->organization['Organization']['payToDelivery']=='ON-POST')
	echo '<li><a href="#tabs-4" data-toggle="tab">'.__('Dopo l\'arrivo della merce').'</a></li>';
else
if($user->organization['Organization']['payToDelivery']=='POST')
	echo '<li><a href="#tabs-4" data-toggle="tab">'.__('Dopo la consegna').'</a></li>';
echo '</ul>';

echo '<div class="tab-content">';
echo '<div class="tab-pane fade active in" id="tabs-0">';

	/*
	 * Supplier
	 */
	$options = array('id' => 'supplier_organization_id', 
					 'data-placeholder' => 'Scegli un produttore',
					 'options' => $ACLsuppliersOrganization, 
					 'default' => $supplier_organization_id, 
					 'required' => 'false', 
					 'after' => '<div class="col-md-2" id="suppliers_organization_details"></div>',
					 'empty' => Configure::read('option.empty'));
	if(count($ACLsuppliersOrganization) > Configure::read('HtmlSelectWithSearchNum')) 
		$options += array('class'=> 'selectpicker', 'data-live-search' => true); 

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
	
	echo $this->Form->input('data_inizio',array('type' => 'text','size'=>'30','label' => __('Data inizio'), 'value' => $data_inizio, 'required'=>'false'));
	echo $this->Ajax->datepicker('OrderDataInizio',array('dateFormat' => 'DD, d MM yy','altField' => '#OrderDataInizioDb', 'altFormat' => 'yy-mm-dd'));
	echo '<input type="hidden" id="OrderDataInizioDb" name="data[Order][data_inizio_db]" value="'.$data_inizio_db.'" />';
	
	echo $this->Form->input('data_fine',array('type' => 'text','size'=>'30','label' => __('Data fine'), 'value' => $data_fine, 'required'=>'false'));
	echo $this->Ajax->datepicker('OrderDataFine',array('dateFormat' => 'DD, d MM yy','altField' => '#OrderDataFineDb', 'altFormat' => 'yy-mm-dd'));
	echo '<input type="hidden" id="OrderDataFineDb" name="data[Order][data_fine_db]" value="'.$data_fine_db.'" />';
	
	/* 
	 * DES, data chiusura ordine
	 */
	if(!empty($des_order_id)) {
		echo '<div class="input text ">';
		echo '<label>'.__('Data fine max').'</label>';
		echo $this->Time->i18nFormat($desOrdersResults['DesOrder']['data_fine_max'],"%A, %e %B %Y");
		echo '</div>';	
	}
	
	echo $this->Form->input('nota', array('type' => 'text', 'after' => '<img width="150" class="print_screen" id="print_screen_order_nota" src="'.Configure::read('App.img.cake').'/print_screen_order_nota.jpg" title="" border="0" />'));
	
	echo $this->Html->div('clearfix','');
	
	if($user->organization['Organization']['hasVisibility']=='Y') {
		echo $this->Html->div('clearfix','');
		echo $this->App->drawFormRadio('Order','isVisibleFrontEnd',array('options' => $isVisibleFrontEnd, 'value'=> $isVisibleFrontEndDefault, 'label'=>__('isVisibleFrontEnd'), 'required'=>'false',
													   		    'after'=>$this->App->drawTooltip(null,__('toolTipIsVisibleFrontEnd'),$type='HELP')));
	
		echo $this->App->drawFormRadio('Order','isVisibleBackOffice',array('options' => $isVisibleBackOffice, 'value'=> $isVisibleBackOfficeDefault, 'label'=>__('isVisibleBackOffice'), 'required'=>'false',
													   		    'after'=>$this->App->drawTooltip(null,__('toolTipIsVisibleBackOffice'),$type='HELP')));
	}

echo '</div>';
echo '<div class="tab-pane fade" id="tabs-1">';
	
    echo '<div id="mail_order_open_Y">';
    echo $this->Form->input('mail_open_testo', array('after' => '<img width="100" class="print_screen" id="print_screen_mail_open_testo" src="'.Configure::read('App.img.cake').'/print_screen_mail_open_testo.jpg" title="" border="0" />'));	
    echo $this->element('legendaOrdersSendMail', array('modalita' => 'ADD'));
	echo $this->element('legendaOrderTestoMailFrontEnd');
    echo '</div>';
    
    echo '<div style="display:none;" id="mail_order_open_N">'.__('msg_mail_order_open_N').'</div>';
    echo '<div style="display:none;" id="mail_order_close_Y"></div>';
    echo '<div style="display:none;" id="mail_order_close_N">'.__('msg_mail_order_close_N').'</div>';
    
echo '</div>';

echo '<div class="tab-pane fade" id="tabs-2">';
echo $this->element('boxOrdersTypeDraw', array('modalita' => 'ADD'));
echo $this->Html->div('clearfix','');
echo '</div>';

if(empty($des_order_id))  {
	echo '<div class="tab-pane fade" id="tabs-3">';

	echo "\r\n";
	echo '<table>';
	echo '<tr>';
	echo '<td>';
	echo __('order_qta_massima');
	echo '</td>';
	echo '<td>';
	echo $this->Form->input('qta_massima', array('label' => false, 'value' => $qta_massima, 'type' => 'text', 'id' => 'qta_massima', 'size'=> 5,'class' => 'noWidth'));
	echo '</td>';
	echo '<td>';
	echo $this->Form->input('qta_massima_um',array('id' => 'qta_massima_um', 'label' => false, 'options' => $qta_massima_um_options, 'default' => $qta_massima_um, 'required' => 'false'));
	echo '</td>';
	echo '<td><div class="legenda legenda-ico-mails">'.__('order_qta_massima_help').'</div></td>';
	echo '</tr>';

	echo '<tr>';
	echo '<td>';
	echo __('order_importo_massimo');
	echo '</td>';
	echo '<td colspan="2">';
	echo $this->Form->input('importo_massimo', array('label' => false, 'value' => $importo_massimo, 'type' => 'text', 'id' => 'importo_massimo', 'size'=> 5,'class' => 'noWidth double', 'after' => '&euro;'));
	echo '</td>';
	echo '<td><div class="legenda legenda-ico-mails">'.__('order_importo_massimo_help').'</div></td>';
	echo '</tr>';
	echo '</table>';

	echo '</div>';
}

if($user->organization['Organization']['payToDelivery']=='ON' || $user->organization['Organization']['payToDelivery']=='POST' || $user->organization['Organization']['payToDelivery']=='ON-POST') {
	echo '<div class="tab-pane fade" id="tabs-4">';
	
		echo $this->element('boxOrdersTypeGest', array('modalita' => 'EDIT', 'value'=>$this->Form->value('Order.typeGest')));
		
		echo $this->Html->div('clearfix','');
		
		if($user->organization['Organization']['hasTrasport']=='Y') {
			echo $this->App->drawFormRadio('Order','hasTrasport',array('options' => $hasTrasport, 'value'=> $hasTrasportDefault, 'label'=>__('HasTrasport'), 'required'=>'false',
					'after' => '<div class="action actionTrasport"></div>'.$this->App->drawTooltip(null,__('toolTipHasTrasport'),$type='HELP')));
			echo $this->Html->div('clearfix','');
		}
		
		if($user->organization['Organization']['hasCostMore']=='Y') {
			echo $this->App->drawFormRadio('Order','hasCostMore',array('options' => $hasCostMore, 'value'=> $hasCostMoreDefault, 'label'=>__('HasCostMore'), 'required'=>'false',
				'after' => '<div class="action actionCostMore"></div>'.$this->App->drawTooltip(null,__('toolTipHasCostMore'),$type='HELP')));
			echo $this->Html->div('clearfix','');
		}
		
		if($user->organization['Organization']['hasCostLess']=='Y') {
			echo $this->App->drawFormRadio('Order','hasCostLess',array('options' => $hasCostLess, 'value'=> $hasCostLessDefault, 'label'=>__('HasCostLess'), 'required'=>'false',
				'after' => '<div class="action actionCostLess"></div>'.$this->App->drawTooltip(null,__('toolTipHasCostLess'),$type='HELP')));		
			echo $this->Html->div('clearfix','');
		}
		
	echo '</div>';
}
echo '</div>'; // end class tab-content
echo '</fieldset>';

echo '<input type="hidden" name="data[Order][organization_id]" value="'.$this->Form->value('Order.organization_id').'" />'; // serve per ModelOrder::date_comparison_to_delivery
echo '<input type="hidden" name="data[Order][des_order_id]" value="'.$des_order_id.'" />';
if(!empty($des_order_id)) {
	echo '<input type="hidden" name="data[Order][qta_massima_um]" value="" />';
	echo '<input type="hidden" name="data[Order][qta_massima]" value="0" />';
	echo '<input type="hidden" name="data[Order][importo_massimo]" value="0" />';
	echo '<input type="hidden" name="data[Order][des_data_fine_max]" value="'.$desOrdersResults['DesOrder']['data_fine_max'].'" />';
}
else { 
	/*
	 * setto una data futura cosi' non si blocca al controllo OrderModel::dateToDesDataFineMax
	 */
	echo '<input type="hidden" name="data[Order][des_data_fine_max]" value="2200-01-01" />';
}

echo $this->Form->end(__('Submit'));
echo '</div>';


$links = [];
$links[] = $this->Html->link('<span class="desc animate"> '.__('List Orders').' </span><span class="fa fa-reply"></span>', array('controller' => 'Orders', 'action' => 'index'), ['class' => 'animate', 'escape' => false]);
$links[] = $this->Html->link('<span class="desc animate"> '.__('Add Easy Order').' </span><span class="fa fa-plus-square"></span>', array('controller' => 'Orders', 'action' => 'easy_add'), ['class' => 'animate', 'escape' => false]);
$links[] = $this->Html->link('<span class="desc animate"> '.__('List DesOrders').' </span><span class="fa fa-reply"></span>', array('controller' => 'DesOrders', 'action' => 'index'), ['class' => 'animate', 'escape' => false]); 
echo $this->Menu->draw($links);

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
            
function suppliersOrganizationDetails(supplier_organization_id) {
	if(supplier_organization_id!=undefined && supplier_organization_id!=0 && supplier_organization_id!='') {
		var url = "/administrator/index.php?option=com_cake&controller=Ajax&action=suppliersOrganizationDetails&supplier_organization_id="+supplier_organization_id+"&des_order_id=<?php echo $des_order_id;?>&format=notmpl";
		var idDivTarget = 'suppliers_organization_details';
		ajaxCallBox(url, idDivTarget);		
	}
}	
 
$(document).ready(function() {

	var battute = <?php echo Configure::read('OrderNotaMaxLen');?>;
	
	$("input[name='data[Order][nota]']").after("<p style='float:right' class='avviso'>Hai ancora <strong>"+ (battute - $("input[name='data[Order][nota]']").val().length)+"</strong> caratteri disponibili</p>");

	$("input[name='data[Order][nota]']").keyup(function() {
		if($(this).val().length > battute) {
			$(this).val($(this).val().substr(0, battute));
		}
		$(this).parent().find('p.avviso').html("Hai ancora <strong>"+ (battute - $(this).val().length)+"</strong> caratteri disponibili");
	});
	
	$('#qta_massima').focusout(function() {validateNumberField(this,'quantita\' massima');});
	$('.double').focusout(function() {validateNumberField(this,'importo massimo');});
	
	$('#supplier_organization_id').change(function() {
		var supplier_organization_id = $(this).val();
		suppliersOrganizationDetails(supplier_organization_id);
	});
	
	$('#formGas').submit(function() {
	
		var supplier_organization_id = $('#supplier_organization_id').val();
		if(supplier_organization_id=='' || supplier_organization_id==undefined) {
			alert("<?php echo __('jsAlertSupplierRequired');?>");
			$('.tabs').tabs('option', 'active',0);
			$('#supplier_organization_id').focus();
			return false;
		}

		var typeDelivery = $("input[name='typeDelivery']:checked").val();
		if(typeDelivery==undefined || typeDelivery!='to_defined') {
			var delivery_id = $('#delivery_id').val();
			if(delivery_id=='' || delivery_id==undefined) {
				alert("<?php echo __('jsAlertDeliveryRequired');?>");
				$('.tabs').tabs('option', 'active',0);
				$('#delivery_id').focus();
				return false;
			}	    
		}
		
		var orderDataInizioDb = $('#OrderDataInizioDb').val();
		if(orderDataInizioDb=='' || orderDataInizioDb==undefined) {
			$('.tabs').tabs('option', 'active',0);
			alert("Devi indicare la data di apertura dell'ordine");
			return false;
		}	
		
		var OrderDataFineDb = $('#OrderDataFineDb').val();
		if(OrderDataFineDb=='' || OrderDataFineDb==undefined) {
			$('.tabs').tabs('option', 'active',0);
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
		
		$("#dialog-send_mail").modal();

		return false;	
	});
	
	<?php
	if(!empty($des_order_id)) 
		echo 'suppliersOrganizationDetails('.$supplier_organization_id.');';
	?>
});
</script>