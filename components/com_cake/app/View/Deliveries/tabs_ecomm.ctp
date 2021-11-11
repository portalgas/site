<?php
echo '<div id="tabsDelivery">';
echo '<h2>'.__('FEShop').'</h2>';

echo '<div class="panel with-nav-tabs panel-default">';
echo '   <div class="panel-heading" style="padding-bottom:0px;background-color: #d9edf7;border-bottom: medium none;">';
echo '		<ul class="nav nav-tabs" role="tablist">';
echo '		  <li role="presentation" class="active"><a data-toggle="tab" href="#tabType0content" onClick="javascript:drawTab(0)">'.__('OrdersToDelivery').'</a></li>';
echo '		  <li role="presentation"><a data-toggle="tab" href="#tabType1content" onClick="javascript:drawTab(1)">'.__('OrdersToSupplier').'</a></li>';
if(!empty($prodGasPromotionsOrganizationsResults))
	echo '<li role="presentation"><a target="_blank" class="btn-externel btn btn-orange" href="'.$url_prd_gas_promotion.'">'.__('ProdGasPromotionNames').'</a></li>';
// echo '		  <li role="presentation"><a href="/?option=com_cake&controller=Deliveries&action=tabsUserCart">Carrello</a></li>';
echo '		</ul>';	
echo '	</div>';
echo '	<div class="panel-body">';
echo '		<div class="tab-content">';
echo '			<div id="tabType0content" class="tab-pane fade active in"></div>'; 
echo '			<div id="tabType1content" class="tab-pane fade "></div>'; 
echo '			<div id="tabType2content" class="tab-pane fade "></div>'; 
echo '		</div>';
echo '	</div>';
echo '</div>';
?>

<script type="text/javascript">
function drawTab(numTabs) {
	
	if(numTabs==0) {
		$('#tabType1content').html('');
		url = "/?option=com_cake&controller=Deliveries&action=tabsEcommTabOrdersDelivery&format=notmpl";
	}
	else
	if(numTabs==1) {
		$('#tabType0content').html('');
		url = "/?option=com_cake&controller=Deliveries&action=tabsEcommTabAllOrders&format=notmpl";		
	}
	else
	if(numTabs==2) {
		$('#tabType0content').html('');
		url = "/?option=com_cake&controller=Deliveries&action=tabsEcommTabProdGasPromotions&format=notmpl";		
	}
	else
		return;
			
	$('#tabs-'+numTabs).addClass('active');
	$('#tabType'+numTabs+'content').css('min-height','100');
	$('#tabType'+numTabs+'content').css('background', 'url("/images/cake/ajax-loader.gif") no-repeat scroll center 0 transparent');
	
	$.ajax({
			type: "GET",
			url: url, 
			data: "",
			success: function(response){
				$('#tabType'+numTabs+'content').css('background', 'none repeat scroll 0 0 transparent');
				$('#tabType'+numTabs+'content').html(response);
			},
			error:function (XMLHttpRequest, textStatus, errorThrown) {
				$('#tabType'+numTabs+'content').css('background', 'none repeat scroll 0 0 transparent');
				$('#tabType'+numTabs+'content').html(textStatus);
			}
	});
}

$(document).ready(function() {
	drawTab(0);
});
</script>
</div>