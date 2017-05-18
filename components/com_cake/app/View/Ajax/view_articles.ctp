<div class="related table">
	<?php
	echo '<table id="tableList_79" class="table">';
		
	if(!empty($results['Article']['img1']) && file_exists(Configure::read('App.root').Configure::read('App.img.upload.article').DS.$results['Article']['organization_id'].DS.$results['Article']['img1'])) {
		echo "\n\r";
		echo '<tr>';
		echo '<td colspan="4">';
		echo '<img src="'.Configure::read('App.server').Configure::read('App.web.img.upload.article').'/'.$results['Article']['organization_id'].'/'.$results['Article']['img1'].'" />';	
		echo '</td>';
		echo '</tr>';		
	}
	
	echo "\n\r";
	echo '<tr>';
	echo '<th style="width:240px;">'.__('Supplier').'</th>';
	echo '<td colspan="3">'.$results['SuppliersOrganization']['name'];
	if(!empty($results['Supplier']['descrizione']))
		echo ' - '.$results['Supplier']['descrizione'];
		
	if($results['Article']['bio']=='Y')		echo '<span style="float:right;" class="bio" title="'.Configure::read('bio').'"></span>';	
	echo '</td>';
	echo '</tr>';
	echo "\n\r";
	echo '<tr>';
	
	if(!empty($results['ArticlesType'])) {		echo "\n\r";		echo '<tr>';		echo '<th>Tipologia</th>';		echo '<td colspan="3">';		foreach($results['ArticlesType'] as $articlesType)			echo $articlesType['descrizione'].'<br />';			echo '</td>';		echo '</tr>';	}
	
	if($user->organization['Organization']['hasFieldArticleCategoryId']=='Y') {
		if(!empty($results['CategoriesArticle']['name'])) {
			echo "\n\r";
			echo '<tr>';
			echo '<th>'.__('Category').'</th>';
			echo '<td colspan="3">'.$results['CategoriesArticle']['name'].'</td>';
			echo '</tr>';
		}
	}
		
	if($user->organization['Organization']['hasFieldArticleCodice']=='Y' && !empty($results['Article']['codice'])) {
		echo "\n\r";
		echo '<tr>';
		echo '<th>Codice</th>';
		echo '<td colspan="3">'.$results['Article']['codice'].'</td>';
		echo '</tr>';
	}

	if($user->organization['Organization']['hasFieldArticleIngredienti']=='Y' && !empty($results['Article']['ingredienti'])) {
		echo "\n\r";
		echo '<tr>';
		echo '<th>Ingredienti</th>';
		echo '<td colspan="3">'.$results['Article']['ingredienti'].'</td>';
		echo '</tr>';
	}

	if(!empty($results['Article']['nota'])) {
		echo "\n\r";
		echo '<tr>';
		echo '<th>Nota</th>';
		echo '<td colspan="3">'.strip_tags($results['Article']['nota']).'</td>';
		echo '</tr>';
	}
	
	if(empty($results['Article']['modified']) ? $colspan = 'colspan="3"' : $colspan = '');
	
	echo "\n\r";
	echo '<tr>';
	echo '<th>Articolo inserito il</th>';
	echo '<td '.$colspan.'>'.$this->App->formatDateCreatedModifier($results['Article']['created']).'</td>';

	if(!empty($results['Article']['modified'])) {
		echo "\n\r";
		echo '<th>Ultima modifica</th>';
		echo '<td>'.$this->App->formatDateCreatedModifier($results['Article']['modified']).'</td>';
	}

	echo '</tr>';
	
echo '</table>';			
echo '</div>';