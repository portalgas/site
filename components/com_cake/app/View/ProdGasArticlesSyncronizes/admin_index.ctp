<?php
/*
echo "<pre> \n";
print_r($articlesResults);
echo "</pre>";
*/

$this->Html->addCrumb(__('Home'), ['controller' => 'Pages', 'action' => 'home']);
$this->Html->addCrumb(__('ProdGasSupplier home'),array('controller' => 'ProdGasSuppliers', 'action' => 'index'));
$this->Html->addCrumb(__('ProdGasArticlesSyncronizesSimple'));
echo $this->Html->getCrumbList(array('class'=>'crumbs'));

echo '<div class="organizations">';
	
if(!$permission_to_continue) {
	echo $this->element('boxMsg',array('class_msg' => 'message', 'msg' => __('msgProdGasSuppliersNoOrganizationsOwnerSupplier')));
}
else {
	
	echo '<h2 class="ico-articles">';
	echo __('ProdGasArticlesSyncronizesSimple');
	echo '<div class="actions-img">';
	echo '<ul>';
	echo '<li>';
	echo $this->Html->link(__('ProdGasArticlesSyncronizesAdvanced'), array('action' => 'index', null, 'organization_id='.$organization_id.'&type=ADVANCED'), array('class' => 'action actionBackup','title' => __('ProdGasArticlesSyncronizesAdvanced')));
	echo '</li>';
	echo '</ul>';
	echo '</div>';
	echo '</h2>';

	if(!empty($organizationsResults)) {
		
		echo $this->Form->create('ProdGasArticlesSyncronize',array('id' => 'formGas', 'type' => 'get'));
		echo '<fieldset>';
			
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
				$('#formGas').submit();
			}
		}

		$(document).ready(function() {
			choiceOrganization();
		});	
		</script>	
		<?php
		echo '</fieldset>';
		echo $this->Form->end();		
	}
	else {
		
			echo $this->Form->create('ProdGasArticlesSyncronize',array('id' => 'formGas'));
			echo '<fieldset>';
	
			echo '<table cellpadding="0" cellspacing="0">';
			echo '<td style="width:50px;">';
			echo '<img width="50" src="'.Configure::read('App.web.img.upload.content').'/'.$organizations['Organization']['img1'].'" alt="'.$organizations['Organization']['name'].'" />';
			echo '</td>';
			echo '<td><h3>'.$organizations['Organization']['name'].'</h3></td>';
			echo '</table>';
			
			echo '<div class="panel-group">';
			echo '<div class="panel panel-primary">';
			echo '<div class="panel-heading">';
			echo '<h4 class="panel-title">';
			echo '	<a data-toggle="collapse" data-parent="#accordion" href="#collapse1"><i class="fa fa-lg fa-minus" aria-hidden="true"></i> Articoli del produttore già associati ('.count($articlesResults).')</a>';
			echo '</h4>';
			echo '</div>';
			echo '<div id="collapse1" class="panel-collapse collapse ';
			if(count($articlesResults)>0) echo 'in';
			echo '">';
			echo ' <div class="panel-body">';
		
		if(count($articlesResults)>0) {
		?>
			<div class="table-responsive"><table class="table table-hover">
			<tr>
				<th><?php echo __('N');?></th>
				<th colspan="2"><?php echo __('Name');?></th>
				<th><?php echo __('Conf');?></th>
				<th><?php echo __('PrezzoUnita');?></th>
				<th><?php echo __('ProdGasSupplierInArticlesOrder');?></th>
				<th><?php echo __('ProdGasSupplierInCart');?></th>
				<th><?php echo __('FlagPresenteArticlesorders');?> *</th>
				<th class="actions"><?php echo __('Actions');?></th>
				<th>
					<input type="checkbox" id="articles_just_associate_selected_all" name="articles_just_associate_selected_all" value="ALL" />
				</th>
			</tr>
			<?php
			foreach ($articlesResults as $numResult => $result):
				/*	
				echo "<pre>";
				print_r($result);
				echo "</pre>";
				*/
				
				/*
				 * se non esiste come articolo del produttore non lo visualizzo, si in modalita avanzata
				 */
				if(!empty($result['ProdGasArticle']['id'])) 
					$prodGasArticleExist = true;
				else
					$prodGasArticleExist = false;

					
				if($prodGasArticleExist) {
					
					$isArticleInCart = $result['isArticleInCart'];

					
					echo '<tr class="view">';
					
					echo '<td>'.($numResult+1).'</td>';

					echo '<td>';
					if(!empty($result['ProdGasArticle']['img1']) && file_exists(Configure::read('App.root').Configure::read('App.img.upload.prod_gas_article').DS.$result['ProdGasArticle']['organization_id'].DS.$result['ProdGasArticle']['img1'])) {
						echo '<img width="50" class="img-responsive-disabled userAvatar" src="'.Configure::read('App.server').Configure::read('App.web.img.upload.prod_gas_article').'/'.$result['ProdGasArticle']['organization_id'].'/'.$result['ProdGasArticle']['img1'].'" />';	
					}
					echo '</td>';

					echo '<td>';
					echo $result['ProdGasArticle']['name'];
					echo '</td>';
					echo '<td>';
					echo $this->App->getArticleConf($result['ProdGasArticle']['qta'], $result['ProdGasArticle']['um']);
					echo '</td>';				
					echo '<td>';
					echo $result['ProdGasArticle']['prezzo_e'];
					echo '</td>';	

					if($result['Article']['ArticlesOrder'])
						echo '<td class="stato_si" title="'.__('si').'" >';		
					else
						echo '<td class="stato_no" title="'.__('no').'" >';	
					
					if($isArticleInCart) 
						echo '<td class="stato_si" title="'.__('si').'" >';		
					else
						echo '<td class="stato_no" title="'.__('no').'" >';	
					
					if($result['Article']['flag_presente_articlesorders']=='Y') 
						echo '<td class="orderStatoPROCESSED-POST-DELIVERY" title="'.__('si').'" >';		
					else
						echo '<td class="orderStatoCLOSE" title="'.__('no').'" >';
					
					echo '<td class="actions-table-img">';
					/*
					se non esiste come articolo del produttore non lo visualizzo, si in modalita avanzata 
					if($prodGasArticleExist) {
					*/
						echo $this->Html->link(null, array('action' => 'syncronize_flag_presente_articlesorders', null, 'organization_id='.$organization_id.'&article_id='.$result['Article']['id'].'&type=SIMPLE'), array('class' => 'action actionOnOff', 'title' => __('ProdGasSyncronizeFlagPresenteArticlesordersButton')));
						echo $this->Html->link(null, array('action' => 'syncronize_update', null, 'organization_id='.$organization_id.'&prod_gas_article_id='.$result['ProdGasArticle']['id'].'&category_article_id='.$result['Article']['id'].'&type=SIMPLE'), array('class' => 'action actionSyncronize','id' => $result['ProdGasArticle']['id'], 'title' => __('ProdGasSyncronizeUpdate')));			
					/*
					}
					else {
						if(!$isArticleInCart)
							echo $this->Html->link(null, array('action' => 'syncronize_delete', null, 'organization_id='.$organization_id.'&article_id='.$result['Article']['id'].'&type=SIMPLE'), array('class' => 'action actionDelete', 'title' => __('ProdGasSyncronizeDelete')));
						else
							echo $this->Html->link(null, array('action' => 'syncronize_flag_presente_articlesorders', null, 'organization_id='.$organization_id.'&article_id='.$result['Article']['id'].'&type=SIMPLE'), array('class' => 'action actionOnOff', 'title' => __('ProdGasSyncronizeFlagPresenteArticlesordersButton')));
					}
					*/
					echo '</td>';
					echo '<td>';
					echo '<input type="checkbox" id="articles_just_associate_selected" name="articles_just_associate_selected" 
									data-attr-article_id="'.$result['Article']['id'].'" 
									data-attr-category_article_id="'.$result['Article']['category_article_id'].'" 
									data-attr-prod_gas_article_id="'.$result['ProdGasArticle']['id'].'" 
									/>';
					echo '</td>';		
				echo '</tr>';
			
				} // end if($prodGasArticleExist)
			endforeach;
			
			echo '</table></div>';
			
			echo '<div style="margin-left:25px;float:right;">';
			echo $this->Html->link(null, ['action' => 'syncronize_update_ids'], 
										 ['id' => 'articles_just_associate_syncronize_update', 'data-attr-organization_id' => $organization_id, 'class' => 'action actionSyncronize', 'title' => __('ProdGasSyncronizeUpdate')]).__('ProdGasSyncronizeUpdateIds');
			echo '</div>';				
			echo '<div style="float:right;">';
			echo $this->Html->link(null, ['action' => 'syncronize_flag_presente_articlesorders_ids'], 
										 ['id' => 'articles_just_associate_syncronize_flag_presente_articlesorders', 'data-attr-organization_id' => $organization_id, 'class' => 'action actionOnOff', 'title' => __('ProdGasSyncronizeFlagPresenteArticlesordersIdsButton')]).__('ProdGasSyncronizeFlagPresenteArticlesordersButton');
			echo '</div>';	
		} 
		else // if(count($articlesResults)>0)
			echo $this->element('boxMsg',array('class_msg' => 'message', 'msg' => "Non ci sono articoli del produttore."));


		echo '</div>';
		echo '</div>';
		echo '</div>';
		echo '<div class="panel panel-primary">';
		echo '<div class="panel-heading">';
		echo '<h4 class="panel-title">';
		echo '<a data-toggle="collapse" data-parent="#accordion" href="#collapse2"><i class="fa fa-lg fa-plus" aria-hidden="true"></i> Articoli del produttore ancora da associare ('.count($prodGasArticlesResults).')</a>';
		echo '</h4>';
		echo '</div>';
		echo '<div id="collapse2" class="panel-collapse collapse ';
		if(count($prodGasArticlesResults)>0) echo 'in';
		echo '">';
		echo '<div class="panel-body">';
			  
		if(count($prodGasArticlesResults)>0) {
		?>
			<div class="table-responsive"><table class="table table-hover">
			<tr>
					<th><?php echo __('N');?></th>
					<th colspan="2"><?php echo __('Name');?></th>
					<th><?php echo __('Conf');?></th>
					<th><?php echo __('PrezzoUnita');?></th>
					<th><?php echo __('Categories');?></th>
					<th class="actions"><?php echo __('Actions');?></th>
					<th>
						<input type="checkbox" id="articles_to_associate_selected_all" name="articles_to_associate_selected_all" value="ALL" />
					</th>
			</tr>
			<?php
			foreach ($prodGasArticlesResults as $numResult => $result):
							
				echo '<tr class="view">';
				
				echo '<td>'.($numResult+1).'</td>';

				echo '<td>';
				if(!empty($result['ProdGasArticle']['img1']) && file_exists(Configure::read('App.root').Configure::read('App.img.upload.prod_gas_article').DS.$result['ProdGasArticle']['organization_id'].DS.$result['ProdGasArticle']['img1'])) {
					echo '<img width="50" class="img-responsive-disabled userAvatar" src="'.Configure::read('App.server').Configure::read('App.web.img.upload.prod_gas_article').'/'.$result['ProdGasArticle']['organization_id'].'/'.$result['ProdGasArticle']['img1'].'" />';	
				}
				echo '</td>';
				echo '<td>';
				echo $result['ProdGasArticle']['name'];
				echo '</td>';
				echo '<td>';
				echo $this->App->getArticleConf($result['ProdGasArticle']['qta'], $result['ProdGasArticle']['um']);
				echo '</td>';			
				echo '<td>';
				echo $result['ProdGasArticle']['prezzo_e'];
				echo '</td>';

				echo '<td>';
				echo $this->Form->input('category_article_id', array('id' => 'category_article_id-'.$result['ProdGasArticle']['id'], 'options' => $categories, 'label' => false, 'escape' => false));
				echo '</td>';
				
				echo '<td class="actions-table-img">';
				echo $this->Html->link(null, array('action' => 'syncronize_insert', null, 'organization_id='.$organization_id.'&prod_gas_article_id='.$result['ProdGasArticle']['id']), array('class' => 'action actionAdd actionGetCategory','id' => $result['ProdGasArticle']['id'], 'title' => __('ProdGasSyncronizeInsert')));			
				echo '</td>';
				echo '<td>';
				echo '<input type="checkbox" id="articles_to_associate_selected" name="articles_to_associate_selected" 
						data-attr-prod_gas_article_id="'.$result['ProdGasArticle']['id'].'" />';
				echo '</td>';			
			echo '</tr>';
			
			endforeach;
			
			echo '</table></div>';
			
			echo '<div style="margin-left:25px;float:right;">';
			echo $this->Html->link(null, ['action' => 'syncronize_insert_ids'], 
										 ['id' => 'articles_to_associate_syncronize_insert', 'data-attr-organization_id' => $organization_id, 'class' => 'action actionAdd', 'title' => __('ProdGasSyncronizeInsert')]).__('ProdGasSyncronizeInsert');
			echo '</div>';
			echo '<div style="float:right;">';
			echo $this->Form->input('category_article_id', ['id' => 'category_article_all', 'options' => $categories, 'label' => false, 'escape' => false]);
			echo '</div>';

		} // end if(count($prodGasArticlesResults)>0)

		echo '</div>';
		echo '</div>';
		echo '</div>';

		
		echo '</div> <!-- panel-group --> ';

		echo '<div class="clearfix;"></div>';
		echo $this->element('legendaProdGasSupplierSyncronizeActions');
		echo '<div class="clearfix;"></div>';
		echo $this->element('legendaArticleFlagPresenteArticlesorders');	
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
		 * aggiungo la categoria
		 */
		$(".actionGetCategory").click(function() {
			
			var id = $(this).attr('id');
			var category_article_id = $('#category_article_id-'+id).val();
			if(category_article_id=='' || category_article_id==undefined) {
				alert("Devi indicare la categoria dell'articolo.");
				return false;
			}
			var href = $(this).attr('href');
			href = href + "&category_article_id="+category_article_id+"&type=<?php echo $type;?>";
			/* console.log(href); */
			
			$(this).attr('href', href);
			 
			return true;
			
		});
		
		/*
		 * Associati gli articoli del GAS non ancora associati ai tuoi articoli
		 */
		$(".actionGetListGas").click(function() {
			
			var id = $(this).attr('id');
			var article_id = $('#article_id-'+id).val();
			if(article_id=='' || article_id==undefined) {
				alert("Devi scegliere quale articolo del GAS associare un tuo articolo.");
				return false;
			}
			var href = $(this).attr('href');
			href = href + "&article_id="+article_id+"&type=<?php echo $type;?>";
			console.log(href); 
			
			$(this).attr('href', href);
			 
			return true;
			
		});	
		
		/*
		 * actions multiple
		 */
		$("#articles_just_associate_syncronize_update").click(function(event) { 
			// event.preventDefault(); 
			 
			var organization_ids = getOrganizationIds(this);
			var action = getAction(this);
			var category_article_id = getCategoryArticleId('articles_just_associate_selected');
			
			if(!getIds('articles_just_associate_selected')) {
				return false;
			}
		
			if(prod_gas_article_ids=='') {
				alert("Seleziona un articolo");
				return false;	
			}
			var href = action +"&organization_id="+organization_ids+"&prod_gas_article_ids="+prod_gas_article_ids+"&category_article_id="+category_article_id+"&type=<?php echo $type;?>";
			console.log(href); 
			
			$(this).attr('href', href);
			 
			return true;	
		});		
		
		$("#articles_just_associate_syncronize_flag_presente_articlesorders").click(function(event) { 
			// event.preventDefault(); 
		
			var organization_ids = getOrganizationIds(this);
			var action = getAction(this);
		
			if(!getIds('articles_just_associate_selected'))
				return false;
			
			var href = action +"&organization_id="+organization_ids+"&article_ids="+article_ids+"&type=<?php echo $type;?>";
			console.log(href); 
			
			$(this).attr('href', href);
					
			return true;
		});	
		
		$("#articles_to_associate_syncronize_insert").click(function(event) { 
			// event.preventDefault(); 

			var category_article_id = $('#category_article_all').val();
			if(category_article_id=='' || category_article_id==undefined) {
				alert("Devi indicare la categoria dell'articolo.");
				return false;
			}	

			var organization_ids = getOrganizationIds(this);
			var action = getAction(this);
			
			if(!getIds('articles_to_associate_selected'))
				return false;

			var href = action +"&organization_id="+organization_ids+"&prod_gas_article_ids="+prod_gas_article_ids+"&category_article_id="+category_article_id+"&type=<?php echo $type;?>";
			console.log(href); 
			
			$(this).attr('href', href);
			
			return true;
		});	
	});

	var article_ids = '';
	var prod_gas_article_ids = '';

	function getOrganizationIds(field) {
		var organization_ids = '';
		if($(field).attr('data-attr-organization_id'))
			organization_ids += $(field).attr('data-attr-organization_id')+',';	
		console.log("organization_ids "+organization_ids);
		if (typeof(organization_ids) != 'undefined' && organization_ids!='') {
			organization_ids = organization_ids.substring(0,organization_ids.length-1);		
		}
		return organization_ids;
	}

	function getAction(field) {
		var action = '';
		action = $(field).attr('href');
		console.log("action "+action);
		
		return action;
	}

	function getCategoryArticleId(field_name) {
		var categoryArticleId = '';
		var elem = $("input[name='"+field_name+"']");
		if(elem.attr('data-attr-category_article_id'))
			categoryArticleId = elem.attr('data-attr-category_article_id');
		console.log("categoryArticleId "+categoryArticleId);
		
		return categoryArticleId;
	}

	function getIds(field_name) {

		article_ids = '';
		prod_gas_article_ids = '';
		
		for(i = 0; i < $("input[name='"+field_name+"']:checked").length; i++) {
			var elem = $("input[name='"+field_name+"']:checked").eq(i);

			if(elem.attr('data-attr-article_id'))
				article_ids += elem.attr('data-attr-article_id')+',';
			if(elem.attr('data-attr-prod_gas_article_id'))
				prod_gas_article_ids += elem.attr('data-attr-prod_gas_article_id')+',';
		}

		console.log('['+field_name+"] article_ids "+article_ids);
		console.log('['+field_name+"] prod_gas_article_ids "+prod_gas_article_ids);
		
		if (typeof(article_ids) != 'undefined' && article_ids!='') {
			article_ids = article_ids.substring(0,article_ids.length-1);		
		}
		else
			article_ids=='';
		
		if (typeof(prod_gas_article_ids) != 'undefined' && prod_gas_article_ids!='') {
			prod_gas_article_ids = prod_gas_article_ids.substring(0,prod_gas_article_ids.length-1);		
		}
		else
			prod_gas_article_ids = '';

		if(prod_gas_article_ids=='' && article_ids=='') {
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