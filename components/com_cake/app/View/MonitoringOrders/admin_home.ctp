<?php
$this->Html->addCrumb(__('Home'),array('controller' => 'Pages', 'action' => 'home'));
$this->Html->addCrumb(__('Gest MonitoringSuppliersOrganizations'), array('controller' => 'MonitoringSuppliersOrganizations', 'action' => 'home'));
$this->Html->addCrumb(__('Gest MonitoringOrders'));
echo $this->Html->getCrumbList(array('class'=>'crumbs'));

echo '<h2 class="ico-monitoring-orders">';
echo __('Monitoring Orders');
echo '<div class="actions-img">';			
echo '	<ul>';
echo '		<li>'.$this->Html->link(__('Gest MonitoringOrders'), array('action' => 'index'),array('class' => 'action actionAdd','title' => __('Gest MonitoringOrders'))).'</li>';
echo '	</ul>';
echo '</div>';
echo '</h2>';

/*
 *  elenco ordini
 */
if(!empty($results)) {
	
	echo '<p style="clear: both;">';
	
	foreach ($results as $result) {

		$id = $result['Order']['delivery_id'].'_'.$result['Order']['id'];
		
		echo '<div id="tabs-'.$id.'" style="margin-top:5px;">';
		
		echo '<table cellpadding = "0" cellspacing = "0">';
		echo '<tr style="border-radius:5px;">';

		echo '	<th width="20%">'.$this->App->drawOrdersStateDiv($result).'&nbsp;'.__($result['Order']['state_code'].'-label').'</th>';
				
		echo '	<th width="35%">'.__('Delivery').': <span style="font-weight:normal;">';
		if($result['Delivery']['sys']=='N')
			echo $result['Delivery']['luogoData'];
		else 
			echo $result['Delivery']['luogo'];
		echo '</span></th>';
		echo '	<th width="35%">'.__('SuppliersOrganization').': <span style="font-weight:normal;">'.$result['SuppliersOrganization']['name'].'</span></th>';

		echo '	<th width="10%">';
		echo 	$this->Html->link(null, array('controller' => 'Orders', 'action' => 'home', null, 'order_id='.$result['Order']['id']), array('class' => 'action actionWorkflow','title' => __('Order home')));

		echo '	<a id="actionMenu-'.$result['Order']['id'].'" class="action actionMenu" title="'.__('Expand menu').'"></a>';
		echo '	<div class="menuDetails" id="menuDetails-'.$result['Order']['id'].'" style="display:none;">';
		echo '		<a class="menuDetailsClose" id="menuDetailsClose-'.$result['Order']['id'].'"></a>';
		echo '		<div id="order-sotto-menu-'.$result['Order']['id'].'"></div>';
		echo '	</div>';		
		echo '</th>';
		
		echo '</tr>';
		echo '</table>';
		
		echo ' <ul>';
		if($result['Order']['toValidate'] || $result['Order']['toQtaMassima'] || $result['Order']['toQtaMinimaOrder'])
			echo ' <li><a href="#tabs-0-'.$id.'" class="tabsDelivery" onClick="javascript:AjaxCallToDocPreview'.$id.'(\'to-articles-monitoring\', \'tabs-0-'.$id.'\')">'.__('to_articles_short').'</a></li>';
		else
			echo ' <li><a href="#tabs-0-'.$id.'" class="tabsDelivery" onClick="javascript:AjaxCallToDocPreview'.$id.'(\'to-articles\', \'tabs-0-'.$id.'\')">'.__('to_articles_short').'</a></li>';
		echo ' <li><a href="#tabs-1-'.$id.'" class="tabsDelivery" onClick="javascript:AjaxCallToDocPreview'.$id.'(\'to-articles-details\', \'tabs-1-'.$id.'\')">'.__('to_articles_details_short').'</a></li>';
		echo ' <li><a href="#tabs-2-'.$id.'" class="tabsDelivery" onClick="javascript:AjaxCallToDocPreview'.$id.'(\'to-users\', \'tabs-2-'.$id.'\')">'.__('to_users_short').'</a></li>';
		echo ' <li><a href="#tabs-3-'.$id.'" class="tabsDelivery" onClick="javascript:AjaxCallToDocPreview'.$id.'(\'to-articles-weight\', \'tabs-3-'.$id.'\')">'.__('to_articles_weight_short').'</a></li>';
		
		echo '</ul>';
		echo '<div style="min-height:50px;" id="tabs-0-'.$id.'"></div>';
		echo '<div style="min-height:50px;" id="tabs-1-'.$id.'"></div>';
		echo '<div style="min-height:50px;" id="tabs-2-'.$id.'"></div>';
		echo '<div style="min-height:50px;" id="tabs-3-'.$id.'"></div>';
		
		echo $this->element('boxOrderLimit', array('orderResult' => $result));
				
		echo '</div>';
		?>
			<script type="text/javascript">
			function AjaxCallToDocPreview<?php echo $id;?> (doc_options, idDivTarget) {
			
				/*
				 * setting, uguale a 
				 *              Ajax::admin_view_tesoriere_export_docs.ctp 	 
				 *				AjaxGasCode::admin_box_doc_print_referente.ctp, 
				 *				Doc::admin_referent_docs_export.ctp, 
				 *				Doc::admin_cassiere_docs_export.ctp, 
				 *              Pages:admin_home.ctp
				 */
				var a = '';
				var b = '';
				var c = '';
				var d = '';
				var e = '';
				var f = '';
				var g = '';
				var h = '';
				if(doc_options=='to-users-all-modify') {
					a = 'N';  /* jQuery("input[name='trasport1']:checked").val(); */
				}
				else	
				if(doc_options=='to-users') {
					a = 'Y';  /* jQuery("input[name='user_phone1']:checked").val(); */
					b = 'Y';  /* jQuery("input[name='user_email1']:checked").val(); */
					c = 'N';  /* jQuery("input[name='user_address1']:checked").val(); */
					d = 'Y';  /*  jQuery("input[name='totale_per_utente']:checked").val(); */
					e = 'N';  /* jQuery("input[name='trasport2']:checked").val(); */
					f = 'N';  /* jQuery("input[name='user_avatar1']:checked").val(); */
					g = 'Y';  /*  jQuery("input[name='dettaglio_per_utente']:checked").val(); */
					h = 'N';  /* jQuery("input[name='note1']:checked").val(); */
				}
				else
				if(doc_options=='to-users-label') {
					a = 'Y';  /* jQuery("input[name='user_phone']:checked").val(); */
					b = 'Y';  /* jQuery("input[name='user_email']:checked").val(); */
					c = 'N';  /* jQuery("input[name='user_address']:checked").val(); */
					d = 'N';  /* jQuery("input[name='trasport3']:checked").val(); */
					e = 'N';  /* jQuery("input[name='user_avatar2']:checked").val(); */
				}
				else
				if(doc_options=='to-articles') {
					a = 'N';  /* jQuery("input[name='trasport4']:checked").val(); */
					b = 'Y';  /* jQuery("input[name='codice2']:checked").val(); */ 
				}
				else
				if(doc_options=='to-articles-details') {
					a = 'Y';  /* jQuery("input[name='acquistato_il']:checked").val(); */
					b = 'N';  /* jQuery("input[name='article_img']:checked").val(); */
					c = 'N';  /* jQuery("input[name='trasport5']:checked").val(); */
					d = 'Y';  /* jQuery("input[name='totale_per_articolo']:checked").val();	*/
					e = 'Y';  /* jQuery("input[name='codice1']:checked").val(); */	
				}
				
				if(doc_options=='to-articles-weight') 
					var url = '/administrator/index.php?option=com_cake&controller=ExportDocs&action=exportToArticlesWeight&delivery_id=<?php echo $result['Order']['delivery_id'];?>&order_id=<?php echo $result['Order']['id'];?>&doc_options='+doc_options+'&doc_formato=PREVIEW&format=notmpl';
				else			
					var url = '/administrator/index.php?option=com_cake&controller=ExportDocs&action=exportToReferent&delivery_id=<?php echo $result['Order']['delivery_id'];?>&order_id=<?php echo $result['Order']['id'];?>&doc_options='+doc_options+'&doc_formato=PREVIEW&a='+a+'&b='+b+'&c='+c+'&d='+d+'&e='+e+'&f='+f+'&g='+g+'&h='+h+'&format=notmpl';
				ajaxCallBox(url, idDivTarget);	
			}
			jQuery(document).ready(function() {
				<?php
				echo 'jQuery(\'#tabs-'.$id.'\').tabs({event: "click"});';
				
				echo "\r\n";
				
				if($result['Order']['toValidate'] || $result['Order']['toQtaMassima'] || $result['Order']['toQtaMinimaOrder'])
					echo 'AjaxCallToDocPreview'.$id.' (\'to-articles-monitoring\', \'tabs-0-'.$id.'\');';
				else
					echo 'AjaxCallToDocPreview'.$id.' (\'to-articles\', \'tabs-0-'.$id.'\');';
				?>				
			});
			</script>
		<?php
		
	} // end foreach ($results as $i => $result)
	
	echo '</p>';
	
} // end if(!empty($results)) 
else 
	echo $this->element('boxMsg',array('class_msg' => 'message resultsNotFonud', 'msg' => "Non ci sono ancora ordini da monitorare"));

?>


<script type="text/javascript">
jQuery(document).ready(function() {
	jQuery(".actionMenu").each(function () {
		jQuery(this).click(function() {

			jQuery('.menuDetails').css('display','none');
			
			var idRow = jQuery(this).attr('id');
			numRow = idRow.substring(idRow.indexOf('-')+1,idRow.lenght);
			jQuery('#menuDetails-'+numRow).show();

			viewOrderSottoMenu(numRow,"bgLeft");

			var offset = jQuery(this).offset();
			var newTop = (offset.top - 100);
			var newLeft = (offset.left - 350);

			jQuery('#menuDetails-'+numRow).offset({ top: newTop, left: newLeft});			
		});
	});	

	jQuery(".menuDetailsClose").each(function () {
		jQuery(this).click(function() {
			var idRow = jQuery(this).attr('id');
			numRow = idRow.substring(idRow.indexOf('-')+1,idRow.lenght);
			jQuery('#menuDetails-'+numRow).hide('slow');
		});
	});		
});
</script>