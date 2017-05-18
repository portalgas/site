<?php
$this->Html->addCrumb(__('Home'),array('controller' => 'Pages', 'action' => 'home'));
$this->Html->addCrumb(__('List Articles'), array('controller' => 'Articles', 'action' => 'context_articles_index'));
$this->Html->addCrumb(__('Title Delete Article'));
echo $this->Html->getCrumbList(array('class'=>'crumbs'));

echo '<div class="suppliers form">';
echo $this->Form->create('Article', array('type' => 'post'));
echo '<fieldset>';
echo '<legend>'.__('Title Delete Article').'</legend>';

echo '<div class="input text"><label for="">'.__('Article').'</label>'.$this->request->data['Article']['name'].'</div>';

echo '<div class="input text"><label for="">'.__('Supplier').'</label>'.$this->request->data['SuppliersOrganization']['name'].'</div>';
		
if(isset($file1)) {
	echo '<div class="input">';
	echo '<img src="'.Configure::read('App.server').Configure::read('App.web.img.upload.article').'/'.$this->request->data['Article']['organization_id'].'/'.$file1->name.'" />';	
	echo '&nbsp;&nbsp;&nbsp;'.$this->App->formatBytes($file1->size());
	echo '</div>';	
}

if(!$isArticleInCart) 
	echo $this->Element('boxMsg',array('msg' => "Elementi associati che verranno cancellati definitivamente")); 

if(empty($results)) 
	echo '<div class="input text"><label for="">L\'articolo non è associato ad alcun ordine</label><span class="qtaZero">0</span></div>';	
else {
	echo '<div class="input text"><label for="">L\'articolo è associato ai seguenti ordini:</label></div>';
	
	
		echo '<table cellpadding="0" cellspacing="0">';
		echo '	<tr>';
		echo '		<th>'.__('N').'</th>';
		echo '		<th>'.__('Data inizio').'</th>';
		echo '		<th>'.__('Data fine').'</th>';
		echo '<th>'.__('Aperto/Chiuso').'</th>';
		echo '<th>'.__('stato_elaborazione').'</th>';
		echo '<th>'.__('Created').'</th>';
		echo '</tr>';
			
		$delivery_id_old = 0;
		foreach ($results as $i => $result):

			if($delivery_id_old==0 || $delivery_id_old!=$result['Delivery']['id']) {
				
				echo '<tr><td class="trGroup" colspan="12">';
				
				if($result['Delivery']['sys']=='N') {
					echo __('Delivery').': '.h($result['Delivery']['luogoData']);
					
					echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
					if($result['Delivery']['daysToEndConsegna']<0) {
						echo '<span style="color:red;">Chiusa</span>';
					}
					else {
						echo '<span style="color:green;">Aperta';
						if($result['Delivery']['daysToEndConsegna']==0) echo '(scade oggi)';
						else echo '(per ancora '.$result['Delivery']['daysToEndConsegna'].'&nbsp;gg)';
						echo '</span>';
					}
				}
				else {
					echo __('Delivery').': '.h($result['Delivery']['luogo']);
				}
				echo '</td></tr>';
			}
		
			echo '<tr class="view">';
			echo '<td>'.($i+1).'</td>';
			echo '<td style="white-space:nowrap;">'.$this->Time->i18nFormat($result['Order']['data_inizio'],"%A %e %B %Y").'</td>';
			echo '<td style="white-space:nowrap;">'.$this->Time->i18nFormat($result['Order']['data_fine'],"%A %e %B %Y").'</td>';
			echo '<td style="white-space:nowrap;">'.$this->App->utilsCommons->getOrderTime($result['Order']).'</td>';
			echo '<td>';
			echo $this->App->drawOrdersStateDiv($result);
			echo '&nbsp;';
		    echo __($result['Order']['state_code'].'-label');
			echo '</td>';
			if($user->organization['Organization']['hasVisibility']=='Y') {
				echo '<td title="'.__('toolTipsVisibleFrontEnd').'" class="stato_'.$this->App->traslateEnum($result['Order']['isVisibleFrontEnd']).'"></td>';
				echo '<td title="'.__('toolTipVisibleBackOffice').'" class="stato_'.$this->App->traslateEnum($result['Order']['isVisibleBackOffice']).'"></td>';		
			}
			echo '<td style="white-space: nowrap;">'.$this->App->formatDateCreatedModifier($result['Order']['created']).'</td>';
			echo '</tr>';

			$delivery_id_old=$result['Delivery']['id'];
		endforeach; 
		
		echo '</table>';
} // end if($results['ArticlesOrder']== 0) 
				
echo $this->Form->hidden('id',array('value' => $this->request->data['Article']['id']));

if(!empty($sort)) echo $this->Form->hidden('sort',array('value'=>$sort));
if(!empty($direction)) echo $this->Form->hidden('direction',array('value'=>$direction));
if(!empty($page)) echo $this->Form->hidden('page',array('value'=>$page));

if($isArticleInCart) {
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
		<li><?php echo $this->Html->link(__('List Articles'), array('action' => 'context_articles_index',null,
																	'sort:'.$sort,'direction:'.$direction,'page:'.$page),
																	array('class'=>'action actionReload','title' => __('List Articles')));?></li>
		<li><?php echo $this->Html->link(__('Edit'), array('action' => 'context_articles_edit', $this->request->data['Article']['id'],
																	'sort:'.$sort,'direction:'.$direction,'page:'.$page),
																	array('class' => 'action actionEdit','title' => __('Edit'))); ?></li>
	</ul>
</div>