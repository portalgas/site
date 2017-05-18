<?php
if($order['ProdGasArticlesPromotion']['state_code'] != 'CREATE-INCOMPLETE' && $order['ProdGasArticlesPromotion']['state_code'] != 'OPEN-NEXT' && $order['ProdGasArticlesPromotion']['state_code'] != 'OPEN') {
	$label = __('Edit ArticlesOrder');
}
else {
	$label = __('Edit ArticlesOrder OPEN-NEXT');
}

$this->Html->addCrumb(__('Home'),array('controller' => 'Pages', 'action' => 'home'));
$this->Html->addCrumb(__('List Orders'),array('controller' => 'Orders', 'action' => 'index'));
if(isset($order['ProdGasArticlesPromotion']['id']) && !empty($order['ProdGasArticlesPromotion']['id']))
	$this->Html->addCrumb(__('Order home'),array('controller'=>'Orders','action'=>'home', null, 'order_id='.$order['ProdGasArticlesPromotion']['id']));
$this->Html->addCrumb($label);
echo $this->Html->getCrumbList(array('class'=>'crumbs'));

if($user->organization['Organization']['hasFieldArticleCodice']=='Y' && $user->organization['Organization']['hasFieldArticleAlertToQta']=='Y')
	$colspan = '14';
else
if($user->organization['Organization']['hasFieldArticleCodice']=='N' && $user->organization['Organization']['hasFieldArticleAlertToQta']=='N')
	$colspan = '12';
else
	$colspan = '13';
?>
		
<h2 class="ico-edit-cart">
	<?php echo $label;?>
	<div class="actions-img">
		<ul>
			<li><?php echo $this->Html->link(__('Order home'), array('controller' => 'Orders', 'action' => 'home', null, 'order_id='.$order['ProdGasArticlesPromotion']['id']),array('class' => 'action actionWorkflow','title' => __('Order home'))); ?></li>
		</ul>
	</div>
</h2>
<?php 	
echo '<div class="contentMenuLaterale">';

echo $this->Form->create('ProdGasArticlesPromotion',array('id' => 'formGas'));
echo $this->Form->hidden('article_order_key_selected',array('id' =>'article_order_key_selected', 'value'=>''));
echo $this->Form->hidden('article_id_selected',array('id' =>'article_id_selected', 'value'=>''));
?>
<fieldset>

	<div class="box-open-close">
		<div class="barra-open-close open" id="articlesOrdersLabel">
			Elenco degli articoli già associati all'ordine (<?php echo count($results);?>)
		</div>
	
			<div id="articlesOrdersList" style="display:block;">
				
				<?php 
				if(count($results)>0) {
				?>
				<table cellpadding="0" cellspacing="0">
				<tr>
						<th></th>
						<th><?php echo __('N');?></th>
						<th colspan="3">
							<input type="checkbox" id="article_order_key_selected_all" name="article_order_key_selected_all" value="ALL" />
							<img alt="Seleziona gli articoli da cancellare dall'ordine" src="<?php echo Configure::read('App.img.cake');?>/actions/24x24/button_cancel.png" />
							Seleziona gli articoli da cancellare dall'ordine
						</th>
						<th></th>
						<th><?php echo __('Prezzo');?></th>
						<th><?php echo __('pezzi_confezione');?></th>
						<th><?php echo __('qta');?></th>
						<th>Stato</th>	
						<th class="actions"><?php echo __('Actions');?></th>		
				</tr>
				<?php
				foreach ($results as $i => $result):
								
					echo '<tr class="view">';
					
					echo '<td><a action="articles_order_carts-'.$order['ProdGasArticlesPromotion']['id'].'_'.$result['ProdGasArticle']['supplier_id'].'_'.$result['ProdGasArticle']['id'].'" class="actionTrView openTrView" href="#" title="'.__('Href_title_expand').'"></a></td>';
					
					echo '<td>'.($i+1).'</td>';
					echo '<td>';
					echo '<input type="checkbox" id="'.$result['ProdGasArticlesPromotion']['order_id'].'_'.$result['ProdGasArticlesPromotion']['prod_gas_article_id'].'" name="article_order_key_selected" value="'.$result['ProdGasArticlesPromotion']['order_id'].'_'.$result['ProdGasArticlesPromotion']['prod_gas_article_id'].'" />';
					echo '</td>';

					echo '<td idArticlesOrderCheckbox="'.$order['ProdGasArticlesPromotion']['id'].'_'.$result['ProdGasArticle']['id'].'" class="bindCheckbox">'.$result['ProdGasArticle']['codice'].'</td>';
					
					echo '<td idArticlesOrderCheckbox="'.$order['ProdGasArticlesPromotion']['id'].'_'.$result['ProdGasArticle']['id'].'" class="bindCheckbox">';
					echo $result['ProdGasArticle']['name'].'&nbsp;';
					// confezione
					echo $this->App->getArticleConf($result['ProdGasArticle']['qta'], $result['ProdGasArticle']['um']).'&nbsp;';
			
					if(!empty($result['ProdGasArticle']['nota'])) echo '<div class="small">'.strip_tags($result['ProdGasArticle']['nota']).'</div>';
					echo '</td>';
					echo '<td>';
					if(!empty($result['ProdGasArticle']['img1']) && file_exists(Configure::read('App.root').Configure::read('App.img.upload.prod_gas_article').DS.$result['ProdGasArticle']['supplier_id'].DS.$result['ProdGasArticle']['img1'])) {
						echo '<img width="50" class="userAvatar" src="'.Configure::read('App.server').Configure::read('App.web.img.upload.prod_gas_article').'/'.$result['ProdGasArticle']['supplier_id'].'/'.$result['ProdGasArticle']['img1'].'" />';	
					}
					echo '</td>';
					echo '<td nowrap>'.$result['ProdGasArticle']['prezzo_e'].'</td>';
					echo '<td>'.$result['ProdGasArticle']['pezzi_confezione'].'</td>';
					echo '<td>'.$result['ProdGasArticlesPromotion']['qta'].'</td>';
					
					echo '<td ';
					echo 'title="'.$this->App->traslateArticlesOrderStato($result).'" ';			
					echo ' class="stato_'.strtolower($result['ProdGasArticlesPromotion']['stato']).'">';
					echo '</td>';

					echo '<td class="actions-table-img">';
					echo $this->Html->link(null, array('action' => 'edit', null, 'order_id='.$result['ProdGasArticlesPromotion']['order_id'], 'article_supplier_id='.$result['ProdGasArticlesPromotion']['article_supplier_id'], 'article_id='.$result['ProdGasArticlesPromotion']['prod_gas_article_id']) ,array('class' => 'action actionEdit','title' => __('Edit')));
					echo '</td>';
				echo '</tr>';
				echo '<tr class="trView" id="trViewId-'.$order['ProdGasArticlesPromotion']['id'].'_'.$result['ProdGasArticle']['supplier_id'].'_'.$result['ProdGasArticle']['id'].'">';
				echo '<td colspan="2"></td>';
				echo '<td colspan="'.$colspan.'" id="tdViewId-'.$order['ProdGasArticlesPromotion']['id'].'_'.$result['ProdGasArticle']['supplier_id'].'_'.$result['ProdGasArticle']['id'].'"></td>';
				echo '</tr>';
				
				endforeach;
				
				echo '</table>';
				
				} 
				else // if(count($results)>0)
					echo $this->element('boxMsg',array('class_msg' => 'message', 'msg' => "Non ci sono ancora articoli associati all'ordine."));
				?>
				
			</div>
	</div>


	
	
	
	<div class="box-open-close">
		<div class="barra-open-close open" id="articlesLabel">
			Elenco degli articoli attivi ancora da associare all'ordine (<?php echo count($articles);?>)
		</div>				
		
		<div id="articlesList" style="display:none;">
	
			<?php 
			if(count($articles)>0) {
			?>
			<table cellpadding="0" cellspacing="0">
			<tr>
				<th><?php echo __('N');?></th>
				<th colspan="<?php echo ($user->organization['Organization']['hasFieldArticleCodice']=='Y') ? '3' :'2';?>">
					<input type="checkbox" id="article_id_selected_all" name="article_id_selected_all" value="ALL" />
					<img alt="Seleziona gli articoli da inserire nell'ordine" src="<?php echo Configure::read('App.img.cake');?>/actions/24x24/edit_add.png" />
					Seleziona gli articoli da inserire nell'ordine 
				<?php
				echo '</th>';
				echo '<th></th>';
				echo '<th>'.__('Prezzo').'</th>';
				echo '<th>'.__('pezzi_confezione').'</th>';
				echo '<th>'.__('qta').'</th>';
							
			echo '</tr>';
			
			foreach ($articles as $ii => $article):
				echo '<tr class="view">';
				echo '<td>'.($ii+1).'</td>';
				echo '<td>';
				echo '<input type="checkbox" id="'.$article['ProdGasArticle']['id'].'" name="article_id_selected" value="'.$article['ProdGasArticle']['id'].'" />';
				echo '</td>';
				
				if($user->organization['Organization']['hasFieldArticleCodice']=='Y')
					echo '<td idArticleCheckbox="'.$article['ProdGasArticle']['id'].'" class="bindCheckbox">'.$article['ProdGasArticle']['codice'].'</td>';
				echo '<td idArticleCheckbox="'.$article['ProdGasArticle']['id'].'" class="bindCheckbox">';
				echo $article['ProdGasArticle']['name'].'&nbsp;';
				if(!empty($article['ProdGasArticle']['nota'])) echo '<div class="small">'.strip_tags($article['ProdGasArticle']['nota']).'</div>';
				echo '</td>';
				echo '<td>';
				if(!empty($article['ProdGasArticle']['img1']) && file_exists(Configure::read('App.root').Configure::read('App.img.upload.article').DS.$article['ProdGasArticle']['supplier_id'].DS.$article['ProdGasArticle']['img1'])) {
					echo '<img width="50" class="userAvatar" src="'.Configure::read('App.server').Configure::read('App.web.img.upload.article').'/'.$article['ProdGasArticle']['supplier_id'].'/'.$article['ProdGasArticle']['img1'].'" />';	
				}
				echo '</td>';
				echo '<td nowrap>'.$this->Form->input('prezzo',array('label'=>false,'name'=>'data[ProdGasArticle]['.$article['ProdGasArticle']['id'].'][ArticlesOrderPrezzo]','type' => 'text','class' => 'noWidth','value'=>$article['ProdGasArticle']['prezzo_'],'size'=>10,'tabindex'=>($ii+1),'after'=>'&euro;','class'=>'double')).'</td>';
				echo '<td>'.$this->Form->input('pezzi_confezione',array('label'=>false,'name'=>'data[ProdGasArticle]['.$article['ProdGasArticle']['id'].'][ArticlesOrderPezziConfezione]','type' => 'text','class' => 'noWidth','value'=>$article['ProdGasArticle']['pezzi_confezione'],'size'=>3,'tabindex'=>($ii+1))).'</td>';
				echo '<td>'.$this->Form->input('qta',array('label'=>false,'name'=>'data[ProdGasArticle]['.$article['ProdGasArticle']['id'].'][ArticlesOrderQtaMultipli]','type' => 'text','class' => 'noWidth','value'=>$article['ProdGasArticle']['qta_multipli'],'size'=>3,'tabindex'=>($ii+1))).'</td>';
			
			echo '</tr>';
			endforeach;
			echo '</table>';
			} 
			else // if(count($articles)>0)
				echo $this->element('boxMsg',array('class_msg' => 'message', 'msg' => "Non ci sono articoli ancora da associare all'ordine."));
					
		echo '</div>';
	echo '</div>';

echo '</fieldset>';

echo $this->Form->end("Cancella/Associa all'ordine gli articoli selezionati");

echo '</div>';

$options = [];
echo $this->MenuOrders->drawWrapper($order['ProdGasArticlesPromotion']['id'], $options);

	
echo '<div class="clearfix"></div>';
echo $this->element('legendaArticlesOrderStato');
?>
		
<script type="text/javascript">
jQuery(document).ready(function() {
	jQuery('#articlesOrdersLabel').click(function() {
		if(jQuery('#articlesOrdersList').css('display')=='none')  {
			jQuery('#articlesOrdersLabel').removeClass('close');
			jQuery('#articlesOrdersLabel').addClass('open');
			jQuery('#articlesOrdersList').show('slow');
		}	
		else {
			jQuery('#articlesOrdersLabel').removeClass('open');
			jQuery('#articlesOrdersLabel').addClass('close');
			jQuery('#articlesOrdersList').hide('slow');
		}
	});
	jQuery('#articlesLabel').click(function() {
		if(jQuery('#articlesList').css('display')=='none') {
			jQuery('#articlesLabel').removeClass('close');
			jQuery('#articlesLabel').addClass('open');  
			jQuery('#articlesList').show('slow');
		}	
		else {
			jQuery('#articlesLabel').removeClass('open');
			jQuery('#articlesLabel').addClass('close');  
			jQuery('#articlesList').hide('slow');
		}	
	});
	
	jQuery('#article_order_key_selected_all').click(function () {
		var checked = jQuery("input[name='article_order_key_selected_all']:checked").val();
		if(checked=='ALL')
			jQuery('input[name=article_order_key_selected]').prop('checked',true);
		else
			jQuery('input[name=article_order_key_selected]').prop('checked',false);
	});
	
	jQuery('#article_id_selected_all').click(function () {
		var checked = jQuery("input[name='article_id_selected_all']:checked").val();
		if(checked=='ALL')
			jQuery('input[name=article_id_selected]').prop('checked',true);
		else
			jQuery('input[name=article_id_selected]').prop('checked',false);
	});
	
	jQuery(".bindCheckbox").each(function () {
		jQuery(this).click(function() {
			var idArticlesOrderCheckbox = jQuery(this).attr('idArticlesOrderCheckbox');
			var idArticleCheckbox = jQuery(this).attr('idArticleCheckbox');
			
			if(idArticlesOrderCheckbox!=null && idArticlesOrderCheckbox!=undefined) {
				if(jQuery("#"+idArticlesOrderCheckbox).is(':checked'))
					jQuery("#"+idArticlesOrderCheckbox).prop('checked',false);
				else
					jQuery("#"+idArticlesOrderCheckbox).prop('checked',true);
			}
			else
			if(idArticleCheckbox!=null && idArticleCheckbox!=undefined) {
				if(jQuery("#"+idArticleCheckbox).is(':checked'))
					jQuery("#"+idArticleCheckbox).prop('checked',false);
				else
					jQuery("#"+idArticleCheckbox).prop('checked',true);
			}
		}); 
	});
	
	jQuery('#formGas').submit(function() {

		if(article_order_key_selected!='') {
			article_order_key_selected = article_order_key_selected.substring(0,article_order_key_selected.length-1);		
			jQuery('#article_order_key_selected').val(article_order_key_selected);
		}	    

		/*
		 * articoli ancora da associare
		 */
		var article_id_selected = '';
		for(i = 0; i < jQuery("input[name='article_id_selected']:checked").length; i++) {
			article_id_selected += jQuery("input[name='article_id_selected']:checked").eq(i).val()+',';
			
			article_id = jQuery("input[name='article_id_selected']:checked").eq(i).val();
			
			prezzo = jQuery("input[name='data[ProdGasArticle]["+article_id+"][ArticlesOrderPrezzo]']").val(); 
			if(prezzo=='' || prezzo==null || prezzo=='0,00' || prezzo=='0.00' || prezzo=='0') {
				alert("Devi indicare l'importo per gli articoli che desideri associare all'ordine");
				jQuery("input[name='data[ProdGasArticle]["+article_id+"][ArticlesOrderPrezzo]']").focus();
				return false;			
			}
			
			pezzi_confezione = jQuery("input[name='data[ProdGasArticle]["+article_id+"][ArticlesOrderPezziConfezione]']").val(); 
			if(pezzi_confezione=='' || pezzi_confezione==null || !isFinite(pezzi_confezione)) {
				alert("Devi indicare il numero di pezzi per confezione per gli articoli che desideri associare all'ordine");
				jQuery("input[name='data[ProdGasArticle]["+article_id+"][ArticlesOrderPezziConfezione]']").focus();
				return false;			
			}
			if(pezzi_confezione <= 0) {
				alert("Il numero di pezzi per confezione per gli articoli che desideri associare all'ordine deve essere > di zero");
				jQuery("input[name='data[ProdGasArticle]["+article_id+"][ArticlesOrderPezziConfezione]']").focus();
				return false;			
			}
						
			qta_minima = jQuery("input[name='data[ProdGasArticle]["+article_id+"][ArticlesOrderQtaMinima]']").val(); 
			if(qta_minima=='' || qta_minima==null || !isFinite(qta_minima)) {
				alert("Devi indicare la quantità minima per gli articoli associati all'ordine");
				jQuery("input[name='data[ProdGasArticle]["+article_id+"][ArticlesOrderQtaMinima]']").focus();
				return false;			
			}
			qta_minima = parseInt(qta_minima);
			if(qta_minima <= 0) {
				alert("La quantità minima per gli articoli che desideri associare all'ordine deve essere > di zero");
				jQuery("input[name='data[ProdGasArticle]["+article_id+"][ArticlesOrderQtaMinima]']").focus();
				return false;			
			}

			qta_minima_order = jQuery("input[name='data[ProdGasArticle]["+article_id+"][ArticlesOrderQtaMinimaOrder]']").val(); 
			if(qta_minima_order=='' || qta_minima_order==null || !isFinite(qta_minima_order)) {
				alert("Devi indicare la quantità minima rispetto a tutti gli acquisti per gli articoli associati all'ordine");
				jQuery("input[name='data[ProdGasArticle]["+article_id+"][ArticlesOrderQtaMinimaOrder]']").focus();
				return false;			
			}
			
			qta_massima_order = jQuery("input[name='data[ProdGasArticle]["+article_id+"][ArticlesOrderQtaMassimaOrder]']").val(); 
			if(qta_massima_order=='' || qta_massima_order==null || !isFinite(qta_massima_order)) {
				alert("Devi indicare la quantità massima per gli articoli associati all'ordine");
				jQuery("input[name='data[ProdGasArticle]["+article_id+"][ArticlesOrderQtaMassimaOrder]']").focus();
				return false;			
			}
			qta_massima_order = parseInt(qta_massima_order);
			if(qta_massima_order > 0 && qta_massima_order < pezzi_confezione) {
				alert("La quantità massima è inferiore al numero di pezzi in una confezione");
				jQuery("input[name='data[ProdGasArticle]["+article_id+"][ArticlesOrderQtaMassimaOrder]']").focus();
				return false;			
			}
			
			qta_multipli = jQuery("input[name='data[ProdGasArticle]["+article_id+"][ArticlesOrderQtaMultipli]']").val(); 
			if(qta_multipli=='' || qta_multipli==null || !isFinite(qta_multipli)) {
				alert("Devi indicare di che multiplo dev'essere la quantità per gli articoli associati all'ordine");
				jQuery("input[name='data[ProdGasArticle]["+article_id+"][ArticlesOrderQtaMultipli]']").focus();
				return false;			
			}
			qta_multipli = parseInt(qta_multipli);
			if(qta_multipli <= 0) {
				alert("Il multiplo per gli articoli che desideri associare all'ordine deve essere > di zero");
				jQuery("input[name='data[ProdGasArticle]["+article_id+"][ArticlesOrderQtaMultipli]']").focus();
				return false;			
			}
			
			<?php
			if($user->organization['Organization']['hasFieldArticleAlertToQta']=='Y') {
			?>
				alert_to_qta = jQuery("input[name='data[ProdGasArticle]["+article_id+"][ArticlesOrderAlertToQta]']").val(); 
				if(alert_to_qta=='' || alert_to_qta==null || !isFinite(alert_to_qta)) {
					alert("Devi indicare quando avvisare raggiunta una certa quantità per gli articoli associati all'ordine");
					jQuery("input[name='data[ProdGasArticle]["+article_id+"][ArticlesOrderAlertToQta]']").focus();
					return false;			
				}
				if(alert_to_qta <= 0) {
					alert("La quantità che indica quando avvisare per gli articoli che desideri associare all'ordine deve essere > di zero");
					jQuery("input[name='data[ProdGasArticle]["+article_id+"][ArticlesOrderAlertToQta]']").focus();
					return false;			
				}
			<?php
			}
			?>			
		}
		if(article_id_selected!='') {
			article_id_selected = article_id_selected.substring(0,article_id_selected.length-1);
			jQuery('#article_id_selected').val(article_id_selected);
		}	    
		
		return true;
	});
});
</script>
<style type="text/css">
.bindCheckbox {cursor:pointer;}
</style>