<?php
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
	if($order['Order']['state_code'] == 'OPEN-NEXT') {		$label = __('Edit ArticlesOrder DES OPEN-NEXT');
		$labelSingle = __('Edit Single ArticlesOrder DES OPEN-NEXT');
	}		else {		$label = __('Edit ArticlesOrder DES');
		$labelSingle = __('Edit Single ArticlesOrder DES');
	}
}

$this->Html->addCrumb(__('Home'),array('controller' => 'Pages', 'action' => 'home'));
$this->Html->addCrumb(__('List Orders'),array('controller' => 'Orders', 'action' => 'index'));
if(isset($order['Order']['id']) && !empty($order['Order']['id'])) {
	$this->Html->addCrumb(__('Order home'),array('controller'=>'Orders','action'=>'home', null, 'order_id='.$order['Order']['id']));
	$this->Html->addCrumb($label, array('controller' => 'ArticlesOrders', 'action' => 'index',$order['Order']['id']));
}
$this->Html->addCrumb($labelSingle);
echo $this->Html->getCrumbList(array('class'=>'crumbs'));

echo '<div class="articlesOrders form">';

if(!empty($des_order_id))
	echo $this->element('boxDesOrder', array('results' => $desOrdersResults));

echo $this->Form->create('ArticlesOrder',array('id' => 'formGas'));

	echo '<fieldset>';
	echo '<legend>'.$labelSingle.'</legend>';
		
	include('box_order_detail.ctp');
	
	include('box_article_detail.ctp');
	
		$i=0;
		echo $this->Form->input('prezzo',array('value'=> $this->request->data['ArticlesOrder']['prezzo_'], 'size'=>10,'class' => 'noWidth', 'type' => 'text', 'after' => '&euro;','tabindex'=>($i+1),'class'=>'noWidth double', 'required'=>'required'));
		
		echo $this->Form->input('qta_minima',array('label' => __('qta_minima'),'type' => 'text','size' => 2,'class' => 'noWidth','tabindex'=>($i+1)));
		echo $this->Form->input('qta_massima',array('label' => __('qta_massima'),'type' => 'text','size' => 2,'class' => 'noWidth','tabindex'=>($i+1)));
		
		echo $this->Form->input('qta_multipli',array('label' => __('qta_multipli'),'type' => 'text','size' => '2','class' => 'noWidth','tabindex'=>($i+1),'after'=>$this->App->drawTooltip(__('qta_multipli'),__('toolTipQtaMultipli'),$type='INFO')));
		
		echo $this->Form->input('pezzi_confezione',array('label' => __('pezzi_confezione'),'type' => 'text','size' => '2','class' => 'noWidth','tabindex'=>($i+1),'after'=>$this->App->drawTooltip(__('pezzi_confezione'),__('toolTipPezziConfezione'),$type='INFO')));
		if($user->organization['Organization']['hasFieldArticleAlertToQta']=='Y')
			echo $this->Form->input('alert_to_qta',array('label' => __('alert_to_qta'),'type' => 'text','size' => '2','class' => 'noWidth','tabindex'=>($i+1),'after'=>$this->App->drawTooltip(__('alert_to_qta'),__('toolTipAlertToQta'),$type='INFO')));

		echo $this->Form->input('qta_minima_order',array('label' => __('qta_minima_order'),'type' => 'text','size' => 5,'class' => 'noWidth','tabindex'=>($i+1),'required'=>'required','after'=>$this->App->drawTooltip(__('qta_minima_order'),__('toolTipQtaMinOrder'),$type='INFO')));
		echo $this->Form->input('qta_massima_order',array('label' => __('qta_massima_order'),'type' => 'text','size' => 5,'class' => 'noWidth','tabindex'=>($i+1),'required'=>'required','after'=>$this->App->drawTooltip(__('qta_massima_order'),__('toolTipQtaMaxOrder'),$type='INFO')));
		
		if($order['Order']['state_code'] != 'CREATE-INCOMPLETE' && $order['Order']['state_code'] != 'OPEN-NEXT' && $order['Order']['state_code'] != 'OPEN')
			echo $this->element('legendaArticlesOrderQtaMassima', array('results' => $this->request->data));
		
		
		if($this->request->data['ArticlesOrder']['stato']=='QTAMAXORDER') {
			echo '<div class="legenda legenda-ico-alert" style="float:none;">';
			echo "L'articolo ha raggiunto la quantità massima che è stata impostata";
			echo '</div>';
		}
		else {
			echo $this->App->drawFormRadio('ArticlesOrder','stato',array('options' => $stato, 'value'=>$this->Form->value('ArticlesOrder.stato'), 'label'=>'Stato','tabindex'=>($i+1), 'required'=>'required',
																						  'after'=>$this->App->drawTooltip(null,__('toolTipStatoArticlesOrder'),$type='HELP')));
		}
	
	echo '</fieldset>';
	 
	echo $this->Form->hidden('order_id',array('id' =>'order_id', 'value'=> $this->request->data['ArticlesOrder']['order_id']));
	echo $this->Form->hidden('article_organization_id',array('id' =>'article_organization_id', 'value'=> $this->request->data['ArticlesOrder']['article_organization_id']));
	echo $this->Form->hidden('article_id',array('id' =>'article_id', 'value'=> $this->request->data['ArticlesOrder']['article_id']));
	echo $this->Form->hidden('qta_cart',array('id' =>'qta_cart', 'value'=> $this->request->data['ArticlesOrder']['qta_cart']));
	echo $this->Form->end(__('Submit')); 
	
	echo '</div>';
	?>

<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('List Articles Orders'), array('controller'=>'ArticlesOrders', 'action' => 'index', $order['Order']['id']),array('class'=>'action actionReload'));?></li>
	</ul>
</div>

<script type="text/javascript">
jQuery(document).ready(function() {

	jQuery('#formGas').submit(function() {

		prezzo = jQuery('#ArticlesOrderPrezzo').val(); 
		if(prezzo=='' || prezzo==null || prezzo=='0,00' || prezzo=='0.00' || prezzo=='0') {
			alert("Devi indicare l'importo per gli articoli che desideri associare all'ordine");
			jQuery('#ArticlesOrderPrezzo').focus();
			return false;			
		}
			
		pezzi_confezione = jQuery('#ArticlesOrderPezziConfezione').val(); 
		if(pezzi_confezione=='' || pezzi_confezione==null || !isFinite(pezzi_confezione)) {
			alert("Devi indicare il numero di pezzi per confezione per gli articoli che desideri associare all'ordine");
			jQuery('#ArticlesOrderPezziConfezione').focus();
			return false;			
		}
		if(pezzi_confezione <= 0) {
			alert("Il numero di pezzi per confezione per gli articoli che desideri associare all'ordine deve essere > di zero");
			jQuery('#ArticlesOrderPezziConfezione').focus();
			return false;			
		}
					
		qta_minima = jQuery('#ArticlesOrderQtaMinima').val(); 
		if(qta_minima=='' || qta_minima==null || !isFinite(qta_minima)) {
			alert("Devi indicare la quantità minima che un gasista può acquistare");
			jQuery('#ArticlesOrderQtaMinima').focus();
			return false;			
		}
		qta_minima = parseInt(qta_minima);
		if(qta_minima <= 0) {
			alert("La quantità minima che un gasista può acquistare deve essere > di zero");
			jQuery('ArticlesOrderQtaMinima').focus();
			return false;			
		}

		qta_massima = jQuery('#ArticlesOrderQtaMassima').val(); 
		if(qta_massima=='' || qta_massima==null || !isFinite(qta_massima)) {
			alert("Devi indicare la quantità massima che un gasista può acquistare: di default 0");
			jQuery('#ArticlesOrderQtaMassima').focus();
			return false;			
		}
		
		qta_minima_order = jQuery("#ArticlesOrderQtaMinimaOrder").val(); 
		if(qta_minima_order=='' || qta_minima_order==null || !isFinite(qta_minima_order)) {
			alert("Devi indicare la quantità minima rispetto a tutti gli acquisti dell'ordine");
			jQuery("#ArticlesOrderQtaMinimaOrder").focus();
			return false;			
		}
		
		qta_massima_order = jQuery('#ArticlesOrderQtaMassimaOrder').val(); 
		if(qta_massima_order=='' || qta_massima_order==null || !isFinite(qta_massima_order)) {
			alert("Devi indicare la quantità massima rispetto a tutti gli acquisti dell'ordine");
			jQuery('#ArticlesOrderQtaMassimaOrder').focus();
			return false;			
		}
		qta_massima_order = parseInt(qta_massima_order);
		if(qta_massima_order > 0 && qta_massima_order < pezzi_confezione) {
			alert("La quantità massima rispetto a tutti gli acquisti dell'ordine è inferiore al numero di pezzi in una confezione");
			jQuery('#ArticlesOrderQtaMassimaOrder').focus();
			return false;			
		}
		qta_cart = jQuery('#qta_cart').val(); 
		if(qta_massima_order > 0 && qta_cart > qta_massima_order) {
			if(!confirm("La quantità massima è inferiore alla quantità finora acquistata, sei sicuro di voler procedere?"))
				return false;
		}
		
		qta_multipli = jQuery('#ArticlesOrderQtaMultipli').val(); 
		if(qta_multipli=='' || qta_multipli==null || !isFinite(qta_multipli)) {
			alert("Devi indicare di che multiplo dev'essere la quantità per gli articoli associati all'ordine");
			jQuery('#ArticlesOrderQtaMultipli').focus();
			return false;			
		}
		qta_multipli = parseInt(qta_multipli);
		if(qta_multipli <= 0) {
			alert("Il multiplo per gli articoli che desideri associare all'ordine deve essere > di zero");
			jQuery('#ArticlesOrderQtaMultipli').focus();
			return false;			
		}
	
		if((qta_massima) > 0 && (qta_massima < qta_multipli)) {
			alert("La quantità massima che un gasista può acquistare non può essere inferiore della quantità multipla");
			jQuery("input[name='data[Article]["+article_id+"][ArticlesOrderQtaMassima]']").focus();
			return false;
		}
				
		<?php
		if($user->organization['Organization']['hasFieldArticleAlertToQta']=='Y') {
		?>
			alert_to_qta = jQuery('#ArticlesOrderQtaAlertToQta').val(); 
			if(alert_to_qta=='' || alert_to_qta==null || !isFinite(alert_to_qta)) {
				alert("Devi indicare quando avvisare raggiunta una certa quantità per gli articoli associati all'ordine");
				jQuery('#ArticlesOrderQtaAlertToQta').focus();
				return false;			
			}
			if(alert_to_qta <= 0) {
				alert("La quantità che indica quando avvisare per gli articoli che desideri associare all'ordine deve essere > di zero");
				jQuery('#ArticlesOrderQtaAlertToQta').focus();
				return false;			
			}
		<?php
		}
		?>			

		return true;
	});
});
</script>