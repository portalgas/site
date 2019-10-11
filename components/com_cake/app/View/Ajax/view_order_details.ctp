<?php
/* 
 * come Page::home
 */

echo '<ul class="nav nav-tabs">'; // nav-tabs nav-pills
if($results['Order']['toValidate'] || $results['Order']['toQtaMassima'] || $results['Order']['toQtaMinimaOrder'])
	echo '<li class="active"><a href="#tabs-0-'.$results['Order']['id'].'" data-toggle="tab" onClick="javascript:AjaxCallToDocPreview'.$results['Order']['id'].'(\'to-articles-monitoring\', \'tabs-0-'.$results['Order']['id'].'\')">'.__('to_articles_short').'</a></li>';
else
	echo '<li class="active"><a href="#tabs-0-'.$results['Order']['id'].'" data-toggle="tab" onClick="javascript:AjaxCallToDocPreview'.$results['Order']['id'].'(\'to-articles\', \'tabs-0-'.$results['Order']['id'].'\')">'.__('to_articles_short').'</a></li>';
echo '<li><a href="#tabs-1-'.$results['Order']['id'].'" data-toggle="tab" onClick="javascript:AjaxCallToDocPreview'.$results['Order']['id'].'(\'to-articles-details\', \'tabs-1-'.$results['Order']['id'].'\')">'.__('to_articles_details_short').'</a></li>';
echo '<li><a href="#tabs-2-'.$results['Order']['id'].'" data-toggle="tab" onClick="javascript:AjaxCallToDocPreview'.$results['Order']['id'].'(\'to-users\', \'tabs-2-'.$results['Order']['id'].'\')">'.__('to_users_short').'</a></li>';
echo '<li><a href="#tabs-3-'.$results['Order']['id'].'" data-toggle="tab" onClick="javascript:AjaxCallToDocPreview'.$results['Order']['id'].'(\'to-articles-weight\', \'tabs-3-'.$results['Order']['id'].'\')">'.__('to_articles_weight_short').'</a></li>';
echo '<li><a href="#tabs-4-'.$results['Order']['id'].'" data-toggle="tab" onClick="javascript:AjaxCallToArticlesOrders'.$results['Order']['id'].'(\'Related Articles\', \'tabs-4-'.$results['Order']['id'].'\')">'.__('Related Articles').'</a></li>';
echo '</ul>';

echo '<div class="tab-content">';
echo '<div class="tab-pane fade active in" id="tabs-0-'.$results['Order']['id'].'">';
echo '</div>';
echo '<div class="tab-pane fade" id="tabs-1-'.$results['Order']['id'].'">';
echo '</div>';
echo '<div class="tab-pane fade" id="tabs-2-'.$results['Order']['id'].'">';
echo '</div>';
echo '<div class="tab-pane fade" id="tabs-3-'.$results['Order']['id'].'">';
echo '</div>';
echo '<div class="tab-pane fade" id="tabs-4-'.$results['Order']['id'].'">';
echo '</div>';
echo '</div>'; // end class tab-content
echo '</div>';
?>
<script type="text/javascript">
function AjaxCallToArticlesOrders<?php echo $results['Order']['id'];?> (doc_options, idDivTarget) {
	var url = '/administrator/index.php?option=com_cake&controller=Ajax&action=view_orders&order_id=<?php echo $results['Order']['id'];?>&format=notmpl';
	ajaxCallBox(url, idDivTarget);	
}
function AjaxCallToDocPreview<?php echo $results['Order']['id'];?> (doc_options, idDivTarget) {

	var a = '';
	var b = '';
	var c = '';
	var d = '';
	var e = '';
	var f = '';
	var g = '';
	var h = '';
	if(doc_options=='to-users-all-modify') {
		a = 'N';  /* $("input[name='trasportAndCost1']:checked").val(); */
	}
	else	
	if(doc_options=='to-users') {
		a = 'Y';  /* $("input[name='user_phone1']:checked").val(); */
		b = 'Y';  /* $("input[name='user_email1']:checked").val(); */
		c = 'N';  /* $("input[name='user_address1']:checked").val(); */
		d = 'Y';  /*  $("input[name='totale_per_utente']:checked").val(); */
		e = 'N';  /* $("input[name='trasportAndCost2']:checked").val(); */
		f = 'N';  /* $("input[name='user_avatar1']:checked").val(); */
		g = 'Y';  /*  $("input[name='dettaglio_per_utente']:checked").val(); */
		h = 'N';  /* $("input[name='note1']:checked").val(); */
	}
	else
	if(doc_options=='to-users-label') {
		a = 'Y';  /* $("input[name='user_phone']:checked").val(); */
		b = 'Y';  /* $("input[name='user_email']:checked").val(); */
		c = 'N';  /* $("input[name='user_address']:checked").val(); */
		d = 'N';  /* $("input[name='trasportAndCost3']:checked").val(); */
		e = 'N';  /* $("input[name='user_avatar2']:checked").val(); */
	}
	else
	if(doc_options=='to-articles') {
		a = 'N';  /* $("input[name='trasportAndCost4']:checked").val(); */
		b = 'Y';  /* $("input[name='codice2']:checked").val(); */ 
	}
	else
	if(doc_options=='to-articles-details') {
		a = 'Y';  /* $("input[name='acquistato_il']:checked").val(); */
		b = 'N';  /* $("input[name='article_img']:checked").val(); */
		c = 'N';  /* $("input[name='trasportAndCost5']:checked").val(); */
		d = 'Y';  /* $("input[name='totale_per_articolo']:checked").val();	*/
		e = 'Y';  /* $("input[name='codice1']:checked").val(); */	
	}
	
	if(doc_options=='to-articles-weight') 
		var url = '/administrator/index.php?option=com_cake&controller=ExportDocs&action=exportToArticlesWeight&delivery_id=<?php echo $results['Order']['delivery_id'];?>&order_id=<?php echo $results['Order']['id'];?>&doc_options='+doc_options+'&doc_formato=PREVIEW&format=notmpl';
	else
		var url = '/administrator/index.php?option=com_cake&controller=ExportDocs&action=exportToReferent&delivery_id=<?php echo $results['Order']['delivery_id'];?>&order_id=<?php echo $results['Order']['id'];?>&doc_options='+doc_options+'&doc_formato=PREVIEW&a='+a+'&b='+b+'&c='+c+'&d='+d+'&e='+e+'&f='+f+'&g='+g+'&h='+h+'&format=notmpl';
	
	ajaxCallBox(url, idDivTarget);	
}

$(document).ready(function() {
	<?php
	if($results['Order']['toValidate'] || $results['Order']['toQtaMassima'] || $results['Order']['toQtaMinimaOrder'])
		echo 'AjaxCallToDocPreview'.$results['Order']['id'].' (\'to-articles-monitoring\', \'tabs-0-'.$results['Order']['id'].'\');';
	else
		echo 'AjaxCallToDocPreview'.$results['Order']['id'].' (\'to-articles\', \'tabs-0-'.$results['Order']['id'].'\');';
	?>				
});
</script>