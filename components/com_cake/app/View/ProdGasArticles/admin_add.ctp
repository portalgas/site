<?php
echo $this->Html->script('moduleCtrlProdGasArticle-v01.min');

$this->Html->addCrumb(__('Home'), ['controller' => 'Pages', 'action' => 'home']);
$this->Html->addCrumb(__('List Articles'), array('controller' => 'ProdGasArticles', 'action' => 'index'));
$this->Html->addCrumb(__('Add Article'));
echo $this->Html->getCrumbList(array('class'=>'crumbs'));
?>

<div class="contentMenuLaterale">
<?php 
echo $this->Form->create('ProdGasArticle',array('id'=>'formGas','enctype' => 'multipart/form-data'));?>

	<fieldset>
		<legend><?php echo __('Add Article'); ?></legend>

		<?php
			echo '<div class="tabs">';
			echo '<ul class="nav nav-tabs">'; // nav-tabs nav-pills
			echo '<li class="active"><a href="#tabs-0" data-toggle="tab">'.__('Dati articolo').'</a></li>';
			echo '<li><a href="#tabs-1" data-toggle="tab">'.__('Prezzo').'</a></li>';
			echo '<li><a href="#tabs-2" data-toggle="tab">'.__('Condizioni d\'acquisto').'</a></li>';
			echo '<li><a href="#tabs-3" data-toggle="tab">'.__('Immagine').'</a></li>';	
			if(!empty($syncronizeResults))		
				echo '<li><a href="#tabs-4" data-toggle="tab">'.__('ProdGasArticlesSyncronize').'</a></li>';
			echo '</ul>';

			echo '<div class="tab-content">';
			echo '<div class="tab-pane fade active in" id="tabs-0">';

					$i=0;
					echo $this->Form->input('codice',array('style' => 'width:75px','tabindex'=>($i+1), 'required'=>'false'));
						
					echo $this->Form->input('name',array('id' => 'name','tabindex'=>($i+1),  'required' => 'false'));

					echo $this->Form->input('ingredienti',array('tabindex'=>($i+1),  'class' => 'noeditor', 'cols' => '75','required' => 'false'));
						
					echo $this->Form->input('nota',array('tabindex'=>($i+1), 'class' => 'noeditor', 'cols' => '75', 'required' => 'false'));
			
			echo '</div>';
			echo '<div class="tab-pane fade" id="tabs-1">';
			
						echo "\r\n";
						echo '<table>';
						echo '<tr>';
						echo '<th colspan="2" style="text-align:center;border-bottom:medium none;">'.__('Package').'</th>';
						echo '<th rowspan="2">Prezzo</th>';
						echo '<th rowspan="2"><span style="float:left;">Prezzo/UM<br />(Unit&agrave; di misura di riferimento)</span>';
						echo '<span style="float:right;">'.$this->App->drawTooltip('Unit&agrave; di misura di riferimento',__('toolTipUmRiferimento'),$type='HELP',$pos='LEFT').'</span>';
						echo '</th>';
						echo '</tr>';
						
						echo '<tr>';
						echo '<th style="width:135px;">'.__('qta').'</th>';
						echo '<th style="width:135px;">Unit&agrave; di misura</th>';
						echo '<tr>';
						
						echo "\r\n";
						echo '<tr>';
						echo "\r\n";
						echo '<td>';
						echo $this->Form->input('qta',array('id' => 'qta', 'type' => 'text', 'label' => false,'size' => '5','tabindex'=>($i+1),'class' => 'double', 'required'=>'false'));
						echo '</td>';
						echo "\r\n";
						echo '<td>';
						echo $this->Form->input('um',array('id' => 'um', 'label' => false,'options' => $um,'tabindex'=>($i+1), 'required'=>'false'));
						echo '</td>';
						echo "\r\n";
						echo '<td style="white-space: nowrap;">';
						echo $this->Form->input('prezzo',array('id' => 'prezzo', 'type' => 'text','label' => false,'after' => '&nbsp;&euro;','size' => '5','tabindex'=>($i+1),'class' => 'double', 'style' => 'display:inline', 'required'=>'false'));
						echo '</td>';
						echo "\r\n";
						echo '<td class="prezzo_um_riferimento">';
						echo '</td>';
						echo '</tr>';
						echo '</table>';
						echo "\r\n";

			echo '</div>';
			echo '<div class="tab-pane fade" id="tabs-2">';
			
						echo $this->Form->input('qta_minima',array('id' => 'qta_minima', 'label' => __('qta_minima'), 'type' => 'text','size' => '2','default' => '1','tabindex'=>($i+1), 'required'=>'false'));
						echo $this->Form->input('pezzi_confezione',array('id' => 'pezzi_confezione', 'type' => 'text','size' => '2','default' => '1','after'=>$this->App->drawTooltip(__('pezzi_confezione'),__('toolTipPezziConfezione'),$type='INFO'),'tabindex'=>($i+1), 'required'=>'false'));
						echo $this->Form->input('qta_multipli',array('id' => 'qta_multipli', 'label' => __('qta_multipli'), 'type' => 'text','size' => '2','default' => '1','after'=>$this->App->drawTooltip(__('qta_multipli'),__('toolTipQtaMultipli'),$type='INFO'),'tabindex'=>($i+1), 'required'=>'false'));

			echo '</div>';
			echo '<div class="tab-pane fade" id="tabs-3">';
			
					echo $this->Form->input('Document.img1', array(
					    'between' => '<br />',
					    'type' => 'file',
					     'label' => 'Carica una nuova immagine', 'tabindex'=>($i+1)
					));
						
					echo $this->element('legendaArticleImg');
			echo '</div>';
			
			if(!empty($syncronizeResults)) {
				echo '<div class="tab-pane fade" id="tabs-4">';
				
				include(dirname(__FILE__). DS . 'box_tab_syncronize.php');
	
				echo '</div>';
			} // end if(!empty($syncronizeResults))
						
									
			echo '</div>'; // tab-content
			echo '</div>';
			echo '</fieldset>';
echo $this->Form->hidden('action_post',array('id' => 'action_post','value' => 'index'));

echo $this->Form->submit(__('Submit Post Index'),array('id' => 'action_post_index', 'div'=> 'submitMultiple'));
echo $this->Form->submit(__('Submit Post Add Article'),array('id' => 'action_post_add', 'div'=> 'submitMultiple','class' => 'buttonBlu'));

echo $this->Form->end();
echo '</div>';

$links = [];
$links[] = $this->Html->link('<span class="desc animate"> '.__('List Articles').' </span><span class="fa fa-reply"></span>', array('controller' => 'ProdGasArticles', 'action' => 'index'), ['class' => 'animate', 'escape' => false]);
echo $this->Menu->draw($links);
?>
<script type="text/javascript">
$(document).ready(function() {
	$('#qta').focusout(function() {validateNumberField(this,'quantita\'');});
	$('.double').focusout(function() {validateNumberField(this,'prezzo');});

	$('#qta').focusout(function() {setArticlePrezzoUmRiferimento();});
	$('#um').change(function() {setArticlePrezzoUmRiferimento();});
	$('#prezzo').focusout(function() {setArticlePrezzoUmRiferimento();});

	$('#action_post_index').click(function() {	
		$('#action_post').val('index');
	});
	$('#action_post_add').click(function() {	
		$('#action_post').val('add');
	});
	
	$('#formGas').submit(function() {
		if(!moduleCtrlArticle()) return false;
		else
			return true;
	});
});
</script>