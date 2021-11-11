jQuery(document).ready(function() {
	jQuery('#cart-short').hover(function() {
		jQuery('#cart-short').stop(true).fadeTo(200, 1);
	}, function() {
		jQuery('#cart-short').animate({opacity:0,left: '-275px'}, 1500);
	});
});

function viewCartShort() {	
	var url = "/?option=com_cake&controller=Carts&action=cart_to_user_preview&format=notmpl";
	jQuery('#cart-short').load(url).animate({opacity:1,left: '0px'}, 750);
	jQuery('.lastCart').animate({backgroundColor:'#fff'},350);
	jQuery('#cart-short').delay(2000).animate({opacity:0, left: '-275px'}, 1500);
}

function activeSubmitEcomm(obj) { 
	   /* 
		* submit
		* 	da Delivery/admin_tabecomm.ctp user_id da sessione 
	    */
		jQuery(obj).find('.submitEcomm').click(function() {
			/* get id da id="xxx-1"  */
			var idRow = jQuery(this).attr('id');
			var numRow = idRow.substring(idRow.indexOf('-')+1,idRow.lenght);

			/* chiudo eventuali dettagli */
			jQuery('.trView').hide(); 
			jQuery('.actionTrView').removeClass('closeTrView'); 
			jQuery('.actionTrView').addClass('openTrView'); 
			
			jQuery('#submitEcomm-'+numRow).attr('src',app_img+'/ajax-loader.gif');
			
			var order_id = jQuery('#order_id-'+numRow).val();
			var article_organization_id = jQuery('#article_organization_id-'+numRow).val();
			var article_id = jQuery('#article_id-'+numRow).val();
			var qta = jQuery('#qta-'+numRow).html();
			
			if(!ecommRowsValidation(numRow, backOffice=false)) return false;

			if(jQuery("#order_type_draw_"+order_id).val()=='COMPLETE')  
				action = 'managementCartComplete';
			else
			if(jQuery("#order_type_draw_"+order_id).val()=='VALIDATION_SIMPLE')  
				action = 'managementCartValidationSimple';
			else
				action = 'managementCartSimple';
			
			var url = '';
			url = '/?option=com_cake&controller=AjaxGasCarts&action='+action+'&row_id='+numRow+'&order_id='+order_id+'&article_organization_id='+article_organization_id+'&article_id='+article_id+'&qta='+qta+'&format=notmpl';

			jQuery.ajax({
				type: "GET",
				url: url,
				data: "",
				success: function(response){
					jQuery('#row-'+numRow).html(response);
					/* jQuery('#msgEcomm-'+numRow).html(response); */
				},
				error:function (XMLHttpRequest, textStatus, errorThrown) {
					 jQuery('#msgEcomm-'+numRow).html(textStatus);
					 jQuery('#submitEcomm-'+numRow).attr('src',app_img+'/blank32x32.png');
				}
			});
			return false;
	});
}