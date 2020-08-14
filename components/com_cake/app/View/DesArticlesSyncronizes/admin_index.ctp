<?php
//$this->App::d($mySuppliersOrganizationResults);

$this->Html->addCrumb(__('Home'), ['controller' => 'Pages', 'action' => 'home']);
$this->Html->addCrumb(__('Des'),array('controller' => 'Des', 'action' => 'index'));
$this->Html->addCrumb(__('DesArticlesSyncronizesIntro'),array('controller' => 'DesArticlesSyncronizes', 'action' => 'intro'));
$this->Html->addCrumb(__('DesArticlesSyncronizes'));
echo $this->Html->getCrumbList(array('class'=>'crumbs'));

echo '<div class="organizations">';


echo '<div class="table-responsive"><table class="table table-striped table-hover">';
echo '<tr>';
echo '<th>'.__('G.A.S.').'</th>';
echo '<td>';
echo ' <img width="50" class="img-responsive-disabled userAvatar" src="'.Configure::read('App.web.img.upload.content').'/'.$organizationsResults['Organization']['img1'].'" alt="'.$organizationsResults['Organization']['name'].'" /> ';	
echo '</td>';
echo '<td>';
echo $organizationsResults['Organization']['name'];
echo '</td>';
echo '</tr>';

echo '<tr>';
echo '<th>'.__('SuppliersOrganization').'</th>';
echo '<td>';
if(!empty($suppliersOrganizationResults['Supplier']['img1']) && file_exists(Configure::read('App.root').Configure::read('App.img.upload.content').'/'.$suppliersOrganizationResults['Supplier']['img1']))
	echo '<img width="50" class="img-responsive-disabled userAvatar" src="'.Configure::read('App.server').Configure::read('App.web.img.upload.content').'/'.$suppliersOrganizationResults['Supplier']['img1'].'" />';	
echo '</td>';
echo '<td>';
echo $suppliersOrganizationResults['SuppliersOrganization']['name'];
echo '<td>';
echo '</tr>';
echo '</table></div>';

echo $this->Form->create('SuppliersOrganization');
echo '<fieldset>';

if($mySuppliersOrganizationResults['SuppliersOrganization']['stato']=='N') {
	$msg = "Il produttore ".$mySuppliersOrganizationResults['SuppliersOrganization']['name']." ha lo stato <b>No</b>, riattivalo se desideri utilizzarlo<br />";
	$msg .= "Per riattivarlo <a href='index.php?option=com_cake&controller=SuppliersOrganizations&action=edit&id=".$mySuppliersOrganizationResults['SuppliersOrganization']['id']."'>clicca qui</a>";
	echo $this->element('boxMsg',array('class_msg' => 'message', 'msg' => $msg));
}
else {


	echo '<div class="panel-group">';
	echo '  <div class="panel panel-primary">';
	echo '<div class="panel-heading">';
	echo '	<h4 class="panel-title">';
	echo '		<a data-toggle="collapse" data-parent="#accordion" href="#collapse1"><i class="fa fa-lg fa-minus" aria-hidden="true"></i> Elenco degli articoli già associati al tuo G.A.S. ('.count($myArticles).')</a>';
	echo '</h4>';
	echo '</div>';
	echo '<div id="collapse1" class="panel-collapse collapse in">';
	echo '<div class="panel-body">';
		  
		/*
		 * articoli da sincronizzare 
		 */
		if(count($articlesMasters)>0 && count($myArticles)>0) {
			
			echo $this->element('boxMsg',array('class_msg' => 'message', 'msg' => "Aggiorna il tuo listino con il listino articoli del G.A.S. ".$organizationsResults['Organization']['name']));
		?>
			<div class="table-responsive"><table class="table table-striped table-hover">
			<tr>
				<th rowspan="2"><?php echo __('N');?></th>
				<th class="title prodgas" colspan="4"><?php echo __('DesSupplierMasterOrganization');?></th>
				<th class="title"><?php echo __('DesSupplierMyOrganization');?></th>
				<th rowspan="2"></th>		
			</tr>
			<tr>
				<th class="prodgas" colspan="2"><?php echo __('Name');?></th>
				<th class="prodgas"><?php echo __('Conf');?></th>
				<th class="prodgas"><?php echo __('PrezzoUnita');?></th>				
				<th><?php echo __('Name');?></th>
			</tr>
			<?php
			foreach ($articlesMasters as $numResult => $articlesMaster):
							
				echo '<tr class="view">';
				
				echo '<td>'.((int)$numResult+1).'</td>';

				echo '<td>';
				if(!empty($articlesMaster['Article']['img1']) && file_exists(Configure::read('App.root').Configure::read('App.img.upload.prod_gas_article').DS.$articlesMaster['Article']['organization_id'].DS.$articlesMaster['Article']['img1'])) {
					echo '<img width="50" class="img-responsive-disabled userAvatar" src="'.Configure::read('App.server').Configure::read('App.web.img.upload.prod_gas_article').'/'.$articlesMaster['Article']['organization_id'].'/'.$articlesMaster['Article']['img1'].'" />';	
				}
				echo '</td>';
				echo '<td>';
				echo $articlesMaster['Article']['name'];
				echo '</td>';
				echo '<td>';
				echo $this->App->getArticleConf($articlesMaster['Article']['qta'], $articlesMaster['Article']['um']);
				echo '</td>';		
				echo '<td>';
				echo $articlesMaster['Article']['prezzo_e'];
				echo '</td>';	
				echo '<td>';
				echo $this->Form->input('article_id', array('id' => 'articleMasterId-'.$articlesMaster['Article']['id'], 'class' => 'myArticleId', 'options' => $myArticles, 'empty' => Configure::read('option.empty'), 'label' => false, 'escape' => false));
				echo '</td>';		
				echo '<td id="content-'.$articlesMaster['Article']['id'].'"></td>';
			echo '</tr>';
			
			endforeach;
			
			echo '</table></div>';
			
			echo $this->element('legendaDesSupplierSyncronizeActions');
		} 
		else { // if(count($articlesMasters)>0)
			echo $this->element('boxMsg', ['class_msg' => 'message', 'msg' => "Non ci sono articoli associati al tuo G.A.S."]);
			
			echo $this->Html->link(__('Importa l\'intero listino articoli'), ['controller' => 'DesArticlesSyncronizes', 'action' => 'index',
									  'organization_id' => $organizationsResults['Organization']['id'], 'supplier_id' => $mySuppliersOrganizationResults['SuppliersOrganization']['supplier_id'], 'modalita' => 'IMPORT_ALL'], ['class' => 'btn btn-primary', 'title' => __('Importa l\'intero listino articoli')]);
		}
		
				
	echo '</div>';
	echo '	</div>';
	echo '</div>';
	echo '  <div class="panel panel-primary">';
	echo '<div class="panel-heading">';
	echo '	<h4 class="panel-title">';
	echo '		<a data-toggle="collapse" data-parent="#accordion" href="#collapse2"><i class="fa fa-lg fa-minus" aria-hidden="true"></i> Elenco degli articoli a '.$organizationsResults['Organization']['name'].' ('.count($articlesMasters).')</a>';
	echo '  </h4>';
	echo '</div>';
	echo '<div id="collapse2" class="panel-collapse collapse in">';
	echo '<div class="panel-body">';
		  
		
		/*
		 * articoli da aggiungere
		 */
		if(count($articlesMasters)>0) {

			echo $this->element('boxMsg', ['class_msg' => 'message', 'msg' => "Aggiungi gli articoli del G.A.S. ".$organizationsResults['Organization']['name']." nel tuo listino articoli"]);
		?>
			<div class="table-responsive"><table class="table table-striped table-hover">
			<tr>
				<th rowspan="2"><?php echo __('N');?></th>
				<th class="title prodgas" colspan="4"><?php echo __('DesSupplierMasterOrganization');?></th>
				<th class="title"><?php echo __('DesSupplierMyOrganization');?></th>
				<th rowspan="2" class="actions"><?php echo __('Actions');?></th>		
			</tr>
			<tr>
				<th class="prodgas" colspan="2"><?php echo __('Name');?></th>
				<th class="prodgas"><?php echo __('Conf');?></th>
				<th class="prodgas"><?php echo __('PrezzoUnita');?></th>
				<th><?php echo __('Categories');?></th>					
			</tr>
			<?php
			foreach ($articlesMasters as $numResult => $articlesMaster):
							
				echo '<tr class="view">';
				
				echo '<td>'.((int)$numResult+1).'</td>';

				echo '<td>';
				if(!empty($articlesMaster['Article']['img1']) && file_exists(Configure::read('App.root').Configure::read('App.img.upload.prod_gas_article').DS.$articlesMaster['Article']['organization_id'].DS.$articlesMaster['Article']['img1'])) {
					echo '<img width="50" class="img-responsive-disabled userAvatar" src="'.Configure::read('App.server').Configure::read('App.web.img.upload.prod_gas_article').'/'.$articlesMaster['Article']['organization_id'].'/'.$articlesMaster['Article']['img1'].'" />';	
				}
				echo '</td>';
				echo '<td>';
				echo $articlesMaster['Article']['name'];
				echo '</td>';		
				echo '<td>';
				echo $this->App->getArticleConf($articlesMaster['Article']['qta'], $articlesMaster['Article']['um']);
				echo '</td>';
				echo '<td>';
				echo $articlesMaster['Article']['prezzo_e'];
				echo '</td>';	
				echo '<td>';
				echo $this->Form->input('category_article_id', array('id' => 'category_article_id-'.$articlesMaster['Article']['id'], 'options' => $categories, 'empty' => Configure::read('option.empty'), 'label' => false, 'escape' => false));
				echo '</td>';
				echo '<td class="actions-table-img">';
				echo $this->Html->link(null, array('action' => 'syncronize_insert', null, 'master_organization_id='.$organization_id.'&supplier_id='.$supplier_id.'&master_article_id='.$articlesMaster['Article']['id']), array('class' => 'action actionAdd actionGetCategory','id' => $articlesMaster['Article']['id'], 'title' => __('DesSyncronizeInsert')));					
				echo '</td>';
			echo '</tr>';
			
			endforeach;
			
			echo '</table></div>';	
		} 
		
		if(count($articlesMasters)>0) {		
			echo $this->element('legendaDesSupplierSyncronizeActions');

			echo $this->element('legendaArticleFlagPresenteArticlesorders');
		}
		
	echo '</div>';
	echo '</div>';
	echo '  </div>';
	echo '</div> <!-- panel-group --> ';
	
} // if($suppliersOrganizationResults['SuppliersOrganization']['stato']=='N') 
	

echo '</fieldset>';
echo '<input type="hidden" id="organization_id" value="'.$organization_id.'" />';
echo '<input type="hidden" id="supplier_id" value="'.$supplier_id.'" />';

echo $this->Form->end();
echo '</div>';
?>
<script type="text/javascript">
$(document).ready(function() {
		
	/*
	 * aggiungo la categoria
	 */
	$(".actionGetCategory").click(function() {
		
		var id = $(this).attr('id');
		var category_article_id = $('#category_article_id-'+id).val();
		if(category_article_id=='' || category_article_id==undefined) {
			alert("Devi indicare a quale categoria desideri associare l'articolo.");
			return false;
		}
		var href = $(this).attr('href');
		href = href + "&category_article_id="+category_article_id;
		 console.log(href); 
		
		$(this).attr('href', href);
		 
		return true;
	});
	
	$(".myArticleId").change(function() {
		
		var article_id = $(this).val();
		var arr = $(this).attr('id').split("-"); 
		var idMasterArticle = arr[1]; 
		$('#content-'+idMasterArticle).html("");
		
		if(article_id!='') {
			
			
			$.ajax({
				type: "GET",
				url: "/administrator/index.php?option=com_cake&controller=DesArticlesSyncronizes&action=ctrl_article&master_organization_id=<?php echo $organization_id;?>&supplier_id=<?php echo $supplier_id;?>&master_article_id="+idMasterArticle+"&article_id="+article_id+"&format=notmpl",
				success: function(response){
					$('#content-'+idMasterArticle).html(response);
				},
				error:function (XMLHttpRequest, textStatus, errorThrown) {
				}
			});		
		}
		 
		return false;
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