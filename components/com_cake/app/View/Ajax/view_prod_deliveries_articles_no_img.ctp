<div class="related">
	<?php
	echo '<table cellpadding = "0" cellspacing = "0" class="TableDettaglio">';
	
	if($this->App->isArticlesTypeBio($results['ArticlesType'])) {
		echo "\n\r";
		echo '<tr>';
		echo '<th style="width:240px;"></th>';
		echo '<td colspan="3">';
		echo '<span style="float:right;" class="bio" title="'.Configure::read('bio').'"></span>';
		echo '</td>';
		echo '</tr>';
	}
	
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
		echo '<td colspan="3" class="articleNota">'.strip_tags($results['Article']['nota']).'</td>';
		echo '</tr>';
	}

	echo "\n\r";
	echo '<tr>';
	echo '<th style="width:240px;"></th>';
	echo "\n";  // Conf.
	echo '<td style="white-space: nowrap;">Confezioni da ';
	if($results['Article']['qta']>0)
		echo $this->App->getArticleConf($results['Article']['qta'], $this->App->traslateEnum($results['Article']['um']));
	echo ' al prezzo di '; // Prezzo unità
	echo $results['ProdDeliveriesArticle']['prezzo_e'];

	echo ' (Prezzo/UM ';  // Prezzo/UM
	echo $this->App->getArticlePrezzoUM($results['ProdDeliveriesArticle']['prezzo'], $results['Article']['qta'], $results['Article']['um'], $results['Article']['um_riferimento']);
	echo ')</td>';
	echo '</tr>';
	echo "\n";
	
	echo "\n\r";
	echo '<tr>';
	echo '<th>Acquistato</th>';
	echo '<td colspan="3">';
	if($results['ProdDeliveriesArticle']['qta_cart']==0) echo 'Articolo finora mai acquistato';
	else
	if($results['ProdDeliveriesArticle']['qta_cart']==1) echo 'Articolo acquistato gi&agrave; '.$results['ProdDeliveriesArticle']['qta_cart'].' volta';
	else echo 'Articolo acquistato gi&agrave; '.$results['ProdDeliveriesArticle']['qta_cart'].' volte';
	echo '</td>';
	echo '</tr>';
	
	if($results['ProdDeliveriesArticle']['qta_massima_order'] > 0) {
		echo "\n\r";
		echo '<tr>';
		echo '<th>Se ne possono ordinare ancora</th>';
		echo '<td colspan="3">';
		echo ($results['ProdDeliveriesArticle']['qta_massima_order'] - $results['ProdDeliveriesArticle']['qta_cart']).' pezzi';
		echo '</td>';
		echo '</tr>';
	}
		
	echo "\n\r";
	echo '<tr>';
	echo '<th>'.__('pezzi_confezione').'</th>';
	echo '<td colspan="3">'.$results['ProdDeliveriesArticle']['pezzi_confezione'].'</td>';
	echo '</tr>';

	if($results['ProdDeliveriesArticle']['qta_minima']>1) {
		echo "\n\r";
		echo '<tr>';
		echo '<th>Se ne pu&ograve; acquistare minimo di</th>';
		echo '<td colspan="3">'.$results['ProdDeliveriesArticle']['qta_minima'].'</td>';
		echo '</tr>';
	}
	
	if($results['ProdDeliveriesArticle']['qta_massima']>1) {
		echo "\n\r";
		echo '<tr>';
		echo '<th>Se ne pu&ograve; acquistare massimo di</th>';
		echo '<td colspan="3">'.$results['ProdDeliveriesArticle']['qta_massima'].'</td>';
		echo '</tr>';
	}

	if($results['ProdDeliveriesArticle']['qta_multipli']>1) {
		echo "\n\r";
		echo '<tr>';
		echo '<th>Se ne pu&ograve; acquistare multipli di</th>';
		echo '<td colspan="3">'.$results['ProdDeliveriesArticle']['qta_multipli'].'</td>';
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
	?>
	</table>			
</div>

<script type="text/javascript">
jQuery(document).ready(function() {
	<?php 
	if(!empty($evidenzia)) 
		echo "jQuery('.articleNota').css('background', 'none repeat scroll 0 0 #FFFAC2');";
	else	
		echo "jQuery('.articleNota').css('background', 'none repeat scroll 0 0 #FFFFFF');";
	?>	
});
</script>