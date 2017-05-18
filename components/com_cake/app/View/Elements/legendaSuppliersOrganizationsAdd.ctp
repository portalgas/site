<?php 
echo '<div class="legenda legenda-ico-info">';

if(!empty($results)) {
	echo "Se il produttore che desideri non è presente nella lista clicca su "; 
	echo $this->Html->link(__('Add Supplier Organization'), array('action' => 'add_new', null, 'sort:'.$sort,'direction:'.$direction,'page:'.$page), array('title' => __('Add Supplier Organization')));
}
else { 
	echo "Se desideri creare un nuovo produttore clicca su ";	
	echo $this->Html->link(__('Add Supplier Organization'), array('action' => 'add_new', null, 'sort:'.$sort,'direction:'.$direction,'page:'.$page), array('title' => __('Add Supplier Organization')));
} 
	
echo '</div>';
?>