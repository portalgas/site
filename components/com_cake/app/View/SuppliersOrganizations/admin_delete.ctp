<?php
$this->Html->addCrumb(__('Home'), ['controller' => 'Pages', 'action' => 'home']);
$this->Html->addCrumb(__('List Suppliers'), array('controller' => 'SuppliersOrganizations', 'action' => 'index'));
$this->Html->addCrumb(__('Title Delete Supplier Organization'));
echo $this->Html->getCrumbList(array('class'=>'crumbs'));

echo '<div class="suppliers form">';
echo $this->Form->create('SuppliersOrganization', ['type' => 'post']);
echo '<fieldset>';
echo '<legend>'.__('Title Delete Supplier Organization').'</legend>';

echo '<div class="input text"><label for="">'.__('Category').'</label> '.$results['CategoriesSupplier']['name'].'</div>';
echo '<div class="input text"><label for="">'.__('Business name').'</label> '.$results['SuppliersOrganization']['name'].'</div>';
echo '<div class="input text"><label for="">'.__('Description').'</label> '.$results['Supplier']['descrizione'].'</div>';

$this->Element('boxMsg', ['msg' => "Elementi associati che verranno cancellati definitivamente"]); 

(count($results['Article']) > 0 ? $class = 'qtaUno' : $class = 'qtaZero');
echo '<div class="input text"><label for="">'.__('TotaleArticles').'</label><span class="'.$class.'">'.count($results['Article']).'</span></div>';

(count($results['Order']) > 0 ? $class = 'qtaUno' : $class = 'qtaZero');
echo '<div class="input text"><label for="">'.__('TotaleOrders').'</label><span class="'.$class.'">'.count($results['Order']).'</span></div>';

(count($results['SuppliersOrganizationsReferent']) > 0 ? $class = 'qtaUno' : $class = 'qtaZero');
echo '<div class="input text"><label for="">'.__('TotaleSuppliersOrganizationsReferents').'</label><span class="'.$class.'">'.count($results['SuppliersOrganizationsReferent']).'</span></div>';

(!empty($results['DesSupplier']) ? $class = 'qtaUno' : $class = 'qtaZero');
(!empty($results['DesSupplier']) ? $tot = 1 : $tot = 0);
echo '<div class="input text"><label for="">'.__('TotaleDesSupplier').'</label><span class="'.$class.'">'.$tot.'</span></div>';

echo '</fieldset>';

echo $this->Form->hidden('id',array('value' => $results['SuppliersOrganization']['id']));
echo $this->Form->end(__('Submit Delete'));

echo '</div>';
?>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('List Suppliers Organization'), array('action' => 'index'),array('class'=>'action actionReload'));?></li>
		<li><?php echo $this->Html->link(__('Edit'), array('action' => 'edit', $results['SuppliersOrganization']['id']),array('class' => 'action actionEdit','title' => __('Edit'))); ?></li>
	</ul>
</div>