<?php
	if(!empty($organizationOtherResults)) {
		echo '<p class="details-left"><label>G.A.S.</label> ';
		echo '<span>';
		echo $organizationOtherResults['Organization']['name'];
		echo '<span style="float:right;"><a target="_blank" title="link al sotto-sito di PortAlGas" href="'.$this->App->traslateWww($organizationOtherResults['Organization']['www']).'"><img width="50" class="img-responsive-disabled userAvatar" src="'.Configure::read('App.web.img.upload.content').'/'.$organizationOtherResults['Organization']['img1'].'" alt="'.$organizationOtherResults['Organization']['name'].'" /></span>';
		echo '</span>';
		echo '</p>';	
	}

	/*	
	echo "\n\r";
	echo '<p class="details-left"><label>'.__('Supplier').'</label> ';
	echo '<span>'.$results['SuppliersOrganization']['name'];
	if(!empty($results['Supplier']['descrizione']))
		echo ' - '.$results['Supplier']['descrizione'];
	echo '</span>';
	echo '</p>';
	*/
	echo "\n\r";
	echo '<p class="details-left"><label>Conf.</label> ';
	echo "\n";  // Conf.
	echo '<span>Confezioni da ';
	if($results['Article']['qta']>0)
		echo $this->App->getArticleConf($results['Article']['qta'], $this->App->traslateEnum($results['Article']['um']));
	echo ' al prezzo di '; // Prezzo unità
	echo $results['ArticlesOrder']['prezzo_e'];

	echo ' (Prezzo/UM ';  // Prezzo/UM
	echo $this->App->getArticlePrezzoUM($results['ArticlesOrder']['prezzo'], $results['Article']['qta'], $results['Article']['um'], $results['Article']['um_riferimento']);
	echo ')</span>';
	echo '</p>';
	echo "\n";

	if(!empty($results['ArticlesType'])) {
		echo "\n\r";		echo '<p class="details-left"><label>Tipologia</label> ';		echo '<span>';
		foreach($results['ArticlesType'] as $articlesType)			echo $articlesType['descrizione'].'<br />';
		echo '</span>';		echo '</p>';
	}
	
	if($user->organization['Organization']['hasFieldArticleCategoryId']=='Y') {
		echo "\n\r";
		echo '<p class="details-left"><label>Categoria</label> ';
		echo '<span>'.$results['CategoriesArticle']['name'].'</span>';
		echo '</p>';
	}
			
	if($user->organization['Organization']['hasFieldArticleCodice']=='Y' && !empty($results['Article']['codice'])) {
		echo "\n\r";
		echo '<p class="details-left"><label>Codice</label> ';
		echo '<span>'.$results['Article']['codice'].'</span>';
		echo '</p>';
	}

	if($user->organization['Organization']['hasFieldArticleIngredienti']=='Y' && !empty($results['Article']['ingredienti'])) {
		echo "\n\r";
		echo '<p class="details-left"><label>Ingredienti</label> ';
		echo '<span>'.$results['Article']['ingredienti'].'</span>';
		echo '</p>';
	}

	if(!empty($results['Article']['nota'])) {
		echo "\n\r";
		echo '<p class="details-left"><label>Nota</label> ';
		echo '<span class="articleNota">'.strip_tags($results['Article']['nota']).'</span>';
		echo '</p>';
	}
	
	echo "\n\r";
	echo '<p class="details-left"><label>Acquistato</label> ';
	echo '<span>';
	if($results['ArticlesOrder']['qta_cart']==0) echo 'Articolo finora mai acquistato';
	else
	if($results['ArticlesOrder']['qta_cart']==1) echo 'Articolo acquistato gi&agrave; '.$results['ArticlesOrder']['qta_cart'].' volta';
	else echo 'Articolo acquistato gi&agrave; '.$results['ArticlesOrder']['qta_cart'].' volte';
	echo ' da parte dei gasisti';
	echo '</span>';
	echo '</p>';
	
	if($results['ArticlesOrder']['qta_minima_order'] > 0) {
		echo "\n\r";
		echo '<p class="details-left"><label>Bisogna raggiungere una quantità minima di</label> ';
		echo '<span>';
		echo $results['ArticlesOrder']['qta_minima_order'];
		$differenza = ($results['ArticlesOrder']['qta_minima_order'] - $results['ArticlesOrder']['qta_cart']);
		if($differenza > 0)
			echo ' , ancora '.$differenza;
		else 
			echo ' , quantità raggiunta';
		echo '</span>';
		echo '</p>';
	}
	
	if($results['ArticlesOrder']['qta_massima_order'] > 0) {
		echo "\n\r";
		echo '<p class="details-left"><label>Se ne possono ordinare ancora</label> ';
		echo '<span>';
		echo ($results['ArticlesOrder']['qta_massima_order'] - $results['ArticlesOrder']['qta_cart']).' pezzi';
		echo '</span>';
		echo '</p>';
	}
		
	echo "\n\r";
	echo '<p class="details-left"><label>'.__('pezzi_confezione').'</label> ';
	echo '<span>'.$results['ArticlesOrder']['pezzi_confezione'].'</span>';
	echo '</p>';

	if($results['ArticlesOrder']['qta_minima']>1) {
		echo "\n\r";
		echo '<p class="details-left"><label>Se ne pu&ograve; acquistare minimo di</label> ';
		echo '<span>'.$results['ArticlesOrder']['qta_minima'].'</span>';
		echo '</p>';
	}

	if($results['ArticlesOrder']['qta_massima']>1) {
		echo "\n\r";
		echo '<p class="details-left"><label>Se ne pu&ograve; acquistare un massimo di</label> ';
		echo '<span>'.$results['ArticlesOrder']['qta_massima'].'</span>';
		echo '</p>';
	}
	
	if($results['ArticlesOrder']['qta_multipli']>1) {
		echo "\n\r";
		echo '<p class="details-left"><label>Se ne pu&ograve; acquistare multipli di</label> ';
		echo '<span>'.$results['ArticlesOrder']['qta_multipli'].'</span>';
		echo '</p>';
	}
	
	echo "\n\r";
	echo '<p class="details-left"><label>Articolo inserito il</label> ';
	echo '<span>'.$this->App->formatdateCreatedModifier($results['Article']['created']).'</span>';
	echo '</p>';

	if(!empty($results['Article']['modified'])) {
		echo "\n\r";
		echo '<p class="details-left"><label>Modificato il</label> ';
		echo '<span>'.$this->App->formatdateCreatedModifier($results['Article']['modified']).'</span>';
		echo '</p>';
	}
?>

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