<?php
$this->Html->addCrumb(__('Home'), ['controller' => 'Pages', 'action' => 'home']);
$this->Html->addCrumb(__('ProdGasSupplier home'),array('controller' => 'ProdGasSuppliers', 'action' => 'index'));
$this->Html->addCrumb(__('ProdGasListArticlesGas'));
echo $this->Html->getCrumbList(array('class'=>'crumbs'));

echo '<div class="articles">';
echo '<h2 class="ico-articles">';
echo __('ProdGasListArticlesGas');
echo '<div class="actions-img">';
echo '</div>';
echo '</h2>';
	
echo '<div class="table-responsive"><table class="table">';
echo '<td style="width:50px;">';
echo '<img width="50" src="'.Configure::read('App.web.img.upload.content').'/'.$organizations['Organization']['img1'].'" alt="'.$organizations['Organization']['name'].'" />';
echo '</td>';
echo '<td><h3>'.$organizations['Organization']['name'].'</h3></td>';
echo '</table></div>';

	
	if(!empty($results)) { 

		echo $this->Form->create('Article',array('id' => 'formGas'));

		echo '<table cellpadding="0" cellspacing="0">';	
		echo '<tr>';	
			echo '<th>'.__('N').'</th>';	
			?>
			<th colspan="2"><?php echo $this->Paginator->sort('name','Nome prodotto');?></th>
			<th><?php echo $this->Paginator->sort('Package');?></th>
			<th><?php echo $this->Paginator->sort('PrezzoUnita');?></th>
			<th><?php echo $this->Paginator->sort('Prezzo/UM');?></th>
			<th><?php echo $this->Paginator->sort('bio',__('Bio'));?></th>
			<th><?php echo $this->Paginator->sort('stato',__('Stato'));?></th>
	</tr>
	<?php
	foreach ($results as $i => $result):
		$numRow = ((($this->Paginator->counter(array('format'=>'{:page}'))-1) * $SqlLimit) + $i+1);

		echo '<tr class="view">';
		echo '<td>'.$numRow.'</td>';
		
		
		echo '<td>';
		if(!empty($result['Article']['img1']) && file_exists(Configure::read('App.root').Configure::read('App.img.upload.article').DS.$result['Article']['organization_id'].DS.$result['Article']['img1'])) {
			echo '<img width="50" class="img-responsive-disabled userAvatar" src="'.Configure::read('App.server').Configure::read('App.web.img.upload.article').'/'.$result['Article']['organization_id'].'/'.$result['Article']['img1'].'" />';
		}		
		echo '</td>';
		
		echo '<td>'.$result['Article']['name'].'&nbsp;';
		echo $this->App->drawArticleNota($i, strip_tags($result['Article']['nota']));
		echo '</td>';
		
		echo '<td>';
		echo $this->App->getArticleConf($result['Article']['qta'], $result['Article']['um']);
		echo '</td>';
		echo '<td>';
		echo $result['Article']['prezzo_e'];
		echo '</td>';
		echo '<td>';
		echo $this->App->getArticlePrezzoUM($result['Article']['prezzo'], $result['Article']['qta'], $result['Article']['um'], $result['Article']['um_riferimento']);
		echo '</td>';
		echo '<td>';
		if($result['Article']['bio']=='Y') 
			echo '<span class="bio" title="'.Configure::read('bio').'"></span>';
		echo '</td>';	
		echo '<td class="stato_'.$this->App->traslateEnum($result['Article']['stato']).'" title="'.__('toolTipStato').'" ></td>';
		echo '</tr>';
	endforeach;
	
	echo '</table>';
?>
	<p>
	<?php
	echo $this->Paginator->counter(array(
	'format' => __('Page {:page} of {:pages}, showing {:current} records out of {:count} total, starting on record {:start}, ending on {:end}')
	));
	?>	</p>

	<div class="paging">
	<?php
		echo $this->Paginator->prev('< ' . __('previous'), array(), null, array('class' => 'prev disabled'));
		echo $this->Paginator->numbers(array('separator' => ''));
		echo $this->Paginator->next(__('next') . ' >', array(), null, array('class' => 'next disabled'));
	
		echo '</div>';
		
		echo '</fieldset>';
		
		echo $this->Form->end();
	}
echo '</div>';
?>