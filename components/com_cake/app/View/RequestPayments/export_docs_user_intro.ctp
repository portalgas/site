	<h2>Stampe</h2>

	<?php
	if($user->organization['Template']['payToDelivery']=='POST' || $user->organization['Template']['payToDelivery']=='ON-POST') {
		echo '<div role="alert" class="alert alert-success">';
		echo '<a class="close" data-dismiss="alert">&times;</a>';
				
		if(empty($requestPaymentsResults)) 
			echo "<p>Non ci sono richieste di pagamento da pagare</p>";
		else {		
			foreach ($requestPaymentsResults as $requestPaymentsResult) {
				echo '<p>';
				echo '<a target="_blank" ';
				echo "href=\"/?option=com_cake&controller=ExportDocs&action=userRequestPayment&request_payment_id=".$requestPaymentsResult['RequestPayment']['id']."&doc_formato=PDF&format=notmpl\"";
				echo ' id="userRequestPayment-PDF" style="cursor:pointer;" rel="nofollow" title="stampa la richiesta di pagamento in formato PDF"><img alt="PDF" src="'.Configure::read('App.img.cake').'/minetypes/32x32/pdf.png" />';
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
	
<form class="form-horizontal">	
<div class="table">
	<table class="table">
	<thead>
	<tr>
		<th colspan="2">Tipologia di documento</th>
		<th>Filtro</th>
		<th>Formato pdf</th>
		<th>Formato csv</th>
		<th>Formato excel</th>
	</tr>	
	</thead>
	<tbody>
	<tr>
		<td></td>
		<td>Tutti gli <b>acquisti</b> della consegna</td>
		<td rowspan="2" style="vertical-align: middle;">
			<?php
				echo $this->Form->input('delivery_id',array('label' => false, 'id' => 'delivery_id',
															'empty' => 'Scegli la consegna','escape' => false));
			?>
		</td>
		<td><a class="exportDelivery" id="userCart-PDF" style="cursor:pointer;" rel="nofollow" title="stampa gli acqusiti alla consegna in formato PDF"><i class="fa fa-file-pdf-o fa-2x"></i></a></td>
		<td></td>
		<td></td>
	</tr>
	<tr>
		<td></td>
		<td>Tutti gli <b>utenti</b> che saranno presenti alla <b>consegna</b></td>
		<td><a class="exportDelivery" id="usersDelivery-PDF" style="cursor:pointer;" rel="nofollow" title="stampa gli utenti che saranno presenti alla consegna in formato PDF"><i class="fa fa-file-pdf-o fa-2x"></i></a></td>
		<td><a class="exportDelivery" id="usersDelivery-CSV" style="cursor:pointer;" rel="nofollow" title="stampa gli utenti che saranno presenti alla consegna in formato CSV"><i class="fa fa-file-text-o fa-2x"></i></a></td>
		<td>
			<?php
			if(Configure::read('developer.mode'))
				echo 'No in developer mode';
			else
				echo '<a class="exportDelivery" id="usersDelivery-EXCEL" style="cursor:pointer;" rel="nofollow" title="stampa gli utenti che saranno presenti alla consegna in formato EXCEL"><i class="fa fa-file-excel-o fa-2x"></i></a>';
			?>
		</td>
	</tr>
	<tr>
		<td>
		<a style="cursor:pointer;" data-toggle="collapse" data-target="#trConfigId-Articles" action="Articles" title="<?php echo __('Href_title_expand_config');?>"><i class="fa fa-cogs fa-2x"></i></a></td>
		<td>Tutti gli <b>articoli</b> del produttore</td>
		<td>
			<?php
				echo $this->Form->input('supplier_organization_id',array('label' => false, 'id' => 'supplier_organization_id', 'options' => $suppliersOrganization,
							'title' => 'Scegli per produttore', 'data-live-search' => 'true', 'size' => '1', 'class' => 'selectpicker-report dropup orders_select', 'data-width' => '100%', 'escape' => false));
			?>
		</td>
		<td><a class="exportArticles" id="articlesSupplierOrganization-PDF" style="cursor:pointer;" rel="nofollow" title="stampa gli articoli del produttore in formato PDF"><i class="fa fa-file-pdf-o fa-2x"></i></a></td>
		<td><a class="exportArticles" id="articlesSupplierOrganization-CSV" style="cursor:pointer;" rel="nofollow" title="stampa gli articoli del produttore in formato CSV"><i class="fa fa-file-text-o fa-2x"></i></a></td>
		<td>
			<?php
			if(Configure::read('developer.mode'))
				echo 'No in developer mode';
			else
				echo '<a class="exportArticles" id="articlesSupplierOrganization-EXCEL" style="cursor:pointer;" rel="nofollow" title="stampa gli articoli del produttore in formato EXCEL"><i class="fa fa-file-excel-o fa-2x"></i></a>';
			?>
		</td>
	</tr>
	<tr class="collapse" id="trConfigId-Articles">
		<td></td>
		<td colspan="5" id="tdConfigId-Articles">
			

				<div class="form-group">
					<label class="control-label col-xs-3">Visualizza le tipologie</label>
					<div class="col-xs-2">
						<label class="radio-inline">
							<input type="radio" id="filterTypeY1" name="filterType1" value="Y" checked /> Si
						</label>
					</div>
					<div class="col-xs-2">
						<label class="radio-inline">
							<input type="radio" id="filterTypeN1" name="filterType1" value="N" /> No
						</label>
					</div>
				</div>
				
				<div class="form-group">
					<label class="control-label col-xs-3">Visualizza le categorie</label>
					<div class="col-xs-2">
						<label class="radio-inline">
							<input type="radio" id="filterCategoryY1" name="filterCategory1" value="Y" checked /> Si
						</label>
					</div>
					<div class="col-xs-2">
						<label class="radio-inline">
							<input type="radio" id="filterCategoryN1" name="filterCategory1" value="N" /> No
						</label>
					</div>
				</div>
				
				<div class="form-group">
					<label class="control-label col-xs-3">Visualizza le note</label>
					<div class="col-xs-2">
						<label class="radio-inline">
							<input type="radio" id="filterNotaY1" name="filterNota1" value="Y" checked /> Si
						</label>
					</div>
					<div class="col-xs-2">
						<label class="radio-inline">
							<input type="radio" id="filterNotaN1" name="filterNota1" value="N" /> No
						</label>
					</div>
				</div>
		
				<?php 
				if($user->organization['Organization']['hasFieldArticleIngredienti']=='Y') {
				?>	
						<div class="form-group">
							<label class="control-label col-xs-3">Visualizza gli ingredienti</label>
							<div class="col-xs-2">
								<label class="radio-inline">
									<input type="radio" id="filterIngredientiY1" name="filterIngredienti1" value="Y" checked /> Si
								</label>
							</div>
							<div class="col-xs-2">
								<label class="radio-inline">
									<input type="radio" id="filterIngredientiN1" name="filterIngredienti1" value="N" /> No
								</label>
							</div>
						</div>				
	
				<?php 
				}
				?>
						
		</td>
	</tr>		
	<?php 
        if($user->organization['Organization']['hasDes']=='Y') 
            echo $this->element('reportArticlesDes', array('desOrganizationResults' => $desOrganizationResults, 'type' => 'FE'));
        
        
        
	if(!empty($orders)) {
	?>
	<tr>
		<td>
		<a style="cursor:pointer;" data-toggle="collapse" data-target="#trConfigId-ArticlesOrders" action="ArticlesOrders" title="<?php echo __('Href_title_expand_config');?>"><i class="fa fa-cogs fa-2x"></i></a></td>
		<td>Tutti gli <b>articoli</b> dell'ordine</td>
		<td>
			<?php
				echo $this->Form->input('orders',array('id' => 'order_id', 'label' => false,'options' => $orders, 
														'title' => 'Scegli l\'ordine', 'data-live-search' => 'true', 'size' => '1', 'class' => 'selectpicker-report dropup orders_select', 'data-width' => '100%', 'escape' => false));
			?>
		</td>
		<td><a class="exportArticlesOrders" id="articlesOrders-PDF" style="cursor:pointer;" rel="nofollow" title="stampa gli articoli dell'ordine in formato PDF"><i class="fa fa-file-pdf-o fa-2x"></i></a></td>
		<td><a class="exportArticlesOrders" id="articlesOrders-CSV" style="cursor:pointer;" rel="nofollow" title="stampa gli articoli dell'ordine in formato CSV"><i class="fa fa-file-text-o fa-2x"></i></a></td>
		<td>
			<?php
			if(Configure::read('developer.mode'))
				echo 'No in developer mode';
			else
				echo '<a class="exportArticlesOrders" id="articlesOrders-EXCEL" style="cursor:pointer;" rel="nofollow" title="stampa gli articoli dell\'ordine in formato EXCEL"><i class="fa fa-file-excel-o fa-2x"></i></a>';
			?>
		</td>
	</tr>
	<tr class="collapse" id="trConfigId-ArticlesOrders">
		<td></td>
		<td colspan="5" id="tdConfigId-ArticlesOrders">

				<div class="form-group">
					<label class="control-label col-xs-3">Visualizza le tipologie</label>
					<div class="col-xs-2">
						<label class="radio-inline">
							<input type="radio" id="filterTypeY2" name="filterType2" value="Y" checked /> Si
						</label>
					</div>
					<div class="col-xs-2">
						<label class="radio-inline">
							<input type="radio" id="filterTypeN2" name="filterType2" value="N" /> No
						</label>
					</div>
				</div>
				
				<div class="form-group">
					<label class="control-label col-xs-3">Visualizza le categorie</label>
					<div class="col-xs-2">
						<label class="radio-inline">
							<input type="radio" id="filterCategoryY2" name="filterCategory2" value="Y" checked /> Si
						</label>
					</div>
					<div class="col-xs-2">
						<label class="radio-inline">
							<input type="radio" id="filterCategoryN2" name="filterCategory2" value="N" /> No
						</label>
					</div>
				</div>
				
				<div class="form-group">
					<label class="control-label col-xs-3">Visualizza le note</label>
					<div class="col-xs-2">
						<label class="radio-inline">
							<input type="radio" id="filterNotaY2" name="filterNota2" value="Y" checked /> Si
						</label>
					</div>
					<div class="col-xs-2">
						<label class="radio-inline">
							<input type="radio" id="filterNotaN2" name="filterNota2" value="N" /> No
						</label>
					</div>
				</div>					

	
				<?php 
				if($user->organization['Organization']['hasFieldArticleIngredienti']=='Y') {
				?>
						<div class="form-group">
							<label class="control-label col-xs-3">Visualizza gli ingredienti</label>
							<div class="col-xs-2">
								<label class="radio-inline">
									<input type="radio" id="filterIngredientiY2" name="filterIngredienti2" value="Y" checked /> Si
								</label>
							</div>
							<div class="col-xs-2">
								<label class="radio-inline">
									<input type="radio" id="filterIngredientiN2" name="filterIngredienti2" value="N" /> No
								</label>
							</div>
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
		<td><a class="suppliersOrganizations" id="suppliersOrganizations-PDF" style="cursor:pointer;" rel="nofollow" title="stampa i produttori in formato PDF"><i class="fa fa-file-pdf-o fa-2x"></i></a></td>
		<td></td>
		<td>
			<?php
			if(Configure::read('developer.mode'))
				echo 'No in developer mode';
			else
				echo '<a class="suppliersOrganizations" id="suppliersOrganizations-EXCEL" style="cursor:pointer;" rel="nofollow" title="stampa i produttori in formato EXCEL"><i class="fa fa-file-excel-o fa-2x"></i></a>';
			?>
		</td>		
	</tr>		
	<tr>
		<td><a style="cursor:pointer;" data-toggle="collapse" data-target="#trConfigId-usersData" action="usersData" title="<?php echo __('Href_title_expand_config');?>"><i class="fa fa-cogs fa-2x"></i></a></td>
		<td>Anagrafica di tutti gli <b>utenti</b></td>
		<td></td>
		<td><a class="usersData" id="usersData-PDF" style="cursor:pointer;" rel="nofollow" title="stampa l'anagrafica degli utenti in formato PDF"><i class="fa fa-file-pdf-o fa-2x"></i></a></td>
		<td><a class="usersData" id="usersData-CSV" style="cursor:pointer;" rel="nofollow" title="stampa l'anagrafica degli utenti in formato CSV"><i class="fa fa-file-text-o fa-2x"></i></a></td>
		<td>
			<?php
			if(Configure::read('developer.mode'))
				echo 'No in developer mode';
			else
				echo '<a class="usersData" id="usersData-EXCEL" style="cursor:pointer;" rel="nofollow" title="stampa l\'anagrafica degli utenti in formato EXCEL"><i class="fa fa-file-excel-o fa-2x"></i></a>';
			?>
		</td>		
	</tr>
	<tr class="collapse" id="trConfigId-usersData">
		<td></td>
		<td colspan="5" id="tdConfigId-userData">
			
				<div class="form-group">	
					<?php 
					foreach ($filterUserGroups as $id => $label) {
					?>
						<div class="col-xs-2">
							<label class="checkbox-inline">
								<?php echo '<input type="checkbox" id="filterUserGroups'.$id.'" name="filterUserGroups" value="'.$id.'" checked /> '.$label;?>
							</label>
						</div>
					<?php
					} 
					?>
				</div>			

				<?php
				/*
				echo '<div style="clear:both;float:left;margin-right:10px;">';
				echo '<input type="checkbox" id="filterUsersImg" name="filterUsersImg" value="Y" />';
				echo '<label for="filterUsersImg">';
				echo "Immagine degli utenti";
				echo '</label> ';
				echo '</div>';
				*/		
				?>
						
		</td>
	</tr>
	<tr>
		<td><a style="cursor:pointer;" data-toggle="collapse" data-target="#trConfigId-referentsData" action="referentsData" title="<?php echo __('Href_title_expand_config');?>"><i class="fa fa-cogs fa-2x"></i></a></td>
		<td>Stampa dei <b>referenti</b></td>
		<td></td>
		<td><a class="referentsData" id="referentsData-PDF" style="cursor:pointer;" rel="nofollow" title="stampa dei referenti in formato PDF"><i class="fa fa-file-pdf-o fa-2x"></i></a></td>
		<td><a class="referentsData" id="referentsData-CSV" style="cursor:pointer;" rel="nofollow" title="stampa dei referenti in formato CSV"><i class="fa fa-file-text-o fa-2x"></i></a></td>
		<td>
			<?php
			if(Configure::read('developer.mode'))
				echo 'No in developer mode';
			else
				echo '<a class="referentsData" id="referentsData-EXCEL" style="cursor:pointer;" rel="nofollow" title="stampa dei referenti in formato EXCEL"><i class="fa fa-file-excel-o fa-2x"></i></a>';
			?>
		</td>		
	</tr>
	<tr class="collapse" id="trConfigId-referentsData">
		<td></td>
		<td colspan="5" id="tdConfigId-referentsData">
			
				<div class="form-group">
					<label class="control-label col-xs-3">Opzioni stampa:</label>
					<div class="col-xs-2">
						<label class="radio-inline">
							<input type="radio" id="filterOrderSuppliers" name="filterOrder" value="SUPPLIERS" checked /> Produttore
						</label>
					</div>
					<div class="col-xs-2">
						<label class="radio-inline">
							<input type="radio" id="filterOrderUsers" name="filterOrder" value="USERS" /> Utente
						</label>
					</div>
				</div>			
						
		</td>
	</tr>	
	
	<?php
	if($user->organization['Organization']['hasStoreroom']=='Y') 
		echo $this->element('reportStoreroom', array('type' => 'FE'));
	?>
	
	</tbody>	
	</table>
</div>
</form>




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
		
		window.open('/?option=com_cake&controller=ExportDocs&action='+action+'&delivery_id='+delivery_id+'&doc_formato='+doc_formato+'&format=notmpl','win2','status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=640,height=480,directories=no,location=no');	
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
				
		window.open('/?option=com_cake&controller=ExportDocs&action='+action+'&supplier_organization_id='+supplier_organization_id+'&filterType='+filterType+'&filterCategory='+filterCategory+'&filterNota='+filterNota+'&filterIngredienti='+filterIngredienti+'&doc_formato='+doc_formato+'&format=notmpl','win2','status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=640,height=480,directories=no,location=no');	
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
				
		window.open('/?option=com_cake&controller=ExportDocs&action='+action+'&order_id='+order_id+'&filterType='+filterType+'&filterCategory='+filterCategory+'&filterNota='+filterNota+'&filterIngredienti='+filterIngredienti+'&doc_formato='+doc_formato+'&format=notmpl','win2','status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=640,height=480,directories=no,location=no');
	});

	$('.suppliersOrganizations').click(function() {	
		var id =  $(this).attr('id');
		idArray = id.split('-');
		var action          = idArray[0];
		var doc_formato = idArray[1];

		window.open('/?option=com_cake&controller=ExportDocs&action='+action+'&doc_formato='+doc_formato+'&format=notmpl','win2','status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=640,height=480,directories=no,location=no');		
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

		/* var filterUsersImg = $("input[name='filterUsersImg']:checked").val(); 
		if(filterUsersImg!='Y') */ filterUsersImg = 'N';
		
		window.open('/?option=com_cake&controller=ExportDocs&action='+action+'&userGroupIds='+userGroupIds+'&filterUsersImg='+filterUsersImg+'&doc_formato='+doc_formato+'&format=notmpl','win2','status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=640,height=480,directories=no,location=no');		
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
		
		window.open('/?option=com_cake&controller=ExportDocs&action='+action+'&filterOrder='+filterOrder+'&doc_formato='+doc_formato+'&format=notmpl','win2','status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=640,height=480,directories=no,location=no');		
	});	
	
	$('.exportStoreroom').click(function() {
		var id =  $(this).attr('id');
		idArray = id.split('-');
		var id =  $(this).attr('id');
		var action      = idArray[0];
		var doc_formato = idArray[1];
				
		window.open('/?option=com_cake&controller=Storerooms&action='+action+'&doc_formato='+doc_formato+'&format=notmpl','win2','status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=640,height=480,directories=no,location=no');	
	});
	
	$('.selectpicker-report').selectpicker({
		style: 'selectpicker'
	});
});
</script>