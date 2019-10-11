<?php
/*
echo "<pre> \n";
print_r($articlesResults);
echo "</pre>";
*/

$this->Html->addCrumb(__('Home'), ['controller' => 'Pages', 'action' => 'home']);
$this->Html->addCrumb(__('ProdGasSupplier home'),array('controller' => 'ProdGasSuppliers', 'action' => 'index'));
$this->Html->addCrumb(__('ProdGasArticlesSyncronizesArticlesOrders'));
echo $this->Html->getCrumbList(array('class'=>'crumbs'));


echo '<div class="organizations">';
	
if(!$permission_to_continue) {
	echo $this->element('boxMsg',array('class_msg' => 'message', 'msg' => __('msgProdGasSuppliersNoOrganizationsOwnerSupplier')));
}
else {

	echo $this->Form->create('ProdGasArticlesSyncronize',array('id' => 'formGasPre', 'type' => 'get'));
	echo '<fieldset>';
	
	if(!empty($organizationsResults)) {
			
		$options =  array('id' => 'organization_id',
						  'empty' => Configure::read('option.empty'),
						  'onChange' => 'javascript:choiceOrganization(this);',
						  'options' => $organizationsResults,
						  'value' => $organization_id,
						  'class'=> 'selectpicker', 'data-live-search' => true);
		echo $this->Form->input('organization_id',$options);	
		?>
		<script type="text/javascript">
		function choiceOrganization() {
			var organization_id = $("#organization_id").val();	
			if(organization_id!='') {
				$('#formGasPre').submit();
			}
		}
		</script>	
		<?php	
	}

	if(!empty($organization_id)) {
				
		if(!empty($orderResults)) {

			$options =  array('id' => 'order_id',
							  'empty' => Configure::read('option.empty'),
							  'onChange' => 'choiceOrder(this);',
							  'options' => $orderResults,
							  'value' => $order_id,
							  'class'=> 'selectpicker', 'data-live-search' => true);
			echo $this->Form->input('order_id',$options);	
			?>
			<script type="text/javascript">
			function choiceOrder() {
				var order_id = $("#order_id").val();	
				if(order_id!='') {
					$('#formGasPre').submit();
				}
			}
			</script>	
			<?php
		}
		else
			echo $this->element('boxMsg',array('class_msg' => 'message', 'msg' => __('msgProdGasSuppliersNoDeliveryWithOrders')));
	}	
	echo '</fieldset>';
	echo $this->Form->end();
	
	if(!empty($organization_id) && !empty($order_id)) {
		
		echo $this->Form->create('ProdGasArticlesSyncronize',array('id' => 'formGas'));
		echo '<fieldset>';

		echo '<div class="panel-group">';
	    echo '<div class="panel panel-primary">';
		echo '<div class="panel-heading">';
		echo '<h4 class="panel-title">';
		echo '	<a data-toggle="collapse" data-parent="#accordion" href="#collapse1"><i class="fa fa-lg fa-minus" aria-hidden="true"></i> Articoli inseriri nell\'ordine ('.count($articlesResults).')</a>';
		echo '</h4>';
		echo '</div>';
		echo '<div id="collapse1" class="panel-collapse collapse ';
		echo 'in';
		echo '">';
		echo ' <div class="panel-body">';
	
		if(count($articlesResults)>0) {
		?>
		<div class="table-responsive"><table class="table table-hover">
		<tr>
			<th rowspan="2"><?php echo __('N');?></th>
			<th class="title prodgas" colspan="4"><?php echo __('ProdGasSupplierArticles');?></th>
			<th class="title" colspan="3"><?php echo __('ProdGasSupplierArticlesOrganization');?></th>
			<th style="width:5px;"></th>
			<th colspan="2" class="title"><?php echo __('ProdGasSupplierInCart');?></th>
			<th rowspan="2" class="actions"><?php echo __('Actions');?></th>
		</tr>
		<tr>
			<th class="prodgas" colspan="2"><?php echo __('Name');?></th>
			<th class="prodgas"><?php echo __('Conf');?></th>
			<th class="prodgas"><?php echo __('PrezzoUnita');?></th>
			<th style="width:5px;"></th>
			<th><?php echo __('Name');?></th>				
			<th><?php echo __('Conf');?></th>
			<th><?php echo __('PrezzoUnita');?></th>
			<th colspan="2" style=text-align:right;">
				<input type="checkbox" id="articles_just_associate_selected_all" name="articles_just_associate_selected_all" value="ALL" />
			</th>			
		</tr>
		<?php
		foreach ($articlesResults as $numResult => $result):
						
			if(!empty($result['ProdGasArticle']['id'])) 
				$prodGasArticleExist = true;
			else
				$prodGasArticleExist = false;

			$isArticleInCart = $result['isArticleInCart'];

			
			echo '<tr class="view">';
			
			echo '<td>'.($numResult+1).'</td>';

			echo '<td>';
			if(!empty($result['ProdGasArticle']['img1']) && file_exists(Configure::read('App.root').Configure::read('App.img.upload.prod_gas_article').DS.$result['ProdGasArticle']['organization_id'].DS.$result['ProdGasArticle']['img1'])) {
				echo '<img width="50" class="img-responsive-disabled userAvatar" src="'.Configure::read('App.server').Configure::read('App.web.img.upload.prod_gas_article').'/'.$result['ProdGasArticle']['organization_id'].'/'.$result['ProdGasArticle']['img1'].'" />';	
			}
			echo '</td>';
			if($prodGasArticleExist) {
				echo '<td>';
				echo $result['ProdGasArticle']['name'];
				echo '</td>';
				echo '<td>';
				echo $this->App->getArticleConf($result['ProdGasArticle']['qta'], $result['ProdGasArticle']['um']);
				echo '</td>';				
				echo '<td>';
				echo $result['ProdGasArticle']['prezzo_e'];
				echo '</td>';	
			}
			else {
				echo '<td colspan="3">';
				echo "Articolo non più presente nell'archivio del produttore";
				echo '</td>';
			}

			/*
			 * ctrl differenza tra articolo produttore q quello del gas
			 */
			$differente = false; 
			if($prodGasArticleExist) {
				if($result['ProdGasArticle']['name']!=$result['ArticlesOrder']['name'] || 
				   $result['ProdGasArticle']['prezzo']!=$result['ArticlesOrder']['prezzo']
				  )
				  $differente = true;
			} 
			echo '<td style="';
			if($differente)
				echo 'background-color:red;';
			else	
				echo 'background-color:green;';
			echo '"></td>';

			echo '<td>';
			echo $result['ArticlesOrder']['name'];
			echo '</td>';
			echo '<td>';
			echo $this->App->getArticleConf($result['Article']['qta'], $result['Article']['um']);
			echo '</td>';			
			echo '<td>';
			echo $result['ArticlesOrder']['prezzo_e'];
			echo '</td>';
			
			if($isArticleInCart) 
				echo '<td class="stato_si" title="'.__('si').'" >';		
			else
				echo '<td class="stato_no" title="'.__('no').'" >';	
			
			echo '<td style=text-align:right;';
			if($isArticleInCart) echo 'background-color:red;';
			echo '">';
			echo '<input type="checkbox" id="articles_just_associate_selected" name="articles_just_associate_selected" 
							data-attr-article_id="'.$result['ArticlesOrder']['article_id'].'" 
							data-attr-article_organization_id="'.$result['ArticlesOrder']['article_organization_id'].'" 
							data-attr-prod_gas_article_id="'.$result['ProdGasArticle']['id'].'" 
							/>';
			echo '</td>';
			echo '<td class="actions-table-img">';
			if($prodGasArticleExist) {
				echo $this->Html->link(null, array('action' => 'syncronize_articles_orders_update', null, 
									'organization_id='.$organization_id.'&order_id='.$order_id.'&article_organization_id='.$result['ArticlesOrder']['article_organization_id'].'&article_id='.$result['ArticlesOrder']['article_id'].'&prod_gas_article_id='.$result['ProdGasArticle']['id']), array('class' => 'action actionSyncronize','id' => $result['ProdGasArticle']['id'], 'title' => __('ProdGasSyncronizeArticlesOrdersUpdate')));			
			}

			$opts = ['class' => 'action actionDelete', 'title' => __('ProdGasSyncronizeArticlesOrdersDelete')]; 
			if($isArticleInCart) 
				$opts += ['confirm' => "L'articolo ha già degli acquisti, sei sicuro di volerli eliminare?"];
			
			echo $this->Html->link(null, array('action' => 'syncronize_articles_orders_delete', null, 
							'organization_id='.$organization_id.'&order_id='.$order_id.'&article_organization_id='.$result['ArticlesOrder']['article_organization_id'].'&article_id='.$result['ArticlesOrder']['article_id']), 
							$opts);
			echo '</td>';
		echo '</tr>';
		
		endforeach;
		
		echo '</table></div>';
		
		echo '<div style="margin-left:25px;float:right;">';
		echo $this->Html->link(null, ['action' => 'syncronize_articles_orders_update_ids'], 
									 ['id' => 'articles_just_associate_syncronize_update', 
									 'data-attr-organization_id' => $organization_id, 'data-attr-order_id' => $result['ArticlesOrder']['order_id'],
									 'class' => 'action actionSyncronize', 'title' => __('ProdGasSyncronizeArticlesOrdersUpdate')]).__('ProdGasSyncronizeArticlesOrdersUpdateIds'); 
		echo '</div>';				
	} 
	else // if(count($articlesResults)>0)
		echo $this->element('boxMsg',array('class_msg' => 'message', 'msg' => "Non ci sono articoli del produttore associati all'ordine."));


	echo '</div>';
	echo '</div>';
	echo '</div>';
	
	
	
	    echo '<div class="panel panel-primary">';
		echo '<div class="panel-heading">';
		echo '<h4 class="panel-title">';
		echo '	<a data-toggle="collapse" data-parent="#accordion" href="#collapse2"><i class="fa fa-lg fa-minus" aria-hidden="true"></i> Articoli del GAS da inserire nell\'ordine ('.count($articles).')</a>';
		echo '</h4>';
		echo '</div>';
		echo '<div id="collapse2" class="panel-collapse collapse ';
		echo 'in';
		echo '">';
		echo ' <div class="panel-body">';
	
		if(count($articles)>0) {
		?>
			<div class="table-responsive"><table class="table table-hover">
			<tr>
					<th><?php echo __('N');?></th>
					<th colspan="2"><?php echo __('Name');?></th>
					<th><?php echo __('Conf');?></th>
					<th><?php echo __('PrezzoUnita');?></th>
					<th class="actions"><?php echo __('Actions');?></th>
					<th>
						<input type="checkbox" id="articles_to_associate_selected_all" name="articles_to_associate_selected_all" value="ALL" />
					</th>
			</tr>
			<?php
			foreach ($articles as $numResult => $result):
								
				echo '<tr class="view">';
				
				echo '<td>'.($numResult+1).'</td>';

				echo '<td>';
				if(!empty($result['Article']['img1']) && file_exists(Configure::read('App.root').Configure::read('App.img.upload.prod_gas_article').DS.$result['Article']['organization_id'].DS.$result['Article']['img1'])) {
					echo '<img width="50" class="img-responsive-disabled userAvatar" src="'.Configure::read('App.server').Configure::read('App.web.img.upload.prod_gas_article').'/'.$result['Article']['organization_id'].'/'.$result['Article']['img1'].'" />';	
				}
				echo '</td>';
				echo '<td>';
				echo $result['Article']['name'];
				echo '</td>';
				echo '<td>';
				echo $this->App->getArticleConf($result['Article']['qta'], $result['Article']['um']);
				echo '</td>';			
				echo '<td>';
				echo $result['Article']['prezzo_e'];
				echo '</td>';
				
				echo '<td class="actions-table-img">';
				echo $this->Html->link(null, array('action' => 'syncronize_articles_orders_insert', null, 'organization_id='.$organization_id.'&order_id='.$order_id.'&prod_gas_article_id='.$result['Article']['prod_gas_article_id']), array('class' => 'action actionAdd','id' => $result['Article']['prod_gas_article_id'], 'title' => __('ProdGasSyncronizeArticlesOrdersInsert')));			
				echo '</td>';
				echo '<td>';
				echo '<input type="checkbox" id="articles_to_associate_selected" name="articles_to_associate_selected" 
						data-attr-prod_gas_article_id="'.$result['Article']['prod_gas_article_id'].'" />';
				echo '</td>';			
			echo '</tr>';
			
			endforeach;
			
			echo '</table></div>';
			
			echo '<div style="margin-left:25px;float:right;">';
			echo $this->Html->link(null, ['action' => 'syncronize_articles_orders_insert_ids'], 
										 ['id' => 'articles_to_associate_syncronize_insert', 'data-attr-organization_id' => $organization_id, 'data-attr-order_id' => $order_id, 'class' => 'action actionAdd', 'title' => __('ProdGasSyncronizeInsert')]).__('ProdGasSyncronizeInsert');
			echo '</div>';
	} 
	else // if(count($articlesResults)>0)
		echo $this->element('boxMsg',array('class_msg' => 'message', 'msg' => "Non ci sono articoli del produttore associati all'ordine."));


	echo '</div>';
	echo '</div>';
	echo '</div>';



	
	echo '</div> <!-- panel-group --> ';

	echo '<div class="clearfix;"></div>';
	echo $this->element('legendaProdGasSupplierSyncronizeArticlesOrdersActions');
	echo '<div class="clearfix;"></div>';
	
	echo '</fieldset>';
	echo $this->Form->end();
	?>
	<script type="text/javascript">
	$(document).ready(function() {
		
		$('#articles_just_associate_selected_all').click(function () {
			var checked = $("input[name='articles_just_associate_selected_all']:checked").val();
			if(checked=='ALL')
				$('input[name=articles_just_associate_selected]').prop('checked',true);
			else
				$('input[name=articles_just_associate_selected]').prop('checked',false);
		});
		
		$('#articles_to_associate_selected_all').click(function () {
			var checked = $("input[name='articles_to_associate_selected_all']:checked").val();
			if(checked=='ALL')
				$('input[name=articles_to_associate_selected]').prop('checked',true);
			else
				$('input[name=articles_to_associate_selected]').prop('checked',false);
		});
		
		/*
		 * actions multiple
		 */
		$("#articles_just_associate_syncronize_update").click(function(event) { 
			// event.preventDefault(); 
			 
			var organization_id = getOrganizationId(this);
			var order_id = getOrderId(this);
			var action = getAction(this);
			
			if(!getIds('articles_just_associate_selected')) {
				return false;
			}
		
			if(prod_gas_article_ids=='') {
				alert("Seleziona un articolo");
				return false;	
			}	
			var href = action +"&organization_id="+organization_id+"&order_id="+order_id+"&article_organization_ids="+article_organization_ids+"&article_ids="+article_ids+"&prod_gas_article_ids="+prod_gas_article_ids;
			console.log(href); 
			
			$(this).attr('href', href);
			 
			return true;	
		});
		
		$("#articles_to_associate_syncronize_insert").click(function(event) { 
			// event.preventDefault(); 
			 
			var organization_id = getOrganizationId(this);
			var order_id = getOrderId(this);
			var action = getAction(this);
			
			if(!getIds('articles_to_associate_selected')) {
				return false;
			}
		
			if(prod_gas_article_ids=='') {
				alert("Seleziona un articolo");
				return false;	
			}	
			var href = action +"&organization_id="+organization_id+"&order_id="+order_id+"&prod_gas_article_ids="+prod_gas_article_ids;
			console.log(href); 
			
			$(this).attr('href', href);
			 
			return true;	
		});			
	});

	var article_ids = '';
	var article_organization_ids = '';
	var prod_gas_article_ids = '';

	function getOrganizationId(field) {
		var organization_id = '';
		if($(field).attr('data-attr-organization_id'))
			organization_id += $(field).attr('data-attr-organization_id');	
		console.log("organization_id "+organization_id);
		return organization_id;
	}
	
	function getOrderId(field) {
		var order_id = '';
		if($(field).attr('data-attr-order_id'))
			order_id += $(field).attr('data-attr-order_id');	
		console.log("order_id "+order_id);
		return order_id;
	}

	function getAction(field) {
		var action = '';
		action = $(field).attr('href');
		console.log("action "+action);
		
		return action;
	}

	function getIds(field_name) {

		article_ids = '';
		article_organization_ids = '';
		prod_gas_article_ids = '';
		
		for(i = 0; i < $("input[name='"+field_name+"']:checked").length; i++) {
			var elem = $("input[name='"+field_name+"']:checked").eq(i);

			if(elem.attr('data-attr-article_id'))
				article_ids += elem.attr('data-attr-article_id')+',';
			if(elem.attr('data-attr-article_organization_id'))
				article_organization_ids += elem.attr('data-attr-article_organization_id')+',';
			if(elem.attr('data-attr-prod_gas_article_id'))
				prod_gas_article_ids += elem.attr('data-attr-prod_gas_article_id')+',';
		}

		console.log('['+field_name+"] article_ids "+article_ids);
		console.log('['+field_name+"] article_organization_ids "+article_organization_ids);
		console.log('['+field_name+"] prod_gas_article_ids "+prod_gas_article_ids);
		
		if (typeof(article_ids) != 'undefined' && article_ids!='') {
			article_ids = article_ids.substring(0,article_ids.length-1);		
		}
		else
			article_ids=='';
			
		if (typeof(article_organization_ids) != 'undefined' && article_organization_ids!='') {
			article_organization_ids = article_organization_ids.substring(0,article_organization_ids.length-1);		
		}
		else
			article_organization_ids=='';
		
		if (typeof(prod_gas_article_ids) != 'undefined' && prod_gas_article_ids!='') {
			prod_gas_article_ids = prod_gas_article_ids.substring(0,prod_gas_article_ids.length-1);		
		}
		else
			prod_gas_article_ids = '';

		if(prod_gas_article_ids=='' && article_ids=='' && article_organization_ids=='') {
			alert("Devi selezionate almeno un articolo");
			return false;
		}
		
		return true;	
	}
	</script>

	<style>
	th.prodgas {
		background-color: #e5e5e5;	
	}
	th.title {
		border-bottom: medium none;
		text-align: center;	
	}
	</style>
<?php
	} // end if(!empty($organizationsResults)) 
} // end permission_to_continue

echo '</div>';
?>	