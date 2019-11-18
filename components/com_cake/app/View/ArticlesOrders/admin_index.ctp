<?php
$debug = false;

if(empty($des_order_id)) {
	if($order['Order']['state_code'] != 'CREATE-INCOMPLETE' && $order['Order']['state_code'] != 'OPEN-NEXT' && $order['Order']['state_code'] != 'OPEN') 
		$label = __('Edit ArticlesOrder');
	else 
		$label = __('Edit ArticlesOrder OPEN-NEXT');
}
else {
	if($order['Order']['state_code'] != 'CREATE-INCOMPLETE' && $order['Order']['state_code'] != 'OPEN-NEXT' && $order['Order']['state_code'] != 'OPEN') 
		$label = __('Edit ArticlesOrder DES');
	else 
		$label = __('Edit ArticlesOrder DES OPEN-NEXT');
}

switch($user->organization['Organization']['type']) {
	case 'PRODGAS':
		$this->Html->addCrumb(__('Home'), ['controller' => 'Pages', 'action' => 'home']);
		$this->Html->addCrumb(__('ProdGasSupplier home'),array('controller' => 'ProdGasSuppliers', 'action' => 'index'));
		$this->Html->addCrumb(__('ProdGasOrders'),array('controller' => 'ProdGasOrders', 'action' => 'index'));
		$this->Html->addCrumb($label);
		echo $this->Html->getCrumbList(array('class'=>'crumbs'));			
	break;
	case 'GAS':
		$this->Html->addCrumb(__('Home'), ['controller' => 'Pages', 'action' => 'home']);
		$this->Html->addCrumb(__('List Orders'),array('controller' => 'Orders', 'action' => 'index'));
		if(isset($order['Order']['id']) && !empty($order['Order']['id']))
			$this->Html->addCrumb(__('Order home'),array('controller'=>'Orders','action'=>'home', null, 'order_id='.$order['Order']['id']));
		$this->Html->addCrumb($label);
		echo $this->Html->getCrumbList(array('class'=>'crumbs'));	
	break;
	case 'PROD':
	
	break;
}



if($user->organization['Organization']['hasFieldArticleCodice']=='Y' && $user->organization['Organization']['hasFieldArticleAlertToQta']=='Y')
	$colspan = '14';
else
if($user->organization['Organization']['hasFieldArticleCodice']=='N' && $user->organization['Organization']['hasFieldArticleAlertToQta']=='N')
	$colspan = '12';
else
	$colspan = '13';


echo '<h2 class="ico-edit-cart">';
echo $label;
echo '<div class="actions-img">';
echo '<ul>';
if($user->organization['Organization']['type']=='GAS')	
	echo '<li>'.$this->Html->link(__('Order home'), ['controller' => 'Orders', 'action' => 'home', null, 'order_id='.$order['Order']['id']], ['class' => 'action actionWorkflow','title' => __('Order home')]).'</li>';
echo '</ul>';
echo '</div>';
echo '</h2>';

if(!empty($des_order_id))
	echo $this->element('boxDesOrder', array('results' => $desOrdersResults));	

	
echo '<div class="contentMenuLaterale">';

echo $this->Form->create('ArticlesOrder', ['id' => 'formGas']);
echo $this->Form->hidden('article_order_key_selected', ['id' => 'article_order_key_selected', 'value' => '']);
echo $this->Form->hidden('article_id_selected', ['id' => 'article_id_selected', 'value' => '']);

echo '<fieldset>';

echo $this->element('boxOrder', ['results' => $order]);
?>

	<div class="panel-group">
	  <div class="panel panel-primary">
		<div class="panel-heading">
		  <h4 class="panel-title">
			<a data-toggle="collapse" data-parent="#accordion" href="#collapse1"><i class="fa fa-lg fa-minus" aria-hidden="true"></i> Elenco degli articoli già associati all'ordine (<?php echo count($results);?>)</a>
		  </h4>
		</div>
		<div id="collapse1" class="panel-collapse collapse in">
		  <div class="panel-body">
		  
			<?php 
			if(count($results)>0) {
				
				echo '<div class="table-responsive"><table class="table table-hover">';
				echo '<tr>';
					echo '<th></th>';
					echo '<th>'.__('N').'</th>';
					echo '<th colspan="';
					echo ($user->organization['Organization']['hasFieldArticleCodice']=='Y') ? '3' :'2';
					echo '">';
					echo '	<input type="checkbox" id="article_order_key_selected_all" name="article_order_key_selected_all" value="ALL" />';
					echo '	<img alt="Seleziona gli articoli da cancellare dall\'ordine" src="'.Configure::read('App.img.cake').'/actions/24x24/button_cancel.png" />';
					echo "	Seleziona gli articoli da cancellare dall'ordine";
					echo '</th>';
					echo '<th></th>';
					echo '<th style="text-align:center;">'.__('Prezzo').'</th>';
					echo '<th style="text-align:center;">'.__('pezzi_confezione').'</th>';
					echo '<th style="text-align:center;">'.__('qta_minima_short').'</th>';
					echo '<th style="text-align:center;">'.__('qta_massima_short').'</th>';
					echo '<th style="text-align:center;">'.__('qta_multipli').'</th>';
					echo '<th style="text-align:center;">'.__('qta_minima_order_short').'</th>';
					echo '<th style="text-align:center;">'.__('qta_massima_order_short').'</th>';
					
					if($user->organization['Organization']['hasFieldArticleAlertToQta']=='Y')
						echo '<th style="text-align:center;">'.__('alert_to_qta').'</th>';
					
					echo '<th>Stato</th>';
					echo '<th class="actions">';
					if($canEdit)
						echo __('Actions');
					echo '</th>';
				echo '</tr>';
				
				foreach ($results as $numResult => $result) {

					$box_detail_link = '';
					$box_detail_id = '';	
					$edit_link = '';

					switch($user->organization['Organization']['type']) {
						case 'PRODGAS':
							if($currentOrganization['SuppliersOrganization']['can_view_orders']!='Y' && $currentOrganization['SuppliersOrganization']['can_view_orders_users']!='Y')
								$box_detail_link = '<a action="prodgas_articles_order_carts-'.$order['Order']['organization_id'].'_'.$order['Order']['id'].'_'.$result['Article']['organization_id'].'_'.$result['Article']['id'].'" class="actionTrView openTrView" href="#" title="'.__('Href_title_expand').'"></a>';
							$box_detail_id = $order['Order']['organization_id'].'_'.$order['Order']['id'].'_'.$result['Article']['organization_id'].'_'.$result['Article']['id'];
							// if($canEdit)  il ctrl lo faccio dopo perche' alcuni campi sono modificabili
							$edit_link = $this->Html->link(null, ['action' => 'prodgas_edit', null, 'organization_id='.$result['ArticlesOrder']['organization_id'].'&order_id='.$result['ArticlesOrder']['order_id'], 'article_organization_id='.$result['ArticlesOrder']['article_organization_id'], 'article_id='.$result['ArticlesOrder']['article_id']], ['class' => 'action actionEdit','title' => __('Edit')]);
						break;
						case 'GAS':
							$box_detail_link = '<a action="articles_order_carts-'.$order['Order']['id'].'_'.$result['Article']['organization_id'].'_'.$result['Article']['id'].'" class="actionTrView openTrView" href="#" title="'.__('Href_title_expand').'"></a>';
							$box_detail_id = $order['Order']['id'].'_'.$result['Article']['organization_id'].'_'.$result['Article']['id'];
							// if($canEdit)  il ctrl lo faccio dopo perche' alcuni campi sono modificabili
							$edit_link = $this->Html->link(null, ['action' => 'edit', null, 'order_id='.$result['ArticlesOrder']['order_id'], 'article_organization_id='.$result['ArticlesOrder']['article_organization_id'], 'article_id='.$result['ArticlesOrder']['article_id']], ['class' => 'action actionEdit','title' => __('Edit')]);
						break;
						case 'PROD':
						
						break;
					}
									
					/*
				     * ctrl se l'articolo e' gia' stato acquaitato
					 */
					if(!empty($result['Cart'])) 
						$articleJustInCart=true;
					else
						$articleJustInCart=false;
				
					echo '<tr class="view">';
					
					echo '<td>'.$box_detail_link;
					if($debug) {
						echo 'ArticlesOrder.organization_id '.$result['ArticlesOrder']['organization_id'].'<br />';
						echo 'ArticlesOrder.article_id '.$result['ArticlesOrder']['article_id'].'<br />';
						echo 'ArticlesOrder.article_organization_id '.$result['ArticlesOrder']['article_organization_id'].'<br />';
						echo 'ArticlesOrder.order_id '.$result['ArticlesOrder']['order_id'];
					}
					echo '</td>';
					
					echo '<td>'.($numResult+1).'</td>';
					echo '<td ';
					if($articleJustInCart) echo 'style="background-color:red;" title="Articolo già acquistato"';
					echo '>';
					echo '<input type="checkbox" ';
					if($articleJustInCart) 
						echo ' articleJustInCart=true '; 
					else 
						echo ' articleJustInCart=false '; 
					echo ' name="article_order_key_selected" value="'.$result['ArticlesOrder']['order_id'].'_'.$result['ArticlesOrder']['article_organization_id'].'_'.$result['ArticlesOrder']['article_id'].'" />';
					echo '</td>';

					if($user->organization['Organization']['hasFieldArticleCodice']=='Y')
						echo '<td idArticlesOrderCheckbox="'.$order['Order']['id'].'_'.$result['Article']['id'].'" class="bindCheckbox">'.$result['Article']['codice'].'</td>';
					
					echo '<td idArticlesOrderCheckbox="'.$order['Order']['id'].'_'.$result['Article']['id'].'" class="bindCheckbox">';
					echo $result['ArticlesOrder']['name'].'&nbsp;';
					// confezione
					echo $this->App->getArticleConf($result['Article']['qta'], $result['Article']['um']).'&nbsp;';
			
					if(!empty($result['Article']['nota'])) echo '<div class="small">'.strip_tags($result['Article']['nota']).'</div>';
					echo '</td>';
					echo '<td>';
					if(!empty($result['Article']['img1']) && file_exists(Configure::read('App.root').Configure::read('App.img.upload.article').DS.$result['Article']['organization_id'].DS.$result['Article']['img1'])) {
						echo '<img width="50" class="img-responsive-disabled userAvatar" src="'.Configure::read('App.server').Configure::read('App.web.img.upload.article').'/'.$result['Article']['organization_id'].'/'.$result['Article']['img1'].'" />';	
					}
					echo '</td>';
					echo '<td nowrap>'.$result['ArticlesOrder']['prezzo_e'].'</td>';
					echo '<td style="text-align:center;">'.$result['ArticlesOrder']['pezzi_confezione'].'</td>';
					echo '<td style="text-align:center;">'.$result['ArticlesOrder']['qta_minima'].'</td>';
					echo '<td style="text-align:center;">'.$result['ArticlesOrder']['qta_massima'].'</td>';
					echo '<td style="text-align:center;">'.$result['ArticlesOrder']['qta_multipli'].'</td>';
					echo '<td style="text-align:center;">'.$result['ArticlesOrder']['qta_minima_order'].'</td>';
					echo '<td style="text-align:center;">'.$result['ArticlesOrder']['qta_massima_order'].'</td>';
					
					if($user->organization['Organization']['hasFieldArticleAlertToQta']=='Y')
						echo '<td style="text-align:center;">'.$result['ArticlesOrder']['alert_to_qta'].'</td>';
						
					echo '<td ';
					echo 'title="'.$this->App->traslateArticlesOrderStato($result).'" ';
					if(strtolower($result['ArticlesOrder']['stato'])=='qtamaxorder')
						$stato = 'qtamax';
					else
						$stato = $result['ArticlesOrder']['stato'];
					echo ' class="stato_'.strtolower($stato).'">';
					echo '</td>';

					echo '<td class="actions-table-img">'.$edit_link.'</td>';
					echo '</tr>';
					echo '<tr class="trView" id="trViewId-'.$box_detail_id.'">';
					echo '<td colspan="2"></td>';
					echo '<td colspan="'.$colspan.'" id="tdViewId-'.$box_detail_id.'"></td>';
					echo '</tr>';
				
					echo $this->Form->hidden('article_organization_id',['name' => 'data[ArticlesOrder]['.$result['Article']['id'].'][article_organization_id]', 'value' => $result['ArticlesOrder']['article_organization_id']]);
					echo $this->Form->hidden('article_id',['name' => 'data[ArticlesOrder]['.$result['Article']['id'].'][article_id]', 'value' => $result['ArticlesOrder']['article_id']]);
					
				} // end foreach ($results as $numResult => $result)
			
				echo '</table></div>';
				
				} 
				else // if(count($results)>0)
					echo $this->element('boxMsg',array('class_msg' => 'message', 'msg' => "Non ci sono ancora articoli associati all'ordine."));
				?>
				
		  </div>
		</div>
	  </div>
	  <div class="panel panel-primary">
		<div class="panel-heading">
		  <h4 class="panel-title">
			<a data-toggle="collapse" data-parent="#accordion" href="#collapse2"><i class="fa fa-lg fa-plus" aria-hidden="true"></i> Elenco degli articoli attivi ancora da associare all'ordine (<?php echo count($articles);?>)</a>
		  </h4>
		</div>
		<div id="collapse2" class="panel-collapse collapse">
		  <div class="panel-body">
		  
			<?php 
			if(count($articles)>0) {
			?>
			<div class="table-responsive"><table class="table table-hover">
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
				echo '<th>'.__('qta_minima_short').'</th>';
				echo '<th>'.__('qta_massima_short').'</th>';
				echo '<th>'.__('qta_multipli').'</th>';
				echo '<th>'.__('qta_minima_order').'</th>';
				echo '<th>'.__('qta_massima_order').'</th>';
				
				if($user->organization['Organization']['hasFieldArticleAlertToQta']=='Y') 
					echo '<th>'.__('alert_to_qta').'</th>';
							
				echo '</tr>';
				
				$disabledOpts = ['disabled' => 'disabled'];
				$numResult=0;
				foreach ($articles as $article) {
					
					$numResult++;
					
					$opts = ['label' => false, 'type' => 'text'];
					if(!$canEdit) 
						$noOwnerOpts = array_merge($opts, $disabledOpts);
					else 
						$noOwnerOpts = $opts;
									
					echo '<tr class="view">';
					echo '<td>'.$numResult.'</td>';
					echo '<td>';
					echo '<input type="checkbox" id="'.$article['Article']['id'].'" name="article_id_selected" value="'.$article['Article']['id'].'" />';
					if($debug) {
						echo 'Article.organization_id '.$result['Article']['organization_id'].'<br />';
						echo 'Article.id '.$result['Article']['id'].'<br />';
						echo 'Article.supplier_organization_id '.$result['Article']['supplier_organization_id'];
					}					
					echo '</td>';
					
					if($user->organization['Organization']['hasFieldArticleCodice']=='Y')
						echo '<td idArticleCheckbox="'.$article['Article']['id'].'" class="bindCheckbox">'.$article['Article']['codice'].'</td>';
					echo '<td idArticleCheckbox="'.$article['Article']['id'].'" class="bindCheckbox">';
					echo $article['Article']['name'].'&nbsp;';
					if(!empty($article['Article']['nota'])) echo '<div class="small">'.strip_tags($article['Article']['nota']).'</div>';
					echo '</td>';
					echo '<td>';
					if(!empty($article['Article']['img1']) && file_exists(Configure::read('App.root').Configure::read('App.img.upload.article').DS.$article['Article']['organization_id'].DS.$article['Article']['img1'])) {
						echo '<img width="50" class="img-responsive-disabled userAvatar" src="'.Configure::read('App.server').Configure::read('App.web.img.upload.article').'/'.$article['Article']['organization_id'].'/'.$article['Article']['img1'].'" />';	
					}
					echo '</td>';
					
					/*
					 * campi bloccati se non si e' proprietari dell'articolo
					 */							
					echo '<td style="white-space: nowrap;">'.$this->Form->input('prezzo', array_merge(['name'=>'data[Article]['.$article['Article']['id'].'][ArticlesOrderPrezzo]', 'style' => 'display:inline', 'value' => $article['Article']['prezzo_'], 'tabindex'=>($numResult+1),'after'=>'&nbsp;&euro;', 'class'=>'double'], $noOwnerOpts)).'</td>';
					echo '<td>'.$this->Form->input('pezzi_confezione', array_merge(['name'=>'data[Article]['.$article['Article']['id'].'][ArticlesOrderPezziConfezione]','value' => $article['Article']['pezzi_confezione'], 'tabindex'=>($numResult+1)], $noOwnerOpts)).'</td>';
					
					/*
					 * campi gestiti anche da chi non e' proprietario dell'articolo
					 */					
					echo '<td>'.$this->Form->input('qta_minima', array_merge(['name'=>'data[Article]['.$article['Article']['id'].'][ArticlesOrderQtaMinima]', 'value' => $article['Article']['qta_minima'], 'tabindex'=>($numResult+1)], $opts)).'</td>';
					echo '<td>'.$this->Form->input('qta_massima', array_merge(['name'=>'data[Article]['.$article['Article']['id'].'][ArticlesOrderQtaMassima]', 'value' => $article['Article']['qta_massima'], 'tabindex'=>($numResult+1)], $opts)).'</td>';
					echo '<td>'.$this->Form->input('qta_multipli', array_merge(['name'=>'data[Article]['.$article['Article']['id'].'][ArticlesOrderQtaMultipli]', 'value' => $article['Article']['qta_multipli'], 'tabindex'=>($numResult+1)], $opts)).'</td>';
					echo '<td>'.$this->Form->input('qta_minima_order', array_merge(['name'=>'data[Article]['.$article['Article']['id'].'][ArticlesOrderQtaMinimaOrder]', 'value' => $article['Article']['qta_minima_order'], 'tabindex'=>($numResult+1)], $opts)).'</td>';
					echo '<td>'.$this->Form->input('qta_massima_order', array_merge(['name'=>'data[Article]['.$article['Article']['id'].'][ArticlesOrderQtaMassimaOrder]', 'value' => $article['Article']['qta_massima_order'], 'tabindex'=>($numResult+1)], $opts)).'</td>';
					
					if($user->organization['Organization']['hasFieldArticleAlertToQta']=='Y')
						echo '<td>'.$this->Form->input('alert_to_qta', array_merge(['name'=>'data[Article]['.$article['Article']['id'].'][ArticlesOrderAlertToQta]','value' => $article['Article']['alert_to_qta'],'tabindex'=>($numResult+1)], $opts)).'</td>';
				
					echo $this->Form->hidden('organization_id',['name' => 'data[Article]['.$article['Article']['id'].'][article_organization_id]', 'value' => $article['Article']['organization_id']]);
					echo $this->Form->hidden('supplier_organization_id',['name' => 'data[Article]['.$article['Article']['id'].'][supplier_organization_id]', 'value' => $article['Article']['supplier_organization_id']]);
					
					echo '</tr>';
				} // end loop 
				echo '</table></div>';
			} 
			else // if(count($articles)>0)
				echo $this->element('boxMsg',array('class_msg' => 'message', 'msg' => "Non ci sono articoli ancora da associare all'ordine."));
			?>
					
		  </div>
		</div>
	  </div>
	</div> <!-- panel-group -->
	
	
<?php
echo '</fieldset>';

echo $this->Form->hidden('order_id',['name' => 'data[ArticlesOrder][order_id]', 'value' => $order_id]);
echo $this->Form->end("Cancella/Associa all'ordine gli articoli selezionati");

echo '</div>';

echo $this->MenuOrders->drawWrapper($order['Order']['id'], $options=[]);

echo '<div class="clearfix"></div>';
echo $this->element('legendaArticlesOrderStato');
?>
		
<script type="text/javascript">
$(document).ready(function() {

	$('#article_order_key_selected_all').click(function () {
		var checked = $("input[name='article_order_key_selected_all']:checked").val();
		if(checked=='ALL')
			$('input[name=article_order_key_selected]').prop('checked',true);
		else
			$('input[name=article_order_key_selected]').prop('checked',false);
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
			var idArticlesOrderCheckbox = $(this).attr('idArticlesOrderCheckbox');
			var idArticleCheckbox = $(this).attr('idArticleCheckbox');
			
			if(idArticlesOrderCheckbox!=null && idArticlesOrderCheckbox!=undefined) {
				if($("#"+idArticlesOrderCheckbox).is(':checked'))
					$("#"+idArticlesOrderCheckbox).prop('checked',false);
				else
					$("#"+idArticlesOrderCheckbox).prop('checked',true);
			}
			else
			if(idArticleCheckbox!=null && idArticleCheckbox!=undefined) {
				if($("#"+idArticleCheckbox).is(':checked'))
					$("#"+idArticleCheckbox).prop('checked',false);
				else
					$("#"+idArticleCheckbox).prop('checked',true);
			}
		}); 
	});
	
	$('#formGas').submit(function() {

		/*
		 * articoli gia' associati
		 */
		var articleJustInCart=0;
		var article_order_key_selected = '';
		for(i = 0; i < $("input[name='article_order_key_selected']:checked").length; i++) {
			var elem = $("input[name='article_order_key_selected']:checked").eq(i);
			if($(elem).attr('articleJustInCart')=='true') 
				articleJustInCart++;

			article_order_key_selected += elem.val()+',';
		}
		
		if(articleJustInCart>0) {
			if(!confirm("Alcuni articoli che hai scelto di cancellare sono già stati acquistati:\nconfermi la cancellazione degli articoli e gli acquisti associati?"))	return false;
		}
		
		if(article_order_key_selected!='') {
			article_order_key_selected = article_order_key_selected.substring(0,article_order_key_selected.length-1);		
			$('#article_order_key_selected').val(article_order_key_selected);
		}	    

		/*
		 * articoli ancora da associare
		 */
		var article_id_selected = '';
		for(i = 0; i < $("input[name='article_id_selected']:checked").length; i++) {
			article_id_selected += $("input[name='article_id_selected']:checked").eq(i).val()+',';
			
			article_id = $("input[name='article_id_selected']:checked").eq(i).val();
			
			prezzo = $("input[name='data[Article]["+article_id+"][ArticlesOrderPrezzo]']").val(); 
			if(prezzo=='' || prezzo==null || prezzo=='0,00' || prezzo=='0.00' || prezzo=='0') {
				alert("Devi indicare l'importo per gli articoli che desideri associare all'ordine");
				$("input[name='data[Article]["+article_id+"][ArticlesOrderPrezzo]']").focus();
				return false;			
			}
			
			pezzi_confezione = $("input[name='data[Article]["+article_id+"][ArticlesOrderPezziConfezione]']").val(); 
			if(pezzi_confezione=='' || pezzi_confezione==null || !isFinite(pezzi_confezione)) {
				alert("Devi indicare il numero di pezzi per confezione per gli articoli che desideri associare all'ordine");
				$("input[name='data[Article]["+article_id+"][ArticlesOrderPezziConfezione]']").focus();
				return false;			
			}
			if(pezzi_confezione <= 0) {
				alert("Il numero di pezzi per confezione per gli articoli che desideri associare all'ordine deve essere > di zero");
				$("input[name='data[Article]["+article_id+"][ArticlesOrderPezziConfezione]']").focus();
				return false;			
			}
						
			qta_minima = $("input[name='data[Article]["+article_id+"][ArticlesOrderQtaMinima]']").val(); 
			if(qta_minima=='' || qta_minima==null || !isFinite(qta_minima)) {
				alert("Devi indicare la quantità minima per gli articoli associati all'ordine");
				$("input[name='data[Article]["+article_id+"][ArticlesOrderQtaMinima]']").focus();
				return false;			
			}
			qta_minima = parseInt(qta_minima);
			if(qta_minima <= 0) {
				alert("La quantità minima per gli articoli che desideri associare all'ordine deve essere > di zero");
				$("input[name='data[Article]["+article_id+"][ArticlesOrderQtaMinima]']").focus();
				return false;			
			}

			qta_minima_order = $("input[name='data[Article]["+article_id+"][ArticlesOrderQtaMinimaOrder]']").val(); 
			if(qta_minima_order=='' || qta_minima_order==null || !isFinite(qta_minima_order)) {
				alert("Devi indicare la quantità minima rispetto a tutti gli acquisti per gli articoli associati all'ordine");
				$("input[name='data[Article]["+article_id+"][ArticlesOrderQtaMinimaOrder]']").focus();
				return false;			
			}
			
			qta_massima_order = $("input[name='data[Article]["+article_id+"][ArticlesOrderQtaMassimaOrder]']").val(); 
			if(qta_massima_order=='' || qta_massima_order==null || !isFinite(qta_massima_order)) {
				alert("Devi indicare la quantità massima per gli articoli associati all'ordine");
				$("input[name='data[Article]["+article_id+"][ArticlesOrderQtaMassimaOrder]']").focus();
				return false;			
			}
			qta_massima_order = parseInt(qta_massima_order);
			if(qta_massima_order > 0 && qta_massima_order < pezzi_confezione) {
				alert("La quantità massima è inferiore al numero di pezzi in una confezione");
				$("input[name='data[Article]["+article_id+"][ArticlesOrderQtaMassimaOrder]']").focus();
				return false;			
			}
			
			qta_multipli = $("input[name='data[Article]["+article_id+"][ArticlesOrderQtaMultipli]']").val(); 
			if(qta_multipli=='' || qta_multipli==null || !isFinite(qta_multipli)) {
				alert("Devi indicare di che multiplo dev'essere la quantità per gli articoli associati all'ordine");
				$("input[name='data[Article]["+article_id+"][ArticlesOrderQtaMultipli]']").focus();
				return false;			
			}
			qta_multipli = parseInt(qta_multipli);
			if(qta_multipli <= 0) {
				alert("Il multiplo per gli articoli che desideri associare all'ordine deve essere > di zero");
				$("input[name='data[Article]["+article_id+"][ArticlesOrderQtaMultipli]']").focus();
				return false;			
			}
			
			<?php
			if($user->organization['Organization']['hasFieldArticleAlertToQta']=='Y') {
			?>
				alert_to_qta = $("input[name='data[Article]["+article_id+"][ArticlesOrderAlertToQta]']").val(); 
				if(alert_to_qta=='' || alert_to_qta==null || !isFinite(alert_to_qta)) {
					alert("Devi indicare quando avvisare raggiunta una certa quantità per gli articoli associati all'ordine");
					$("input[name='data[Article]["+article_id+"][ArticlesOrderAlertToQta]']").focus();
					return false;			
				}
				if(alert_to_qta <= 0) {
					alert("La quantità che indica quando avvisare per gli articoli che desideri associare all'ordine deve essere > di zero");
					$("input[name='data[Article]["+article_id+"][ArticlesOrderAlertToQta]']").focus();
					return false;			
				}
			<?php
			}
			?>			
		}
		if(article_id_selected!='') {
			article_id_selected = article_id_selected.substring(0,article_id_selected.length-1);
			$('#article_id_selected').val(article_id_selected);
		}	    
		
		return true;
	});
});
</script>
<style type="text/css">
.bindCheckbox {cursor:pointer;}
</style>