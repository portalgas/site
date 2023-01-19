<?php
echo $this->Html->script('indexRows.min');

if($order['Order']['order_state_id'] >= Configure::read('OPEN')) {
	$label = __('Edit ArticlesOrder OPEN');
}
else {
	$label = __('Edit ArticlesOrder OPEN-NEXT');
}

$this->Html->addCrumb(__('Home'), ['controller' => 'Pages', 'action' => 'home']);
$this->Html->addCrumb(__('List Orders'),array('controller' => 'Orders', 'action' => 'index'));
if(isset($order['Order']['id']) && !empty($order['Order']['id']))
	$this->Html->addCrumb(__('Order home'),array('controller'=>'Orders','action'=>'home', null, 'order_id='.$order['Order']['id']));
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
			<li><?php echo $this->Html->link(__('Order home'), array('controller' => 'Orders', 'action' => 'home', null, 'order_id='.$order['Order']['id']),array('class' => 'action actionWorkflow','title' => __('Order home'))); ?></li>
		</ul>
	</div>
</h2>
<?php 
echo '<div class="contentMenuLaterale">';

echo $this->Form->create('ArticlesOrder',array('id' => 'formGas'));
?>
<fieldset>

	<?php include('box_order_detail.ctp');?>

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
						<th colspan="<?php echo ($user->organization['Organization']['hasFieldArticleCodice']=='Y') ? '3' :'2';?>">
							<input type="checkbox" id="article_order_key_selected_all" name="article_order_key_selected_all" value="ALL" />
							<img alt="Seleziona gli articoli da cancellare dall'ordine" src="<?php echo Configure::read('App.img.cake');?>/actions/24x24/button_cancel.png" />
							Seleziona gli articoli da cancellare dall'ordine
						</th>
						<th></th>
						<th><?php echo __('Prezzo');?></th>
						<th><?php echo __('pezzi_confezione');?></th>
						<th><?php echo __('qta_minima_short');?></th>
						<th><?php echo __('qta_massima_short');?></th>
						<th><?php echo __('qta_multipli');?></th>
						<th><?php echo __('qta_minima_order_short');?></th>
						<th><?php echo __('qta_massima_order_short');?></th>
						
						<?php
						if($user->organization['Organization']['hasFieldArticleAlertToQta']=='Y')
							echo '<th>'.__('alert_to_qta').'</th>';
						?>
						<th>Stato</th>	
						<th class="actions"><?php echo __('Actions');?></th>		
				</tr>
				<?php
				foreach ($results as $i => $result):
				
					/*
				     * ctrl se l'articolo e' gia' stato acquaitato
					 */
					if(!empty($result['Cart'])) 
						$articleJustInCart=true;
					else
						$articleJustInCart=false;
				
					echo '<tr class="view">';
					
					echo '<td><a action="articles_order_carts-'.$order['Order']['id'].'_'.$result['Article']['organization_id'].'_'.$result['Article']['id'].'" class="actionTrView openTrView" href="#" title="'.__('Href_title_expand').'"></a></td>';
					
					echo '<td>'.($i+1).'</td>';
					echo '<td ';
					if($articleJustInCart) echo 'style="background-color:red;" title="Articolo già acquistato"';
					echo '>';
					echo '<input type="checkbox" ';
					if($articleJustInCart) 
						echo ' articleJustInCart=true '; 
					else 
						echo ' articleJustInCart=false '; 
					echo 'id="'.$result['ArticlesOrder']['order_id'].'_'.$result['ArticlesOrder']['article_id'].'" name="article_order_key_selected" value="'.$result['ArticlesOrder']['order_id'].'_'.$result['ArticlesOrder']['article_id'].'" />';
					echo '</td>';

					if($user->organization['Organization']['hasFieldArticleCodice']=='Y')
						echo '<td idArticlesOrderCheckbox="'.$order['Order']['id'].'_'.$result['Article']['id'].'" class="bindCheckbox">'.$result['Article']['codice'].'</td>';
					
					echo '<td idArticlesOrderCheckbox="'.$order['Order']['id'].'_'.$result['Article']['id'].'" class="bindCheckbox">';
					echo $result['Article']['name'].'&nbsp;';
					if(!empty($result['Article']['nota'])) echo '<div class="small">'.strip_tags($result['Article']['nota']).'</div>';
					echo '</td>';
					echo '<td>';
					if(!empty($result['Article']['img1']) && file_exists(Configure::read('App.root').Configure::read('App.img.upload.article').DS.$result['Article']['organization_id'].DS.$result['Article']['img1'])) {
						echo '<img width="50" class="img-responsive-disabled userAvatar" src="'.Configure::read('App.server').Configure::read('App.web.img.upload.article').'/'.$result['Article']['organization_id'].'/'.$result['Article']['img1'].'" />';	
					}
					echo '</td>';
					echo '<td nowrap>'.$result['ArticlesOrder']['prezzo_e'].'</td>';
					echo '<td>'.$result['ArticlesOrder']['pezzi_confezione'].'</td>';
					echo '<td>'.$result['ArticlesOrder']['qta_minima'].'</td>';
					echo '<td>'.$result['ArticlesOrder']['qta_massima'].'</td>';
					echo '<td>'.$result['ArticlesOrder']['qta_multipli'].'</td>';
					echo '<td>'.$result['ArticlesOrder']['qta_minima_order'].'</td>';
					echo '<td>'.$result['ArticlesOrder']['qta_massima_order'].'</td>';
					
					if($user->organization['Organization']['hasFieldArticleAlertToQta']=='Y')
						echo '<td>'.$result['ArticlesOrder']['alert_to_qta'].'</td>';
						
					echo '<td ';
					echo 'title="'.$this->App->traslateArticlesOrderStato($result).'" ';
					if(strtolower($result['ArticlesOrder']['stato'])=='qtamaxorder')
						$stato = 'qtamax';
					else
						$stato = $result['ArticlesOrder']['stato'];
					echo ' class="stato_'.strtolower($stato).'">';					
					echo '</td>';

					echo '<td class="actions-table-img">';
					echo $this->Html->link(null, array('action' => 'edit_open', null, 'order_id='.$result['ArticlesOrder']['order_id'], 'article_organization_id='.$result['ArticlesOrder']['article_organization_id'], 'article_id='.$result['ArticlesOrder']['article_id']) ,array('class' => 'action actionEdit','title' => __('Edit')));
					echo '</td>';
				echo '</tr>';
				echo '<tr class="trView" id="trViewId-'.$order['Order']['id'].'_'.$result['Article']['organization_id'].'_'.$result['Article']['id'].'">';
				echo '<td colspan="2"></td>';
				echo '<td colspan="'.$colspan.'" id="tdViewId-'.$order['Order']['id'].'_'.$result['Article']['organization_id'].'_'.$result['Article']['id'].'"></td>';
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
				echo '<th>'.__('qta_minima').'</th>';
				echo '<th>'.__('qta_massima').'</th>';
				echo '<th>'.__('qta_multipli').'</th>';
				echo '<th>'.__('qta_minima_order').'</th>';
				echo '<th>'.__('qta_massima_order').'</th>';
				
				if($user->organization['Organization']['hasFieldArticleAlertToQta']=='Y') 
					echo '<th>'.__('alert_to_qta').'</th>';
							
			echo '</tr>';
			
			foreach ($articles as $ii => $article):
				echo '<tr class="view">';
				echo '<td>'.($ii+1).'</td>';
				echo '<td>';
				echo '<input type="checkbox" id="'.$article['Article']['id'].'" name="article_id_selected" value="'.$article['Article']['id'].'" />';
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
				echo '<td style="white-space: nowrap;">'.$this->Form->input('prezzo',array('label'=>false,'name'=>'data[Article]['.$article['Article']['id'].'][ArticlesOrderPrezzo]','type' => 'text','value'=>$article['Article']['prezzo_'],'tabindex'=>($ii+1),'after'=>'&nbsp;&euro;','style' => 'display:inline','class'=>'double')).'</td>';
				echo '<td>'.$this->Form->input('pezzi_confezione',array('label'=>false,'name'=>'data[Article]['.$article['Article']['id'].'][ArticlesOrderPezziConfezione]','type' => 'text','value'=>$article['Article']['pezzi_confezione'],'tabindex'=>($ii+1))).'</td>';
				echo '<td>'.$this->Form->input('qta_minima',array('label'=>false,'name'=>'data[Article]['.$article['Article']['id'].'][ArticlesOrderQtaMinima]','type' => 'text','value'=>$article['Article']['qta_minima'],'tabindex'=>($ii+1))).'</td>';
				echo '<td>'.$this->Form->input('qta_massima',array('label'=>false,'name'=>'data[Article]['.$article['Article']['id'].'][ArticlesOrderQtaMassima]','type' => 'text','value'=>$article['Article']['qta_massima'],'tabindex'=>($ii+1))).'</td>';
				echo '<td>'.$this->Form->input('qta_multipli',array('label'=>false,'name'=>'data[Article]['.$article['Article']['id'].'][ArticlesOrderQtaMultipli]','type' => 'text','value'=>$article['Article']['qta_multipli'],'tabindex'=>($ii+1))).'</td>';
				echo '<td>'.$this->Form->input('qta_minima_order',array('label'=>false,'name'=>'data[Article]['.$article['Article']['id'].'][ArticlesOrderQtaMinimaOrder]','type' => 'text','value'=>$article['Article']['qta_minima_order'],'tabindex'=>($ii+1))).'</td>';
				echo '<td>'.$this->Form->input('qta_massima_order',array('label'=>false,'name'=>'data[Article]['.$article['Article']['id'].'][ArticlesOrderQtaMassimaOrder]','type' => 'text','value'=>$article['Article']['qta_massima_order'],'tabindex'=>($ii+1))).'</td>';
				
				if($user->organization['Organization']['hasFieldArticleAlertToQta']=='Y')
					echo '<td>'.$this->Form->input('alert_to_qta',array('label'=>false,'name'=>'data[Article]['.$article['Article']['id'].'][ArticlesOrderAlertToQta]','value'=>$article['Article']['alert_to_qta'],'tabindex'=>($ii+1))).'</td>'; 
			
			echo '</tr>';
			endforeach;
			echo '</table>';
			} 
			else // if(count($articles)>0)
				echo $this->element('boxMsg',array('class_msg' => 'message', 'msg' => "Non ci sono articoli ancora da associare all'ordine."));
					
		echo '</div>';
	echo '</div>';

echo '</fieldset>';

echo $this->Form->hidden('article_order_key_selected',array('id' =>'article_order_key_selected', 'value'=>''));
echo $this->Form->hidden('article_id_selected',array('id' =>'article_id_selected', 'value'=>''));
echo $this->Form->end("Cancella/Associa all'ordine gli articoli selezionati");

echo '</div>';
	
echo '<div class="clearfix"></div>';
echo $this->element('legendaArticlesOrderStato');

$options = [];
echo $this->MenuOrders->drawWrapper($order_id, $options);
?>
		
<script type="text/javascript">
$(document).ready(function() {
	$('#articlesOrdersLabel').click(function() {
		if($('#articlesOrdersList').css('display')=='none')  {
			$('#articlesOrdersLabel').removeClass('close');
			$('#articlesOrdersLabel').addClass('open');
			$('#articlesOrdersList').show('slow');
		}	
		else {
			$('#articlesOrdersLabel').removeClass('open');
			$('#articlesOrdersLabel').addClass('close');
			$('#articlesOrdersList').hide('slow');
		}
	});
	$('#articlesLabel').click(function() {
		if($('#articlesList').css('display')=='none') {
			$('#articlesLabel').removeClass('close');
			$('#articlesLabel').addClass('open');  
			$('#articlesList').show('slow');
		}	
		else {
			$('#articlesLabel').removeClass('open');
			$('#articlesLabel').addClass('close');  
			$('#articlesList').hide('slow');
		}	
	});
	
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
			if(!confirm("Alcuni articoli che hai scelto di cancellare sono già stati acquistati:\nconfermi la cancellazione dall'ordine degli articoli e degli acquisti associati?"))	return false;
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