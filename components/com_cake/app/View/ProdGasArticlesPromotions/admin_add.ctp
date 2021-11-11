<?php
$this->Html->addCrumb(__('Home'), ['controller' => 'Pages', 'action' => 'home']);
$this->Html->addCrumb(__('List Orders'),array('controller' => 'Orders', 'action' => 'index'));
$this->Html->addCrumb(__('Order'),array('controller'=>'Orders','action'=>'home',$order['Order']['id']));
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

echo $this->Form->create('ArticlesOrder', array('id' => 'formGas'));
echo $this->Form->hidden('article_id_selected',array('id' =>'article_id_selected', 'value'=>''));
?>
<fieldset>
	<legend></legend>

	<div class="legenda">
		<table cellpadding="0" cellspacing="0">
		<tr>
			<th><?php echo __('Delivery');?></th>
			<th><?php echo __('Supplier');?></th>
			<th><?php echo __('Order');?></th>
		</tr>
		<tr class="view">
			<td><?php
			if($order['Delivery']['sys']=='N')
				echo $order['Delivery']['luogoData'];
			else 
				echo $order['Delivery']['luogo'];
			?></td>
			<td><?php echo $order['SuppliersOrganization']['name']; ?></td>
			<td><?php echo $order['Order']['name']; ?>
		</tr>
		</table>
	</div>

	<?php
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
	
	if(count($results)>0) {
		
		if(count($results)>10) 		
			echo '<div class="submit" style="float:right;"><input type="submit" id="action_articles_orders_current1" value="Associa all\'ordine gli articoli selezionati" /></div>';
	
	echo '</h2>';
	?>	
		
	<div class="articlesOrders">
	
		<table cellpadding="0" cellspacing="0">
		<tr>
				<th></th>
				<th><?php echo __('N');?></th>
				<th><?php echo '<input type="checkbox" id="article_id_selected_all" name="article_id_selected_all" value="ALL" />';?></th>
				<?php
				if($user->organization['Organization']['hasFieldArticleCodice']=='Y')
					echo '<th>'.__('Codice').'</th>';
				?>				
				<th>Nome prodotto</th>
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
		</tr>
		<?php
		foreach ($results as $i => $result):
		?>
		<tr class="view">
			<td><a action="articles-<?php echo $result['Article']['organization_id']; ?>_<?php echo $result['Article']['id']; ?>" class="actionTrView openTrView" href="#" title="<?php echo __('Href_title_expand');?>"></a></td>
			<td><?php echo ($i+1);?></td>
			<td><?php echo '<input type="checkbox" id="'.$result['Article']['id'].'" name="article_id_selected" value="'.$result['Article']['id'].'" />';?></td>
			<?php
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
			?>
			<td style="white-space: nowrap;"><?php echo $this->Form->input('prezzo',array('type' => 'text', 'label'=>false,'name'=>'data[Article]['.$result['Article']['id'].'][ArticlesOrderPrezzo]','value'=>$result['Article']['prezzo_'],'tabindex'=>($i+1),'after'=>'&nbsp;&euro;','style'=>'display:inline','class'=>'double'));?></td>
			<td><?php echo $this->Form->input('pezzi_confezione',array('type' => 'text', 'label'=>false,'name'=>'data[Article]['.$result['Article']['id'].'][ArticlesOrderPezziConfezione]','value'=>$result['Article']['pezzi_confezione'],'tabindex'=>($i+1)));?></td>
			<td><?php echo $this->Form->input('qta_minima',array('type' => 'text', 'label'=>false,'name'=>'data[Article]['.$result['Article']['id'].'][ArticlesOrderQtaMinima]','value'=>$result['Article']['qta_minima'],'tabindex'=>($i+1)));?></td>
			<td><?php echo $this->Form->input('qta_massima',array('type' => 'text', 'label'=>false,'name'=>'data[Article]['.$result['Article']['id'].'][ArticlesOrderQtaMassima]','value'=>$result['Article']['qta_massima'],'tabindex'=>($i+1)));?></td>
			<td><?php echo $this->Form->input('qta_multipli',array('type' => 'text', 'label'=>false,'name'=>'data[Article]['.$result['Article']['id'].'][ArticlesOrderQtaMultipli]','value'=>$result['Article']['qta_multipli'],'tabindex'=>($i+1)));?></td>
			<td><?php echo $this->Form->input('qta_minima_order',array('type' => 'text', 'label'=>false,'name'=>'data[Article]['.$result['Article']['id'].'][ArticlesOrderQtaMinimaOrder]','value'=>$result['Article']['qta_minima_order'],'tabindex'=>($i+1)));?></td>
			<td><?php echo $this->Form->input('qta_massima_order',array('type' => 'text', 'label'=>false,'name'=>'data[Article]['.$result['Article']['id'].'][ArticlesOrderQtaMassimaOrder]','value'=>$result['Article']['qta_massima_order'],'tabindex'=>($i+1)));?></td>
				<?php 
				if($user->organization['Organization']['hasFieldArticleAlertToQta']=='Y') 
					echo '<td>'.$this->Form->input('alert_to_qta',array('type' => 'text', 'label'=>false,'name'=>'data[Article]['.$result['Article']['id'].'][ArticlesOrderAlertToQta]','value'=>$result['Article']['alert_to_qta'],'tabindex'=>($i+1))).'</td>';
				?>
		</tr>
		<tr class="trView" id="trViewId-<?php echo $result['Article']['organization_id']; ?>_<?php echo $result['Article']['id'];?>">
			<td colspan="2"></td>
			<td colspan="<?php echo $colspan;?>" id="tdViewId-<?php echo $result['Article']['organization_id']; ?>_<?php echo $result['Article']['id'];?>"></td>
		</tr>
	<?php endforeach; ?>
		</table>
	
	</div>
</fieldset>
<?php 
	echo $this->Form->hidden('action_post',array('id' => 'action_post','value' => 'action_articles_orders_current'));
	echo '<input type="hidden" name="data[ArticlesOrder][des_order_id]" value="'.$des_order_id.'" />';

	echo '<div class="submit" style="float:right;"><input type="submit" id="action_articles_orders_current2" value="Associa all\'ordine gli articoli selezionati" /></div>';

	echo $this->Form->end();
	
	}
	else  // if(count($results)>0)
		echo $this->element('boxMsg',array('class_msg' => 'message', 'msg' => "Il produttore non ha articoli associati!"));
?>
</div>

<script type="text/javascript">
$(document).ready(function() {
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
			pezzi_confezione = parseInt(pezzi_confezione);
			if(pezzi_confezione <= 0) {
				alert("Il numero di pezzi per confezione per gli articoli che desideri associare all'ordine deve essere > di zero");
				$("input[name='data[Article]["+article_id+"][ArticlesOrderPezziConfezione]']").focus();
				return false;			
			}
						
			qta_minima = $("input[name='data[Article]["+article_id+"][ArticlesOrderQtaMinima]']").val(); 
			if(qta_minima=='' || qta_minima==null || !isFinite(qta_minima)) {
				alert("Devi indicare la quantità minima che un gasista può acquistare");
				$("input[name='data[Article]["+article_id+"][ArticlesOrderQtaMinima]']").focus();
				return false;			
			}
			if(qta_minima <= 0) {
				alert("La quantità minima che un gasista può acquistare deve essere > di zero");
				$("input[name='data[Article]["+article_id+"][ArticlesOrderQtaMinima]']").focus();
				return false;			
			}
			
			qta_massima = $("input[name='data[Article]["+article_id+"][ArticlesOrderQtaMassima]']").val(); 
			if(qta_massima=='' || qta_massima==null || !isFinite(qta_massima)) {
				alert("Devi indicare la quantità massima che un gasista può acquistare: di default 0");
				$("input[name='data[Article]["+article_id+"][ArticlesOrderQtaMassima]']").focus();
				return false;			
			}
			
			qta_minima_order = $("input[name='data[Article]["+article_id+"][ArticlesOrderQtaMinimaOrder]']").val(); 
			if(qta_minima_order=='' || qta_minima_order==null || !isFinite(qta_minima_order)) {
				alert("Devi indicare la quantità minima rispetto a tutti gli acquisti dell'ordine");
				$("input[name='data[Article]["+article_id+"][ArticlesOrderQtaMinimaOrder]']").focus();
				return false;			
			}
			
			qta_massima_order = $("input[name='data[Article]["+article_id+"][ArticlesOrderQtaMassimaOrder]']").val(); 
			if(qta_massima_order=='' || qta_massima_order==null || !isFinite(qta_massima_order)) {
				alert("Devi indicare la quantità massima rispetto a tutti gli acquisti dell'ordine");
				$("input[name='data[Article]["+article_id+"][ArticlesOrderQtaMassimaOrder]']").focus();
				return false;			
			}
			qta_massima_order = parseInt(qta_massima_order);
			if(qta_massima_order > 0 && qta_massima_order < pezzi_confezione) {
				alert("La quantità massima rispetto a tutti gli acquisti dell'ordine è inferiore al numero di pezzi in una confezione");
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
			
			if((qta_massima) > 0 && (qta_massima < qta_multipli)) {
				alert("La quantità massima che un gasista può acquistare non può essere inferiore della quantità multipla");
				$("input[name='data[Article]["+article_id+"][ArticlesOrderQtaMassima]']").focus();
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