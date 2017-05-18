<?php
echo '<div class="docs">';

if($user->organization['Organization']['payToDelivery']=='POST' || $user->organization['Organization']['payToDelivery']=='ON-POST') {
	echo '<div class="legenda">';
	echo '<h1>Richieste di pagamento</h1>';

	if(empty($requestPaymentsResults)) 
		echo "Non ci sono richieste di pagamento da pagare";
	else {
		foreach ($requestPaymentsResults as $requestPaymentsResult) {
			echo '<p>';
			echo '<a target="_blank" ';
			echo "href=\"/administrator/index.php?option=com_cake&controller=ExportDocs&action=userRequestPayment&request_payment_id=".$requestPaymentsResult['RequestPayment']['id']."&doc_formato=PDF&format=notmpl\"";
			echo ' id="userRequestPayment-PDF" style="cursor:pointer;" rel="nofollow" title="stampa la richiesta di pagamento '.__('formatFilePdf').'"><img alt="PDF" src="'.Configure::read('App.img.cake').'/minetypes/32x32/pdf.png">';
			echo '&nbsp;'.__('request_payment_num').' '.$requestPaymentsResult['RequestPayment']['num'];
			echo ' di '.$this->Time->i18nFormat($requestPaymentsResult['RequestPayment']['data_send'],"%A %e %B %Y");
			echo '</a>: '.$requestPaymentsResult['SummaryPayment']['importo_dovuto_e'];
			echo ' ('.$this->App->traslateEnum($requestPaymentsResult['SummaryPayment']['stato']).')';
			echo '</p>'; 

			if(!empty($requestPaymentsResult['RequestPayment']['nota'])) 
				echo '<p>'.$requestPaymentsResult['RequestPayment']['nota'].'</p>';			
		}
	}
	
	echo '</div>';
}
?>
	
<table cellpadding = "0" cellspacing = "0">
<tr>
	<th colspan="2">Tipologia di documento</th>
	<th>Filtro</th>
	<th>Formato pdf</th>
	<th>Formato csv</th>
	<th>Formato excel</th>
</tr>	

<?php
	echo $this->element('reportDeliveries', array('type' => 'BO', 'deliveries' => $deliveries));
    
    echo $this->element('reportArticles', array('suppliersOrganization' => $suppliersOrganization, 'type' => 'BO'));

    if($user->organization['Organization']['hasDes']=='Y') 
        echo $this->element('reportArticlesDes', array('desOrganizationResults' => $desOrganizationResults, 'type' => 'BO'));

if(!empty($orders)) {
?>
<tr>
	<td><a action="ArticlesOrders" class="actionTrConfig openTrConfig" href="#" title="<?php echo __('Href_title_expand_config');?>"></a></td>
	<td>Tutti gli <b>articoli</b> dell'ordine</td>
	<td>
		<?php
			echo $this->Form->input('orders',array('id' => 'order_id', 'label' => false,'options' => $orders, 'empty' => 'Scegli l\'ordine', 'escape' => false));
		?>
	</td>
	<td><a class="exportArticlesOrders" id="articlesOrders-PDF" style="cursor:pointer;" rel="nofollow" title="stampa gli articoli dell'ordine <?php echo __('formatFilePdf');?>"><img alt="PDF" src="<?php echo Configure::read('App.img.cake');?>/minetypes/32x32/pdf.png"></a></td>
	<td><a class="exportArticlesOrders" id="articlesOrders-CSV" style="cursor:pointer;" rel="nofollow" title="stampa gli articoli dell'ordine <?php echo __('formatFileCsv');?>"><img alt="CSV" src="<?php echo Configure::read('App.img.cake');?>/minetypes/32x32/spreadsheet.png"></a></td>
	<td>
		<?php
		if(Configure::read('developer.mode'))
			echo 'No in developer mode';
		else
			echo '<a class="exportArticlesOrders" id="articlesOrders-EXCEL" style="cursor:pointer;" rel="nofollow" title="stampa gli articoli dell\'ordine '.__('formatFileExcel').'"><img alt="EXCEL" src="'.Configure::read('App.img.cake').'/minetypes/32x32/vcalendar.png"></a>';
		?>
	</td>
</tr>
<tr class="trConfig" id="trConfigId-ArticlesOrders">
	<td></td>
	<td colspan="5" id="tdConfigId-ArticlesOrders">
		
		<div class="left label" style="width:125px !important;">Opzioni stampa</div>
		<div class="left radio">
			<p>
				<label for="filterType">Visualizza le tipologie</label>
				<input type="radio" id="filterTypeY2" name="filterType2" value="Y" checked /><label for="filterTypeY2W">Si</label>
				<input type="radio" id="filterTypeN2" name="filterType2" value="N" /><label for="filterTypeN2">No</label>
			</p>
			<p>
				<label for="filterCategory">Visualizza le categorie</label>
				<input type="radio" id="filterCategoryY2" name="filterCategory2" value="Y" checked /><label for="filterCategoryY2">Si</label>
				<input type="radio" id="filterCategoryN2" name="filterCategory2" value="N" /><label for="filterCategoryN2">No</label>
			</p>	
			<p>
				<label for="filterNota">Visualizza le note</label>
				<input type="radio" id="filterNotaY2" name="filterNota2" value="Y" checked /><label for="filterNota2">Si</label>
				<input type="radio" id="filterNotaN2" name="filterNota2" value="N" /><label for="filterNotaN2">No</label>
			</p>
			<?php 
			if($user->organization['Organization']['hasFieldArticleIngredienti']=='Y') {
			?>
			<p>
				<label for="filterIngredientiY">Visualizza gli ingredienti</label>
				<input type="radio" id="filterIngredientiY2" name="filterIngredienti2" value="Y" checked /><label for="filterIngredientiY2">Si</label>
				<input type="radio" id="filterIngredientiN2" name="filterIngredienti2" value="N" /><label for="filterIngredientiN2">No</label>
			</p>
			<?php 
			}
			?>							
		</div>	
					
	</td>
</tr>	
<?php 
}
?>
<tr>
	<td></td>
	<td>Anagrafica dei <b>produttori</b></td>
	<td></td>
	<td><a class="suppliersOrganizations" id="suppliersOrganizations-PDF" style="cursor:pointer;" rel="nofollow" title="stampa i produttori <?php echo __('formatFilePdf');?>"><img alt="PDF" src="<?php echo Configure::read('App.img.cake');?>/minetypes/32x32/pdf.png"></a></td>
	<td></td>
	<td>
		<?php
		if(Configure::read('developer.mode'))
			echo 'No in developer mode';
		else
			echo '<a class="suppliersOrganizations" id="suppliersOrganizations-EXCEL" style="cursor:pointer;" rel="nofollow" title="stampa i produttori '.__('formatFileExcel').'"><img alt="EXCEL" src="'.Configure::read('App.img.cake').'/minetypes/32x32/vcalendar.png"></a>';
		?>
	</td>		
</tr>	
		
<?php
echo $this->element('reportUsers', array('type' => 'BO', 'isManager' => $isManager));

if($isRoot || $isManager) {
?>
	<tr>
		<td></td>
		<td>Tutti gli <b>acquisti</b> dell'utente</td>
		<td style="vertical-align: middle;">
			<?php
				echo $this->Form->input('user_id',array('label' => false, 'id' => 'other_user_id',
															'class'=> 'selectpicker', 'data-live-search' => true',
															'empty' => 'Scegli l\'utente','escape' => false));

				echo '<br />';
				
				echo $this->Form->input('delivery_id',array('label' => false, 'id' => 'other_delivery_id',
															'empty' => 'Scegli la consegna','escape' => false));
			?>
		</td>
		<td><a class="exportOtherDelivery" id="userOtherCart-PDF" style="cursor:pointer;" rel="nofollow" title="stampa gli acquisti dell'utente scelto <?php echo __('formatFilePdf');?>"><img alt="PDF" src="<?php echo Configure::read('App.img.cake');?>/minetypes/32x32/pdf.png"></a></td>
		<td></td>
		<td></td>
	</tr>
<?php 
}
?>	

<?php 
if($isRoot || $isManager || $isSuperReferente || $isCassiere) {
?>
	<tr>
		<td></td>
		<td>Tutti gli <b>ordini</b> della consegna</td>
		<td style="vertical-align: middle;">
			<?php
				echo $this->Form->input('delivery_id',array('label' => false, 'id' => 'other_delivery_id2',
															'empty' => 'Scegli la consegna','escape' => false));
			?>
		</td>
		<td></td>
		<td></td>
		<td>
		<?php
		if(Configure::read('developer.mode'))
			echo 'No in developer mode';
		else
			echo '<a class="exportToCassiereAllDelivery" id="exportToCassiereAllDelivery-EXCEL" style="cursor:pointer;" rel="nofollow" title="stampa dei referenti '.__('formatFileExcel').'"><img alt="EXCEL" src="'.Configure::read('App.img.cake').'/minetypes/32x32/vcalendar.png"></a>';
		?>		
		</td>
	</tr>
<?php 
}
?>

<?php 
if($user->organization['Organization']['payToDelivery']=='POST' || $user->organization['Organization']['payToDelivery']=='ON-POST') {
	if($isRoot || $isManager || $isTesoriereGeneric) {
?>
	<tr>
		<td></td>
		<td>La <b>richiesta di pagamento</b> dell'utente</td>
		<td style="vertical-align: middle;">
			<?php
				echo $this->Form->input('user_id',array('label' => false, 'id' => 'request_payment_user_id',
															'class'=> 'selectpicker', 'data-live-search' => true,
															'empty' => 'Scegli l\'utente','escape' => false));

				echo '<br />';
				
				echo $this->Form->input('request_payment_id',array('label' => false, 'id' => 'request_payment_id', 'options' => $requestPaymentsListResults,
															'empty' => 'Scegli la richiesta di pagamento','escape' => false));
			?>
		</td>
		<td><a class="exportRequestPayment" id="userOtherRequestPayment-PDF" style="cursor:pointer;" rel="nofollow" title="stampa la richiesta di pagamento dell'utente scelto <?php echo __('formatFilePdf');?>"><img alt="PDF" src="<?php echo Configure::read('App.img.cake');?>/minetypes/32x32/pdf.png"></a></td>
		<td></td>
		<td></td>
	</tr>
<?php 
	} 
}

if($user->organization['Organization']['hasStoreroom']=='Y') 
	echo $this->element('reportStoreroom', array('type' => 'BO'));
	
echo '</table>';
echo '</div>';
?>		
	


<script type="text/javascript">
jQuery(document).ready(function() {

	jQuery('.exportArticlesOrders').click(function() {
		var order_id = jQuery('#order_id').val();
		if(order_id=="" || order_id==undefined) {
			alert("Devi scegliere l'ordine");
			return false;
		}
		
		var id =  jQuery(this).attr('id');
		idArray = id.split('-');
		var action          = idArray[0];
		var doc_formato = idArray[1];

		/*
		 * filtri
		 */
		var filterType = jQuery("input[name='filterType2']:checked").val();
		var filterCategory = jQuery("input[name='filterCategory2']:checked").val();
		var filterNota = jQuery("input[name='filterNota2']:checked").val();
		var filterIngredienti = 'N';
		<?php 
		if($user->organization['Organization']['hasFieldArticleIngredienti']=='Y') {
		?>
		filterIngredienti = jQuery("input[name='filterIngredienti2']:checked").val();	
		<?php 
		}
		?>
				
		window.open('/administrator/index.php?option=com_cake&controller=ExportDocs&action='+action+'&order_id='+order_id+'&filterType='+filterType+'&filterCategory='+filterCategory+'&filterNota='+filterNota+'&filterIngredienti='+filterIngredienti+'&doc_formato='+doc_formato+'&format=notmpl','win2','status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=640,height=480,directories=no,location=no');
	});
	
	jQuery('.suppliersOrganizations').click(function() {	
		var id =  jQuery(this).attr('id');
		idArray = id.split('-');
		var action          = idArray[0];
		var doc_formato = idArray[1];

		window.open('/administrator/index.php?option=com_cake&controller=ExportDocs&action='+action+'&doc_formato='+doc_formato+'&format=notmpl','win2','status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=640,height=480,directories=no,location=no');		
	});
	
	<?php 
	if($isRoot || $isManager || $isSuperReferente || $isCassiere) {
	?>
		jQuery('.exportToCassiereAllDelivery').click(function() {
			var delivery_id = jQuery('#other_delivery_id2').val();
			if(delivery_id=="") {
				alert("<?php echo __('jsAlertDeliveryRequired');?>");
				return false;
			}
			
			var id =  jQuery(this).attr('id');
			idArray = id.split('-');
			var action          = idArray[0];
			var doc_formato = idArray[1];
	
			window.open('/administrator/index.php?option=com_cake&controller=ExportDocs&action='+action+'&delivery_id='+delivery_id+'&doc_options=to-delivery-cassiere-users-compact-all&doc_formato='+doc_formato+'&format=notmpl','win2','status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=640,height=480,directories=no,location=no');
		});
	<?php 
	}

	
	if($isRoot || $isManager) {
	?>
		jQuery('.exportOtherDelivery').click(function() {
			var user_id = jQuery('#other_user_id').val();
			if(user_id=="") {
				alert("<?php echo __('jsAlertUserRequired');?>");
				return false;
			}
			
			var delivery_id = jQuery('#other_delivery_id').val();
			if(delivery_id=="") {
				alert("<?php echo __('jsAlertDeliveryRequired');?>");
				return false;
			}
			
			var id =  jQuery(this).attr('id');
			idArray = id.split('-');
			var action          = idArray[0];
			var doc_formato = idArray[1];
	
			window.open('/administrator/index.php?option=com_cake&controller=ExportDocs&action='+action+'&delivery_id='+delivery_id+'&user_id='+user_id+'&doc_formato='+doc_formato+'&format=notmpl','win2','status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=640,height=480,directories=no,location=no');
		});
	<?php 
	}

	if($user->organization['Organization']['payToDelivery']=='POST' || $user->organization['Organization']['payToDelivery']=='ON-POST') {
		if($isRoot || $isManager|| $isTesoriereGeneric) {
	?>
		jQuery('.exportRequestPayment').click(function() {
			var user_id = jQuery('#request_payment_user_id').val();
			if(user_id=="") {
				alert("<?php echo __('jsAlertUserRequired');?>");
				return false;
			}
			
			var request_payment_id = jQuery('#request_payment_id').val();
			if(request_payment_id=="") {
				alert("<?php echo __('jsAlertRequestPaymentRequired');?>");
				return false;
			}
			
			var id =  jQuery(this).attr('id');
			idArray = id.split('-');
			var action          = idArray[0];
			var doc_formato = idArray[1];
	
			window.open('/administrator/index.php?option=com_cake&controller=ExportDocs&action='+action+'&request_payment_id='+request_payment_id+'&user_id='+user_id+'&doc_formato='+doc_formato+'&format=notmpl','win2','status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=640,height=480,directories=no,location=no');
		});
	<?php
		}
	}
	?>	
});
</script>