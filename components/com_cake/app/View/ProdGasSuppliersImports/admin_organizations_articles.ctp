<?php
$this->App->d($results);

$this->Html->addCrumb(__('Home'), ['controller' => 'Pages', 'action' => 'home']);
$this->Html->addCrumb(__('ProdGasSuppliersImportCompareListArticles'));
echo $this->Html->getCrumbList(array('class'=>'crumbs'));

echo '<div class="organizations">';

echo '<h3>'.__('ProdGasSuppliersImportCompareListArticles').'</h3>';

echo '<div class="table-responsive"><table class="table table-hover">';
echo '<tr>';
echo '<th>N.</th>';
echo '<th colspan="2">Gas</th>';
if($isRoot) {
	echo '<th style="width:50px;">supplier_id</th>';
	echo '<th style="width:50px;">prod_gas_article_id</th>';
}
echo '<th colspan="3">'.__('Name').'</th>';
echo '<th style="width:50px;">'.__('Codice').'</th>';
echo '<th>'.__('Prezzo').'</th>';
echo '<th>'.__('Conf').'</th>';
echo '</tr>';

$i = 0;
foreach($results as $numResult => $result) {

	$numRow = $result['Article']['supplier_id'].'-'.$result['Organization']['id'].'-'.$result['Article']['id']; 
	
	echo '<tr>';
	echo '<td>'.($numResult+1).'</td>';
	echo '<td>';
	if(!empty($result['Organization']['id']))	
		echo ' <img width="50" class="img-responsive userAvatar" src="'.Configure::read('App.web.img.upload.content').'/'.$result['Organization']['img1'].'" alt="'.$result['Organization']['name'].'" /> ';	
	echo '</td>';	
	echo '<td>';
	if(!empty($result['Organization']['id']))
		echo $result['Organization']['name'];
	else
		echo '<label class="btn btn-info">Produttore</label>';
	echo '</td>';
	if($isRoot) {
		if($result['SuppliersOrganization']['owner_articles']=='SUPPLIER' && !empty($result['Organization']['id'])) {
			echo '<td>';
			echo $this->Form->input('name', ['data-attr-supplier_id' => $result['Article']['supplier_id'], 'data-attr-organization_id' => $result['Organization']['id'],  'id' => 'supplier_id-'.$result['Article']['id'], 
										 'class' => 'activeUpdate', 'value' => $result['Article']['supplier_id'], 'type' => 'text', 'label' => false, 'tabindex'=>($i+1)]);	
			echo '</td>';
			echo '<td>';
			echo $this->Form->input('name', ['data-attr-supplier_id' => $result['Article']['supplier_id'], 'data-attr-organization_id' => $result['Organization']['id'],  'id' => 'prod_gas_article_id-'.$result['Article']['id'], 
										 'class' => 'activeUpdate', 'value' => $result['Article']['prod_gas_article_id'], 'type' => 'text', 'label' => false, 'tabindex'=>($i+1)]);	
			echo '</td>';
		}
		else {
			echo '<td>'.$result['Article']['supplier_id'].'</td>';
			echo '<td>'.$result['Article']['prod_gas_article_id'].'</td>';
		}
	}
	echo '<td>';
	if(!empty($result['Article']['img1']) && file_exists(Configure::read('App.root').Configure::read('App.img.upload.article').DS.$result['Article']['organization_id'].DS.$result['Article']['img1'])) {
		echo '<img width="50" class="img-responsive-disabled userAvatar" src="'.Configure::read('App.server').Configure::read('App.web.img.upload.article').'/'.$result['Article']['organization_id'].'/'.$result['Article']['img1'].'" />';
	}		
	echo '</td>';	
	echo '<td>';
	if($result['SuppliersOrganization']['owner_articles']=='SUPPLIER')
		echo $this->Form->input('name', ['data-attr-supplier_id' => $result['Article']['supplier_id'], 'data-attr-organization_id' => $result['Organization']['id'],  'id' => 'name-'.$result['Article']['id'], 
								     'class' => 'activeUpdate', 'value' => $result['Article']['name'], 'type' => 'text', 'label' => false, 'tabindex'=>($i+1)]);	
	else
		echo $result['Article']['name'];
	echo '</td>';
	echo '<td>';
	echo '<img alt="" src="'.Configure::read('App.img.cake').'/blank32x32.png" id="submitEcomm-'.$numRow.'" class="buttonCarrello submitEcomm" />';
	echo '<div id="msgEcomm-'.$numRow.'" class="msgEcomm"></div>';
	echo '</td>';			
	echo '<td>';
	if($result['SuppliersOrganization']['owner_articles']=='SUPPLIER')
		echo $this->Form->input('codice', ['data-attr-supplier_id' => $result['Article']['supplier_id'], 'data-attr-organization_id' => $result['Organization']['id'],  'id' => 'codice-'.$result['Article']['id'], 
									   'class' => 'activeUpdate', 'value' => $result['Article']['codice'], 'type' => 'text', 'label' => false, 'tabindex'=>($i+1)]);
	else
		echo $result['Article']['codice'];
	echo '</td>';
	echo '<td style="white-space: nowrap;">';
	if($result['SuppliersOrganization']['owner_articles']=='SUPPLIER')
		echo $this->Form->input('prezzo', ['data-attr-supplier_id' => $result['Article']['supplier_id'], 'data-attr-organization_id' => $result['Organization']['id'],  'id' => 'prezzo-'.$result['Article']['id'], 
										'class' => 'activeUpdate double', 'value' => $result['Article']['prezzo_'], 'type' => 'text', 'label' => false, 'after' => '&nbsp;&euro;', 'style' => 'display:inline', 'tabindex'=>($i+1)]);
	else
		echo $result['Article']['prezzo_e'];
	echo '</td>';
	echo '<td>'.$this->App->getArticleConf($result['Article']['qta'], $result['Article']['um']).'</td>';
	echo '</tr>';			
}
echo '</table></div>';			

echo '</div>';
?>
<script type="text/javascript">
$(document).ready(function() {

	$('.qta').focusout(function() {validateNumberField(this,'quantita\'');});
	$('.double').focusout(function() {validateNumberField(this,'prezzo');});
	
	$(".activeUpdate").change(function() {

		var supplier_id = $(this).attr('data-attr-supplier_id');
		var organization_id = $(this).attr('data-attr-organization_id');
		var name_field_and_id = $(this).attr('id');
		var name_field_and_ids = name_field_and_id.split('-');
		var name_field = name_field_and_ids[0];
		var id = name_field_and_ids[1];
		var numRow = supplier_id+'-'+organization_id+'-'+id;
		
		var value =  $(this).val();
		/* console.log(name_field+' '+value); */
		if(name_field=='name' && value=='') {
			alert("Il nome dell'articolo è obbligatorio");
			return false;
		}
		else
		if(name_field=='prezzo' && (value=='' || value=='0' || value=='0,0' || value=='0.0' || value=='0,00' || value=='0.00')) {
			alert("Il prezzo dell'articolo è obbligatorio");
			return false;
		}
					
		var url = '';
		url = "/administrator/index.php?option=com_cake&controller=ProdGasSuppliersImports&action=updateField&supplier_id="+supplier_id+"&organization_id="+organization_id+"&id="+name_field_and_id+"&format=notmpl";
		/* console.log(url); */

		$.ajax({
			type: "POST",
			url: url,
			data: "value="+value,
			success: function(response){
				 /* console.log('#msgEcomm-'+numRow+' '+response); */
				 $('#msgEcomm-'+numRow).html(response);
			},
			error:function (XMLHttpRequest, textStatus, errorThrown) {
				 $('#msgEcomm-'+numRow).html(textStatus);
				 $('#submitEcomm-'+numRow).attr('src',app_img+'/blank32x32.png');
			}
		});
		return false;			
	});	
});		
</script>