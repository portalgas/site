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
$(document).ready(function() {

	$(".rowEcomm").each(function () {
		activeEcommRows(this);    /* active + / - , mouseenter mouseleave */
		activeSubmitEcommBookMmarks(this);
	});	
	
	$('.actionTrView').css('display','inline-block');  /* rendo visibile il tasto espandi per i dettagli ajax */
	
	$('.actionTrView').each(function () {
		actionTrView(this);
	});
	
	$('.actionNotaView').css('display','inline-block');  /* rendo visibile il tasto espandi per i dettagli ajax */
	
	$('.actionNotaView').each(function () {
		actionNotaView(this); 
	});
});	

function activeSubmitEcommBookMmarks(obj) { 

		$(obj).find('.submitEcomm').click(function() {
			/* get id da id="xxx-1"  */
			var idRow = $(this).attr('id');
			var numRow = idRow.substring(idRow.indexOf('-')+1,idRow.lenght);

			/* chiudo eventuali dettagli */
			$('.trView').hide(); 
			$('.actionTrView').removeClass('closeTrView'); 
			$('.actionTrView').addClass('openTrView'); 
			
			$('#submitEcomm-'+numRow).attr('src',app_img+'/ajax-loader.gif');
			
			var supplier_organization_id = $('#supplier_organization_id').val();
			var article_organization_id = $('#article_organization_id-'+numRow).val();
			var article_id = $('#article_id-'+numRow).val();
			var qta = $('#qta-'+numRow).html();
			
			if(!ecommRowsValidation(numRow, backOffice=false)) return false;
			
			var url = '';
			url = '/?option=com_cake&controller=BookmarksArticles&action=managementCartSimple&rowId='+numRow+'&supplier_organization_id='+supplier_organization_id+'&article_organization_id='+article_organization_id+'&article_id='+article_id+'&qta='+qta+'&format=notmpl';

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

function managementCartBookmarks(c, b, a) {
    $("#submitEcomm-" + c).attr("style", "opacity:1");
    $("#msgEcomm-" + c).attr("style", "opacity:1");
    $("#msgEcomm-" + c).html("");
    if (b == "OK") {
        $("#submitEcomm-" + c).attr("src", app_img + "/actions/32x32/bookmark.png");
        $("#submitEcomm-" + c).css("cursor", "default");
        $("#msgEcomm-" + c).html("Salvato!");
        $("#submitEcomm-" + c).delay(1000).animate({
            opacity: 0
        }, 1500);
        $("#msgEcomm-" + c).delay(1000).animate({
            opacity: 0
        }, 1500);
    } 
    else 
    if (b == "DELETE") {
            $("#submitEcomm-" + c).attr("src", app_img + "/actions/32x32/blue_basket.png");
            $("#submitEcomm-" + c).css("cursor", "default");
            $("#msgEcomm-" + c).html("Cancellato!");
            $("#submitEcomm-" + c).delay(1000).animate({
                opacity: 0
            }, 1500);
            $("#msgEcomm-" + c).delay(1000).animate({
                opacity: 0
            }, 1500)
    }
    else
    if (b == "NO") {
        $("#submitEcomm-" + c).attr("src", app_img + "/apps/32x32/error.png");
           $("#submitEcomm-" + c).css("cursor", "default");
           $("#msgEcomm-" + c).html("Errore!")
    }
}
</script>