<?php
$this->Html->addCrumb(__('Home'), ['controller' => 'Pages', 'action' => 'home']);
// $this->Html->addCrumb(__('List Articles'), array('controller' => 'Articles', 'action' => 'context_articles_index'));
$this->Html->addCrumb(__('List Articles'), array('controller' => 'Connects', 'action' => 'index', 'c_to' => 'admin/articles&a_to=index-quick'));
$this->Html->addCrumb(__('Title Delete Article'));
echo $this->Html->getCrumbList(array('class'=>'crumbs'));

echo '<div class="suppliers form">';
echo $this->Form->create('Article', array('type' => 'post'));
echo '<fieldset>';
echo '<legend>'.__('Title Delete Article').'</legend>';

echo '<div class="input text"><label for="">'.__('Article').'</label> '.$this->request->data['Article']['name'].'</div>';

echo '<div class="input text"><label for="">'.__('Supplier').'</label> '.$this->request->data['SuppliersOrganization']['name'].'</div>';
		
if(isset($file1)) {
	echo '<div class="input">';
	echo '<img class="img-responsive-disabled" src="'.Configure::read('App.server').Configure::read('App.web.img.upload.article').'/'.$this->request->data['Article']['organization_id'].'/'.$file1->name.'" />';	
	echo '&nbsp;&nbsp;&nbsp;'.$this->App->formatBytes($file1->size());
	echo '</div>';	
}

if(empty($orderResults)) 
	echo '<div class="input text"><label for="">L\'articolo non è associato ad alcun ordine</label><span class="qtaZero">0</span></div>';	
else {

		switch($user->organization['Organization']['type']) {
			case 'PROD':
			break;
			case 'PRODGAS':        
				echo '<h3 class="title_details">'.__('Related ProdGas ArticlesOrder').'</h3>';
				
				echo '<div class="table-responsive"><table class="table table-hover">';
				echo '<tr>';
				echo '	<th>'.__('N').'</th>';
				echo '	<th colspan="2">'.__('GasOrganization').'</th>';
				echo '	<th>'.__('Order').'</th>';
				echo '	<th>'.__('DataInizio').'</th>';
				echo '  <th>'.__('DataFine').'</th>';
				echo '	<th>'.__('OpenClose').'</th>';				
				echo '</tr>';	
			
				foreach($orderResults as $numResult => $result) {
			
					echo "\r\n";
					echo '<tr>';
					echo '<td>'.((int)$numResult+1).'</td>';
					echo '<td>';
					echo ' <img width="50" class="img-responsive-disabled userAvatar" src="'.Configure::read('App.web.img.upload.content').'/'.$result['Organization']['img1'].'" alt="'.$result['Organization']['name'].'" /> ';	
					echo '</td>';			
					echo '<td>'.$result['Organization']['name'].'</td>';
					echo '<td></td>';
					echo '<td>';
					echo $this->Time->i18nFormat($result['Order']['data_inizio'],"%A %e %B %Y");
					echo '</td>';
					echo '<td>';
					echo $this->Time->i18nFormat($result['Order']['data_fine'],"%A %e %B %Y");
					if($result['Order']['data_fine_validation']!=Configure::read('DB.field.date.empty'))
						echo '<br />Riaperto fino a '.$this->Time->i18nFormat($result['Order']['data_fine_validation'],"%A %e %B %Y");
					echo '</td>';
					echo '<td>';
					echo $this->App->utilsCommons->getOrderTime($result['Order']);
					echo '</td>';
					echo '</tr>';
				} // foreach($results as $numResult => $result) 
				
				echo '</table></div>';

			break;
			case 'GAS':
            case 'SOCIALMARKET':
				echo '<div class="table-responsive"><table class="table table-hover">';
				echo '	<tr>';
				echo '		<th>'.__('N').'</th>';
				echo '		<th>'.__('DataInizio').'</th>';
				echo '		<th>'.__('DataFine').'</th>';
				echo '<th>'.__('OpenClose').'</th>';
				echo '<th>'.__('StatoElaborazione').'</th>';
				echo '<th>'.__('Created').'</th>';
				echo '</tr>';
					
				$delivery_id_old = 0;
				foreach ($orderResults as $i => $result) {
			
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
				}
				echo '</table></div>';
			break;
			default:
				self::x(__('msg_error_org_type'));
			break;
		}	
} // end if($results['ArticlesOrder']== 0) 
				
if(!empty($sort)) echo $this->Form->hidden('sort', ['value' => $sort]);
if(!empty($direction)) echo $this->Form->hidden('direction', ['value' => $direction]);
if(!empty($page)) echo $this->Form->hidden('page', ['value' => $page]);
echo $this->Form->hidden('id', ['value' => $this->request->data['Article']['id']]);
echo $this->Form->hidden('article_organization_id', ['value' => $this->request->data['Article']['organization_id']]);

if($isArticleInCart) {
	echo $this->Element('boxMsg', ['msg' => __('IsArticleInCart'), 'class_msg' => 'danger']); 
	echo $this->Form->end();
}
else 
if(!$isArticleInCart && !empty($orderResults)) {
	echo $this->Element('boxMsg', ['msg' => __('IsArticleInOrder'), 'class_msg' => 'danger']); 
	echo $this->Form->end(__('Submit Delete'));
}
else
	echo $this->Form->end(__('Submit Delete'));
?>
</div>

<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('List Articles'), array('controller' => 'Connects', 'action' => 'index', 'c_to' => 'admin/articles&a_to=index-quick'),
																	array('class'=>'action actionReload','title' => __('List Articles')));?></li>
		<li><?php echo $this->Html->link(__('Edit'), array('action' => 'context_articles_edit', $this->request->data['Article']['id'], 'article_organization_id' => $this->request->data['Article']['organization_id'],
																	'sort:'.$sort,'direction:'.$direction,'page:'.$page),
																	array('class' => 'action actionEdit','title' => __('Edit'))); ?></li>
	</ul>
</div>