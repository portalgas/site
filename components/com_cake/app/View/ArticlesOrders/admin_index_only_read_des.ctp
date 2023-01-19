<?php
$this->Html->addCrumb(__('Home'), ['controller' => 'Pages', 'action' => 'home']);
$this->Html->addCrumb(__('List Orders'),array('controller' => 'Orders', 'action' => 'index'));
if(isset($order['Order']['id']) && !empty($order['Order']['id']))
	$this->Html->addCrumb(__('Order home DES'),array('controller'=>'Orders','action'=>'home', null, 'order_id='.$order['Order']['id']));
$this->Html->addCrumb(__('View ArticlesOrder DES'));
echo $this->Html->getCrumbList(array('class'=>'crumbs'));

if($user->organization['Organization']['hasFieldArticleCodice']=='Y' && $user->organization['Organization']['hasFieldArticleAlertToQta']=='Y')
	$colspan = '14';
else
if($user->organization['Organization']['hasFieldArticleCodice']=='N' && $user->organization['Organization']['hasFieldArticleAlertToQta']=='N')
	$colspan = '13';
else
	$colspan = '14';
?>
		
<h2 class="ico-edit-cart">
	<?php echo __('View ArticlesOrder DES').' ('.count($results).')';?>
	<div class="actions-img">
		<ul>
			<li><?php echo $this->Html->link(__('Order home'), array('controller' => 'Orders', 'action' => 'home', null, 'order_id='.$order['Order']['id']),array('class' => 'action actionWorkflow','title' => __('Order home'))); ?></li>
		</ul>
	</div>	
</h2>
<?php 
echo '<div class="contentMenuLaterale">';

echo $this->Form->create('ArticlesOrder',array('id' => 'formGas'));
echo $this->Form->hidden('article_order_key_selected',array('id' =>'article_order_key_selected', 'value'=>''));
echo $this->Form->hidden('article_id_selected',array('id' =>'article_id_selected', 'value'=>''));

echo '<fieldset>';

	echo $this->element('boxDesOrder', array('results' => $desOrdersResults));		
	
	echo '<div style="clear:both"></div>';
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
		  			?> 
					<div class="table-responsive"><table class="table table-hover">
					<tr>
							<th colspan="2"><?php echo __('N');?></th>
							<th>
								<input type="checkbox" id="article_order_key_selected_all" name="article_order_key_selected_all" value="ALL" />
								<img alt="Seleziona gli articoli da cancellare dall'ordine" src="<?php echo Configure::read('App.img.cake');?>/actions/24x24/button_cancel.png" />
								Seleziona gli articoli da cancellare dall'ordine
							</th>							
							<?php
							if($user->organization['Organization']['hasFieldArticleCodice']=='Y')
								echo '<th>'.__('codice').'</th>';
							?>
							<th>Nome prodotto</th>
							<th><?php echo __('Prezzo');?></th>
							<th><?php echo __('pezzi_confezione');?></th>
							<th><?php echo __('qta_minima');?></th>
							<th><?php echo __('qta_massima');?></th>
							<th><?php echo __('qta_multipli');?></th>
							<th><?php echo __('qta_minima_order');?></th>
							<th><?php echo __('qta_massima_order');?></th>
							<?php
							if($user->organization['Organization']['hasFieldArticleAlertToQta']=='Y')
								echo '<th>'.__('alert_to_qta').'</th>';
							?>
							<th>Stato</th>			
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
							echo '<td>'.$result['Article']['codice'].'</td>';
						
						echo '<td>';
						echo $result['Article']['name'].'&nbsp;';
						if(!empty($result['Article']['nota'])) echo '<div class="small">'.strip_tags($result['Article']['nota']).'</div>';
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
						
					echo '</tr>';
					echo '<tr class="trView" id="trViewId-'.$order['Order']['id'].'_'.$result['Article']['organization_id'].'_'.$result['Article']['id'].'">';
					echo '<td colspan="2"></td>';
					echo '<td colspan="'.$colspan.'" id="tdViewId-'.$order['Order']['id'].'_'.$result['Article']['organization_id'].'_'.$result['Article']['id'].'"></td>';
					echo '</tr>';
					
					endforeach;
					
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
					echo '<td nowrap>'.$article['ArticlesOrder']['prezzo_e'].'</td>';
					echo '<td style="text-align:center;">'.$article['ArticlesOrder']['pezzi_confezione'].'</td>';
					echo '<td style="text-align:center;">'.$article['ArticlesOrder']['qta_minima'].'</td>';
					echo '<td style="text-align:center;">'.$article['ArticlesOrder']['qta_massima'].'</td>';
					echo '<td style="text-align:center;">'.$article['ArticlesOrder']['qta_multipli'].'</td>';
					echo '<td style="text-align:center;">'.$article['ArticlesOrder']['qta_minima_order'].'</td>';
					echo '<td style="text-align:center;">'.$article['ArticlesOrder']['qta_massima_order'].'</td>';
						
					if($user->organization['Organization']['hasFieldArticleAlertToQta']=='Y')
						echo '<td>'.$article['Article']['alert_to_qta'].'</td>'; 
				
					echo '</tr>';
					endforeach;
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

echo $this->Form->end("Cancella/Associa all'ordine gli articoli selezionati", ['div'=> 'submitMultiple']);
echo '&nbsp;';
echo '<div><a class="btn btn-primary" href="index.php?option=com_cake&amp;controller=Orders&amp;action=home&amp;id='.$order['Order']['id'].'" title="'.__('OrderDesCreatedGoToHome').'">'.__('OrderDesCreatedGoToHome').'</a></div>';

echo '</div>';

$options = [];
echo $this->MenuOrders->drawWrapper($order_id, $options);

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
	

	
		if(i==$("input[name='article_order_key_selected']").length) {
			alert("Non puoi eliminare tutti gli articoli dall'ordine");
			return false;
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