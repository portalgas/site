<?php
/*
echo "<pre>";
print_r($this->request->data);
echo "</pre>";
*/
$this->Html->addCrumb(__('Home'),array('controller' => 'Pages', 'action' => 'home'));
$this->Html->addCrumb(__('List Articles'), array('controller' => 'Articles', 'action' => 'context_articles_index'));
$this->Html->addCrumb(__('View Article'));
echo $this->Html->getCrumbList(array('class'=>'crumbs'));

echo '<div class="articles form">';

echo $this->Form->create('Article',array('id'=>'formGas'));
?>
	<fieldset>
		<legend><?php echo __('View Article'); ?></legend>

         <div class="tabs">
             <ul>
                 <li><a href="#tabs-0"><span><?php echo __('Dati articolo'); ?></span></a></li>
                 <li><a href="#tabs-1"><span><?php echo __('Prezzo'); ?></span></a></li>
                 <li><a href="#tabs-2"><span><?php echo __('Condizioni d\'acquisto'); ?></span></a></li>
                 <li><a href="#tabs-3"><span><?php echo __('Immagine'); ?></span></a></li>
             </ul>

             <div id="tabs-0">
					<?php
					echo '<div class="input text ">';
					echo '<label>'.__('SuppliersOrganization').'</label>';
					echo $this->request->data['SuppliersOrganization']['name'];
					echo '</div>';
					
					if($user->organization['Organization']['hasFieldArticleCategoryId']=='Y') {
						echo '<div class="input text ">';
						echo '<label>'.__('Category').'</label>';
						echo $this->request->data['CategoriesArticle']['name'];
						echo '</div>';						
					}
		
					if($user->organization['Organization']['hasFieldArticleCodice']=='Y') {
						echo '<div class="input text ">';
						echo '<label>'.__('Code').'</label>';
						echo $this->request->data['Article']['codice'];
						echo '</div>';						
					}
					
					if($user->organization['Organization']['hasFieldArticleIngredienti']=='Y') {
						echo '<div class="input text ">';
						echo '<label>'.__('Ingredienti').'</label>';
						echo $this->request->data['Article']['ingredienti'];
						echo '</div>';						
					}
					
					echo '<div class="input text ">';
					echo '<label>'.__('Nota').'</label>';
					echo $this->request->data['Article']['nota'];
					echo '</div>';						
					?>
				</div>
            <div id="tabs-1">
						<?php
							echo "\r\n";
							echo '<table>';
							echo '<tr>';
							echo '<th colspan="2" style="text-align:center;border-bottom:medium none;">Confezione</th>';
							echo '<th rowspan="2">Prezzo</th>';
							echo '<th rowspan="2">Prezzo/UM (Unit&agrave; di misura di riferimento)</th>';
							echo '</tr>';

							echo '<tr>';
							echo '<th style="width:135px;">Quantit&agrave;</th>';
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
					?>
			</div>
			<div id="tabs-2">
					<?php
					echo '<div class="input text ">';
					echo '<label style="width:30% !important">'.__('qta_minima').'</label>';
					echo $this->request->data['Article']['qta_minima'];
					echo '</div>';	

					echo '<div class="input text ">';
					echo '<label style="width:30% !important">'.__('qta_massima').'</label>';
					echo $this->request->data['Article']['qta_massima'];
					echo '</div>';	

					echo '<div class="input text ">';
					echo '<label style="width:30% !important">'.__('pezzi_confezione').'</label>';
					echo $this->request->data['Article']['pezzi_confezione'];
					echo '</div>';	

					echo '<div class="input text ">';
					echo '<label style="width:30% !important">'.__('qta_multipli').'</label>';
					echo $this->request->data['Article']['qta_multipli'];
					echo '</div>';	

					if($user->organization['Organization']['hasFieldArticleAlertToQta']=='Y') {
						echo '<div class="input text ">';
						echo '<label style="width:30% !important">'.__('alert_to_qta').'</label>';
						echo $this->request->data['Article']['alert_to_qta'];
						echo '</div>';	
					}

					echo '<div class="input text ">';
					echo '<label style="width:30% !important">'.__('qta_minima_order').'</label>';
					echo $this->request->data['Article']['qta_minima_order'];
					echo '</div>';
					
					echo '<div class="input text ">';
					echo '<label style="width:30% !important">'.__('qta_massima_order').'</label>';
					echo $this->request->data['Article']['qta_massima_order'];
					echo '</div>';	
					?>
			</div>
			<div id="tabs-3">
					<?php
					if(!empty($this->request->data['Article']['img1']) && file_exists(Configure::read('App.root').Configure::read('App.img.upload.article').DS.$this->request->data['Article']['organization_id'].DS.$this->request->data['Article']['img1'])) {
						echo '<img src="'.Configure::read('App.server').Configure::read('App.web.img.upload.article').'/'.$this->request->data['Article']['organization_id'].'/'.$this->request->data['Article']['img1'].'" />';
					}	
					?>
				</div>
			</div>
	</fieldset>
<?php 
if(!empty($sort)) echo $this->Form->hidden('sort',array('value'=>$sort));
if(!empty($direction)) echo $this->Form->hidden('direction',array('value'=>$direction));
if(!empty($page)) echo $this->Form->hidden('page',array('value'=>$page));

echo $this->Form->end();
?>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('List Articles'), array('action' => 'context_articles_index',null,
																	'sort:'.$sort,'direction:'.$direction,'page:'.$page),
																	array('class'=>'action actionReload'));?></li>
	</ul>
</div>

<script type="text/javascript">
jQuery(document).ready(function() {
	jQuery(function() {
		jQuery( ".tabs" ).tabs({
			event: "click"
		});
	});	
});
</script>