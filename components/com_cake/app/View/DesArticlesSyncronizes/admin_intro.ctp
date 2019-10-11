<?php
$this->Html->addCrumb(__('Home'), ['controller' => 'Pages', 'action' => 'home']);
$this->Html->addCrumb(__('Des'),array('controller' => 'Des', 'action' => 'index'));
$this->Html->addCrumb(__('DesArticlesSyncronizesIntro'));
echo $this->Html->getCrumbList(array('class'=>'crumbs'));

echo '<div class="organizations" style="min-height:450px;">';

echo '<div class="legenda legenda-ico-info" style="float:none;">';
echo "Aggiorna il tuo listino articoli con quello di un'altro G.A.S.";
echo '</div>';

echo $this->Form->create('DesArticlesSyncronize',array('id' => 'formGas'));
echo '<fieldset>';

$options = array('label' => 'G.A.S.',
				'options' => $desOrganizations,
				'empty' => Configure::read('option.empty'),
				 'escape' => false);
if(count($desOrganizations) > Configure::read('HtmlSelectWithSearchNum'))
	$options += array('class'=> 'selectpicker', 'data-live-search' => true);

echo $this->Form->input('organization_id',$options);

$options = array('options' => $ACLdesSuppliers,
				'empty' => Configure::read('option.empty'),
				 'escape' => false);
if(count($ACLdesSuppliers) > Configure::read('HtmlSelectWithSearchNum'))
	$options += array('class'=> 'selectpicker', 'data-live-search' => true);

echo $this->Form->input('des_supplier_id',$options);
 	
echo '</fieldset>';

echo $this->Form->end(__('Filtra'));

echo '</div>';
?>