<?phpclass OrderHomeHelper extends AppHelper {	var $helpers = array('Html','Time');	private function getTextToolTip($state_code, $text_num) {		/* 		 * testo trasversale		 */		$arrayText[0][0] = "Crea un ordine associandolo ad:<br />- una consegna<br />- un produttore<br />e scegli un periodo d'ordine durante il quale i soci del GAS potranno effettuare gli ordini";				$arrayText['CREATE-INCOMPLETE'][0] = "Non essendoci articoli associati, l'ordine non si potr&agrave; aprire. ";				$arrayText['OPEN-NEXT'][0] = "All'apertura dell'ordine viene inviata una mail a tutti gli utenti per avvisarli dell'apertura.<br />Dal periodo di apertura dell'ordine fino alla sua chiusura i soci del GAS potranno effettuare acquisti.";				$arrayText['OPEN'][0] = "All'apertura dell'ordine e&grave; stata inviata una mail a tutti gli utenti per avvisarli dell'apertura.<br />Dal periodo di apertura dell'ordine fino alla sua chiusura i soci del GAS potranno effettuare acquisti.";		$arrayText['OPEN'][1] = "Una volta che l'ordine e&grave; chiuso potrai<br />- verificarlo prima della Stampa ed Invio al produttore, modificando ogni acquisto effettuato dai soci del GAS<br />- dopo la consegna potrai verificare la corrispondenza tra ordinato e consegnato.";				$arrayText['RI-OPEN-VALIDATE'][0] = "All'apertura dell'ordine e&grave; stata inviata una mail a tutti gli utenti per avvisarli dell'apertura.<br />Dal periodo di apertura dell'ordine fino alla sua chiusura i soci del GAS potranno effettuare acquisti.";
		$arrayText['RI-OPEN-VALIDATE'][1] = "Una volta che l'ordine e&grave; chiuso potrai<br />- verificarlo prima della Stampa ed Invio al produttore, modificando ogni acquisto effettuato dai soci del GAS<br />- dopo la consegna potrai verificare la corrispondenza tra ordinato e consegnato.";
				$arrayText['PROCESSED-BEFORE-DELIVERY'][0] = "Una volta che l'ordine e&grave; chiuso potrai<br />- verificarlo prima della Stampa ed Invio al produttore, modificando ogni acquisto effettuato dai soci del GAS<br />- dopo la consegna potrai verificare la corrispondenza tra ordinato e consegnato.";		$arrayText['PROCESSED-BEFORE-DELIVERY'][1] = "Modifica acquisti effettuati di un singoli utente";		$arrayText['PROCESSED-BEFORE-DELIVERY'][2] = "Modifica gli acquisti effettuati aggregati per utenti";		$arrayText['PROCESSED-BEFORE-DELIVERY'][3] = "Verifica l'ordine e controllalo prima della stampa e l'invio al produttore";				$arrayText['PROCESSED-POST-DELIVERY'][0] = "Una volta che l'ordine e&grave; chiuso potrai<br />- verificarlo prima della Stampa ed Invio al produttore, modificando ogni acquisto effettuato dai soci del GAS<br />- dopo la consegna potrai verificare la corrispondenza tra ordinato e consegnato.";		$arrayText['PROCESSED-POST-DELIVERY'][1] = "Gestisci gli acquisti effettuati dagli utenti";		$arrayText['PROCESSED-POST-DELIVERY'][2] = "Modifica gli acquisti effettuati aggregati per utenti";		$arrayText['PROCESSED-POST-DELIVERY'][3] = "Gestisci i colli per arrivarare al numero corretto";		$arrayText['PROCESSED-POST-DELIVERY'][4] = "Gestisci l'importo del trasporto suddividendolo tra i gasisti";		$arrayText['PROCESSED-POST-DELIVERY'][5] = "Verifica l'ordine e controllalo prima della stampa e l'invio al produttore";				$arrayText['PROCESSED-ON-DELIVERY'][0] = "Una volta che l'ordine e&grave; chiuso potrai<br />- verificarlo prima della Stampa ed Invio al produttore, modificando ogni acquisto effettuato dai soci del GAS<br />- dopo la consegna potrai verificare la corrispondenza tra ordinato e consegnato.";
		$arrayText['PROCESSED-ON-DELIVERY'][1] = "Verifica l'ordine e controllalo prima della stampa e l'invio al produttore";		$arrayText['PROCESSED-ON-DELIVERY'][2] = "In carico al tesoriere dopo la consegna";				$arrayText['WAIT-PROCESSED-TESORIERE'][0] = "All'apertura dell'ordine e&grave; stata inviata una mail a tutti gli utenti per avvisarli dell'apertura.<br />Dal periodo di apertura dell'ordine fino alla sua chiusura i soci del GAS potranno effettuare acquisti.";		$arrayText['WAIT-PROCESSED-TESORIERE'][1] = "Una volta che l'ordine e&grave; chiuso potrai<br />- verificarlo prima della Stampa ed Invio al produttore, modificando ogni acquisto effettuato dai soci del GAS<br />- dopo la consegna potrai verificare la corrispondenza tra ordinato e consegnato.";				$arrayText['PROCESSED-TESORIERE'][0] = "All'apertura dell'ordine e&grave; stata inviata una mail a tutti gli utenti per avvisarli dell'apertura.<br />Dal periodo di apertura dell'ordine fino alla sua chiusura i soci del GAS potranno effettuare acquisti.";		$arrayText['PROCESSED-TESORIERE'][1] = "Una volta che l'ordine e&grave; chiuso potrai<br />- verificarlo prima della Stampa ed Invio al produttore, modificando ogni acquisto effettuato dai soci del GAS<br />- dopo la consegna potrai verificare la corrispondenza tra ordinato e consegnato.";		$arrayText['PROCESSED-TESORIERE'][2] = "L'ordine ae&grave; in carico al tesoriere per essere elaborato e per effettuare i pagamenti da parte<br />- dei soci del gas per i loro acquisti<br />- della fattura dei produttori";				$arrayText['TO-PAYMENT'][0] = "All'apertura dell'ordine e&grave; stata inviata una mail a tutti gli utenti per avvisarli dell'apertura.<br />Dal periodo di apertura dell'ordine fino alla sua chiusura i soci del GAS potranno effettuare acquisti.";		$arrayText['TO-PAYMENT'][1] = "Una volta che l'ordine e&grave; chiuso potrai<br />- verificarlo prima della Stampa ed Invio al produttore, modificando ogni acquisto effettuato dai soci del GAS<br />- dopo la consegna potrai verificare la corrispondenza tra ordinato e consegnato.";				$arrayText['CLOSE'][0] = "All'apertura dell'ordine e&grave; stata inviata una mail a tutti gli utenti per avvisarli dell'apertura.<br />Dal periodo di apertura dell'ordine fino alla sua chiusura i soci del GAS potranno effettuare acquisti.";		$arrayText['CLOSE'][1] = "Una volta che l'ordine e&grave; chiuso potrai<br />- verificarlo prima della Stampa ed Invio al produttore, modificando ogni acquisto effettuato dai soci del GAS<br />- dopo la consegna potrai verificare la corrispondenza tra ordinato e consegnato.";				return $arrayText[$state_code][$text_num];	} 		/*	 * ULclass: workflow, workflowIILiv	 * class: alert, 	 * span: blu	 */	public function drawAction($action_type, $results, $tagUL=array(), $class=array()) {		$tmp = '';		$urlBase = Configure::read('App.server').'/administrator/index.php?option=com_cake&';			if(isset($tagUL['ULopen']) && $tagUL['ULopen']==true) {			$tmp .= '<ul class="';			if(isset($tagUL['ULclass']))				$tmp .= $tagUL['ULclass'];			$tmp .= '">';		}						$tmp .= '<li><span class="';		if(isset($class['span'])) $tmp .= $class['span'];		$tmp .= '">';		switch ($action_type) {			case 'AddArticlesOrderError':				$tmp .= $this->Html->link(__('Add ArticlesOrder Error'), $urlBase.'controller=ArticlesOrders&action=add&delivery_id='.$results['Order']['delivery_id'].'&order_id='.$results['Order']['id'],array('class' => 'actionEditCartWF'));				break;			case 'EditOrder':				$tmp .= $this->Html->link(__('Edit Order'), $urlBase.'controller=Orders&action=edit&delivery_id='.$results['Order']['delivery_id'].'&order_id='.$results['Order']['id'],array('class' => 'actionEditWF'));				break;			case 'EditArticlesOrderShort':				$tmp .= $this->Html->link(__('Edit ArticlesOrder Short'), $urlBase.'controller=ArticlesOrders&action=index&delivery_id='.$results['Order']['delivery_id'].'&order_id='.$results['Order']['id'],array('class' => 'actionEditCartWF'));				break;			case 'ListArticles';			$tmp .= $this->Html->link(__('List Articles'), $urlBase.'controller=Articles&action=context_order_index&FilterArticleSupplierId='.$results['Order']['supplier_organization_id'].'&delivery_id='.$results['Order']['delivery_id'].'&order_id='.$results['Order']['id'],array('class' => 'actionListWF'));			break;			case 'DeleteOrder':				$tmp .= $this->Html->link(__('Delete'), $urlBase.'controller=Orders&action=delete&delivery_id='.$results['Order']['delivery_id'].'&order_id='.$results['Order']['id'],array('class' => 'actionDeleteWF'));				break;			case 'ManagementCartsOne':				if($results['Order']['state_code']=='OPEN')					$label = "Controlla gli acquisti parziali effettuati dagli utenti";				else					$label = __('Management Carts One Short');									$tmp .= $this->Html->link($label, $urlBase.'controller=Carts&action=managementCartsOne&delivery_id='.$results['Delivery']['id'].'&order_id='.$results['Order']['id'], array('class' => 'actionEditDbOneWF'));			break;			case 'ManagementCartsGroupByUsers':				$tmp .= $this->Html->link(__('Management Carts Group By Users Short'), $urlBase.'controller=Carts&action=managementCartsGroupByUsers&delivery_id='.$results['Delivery']['id'].'&order_id='.$results['Order']['id'], array('class' => 'actionEditDbGroupByUsersWF'));				break;			case 'ManagementCartsSplit':				$tmp .= $this->Html->link(__('Management Carts Split Short'), $urlBase.'controller=Carts&action=managementCartsSplit&delivery_id='.$results['Delivery']['id'].'&order_id='.$results['Order']['id'], array('class' => 'actionEditDbSplitWF'));				break;			case 'ExportDoc':				if($results['Order']['state_code']=='OPEN')					$label = __('Print Order Partial');				else					$label = __('Print Order');									$tmp .= $this->Html->link($label, $urlBase.'controller=Docs&action=referentDocsExport&delivery_id='.$results['Delivery']['id'].'&order_id='.$results['Order']['id'], array('class' => 'actionPrinterWF'));				break;			case 'ExportDocToUsers':				$tmp .= $this->Html->link(__('to_users'), $urlBase.'controller=Docs&action=referentDocsExport&delivery_id='.$results['Delivery']['id'].'&order_id='.$results['Order']['id'], array('class' => 'actionPrinterWF'));				break;			case 'ExportDocToArticle':				$tmp .= $this->Html->link(__('to_articles'), $urlBase.'controller=Docs&action=referentDocsExport&delivery_id='.$results['Delivery']['id'].'&order_id='.$results['Order']['id'], array('class' => 'actionPrinterWF'));				break;			case 'ExportDocToArticlesDetails':				$tmp .= $this->Html->link(__('to_articles_details'), $urlBase.'controller=Docs&action=referentDocsExport&delivery_id='.$results['Delivery']['id'].'&order_id='.$results['Order']['id'], array('class' => 'actionPrinterWF'));				break;			case 'ValidateOrder':				$tmp .= $this->Html->link(__('Validation Carts'), $urlBase.'controller=Carts&action=validationCarts&delivery_id='.$results['Delivery']['id'].'&order_id='.$results['Order']['id'], array('class' => 'actionValidateWF'));				break;			case 'CartsToStoreroom':				$tmp .= $this->Html->link(__('CartsToStoreroom'), $urlBase.'controller=Storerooms&action=carts_to_storeroom&delivery_id='.$results['Delivery']['id'].'&order_id='.$results['Order']['id'], array('class' => 'actionToStoreroomWF'));				break;			case 'ManagementTrasport':				$tmp .= $this->Html->link(__('Management trasport'), $urlBase.'controller=Carts&action=trasport&delivery_id='.$results['Delivery']['id'].'&order_id='.$results['Order']['id'], array('class' => 'actionTrasportWF'));				break;			case 'order_state_in_TO_PAYMENT':				$tmp .= $this->Html->link("Gestisci il pagamento dell'ordine", $urlBase.'controller=Referente&action=order_state_in_TO_PAYMENT&delivery_id='.$results['Delivery']['id'].'&order_id='.$results['Order']['id'], array('class' => 'actionPayWF'));				break;			case 'order_state_in_WAIT_PROCESSED_TESORIERE':				$tmp .= $this->Html->link("Passa l'ordine al tesoriere affinche' possa effettuare i pagamenti", $urlBase.'controller=Referente&action=order_state_in_WAIT_PROCESSED_TESORIERE&delivery_id='.$results['Delivery']['id'].'&order_id='.$results['Order']['id'], array('class' => 'actionShareWF'));				break;			case 'order_state_in_PROCESSED_POST_DELIVERY':				$tmp .= $this->Html->link("Riportalo l'ordine allo stato 'in carico al referente' per effettuare le tue modifiche", $urlBase.'controller=Referente&action=order_state_in_PROCESSED_POST_DELIVERY&delivery_id='.$results['Delivery']['id'].'&order_id='.$results['Order']['id'], array('class' => 'actionShareWF'));				break;			case 'EditRequestPaymentsOrder':				$tmp .= $this->Html->link(__('Edit Request Payments Order'), $urlBase.'controller=RequestPayments&action=index&delivery_id='.$results['Delivery']['id'].'&order_id='.$results['Order']['id'], array('class' => 'actionShareWF'));				break;			case 'RequestPayment':				$tmp .= $this->Html->link("Pagamento dell'ordine", $urlBase.'controller=RequestPayments&action=view&delivery_id='.$results['Delivery']['id'].'&order_id='.$results['Order']['id'], array('class' => 'actionPayWF'));				break;		}			$tmp.= '</span></li>';			if(isset($tagUL['ULclose']) && $tagUL['ULclose']==true)			$tmp .= '</ul>';		return $tmp;	}	/*	 * ULclass: workflow,	* class: 'span' => 'statoCurrent', 'href'=>'actionOpenWF'	*/	public function drawBoxTitle($state_code, $title, $text_num=0, $tagUL=array(), $class=array()) {		$tmp = "";				if(isset($tagUL['ULopen']) && $tagUL['ULopen']==true) {			$tmp .= '<ul class="';			if(isset($tagUL['ULclass']))				$tmp .= $tagUL['ULclass'];			$tmp .= '"><li>';				}			$tmp .= '<span class="helpWF ';		if(isset($class['span'])) $tmp .= $class['span'];		$tmp .= '">';				$tmp .= '<a class="';		if(isset($class['href'])) $tmp .= $class['href'];		$tmp .= '">';					$tmp .= $title;		$tmp .= '</a>';		$tmp .= '<div class="helpTextWF">';		$tmp .= '<img width="24" height="24" alt="Informazione per aiutarti" src="'.Configure::read('App.img.cake').'/tooltips/24x24/help.png" />';		$tmp .= $this->getTextToolTip($state_code, $text_num);		$tmp .= '</div>';		$tmp .= '</span>';			if(isset($tagUL['ULclose']) && $tagUL['ULclose']==true)			$tmp .= '</li></ul>';				return $tmp;	}		public function drawBoxTitleUlClose() {		$tmp = '</li></ul>';		return $tmp;			}}