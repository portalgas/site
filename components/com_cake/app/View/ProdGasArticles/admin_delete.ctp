<?php
$this->Html->addCrumb(__('Home'), ['controller' => 'Pages', 'action' => 'home']);
$this->Html->addCrumb(__('List Articles'), array('controller' => 'Articles', 'action' => 'index'));
$this->Html->addCrumb(__('Title Delete Article'));
echo $this->Html->getCrumbList(array('class'=>'crumbs'));

echo '<div class="suppliers form">';
echo $this->Form->create('ProdGasArticle', array('type' => 'post'));
echo '<fieldset>';
echo '<legend>'.__('Title Delete Article').'</legend>';

echo '<div class="input text"><label for="">'.__('Article').'</label> '.$this->request->data['ProdGasArticle']['name'].'</div>';

		
if(isset($file1)) {
	echo '<div class="input">';
	echo '<img class="img-responsive-disabled" src="'.Configure::read('App.server').Configure::read('App.web.img.upload.prod_gas_article').'/'.$this->request->data['ProdGasArticle']['organization_id'].'/'.$file1->name.'" />';	
	echo '&nbsp;&nbsp;&nbsp;'.$this->App->formatBytes($file1->size());
	echo '</div>';	
}
		
if(empty($promotionsResults)) 
	echo '<div class="input text"><label for="">L\'articolo non è associato ad alcuna promozione</label><span class="qtaZero">0</span></div>';	
else {
	echo '<div class="input text"><label for="">L\'articolo è associato alle seguenti promozioni:</label></div>';
	
	
		echo '<table cellpadding="0" cellspacing="0">';
		echo '	<tr>';
		echo '		<th>'.__('N').'</th>';
		echo '		<th>'.__('DataInizio').'</th>';
		echo '		<th>'.__('DataFine').'</th>';
		echo '<th>'.__('OpenClose').'</th>';
		echo '<th>'.__('StatoElaborazione').'</th>';
		echo '<th>'.__('Created').'</th>';
		echo '</tr>';
			
		foreach ($results as $i => $result):
		
			echo '<tr class="view">';
			echo '<td>'.($i+1).'</td>';
			echo '<td style="white-space:nowrap;">'.$this->Time->i18nFormat($result['ProdGasPromotion']['data_inizio'],"%A %e %B %Y").'</td>';
			echo '<td style="white-space:nowrap;">'.$this->Time->i18nFormat($result['ProdGasPromotion']['data_fine'],"%A %e %B %Y").'</td>';
			echo '<td style="white-space:nowrap;">'.$this->App->utilsCommons->getOrderTime($result['ProdGasPromotion']).'</td>';
			echo '<td>';
			echo $this->App->drawOrdersStateDiv($result);
			echo '&nbsp;';
		    echo __($result['ProdGasPromotion']['state_code'].'-label');
			echo '</td>';
			echo '<td style="white-space: nowrap;">'.$this->App->formatDateCreatedModifier($result['ProdGasPromotion']['created']).'</td>';
			echo '</tr>';

		endforeach; 
		
		echo '</table>';
} 
				
echo $this->Form->hidden('id',array('value' => $this->request->data['ProdGasArticle']['id']));

if(!empty($sort)) echo $this->Form->hidden('sort',array('value'=>$sort));
if(!empty($direction)) echo $this->Form->hidden('direction',array('value'=>$direction));
if(!empty($page)) echo $this->Form->hidden('page',array('value'=>$page));

if($promotionsResults) {
	echo $this->Element('boxMsg',array('msg' => __('IsArticleInCart'))); 
	echo $this->Form->end();
}
else
	echo $this->Form->end(__('Submit Delete'));
?>
</div>

<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('List Articles'), array('action' => 'index',null,
																	'sort:'.$sort,'direction:'.$direction,'page:'.$page),
																	array('class'=>'action actionReload','title' => __('List Articles')));?></li>
		<li><?php echo $this->Html->link(__('Edit'), array('action' => 'context_articles_edit', $this->request->data['Article']['id'],
																	'sort:'.$sort,'direction:'.$direction,'page:'.$page),
																	array('class' => 'action actionEdit','title' => __('Edit'))); ?></li>
	</ul>
</div>