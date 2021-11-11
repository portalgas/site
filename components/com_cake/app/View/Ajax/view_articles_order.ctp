<div class="related table">
	<?php
	echo '<div class="table-responsive"><table class="table table-hover">';
	
	if(!empty($results['Article']['img1']) && file_exists(Configure::read('App.root').Configure::read('App.img.upload.article').DS.$results['Article']['organization_id'].DS.$results['Article']['img1'])) {
		echo "\n\r";
		echo '<tr>';
		echo '<td colspan="4">';
		echo '<img class="img-responsive-disabled" src="'.Configure::read('App.server').Configure::read('App.web.img.upload.article').'/'.$results['Article']['organization_id'].'/'.$results['Article']['img1'].'" />';	
		echo '</td>';
		echo '</tr>';		
	}
	
	echo "\n\r";
	echo '<tr>';
	echo '<th style="width:240px;">'.__('Supplier').'</th>';
	echo '<td colspan="3">'.$results['SuppliersOrganization']['name'];
	if(!empty($results['Supplier']['descrizione']))
		echo ' - '.$results['Supplier']['descrizione'];
	
	if($this->App->isArticlesTypeBio($results['ArticlesType']))		echo '<span style="float:right;" class="bio" title="'.Configure::read('bio').'"></span>';		
	echo '</td>';
	echo '</tr>';
	
	if(!empty($results['ArticlesType'])) {
		echo "\n\r";		echo '<tr>';		echo '<th>Tipologia</th>';		echo '<td colspan="3">';
		foreach($results['ArticlesType'] as $articlesType)			echo $articlesType['descrizione'].'<br />';
		echo '</td>';		echo '</tr>';
	}
	
	if($user->organization['Organization']['hasFieldArticleCategoryId']=='Y') {
		echo "\n\r";
		echo '<tr>';
		echo '<th>Categoria</th>';
		echo '<td colspan="3">'.$results['CategoriesArticle']['name'].'</td>';
		echo '</tr>';
	}
			
	if($user->organization['Organization']['hasFieldArticleCodice']=='Y' && !empty($results['Article']['codice'])) {
		echo "\n\r";
		echo '<tr>';
		echo '<th>Codice</th>';
		echo '<td colspan="3">'.$results['Article']['codice'].'</td>';
		echo '</tr>';
	}

	// Prezzo/UM
	echo "\n\r";
	echo '<tr>';
	echo '<th>'.__('Prezzo/UM').'</th>';
	echo '<td colspan="3">'.$this->App->getArticlePrezzoUM($results['ArticlesOrder']['prezzo'], $results['Article']['qta'], $results['Article']['um'], $results['Article']['um_riferimento']).'</td>';
	echo '</tr>';
	
	// Qta minima
	echo "\n\r";
	echo '<tr>';
	echo '<th>Se ne può acquistare un minimo di</th>';
	echo '<td colspan="3">'.sprintf("%5.2f",$results['ArticlesOrder']['qta_minima']).'</td>';
	echo '</tr>';

	if($user->organization['Organization']['hasFieldArticleIngredienti']=='Y' && !empty($results['Article']['ingredienti'])) {
		echo "\n\r";
		echo '<tr>';
		echo '<th>Ingredienti</th>';
		echo '<td colspan="3">'.strip_tags($results['Article']['ingredienti']).'</td>';
		echo '</tr>';
	}

	if(!empty($results['Article']['nota'])) {
		echo "\n\r";
		echo '<tr>';
		echo '<th>Nota</th>';
		echo '<td colspan="3" class="articleNota">'.strip_tags($results['Article']['nota']).'</td>';
		echo '</tr>';
	}

	echo "\n\r";
	echo '<tr>';
	echo '<th></th>';
	echo "\n";  // Conf.
	echo '<td style="white-space: nowrap;">Confezioni da ';
	if($results['Article']['qta']>0)
		echo $this->App->getArticleConf($results['Article']['qta'], $this->App->traslateEnum($results['Article']['um']));
	echo ' al prezzo di '; // Prezzo unità
	echo $results['ArticlesOrder']['prezzo_e'];

	echo ' (Prezzo/UM ';  // Prezzo/UM
	echo $this->App->getArticlePrezzoUM($results['ArticlesOrder']['prezzo'], $results['Article']['qta'], $results['Article']['um'], $results['Article']['um_riferimento']);
	echo ')</td>';
	echo '</tr>';
	echo "\n";
	
	echo "\n\r";
	echo '<tr>';
	echo '<th>Acquistato</th>';
	echo '<td colspan="3">';
	if($results['ArticlesOrder']['qta_cart']==0) echo 'Articolo finora mai acquistato';
	else
	if($results['ArticlesOrder']['qta_cart']==1) echo 'Articolo acquistato gi&agrave; '.$results['ArticlesOrder']['qta_cart'].' volta';
	else echo 'Articolo acquistato gi&agrave; '.$results['ArticlesOrder']['qta_cart'].' volte';
	echo ' da parte dei gasisti';
	echo '</td>';
	echo '</tr>';
	
	if($results['ArticlesOrder']['qta_minima_order'] > 0) {
		echo "\n\r";
		echo '<tr>';
		echo '<th>Bisogna raggiungere una quantità minima di</th>';
		echo '<td colspan="3">';
		echo $results['ArticlesOrder']['qta_minima_order'];
		$differenza = ($results['ArticlesOrder']['qta_minima_order'] - $results['ArticlesOrder']['qta_cart']);
		if($differenza > 0)
			echo ' , ancora '.$differenza;
		else
			echo ' , quantità raggiunta';
		echo '</td>';
		echo '</tr>';
	}	
	
	if($results['ArticlesOrder']['qta_massima_order'] > 0) {
		echo "\n\r";
		echo '<tr>';
		echo '<th>Se ne possono ordinare ancora</th>';
		echo '<td colspan="3">';
		echo ($results['ArticlesOrder']['qta_massima_order'] - $results['ArticlesOrder']['qta_cart']).' pezzi';
		echo '</td>';
		echo '</tr>';
	}
		
	echo "\n\r";
	echo '<tr>';
	echo '<th>'.__('pezzi_confezione').'</th>';
	echo '<td colspan="3">'.$results['ArticlesOrder']['pezzi_confezione'].'</td>';
	echo '</tr>';

	if($results['ArticlesOrder']['qta_minima']>1) {
		echo "\n\r";
		echo '<tr>';
		echo '<th>Se ne pu&ograve; acquistare minimo di</th>';
		echo '<td colspan="3">'.$results['ArticlesOrder']['qta_minima'].'</td>';
		echo '</tr>';
	}
	
	if($results['ArticlesOrder']['qta_massima']>1) {
		echo "\n\r";
		echo '<tr>';
		echo '<th>Se ne pu&ograve; acquistare un massimo di</th>';
		echo '<td colspan="3">'.$results['ArticlesOrder']['qta_massima'].'</td>';
		echo '</tr>';
	}

	if($results['ArticlesOrder']['qta_multipli']>1) {
		echo "\n\r";
		echo '<tr>';
		echo '<th>Se ne pu&ograve; acquistare multipli di</th>';
		echo '<td colspan="3">'.$results['ArticlesOrder']['qta_multipli'].'</td>';
		echo '</tr>';
	}
	
	if(empty($results['Article']['modified']) ? $colspan = 'colspan="3"' : $colspan = '');

	echo "\n\r";
	echo '<tr>';
	echo '<th>Articolo inserito il</th>';
	echo '<td '.$colspan.'>'.$this->App->formatDateCreatedModifier($results['Article']['created']).'</td>';

	if(!empty($results['Article']['modified'])) {
		echo "\n\r";
		echo '<th>Modificato il</th>';
		echo '<td>'.$this->App->formatDateCreatedModifier($results['Article']['modified']).'</td>';
	}

	echo '</tr>';
	
	/*
	 * dati owner_articles listino REFERENT / DES / SUPPLIER 
	 */	
	if(isset($organizationResults)) {
		echo '<tr>';
		echo '<th>'.__('organization_owner_articles').'</th>';
		echo '<td colspan="3">';
		echo ' <img width="50" class="img-responsive-disabled userAvatar" src="'.Configure::read('App.web.img.upload.content').'/'.$organizationResults['Organization']['img1'].'" alt="'.$organizationResults['Organization']['name'].'" /> ';	
		echo $organizationResults['Organization']['name']; 
		echo '</td>';
		echo '</tr>';
	
	}	
	?>
	</table></div>			
</div>

<script type="text/javascript">
$(document).ready(function() {
	<?php 
	if(!empty($evidenzia)) 
		echo "$('.articleNota').css('background', 'none repeat scroll 0 0 #FFFAC2');";
	else	
		echo "$('.articleNota').css('background', 'none repeat scroll 0 0 #FFFFFF');";
	?>	
});
</script>