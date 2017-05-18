<?php
echo $this->Html->script('moduleCtrlArticle.min');

$this->Html->addCrumb(__('Home'),array('controller' => 'Pages', 'action' => 'home'));
$this->Html->addCrumb(__('List Articles'), array('controller' => 'ProdGasArticles', 'action' => 'index'));
$this->Html->addCrumb(__('Edit Article'));
echo $this->Html->getCrumbList(array('class'=>'crumbs'));

$qta = $this->Form->value('ProdGasArticle.qta');
$prezzo = $this->Form->value('ProdGasArticle.prezzo');

echo '<div class="articles form">';

echo $this->Form->create('ProdGasArticle',array('id'=>'formGas','enctype' => 'multipart/form-data'));
?>
	<fieldset>
		<legend><?php echo __('Edit Article'); ?></legend>

         <div class="tabs">
             <ul>
                 <li><a href="#tabs-0"><span><?php echo __('Dati articolo'); ?></span></a></li>
                 <li><a href="#tabs-1"><span><?php echo __('Prezzo'); ?></span></a></li>
                 <li><a href="#tabs-2"><span><?php echo __('Condizioni d\'acquisto'); ?></span></a></li>
                 <li><a href="#tabs-3"><span><?php echo __('Immagine'); ?></span></a></li>
             </ul>

             <div id="tabs-0">
					<?php
					$i=0; 
					echo $this->Form->input('id');
					
					echo $this->Form->input('codice',array('id' => 'codice','style' => 'width:75px;','tabindex'=>($i+1), 'required' => 'false'));
						
					echo $this->Form->input('name',array('id' => 'name','tabindex'=>($i+1), 'required' => 'false'));

					echo $this->Form->input('ingredienti',array('tabindex'=>($i+1),  'class' => 'noeditor', 'cols' => '75', 'required' => 'false'));
						
					echo $this->Form->input('nota',array('tabindex'=>($i+1),  'class' => 'noeditor', 'cols' => '75', 'required' => 'false'));

					
					echo '<div class="input text">';
					echo '<label for="codice">'.__('Created').'</label>';
					if($this->request->data['ProdGasArticle']['created'] != '0000-00-00 00:00:00')
						echo $this->Time->i18nFormat($this->request->data['ProdGasArticle']['created'],"%e %B %Y");
					echo '</div>';

					echo '<div class="input text">';
					echo '<label for="codice">'.__('Modified').'</label>';
					if($this->request->data['ProdGasArticle']['modified'] != '0000-00-00 00:00:00')
						echo $this->Time->i18nFormat($this->request->data['ProdGasArticle']['modified'],"%e %B %Y");
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
							echo '<th rowspan="2"><span style="float:left;">Prezzo/UM<br />(Unit&agrave; di misura di riferimento)</span>';
							echo '<span style="float:right;">'.$this->App->drawTooltip('Unit&agrave; di misura di riferimento',__('toolFlag_presente_articlesorders'), $type='HELP',$pos='LEFT').'</span>';
							echo '</th>';
							echo '</tr>';

							echo '<tr>';
							echo '<th style="width:135px;">Quantit&agrave;</th>';
							echo '<th style="width:135px;">Unit&agrave; di misura</th>';
							echo '<tr>';
								
							echo "\r\n";
							echo '<tr>';
							echo "\r\n";
							echo '<td>';
							echo $this->Form->input('qta',array('id' => 'qta', 'value' => $this->request->data['ProdGasArticle']['qta_'], 'type' => 'text','label' => false,'size' => '5','tabindex'=>($i+1),'class' => 'double', 'required' => 'false'));
							echo '</td>';
							echo "\r\n";
							echo '<td>';
							echo $this->Form->input('um',array('id' => 'um', 'label' => false,'options' => $um,'tabindex'=>($i+1), 'required' => 'false'));
							echo '</td>';
							echo "\r\n";
							echo '<td>';
							echo $this->Form->input('prezzo',array('id' => 'prezzo', 'value' => $this->request->data['ProdGasArticle']['prezzo_'], 'type' => 'text','label' => false,'after' => '&nbsp;&euro;','size' => '5','tabindex'=>($i+1),'class' => 'double', 'required' => 'false'));
							echo '</td>';
							echo "\r\n";
							echo '<td class="prezzo_um_riferimento">';
							echo '</td>';
							echo '</tr>';
							echo '</table>';
							echo "\r\n";						
					?>
			</div>
			<div id="tabs-2">
					<?php
						echo $this->Form->input('qta_minima',array('id' => 'qta_minima', 'label' => __('qta_minima'), 'type' => 'text','size' => '2','tabindex'=>($i+1),'class' => 'noWidth', 'required' => 'false'));
						echo $this->Form->input('pezzi_confezione',array('id' => 'pezzi_confezione', 'type' => 'text','size' => '2','tabindex'=>($i+1),'after'=>$this->App->drawTooltip(__('pezzi_confezione'),__('toolTipPezziConfezione'),$type='INFO'),'class' => 'noWidth', 'required' => 'false'));
						echo $this->Form->input('qta_multipli',array('id' => 'qta_multipli', 'label' => __('qta_multipli'), 'type' => 'text','size' => '2','tabindex'=>($i+1),'after'=>$this->App->drawTooltip(__('qta_multipli'),__('toolTipQtaMultipli'),$type='INFO'),'class' => 'noWidth', 'required' => 'false'));
					?>
			</div>
			<div id="tabs-3">
					<?php
						if(isset($file1)) {
							echo '<div class="input">';
							echo '<img src="'.Configure::read('App.server').Configure::read('App.web.img.upload.prod_gas_article').'/'.$this->request->data['ProdGasArticle']['supplier_id'].'/'.$file1->name.'" />';	
							echo '&nbsp;&nbsp;&nbsp;'.$this->App->formatBytes($file1->size());
							echo '</div>';	
							echo $this->Form->checkbox('file1_delete', array('label' => 'Cancella file', 'value' => 'Y'));
							echo $this->Form->label('Cancella file');					
						}
														
						echo $this->Form->input('Document.img1', array(
						    'between' => '<br />',
						    'type' => 'file',
						     'label' => 'Carica una nuova immagine', 'tabindex'=>($i+1)
						));
						
						echo $this->element('legendaArticleImg');
					?>
				</div>
			</div>
	</fieldset>
<?php 
if(!empty($sort)) echo $this->Form->hidden('sort',array('value'=>$sort));
if(!empty($direction)) echo $this->Form->hidden('direction',array('value'=>$direction));
if(!empty($page)) echo $this->Form->hidden('page',array('value'=>$page));

echo $this->Form->end(__('Submit'));
?>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('List Articles'), array('action' => 'index',null,
																	'sort:'.$sort,'direction:'.$direction,'page:'.$page),
																	array('class'=>'action actionReload'));?></li>
		<li><?php echo $this->Html->link(__('Delete'), array('action' => 'context_articles_delete', $this->Form->value('ProdGasArticle.id'),
																	'sort:'.$sort,'direction:'.$direction,'page:'.$page),
																	array('class' => 'action actionDelete','title' => __('Delete'))); ?></li>
	</ul>
</div>

<script type="text/javascript">
jQuery(document).ready(function() {
	jQuery('#qta').focusout(function() {validateNumberField(this,'quantita\'');});
	jQuery('.double').focusout(function() {validateNumberField(this,'prezzo');});

	jQuery('#qta').focusout(function() {setArticlePrezzoUmRiferimento('', jQuery("input:radio[name='data[ProdGasArticle][um_riferimento]']").filter(':checked').val());});
	jQuery('#um').change(function() {setArticlePrezzoUmRiferimento('', jQuery("input:radio[name='data[ProdGasArticle][um_riferimento]']").filter(':checked').val());});
	jQuery('#prezzo').focusout(function() {setArticlePrezzoUmRiferimento('', jQuery("input:radio[name='data[ProdGasArticle][um_riferimento]']").filter(':checked').val());});

	setArticlePrezzoUmRiferimento('<?php echo $this->Form->value('ProdGasArticle.um');?>', '<?php echo $this->Form->value('ProdGasArticle.um_riferimento');?>');

	jQuery('#formGas').submit(function() {
		return true;
	});

	jQuery(function() {
		jQuery( ".tabs" ).tabs({
			event: "click"
		});
	});	
});
</script>