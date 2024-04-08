<?php 
echo '<div class="organizations form">';

foreach($organizationPayResults as $organizationPayResult) {

    debug($organizationPayResult['Organization']['name'].' '.$organizationPayResult['Organization']['id']);

    $url = '/administrator/index.php?option=com_cake&controller=OrganizationsPays&action=ajax_invoice_create_pdf&format=notmpl';
    debug("chiamo l'url POST ".$url);
?>
    <script type="text/javascript">
        let url_<?php echo $organizationPayResult['Organization']['id'];?> = '/administrator/index.php?option=com_cake&controller=OrganizationsPays&action=ajax_invoice_create_pdf&format=notmpl';
        let datas_<?php echo $organizationPayResult['Organization']['id'];?> = {'id': <?php echo $organizationPayResult['Organization']['id'];?>}
        console.log(datas_<?php echo $organizationPayResult['Organization']['id'];?>);

        $.ajax({
            type: "POST",
            url: url_<?php echo $organizationPayResult['Organization']['id'];?>,
            data: datas_<?php echo $organizationPayResult['Organization']['id'];?>,
            success: function(response){
                console.log(response);
            },
            error:function (XMLHttpRequest, textStatus, errorThrown) {
                console.error(response);
            }
        });
    </script>
<?php
}
echo '</div>';
?>

<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('Prospetto pagamenti'), array('action' => 'index'),array('class'=>'action actionReload'));?></li>
	</ul>
</div>