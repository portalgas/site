<div class="organizations form">

<?php 
echo $this->Form->create('OrganizationsPay', array('id' => 'formGas', 
												   'target' => '_blank',
												   'url' => array('controller' => 'OrganizationsPays', 'action' => 'admin_invoice_create_pdf',
																	'doc_formato'=>'PDF',
																	'format'=>'notmpl'
																)));

echo '<fieldset>';
echo '<legend>'.__('OrganizationsPay').'</legend>';

$options =  array('id' => 'organization_id',
				  'empty' => Configure::read('option.empty'),
				  'class'=> 'selectpicker', 'data-live-search' => true);
echo $this->Form->input('organization_id',$options);

$options = array('options' => $type_pay, 'value' => 'RICEVUTA', 'label'=>__('Stato'), 'required'=>'true');
echo $this->App->drawFormRadio('OrganizationsPay','type_pay', $options);

echo $this->Form->input('title',array('label' => __('Title'), 'required'=>'true', 'id' => 'title'));
echo $this->Form->input('intro',array('label' => __('Intro'), 'type' => 'textarea', 'required'=>'true'));
echo $this->Form->input('text',array('label' => __('Text'), 'type' => 'textarea', 'required'=>'false', 'id' => 'text'));

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
	var type_pay = jQuery('input[name="data[OrganizationsPay][type_pay]"]:checked').val();
	console.log("type_pay "+type_pay);
	if(type_pay!='') {
		
		if(type_pay=='RICEVUTA') {
			jQuery('#title').val("<?php echo $title_RICEVUTA;?>");
			jQuery('#text').val("<?php echo $text_RICEVUTA;?>");
		}
		else {
			jQuery('#title').val("<?php echo $title_RITENUTA;?>");
			jQuery('#text').val("<?php echo $text_RITENUTA;?>");
		}
	}
}
jQuery(document).ready(function() {
	settingTypePay();
	
	jQuery("input[name='data[OrganizationsPay][type_pay]']").change(function() {	
		settingTypePay();
	});		
});
</script>