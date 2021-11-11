/*$(document).ready(function() {
	$('#cart-short').hover(function() {
		$('#cart-short').stop(true).fadeTo(200, 1);
	}, function() {
		$('#cart-short').animate({opacity:0,left: '-275px'}, 1500);
	});
});
*/
function viewCartShort() {	
	return;
	var url = "/?option=com_cake&controller=Carts&action=cart_to_user_preview&format=notmpl";
	$('#cart-short').load(url).animate({opacity:1,left: '0px'}, 750);
	$('.lastCart').animate({backgroundColor:'#fff'},350);
	$('#cart-short').delay(2000).animate({opacity:0, left: '-275px'}, 1500);
}

function activeSubmitEcomm(obj) { 
	   /* 
		* submit
		* 	da Delivery/admin_tabecomm.ctp user_id da sessione 
	    */
		$(obj).find('.submitEcomm').click(function() {
			/* get id da id="xxx-1"  */
			var idRow = $(this).attr('id');
			var numRow = idRow.substring(idRow.indexOf('-')+1,idRow.lenght);

			/* chiudo eventuali dettagli */
			$('.trView').hide(); 
			$('.actionTrView').removeClass('closeTrView'); 
			$('.actionTrView').addClass('openTrView'); 
			
			$('#submitEcomm-'+numRow).attr('src',app_img+'/ajax-loader.gif');
			
			var order_id = $('#order_id-'+numRow).val();
			var article_organization_id = $('#article_organization_id-'+numRow).val();
			var article_id = $('#article_id-'+numRow).val();
			var qta = $('#qta-'+numRow).html();
			
			if(!ecommRowsValidation(numRow, backOffice=false)) return false;

			if($("#order_type_draw_"+order_id).val()=='COMPLETE')  
				action = 'managementCartComplete';
			else
			if($("#order_type_draw_"+order_id).val()=='VALIDATION_SIMPLE')  
				action = 'managementCartValidationSimple';
			else
			if($("#order_type_draw_"+order_id).val()=='PROMOTION')  
				action = 'managementCartPromotion';
			else
				action = 'managementCartSimple';
			
			var url = '';
			url = '/?option=com_cake&controller=AjaxGasCarts&action='+action+'&row_id='+numRow+'&order_id='+order_id+'&article_organization_id='+article_organization_id+'&article_id='+article_id+'&qta='+qta+'&format=notmpl';

			$.ajax({
				type: "GET",
				url: url,
				data: "",
				success: function(response){
					$('#row-'+numRow).html(response);
					/* $('#msgEcomm-'+numRow).html(response); */
				},
				error:function (XMLHttpRequest, textStatus, errorThrown) {
					 $('#msgEcomm-'+numRow).html(textStatus);
					 $('#submitEcomm-'+numRow).attr('src',app_img+'/blank32x32.png');
				}
			});
			return false;
	});
}