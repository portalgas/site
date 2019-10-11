<?php 
$msg = "Scegli quali articoli desideri che vengano aggiunti in automatico da PortAlGas all'apertura dell'ordine del produttore";
echo $this->element('boxMsgFrontEnd',array('class_msg' => 'notice', 'msg' => $msg));
?>

<div id="tabsDelivery">
	<?php echo $this->Form->input('supplier_organization_id',array('label' => false,'options' => $suppliersOrganizations, 'empty' => __('FilterToSuppliers'), 'escape' => false)); ?>

	<div id="index_articles" style="min-height: 50px;">
	</div>
</div>

<script type="text/javascript">
$(document).ready(function() {

	$('#supplier_organization_id').change(function() {
		var supplier_organization_id = $("#supplier_organization_id").val();

		$('#index_articles').html('');
		$('#index_articles').css('min-height', '50px');
		$('#index_articles').css('background', 'url("'+app_img+'/ajax-loader.gif") no-repeat scroll center 0 transparent');
		
		 url = "/?option=com_cake&controller=BookmarksArticles&action=index_articles&supplier_organization_id="+supplier_organization_id+"&format=notmpl";
		 
		 $.ajax({
			type: "GET",
			url: url,
			data: "",
			success: function(response){
				$('#index_articles').css('background', 'none repeat scroll 0 0 transparent');
				$('#index_articles').html(response);
			},
			error:function (XMLHttpRequest, textStatus, errorThrown) {
				$('#index_articles').css('background', 'none repeat scroll 0 0 transparent');
				$('#index_articles').html(textStatus);
			}
	 	 });
	});  
});
</script>
