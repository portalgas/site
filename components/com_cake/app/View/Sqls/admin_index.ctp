<?php
$this->App->d($sqlResults);

$this->Html->addCrumb(__('Home'),['controller' => 'Pages', 'action' => 'home']);
$this->Html->addCrumb(__('Query'),['controller' => 'Sqls', 'action' => 'index']);
$this->Html->addCrumb(__('Execute'));	 
echo $this->Html->getCrumbList(array('class'=>'crumbs'));

echo '<div class="mails">';
echo '<h2 class="ico-config">';
echo __('Query');
echo '</h2>';
echo '</div>';

echo '<div class="table-responsive"><table class="table table-hover">';	
foreach($results as $numResult => $result) {
	
	echo '<tr>';
	echo '<td>';
	echo $this->Form->create('Sql', ['id' => 'formGas-'.$numResult]);	
	echo '<fieldset>';
	echo '<legend>'.$result['name'].'</legend>';
	
	/*
	echo '<pre class="shell" rel="'.$result['name'].'" style="width:750px;">';
	echo $result['sql'];
	echo '</pre>';
	*/
	
	$params = $result['params'];
	if(!empty($params))
		foreach($params as $key => $label) {
			echo $this->Form->input('param', ['id' => $key, 'type' => 'text', 'label' => $label, 'required' => 'true']);
		}
		
	echo $this->Form->hidden('id', ['value' => $numResult]);	
	echo '</fieldset>';
	echo $this->Form->end(__('Execute'));
	
	echo '</td>';
	echo '</tr>';
}
echo '</table></div>';


if(!empty($sqlResults))	{
	
	echo '<h1>'.$currentResults['name'].'</h1>';
	echo '<div class="table-responsive"><table class="table table-hover">';	

	echo '<tr>';	
	foreach($sqlResults as $values) {
		foreach($values as $key => $value) {
			echo '<th>'.$key.'</th>';
		}
		break;
	}
	echo '</tr>';
	
	
	foreach($sqlResults as $values) {
		echo '<tr>';
		foreach($values as $key => $value) {
			echo '<td>'.$value.'</td>';
		}
		echo '</tr>';
	}
	echo '</table></div>';
}
?>