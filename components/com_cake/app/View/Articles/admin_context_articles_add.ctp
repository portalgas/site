<?php
echo $this->Html->script('moduleCtrlArticle-v02.min');

$this->Html->addCrumb(__('Home'), ['controller' => 'Pages', 'action' => 'home']);
$this->Html->addCrumb(__('List Articles'), array('controller' => 'Articles', 'action' => 'context_articles_index'));
$this->Html->addCrumb(__('Add Article'));
echo $this->Html->getCrumbList(array('class'=>'crumbs'));

echo '<div class="contentMenuLaterale">';

echo $this->Form->create('Article',array('id'=>'formGas','enctype' => 'multipart/form-data'));

echo '<fieldset>';
echo '<legend>'.__('Add Article').'</legend>';

echo '<div class="tabs">';
echo '<ul class="nav nav-tabs">'; // nav-tabs nav-pills
echo '<li class="active"><a href="#tabs-0" data-toggle="tab">'.__('ArticleDati').'</a></li>';
echo '<li><a href="#tabs-1" data-toggle="tab">'.__('Price').'</a></li>';
echo '<li><a href="#tabs-2" data-toggle="tab">'.__('ConditionsOfPurchase').'</a></li>';
echo '<li><a href="#tabs-3" data-toggle="tab">'.__('Img').'</a></li>';			
echo '</ul>';

echo '<div class="tab-content">';
echo '<div class="tab-pane fade active in" id="tabs-0">';		
			
		$i=0;
		if($user->organization['Organization']['type']=='GAS' || $user->organization['Organization']['type']=='PRODGAS') {
			$options = ['id' => 'supplier_organization_id', 
						  'options' => $ACLsuppliersOrganization,
						  'default'=> $supplier_organization_id, 'tabindex'=>($i+1),'escape' => false, 'required' => 'false'];
			if(count($ACLsuppliersOrganization)> 1) 
				$options += ['empty' => Configure::read('option.empty')];									  
			if(count($ACLsuppliersOrganization) > Configure::read('HtmlSelectWithSearchNum')) 
				$options += ['class'=> 'selectpicker', 'data-live-search' => true];
			echo $this->Form->input('supplier_organization_id', $options);						
		}
		
		if($user->organization['Organization']['hasFieldArticleCategoryId']=='Y')
			echo $this->Form->input('category_article_id', ['id' => 'category_article_id', 'options' => $categories, 'empty' => Configure::read('option.empty'),'tabindex'=>($i+1),'required' => false, 'escape' => false]);
		
		if($user->organization['Organization']['hasFieldArticleCodice']=='Y')
			echo $this->Form->input('codice', ['style' => 'width:125px','tabindex'=>($i+1), 'required'=>'false']);
			
		echo $this->Form->input('name', ['id' => 'name','tabindex'=>($i+1),  'required' => 'false']);

		if($user->organization['Organization']['hasFieldArticleIngredienti']=='Y')
			echo $this->Form->input('ingredienti', ['tabindex'=>($i+1),  'class' => 'noeditor', 'cols' => '75', 'required' => 'false']);
			
		echo $this->Form->input('nota', ['tabindex'=>($i+1), 'class' => 'noeditor', 'cols' => '75', 'required' => 'false']);

		echo $this->Form->drawFormCheckbox('Article', 'article_type_id', ['options' => $ArticlesTypeResults, 'selected'=> '', 'label'=>__('Type'), 'tabindex'=>($i+1), 'required'=>'false']);
		
		/*
		 * stato
		*/
		echo $this->element('legendaArticleAddStato', ['context' => 'article', 'results' => $this->request->data, 'isUserPermissionArticlesOrder' => $this->App->isUserPermissionArticlesOrder($user)]);
		
		$options = ['options' => $stato, 'value' => 'Y', 'label'=>__('Stato'), 'tabindex'=>($i+1), 'required'=>'false'];
		echo $this->App->drawFormRadio('Article','stato', $options);
						
		$options = ['options' => $flag_presente_articlesorders, 'value' => 'Y', 'label'=>__('FlagPresenteArticlesorders'), 'tabindex'=>($i+1), 'required'=>'false'];
		echo $this->App->drawFormRadio('Article','flag_presente_articlesorders', $options);

echo '</div>';
echo '<div class="tab-pane fade" id="tabs-1">';

			echo "\r\n";
			echo '<table>';
			echo '<tr>';
			echo '<th colspan="2" style="text-align:center;border-bottom:medium none;">'.__('Package').'</th>';
			echo '<th rowspan="2" colspan="2"></th>';
			echo '<th rowspan="2">'.__('Price').'</th>';
			echo '<th rowspan="2"><span style="float:left;">'.__('Prezzo/UM').'<br />(Unit&agrave; di misura di riferimento)</span>';
			echo '<span style="float:right;">'.$this->App->drawTooltip('Unit&agrave; di misura di riferimento',__('toolTipUmRiferimento'),$type='HELP',$pos='LEFT').'</span>';
			echo '</th>';
			echo '</tr>';
			
			echo '<tr>';
			echo '<th style="width:135px;">'.__('qta').'</th>';
			echo '<th style="width:135px;">'.__('UnitOfMeasure').'</th>';
			echo '<tr>';
			
			echo "\r\n";
			echo '<tr>';
			echo "\r\n";
			echo '<td></td>';
			echo "\r\n";
			echo '<td></td>';
			
			echo "\r\n";
			echo '<td colspan="2">';
			echo $this->Form->input('prezzo_con_iva', ['id' => 'prezzo_con_iva', 'type' => 'text', 'label' => __('PriceWithVAT'), 'tabindex'=> 12, 'class' => 'double', 'style' => 'display:inline', 'required'=>'false']);
			echo '</td>';
			echo '<td></td>';
			
			echo '<td></td>';
			echo '</tr>';
			
			
			echo "\r\n";
			echo '<tr>';
			echo "\r\n";
			echo '<td>';
			echo $this->Form->input('qta', ['id' => 'qta', 'type' => 'text', 'label' => false,'tabindex'=> 10,'class' => 'double', 'required'=>'false']);
			echo '</td>';
			echo "\r\n";
			echo '<td>';
			echo $this->Form->input('um', ['id' => 'um', 'label' => false,'options' => $um,'tabindex'=> 11, 'required'=>'false']);
			echo '</td>';
			
			echo "\r\n";
			echo '<td></td>';
			echo '<td></td>';
			echo '<td style="white-space: nowrap;">';
			echo $this->Form->input('prezzo', ['id' => 'prezzo', 'type' => 'text','label' => false,'after' => '&nbsp;&euro;','tabindex'=> 13,'class' => 'double', 'style' => 'display:inline', 'required'=>'false']);
			echo '</td>';	
			
			echo '<td class="prezzo_um_riferimento" style="padding-left:25px;">';
			echo '</td>';
			echo '</tr>';
			
			
			
			echo "\r\n";
			echo '<tr>';
			echo '<td></td>';
			echo '<td></td>';
		
			echo '<td>';
			echo $this->Form->input('prezzo_senza_iva', ['id' => 'prezzo_senza_iva', 'type' => 'text','label' => __('PriceWithoutVAT'), 'class' => 'double', 'style' => 'display:inline', 'required'=>'false']);
			echo '</td>';
			echo '<td style="white-space: nowrap; width:75px;">';
			echo $this->Form->input('iva', ['id' => 'iva', 'label' => 'iva', 'options' => $ivas, 'default' => $iva,'required'=>'false']);
			echo '</td>';
			echo '<td></td>';
			
			echo '<td></td>';
			echo '</tr>';

			
			echo '</table>';
			echo "\r\n";

echo '</div>';
echo '<div class="tab-pane fade" id="tabs-2">';

			$i=13;
			
			echo $this->Form->input('pezzi_confezione', ['id' => 'pezzi_confezione', 'type' => 'text','size' => '2','default' => '1','after'=>$this->App->drawTooltip(__('pezzi_confezione'),__('toolTipPezziConfezione'),$type='INFO'),'tabindex'=>($i+1), 'required'=>'false']);
			echo $this->Form->input('qta_minima', ['id' => 'qta_minima', 'label' => __('qta_minima'), 'type' => 'text','size' => '2','default' => '1','tabindex'=>($i+1), 'required'=>'false']);
			echo $this->Form->input('qta_massima', ['id' => 'qta_massima', 'label' => __('qta_massima'), 'type' => 'text','size' => '2','default' => '0','tabindex'=>($i+1), 'required'=>'false']);
			echo $this->Form->input('qta_multipli', ['id' => 'qta_multipli', 'label' => __('qta_multipli'), 'type' => 'text','size' => '2','default' => '1','after'=>$this->App->drawTooltip(__('qta_multipli'),__('toolTipQtaMultipli'),$type='INFO'),'tabindex'=>($i+1), 'required'=>'false']);
			if($user->organization['Organization']['hasFieldArticleAlertToQta']=='Y')
				echo $this->Form->input('alert_to_qta', ['id' => 'alert_to_qta', 'type' => 'text','size' => '2','default' => '0','after'=>$this->App->drawTooltip(__('alert_to_qta'),__('toolTipAlertToQta'),$type='INFO'),'tabindex'=>($i+1), 'required'=>'false']);
			
			/*
			 * settaggi rispetto all'ordine
			 */
			echo $this->Form->input('qta_minima_order', ['id' => 'qta_minima_order', 'label' => __('qta_minima_order'), 'type' => 'text','default' => '0','after'=>$this->App->drawTooltip(__('qta_minima_order'),__('toolTipQtaMinOrder'),$type='INFO'),'tabindex'=>($i+1), 'required'=>'false']);						
			echo $this->Form->input('qta_massima_order', ['id' => 'qta_massima_order', 'label' => __('qta_massima_order'), 'type' => 'text','default' => '0','after'=>$this->App->drawTooltip(__('qta_massima_order'),__('toolTipQtaMaxOrder'),$type='INFO'),'tabindex'=>($i+1), 'required'=>'false']);						

echo '</div>';
echo '<div class="tab-pane fade" id="tabs-3">';

		echo $this->Form->input('Document.img1', [
			'between' => '<br />',
			'type' => 'file',
			'accept' => '.jpg, .jpeg, .gif, .png',
			 'label' => 'Carica una nuova immagine', 'tabindex'=>($i+1)
		]);
			
		echo $this->element('legendaArticleImg');
		
echo '</div>';
echo '</div>'; // tab-content
echo '</div>';
echo '</fieldset>';
			
echo $this->Form->hidden('action_post', ['id' => 'action_post','value' => 'context_articles_index']);

echo $this->Form->submit(__('Submit Post Index'), ['id' => 'action_post_index', 'div'=> 'submitMultiple']);
echo '&nbsp;';
echo $this->Form->submit(__('Submit Post Add Article'), ['id' => 'action_post_add', 'div'=> 'submitMultiple','class' => 'buttonBlu']);

echo $this->Form->end();
echo '</div>';

$links = [];
$links[] = $this->Html->link('<span class="desc animate"> '.__('List Articles').' </span><span class="fa fa-reply"></span>', ['controller' => 'Articles', 'action' => 'context_articles_index'], ['class' => 'animate', 'escape' => false]);
echo $this->Menu->draw($links);
?>

<script type="text/javascript">
function setPrezzoSenzaIva() {
	var prezzo_senza_iva = numberToJs($('#prezzo_senza_iva').val());
	var iva = $('#iva').val();
	console.log("prezzo_senza_iva "+prezzo_senza_iva);
	console.log("iva "+iva);
	
	var delta_iva = (prezzo_senza_iva/100)*iva;
	console.log("delta_iva "+delta_iva);
	//delta_iva = Math.round(delta_iva);
	//console.log("Math.round(delta_iva) "+delta_iva);

	var prezzo = (parseFloat(prezzo_senza_iva) + parseFloat(delta_iva));
	prezzo = number_format(prezzo,2,',','.');
	console.log("prezzo "+prezzo);
	
	$('#prezzo').val(prezzo);
}
$(document).ready(function() {
	$('#qta').focusout(function() {validateNumberField(this,'quantita\'');});
	$('.double').focusout(function() {validateNumberField(this,'prezzo');});

	$('#qta').focusout(function() {setArticlePrezzoUmRiferimento();});
	$('#um').change(function() {setArticlePrezzoUmRiferimento();});
	$('#prezzo_con_iva').focusout(function() {
		$('#prezzo').val($('#prezzo_con_iva').val());
		setArticlePrezzoUmRiferimento();
	});
	$('#prezzo_senza_iva').focusout(function() {
		setPrezzoSenzaIva();
		setArticlePrezzoUmRiferimento();
	});
	$('#iva').change(function() {
		setPrezzoSenzaIva();
		setArticlePrezzoUmRiferimento();
	});
	$('#prezzo').focusout(function() {setArticlePrezzoUmRiferimento();});

	$('#action_post_index').click(function() {	
		$('#action_post').val('context_articles_index');
	});
	$('#action_post_add').click(function() {	
		$('#action_post').val('context_articles_add');
	});
	
	$('#formGas').submit(function() {
		if(!moduleCtrlArticle()) 
			return false;
		else
			return true;
	});	
});
</script>