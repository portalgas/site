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
			echo ' id="userRequestPayment-PDF" style="cursor:pointer;" rel="nofollow" title="stampa la richiesta di pagamento in formato PDF"><img alt="PDF" src="'.Configure::read('App.img.cake').'/minetypes/32x32/pdf.png">';
			echo '&nbsp;Richiesta di pagamento num. '.$requestPaymentsResult['RequestPayment']['num'];
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
<tr>
	<td></td>
	<td>Tutti gli <b>acquisti</b> della consegna</td>
	<td rowspan="2" style="vertical-align: middle;">
		<?php
			echo $this->Form->input('delivery_id',array('label' => false, 'id' => 'delivery_id',
														'empty' => 'Scegli la consegna','escape' => false));
		?>
	</td>
	<td><a class="exportDelivery" id="userCart-PDF" style="cursor:pointer;" rel="nofollow" title="stampa gli acquisti associati alla consegna in formato PDF"><img alt="PDF" src="<?php echo Configure::read('App.img.cake');?>/minetypes/32x32/pdf.png"></a></td>
	<td></td>
	<td></td>
</tr>
<tr>
	<td></td>
	<td>Tutti gli <b>utenti</b> che saranno presenti alla <b>consegna</b></td>
	<td><a class="exportDelivery" id="usersDelivery-PDF" style="cursor:pointer;" rel="nofollow" title="stampa gli utenti che saranno presenti alla consegna in formato PDF"><img alt="PDF" src="<?php echo Configure::read('App.img.cake');?>/minetypes/32x32/pdf.png"></a></td>
	<td><a class="exportDelivery" id="usersDelivery-CSV" style="cursor:pointer;" rel="nofollow" title="stampa gli utenti che saranno presenti alla consegna in formato CSV"><img alt="CSV" src="<?php echo Configure::read('App.img.cake');?>/minetypes/32x32/spreadsheet.png"></a></td>
	<td>
		<?php
		if(Configure::read('developer.mode'))
			echo 'No in developer mode';
		else
			echo '<a class="exportDelivery" id="usersDelivery-EXCEL" style="cursor:pointer;" rel="nofollow" title="stampa gli utenti che saranno presenti alla consegna in formato EXCEL"><img alt="EXCEL" src="'.Configure::read('App.img.cake').'/minetypes/32x32/vcalendar.png"></a>';
		?>
	</td>		
</tr>

<!-- aggiornare anche admin_export_docs_articles -->
<!-- aggiornare anche admin_export_docs_articles -->
<!-- aggiornare anche admin_export_docs_articles -->
<tr>
	<td><a action="Articles" class="actionTrConfig openTrConfig" href="#" title="<?php echo __('Href_title_expand_config');?>"></a></td>
	<td>Tutti gli <b>articoli</b> del produttore</td>
	<td>
		<?php
			$options = array('label' => false, 
							 'id' => 'supplier_organization_id', 'options' => $suppliersOrganization,
							  'empty' => 'Scegli per produttore','escape' => false);
			if(count($suppliersOrganization) > Configure::read('HtmlSelectWithSearchNum')) 
				$options += array('class'=> 'chosen-select', 'style' => 'width:350px;'); 
			echo $this->Form->input('supplier_organization_id',$options);
		?>
	</td>
	<td><a class="exportArticles" id="articlesSupplierOrganization-PDF" style="cursor:pointer;" rel="nofollow" title="stampa gli articoli del produttore in formato PDF"><img alt="PDF" src="<?php echo Configure::read('App.img.cake');?>/minetypes/32x32/pdf.png"></a></td>
	<td><a class="exportArticles" id="articlesSupplierOrganization-CSV" style="cursor:pointer;" rel="nofollow" title="stampa gli articoli del produttore in formato CSV"><img alt="CSV" src="<?php echo Configure::read('App.img.cake');?>/minetypes/32x32/spreadsheet.png"></a></td>
	<td>
		<?php
		if(Configure::read('developer.mode'))
			echo 'No in developer mode';
		else
			echo '<a class="exportArticles" id="articlesSupplierOrganization-EXCEL" style="cursor:pointer;" rel="nofollow" title="stampa gli articoli del produttore in formato EXCEL"><img alt="EXCEL" src="'.Configure::read('App.img.cake').'/minetypes/32x32/vcalendar.png"></a>';
		?>
	</td>		
</tr>
<tr class="trConfig" id="trConfigId-Articles">
	<td></td>
	<td colspan="5" id="tdConfigId-Articles">
		
		<div class="left label" style="width:125px !important;">Opzioni stampa</div>
		<div class="left radio">
			<p>
				<label for="filterType1">Visualizza le tipologie</label>
				<input type="radio" id="filterTypeY1" name="filterType1" value="Y" checked /><label for="filterTypeY1W">Si</label>
				<input type="radio" id="filterTypeN1" name="filterType1" value="N" /><label for="filterTypeN1">No</label>
			</p>
			<p>
				<label for="filterCategory1">Visualizza le categorie</label>
				<input type="radio" id="filterCategoryY1" name="filterCategory1" value="Y" checked /><label for="filterCategoryY1">Si</label>
				<input type="radio" id="filterCategoryN1" name="filterCategory1" value="N" /><label for="filterCategoryN1">No</label>
			</p>	
			<p>
				<label for="filterNota1">Visualizza le note</label>
				<input type="radio" id="filterNotaY1" name="filterNota1" value="Y" checked /><label for="filterNota1">Si</label>
				<input type="radio" id="filterNotaN1" name="filterNota1" value="N" /><label for="filterNotaN1">No</label>
			</p>
			<?php 
			if($user->organization['Organization']['hasFieldArticleIngredienti']=='Y') {
			?>
			<p>
				<label for="filterIngredienti1">Visualizza gli ingredienti</label>
				<input type="radio" id="filterIngredientiY1" name="filterIngredienti1" value="Y" checked /><label for="filterIngredientiY1">Si</label>
				<input type="radio" id="filterIngredientiN1" name="filterIngredienti1" value="N" /><label for="filterIngredientiN1">No</label>
			</p>
			<?php 
			}
			?>							
		</div>							
	</td>
</tr>	

<?php 
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
	<td><a class="exportArticlesOrders" id="articlesOrders-PDF" style="cursor:pointer;" rel="nofollow" title="stampa gli articoli dell'ordine in formato PDF"><img alt="PDF" src="<?php echo Configure::read('App.img.cake');?>/minetypes/32x32/pdf.png"></a></td>
	<td><a class="exportArticlesOrders" id="articlesOrders-CSV" style="cursor:pointer;" rel="nofollow" title="stampa gli articoli dell'ordine in formato CSV"><img alt="CSV" src="<?php echo Configure::read('App.img.cake');?>/minetypes/32x32/spreadsheet.png"></a></td>
	<td>
		<?php
		if(Configure::read('developer.mode'))
			echo 'No in developer mode';
		else
			echo '<a class="exportArticlesOrders" id="articlesOrders-EXCEL" style="cursor:pointer;" rel="nofollow" title="stampa gli articoli dell\'ordine in formato EXCEL"><img alt="EXCEL" src="'.Configure::read('App.img.cake').'/minetypes/32x32/vcalendar.png"></a>';
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
	<td><a class="suppliersOrganizations" id="suppliersOrganizations-PDF" style="cursor:pointer;" rel="nofollow" title="stampa i produttori in formato PDF"><img alt="PDF" src="<?php echo Configure::read('App.img.cake');?>/minetypes/32x32/pdf.png"></a></td>
	<td></td>
	<td>
		<?php
		if(Configure::read('developer.mode'))
			echo 'No in developer mode';
		else
			echo '<a class="suppliersOrganizations" id="suppliersOrganizations-EXCEL" style="cursor:pointer;" rel="nofollow" title="stampa i produttori in formato EXCEL"><img alt="EXCEL" src="'.Configure::read('App.img.cake').'/minetypes/32x32/vcalendar.png"></a>';
		?>
	</td>		
</tr>	
<tr>
	<td><a action="usersData" class="actionTrConfig openTrConfig" href="#" title="<?php echo __('Href_title_expand_config');?>"></a></td>
	<td>Anagrafica di tutti gli <b>utenti</b></td>
	<td></td>
	<td><a class="usersData" id="usersData-PDF" style="cursor:pointer;" rel="nofollow" title="stampa l'anagrafica degli utenti in formato PDF"><img alt="PDF" src="<?php echo Configure::read('App.img.cake');?>/minetypes/32x32/pdf.png"></a></td>
	<td><a class="usersData" id="usersData-CSV" style="cursor:pointer;" rel="nofollow" title="stampa l'anagrafica degli utenti in formato CSV"><img alt="CSV" src="<?php echo Configure::read('App.img.cake');?>/minetypes/32x32/spreadsheet.png"></a></td>
	<td>
		<?php
		if(Configure::read('developer.mode'))
			echo 'No in developer mode';
		else
			echo '<a class="usersData" id="usersData-EXCEL" style="cursor:pointer;" rel="nofollow" title="stampa l\'anagrafica degli utenti in formato EXCEL"><img alt="EXCEL" src="'.Configure::read('App.img.cake').'/minetypes/32x32/vcalendar.png"></a>';
		?>
	</td>		
</tr>
<tr class="trConfig" id="trConfigId-usersData">
	<td></td>
	<td colspan="5" id="tdConfigId-userData">
		
		<div class="left label" style="width:125px !important;">Opzioni stampa</div>
		<div class="left radio">
			<?php 
			foreach ($filterUserGroups as $id => $label) {
				echo '<div style="float:left;margin-right:10px;">';
				echo '<input type="checkbox" id="filterUserGroups'.$id.'" name="filterUserGroups" value="'.$id.'" checked />';
				echo '<label for="filterUserGroups'.$id.'">';
				echo $label;
				echo '</label> ';
				echo '</div>';					
			}
			
			/*
			echo '<div style="clear:both;float:left;margin-right:10px;">';
			echo '<input type="checkbox" id="filterUsersImg" name="filterUsersImg" value="Y" />';
			echo '<label for="filterUsersImg">';
			echo "Immagine degli utenti";
			echo '</label> ';
			echo '</div>';	
			*/			
			?>
		</div>	
					
	</td>
</tr>
<tr>
	<td><a action="referentsData" class="actionTrConfig openTrConfig" href="#" title="<?php echo __('Href_title_expand_config');?>"></a></td>
	<td>Stampa dei <b>referenti</b></td>
	<td></td>
	<td><a class="referentsData" id="referentsData-PDF" style="cursor:pointer;" rel="nofollow" title="stampa dei referenti in formato PDF"><img alt="PDF" src="<?php echo Configure::read('App.img.cake');?>/minetypes/32x32/pdf.png"></a></td>
	<td><a class="referentsData" id="referentsData-CSV" style="cursor:pointer;" rel="nofollow" title="stampa dei referenti in formato CSV"><img alt="CSV" src="<?php echo Configure::read('App.img.cake');?>/minetypes/32x32/spreadsheet.png"></a></td>
	<td>
		<?php
		if(Configure::read('developer.mode'))
			echo 'No in developer mode';
		else
			echo '<a class="referentsData" id="referentsData-EXCEL" style="cursor:pointer;" rel="nofollow" title="stampa dei referenti in formato EXCEL"><img alt="EXCEL" src="'.Configure::read('App.img.cake').'/minetypes/32x32/vcalendar.png"></a>';
		?>
	</td>		
</tr>
<tr class="trConfig" id="trConfigId-referentsData">
	<td></td>
	<td colspan="5" id="tdConfigId-referentsData">
		
		<div class="left label" style="width:125px !important;">Opzioni stampa</div>
		<div class="left radio">
			<p>
				<label for="filterOrder">Ordina per</label>
				<input type="radio" id="filterOrderSuppliers" name="filterOrder" value="SUPPLIERS" checked /><label for="filterOrderSupplier">Produttore</label>
				<input type="radio" id="filterOrderUsers" name="filterOrder" value="USERS" /><label for="filterOrderUsers">Utente</label>
			</p>
		</div>	
					
	</td>
</tr>		
<?php 
if($isRoot || $isManager) {
?>
	<tr>
		<td></td>
		<td>Tutti gli <b>acquisti</b> dell'utente</td>
		<td style="vertical-align: middle;">
			<?php
				echo $this->Form->input('user_id',array('label' => false, 'id' => 'other_user_id',
															'class'=> 'chosen-select',
															'style' => 'width:350px;',
															'empty' => 'Scegli l\'utente','escape' => false));

				echo '<br />';
				
				echo $this->Form->input('delivery_id',array('label' => false, 'id' => 'other_delivery_id',
															'empty' => 'Scegli la consegna','escape' => false));
			?>
		</td>
		<td><a class="exportOtherDelivery" id="userOtherCart-PDF" style="cursor:pointer;" rel="nofollow" title="stampa gli acquisti dell'utente scelto in formato PDF"><img alt="PDF" src="<?php echo Configure::read('App.img.cake');?>/minetypes/32x32/pdf.png"></a></td>
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
			echo '<a class="exportToCassiereAllDelivery" id="exportToCassiereAllDelivery-EXCEL" style="cursor:pointer;" rel="nofollow" title="stampa dei referenti in formato EXCEL"><img alt="EXCEL" src="'.Configure::read('App.img.cake').'/minetypes/32x32/vcalendar.png"></a>';
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
															'class'=> 'chosen-select',
															'style' => 'width:350px;',
															'empty' => 'Scegli l\'utente','escape' => false));

				echo '<br />';
				
				echo $this->Form->input('request_payment_id',array('label' => false, 'id' => 'request_payment_id', 'options' => $requestPaymentsListResults,
															'empty' => 'Scegli la richiesta di pagamento','escape' => false));
			?>
		</td>
		<td><a class="exportRequestPayment" id="userOtherRequestPayment-PDF" style="cursor:pointer;" rel="nofollow" title="stampa la richiesta di pagamento dell'utente scelto in formato PDF"><img alt="PDF" src="<?php echo Configure::read('App.img.cake');?>/minetypes/32x32/pdf.png"></a></td>
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
$(document).ready(function() {

	$('.exportDelivery').click(function() {
		var delivery_id = $('#delivery_id').val();
		if(delivery_id=="") {
			alert("Devi scegliere la consegna");
			return false;
		}
		
		var id =  $(this).attr('id');
		idArray = id.split('-');
		var action          = idArray[0];
		var doc_formato = idArray[1];

		window.open('/administrator/index.php?option=com_cake&controller=ExportDocs&action='+action+'&delivery_id='+delivery_id+'&doc_formato='+doc_formato+'&format=notmpl','win2','status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=640,height=480,directories=no,location=no');
	});
	
	$('.exportArticles').click(function() {
		var supplier_organization_id = $('#supplier_organization_id').val();
		if(supplier_organization_id=="") {
			alert("Devi scegliere il produttore");
			return false;
		}
		
		var id =  $(this).attr('id');
		idArray = id.split('-');
		var action      = idArray[0];
		var doc_formato = idArray[1];

		/*
		 * filtri
		 */
		var filterType = $("input[name='filterType1']:checked").val();
		var filterCategory = $("input[name='filterCategory1']:checked").val();
		var filterNota = $("input[name='filterNota1']:checked").val();
		var filterIngredienti = 'N';
		<?php 
		if($user->organization['Organization']['hasFieldArticleIngredienti']=='Y') {
		?>
		filterIngredienti = $("input[name='filterIngredienti1']:checked").val();	
		<?php 
		}
		?>
				
		window.open('/administrator/index.php?option=com_cake&controller=ExportDocs&action='+action+'&supplier_organization_id='+supplier_organization_id+'&filterType='+filterType+'&filterCategory='+filterCategory+'&filterNota='+filterNota+'&filterIngredienti='+filterIngredienti+'&doc_formato='+doc_formato+'&format=notmpl','win2','status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=640,height=480,directories=no,location=no');	
	});

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
	
	$('.usersData').click(function() {	
		var id =  $(this).attr('id');
		idArray = id.split('-');
		var action          = idArray[0];
		var doc_formato = idArray[1];

		var checked = $("input[name='filterUserGroups']:checked").val();
		var userGroupIds = "";
		$("input[name='filterUserGroups']").each(function() {
		  if($(this).is(":checked")) {
		     userGroupId = $(this).val();
		     userGroupIds += userGroupId+",";
		  } 
		});
		
		if(userGroupIds=="")  {
			alert("Devi selezionare almeno un gruppo");
			return false;
		}
		else
			userGroupIds = userGroupIds.substring(0,(userGroupIds.length-1));

		/*var filterUsersImg = $("input[name='filterUsersImg']:checked").val();
		if(filterUsersImg!='Y') */ filterUsersImg = 'N';
		
		window.open('/administrator/index.php?option=com_cake&controller=ExportDocs&action='+action+'&userGroupIds='+userGroupIds+'&filterUsersImg='+filterUsersImg+'&doc_formato='+doc_formato+'&format=notmpl','win2','status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=640,height=480,directories=no,location=no');		
	});	
	
	$('.referentsData').click(function() {	
		var id =  $(this).attr('id');
		idArray = id.split('-');
		var action      = idArray[0];
		var doc_formato = idArray[1];

		/*
		 * filtri
		 */
		var filterOrder = $("input[name='filterOrder']:checked").val();
		
		window.open('/administrator/index.php?option=com_cake&controller=ExportDocs&action='+action+'&filterOrder='+filterOrder+'&doc_formato='+doc_formato+'&format=notmpl','win2','status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=640,height=480,directories=no,location=no');		
	});	

	
	
	<?php 
	if($isRoot || $isManager || $isSuperReferente || $isCassiere) {
	?>
		$('.exportToCassiereAllDelivery').click(function() {
			var delivery_id = $('#other_delivery_id2').val();
			if(delivery_id=="") {
				alert("Devi scegliere la consegna");
				return false;
			}
			
			var id =  $(this).attr('id');
			idArray = id.split('-');
			var action          = idArray[0];
			var doc_formato = idArray[1];
	
			window.open('/administrator/index.php?option=com_cake&controller=ExportDocs&action='+action+'&delivery_id='+delivery_id+'&doc_options=to-delivery-cassiere-users-compact-all&doc_formato='+doc_formato+'&format=notmpl','win2','status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=640,height=480,directories=no,location=no');
		});
	<?php 
	}

	
	if($isRoot || $isManager) {
	?>
		$('.exportOtherDelivery').click(function() {
			var user_id = $('#other_user_id').val();
			if(user_id=="") {
				alert("Devi scegliere l'utente");
				return false;
			}
			
			var delivery_id = $('#other_delivery_id').val();
			if(delivery_id=="") {
				alert("Devi scegliere la consegna");
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
				alert("Devi scegliere l'utente");
				return false;
			}
			
			var request_payment_id = $('#request_payment_id').val();
			if(request_payment_id=="") {
				alert("Devi scegliere la richiesta di pagamento");
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
	
	$('.exportStoreroom').click(function() {
		var id =  $(this).attr('id');
		idArray = id.split('-');
		var id =  $(this).attr('id');
		var action      = idArray[0];
		var doc_formato = idArray[1];
				
		window.open('/administrator/index.php?option=com_cake&controller=Storerooms&action='+action+'&doc_formato='+doc_formato+'&format=notmpl','win2','status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=640,height=480,directories=no,location=no');	
	});
});
</script>