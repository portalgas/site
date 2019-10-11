<?php
$this->App->d($this->request->data, false);

$this->Html->addCrumb(__('Home'), ['controller' => 'Pages', 'action' => 'home']);
$this->Html->addCrumb(__('List Articles'), array('controller' => 'Articles', 'action' => 'context_articles_index'));
$this->Html->addCrumb(__('View Article'));
echo $this->Html->getCrumbList(array('class'=>'crumbs'));

echo '<div class="contentMenuLaterale">';

echo $this->Form->create('Article',array('id'=>'formGas'));
?>
	<fieldset>
		<legend><?php echo __('View Article'); ?></legend>

		<?php
			echo '<div class="tabs">';
			echo '<ul class="nav nav-tabs">'; // nav-tabs nav-pills
			echo '<li class="active"><a href="#tabs-0" data-toggle="tab">'.__('Dati articolo').'</a></li>';
			echo '<li><a href="#tabs-1" data-toggle="tab">'.__('Price').'</a></li>';
			echo '<li><a href="#tabs-2" data-toggle="tab">'.__('Condizioni d\'acquisto').'</a></li>';
			echo '<li><a href="#tabs-3" data-toggle="tab">'.__('Img').'</a></li>';			
			echo '</ul>';

			echo '<div class="tab-content">';
			echo '<div class="tab-pane fade active in" id="tabs-0">';
			
					/*
					 * dati owner_articles listino REFERENT / DES / SUPPLIER 
					 */	
					if(isset($organizationResults)) {
						echo '<div class="input text ">';
						echo '<label>'.__('organization_owner_articles').'</label> ';
						echo ' <img width="50" class="img-responsive-disabled userAvatar" src="'.Configure::read('App.web.img.upload.content').'/'.$organizationResults['Organization']['img1'].'" alt="'.$organizationResults['Organization']['name'].'" /> ';	
						echo $organizationResults['Organization']['name']; 
						echo '</div>';
					}
	
					echo '<div class="input text ">';
					echo '<label>'.__('SuppliersOrganization').'</label> ';
					echo $this->request->data['SuppliersOrganization']['name'];
					echo '</div>';
					
					if($user->organization['Organization']['hasFieldArticleCategoryId']=='Y') {
						echo '<div class="input text ">';
						echo '<label>'.__('Category').'</label> ';
						echo $this->request->data['CategoriesArticle']['name'];
						echo '</div>';						
					}
		
					if($user->organization['Organization']['hasFieldArticleCodice']=='Y') {
						echo '<div class="input text ">';
						echo '<label>'.__('Code').'</label> ';
						echo $this->request->data['Article']['codice'];
						echo '</div>';						
					}
					
					echo '<div class="input text ">';
					echo '<label>'.__('Name').'</label> ';
					echo $this->request->data['Article']['name'];
					echo '</div>';
						
					if($user->organization['Organization']['hasFieldArticleIngredienti']=='Y') {
						echo '<div class="input text ">';
						echo '<label>'.__('Ingredienti').'</label> ';
						echo $this->request->data['Article']['ingredienti'];
						echo '</div>';						
					}
					
					echo '<div class="input text ">';
					echo '<label>'.__('Nota').'</label> ';
					echo $this->request->data['Article']['nota'];
					echo '</div>';						

			echo '</div>';
			echo '<div class="tab-pane fade" id="tabs-1">';					
					
							echo "\r\n";
							echo '<table>';
							echo '<tr>';
							echo '<th colspan="2" style="text-align:center;border-bottom:medium none;">'.__('Package').'</th>';
							echo '<th rowspan="2">Prezzo</th>';
							echo '<th rowspan="2">Prezzo/UM (Unit&agrave; di misura di riferimento)</th>';
							echo '</tr>';

							echo '<tr>';
							echo '<th style="width:135px;">'.__('qta').'</th>';
							echo '<th style="width:135px;">Unit&agrave; di misura</th>';
							echo '<tr>';
								
							echo "\r\n";
							echo '<tr>';
							echo "\r\n";
							echo '<td>';
							echo $this->request->data['Article']['qta_'];
							echo '</td>';
							echo "\r\n";
							echo '<td>';
							echo $this->request->data['Article']['um'];
							echo '</td>';
							echo "\r\n";
							echo '<td>';
							echo $this->request->data['Article']['prezzo_e'];
							echo '</td>';
							echo '<td>';
							echo $this->App->getArticlePrezzoUM($this->request->data['Article']['prezzo'], $this->request->data['Article']['qta'], $this->request->data['Article']['um'], $this->request->data['Article']['um_riferimento']);
							echo '</td>';
							echo '</tr>';
							echo '</table>';
							echo "\r\n";							

			echo '</div>';
			echo '<div class="tab-pane fade" id="tabs-2">';							
							
					echo '<div class="input text ">';
					echo '<label style="width:30% !important">'.__('qta_minima').'</label> ';
					echo $this->request->data['Article']['qta_minima'];
					echo '</div>';	

					echo '<div class="input text ">';
					echo '<label style="width:30% !important">'.__('qta_massima').'</label> ';
					echo $this->request->data['Article']['qta_massima'];
					echo '</div>';	

					echo '<div class="input text ">';
					echo '<label style="width:30% !important">'.__('pezzi_confezione').'</label> ';
					echo $this->request->data['Article']['pezzi_confezione'];
					echo '</div>';	

					echo '<div class="input text ">';
					echo '<label style="width:30% !important">'.__('qta_multipli').'</label> ';
					echo $this->request->data['Article']['qta_multipli'];
					echo '</div>';	

					if($user->organization['Organization']['hasFieldArticleAlertToQta']=='Y') {
						echo '<div class="input text ">';
						echo '<label style="width:30% !important">'.__('alert_to_qta').'</label> ';
						echo $this->request->data['Article']['alert_to_qta'];
						echo '</div>';	
					}

					echo '<div class="input text ">';
					echo '<label style="width:30% !important">'.__('qta_minima_order').'</label> ';
					echo $this->request->data['Article']['qta_minima_order'];
					echo '</div>';
					
					echo '<div class="input text ">';
					echo '<label style="width:30% !important">'.__('qta_massima_order').'</label> ';
					echo $this->request->data['Article']['qta_massima_order'];
					echo '</div>';	

			echo '</div>';
			echo '<div class="tab-pane fade" id="tabs-3">';					
					
					if(!empty($this->request->data['Article']['img1']) && file_exists(Configure::read('App.root').Configure::read('App.img.upload.article').DS.$this->request->data['Article']['organization_id'].DS.$this->request->data['Article']['img1'])) {
						echo '<img class="img-responsive-disabled" src="'.Configure::read('App.server').Configure::read('App.web.img.upload.article').'/'.$this->request->data['Article']['organization_id'].'/'.$this->request->data['Article']['img1'].'" />';
					}	

			echo '</div>';
			echo '</div>'; // tab-content
			echo '</div>';
			echo '</fieldset>';
			
if(!empty($sort)) echo $this->Form->hidden('sort',array('value'=>$sort));
if(!empty($direction)) echo $this->Form->hidden('direction',array('value'=>$direction));
if(!empty($page)) echo $this->Form->hidden('page',array('value'=>$page));

echo $this->Form->end();
echo '</div>';

$links = [];
$links[] = $this->Html->link('<span class="desc animate"> '.__('List Articles').' </span><span class="fa fa-reply"></span>', array('controller' => 'Articles', 'action' => 'context_articles_index'), ['class' => 'animate', 'escape' => false]);
echo $this->Menu->draw($links);
?>