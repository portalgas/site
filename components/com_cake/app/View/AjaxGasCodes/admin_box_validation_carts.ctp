<?php
echo '<div class="carts">';

if(empty($results)) 
	echo $this->element('boxMsg', array('class_msg' => 'success', 'msg' => __('OrderToValidateOk')));
else {
?>
	<table cellpadding="0" cellspacing="0">
		<tr>
			<th></th>
			<th><?php echo __('N');?></th>
			<th></th>
			<th><?php echo __('Name');?></th>
			<th><?php echo __('PrezzoUnita');?></th>
			<th><?php echo __('pezzi_confezione_short');?></th>
			<th>Colli<br />completati</th>
			<th>Quantità<br />ordinata</th>
			<th>
				<span style="float:left;">Differenza<br />da ordinare</span>
				<span style="float:right;"><?php echo $this->Tabs->drawTooltip('Importo forzato',__('toolTipQtaDifferenza'),$type='WARNING',$pos='LEFT');?></span>
			</th>
			<th>Importo<br />da pagare</th>
			<?php 
			if($order['Order']['state_code']=='PROCESSED-BEFORE-DELIVERY' ||
			   $order['Order']['state_code']=='PROCESSED-POST-DELIVERY') {
				  
				if($isStoreroom) { 
				 	echo '<th>';
					echo '<input type="checkbox" checked id="article_order_id_selected_all" name="article_order_id_selected_all" value="ALL" />';
					echo '</th>';
				}
				echo '<th>';
				echo '</th>';
			}
			?>
		</tr>
		<?php 	
		$i=0;
		foreach($results as $numResult => $result) {
		
			/*
			 * colli_completi / differenza_da_ordinare
			 */
			$colli_completi = intval($result['ArticlesOrder']['qta_cart'] / $result['ArticlesOrder']['pezzi_confezione']);
			if($colli_completi>0)
				$differenza_da_ordinare = (($result['ArticlesOrder']['pezzi_confezione'] * ($colli_completi +1)) - $result['ArticlesOrder']['qta_cart']);
			else {
				$differenza_da_ordinare = ($result['ArticlesOrder']['pezzi_confezione'] - $result['Articlesresult']['qta_cart']);
				$colli_completi = '-';
			}
		?>
				<tr>
					<td><a action="articles_order_carts-<?php echo $result['ArticlesOrder']['order_id']; ?>_<?php echo $result['ArticlesOrder']['article_organization_id']; ?>_<?php echo $result['ArticlesOrder']['article_id']; ?>" class="actionTrView openTrView" href="#" title="<?php echo __('Href_title_expand');?>"></a></td>
					<td><?php echo ($i+1);?></td>
					<td>
					<?php 
					if(!empty($result['Article']['img1']) && file_exists(Configure::read('App.root').Configure::read('App.img.upload.article').DS.$result['Article']['organization_id'].DS.$result['Article']['img1'])) {
						echo '<img width="50" class="userAvatar" src="'.Configure::read('App.server').Configure::read('App.web.img.upload.article').'/'.$result['Article']['organization_id'].'/'.$result['Article']['img1'].'" />';			
					}		
					?>	
					</td>					
					<td><?php echo $result['ArticlesOrder']['name'];?></td>
					<td style="text-align:center;"><?php echo $result['ArticlesOrder']['prezzo_e'];?></td>
					<td style="text-align:center;"><?php echo $result['ArticlesOrder']['pezzi_confezione'];?></td>
					<td style="text-align:center;"><?php echo $colli_completi;?></td>
					<td style="text-align:center;"><?php echo $result['ArticlesOrder']['qta_cart'];?></td>
					<td style="text-align:center;">
						<div class="valoreEvidenzia"><?php echo $result['ArticlesOrder']['differenza_da_ordinare'];?></div></td>
					<td style="text-align:center;"><?php echo number_format($result['ArticlesOrder']['differenza_importo'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));?>&nbsp;&euro;</td>
					<?php 
					if($order['Order']['state_code']=='PROCESSED-BEFORE-DELIVERY' ||
					   $order['Order']['state_code']=='PROCESSED-POST-DELIVERY') {						
						
						if($isStoreroom) { 
						 	echo '<td>';
							echo '<input type="checkbox" checked id="'.$result['ArticlesOrder']['order_id'].'_'.$result['ArticlesOrder']['article_organization_id'].'_'.$result['ArticlesOrder']['article_id'].'[article_order_id_selected]" name="article_order_id_selected" value="'.$result['ArticlesOrder']['order_id'].'_'.$result['ArticlesOrder']['article_organization_id'].'_'.$result['ArticlesOrder']['article_id'].'" />';
							echo '</td>';
						}
						echo '<td>';
						echo $this->Html->link(null, array('controller' => 'Carts', 'action' => 'validation_carts_edit', null, 'delivery_id='.$delivery_id, 'order_id='.$order_id, 'article_organization_id='.$result['ArticlesOrder']['article_organization_id'], 'article_id='.$result['ArticlesOrder']['article_id']),array('class' => 'action actionEdit','title' => __('Edit')));
						echo '</td>';
					}
					?>
				</tr>
				<tr class="trView" id="trViewId-<?php echo $result['ArticlesOrder']['order_id'];?>_<?php echo $result['ArticlesOrder']['article_organization_id'];?>_<?php echo $result['ArticlesOrder']['article_id'];?>">
					<td colspan="2"></td>
					<td colspan="10" id="tdViewId-<?php echo $result['ArticlesOrder']['order_id'];?>_<?php echo $result['ArticlesOrder']['article_organization_id'];?>_<?php echo $result['ArticlesOrder']['article_id'];?>"></td>
				</tr>			
				<?php 
				echo $this->Form->hidden('differenza_da_ordinare',array('name' => 'data[Cart]['.$result['ArticlesOrder']['order_id'].'_'.$result['ArticlesOrder']['article_organization_id'].'_'.$result['ArticlesOrder']['article_id'].'][differenza_da_ordinare]','value' => $result['ArticlesOrder']['differenza_da_ordinare']));
		
			$i++;
		} // foreach($newResults['ArticlesOrder'] as $numResult => $result) 
	
echo '</table>';	 
echo $this->Form->hidden('article_order_id_selected',array('id' =>'article_order_id_selected', 'value'=>''));

if($isStoreroom && (
   $order['Order']['state_code']=='PROCESSED-BEFORE-DELIVERY' ||
   $order['Order']['state_code']=='PROCESSED-POST-DELIVERY'))
	echo $this->Form->submit(__('ValidationCartToStoreroom'),array('id' => 'action_post_storeroom', 'div'=> 'submitMultiple'));

if($order['Order']['state_code']=='PROCESSED-BEFORE-DELIVERY') {
	if($order['Order']['data_fine_validation']!=Configure::read('DB.field.date.empty'))	
		$label = 'Riaperto fino a '.$this->Time->i18nFormat($order['Order']['data_fine_validation'],"%A %e %B %Y").' - '.__('JustValidationCartRiOpen');
	else 
		$label = __('ValidationCartRiOpen');
	echo $this->Form->submit($label,array('id' => 'action_post_riopen', 'div'=> 'submitMultiple','class' => 'buttonBlu'));
}
}  // if(empty($results)) 

echo '</div>';
?>

<script type="text/javascript">
jQuery(document).ready(function() {

	var submitStoreroom = false;
	
	jQuery('#article_order_id_selected_all').click(function () {
		var checked = jQuery("input[name='article_order_id_selected_all']:checked").val();
		if(checked=='ALL')
			jQuery('input[name=article_order_id_selected]').prop('checked',true);
		else
			jQuery('input[name=article_order_id_selected]').prop('checked',false);
	});

	jQuery('#action_post_riopen').click(function() {
		var action = jQuery('#formGas').attr('action');
		action = action.substring(0,action.indexOf('controller'))+'controller=Orders&action=edit_validation_cart';

		jQuery('#formGas').attr('action',action);
	});
	jQuery('#action_post_storeroom').click(function() {	
		var action = jQuery('#formGas').attr('action');
		action = action.substring(0,action.indexOf('todefined'))+'validationCarts';

		jQuery('#formGas').attr('action',action);
		submitStoreroom=true;
	});

	jQuery('#formGas').submit(function() {

		if(submitStoreroom) {
			var article_order_id_selected = '';
			for(i = 0; i < jQuery("input[name='article_order_id_selected']:checked").length; i++) {
				article_order_id_selected += jQuery("input[name='article_order_id_selected']:checked").eq(i).val()+',';
			}
	
			if(article_order_id_selected=='') {
				alert("Devi scegliere almeno un articolo da associare alla dispensa");
				return false;
			}	    
			article_order_id_selected = article_order_id_selected.substring(0,article_order_id_selected.length-1);
			
			jQuery('#article_order_id_selected').val(article_order_id_selected);
		}
		
		return true;
	});
});
</script>