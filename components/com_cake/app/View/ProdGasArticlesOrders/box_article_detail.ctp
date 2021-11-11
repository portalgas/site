<?php
echo '<div class="related">';
echo '<table cellpadding = "0" cellspacing = "0" class="TableDettaglio">';
		
	echo "\n\r";
	echo '<tr>';
	echo '<th style="width:240px;">'.__('Name').'</th>';
	echo '<td>'.$article['Article']['name'].'&nbsp;';
		
	// confezione
	echo $this->App->getArticleConf($article['Article']['qta'], $article['Article']['um']).'&nbsp;';
			
	if($article['Article']['bio']=='Y')
		echo '<span style="float:right;" class="bio" title="'.Configure::read('bio').'"></span>';
	
	echo '</td>';
	echo '</tr>';	
echo '</table>';			
echo '</div>';
?>