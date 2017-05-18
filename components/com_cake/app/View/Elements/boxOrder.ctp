<?php 
if($results['Delivery']['sys']=='N')
	$label = $results['Delivery']['luogoData'];
else 
	$label = $results['Delivery']['luogo'];
?>

<div style="width:55%;float:left;">
	<div>
		<select name="delivery_id" id="delivery_id">
			<option value="<?php echo $results['Delivery']['id'];?>"><?php echo $label;?></option>
		</select>
	</div>
	
	<div>
		<select name="order_id" id="order_id">
			<option value="<?php echo $results['Order']['id'];?>"><?php echo $results['SuppliersOrganization']['name'].' - dal '.$results['Order']['data_inizio_'].' al '.$results['Order']['data_fine_'];?></option>
		</select>
	</div>
</div>
<div style="clear:none;width:45%;float:left;">
	
	<table cellpadding = "0" cellspacing = "0">
		<tr>
			<th><?php __('StateOrder');?></th>
		  	<th><?php echo __('stato_elaborazione');?></th>
			<?php 
			if($user->organization['Organization']['hasVisibility']=='Y')
				echo '<th>'.__('isVisibleFrontEnd').'</th>';
			?>
		</tr>
		<tr>
			<td style="white-space:nowrap;">
				<?php echo $this->App->utilsCommons->getOrderTime($results['Order']);?>
			</td>
			<td><?php 
					echo __($results['Order']['state_code'].'-label');
					echo $this->App->drawOrdersStateDiv($results);
				?>
			</td>
			<?php 
			if($user->organization['Organization']['hasVisibility']=='Y')
				echo '<td title="'.__('toolTipsVisibleFrontEnd').'" class="stato_'.$this->App->traslateEnum($results['Order']['isVisibleFrontEnd']).'"></td>';
			?>
		</tr>
	</table>

</div>

<div id="order-permission">

	<?php
	/*
	 * call_action: valore di $this->action
	 * 		admin_managementCartsOne           se chiamato da controller=Carts&action=managementCartsOne
	 * 		admin_managementCartsGroupByUsers  se chiamato da controller=Carts&action=managementCartsGroupByUsers
	 * 		admin_referentDocsExport           se chiamato da controller=Docs&action=referentDocsExport
	 * 		admin_referentDocsExportHistory    se chiamato da controller=Docs&action=referentDocsExportHistory
	 * 		admin_cassiere_docs_export         se chiamato da controller=Docs&action=cassiere_docs_export
	 * 		admin_validationCarts              se chiamato da controller=Carts&action=validationCarts
	 * 		admin_summary_orders              se chiamato da controller=Referente&action=summary_orders
	 * */
	
	// visualizzo il msg solo se non va bene
	$msg_visible=false;
	$msgIni='';
	$msgEnd='';
	
	if($call_action=='admin_managementCartsOne' || $call_action=='admin_managementCartsGroupByUsers'|| $call_action=='admin_managementCartsSplit' || $call_action=='admin_validationCarts' || $call_action=='admin_trasport') { 
		$msgIni = "Elaborazione dell'ordine";
		
		if(!$results['Order']['permissionToEditReferente']) {
			$msgEnd = '<br />Non si potranno modificare i dati.';
			$msg_visible=true;
		}	
		else {
			$msgEnd = "<br />Si pu&ograve; proseguire con la gestione dell'ordine.";
			$msg_visible=false;
		}
	}
	else
	if($call_action=='admin_referentDocsExport' || $call_action=='admin_summary_orders') { 
		$msgIni = "Esportazione dell'ordine";
	
	/* 	if(!$isReferentGeneric)
			$msgEnd = "<br />Non sei referente dell'ordine, non si potr&agrave; esportare i dati.";
		else */
		if(!$results['Order']['permissionToEditReferente']) {
			$msgEnd = "<br />L'esportazione dell'ordine sar&agrave; parziale";
			$msg_visible=true;
		}	
		else {
			$msgEnd = "<br />Si pu&ograve; proseguire con l'esportazione dell'ordine.";
			$msg_visible=false;
		}
	}
	
		
	$msg = '';
	if($results['Order']['state_code']=='OPEN') {
		$msg .= "<br />L'ordine&nbsp;non&nbsp;e&grave;&nbsp;ancora&nbsp;chiuso,&nbsp;";
		
		if($results['Order']['dayDiffToDateFine']==0) $msg .= 'chiuderà&nbsp;oggi';
		else {
			$msg .= 'chiuderà&nbsp;tra&nbsp;'.(-1 * $results['Order']['dayDiffToDateFine']).'&nbsp;gg,';
			$msg .= '&nbsp;il&nbsp;'.$this->Time->i18nFormat($results['Order']['data_fine'],"%A %e %B %Y");
		}
	}
	else
	if($results['Order']['state_code']=='RI-OPEN-VALIDATE') {
		$msg .= "<br />L'ordine&nbsp;e&grave;&nbsp;stato riaperto,&nbsp;";
	
		if($results['Order']['dayDiffToDateFine']==0) $msg .= 'chiuderà&nbsp;oggi';
		else {
			$msg .= 'chiuderà&nbsp;tra&nbsp;'.(-1 * $results['Order']['dayDiffToDateFine']).'&nbsp;gg,';
			$msg .= '&nbsp;il&nbsp;'.$this->Time->i18nFormat($results['Order']['data_fine_validation'],"%A %e %B %Y");
		}
	}
	else		
	if($results['Order']['state_code']=='WAIT-PROCESSED-TESORIERE')
		$msg .= "<br />".__($results['Order']['state_code'].'-label');
	else 
	if($results['Order']['state_code']=='PROCESSED-TESORIERE')
		$msg .= "<br />".__($results['Order']['state_code'].'-label');
	else
	if($results['Order']['state_code']=='CLOSE' || 
	   $results['Order']['state_code']=='TO-PAYMENT') {
		$msg .= "<br />".__($results['Order']['state_code'].'-label');
		$msgEnd = '';
	}
	
	$msgFinale = $msgIni.$msg.$msgEnd;
	if($msg_visible) 
		echo $this->element('boxMsg',array('class_msg' => 'message nomargin','msg' => $msgFinale));
	?>
	<script type="text/javascript">
	jQuery(document).ready(function() {
		var order_id = jQuery("#order_id").val();
		if(order_id>0)	choiceOrderPermission();
	});
	</script>

</div>