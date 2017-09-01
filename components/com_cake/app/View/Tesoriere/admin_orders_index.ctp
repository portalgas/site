<?php 
echo '<label for="order_id">Ordini</label>';
echo '<div>';

$tot_order_checked=0;
if (!empty($results['Order'])):

	/*
	 * legenda con gli Order.state_code
	 */
	echo '<div style="float:right;">';
	echo '	<a id="legendaOrderState" href="#" title="'.__('Href_title_expand').'"><img src="/images/cake/actions/32x32/viewmag+.png" /> Visualizza/Nascondi gli ordini</a>';
	echo '<div id="legendaOrderStateContent" class="legenda">';
	echo '<div id="box-account-close"></div>';
	foreach ($orderStateResults as $orderStateResult) {
	
		echo '<div>';
		echo '<span class="action orderStato'.$orderStateResult['Order']['state_code'].'" title="'.__($orderStateResult['Order']['state_code'].'-intro').'"></span>';
		echo '&nbsp;';
		echo '<input style="clear: none;float: none;" type="checkbox" name="order_state_selected" value="'.$orderStateResult['Order']['state_code'].'" checked="checked" />';
		echo '&nbsp;';		
		echo __($orderStateResult['Order']['state_code'].'-label');
		echo '</div>';
	}
	echo '</div>';
	echo '</div>';
?>
<style>
#legendaOrderStateContent {
	display:none;
	z-index:15;
	width:350px;
	position:fixed;
	right:50px;
	background-color: #fff;
}
#box-account-close {
   background: url("/images/cake/close-popup-red.png") no-repeat scroll 0 0 rgba(0, 0, 0, 0);
    cursor: pointer;
    float: right;
    height: 32px;
    position: absolute;
    right: -15px;
    top: -15px;
    width: 32px;	
}
</style>
	<table cellpadding = "0" cellspacing = "0">
	<tr>
		<th></th>
		<th>
			<input type="checkbox" id="order_id_selected_all" name="order_id_selected_all" value="ALL" />
		</th>
		<th><?php echo __('stato_elaborazione'); ?></th>
		<th colspan="2"><?php echo __('Supplier'); ?></th>
		<th>
			<?php echo __('Data inizio');?><br />
			<?php echo __('Data fine');?>
		</th>
		<th>Aperto<br />Chiuso</th>
		<?php 
		if($user->organization['Organization']['hasVisibility']=='Y')
			echo '<th>'.__('isVisibleFrontEnd').'</th>';		
		?>
		<th><?php echo __('Referenti'); ?></th>
		<th colspan="2"><?php echo __('Fattura'); ?></th>
		<th style="width:10px"></th>
	</tr>
	<?php
		foreach ($results['Order'] as $numResult => $order):
		
		// echo '<br />'.$order['state_code'].' '.$order_state_code_checked;
		
		if($order['state_code']==$order_state_code_checked) 
			echo '<tr class="OrderState'.$order['state_code'].'" style="background-color:yellow">';
		else
			echo '<tr class="OrderState'.$order['state_code'].'">';
			
			echo '<td rowspan="2">';
			echo '<a action="tesoriere_export_docs-'.$order['id'].'" class="actionTrView openTrView" href="#" title="'.__('Href_title_expand').'"></a>';
			echo '</td>';
			// echo '<td rowspan="2">'.($numResult+1).'</td>';
			echo '<td rowspan="2">';
			if($order['state_code']==$order_state_code_checked) {
				$tot_order_checked++;
				echo '<input type="checkbox" name="order_id_selected" value="'.$order['id'].'" />';
			}	
			echo '</td>';
			echo '<td rowspan="2">';
			echo __($order['state_code'].'-label');
			echo '&nbsp;';
			echo $this->App->drawOrdersStateDiv($order);
				?>
			</td>
			<td rowspan="2">
			<?php 
			if(!empty($order['Supplier']['img1']) && file_exists(Configure::read('App.root').Configure::read('App.img.upload.content').'/'.$order['Supplier']['img1']))
				echo ' <img width="50" class="userAvatar" src="'.Configure::read('App.web.img.upload.content').'/'.$order['Supplier']['img1'].'" alt="'.$order['SupplierOrganization']['name'].'" /> ';	
			?></td>
			<td rowspan="2"><?php echo $order['SuppliersOrganization']['name']; ?></td>
			<td rowspan="2" style="white-space:nowrap;">
				<?php echo $this->Time->i18nFormat($order['data_inizio'], "%e %b %Y"); ?><br />
				<?php echo $this->Time->i18nFormat($order['data_fine'], "%e %b %Y"); ?>
			</td>
			<?php
			echo '<td rowspan="2" style="white-space:nowrap;">';
			echo $this->App->utilsCommons->getOrderTime($order);
			echo '</td>';
			if($user->organization['Organization']['hasVisibility']=='Y')
				echo '<td rowspan="2" title="'.__('toolTipsVisibleFrontEnd').'" class="stato_'.$this->App->traslateEnum($order['Order']['isVisibleFrontEnd']).'"></td>';
			echo '<td rowspan="2">';
			if(isset($order['SuppliersOrganizationsReferent'])) // deve sempre esistere!
				echo $this->App->drawListSuppliersOrganizationsReferents($user, $order['SuppliersOrganizationsReferent']);
			else 
				echo "Nessun referente associato!";
			?>
			</td>
			<?php 
			echo '<td>';
			if(!empty($order['tesoriere_doc1']) && file_exists(Configure::read('App.root').Configure::read('App.doc.upload.tesoriere').DS.$user->organization['Organization']['id'].DS.$order['tesoriere_doc1'])) {
				$ico = $this->App->drawDocumentIco($order['tesoriere_doc1']);
				echo '<a alt="Scarica il documento" title="Scarica il documento" href="'.Configure::read('App.server').Configure::read('App.web.doc.upload.tesoriere').'/'.$user->organization['Organization']['id'].'/'.$order['tesoriere_doc1'].'" target="_blank"><img src="'.$ico.'" /></a>';
			}
			else
				echo "";

			echo '</td>';
			
			echo '<td>';
			if(!empty($order['tesoriere_nota'])) {
						
				echo '<button type="button" class="btn btn-info" data-toggle="modal" data-target="#order_nota_'.$order['id'].'"><i class="fa fa-2x fa-info-circle" aria-hidden="true"></i></button>';
				echo '<div id="order_nota_'.$order['id'].'" class="modal fade" role="dialog">';
				echo '<div class="modal-dialog">';
				echo '<div class="modal-content">';
				echo '<div class="modal-header">';
				echo '<button type="button" class="close" data-dismiss="modal">&times;</button>';
				echo '<h4 class="modal-title">Nota del referente</h4>';
				echo '</div>';
				echo '<div class="modal-body"><p>'.$order['tesoriere_nota'].'</p>';
				echo '</div>';
				echo '<div class="modal-footer">';
				echo '<button type="button" class="btn btn-primary" data-dismiss="modal">'.__('Close').'</button>';
				echo '</div>';
				echo '</div>';
				echo '</div>';
				echo '</div>';
					
			} // end if(!empty($order['tesoriere_nota']))
			echo '</td>';
			
			echo '<td rowspan="2" style="background-color:';
			if($order['tot_importo']=='0.00') echo '#fff';
			else
			if($order['tot_importo']==$order['tesoriere_fattura_importo']) echo '#fff';
			else 
			if($order['tot_importo']<$order['tesoriere_fattura_importo']) echo 'red';
			else 
			if($order['tot_importo']>$order['tesoriere_fattura_importo']) echo '#006600';
				
			echo '"></td>';
		echo '</tr>';
		
		if($order['state_code']==$order_state_code_checked)
			echo '<tr class="OrderState'.$order['state_code'].'" style="background-color:yellow">';
		else
			echo '<tr class="OrderState'.$order['state_code'].'">';
		
		echo '<td colspan="2" style="text-align="center;">';
		if($order['tesoriere_fattura_importo']>0)
			echo '<b>'.__('Tesoriere fattura importo Short').'</b> '.number_format($order['tesoriere_fattura_importo'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'&nbsp;&euro;';
		if($order['tot_importo']>0)
			echo '<br /><b>'.__('Importo totale ordine Short').'</b> '.number_format($order['tot_importo'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'&nbsp;&euro;';
		echo '</td></tr>';
		
		echo '<tr class="trView" id="trViewId-'.$order['id'].'">';
		echo '	<td colspan="2"></td>'; 
		echo '	<td colspan="9" id="tdViewId-'.$order['id'].'"></td>';
		echo '</tr>';
	endforeach;

	echo '</table>';

else: 
	echo $this->element('boxMsg',array('class_msg' => 'message', 'msg' => "Non ci sono ordini associati"));
endif; 

echo $this->element('menuTesoriereLaterale');
?>
<script type="text/javascript">
$(document).ready(function() {

	$('.actionTrView').css('display','inline-block');  /* rendo visibile il tasto espandi per i dettagli ajax */
	
	$('.actionTrView').each(function () {
		actionTrView(this);
	});

	$('.tesoriere_nota').click(function() {
		var id = $(this).attr('id');
		$("#dialog-msg-"+id ).dialog("open");
	});
	
	<?php if($tot_order_checked>0) { ?>
		$('.submit').css('display','block');
	<?php
	} else { ?>
		$('.submit').css('display','none');
	<?php
	}
	?>	

	if(typeof bindLegenda == 'function') bindLegenda();
	
	$('#order_id_selected_all').click(function () {
		var checked = $("input[name='order_id_selected_all']:checked").val();
		if(checked=='ALL')
			$('input[name=order_id_selected]').prop('checked',true);
		else
			$('input[name=order_id_selected]').prop('checked',false);
	});

	$('#box-account-close').click(function() {
		if($('#legendaOrderStateContent').css('display')=='block')  {
			$('#legendaOrderStateContent').hide();
		}
		else 
			$('#legendaOrderStateContent').show();

		return false;
	});

	$('#legendaOrderState').click(function() {
		if($('#legendaOrderStateContent').css('display')=='block')  {
			$('#legendaOrderStateContent').hide();
		}
		else 
			$('#legendaOrderStateContent').show();

		return false;
	});
	
	$("input[name='order_state_selected']").click(function() {
		var order_state = $(this).val();
		if($(this).is(':checked')) 
			$('.OrderState'+order_state).css('display','table-row');
		else
			$('.OrderState'+order_state).css('display','none');
	});
});
</script>
</div>