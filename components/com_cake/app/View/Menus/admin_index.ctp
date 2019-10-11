<?php
/*
echo "<pre>";
print_r($menus);
echo "</pre>";
*/

if(!empty($menus)) {
	echo '<ul class="menuLateraleItems">';
	
	foreach($menus as $menu) {
	
		$id = '';
		if(isset($menu['id']))
			$id = $menu['id'];
		
		$parameters = '';
		if(isset($menu['params'])) {
			foreach($menu['params'] as $key => $value)
				$parameters .= $key.'='.$value.'&'; 
		}

		echo '<li>';
		echo $this->Html->link($menu['label'], 
								['controller' => $menu['controller'], 
								 'action' => $menu['action'], 
								 $id, $parameters],
								 ['class' => 'bgLeft '.$menu['class'],
								  'title' => $menu['label']]
								);
		echo '</li>';
	}
	echo '</ul>';
}
?>