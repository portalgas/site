<?php 
$msg = "Scegli quali articoli desideri che vengano aggiunti in automatico da PortAlGas all'apertura dell'ordine del produttore";
echo $this->element('boxMsgFrontEnd',array('class_msg' => 'notice', 'msg' => $msg));
?>

<div id="tabsDelivery">
	<?php echo $this->Form->input('supplier_organization_id',array('label' => false,'options' => $suppliersOrganizations, 'empty' => 'Filtra per produttore', 'escape' => false)); ?>

	<div id="index_articles" style="min-height: 50px;">
	</div>
</div>

<script type="text/javascript">
jQuery(document).ready(function() {

	jQuery('#supplier_organization_id').change(function() {
		var supplier_organization_id = jQuery("#supplier_organization_id").val();

		jQuery('#index_articles').html('');
		jQuery('#index_articles').css('min-height', '50px');
		jQuery('#index_articles').css('background', 'url("'+app_img+'/ajax-loader.gif") no-repeat scroll center 0 transparent');
		
		 url = "/?option=com_cake&controller=BookmarksArticles&action=index_articles&supplier_organization_id="+supplier_organization_id+"&format=notmpl";
		 
		 jQuery.ajax({
			type: "GET",
			url: url,
			data: "",
			success: function(response){
				jQuery('#index_articles').css('background', 'none repeat scroll 0 0 transparent');
				jQuery('#index_articles').html(response);
			},
			error:function (XMLHttpRequest, textStatus, errorThrown) {
				jQuery('#index_articles').css('background', 'none repeat scroll 0 0 transparent');
				jQuery('#index_articles').html(textStatus);
			}
	 	 });
	});  
});
</script>
