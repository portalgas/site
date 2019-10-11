<?php 
echo '<div class="legenda legenda-ico-info">';

if(!empty($results)) {
	echo "Se il produttore che desideri non è presente nella lista clicca su "; 
	echo $this->Html->link(__('Add Supplier Organization'), ['action' => 'add_new', null, 'sort:'.$sort,'direction:'.$direction,'page:'.$page], ['class' => 'btn btn-primary', 'title' => __('Add Supplier Organization')]);
}
else { 
	echo "Se desideri creare un nuovo produttore clicca su ";	
	echo $this->Html->link(__('Add Supplier Organization'), ['action' => 'add_new', null, 'sort:'.$sort,'direction:'.$direction,'page:'.$page], ['class' => 'btn btn-primary', 'title' => __('Add Supplier Organization')]);
} 
	
echo '</div>';
?>