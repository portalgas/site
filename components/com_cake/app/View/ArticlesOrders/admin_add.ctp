<?php
$this->App->d($results, false);

echo $this->Html->script('moduleCtrlArticlesOrders.min');
$this->Html->addCrumb(__('Home'), ['controller' => 'Pages', 'action' => 'home']);
$this->Html->addCrumb(__('List Orders'),array('controller' => 'Orders', 'action' => 'index'));
$this->Html->addCrumb(__('Order home'),array('controller'=>'Orders','action'=>'home', $order['Order']['id']));
if(empty($des_order_id)) 
	$this->Html->addCrumb(__('Add ArticlesOrder'));
else
	$this->Html->addCrumb(__('Add ArticlesOrder DES'));
echo $this->Html->getCrumbList(array('class'=>'crumbs'));

if($user->organization['Organization']['hasFieldArticleCodice']=='Y' && $user->organization['Organization']['hasFieldArticleAlertToQta']=='Y')
	$colspan = '12';
else
if($user->organization['Organization']['hasFieldArticleCodice']=='N' && $user->organization['Organization']['hasFieldArticleAlertToQta']=='N')
	$colspan = '10';
else
	$colspan = '11';
	
echo '<div class="articles">';

if(!empty($des_order_id))
	echo $this->element('boxDesOrder', array('results' => $desOrdersResults));	

echo $this->element('boxOrder', ['results' => $order]);


echo $this->Form->create('ArticlesOrder', ['id' => 'formGas']);
echo $this->Form->hidden('article_id_selected', ['id' => 'article_id_selected', 'value' => '']);
echo '<fieldset>';
echo '<legend></legend>';
	
/*
 *  articoli dell'ordine precedente
 */
if($previousResults)
	echo '<div class="submit" style="float:left;"><input type="submit" id="action_articles_orders_previuos" class="buttonBlu" value="Associa all\'ordine gli articoli dell\'ordine precedente" /></div>';

echo '<h2 style="clear: both;">';
if(empty($des_order_id)) 
	echo "Elenco degli articoli attivi da associare all'ordine";
else
	echo "Elenco degli articoli attivi da associare all'ordine condiviso";
if(count($results)>10) 		
	echo '<div class="submit" style="float:right;"><input type="submit" id="action_articles_orders_current1" value="Associa all\'ordine gli articoli selezionati" /></div>';
echo '</h2>';

		
/*
 * filtro search / sort
 */
if(!empty($sorts)) {
	echo '<div class="clearfix">';
	echo '<div class="row">';
	echo '<div class="col col-md-6">';
	echo $this->Form->input('filter_name', ['id' => 'filter_name', 'label' => __('FilterArticlesOrdersName'), 'value' => $filter_name]);	 
	echo $this->Form->hidden('delivery_id',['id' => 'delivery_id', 'value' => $delivery_id]);	 
	echo $this->Form->hidden('order_id',['id' => 'order_id', 'value' => $order_id]);	 
	echo $this->Form->hidden('des_order_id',['id' => 'des_order_id', 'value' => $des_order_id]);
	echo '</div>';	
	echo '<div class="col col-md-6">';
	echo $this->Form->input('sort', ['id' => 'sort', 'options' => $sorts, 'default' => $sort]);	 	
	echo '</div>';	
	echo '</div>';	// row
	echo '</div>';	
} // if(!empty($sorts))

if(count($results)>0) { 
		
	echo '<div class="articlesOrders">';
	
	echo '<div class="table-responsive"><table class="table table-hover">';
	echo '<tr>';
	echo '<th></th>';
	echo '<th>'.__('N').'</th>';
	echo '<th><input type="checkbox" id="article_id_selected_all" name="article_id_selected_all" value="ALL" /></th>';
	if($user->organization['Organization']['hasFieldArticleCodice']=='Y')
	echo '<th>'.__('Codice').'</th>';
	echo '<th>Nome prodotto</th>';
	echo '<th></th>';
	echo '<th>'.__('Prezzo').'</th>';
	echo '<th style="padding-left:15px">'.__('pezzi_confezione').'</th>';
	echo '<th>'.__('qta_minima_short').'</th>';
	echo '<th>'.__('qta_massima_short').'</th>';
	echo '<th>'.__('qta_multipli').'</th>';
	echo '<th>'.__('qta_minima_order_short').'</th>';
	echo '<th>'.__('qta_massima_order_short').'</th>';
	if($user->organization['Organization']['hasFieldArticleAlertToQta']=='Y') 
	echo '<th>'.__('alert_to_qta').'</th>';
	echo '</tr>';
	$disabledOpts = ['disabled' => 'disabled'];
	foreach ($results as $numResult => $result) {
	
		$opts = ['label' => false, 'type' => 'text'];
		if(!$canEdit) {
			$noOwnerOpts = array_merge($opts, $disabledOpts);
		}
		else 
			$noOwnerOpts = $opts;
			
		echo '<tr class="view">';
		echo '<td><a action="articles-'.$result['Article']['organization_id'].'_'.$result['Article']['id'].'" class="actionTrView openTrView" href="#" title="'.__('Href_title_expand').'"></a></td>';
		echo '<td>'.((int)$numResult+1).'</td>';
		echo '<td><input type="checkbox" id="'.$result['Article']['id'].'" name="article_id_selected" value="'.$result['Article']['id'].'" /></td>';
		
		if($user->organization['Organization']['hasFieldArticleCodice']=='Y')
			echo '<td idArticleCheckbox="'.$result['Article']['id'].'" class="bindCheckbox">'.$result['Article']['codice'].'</td>';
		echo '<td idArticleCheckbox="'.$result['Article']['id'].'" class="bindCheckbox">';
		echo $result['Article']['name'].'&nbsp;';
		// confezione
		echo $this->App->getArticleConf($result['Article']['qta'], $result['Article']['um']).'&nbsp;';
			 if(!empty($result['Article']['nota'])) echo '<div class="small">'.strip_tags($result['Article']['nota']).'</div>';
		echo '</td>';
		echo '<td>';
		if(!empty($result['Article']['img1']) && file_exists(Configure::read('App.root').Configure::read('App.img.upload.article').DS.$result['Article']['organization_id'].DS.$result['Article']['img1'])) {
			echo '<img width="50" class="img-responsive-disabled userAvatar" src="'.Configure::read('App.server').Configure::read('App.web.img.upload.article').'/'.$result['Article']['organization_id'].'/'.$result['Article']['img1'].'" />';	
		}
		echo '</td>';

		/*
		 * campi bloccati se non si e' proprietari dell'articolo
		 */		
		 if(!$canEdit && !empty($des_order_id)) { 
		 	$prezzo_ = $result['ArticlesOrder']['prezzo_']; // lo prendo dall'articolo associato all'ordine del titolare DES
			echo '<td style="white-space: nowrap;">';
			echo $this->Form->input('prezzo_disabled', array_merge(['name'=>'data[Article]['.$result['Article']['id'].'][ArticlesOrderPrezzoDisabled]', 'style' => 'display:inline', 'value' => $prezzo_, 'tabindex'=>((int)$numResult+1), 'after'=>'&nbsp;&euro;', 'class'=>'double', 'id' => 'ArticlesOrderPrezzoDisabled-'.$result['Article']['id']], $noOwnerOpts));
			echo $this->Form->hidden('prezzo', ['name'=>'data[Article]['.$result['Article']['id'].'][ArticlesOrderPrezzo]', 'value' => $prezzo_, 'id' => 'ArticlesOrderPrezzo-'.$result['Article']['id']]);
			echo '</td>';			 	
		 }
		 else {
		 	$prezzo_ = $result['Article']['prezzo_'];
			echo '<td style="white-space: nowrap;">';
			echo $this->Form->input('prezzo', array_merge(['name'=>'data[Article]['.$result['Article']['id'].'][ArticlesOrderPrezzo]', 'style' => 'display:inline', 'value' => $prezzo_, 'tabindex'=>((int)$numResult+1),'after'=>'&nbsp;&euro;', 'class'=>'double', 'id' => 'ArticlesOrderPrezzo-'.$result['Article']['id']], $noOwnerOpts));
			echo '</td>';
		 }
		 
		 if(!$canEdit && !empty($des_order_id)) {
		 	$pezzi_confezione = $result['ArticlesOrder']['pezzi_confezione']; // lo prendo dall'articolo associato all'ordine del titolare DES
		 	echo '<td>';
		 	echo $this->Form->input('pezzi_confezione_disabled', array_merge(['name'=>'data[Article]['.$result['Article']['id'].'][ArticlesOrderPezziConfezioneDisabled]','value' => $pezzi_confezione, 'tabindex'=>((int)$numResult+1), 'id' => 'ArticlesOrderPezziConfezioneDisabled-'.$result['Article']['id']], $noOwnerOpts));
		 	echo $this->Form->hidden('pezzi_confezione', ['name'=>'data[Article]['.$result['Article']['id'].'][ArticlesOrderPezziConfezione]', 'value' => $pezzi_confezione, 'id' => 'ArticlesOrderPezziConfezione-'.$result['Article']['id']]);
		 	echo '</td>';
		 }
		 else {
		 	$pezzi_confezione = $result['Article']['pezzi_confezione'];
		 	echo '<td>'.$this->Form->input('pezzi_confezione', array_merge(['name'=>'data[Article]['.$result['Article']['id'].'][ArticlesOrderPezziConfezione]','value' => $pezzi_confezione, 'tabindex'=>((int)$numResult+1), 'id' => 'ArticlesOrderPezziConfezione-'.$result['Article']['id']], $noOwnerOpts)).'</td>';
		 }
		
		/*
		 * campi gestiti anche da chi non e' proprietario dell'articolo
		 */
		 if(!$canEdit && !empty($des_order_id)) 
		 	$qta_minima = $result['ArticlesOrder']['qta_minima']; // lo prendo dall'articolo associato all'ordine del titolare DES
		 else	
		 	$qta_minima = $result['Article']['qta_minima'];
		// echo '<td>'.$this->Form->input('qta_minima', array_merge(['name'=>'data[Article]['.$result['Article']['id'].'][ArticlesOrderQtaMinima]', 'value' => $qta_minima, 'tabindex'=>((int)$numResult+1)], $opts)).'</td>';
	 	echo '<td><div class="btn btn-value-edit" 
				 		data-attr-model="Article"
				 	    data-attr-id-name="ArticlesOrderQtaMinima"
				 	    data-attr-id="'.$result['Article']['id'].'"
				 	    data-attr-value="'.$qta_minima.'">'.$qta_minima.'</div></td>';

		 if(!$canEdit && !empty($des_order_id)) 
		 	$qta_massima = $result['ArticlesOrder']['qta_massima']; // lo prendo dall'articolo associato all'ordine del titolare DES
		 else	
		 	$qta_massima = $result['Article']['qta_massima'];			
		// echo '<td>'.$this->Form->input('qta_massima', array_merge(['name'=>'data[Article]['.$result['Article']['id'].'][ArticlesOrderQtaMassima]' ,'value' => $qta_massima, 'id' => 'ArticlesOrderQtaMassima-'.$result['Article']['id'], 'tabindex'=>((int)$numResult+1)], $opts)).'</td>';
	 	echo '<td><div class="btn btn-value-edit" 
				 		data-attr-model="Article"
				 	    data-attr-id-name="ArticlesOrderQtaMassima"
				 	    data-attr-id="'.$result['Article']['id'].'"
				 	    data-attr-value="'.$qta_massima.'">'.$qta_massima.'</div></td>';		 

		 if(!$canEdit && !empty($des_order_id)) 
		 	$qta_multipli = $result['ArticlesOrder']['qta_multipli']; // lo prendo dall'articolo associato all'ordine del titolare DES
		 else	
		 	$qta_multipli = $result['Article']['qta_multipli'];
		// echo '<td>'.$this->Form->input('qta_multipli', array_merge(['name'=>'data[Article]['.$result['Article']['id'].'][ArticlesOrderQtaMultipli]', 'value' => $qta_multipli, 'id' => 'ArticlesOrderQtaMultipli-'.$result['Article']['id'], 'tabindex'=>((int)$numResult+1)], $opts)).'</td>';
	 	echo '<td><div class="btn btn-value-edit" 
				 		data-attr-model="Article"
				 	    data-attr-id-name="ArticlesOrderQtaMultipli"
				 	    data-attr-id="'.$result['Article']['id'].'"
				 	    data-attr-value="'.$qta_multipli.'">'.$qta_multipli.'</div></td>';	

		 if(!$canEdit && !empty($des_order_id)) 
		 	$qta_minima_order = $result['ArticlesOrder']['qta_minima_order']; // lo prendo dall'articolo associato all'ordine del titolare DES
		 else	
		 	$qta_minima_order = $result['Article']['qta_minima_order'];
 		// echo '<td>'.$this->Form->input('qta_minima_order', array_merge(['name'=>'data[Article]['.$result['Article']['id'].'][ArticlesOrderQtaMinimaOrder]', 'value' => $qta_minima_order, 'id' => 'ArticlesOrderQtaMinimaOrder-'.$result['Article']['id'], 'tabindex'=>((int)$numResult+1)], $opts)).'</td>';
	 	echo '<td><div class="btn btn-value-edit" 
				 		data-attr-model="Article"
				 	    data-attr-id-name="ArticlesOrderQtaMinimaOrder"
				 	    data-attr-id="'.$result['Article']['id'].'"
				 	    data-attr-value="'.$qta_minima_order.'">'.$qta_minima_order.'</div></td>';	 		

		 if(!$canEdit && !empty($des_order_id)) 
		 	$qta_massima_order = $result['ArticlesOrder']['qta_massima_order']; // lo prendo dall'articolo associato all'ordine del titolare DES
		 else	
		 	$qta_massima_order = $result['Article']['qta_massima_order'];	 		 
		// echo '<td>'.$this->Form->input('qta_massima_order', array_merge(['name'=>'data[Article]['.$result['Article']['id'].'][ArticlesOrderQtaMassimaOrder]', 'value' => $qta_massima_order, 'id' => 'ArticlesOrderQtaMassimaOrder-'.$result['Article']['id'], 'tabindex'=>((int)$numResult+1)], $opts)).'</td>';
	 	echo '<td><div class="btn btn-value-edit" 
				 		data-attr-model="Article"
				 	    data-attr-id-name="ArticlesOrderQtaMassimaOrder"
				 	    data-attr-id="'.$result['Article']['id'].'"
				 	    data-attr-value="'.$qta_massima_order.'">'.$qta_massima_order.'</div></td>';	

		if($user->organization['Organization']['hasFieldArticleAlertToQta']=='Y') {
			// echo '<td>'.$this->Form->input('alert_to_qta', array_merge(['name'=>'data[Article]['.$result['Article']['id'].'][ArticlesOrderAlertToQta]','value' => $result['Article']['alert_to_qta'], 'id' => 'ArticlesOrderAlertToQta-'.$result['Article']['id'], 'tabindex'=>((int)$numResult+1)], $opts)).'</td>';
	 		echo '<td><div class="btn btn-value-edit" 
				 		data-attr-model="Article"
				 	    data-attr-id-name="ArticlesOrderAlertToQta"
				 	    data-attr-id="'.$result['Article']['id'].'"
				 	    data-attr-value="'.$alert_to_qta.'">'.$alert_to_qta.'</div></td>';				
		}
		echo '</tr>';
		echo '<tr class="trView" id="trViewId-'.$result['Article']['organization_id'].'_'.$result['Article']['id'].'">';
			echo '<td colspan="2"></td>';
			echo '<td colspan="'.$colspan.'" id="tdViewId-'.$result['Article']['organization_id'].'_'.$result['Article']['id'].'"></td>';
		echo '</tr>';
		
		echo $this->Form->hidden('organization_id',['name'=>'data[Article]['.$result['Article']['id'].'][article_organization_id]', 'value' => $result['Article']['organization_id'], 'id' => 'article_organization_id-'.$result['Article']['id']]);
		echo $this->Form->hidden('supplier_organization_id',['name'=>'data[Article]['.$result['Article']['id'].'][supplier_organization_id]', 'value' => $result['Article']['supplier_organization_id'],  'id' => 'supplier_organization_id-'.$result['Article']['id']]);
		
	} // end foreach ($results as $numResult => $result)
	echo '</table></div>';
	
	echo '</div>';
	echo '</fieldset>';

	echo $this->Form->hidden('action_post', ['id' => 'action_post', 'value' => 'action_articles_orders_current']);
	echo '<input type="hidden" name="data[ArticlesOrder][des_order_id]" value="'.$des_order_id.'" />';
	echo '<input type="hidden" name="data[ArticlesOrder][order_id]" value="'.$order_id.'" />';

	echo '<div class="submit" style="float:right;"><input type="submit" id="action_articles_orders_current2" value="Associa all\'ordine gli articoli selezionati" /></div>';

	echo $this->Form->end();
	
}
else  {// if(count($results)>0)
	echo $this->element('boxMsg',array('class_msg' => 'message', 'msg' => "Il produttore non ha articoli ordinabili con il filtro impostato!"));
}
echo '</div>';
?>
<script type="text/javascript">
var OrganizationHasFieldArticleAlertToQta = "<?php echo $user->organization['Organization']['hasFieldArticleAlertToQta'];?>";

$(document).ready(function() {

	$('#sort').change(function () {
		var sort = $("#sort").val();
		var filter_name = $('#filter_name').val();
		var delivery_id = $('#delivery_id').val();
		var order_id = $('#order_id').val();
		var des_order_id = $('#des_order_id').val();
		var url = '/administrator/index.php?option=com_cake&controller=ArticlesOrders&action=add&delivery_id='+delivery_id+'&order_id='+order_id+'&des_order_id='+des_order_id+'&sort='+sort+'&filter_name='+filter_name;
		window.location.replace(url);
	});

	$('#filter_name').change(function () {
		var sort = $("#sort").val();
		var filter_name = $('#filter_name').val();
		var delivery_id = $('#delivery_id').val();
		var order_id = $('#order_id').val();
		var des_order_id = $('#des_order_id').val();
		var url = '/administrator/index.php?option=com_cake&controller=ArticlesOrders&action=add&delivery_id='+delivery_id+'&order_id='+order_id+'&des_order_id='+des_order_id+'&sort='+sort+'&filter_name='+filter_name;
		window.location.replace(url);
	});
	
	$('#article_id_selected_all').click(function () {
		var checked = $("input[name='article_id_selected_all']:checked").val();
		if(checked=='ALL')
			$('input[name=article_id_selected]').prop('checked',true);
		else
			$('input[name=article_id_selected]').prop('checked',false);
	});
	 
	$(".bindCheckbox").each(function () {
		$(this).click(function() {
			var idArticleCheckbox = $(this).attr('idArticleCheckbox');
			
			if(idArticleCheckbox!=null && idArticleCheckbox!=undefined) {
				if($("#"+idArticleCheckbox).is(':checked'))
					$("#"+idArticleCheckbox).prop('checked',false);
				else
					$("#"+idArticleCheckbox).prop('checked',true);
			}
		}); 
	});
	 
	$('#article_id_selected_all').click(function () {
		var checked = $("input[name='article_id_selected_all']:checked").val();
		if(checked=='ALL')
			$('input[name=article_id_selected]').prop('checked',true);
		else
			$('input[name=article_id_selected]').prop('checked',false);
	});
	
	$('#action_articles_orders_previuos').click(function() {	
		$('#action_post').val('action_articles_orders_previuos');
	});
	$('#action_articles_orders_current1').click(function() {	
		$('#action_post').val('action_articles_orders_current');
	});
	$('#action_articles_orders_current2').click(function() {	
		$('#action_post').val('action_articles_orders_current');
	});

	 
	$('#formGas').submit(function() {
		
		var action_post = $('#action_post').val();
		if(action_post=='action_articles_orders_previuos') 
			return true;
		
		if(!ctrlArticlesOrders())
			return false;

		if(article_id_selected=='') {
			alert("Devi scegliere almeno un articolo da associare all'ordine");
			return false;
		}	    
		article_id_selected = article_id_selected.substring(0,article_id_selected.length-1);
		
		$('#article_id_selected').val(article_id_selected);

		return true;
	});
});
</script>
<style type="text/css">
.bindCheckbox {cursor:pointer;}
</style>