<?php
$this->Html->addCrumb(__('Home'),array('controller' => 'Pages', 'action' => 'home'));
$this->Html->addCrumb(__('Des'));
echo $this->Html->getCrumbList(array('class'=>'crumbs'));

echo '<div class="des">';
echo '<h2 class="ico-des">';		
echo __('Des').' '.$desResults['De']['name'];
echo '</h2>';


if(!empty($desOrganizationsResults)) {

	echo '<table cellpadding="0" cellspacing="0">';
	echo '<tr>';
	echo '<th>'.__('N').'</th>';
	echo '<th colspan="2">'.__('Organization').'</th>';
	echo '<th>Localit&agrave;</th>';
	echo '<th>Contatti</th>';
	echo '</tr>';

	foreach ($desOrganizationsResults as $numResult => $result):

		echo '<tr class="view-2">';
		echo '<td>'.((int)$numResult+1).'</td>';
		
		echo '<td>';
		echo '<img width="50" src="'.Configure::read('App.web.img.upload.content').'/'.$result['Organization']['img1'].'" alt="'.$result['Organization']['name'].'" />';
		echo '</td>';
		echo '<td>'.$result['Organization']['name'].'</td>';
		echo '<td>';
			   if(!empty($result['Organization']['indirizzo'])) echo $result['Organization']['indirizzo'].'&nbsp;<br />';
			   if(!empty($result['Organization']['localita'])) echo $result['Organization']['localita'].'&nbsp;';
			   if(!empty($result['Organization']['cap'])) echo $result['Organization']['cap'].'&nbsp;';
			   if(!empty($result['Organization']['provincia'])) echo '('.h($result['Organization']['provincia']).')'; 
		echo '</td>';
		echo '<td>';
			    if(!empty($result['Organization']['telefono'])) echo h($result['Organization']['telefono']).'<br />';
			    if(!empty($result['Organization']['telefono2'])) echo  h($result['Organization']['telefono2']).'<br />';
				if(!empty($result['Organization']['mail'])) echo '<a title="'.__('Email send').'" target="_blank" href="mailto:'.h($result['Organization']['mail']).'">'.$result['Organization']['mail'].'</a><br />';
				if(!empty($result['Organization']['www'])) echo '<a title="link al sotto-sito di PortAlGas" href="'.$this->App->traslateWww($result['Organization']['www']).'">'.$this->App->traslateWww($result['Organization']['www']).'</a><br />';
				if(!empty($result['Organization']['www2'])) echo '<a title="link esterno al sito dell\'organizzazione" href="'.$this->App->traslateWww($result['Organization']['www2']).'">'.$this->App->traslateWww($result['Organization']['www2']).'</a><br />';
		echo '</td>';

		echo '</tr>';

	endforeach; 

	echo '</table>';		
} 
else  
	echo $this->element('boxMsg',array('class_msg' => 'message resultsNotFound', 'msg' => "Non ci sono ancora GAS associati"));
	
echo '</div>';
?>