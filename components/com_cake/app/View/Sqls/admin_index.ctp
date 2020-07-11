<?php
$this->App->d($results);
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

/*
 * menu'
 */
echo '<ul>';	
foreach($results as $numResult => $result) {
	echo '<li>';
	echo '<a href="#query-'.$numResult.'">'.$result['name'].'</a>';
	echo '</li>';
}
echo '</ul>';

foreach($results as $numResult => $result) {
	
	echo '<a id="query-'.$numResult.'"></a>';
	echo $this->Form->create('Sql', ['id' => 'formGas-'.$numResult]);	
	echo '<div class="row"';
	if(($numResult % 2) ==0)
		echo ' style="background-color:#f8f9fa;"';
	echo '>';
	echo '<div class="col col-md-4">';
	echo '<h1>'.$result['name'].'</h1>';	
	echo '</div>';
	echo '<div class="col col-md-6">';
	$params = $result['params'];
	if(!empty($params))
		foreach($params as $key => $label) {
			echo $this->Form->input('param', ['id' => $key, 'type' => 'text', 'label' => $label, 'required' => 'true']);
		}
	echo '</div>';
	echo '<div class="col col-md-2">';
	echo $this->Form->submit(__('Execute'), ['class' => 'btn btn-primary']);
	echo '</div>';
	echo '</div>'; // row
	
	/*
	echo '<pre class="shell" rel="'.$result['name'].'" style="width:750px;">';
	echo $result['sql'];
	echo '</pre>';
	*/
	echo $this->Form->hidden('id', ['value' => $numResult]);	
	echo $this->Form->end();
}


if(!empty($sqlResults))	{
	
	// echo '<h1>'.$currentResults['name'].' ('.count($sqlResults).')</h1>';
	echo '<pre class="shell" rel="'.$currentResults['name'].' ('.count($sqlResults).')">';
	echo $currentResults['sql'];
	echo '</pre>';
	echo '<div class="table-responsive"><table class="table table-hover">';	

	echo '<tr>';	
	foreach($sqlResults as $values) {
		echo '<th>'.__('N').'</th>';
		foreach($values as $key => $value) {
			echo '<th>'.$key.'</th>';
		}
		break;
	}
	echo '</tr>';
	
	
	foreach($sqlResults as $numResult => $values) {
		echo '<tr>';
		echo '<td>'.($numResult+1).'</td>';
		foreach($values as $key => $value) {
			echo '<td>'.$value.'</td>';
		}
		echo '</tr>';
	}
	echo '</table></div>';
}
?>