<div class="legenda legenda-ico-alert" style="float:none;overflow: auto;">
Alcuni dati del produttore sono cambiati?

<div style="float:right;" class="actions-img">
<?php 
echo $this->Html->link(__('Send mail to manager suppliers to change'), array(), array( 'class' => 'action actionEdit sendMail','title' => __('Send mail to manager suppliers to change'),
		'pass_org_id' => $organization_id,
		'pass_id' => $supplier_id,
		'pass_entity' => 'SupplierChange',
		'id' => 'content_link'));
?>
</div>
</div>