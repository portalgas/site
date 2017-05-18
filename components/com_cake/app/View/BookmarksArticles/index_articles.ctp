<?php 
echo '<table cellspacing="0" cellpadding="0">';
echo '<tr>';
echo '<th></th>';
echo '<th>'.__('N').'</th>';
echo '<th>'.__('Bio').'</th>';
echo '<th>'.__('Name').'</th>';
echo '<th>'.__('Conf').'</th>';
echo '<th>'.__('PrezzoUnita').'</th>';
echo '<th>'.__('Prezzo/UM').'</th>';
echo '<th>'.__('qta_minima').'</th>';
echo '<th>'.__('qta').'</th>';
echo '<th>'.__('Importo').'</th>';
echo '</tr>';

 		 	
foreach ($results as $numResult => $result) 
	echo $this->RowBookmarks->drawFrontEndSimple($numResult, $result, $options);
	
echo '</table>';
?>

<script type="text/javascript">
jQuery(document).ready(function() {

	jQuery(".rowEcomm").each(function () {
		activeEcommRows(this);    /* active + / - , mouseenter mouseleave */
		activeSubmitEcommBookMmarks(this);
	});	
	
	jQuery('.actionTrView').css('display','inline-block');  /* rendo visibile il tasto espandi per i dettagli ajax */
	
	jQuery('.actionTrView').each(function () {
		actionTrView(this);
	});
	
	jQuery('.actionNotaView').css('display','inline-block');  /* rendo visibile il tasto espandi per i dettagli ajax */
	
	jQuery('.actionNotaView').each(function () {
		actionNotaView(this); 
	});
});	

function activeSubmitEcommBookMmarks(obj) { 

		jQuery(obj).find('.submitEcomm').click(function() {
			/* get id da id="xxx-1"  */
			var idRow = jQuery(this).attr('id');
			var numRow = idRow.substring(idRow.indexOf('-')+1,idRow.lenght);

			/* chiudo eventuali dettagli */
			jQuery('.trView').hide(); 
			jQuery('.actionTrView').removeClass('closeTrView'); 
			jQuery('.actionTrView').addClass('openTrView'); 
			
			jQuery('#submitEcomm-'+numRow).attr('src',app_img+'/ajax-loader.gif');
			
			var supplier_organization_id = jQuery('#supplier_organization_id').val();
			var article_organization_id = jQuery('#article_organization_id-'+numRow).val();
			var article_id = jQuery('#article_id-'+numRow).val();
			var qta = jQuery('#qta-'+numRow).html();
			
			if(!ecommRowsValidation(numRow, backOffice=false)) return false;
			
			var url = '';
			url = '/?option=com_cake&controller=BookmarksArticles&action=managementCartSimple&rowId='+numRow+'&supplier_organization_id='+supplier_organization_id+'&article_organization_id='+article_organization_id+'&article_id='+article_id+'&qta='+qta+'&format=notmpl';

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

function managementCartBookmarks(c, b, a) {
    jQuery("#submitEcomm-" + c).attr("style", "opacity:1");
    jQuery("#msgEcomm-" + c).attr("style", "opacity:1");
    jQuery("#msgEcomm-" + c).html("");
    if (b == "OK") {
        jQuery("#submitEcomm-" + c).attr("src", app_img + "/actions/32x32/bookmark.png");
        jQuery("#submitEcomm-" + c).css("cursor", "default");
        jQuery("#msgEcomm-" + c).html("Salvato!");
        jQuery("#submitEcomm-" + c).delay(1000).animate({
            opacity: 0
        }, 1500);
        jQuery("#msgEcomm-" + c).delay(1000).animate({
            opacity: 0
        }, 1500);
    } 
    else 
    if (b == "DELETE") {
            jQuery("#submitEcomm-" + c).attr("src", app_img + "/actions/32x32/blue_basket.png");
            jQuery("#submitEcomm-" + c).css("cursor", "default");
            jQuery("#msgEcomm-" + c).html("Cancellato!");
            jQuery("#submitEcomm-" + c).delay(1000).animate({
                opacity: 0
            }, 1500);
            jQuery("#msgEcomm-" + c).delay(1000).animate({
                opacity: 0
            }, 1500)
    }
    else
    if (b == "NO") {
        jQuery("#submitEcomm-" + c).attr("src", app_img + "/apps/32x32/error.png");
           jQuery("#submitEcomm-" + c).css("cursor", "default");
           jQuery("#msgEcomm-" + c).html("Errore!")
    }
}
</script>