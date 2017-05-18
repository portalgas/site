<?php
/*
echo "<pre> \n";
print_r($articlesResults);
echo "</pre>";
*/

$this->Html->addCrumb(__('Home'),array('controller' => 'Pages', 'action' => 'home'));
$this->Html->addCrumb(__('ProdGasSupplier home'),array('controller' => 'ProdGasSuppliers', 'action' => 'index'));
$this->Html->addCrumb(__('ProdGasArticlesSyncronizes'));
echo $this->Html->getCrumbList(array('class'=>'crumbs'));


echo '<div class="organizations">';

echo '<table cellpadding="0" cellspacing="0">';
echo '<td style="width:50px;">';
echo '<img width="50" src="'.Configure::read('App.web.img.upload.content').'/'.$organizations['Organization']['img1'].'" alt="'.$organizations['Organization']['name'].'" />';
echo '</td>';
echo '<td><h3>'.$organizations['Organization']['name'].'</h3></td>';
echo '</table>';

echo $this->Form->create('ProdGasArticlesSyncronize',array('id' => 'formGas'));

echo '<fieldset>';

	if(count($articlesResults)>0) {
		
		echo "<h3>Articoli del produttore già associati</h3>";
	?>
		<table cellpadding="0" cellspacing="0">
		<tr>
			<th rowspan="2"><?php echo __('N');?></th>
			<th class="title prodgas" colspan="4"><?php echo __('ProdGasSupplierArticles');?></th>
			<th class="title" colspan="4"><?php echo __('ProdGasSupplierArticlesOrganization');?></th>
			<th rowspan="2"><?php echo __('ProdGasSupplierInArticlesOrder');?></th>
			<th rowspan="2"><?php echo __('ProdGasSupplierInCart');?></th>
			<th rowspan="2"><?php echo __('FlagPresenteArticlesorders');?> *</th>
			<th rowspan="2" class="actions"><?php echo __('Actions');?></th>		
		</tr>
		<tr>
			<th class="prodgas" colspan="2"><?php echo __('Name');?></th>
			<th class="prodgas"><?php echo __('Conf');?></th>
			<th class="prodgas"><?php echo __('PrezzoUnita');?></th>
			<th><?php echo __('Categories');?></th>					
			<th><?php echo __('Name');?></th>				
			<th><?php echo __('Conf');?></th>
			<th><?php echo __('PrezzoUnita');?></th>
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
			if(!empty($result['ProdGasArticle']['img1']) && file_exists(Configure::read('App.root').Configure::read('App.img.upload.prod_gas_article').DS.$result['ProdGasArticle']['supplier_id'].DS.$result['ProdGasArticle']['img1'])) {
				echo '<img width="50" class="userAvatar" src="'.Configure::read('App.server').Configure::read('App.web.img.upload.prod_gas_article').'/'.$result['ProdGasArticle']['supplier_id'].'/'.$result['ProdGasArticle']['img1'].'" />';	
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
				echo '<td>';
				echo $this->Form->input('category_article_id', array('id' => 'category_article_id-'.$result['ProdGasArticle']['id'], 'options' => $categories, 'default' => $result['Article']['category_article_id'], 'label' => false, 'escape' => false));
				echo '</td>';
			}
			else {
				echo '<td colspan="4">';
				echo "Articolo non più presente nell'archivio del produttore";
				echo '</td>';
			}

			echo '<td>';
			echo $result['Article']['name'];
			echo '</td>';
			echo '<td>';
			echo $this->App->getArticleConf($result['Article']['qta'], $result['Article']['um']);
			echo '</td>';			
			echo '<td>';
			echo $result['Article']['prezzo_e'];
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
			if($prodGasArticleExist) {
				if($result['Article']['flag_presente_articlesorders']=='Y') 
					echo $this->Html->link(null, array('action' => 'syncronize_flag_presente_articlesorders', null, 'organization_id='.$organization_id.'&article_id='.$result['Article']['id']), array('class' => 'action actionClose', 'title' => __('ProdGasSyncronizeFlagPresenteArticlesordersButton')));
				echo $this->Html->link(null, array('action' => 'syncronize_update', null, 'organization_id='.$organization_id.'&prod_gas_article_id='.$result['ProdGasArticle']['id']), array('class' => 'action actionCopy actionGetCategory','id' => $result['ProdGasArticle']['id'], 'title' => __('ProdGasSyncronizeUpdate')));			
			}
			else {
				if(!$isArticleInCart)
					echo $this->Html->link(null, array('action' => 'syncronize_delete', null, 'organization_id='.$organization_id.'&article_id='.$result['Article']['id']), array('class' => 'action actionDelete', 'title' => __('ProdGasSyncronizeDelete')));
				else
					echo $this->Html->link(null, array('action' => 'syncronize_flag_presente_articlesorders', null, 'organization_id='.$organization_id.'&article_id='.$result['Article']['id']), array('class' => 'action actionClose', 'title' => __('ProdGasSyncronizeFlagPresenteArticlesordersButton')));
			}
			echo '</td>';
		echo '</tr>';
		
		endforeach;
		
		echo '</table>';
		
	} 
	else // if(count($articlesResults)>0)
		echo $this->element('boxMsg',array('class_msg' => 'message', 'msg' => "Non ci sono articoli del produttore."));

		
	if(count($prodGasArticlesResults)>0) {
	
		echo "<h3>Articoli del produttore ancora da associare</h3>";
	?>
		<table cellpadding="0" cellspacing="0">
		<tr>
				<th><?php echo __('N');?></th>
				<th colspan="2"><?php echo __('Name');?></th>
				<th><?php echo __('Conf');?></th>
				<th><?php echo __('PrezzoUnita');?></th>
				<th><?php echo __('Categories');?></th>
				<th class="actions"><?php echo __('Actions');?></th>		
		</tr>
		<?php
		foreach ($prodGasArticlesResults as $numResult => $result):
						
			echo '<tr class="view">';
			
			echo '<td>'.($numResult+1).'</td>';

			echo '<td>';
			if(!empty($result['ProdGasArticle']['img1']) && file_exists(Configure::read('App.root').Configure::read('App.img.upload.prod_gas_article').DS.$result['ProdGasArticle']['supplier_id'].DS.$result['ProdGasArticle']['img1'])) {
				echo '<img width="50" class="userAvatar" src="'.Configure::read('App.server').Configure::read('App.web.img.upload.prod_gas_article').'/'.$result['ProdGasArticle']['supplier_id'].'/'.$result['ProdGasArticle']['img1'].'" />';	
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
		echo '</tr>';
		
		endforeach;
		
		echo '</table>';
		
	} 

	echo $this->element('legendaProdGasSupplierSyncronizeActions');
	
	echo $this->element('legendaArticleFlagPresenteArticlesorders');
		
		
		
		
	if(count($articlesToAssociateListResults)>0) {
	
		echo '<div class="clearfix;"></div>';
		echo "<h3>Articoli del GAS ancora da associare</h3>";
	?>
		<table cellpadding="0" cellspacing="0">
		<tr>
				<th><?php echo __('N');?></th>
				<th colspan="2"><?php echo __('Articolo del produttore');?></th>
				<th><?php echo __('Conf');?></th>
				<th><?php echo __('PrezzoUnita');?></th>
				<th><?php echo __('Articolo del G.A.S.');?></th>
				<th class="actions"><?php echo __('Actions');?></th>		
		</tr>
		<?php
		foreach ($prodGasArticlesAllResults as $numResult => $result):
						
			echo '<tr class="view">';
			
			echo '<td>'.($numResult+1).'</td>';

			echo '<td>';
			if(!empty($result['ProdGasArticle']['img1']) && file_exists(Configure::read('App.root').Configure::read('App.img.upload.prod_gas_article').DS.$result['ProdGasArticle']['supplier_id'].DS.$result['ProdGasArticle']['img1'])) {
				echo '<img width="50" class="userAvatar" src="'.Configure::read('App.server').Configure::read('App.web.img.upload.prod_gas_article').'/'.$result['ProdGasArticle']['supplier_id'].'/'.$result['ProdGasArticle']['img1'].'" />';	
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
			echo $this->Form->input('article_id', array('id' => 'article_id-'.$result['ProdGasArticle']['id'], 'options' => $articlesToAssociateListResults, 'label' => false, 'escape' => false));
			echo '</td>';
			
			echo '<td class="actions-table-img">';
			echo $this->Html->link(null, array('action' => 'import_article_gas', null, 'organization_id='.$organization_id.'&prod_gas_article_id='.$result['ProdGasArticle']['id']), array('class' => 'action actionBackup actionGetListGas','id' => $result['ProdGasArticle']['id'], 'title' => __('ProdGasImportArticleGas')));			
			echo '</td>';
		echo '</tr>';
		
		endforeach;
		
		echo '</table>';
		
	} 
	
			
echo '</fieldset>';
echo $this->Form->end();
echo '</div>';
?>

<script type="text/javascript">
jQuery(document).ready(function() {
		
	/*
	 * aggiungo la categoria
	 */
	jQuery(".actionGetCategory").click(function() {
		
		var id = jQuery(this).attr('id');
		var category_article_id = jQuery('#category_article_id-'+id).val();
		if(category_article_id=='' || category_article_id==undefined) {
			alert("Devi indicare la categoria dell'articolo.");
			return false;
		}
		var href = jQuery(this).attr('href');
		href = href + "&category_article_id="+category_article_id;
		/* console.log(href); */
		
		jQuery(this).attr('href', href);
		 
		return true;
		
	});
	
	/*
	 * Associati gli articoli del GAS non ancora associati ai tuoi articoli
	 */
	jQuery(".actionGetListGas").click(function() {
		
		var id = jQuery(this).attr('id');
		var article_id = jQuery('#article_id-'+id).val();
		if(article_id=='' || article_id==undefined) {
			alert("Devi scegliere quale articolo del GAS associare un tuo articolo.");
			return false;
		}
		var href = jQuery(this).attr('href');
		href = href + "&article_id="+article_id;
		console.log(href); 
		
		jQuery(this).attr('href', href);
		 
		return true;
		
	});	
});
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