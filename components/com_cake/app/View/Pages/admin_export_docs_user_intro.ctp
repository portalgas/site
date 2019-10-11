<?php
echo '<div class="docs">';

if($user->organization['Template']['payToDelivery']=='POST' || $user->organization['Template']['payToDelivery']=='ON-POST') {
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
	
<div class="table-responsive">
<table class="table table-hover">
<thead>
<tr>
	<th colspan="2">Tipologia di documento</th>
	<th>Filtro</th>
	<th>Formato pdf</th>
	<th>Formato csv</th>
	<th>Formato excel</th>
</tr>	
<thead>
<tbody>
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
	
		<p>Opzioni stampa</p>

		<div class="input ">
			<label class="control-label" for="filterType">Visualizza le tipologie </label>
			<label class="radio-inline" for="filterTypeY2">
				<input checked="checked" value="Y" id="filterTypeY2" name="filterType2" type="radio"> Si</label>
			<label class="radio-inline" for="filterTypeN2">
				<input value="N" id="filterTypeN2" name="filterType2" type="radio"> No</label>
		</div>
		<div class="input ">
			<label class="control-label" for="filterType">Visualizza le categorie </label>
			<label class="radio-inline" for="filterCategoryY2">
				<input checked="checked" value="Y" id="filterCategoryY2" name="filterCategory2" type="radio"> Si</label>
			<label class="radio-inline" for="filterCategoryN2">
				<input value="N" id="filterCategoryN2" name="filterCategory2" type="radio"> No</label>
		</div>
		<div class="input ">
			<label class="control-label" for="filterType">Visualizza le note </label>
			<label class="radio-inline" for="filterNotaY2">
				<input checked="checked" value="Y" id="filterNotaY2" name="filterNota2" type="radio"> Si</label>
			<label class="radio-inline" for="filterNotaN2">
				<input value="N" id="filterNotaN2" name="filterNota2" type="radio"> No</label>
		</div>

		<?php 
		if($user->organization['Organization']['hasFieldArticleIngredienti']=='Y') {
		?>
		<div class="input ">
			<label class="control-label" for="filterType">Visualizza gli ingredienti </label>
			<label class="radio-inline" for="filterIngredientiY2">
				<input checked="checked" value="Y" id="filterIngredientiY2" name="filterIngredienti2" type="radio"> Si</label>
			<label class="radio-inline" for="filterIngredientiN2">
				<input value="N" id="filterIngredientiN2" name="filterIngredienti2" type="radio"> No</label>
		</div>
		<?php 
		}
		?>
					
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
echo $this->element('reportUsers', ['type' => 'BO', 'isManager' => $isManager, 'organizationsResults' => $organizationsResults]);

if($isRoot || $isManager) {
?>
	<tr>
		<td></td>
		<td>Tutti gli <b>acquisti</b> dell'utente</td>
		<td style="vertical-align: middle;">
			<?php
				echo $this->Form->input('user_id',array('label' => false, 'id' => 'other_user_id',
															'class'=> 'selectpicker', 'data-live-search' => true,
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
		<td style="vertical-align: middle;" rowspan="2">
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
	<tr>
		<td></td>
		<td>Tutti gli <b>acquisti per gasista</b> alla Consegna selezionata</td>
		<td></td>
		<td></td>
		<td>
		<?php
		if(Configure::read('developer.mode'))
			echo 'No in developer mode';
		else
			echo '<a class="users_delivery_sum_orders_excel" id="users_delivery_sum_orders_excel-EXCEL" style="cursor:pointer;" rel="nofollow" title="stampa dei referenti '.__('formatFileExcel').'"><img alt="EXCEL" src="'.Configure::read('App.img.cake').'/minetypes/32x32/vcalendar.png"></a>';
		?>		
		</td>
	</tr>	
<?php 
}
?>

<?php 
if($user->organization['Template']['payToDelivery']=='POST' || $user->organization['Template']['payToDelivery']=='ON-POST') {
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
	echo $this->element('reportStoreroom', array('type' => 'BO', 'isUserCurrentStoreroom' => $isUserCurrentStoreroom, 'isManager' => $isManager, 'deliveries' => $deliveriesStorerooms));
	
echo '<tbody>';
echo '</table>';
echo '</div>';
echo '</div>';
?>		
	


<script type="text/javascript">
$(document).ready(function() {

	$('.exportArticlesOrders').click(function() {
		var order_id = $('#order_id').val();
		if(order_id=="" || order_id==undefined) {
			alert("Devi scegliere l'ordine");
			return false;
		}
		
		var id =  $(this).attr('id');
		idArray = id.split('-');
		var action          = idArray[0];
		var doc_formato = idArray[1];

		/*
		 * filtri
		 */
		var filterType = $("input[name='filterType2']:checked").val();
		var filterCategory = $("input[name='filterCategory2']:checked").val();
		var filterNota = $("input[name='filterNota2']:checked").val();
		var filterIngredienti = 'N';
		<?php 
		if($user->organization['Organization']['hasFieldArticleIngredienti']=='Y') {
		?>
		filterIngredienti = $("input[name='filterIngredienti2']:checked").val();	
		<?php 
		}
		?>
				
		window.open('/administrator/index.php?option=com_cake&controller=ExportDocs&action='+action+'&order_id='+order_id+'&filterType='+filterType+'&filterCategory='+filterCategory+'&filterNota='+filterNota+'&filterIngredienti='+filterIngredienti+'&doc_formato='+doc_formato+'&format=notmpl','win2','status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=640,height=480,directories=no,location=no');
	});
	
	$('.suppliersOrganizations').click(function() {	
		var id =  $(this).attr('id');
		idArray = id.split('-');
		var action          = idArray[0];
		var doc_formato = idArray[1];

		window.open('/administrator/index.php?option=com_cake&controller=ExportDocs&action='+action+'&doc_formato='+doc_formato+'&format=notmpl','win2','status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=640,height=480,directories=no,location=no');		
	});
	
	<?php 
	if($isRoot || $isManager || $isSuperReferente || $isCassiere) {
	?>
		$('.exportToCassiereAllDelivery').click(function() {
			var delivery_id = $('#other_delivery_id2').val();
			if(delivery_id=="") {
				alert("<?php echo __('jsAlertDeliveryRequired');?>");
				return false;
			}
			
			var id =  $(this).attr('id');
			idArray = id.split('-');
			var action          = idArray[0];
			var doc_formato = idArray[1];
	
			window.open('/administrator/index.php?option=com_cake&controller=ExportDocs&action='+action+'&delivery_id='+delivery_id+'&doc_options=to-delivery-cassiere-users-compact-all&doc_formato='+doc_formato+'&format=notmpl','win2','status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=640,height=480,directories=no,location=no');
		});
		
		$('.users_delivery_sum_orders_excel').click(function() {
			var delivery_id = $('#other_delivery_id2').val();
			if(delivery_id=="") {
				alert("<?php echo __('jsAlertDeliveryRequired');?>");
				return false;
			}
			
			var id =  $(this).attr('id');
			idArray = id.split('-');
			var action      = idArray[0];
			var doc_formato = idArray[1];
	
			window.open('/administrator/index.php?option=com_cake&controller=ExportDocs&action='+action+'&delivery_id='+delivery_id+'&doc_options=users_data_delivery_sum_orders&doc_formato='+doc_formato+'&format=notmpl','win2','status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=640,height=480,directories=no,location=no');
		});		
		
	<?php 
	}

	
	if($isRoot || $isManager) {
	?>
		$('.exportOtherDelivery').click(function() {
			var user_id = $('#other_user_id').val();
			if(user_id=="") {
				alert("<?php echo __('jsAlertUserRequired');?>");
				return false;
			}
			
			var delivery_id = $('#other_delivery_id').val();
			if(delivery_id=="") {
				alert("<?php echo __('jsAlertDeliveryRequired');?>");
				return false;
			}
			
			var id =  $(this).attr('id');
			idArray = id.split('-');
			var action          = idArray[0];
			var doc_formato = idArray[1];
	
			window.open('/administrator/index.php?option=com_cake&controller=ExportDocs&action='+action+'&delivery_id='+delivery_id+'&user_id='+user_id+'&doc_formato='+doc_formato+'&format=notmpl','win2','status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=640,height=480,directories=no,location=no');
		});
	<?php 
	}

	if($user->organization['Template']['payToDelivery']=='POST' || $user->organization['Template']['payToDelivery']=='ON-POST') {
		if($isRoot || $isManager|| $isTesoriereGeneric) {
	?>
		$('.exportRequestPayment').click(function() {
			var user_id = $('#request_payment_user_id').val();
			if(user_id=="") {
				alert("<?php echo __('jsAlertUserRequired');?>");
				return false;
			}
			
			var request_payment_id = $('#request_payment_id').val();
			if(request_payment_id=="") {
				alert("<?php echo __('jsAlertRequestPaymentRequired');?>");
				return false;
			}
			
			var id =  $(this).attr('id');
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