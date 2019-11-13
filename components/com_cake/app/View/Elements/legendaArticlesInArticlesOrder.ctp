<?php
echo '<div class="legenda legenda-ico-info">';
echo __('msg_article_edit_propagate_article_order');

if(!empty($stateCodeNotUpdateArticle)) {
	$stateCodeNotUpdateArticles = explode(',', $stateCodeNotUpdateArticle);
	echo '<p><ul>';
	foreach($stateCodeNotUpdateArticles as $stateCodeNotUpdateArticle) {
		$stateCodeNotUpdateArticle = str_replace("'", '', $stateCodeNotUpdateArticle);
		echo '<li>'.__($stateCodeNotUpdateArticle.'-label').'</li>';
	}
	echo '</ul></p>';
}

echo '</div>';
 
/*
 * posso avere piu' records perche' l'articolo puo' essere legato a piu' ordini
$article_id = $results[0]['Article']['id'];
echo '<div class="legenda  legenda-ico-info">';
echo __('article_msg_edit_values');
echo '<br />Se desideri farlo, clicca su ';
echo $this->Html->link("Modifica gli articoli associati agli ordini", array('controller' => 'ArticlesOrders', 'action' => 'order_choice', $article_id), array('title' => 'Modifica gli articoli associati agli ordini'));
echo '</div>';
 */
?>