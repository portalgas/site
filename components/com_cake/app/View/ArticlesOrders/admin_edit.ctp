<?php
$disabledOpts = [];
if(!$canEdit) $disabledOpts = ['disabled' => 'disabled'];

if(empty($des_order_id)) {
	if($order['Order']['state_code'] == 'OPEN-NEXT') {
		$label = __('Edit ArticlesOrder OPEN-NEXT');
		$labelSingle = __('Edit Single ArticlesOrder OPEN-NEXT');
	}	
	else {
		$label = __('Edit ArticlesOrder');
		$labelSingle = __('Edit Single ArticlesOrder');
	}
}
else {
	if($order['Order']['state_code'] == 'OPEN-NEXT') {
		$label = __('Edit ArticlesOrder DES OPEN-NEXT');
		$labelSingle = __('Edit Single ArticlesOrder DES OPEN-NEXT');
	}	
	else {
		$label = __('Edit ArticlesOrder DES');
		$labelSingle = __('Edit Single ArticlesOrder DES');
	}
}

$this->Html->addCrumb(__('Home'), ['controller' => 'Pages', 'action' => 'home']);
$this->Html->addCrumb(__('List Orders'),array('controller' => 'Orders', 'action' => 'index'));
if(isset($order['Order']['id']) && !empty($order['Order']['id'])) {
	$this->Html->addCrumb(__('Order home'),array('controller'=>'Orders','action'=>'home', null, 'order_id='.$order['Order']['id']));
	$this->Html->addCrumb($label, array('controller' => 'ArticlesOrders', 'action' => 'index',$order['Order']['id']));
}
$this->Html->addCrumb($labelSingle);
echo $this->Html->getCrumbList(array('class'=>'crumbs'));

echo '<div class="contentMenuLaterale">';

if(!empty($des_order_id))
	echo $this->element('boxDesOrder', ['results' => $desOrdersResults]);

echo $this->Form->create('ArticlesOrder', ['id' => 'formGas']);

	echo '<fieldset>';
	echo '<legend>'.$labelSingle.'</legend>';
		
	echo $this->element('boxOrder', ['results' => $order]);
	
	include('box_article_detail.ctp');

	/*
	 * campi bloccati se non si e' proprietari dell'articolo
	 */		
	$i=0;
	echo $this->Form->label(__('Prezzo'));
	echo '<div style="white-space: nowrap;">';
	echo $this->Form->input('prezzo', ['label' => false, 'value'=> $this->request->data['ArticlesOrder']['prezzo_'], 'type' => 'text', 'after' => '&nbsp;&euro;','tabindex'=>($i+1), 'class'=>'double', 'style' => 'display:inline', 'required'=>'required', $disabledOpts]);
	echo '</div>';
	echo $this->Form->input('pezzi_confezione', ['label' => __('pezzi_confezione'),'type' => 'text', 'tabindex'=>($i+1), 'after' => $this->App->drawTooltip(__('pezzi_confezione'),__('toolTipPezziConfezione'),$type='INFO'), $disabledOpts]);
	
	/*
	 * campi gestiti anche da chi non e' proprietario dell'articolo
	 */		
	echo $this->Form->input('qta_minima', ['label' => __('qta_minima'), 'type' => 'text', 'tabindex'=>($i+1)]);
	echo $this->Form->input('qta_massima', ['label' => __('qta_massima'), 'type' => 'text', 'tabindex'=>($i+1)]);
	echo $this->Form->input('qta_multipli', ['label' => __('qta_multipli'), 'type' => 'text', 'tabindex'=>($i+1),'after'=>$this->App->drawTooltip(__('qta_multipli'),__('toolTipQtaMultipli'),$type='INFO')]);
	
	if($user->organization['Organization']['hasFieldArticleAlertToQta']=='Y')
		echo $this->Form->input('alert_to_qta', ['label' => __('alert_to_qta'),'type' => 'text','tabindex'=>($i+1),'after'=>$this->App->drawTooltip(__('alert_to_qta'),__('toolTipAlertToQta'),$type='INFO')]);

	echo $this->Form->input('qta_minima_order', ['label' => __('qta_minima_order'),'type' => 'text','tabindex'=>($i+1),'required'=>'required','after'=>$this->App->drawTooltip(__('qta_minima_order'),__('toolTipQtaMinOrder'),$type='INFO')]);
	
	if(!empty($des_order_id))
		$toolTip = __('toolTipQtaMaxOrderDes');
	else    
		$toolTip = __('toolTipQtaMaxOrder');
	
	echo $this->Form->input('qta_massima_order', ['label' => __('qta_massima_order'),'type' => 'text', 'tabindex' => ($i+1), 'required' => 'required', 'after' => $this->App->drawTooltip(__('qta_massima_order'),$toolTip,$type='INFO')]);

	if($order['Order']['state_code'] != 'CREATE-INCOMPLETE' && $order['Order']['state_code'] != 'OPEN-NEXT' && $order['Order']['state_code'] != 'OPEN') {
		echo '<div class="clearfix"></div>';

		if(empty($des_order_id))
			$desOrdersResults = [];
		
		echo $this->element('legendaArticlesOrderQtaMassima',  ['results' => $this->request->data, 'desOrdersResults' => $desOrdersResults]);
	}
	
	if($this->request->data['ArticlesOrder']['stato']=='QTAMAXORDER') {
		echo '<div class="clearfix"></div>';
		echo '<div class="legenda legenda-ico-alert" style="float:none;">';
		echo "L'articolo ha raggiunto la quantità massima che è stata impostata";
		echo '</div>';
	}
	else {
		echo '<div class="clearfix"></div>';
		echo $this->App->drawFormRadio('ArticlesOrder','stato', ['options' => $stato, 'value'=>$this->Form->value('ArticlesOrder.stato'), 'label'=>'Stato','tabindex'=>($i+1), 'required'=>'required',
																 'after'=>$this->App->drawTooltip(null,__('toolTipStatoArticlesOrder'),$type='HELP')]);
	}
	
	echo '</fieldset>';
	 
	echo $this->Form->hidden('order_id', ['name' => 'data[ArticlesOrder][order_id]', 'id' =>'order_id', 'value'=> $this->request->data['ArticlesOrder']['order_id']]);
	echo $this->Form->hidden('article_organization_id', ['id' =>'article_organization_id', 'value'=> $this->request->data['ArticlesOrder']['article_organization_id']]);
	echo $this->Form->hidden('article_id', ['id' =>'article_id', 'value'=> $this->request->data['ArticlesOrder']['article_id']]);
	echo $this->Form->hidden('qta_cart', ['id' =>'qta_cart', 'value'=> $this->request->data['ArticlesOrder']['qta_cart']]);
	echo $this->Form->end(__('Submit')); 

	echo '</div>';

$links = [];
$links[] = $this->Html->link('<span class="desc animate"> '.__('List Articles Orders').' </span><span class="fa fa-reply"></span>', ['controller' => 'ArticlesOrders', 'action' => 'index', $order['Order']['id']], ['class' => 'animate', 'escape' => false]);
echo $this->Menu->draw($links);
?>
<script type="text/javascript">
$(document).ready(function() {

	$('#formGas').submit(function() {

		prezzo = $('#ArticlesOrderPrezzo').val(); 
		if(prezzo=='' || prezzo==null || prezzo=='0,00' || prezzo=='0.00' || prezzo=='0') {
			alert("Devi indicare l'importo per gli articoli che desideri associare all'ordine");
			$('#ArticlesOrderPrezzo').focus();
			return false;			
		}
			
		pezzi_confezione = $('#ArticlesOrderPezziConfezione').val(); 
		if(pezzi_confezione=='' || pezzi_confezione==null || !isFinite(pezzi_confezione)) {
			alert("Devi indicare il numero di pezzi per confezione per gli articoli che desideri associare all'ordine");
			$('#ArticlesOrderPezziConfezione').focus();
			return false;			
		}
		if(pezzi_confezione <= 0) {
			alert("Il numero di pezzi per confezione per gli articoli che desideri associare all'ordine deve essere > di zero");
			$('#ArticlesOrderPezziConfezione').focus();
			return false;			
		}
					
		qta_minima = $('#ArticlesOrderQtaMinima').val(); 
		if(qta_minima=='' || qta_minima==null || !isFinite(qta_minima)) {
			alert("Devi indicare la quantità minima che un gasista può acquistare");
			$('#ArticlesOrderQtaMinima').focus();
			return false;			
		}
		qta_minima = parseInt(qta_minima);
		if(qta_minima <= 0) {
			alert("La quantità minima che un gasista può acquistare deve essere > di zero");
			$('ArticlesOrderQtaMinima').focus();
			return false;			
		}

		qta_massima = $('#ArticlesOrderQtaMassima').val(); 
		if(qta_massima=='' || qta_massima==null || !isFinite(qta_massima)) {
			alert("Devi indicare la quantità massima che un gasista può acquistare: di default 0");
			$('#ArticlesOrderQtaMassima').focus();
			return false;			
		}
		
		qta_minima_order = $("#ArticlesOrderQtaMinimaOrder").val(); 
		if(qta_minima_order=='' || qta_minima_order==null || !isFinite(qta_minima_order)) {
			alert("Devi indicare la quantità minima rispetto a tutti gli acquisti dell'ordine");
			$("#ArticlesOrderQtaMinimaOrder").focus();
			return false;			
		}
		
		qta_massima_order = $('#ArticlesOrderQtaMassimaOrder').val(); 
		if(qta_massima_order=='' || qta_massima_order==null || !isFinite(qta_massima_order)) {
			alert("Devi indicare la quantità massima rispetto a tutti gli acquisti dell'ordine");
			$('#ArticlesOrderQtaMassimaOrder').focus();
			return false;			
		}
		qta_massima_order = parseInt(qta_massima_order);
		if(qta_massima_order > 0 && qta_massima_order < pezzi_confezione) {
			alert("La quantità massima rispetto a tutti gli acquisti dell'ordine è inferiore al numero di pezzi in una confezione");
			$('#ArticlesOrderQtaMassimaOrder').focus();
			return false;			
		}
		qta_cart = $('#qta_cart').val(); 
		if(qta_massima_order > 0 && qta_cart > qta_massima_order) {
			if(!confirm("La quantità massima è inferiore alla quantità finora acquistata, sei sicuro di voler procedere?"))
				return false;
		}
		
		qta_multipli = $('#ArticlesOrderQtaMultipli').val(); 
		if(qta_multipli=='' || qta_multipli==null || !isFinite(qta_multipli)) {
			alert("Devi indicare di che multiplo dev'essere la quantità per gli articoli associati all'ordine");
			$('#ArticlesOrderQtaMultipli').focus();
			return false;			
		}
		qta_multipli = parseInt(qta_multipli);
		if(qta_multipli <= 0) {
			alert("Il multiplo per gli articoli che desideri associare all'ordine deve essere > di zero");
			$('#ArticlesOrderQtaMultipli').focus();
			return false;			
		}
	
		if((qta_massima) > 0 && (qta_massima < qta_multipli)) {
			alert("La quantità massima che un gasista può acquistare non può essere inferiore della quantità multipla");
			$("input[name='data[Article]["+article_id+"][ArticlesOrderQtaMassima]']").focus();
			return false;
		}
				
		<?php
		if($user->organization['Organization']['hasFieldArticleAlertToQta']=='Y') {
		?>
			alert_to_qta = $('#ArticlesOrderQtaAlertToQta').val(); 
			if(alert_to_qta=='' || alert_to_qta==null || !isFinite(alert_to_qta)) {
				alert("Devi indicare quando avvisare raggiunta una certa quantità per gli articoli associati all'ordine");
				$('#ArticlesOrderQtaAlertToQta').focus();
				return false;			
			}
			if(alert_to_qta <= 0) {
				alert("La quantità che indica quando avvisare per gli articoli che desideri associare all'ordine deve essere > di zero");
				$('#ArticlesOrderQtaAlertToQta').focus();
				return false;			
			}
		<?php
		}
		?>			

		return true;
	});
});
</script>