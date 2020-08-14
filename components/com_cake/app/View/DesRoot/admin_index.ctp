<?php
$this->Html->addCrumb(__('Home'),array('controller' => 'Pages', 'action' => 'home'));
$this->Html->addCrumb(__('Des'));
echo $this->Html->getCrumbList(array('class'=>'crumbs'));

echo '<div class="des">';
echo '<h2 class="ico-des">';		
echo __('Des').' '.$desResults['DesRoot']['name'];
echo '</h2>';


if(!empty($results)) {

	echo '<table cellpadding="0" cellspacing="0">';
	echo '<tr>';
	echo '<th>'.__('N').'</th>';
	echo '<th>D.E.S.</th>';
	echo '<th colspan="2">'.__('Organization').'</th>';
	echo '<th>Localit&agrave;</th>';
	echo '<th>Contatti</th>';
	echo '</tr>';

	foreach ($results as $numResult => $des):

		echo '<tr class="view-2">';
		echo '<td>'.((int)$numResult+1).'</td>';
		echo '<td colspan="5">'.$des['DesRoot']['name'].'</td>';
		echo '</tr>';
		
		foreach ($des['Organization'] as $numResult2 => $organization)  {
			echo '<tr class="view-2">';
			echo '<td></td>';
			echo '<td></td>';
			echo '<td>';
			echo '<img width="50" src="'.Configure::read('App.web.img.upload.content').'/'.$organization['img1'].'" alt="'.$organization['name'].'" />';
			echo '</td>';
			echo '<td>'.$organization['name'].'</td>';
			echo '<td>';
				   if(!empty($organization['indirizzo'])) echo $organization['indirizzo'].'&nbsp;<br />';
				   if(!empty($organization['localita'])) echo $organization['localita'].'&nbsp;';
				   if(!empty($organization['cap'])) echo $organization['cap'].'&nbsp;';
				   if(!empty($organization['provincia'])) echo '('.h($organization['provincia']).')'; 
			echo '</td>';
			echo '<td>';
					if(!empty($organization['telefono'])) echo h($organization['telefono']).'<br />';
					if(!empty($organization['telefono2'])) echo  h($organization['telefono2']).'<br />';
					if(!empty($organization['mail'])) echo '<a title="'.__('Email send').'" target="_blank" href="mailto:'.h($organization['mail']).'">'.$organization['mail'].'</a><br />';
					if(!empty($organization['www'])) echo '<a title="link al sotto-sito di PortAlGas" href="'.$this->App->traslateWww($organization['www']).'">'.$this->App->traslateWww($organization['www']).'</a><br />';
					if(!empty($organization['www2'])) echo '<a title="link esterno al sito dell\'organizzazione" href="'.$this->App->traslateWww($organization['www2']).'">'.$this->App->traslateWww($organization['www2']).'</a><br />';
			echo '</td>';

			echo '</tr>';			
		}


	endforeach; 

	echo '</table>';		
} 
else  
	echo $this->element('boxMsg',array('class_msg' => 'message resultsNotFound', 'msg' => "Non ci sono ancora GAS associati"));
	
echo '</div>';
?>