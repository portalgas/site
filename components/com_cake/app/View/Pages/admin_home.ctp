<?php
/*
 * promozioni
 */
if(!empty($prodGasPromotionsOrganizationsresults) && ($isManager || $isSuperReferente || $isReferente)) {
	echo $this->element('boxMsgProdGasPromotions');
}
echo '<div class="container-fluid text-center">';
echo '  <div class="row">';

// echo $this->element('carousel');

if($isReferentGeneric) {
?>
    <div class="col-xs-12 col-sm-4 col-md-3">
	    <div class="box-container">
    		<a href="/administrator/index.php?option=com_cake&amp;controller=Orders&amp;action=index">
	    		<div class="box">
    		  		<span class="fa fa-3x fa-shopping-basket"></span>
      				<h4>Ordini</h4>
      				<p>Gestici tutto il ciclo di un ordine</p>
    			</div>	
    		</a>
    	</div>
    </div>
    <div class="col-xs-12 col-sm-4 col-md-3">
	    <div class="box-container">
    		<a href="/administrator/index.php?option=com_cake&controller=Connects&action=index&c_to=admin/articles&a_to=index-quick">
	    		<div class="box">
    		  		<span class="fa fa-3x fa-cubes"></span>
      				<h4>Articoli</h4>
      				<p>Gestisci l'anagrafica degli articoli</p>
    			</div>	
    		</a>
    	</div>
    </div>	
    <div class="col-xs-12 col-sm-4 col-md-3">
	    <div class="box-container">
    		<a href="/administrator/index.php?option=com_cake&amp;controller=SuppliersOrganizations&amp;action=index">
	    		<div class="box">
    		  		<span class="fa fa-3x fa-share-alt"></span>
      				<h4>Produttori</h4>
      				<p>I produttori del proprio G.A.S.</p>
    			</div>	
    		</a>
    	</div>
    </div>
    <div class="col-xs-12 col-sm-4 col-md-3">
	    <div class="box-container">
    		<a href="/administrator/index.php?option=com_cake&amp;controller=Suppliers&amp;action=index_relations">
	    		<div class="box">
    		  		<span class="fa fa-3x fa-search text-danger"></span>
      				<h4>Ricerca nuovi produttori</h4>
      				<p>Tutti i produttori di PortAlGas da poter ricercare e confrontare</p>
    			</div>	
    		</a>
    	</div>
    </div>
<?php
} // end if($isReferentGeneric)
?>
	
	<?php
	if($isManager || $isManagerDelivery) 
		$url = "/administrator/index.php?option=com_cake&amp;controller=Deliveries&amp;action=index";
	else 
		$url = "/administrator/index.php?option=com_cake&amp;controller=Deliveries&amp;action=view";
	?>	
    <div class="col-xs-12 col-sm-4 col-md-3">
	    <div class="box-container">
    		<a href="<?php echo $url;?>">
	    		<div class="box">
    		  		<span class="fa fa-3x fa-calendar"></span>
      				<h4>Consegne</h4>
      				<p>Gestisci le consegne</p>
    			</div>	
    		</a>
    	</div>
    </div>
	
	<?php 
	if($isRoot || $isManager) {
	?>
    <div class="col-xs-12 col-sm-4 col-md-3">
	    <div class="box-container">
	    	<!-- a href="/administrator/index.php?option=com_users" -->
	    	<a href="/administrator/index.php?option=com_cake&amp;controller=Users&amp;action=index">
    			<div class="box">
    		  		<span class="fa fa-3x fa-users"></span>
      				<h4>Utenti</h4>
      				<p>Gestisci i gasisti e i ruoli</p>
    			</div>	
    		</a>
    	</div>
    </div>
	<?php 
	}
	?>
    
	<?php
	if($isSuperReferente) {
	?>
    <div class="col-xs-12 col-sm-4 col-md-3">
	    <div class="box-container">
    		<a href="/administrator/index.php?option=com_cake&amp;controller=MonitoringOrders&amp;action=home">
	    		<div class="box">
    		  		<span class="fa fa-3x fa-life-ring"></span>
      				<h4>Ordini da monitorare</h4>
      				<p>Per seguire gli ordini più critici</p>
    			</div>	
    		</a>
    	</div>
    </div>		
    <div class="col-xs-12 col-sm-4 col-md-3">
	    <div class="box-container">
    		<a href="/administrator/index.php?option=com_cake&amp;controller=MonitoringSuppliersOrganizations&amp;action=home">
	    		<div class="box">
    		  		<span class="fa fa-3x fa-life-ring"></span>
      				<h4>Produttori da monitorare</h4>
      				<p>Per seguire i referenti meno pratici</p>
    			</div>	
    		</a>
    	</div>
    </div>		
	<?php
	}
	?>
	
    <div class="col-xs-12 col-sm-4 col-md-3">
	    <div class="box-container">
    		<a href="/administrator/index.php?option=com_cake&amp;controller=Mails&amp;action=send">
	    		<div class="box">
    		  		<span class="fa fa-3x fa-send"></span>
      				<h4>Mail</h4>
      				<p>Invia le mail ai gasisti, ai referenti, etc</p>
    			</div>	
    		</a>
    	</div>
    </div>
	
    <div class="col-xs-12 col-sm-4 col-md-3">
	    <div class="box-container">
    		<a href="/administrator/index.php?option=com_cake&amp;controller=Manuals&amp;action=index">
	    		<div class="box">
    		  		<span class="fa fa-3x fa-info-circle"></span>
      				<h4>Manuale</h4>
      				<p>Se alcuni passaggi non vi sono chiari</p>
    			</div>	
    		</a>
    	</div>
    </div>
    <div class="col-xs-12 col-sm-4 col-md-3">
	    <div class="box-container">
    		<a target="_blank" href="https://www.facebook.com/portalgas.it">
	    		<div class="box">
    		  		<span class="fa fa-3x fa-facebook"></span>
      				<h4>Facebook</h4>
      				<p>Rimani aggiornato seguendoci su Facebook</p>
    			</div>	
    		</a>
    	</div>
    </div>
    <div class="col-xs-12 col-sm-4 col-md-3">
	    <div class="box-container">
    		<a target="_blank" href="https://www.youtube.com/channel/UCo1XZkyDWhTW5Aaoo672HBA">
	    		<div class="box">
    		  		<span class="fa fa-3x fa-youtube"></span>
      				<h4>YouTube</h4>
      				<p>I video tutorial sul canale YouTube</p>
    			</div>	
    		</a>
    	</div>
    </div>
	
</div>  <!-- container-fluid text-center -->
</div>  <!-- row -->

<?php
/*
 *  elenco ruoli
 */
if(Configure::read('developer.mode')) {
	if(isset($user_userGroups))
	foreach($user_userGroups as $user_userGroup) {
		echo ' '.$user_userGroup.'<br />';
	}	
}	


/*
 *  elenco ordini
 */
if($isReferentGeneric && !empty($ordersResults)) {
	
	echo '<p style="clear: both;">';
	
	foreach ($ordersResults as $result) {

		$id = $result['Order']['delivery_id'].'_'.$result['Order']['id'];
		
		echo '<div id="tabs-'.$id.'" style="margin-top:5px;">';
		
		echo '<div class="table-responsive"><table class="table">';
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
		echo '</table></div>';
		
		

		echo '<ul class="nav nav-tabs">'; // nav-tabs nav-pills
		if($result['Order']['toValidate'] || $result['Order']['toQtaMassima'] || $result['Order']['toQtaMinimaOrder'])
			echo '<li class="active"><a href="#tabs-0-'.$id.'" data-toggle="tab" onClick="javascript:AjaxCallToDocPreview'.$id.'(\'to-articles-monitoring\', \'tabs-0-'.$id.'\')">'.__('to_articles_short').'</a></li>';
		else
			echo '<li class="active"><a href="#tabs-0-'.$id.'" data-toggle="tab" onClick="javascript:AjaxCallToDocPreview'.$id.'(\'to-articles\', \'tabs-0-'.$id.'\')">'.__('to_articles_short').'</a></li>';
		echo '<li><a href="#tabs-1-'.$id.'" data-toggle="tab" onClick="javascript:AjaxCallToDocPreview'.$id.'(\'to-articles-details\', \'tabs-1-'.$id.'\')">'.__('to_articles_details_short').'</a></li>';
		echo '<li><a href="#tabs-2-'.$id.'" data-toggle="tab" onClick="javascript:AjaxCallToDocPreview'.$id.'(\'to-users\', \'tabs-2-'.$id.'\')">'.__('to_users_short').'</a></li>';
		echo '<li><a href="#tabs-3-'.$id.'" data-toggle="tab" onClick="javascript:AjaxCallToDocPreview'.$id.'(\'to-articles-weight\', \'tabs-3-'.$id.'\')">'.__('to_articles_weight_short').'</a></li>';
		echo '</ul>';
		
		echo '<div class="tab-content">';
		echo '<div class="tab-pane fade active in" id="tabs-0-'.$id.'">';
		echo '</div>';
		echo '<div class="tab-pane fade" id="tabs-1-'.$id.'">';
		echo '</div>';
		echo '<div class="tab-pane fade" id="tabs-2-'.$id.'">';
		echo '</div>';
		echo '<div class="tab-pane fade" id="tabs-3-'.$id.'">';
		echo '</div>';
		echo '</div>'; // end class tab-content
				
		echo $this->element('boxOrderLimit', array('orderResult' => $result));
		
		echo '</div>';
		
		?>
			<script type="text/javascript">
			function AjaxCallToDocPreview<?php echo $id;?> (doc_options, idDivTarget) {
			
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
				if(doc_options=='to-articles-monitoring') {
					a = 'N';  /* $("input[name='colli1']:checked").val(); */
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
					var url = '/administrator/index.php?option=com_cake&controller=ExportDocs&action=exportToArticlesWeight&delivery_id=<?php echo $result['Order']['delivery_id'];?>&order_id=<?php echo $result['Order']['id'];?>&doc_options='+doc_options+'&doc_formato=PREVIEW&format=notmpl';
				else
					var url = '/administrator/index.php?option=com_cake&controller=ExportDocs&action=exportToReferent&delivery_id=<?php echo $result['Order']['delivery_id'];?>&order_id=<?php echo $result['Order']['id'];?>&doc_options='+doc_options+'&doc_formato=PREVIEW&a='+a+'&b='+b+'&c='+c+'&d='+d+'&e='+e+'&f='+f+'&g='+g+'&h='+h+'&format=notmpl';
				
				ajaxCallBox(url, idDivTarget);	
			}
			
			$(document).ready(function() {
				<?php
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
?>


<script type="text/javascript">
$(document).ready(function() {

	<?php 
	/*
	 * non +, nell'elenco ordine gestisco la chiusura
	if(!empty($alertDeliveriesToClose)) {
		if(!$popUpDisabled)
			echo "apriPopUpBootstrap('".Configure::read('App.server')."/administrator/index.php?option=com_cake&controller=PopUp&action=alert_cassiere_deliveries_to_close&format=notmpl', '')";
	}
	*/
	?>

	$(".actionMenu").each(function () {
		$(this).click(function() {

			$('.menuDetails').css('display','none');
			
			var idRow = $(this).attr('id');
			numRow = idRow.substring(idRow.indexOf('-')+1,idRow.lenght);
			$('#menuDetails-'+numRow).show();

			viewOrderSottoMenu(numRow,"bgLeft");

			var offset = $(this).offset();
			var newTop = (offset.top - 100);
			var newLeft = (offset.left - 350);

			$('#menuDetails-'+numRow).offset({ top: newTop, left: newLeft});			
		});
	});	

	$(".menuDetailsClose").each(function () {
		$(this).click(function() {
			var idRow = $(this).attr('id');
			numRow = idRow.substring(idRow.indexOf('-')+1,idRow.lenght);
			$('#menuDetails-'+numRow).hide('slow');
		});
	});		
});
</script>
