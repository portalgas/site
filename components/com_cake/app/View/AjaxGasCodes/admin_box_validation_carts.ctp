<?php
echo '<div class="carts">';

if(empty($results)) 
	echo $this->element('boxMsg', array('class_msg' => 'success', 'msg' => __('OrderToValidateOk')));
else {

	echo '<div class="table-responsive"><table class="table table-hover">';
	echo '<tr>';
	echo '<th></th>';
	echo '<th>'.__('N').'</th>';
	echo '<th></th>';
	echo '<th>'.__('Name').'</th>';
	echo '<th>'.__('PrezzoUnita').'</th>';
	echo '<th>'.__('pezzi_confezione_short').'</th>';
	echo '<th>Colli<br />completati</th>';
	echo '<th>Quantità<br />ordinata</th>';
	echo '<th>';
	echo '	<span style="float:left;">Differenza<br />da ordinare</span>';
	echo '	<span style="float:right;">'.$this->Tabs->drawTooltip('Importo forzato',__('toolTipQtaDifferenza'),$type='WARNING',$pos='LEFT').'</span>';
	echo '</th>';
	echo '<th>Importo<br />da pagare</th>';
	if($isStoreroom) { 
		echo '<th>';
		echo '<input type="checkbox" checked id="article_order_id_selected_all" name="article_order_id_selected_all" value="ALL" class="form-control" />';
		echo '</th>';
	}
	echo '<th>';
	echo '</th>';
	echo '</tr>';

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
		
		echo '<tr>';
		echo '	<td><a action="articles_order_carts-'.$result['ArticlesOrder']['order_id'].'_'.$result['ArticlesOrder']['article_organization_id'].'_'.$result['ArticlesOrder']['article_id'].'" class="actionTrView openTrView" href="#" title="'.__('Href_title_expand').'"></a></td>';
		echo '	<td>'.($i+1).'</td>';
		echo ' <td>';
		if(!empty($result['Article']['img1']) && file_exists(Configure::read('App.root').Configure::read('App.img.upload.article').DS.$result['Article']['organization_id'].DS.$result['Article']['img1'])) {
			echo '<img width="50" class="img-responsive-disabled userAvatar" src="'.Configure::read('App.server').Configure::read('App.web.img.upload.article').'/'.$result['Article']['organization_id'].'/'.$result['Article']['img1'].'" />';			
		}		
		echo '</td>';
		echo '<td>'.$result['ArticlesOrder']['name'].'</td>';
		echo '<td style="text-align:center;">'.$result['ArticlesOrder']['prezzo_e'].'</td>';
		echo '<td style="text-align:center;">'.$result['ArticlesOrder']['pezzi_confezione'].'</td>';
		echo '<td style="text-align:center;">'.$colli_completi.'</td>';
		echo '<td style="text-align:center;">'.$result['ArticlesOrder']['qta_cart'].'</td>';
		echo '<td style="text-align:center;">';
		echo '<div class="valoreEvidenzia">'.$result['ArticlesOrder']['differenza_da_ordinare'].'</div></td>';
		echo '<td style="text-align:center;">'.number_format($result['ArticlesOrder']['differenza_importo'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'&nbsp;&euro;</td>';

		if($isStoreroom) {
			echo '<td>';
			echo '<input type="checkbox" checked id="'.$result['ArticlesOrder']['order_id'].'_'.$result['ArticlesOrder']['article_organization_id'].'_'.$result['ArticlesOrder']['article_id'].'[article_order_id_selected]" name="article_order_id_selected" value="'.$result['ArticlesOrder']['order_id'].'_'.$result['ArticlesOrder']['article_organization_id'].'_'.$result['ArticlesOrder']['article_id'].'" class="form-control" />';
			echo '</td>';
		}
		echo '<td>';
		echo $this->Html->link(null, array('controller' => 'Carts', 'action' => 'validation_carts_edit', null, 'delivery_id='.$delivery_id, 'order_id='.$order_id, 'article_organization_id='.$result['ArticlesOrder']['article_organization_id'], 'article_id='.$result['ArticlesOrder']['article_id']),array('class' => 'action actionEdit','title' => __('Edit')));
		echo '</td>';

		echo '</tr>';
		echo '<tr class="trView" id="trViewId-'.$result['ArticlesOrder']['order_id'].'_'.$result['ArticlesOrder']['article_organization_id'].'_'.$result['ArticlesOrder']['article_id'].'">';
		echo '<td colspan="2"></td>';
		echo '<td colspan="10" id="tdViewId-'.$result['ArticlesOrder']['order_id'].'_'.$result['ArticlesOrder']['article_organization_id'].'_'.$result['ArticlesOrder']['article_id'].'"></td>';
		echo '</tr>';
		echo $this->Form->hidden('differenza_da_ordinare',array('name' => 'data[Cart]['.$result['ArticlesOrder']['order_id'].'_'.$result['ArticlesOrder']['article_organization_id'].'_'.$result['ArticlesOrder']['article_id'].'][differenza_da_ordinare]','value' => $result['ArticlesOrder']['differenza_da_ordinare']));
		
		$i++;
	} // foreach($results as $numResult => $result)
	
	echo '</table></div>';
	 
	echo $this->Form->hidden('article_order_id_selected',array('id' =>'article_order_id_selected', 'value'=>''));
	
	if($isStoreroom)
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
$(document).ready(function() {

	var submitStoreroom = false;
	
	$('#article_order_id_selected_all').click(function () {
		var checked = $("input[name='article_order_id_selected_all']:checked").val();
		if(checked=='ALL')
			$('input[name=article_order_id_selected]').prop('checked',true);
		else
			$('input[name=article_order_id_selected]').prop('checked',false);
	});

	$('#action_post_riopen').click(function() {
		var action = $('#formGas').attr('action');
		action = action.substring(0,action.indexOf('controller'))+'controller=Orders&action=edit_validation_cart';

		$('#formGas').attr('action',action);
	});
	$('#action_post_storeroom').click(function() {	
		var action = $('#formGas').attr('action');
		action = action.substring(0,action.indexOf('todefined'))+'validationCarts';

		$('#formGas').attr('action',action);
		submitStoreroom=true;
	});

	$('#formGas').submit(function() {

		if(submitStoreroom) {
			var article_order_id_selected = '';
			for(i = 0; i < $("input[name='article_order_id_selected']:checked").length; i++) {
				article_order_id_selected += $("input[name='article_order_id_selected']:checked").eq(i).val()+',';
			}
	
			if(article_order_id_selected=='') {
				alert("Devi scegliere almeno un articolo da associare alla dispensa");
				return false;
			}	    
			article_order_id_selected = article_order_id_selected.substring(0,article_order_id_selected.length-1);
			
			$('#article_order_id_selected').val(article_order_id_selected);
		}
		
		return true;
	});
});
</script>