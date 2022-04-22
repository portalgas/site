<?php 
echo '<div class="organizations form">';

echo $this->Form->create('OrganizationsPay', ['id' => 'formGas', 
											   'target' => '_blank',
											   'url' => ['controller' => 'OrganizationsPays', 
											   			 'action' => 'admin_invoice_create_pdf',
														 'doc_formato'=>'PDF',
														 'format'=>'notmpl'
											]]);

echo '<fieldset>';
echo '<legend>'.__('OrganizationsPay').'</legend>';

$options =  ['id' => 'organization_id',
				  'empty' => Configure::read('option.empty'),
				  'class'=> 'selectpicker', 'data-live-search' => true];

echo '<div class="row">';
echo '<div class="col-md-8">';
echo $this->Form->input('organization_id', $options);
echo '</div>';
echo '<div class="col-md-4" id="organization_details">';
echo '</div>';
echo '</div>';


$options = array('options' => $type_pay, 'value' => 'RICEVUTA', 'label'=>__('Stato'), 'required'=>'true');
echo $this->App->drawFormRadio('OrganizationsPay','type_pay', $options);

echo $this->Form->input('title',array('label' => __('Title'), 'required'=>'true', 'id' => 'title'));
echo $this->Form->input('intro',array('label' => __('Intro'), 'type' => 'textarea', 'required'=>'true'));
echo $this->Form->input('text',array('label' => __('Text'), 'type' => 'textarea', 'required'=>'false', 'id' => 'text'));

echo '<div id="importo-ritenuta">';
$msg = 'Se <b>importo 100</b>: Compenso lordo 100&nbsp;&nbsp;-&nbsp;&nbsp;Ritenuta d’acconto 20&nbsp;&nbsp;-&nbsp;&nbsp;Netto da pagare 80<br />';
$msg .= 'Se <b>importo 75</b>: Compenso lordo 75&nbsp;&nbsp;-&nbsp;&nbsp;Ritenuta d’acconto 15&nbsp;&nbsp;-&nbsp;&nbsp;Netto da pagare 60<br />';
$msg .= 'Se <b>importo 50</b>: Compenso lordo 50&nbsp;&nbsp;-&nbsp;&nbsp;Ritenuta d’acconto 10&nbsp;&nbsp;-&nbsp;&nbsp;Netto da pagare 40';
echo $this->element('boxMsg', ['msg' => $msg, 'class_msg' => 'info']);
echo '</div>';

echo $this->Form->input('nota',array('label' => __('Nota'), 'type' => 'textarea', 'required'=>'required'));

echo $this->Form->input('nota2',array('label' => __('Nota aggiuntiva'), 'type' => 'textarea'));

echo '</fieldset>';
	
echo $this->Form->end(__('Submit'));
?>	
</div>

<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('Prospetto pagamenti'), array('action' => 'index'),array('class'=>'action actionReload'));?></li>
	</ul>
</div>

<script type="text/javascript">
function settingTypePay() {
	var type_pay = $('input[name="data[OrganizationsPay][type_pay]"]:checked').val();
	console.log("type_pay "+type_pay);
	if(type_pay!='') {
		
		if(type_pay=='RICEVUTA') {
			$('#title').val("<?php echo $title_RICEVUTA;?>");
			$('#text').val("<?php echo $text_RICEVUTA;?>");
			$('#importo-ritenuta').hide();
		}
		else {
			$('#title').val("<?php echo $title_RITENUTA;?>");
			$('#text').val("<?php echo $text_RITENUTA;?>");
			$('#importo-ritenuta').show();
		}
	}
}
function organizationDetails(organization_id) {
	if(organization_id!=undefined && organization_id!=0 && organization_id!='') {
		var url = "/administrator/index.php?option=com_cake&controller=OrganizationsPays&action=organizationDetails&organization_id="+organization_id+"&format=notmpl";
		var idDivTarget = 'organization_details';
		ajaxCallBox(url, idDivTarget);		
	}
}	

$(document).ready(function() {
	settingTypePay();
	
	$("input[name='data[OrganizationsPay][type_pay]']").change(function() {	
		settingTypePay();
	});	

	$('#organization_id').change(function() {
		var organization_id = $(this).val();
		organizationDetails(organization_id);
	});
});
</script>