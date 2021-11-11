<div style="width:55%;float:left;">
	<div>
		<select name="prod_delivery_id" id="prod_delivery_id">
			<?php 
				 echo '<option value="'.$results['ProdDelivery']['id'].'">';
				 echo $results['ProdDelivery']['name'].' - dal '.$results['ProdDelivery']['data_inizio_'].' al '.$results['ProdDelivery']['data_fine_'].'</option>';?>
		</select>
	</div>
	
</div>
<div style="clear:none;width:45%;float:left;">
	
	<table cellpadding = "0" cellspacing = "0">
		<tr>
			<th><?php echo __('StateDelivery');?></th>
		  	<th><?php echo __('StatoElaborazione');?></th>
			<?php 
			if($user->organization['Organization']['hasVisibility']=='Y')
				echo '<th>'.__('isVisibleFrontEnd').'</th>';
			?>
		</tr>
		<tr>
			<td style="white-space:nowrap;">
				<?php echo $this->App->utilsCommons->getProdDeliveryTime($results['ProdDelivery']);?>
			</td>
			<td><?php 
					echo $results['ProdDeliveriesState']['label'];
					echo $this->App->drawProdDeliveriesStateDiv($results);
				?>
			</td>
			<?php 
			if($user->organization['Organization']['hasVisibility']=='Y')
				echo '<td title="'.__('toolTipsVisibleFrontEnd').'" class="stato_'.$this->App->traslateEnum($results['ProdDelivery']['isVisibleFrontEnd']).'"></td>';
			?>
		</tr>
	</table>

</div>

<div id="prod_delivery-permission">

	<?php
	// visualizzo il msg solo se non va bene
	$msg_visible=false;
	$msgIni='';
	$msgEnd='';
	
	if($call_action=='admin_managementCartsOne' || $call_action=='admin_managementCartsSplit' ) { 
		$msgIni = "Elaborazione della consegna";
		
		if(!$results['ProdDelivery']['permissionToEditProduttore']) {
			$msgEnd = '<br />Non si potranno modificare i dati.';
			$msg_visible=true;
		}	
		else {
			$msgEnd = "<br />Si pu&ograve; proseguire con la gestione della consegna.";
			$msg_visible=false;
		}
	}
	else
	if($call_action=='admin_produttoreDocsExport') { 
		$msgIni = "Esportazione dell'ordine";
	
	/* 	if(!$isReferentGeneric)
			$msgEnd = "<br />Non sei referente dell'ordine, non si potr&agrave; esportare i dati.";
		else */
		if(!$results['ProdDelivery']['permissionToEditProduttore']) {
			$msgEnd = "<br />L'esportazione della consegna sar&agrave; parziale";
			$msg_visible=true;
		}	
		else {
			$msgEnd = "<br />Si pu&ograve; proseguire con l'esportazione della consegna.";
			$msg_visible=false;
		}
	}
	
		
	$msg = '';
	if($results['ProdDelivery']['prod_delivery_state_id']==Configure::read('OPEN')) {
		$msg .= "<br />La&nbsp;consegna&nbsp;non&nbsp;e&grave;&nbsp;ancora&nbsp;chiuso,&nbsp;";
		
		if($results['ProdDelivery']['dayDiffToDateFine']==0) $msg .= 'chiuderà&nbsp;oggi';
		else {
			$msg .= 'chiuderà&nbsp;tra&nbsp;'.(-1 * $results['ProdDelivery']['dayDiffToDateFine']).'&nbsp;gg,';
			$msg .= '&nbsp;il&nbsp;'.$this->Time->i18nFormat($results['ProdDelivery']['data_fine'],"%A %e %B %Y");
		}
	}
	else
	if($results['ProdDelivery']['prod_delivery_state_id']==Configure::read('CLOSE') || 
	   $results['ProdDelivery']['prod_delivery_state_id']==Configure::read('TO-PAYMENT')) {
		$msg .= "<br />".$results['ProdDeliveriesState']['label'];
		$msgEnd = '';
	}
		
	$msgFinale = $msgIni.$msg.$msgEnd;
	if($msg_visible) 
		echo $this->element('boxMsg',array('class_msg' => 'message nomargin','msg' => $msgFinale));
	?>
	<script type="text/javascript">
	$(document).ready(function() {
		var prod_delivery_id = $("#prod_delivery_id").val();
		if(prod_delivery_id>0)	choiceProdDeliveryPermission();
	});
	</script>

</div>