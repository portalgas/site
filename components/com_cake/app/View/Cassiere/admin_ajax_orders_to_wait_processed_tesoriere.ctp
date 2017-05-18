<?php 
echo '<label for="order_id">Ordini</label>';
echo '<div>';

if (!empty($results['Order'])):
?>
	<table cellpadding = "0" cellspacing = "0">
	<tr>
		<th></th>
		<th colspan="2"><?php echo __('Supplier'); ?></th>
		<th>
			<?php echo __('Data inizio');?><br />
			<?php echo __('Data fine');?>
		</th>
		<th><?php echo __('Referenti'); ?></th>
		<th colspan="3"><?php echo __('Fattura'); ?></th>
		<th style="width:10px"></th>
		<th style="text-align:center">Utenti da passare<br />al tesoriere</th>
		<th><?php echo __('Actions'); ?></th>
	</tr>
	<?php
		foreach ($results['Order'] as $numResult => $order):

			if($order['totUserToTesoriere']>0) {
			
				echo '<tr>';
				
				echo '<td>';
				echo '<a action="cassiere_orders-'.$order['id'].'" class="actionTrView openTrView" href="#" title="'.__('Href_title_expand').'"></a>';
				echo '</td>';
				
				echo '<td>'; 
				if(!empty($order['Supplier']['img1']) && file_exists(Configure::read('App.root').Configure::read('App.img.upload.content').'/'.$order['Supplier']['img1']))
					echo ' <img width="50" class="userAvatar" src="'.Configure::read('App.web.img.upload.content').'/'.$order['Supplier']['img1'].'" alt="'.$order['SupplierOrganization']['name'].'" /> ';	
				echo '</td>';
				?>
				<td><?php echo $order['SuppliersOrganization']['name']; ?></td>
				<td style="white-space:nowrap;">
					<?php echo $this->Time->i18nFormat($order['data_inizio'], "%e %b %Y"); ?><br />
					<?php echo $this->Time->i18nFormat($order['data_fine'], "%e %b %Y"); ?>
				</td>
				<?php
				echo '<td>';
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
					
					echo '<img style="cursor:pointer;" class="tesoriere_nota" id="'.$order['id'].'" src="'.Configure::read('App.img.cake').'/icon-28-info.png" title="Leggi il messaggio del referente" border="0" />';
					
					echo '<div id="dialog-msg-'.$order['id'].'" title="Messaggio del referente">';
					echo '<p>';
					echo $order['tesoriere_nota'];
					echo '</p>';
					echo '</div>';
					
					echo '<script type="text/javascript">';
					echo 'jQuery("#dialog-msg-'.$order['id'].'" ).dialog({';
					echo "\r\n";
					echo '	autoOpen: false,';
					echo "\r\n";
					echo '	height: 450,';
					echo "\r\n";
					echo '	width: 600,';
					echo "\r\n";
					echo '	modal: true,';
					echo "\r\n";
					echo '	buttons: {';
					echo "\r\n";
					echo '		"Chiudi": function() {';
					echo "\r\n";
					echo '			jQuery( this ).dialog( "close" );';
					echo "\r\n";
					echo '		},';
					echo "\r\n";
					echo '	}';
					echo '});';
					echo '</script>';
						
				} // end if(!empty($order['tesoriere_nota']))
				echo '</td>';
				echo '<td style="text-align="center;">';
				if($order['tesoriere_fattura_importo']>0)
					echo '<b>'.__('Tesoriere fattura importo Short').'</b> '.number_format($order['tesoriere_fattura_importo'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).' &euro;';
				if($order['tot_importo']>0)
					echo '<br /><b>'.__('Importo totale ordine Short').'</b> '.number_format($order['tot_importo'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).' &euro;';
				echo '</td>';
				echo '<td style="background-color:';
				if($order['tot_importo']=='0.00') echo '#fff';
				else
				if($order['tot_importo']==$order['tesoriere_fattura_importo']) echo '#fff';
				else 
				if($order['tot_importo']<$order['tesoriere_fattura_importo']) echo 'red';
				else 
				if($order['tot_importo']>$order['tesoriere_fattura_importo']) echo '#006600';
					
				echo '"></td>';
				
				echo '<td style="text-align:center">'.$order['totUserToTesoriere'].'</td>';
				
				echo '<td class="actions-table-img-4">';
				echo $this->Html->link(null, array('action' => 'order_state_in_WAIT_PROCESSED_TESORIERE', null, 'delivery_id='.$order['delivery_id'].'&order_id='.$order['id']),array('class' => 'action actionFromRefToTes','title' => __('OrdersReferenteInWaitProcessedTesoriere'))); 
				echo '</td>';
			echo '</tr>';
			
			echo '<tr>';
					
			echo '<tr class="trView" id="trViewId-'.$order['id'].'">';
			echo '	<td colspan="2"></td>'; 
			echo '	<td colspan="9" id="tdViewId-'.$order['id'].'"></td>';
			echo '</tr>';
			
		} // end if($order['totUserToTesoriere']>0) 			
	endforeach;

	echo '</table>';

else: 
	echo $this->element('boxMsg',array('class_msg' => 'message', 'msg' => "Non ci sono ordini associati"));
endif; 
?>

<script type="text/javascript">
jQuery(document).ready(function() {

	jQuery('.actionTrView').css('display','inline-block');  /* rendo visibile il tasto espandi per i dettagli ajax */
	
	jQuery('.actionTrView').each(function () {
		actionTrView(this);
	});

	jQuery('.tesoriere_nota').click(function() {
		var id = jQuery(this).attr('id');
		jQuery("#dialog-msg-"+id ).dialog("open");
	});	
});
</script>

</div>