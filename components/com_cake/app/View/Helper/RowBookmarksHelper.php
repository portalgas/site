<?php 
App::uses('UtilsCommons', 'Lib');

class RowBookmarksHelper extends AppHelper {
		
	private $debug =  false;
	private $tabindex = 1;

	public function drawBackOfficeSimple($numResult, $result, $options=array()) {
	
		$tmp = '';
		$tmp .= $this->__drawSimpleFieldsHidden($numResult, $result, $options);
		
		$tmp  .= '<td>'.($numResult +1).'</td>';
		$tmp  .= '<td>'.$result['User']['name'].'</td>';
		$tmp  .= '<td>'.$result['SuppliersOrganization']['name'].'</td>';
		
		$tmp .= $this->__drawSimple($numResult, $result, $options);
		
		if(isset($options['tr.no_display']))
			$tmp  .= $this->__draw_js_backoffice($rowId);
		else {
			$tmp .= '</tr>';
		
			$tmp  .= '<tr class="trView" id="trViewId-'.$rowId.'">';
			$tmp  .= '<td colspan="2"></td>';
			$tmp  .= '<td colspan="10" id="tdViewId-'.$rowId.'"></td>';
		}
		
		return $tmp;
	}
	
	public function drawFrontEndSimple($numResult, $result, $options=array()) {
	
		$tmp = '';	
		$tmp .= $this->__drawSimpleFieldsHidden($numResult, $result, $options);
		
		$tmp  .= '<td>'.($numResult +1).'</td>';
		
		$tmp .= $this->__drawSimple($numResult, $result, $options);
		
		if(isset($options['tr.no_display']))
			$tmp  .= $this->__draw_js_frontend($rowId);
		else {
			$tmp .= '</tr>';
		
			$tmp  .= '<tr class="trView" id="trViewId-'.$rowId.'">';
			$tmp  .= '<td colspan="2"></td>';
			$tmp  .= '<td colspan="8" id="tdViewId-'.$rowId.'"></td>';
		}
		
		return $tmp;
		
	}

	private function __drawSimpleFieldsHidden($numResult, $result, $options) {
		$tmp = "";
		$type_input = 'hidden';
		
		$rowId = $this->__getRowId($numResult, $result);
				
		if(!isset($options['tr.no_display'])) {
			$tmp .= "\n";
			$tmp .= '<tr class="row'.$numResult.' rowEcomm" id="row-'.$rowId.'" style="display:table-row; font-size: 14px; height: 60px;">';
		}
		
		
		$tmp  .= '<td>';
		
		$tmp  .= '<input class="debug" type="'.$type_input.'" value="'.$result['BookmarksArticle']['user_id'].'" id="user_id-'.$rowId.'" />';
		$tmp  .= '<input class="debug" type="'.$type_input.'" value="'.$result['Article']['id'].'" id="article_id-'.$rowId.'" />';
		$tmp  .= '<input class="debug" type="'.$type_input.'" value="'.$result['Article']['organization_id'].'" id="article_organization_id-'.$rowId.'" />';
		
		$tmp  .= '<input class="debug" type="'.$type_input.'" value="'.$result['Article']['prezzo'].'" id="prezzo-'.$rowId.'" />';
		$tmp  .= '<input class="debug" type="'.$type_input.'" value="'.$result['Article']['stato'].'" id="article_stato-'.$rowId.'" />';
		$tmp  .= '<input class="debug" type="'.$type_input.'" value="99999999" id="qta_cart-'.$rowId.'" />';   // la qta_cart serve solo per il ctrl js, in Model/AjaxGasCart e' ricalcolato
		$tmp  .= '<input class="debug" type="'.$type_input.'" value="'.$result['Article']['qta_minima'].'" id="qta_minima-'.$rowId.'" />';
		$tmp  .= '<input class="debug" type="'.$type_input.'" value="'.$result['Article']['qta_massima'].'" id="qta_massima-'.$rowId.'" />';
		$tmp  .= '<input class="debug" type="'.$type_input.'" value="'.$result['Article']['qta_minima_order'].'" id="qta_minima_order-'.$rowId.'" />';
		$tmp  .= '<input class="debug" type="'.$type_input.'" value="'.$result['Article']['qta_massima_order'].'" id="qta_massima_order-'.$rowId.'" />';
		$tmp  .= '<input class="debug" type="'.$type_input.'" value="'.$result['Article']['qta_multipli'].'" id="qta_multipli-'.$rowId.'" />';
		$tmp  .= '<input class="debug" type="'.$type_input.'" value="'.$qta.'" id="qta_prima_modifica-'.$rowId.'" />';  // serve in caso Article.stato = QTAMAXORDER o Carts.stato = LOCK per bloccare il tasto +
		
		$tmp  .= '<a id="actionTrView-'.$rowId.'" action="articles-'.$rowId.'" class="actionTrView openTrView" href="#" title="'.__('Href_title_expand').'"></a></td>';
		
		return $tmp;
	}
	
	private function __drawSimple($numResult, $result, $options) {
	
		$tmp = "";
	
		$rowId = $this->__getRowId($numResult, $result);
	
		if(!empty($result['BookmarksArticle']['qta']))
			$qta = $result['BookmarksArticle']['qta'];
		else
			$qta = 0;
			
			
		$tmp  .= '<td>';
		if($result['Article']['bio']=='Y')
			$tmp  .= '<span class="bio" title="'.Configure::read('bio').'"></span>';
		else
			$tmp  .= "";
		$tmp  .= '</td>';
	
		$tmp  .= '<td>';
		$tmp  .= $result['Article']['name'];
		$tmp  .= $this->drawArticleNotaAjax($rowId, $result['Article']['nota']);
		$tmp  .= '</td>';
	
		$tmp  .= '<td style="white-space: nowrap;">';
		if($result['Article']['qta']>0)
			$tmp  .= $this->getArticleConf($result['Article']['qta'], $this->traslateEnum($result['Article']['um']));
		$tmp  .= '</td>';
	
		$tmp  .= "\n";  // Prezzo unità
		$tmp  .= '<td style="white-space: nowrap;">';
		$tmp  .= $result['Article']['prezzo_e'];
		$tmp  .= '</td>';
		$tmp  .= "\n";  // Prezzo/UM
		$tmp  .= '<td style="white-space: nowrap;">';
		$tmp  .= $this->getArticlePrezzoUM($result['Article']['prezzo'], $result['Article']['qta'], $result['Article']['um'], $result['Article']['um_riferimento']);
		$tmp  .= '</td>';
		$tmp  .= "\n";  // Qta minima
		$tmp  .= '<td style="white-space: nowrap;">';
		$tmp  .= sprintf("%5.2f",$result['Article']['qta_minima']);
		$tmp  .= '</td>';
	
		$tmp  .= "\n";  // Qta
		if($qta>0) $classQta = "qtaUno";
		else $classQta = "qtaZero";
	
		$tmp  .= '<td style="white-space:nowrap;text-align:center;width:125px;">';
		$tmp  .= "\n";
		$tmp  .= '<div id="qta-'.$rowId.'" class="'.$classQta.' qta">';
		if($qta>0) $tmp  .= $qta;
		$tmp  .= '</div>';
		$tmp  .= "\n";
		$tmp  .= '<div id="buttonPiuMeno-'.$rowId.'" class="buttonPiuMeno" style="display:none;">';
		$tmp  .= '<img alt="compra in +" src="'.Configure::read('App.img.cake').'/button-piu.png" class="buttonCarrello buttonPiu" />';
		$tmp  .= "\n";
		$tmp  .= '<img alt="compra in -" src="'.Configure::read('App.img.cake').'/button-meno.png" class="buttonCarrello buttonMeno" />';
		$tmp  .= "\n";
		$tmp  .= '</div>';
		$tmp  .= "\n";
		$tmp  .= '</td>';
	
		/*
		 * importo
		*/
		$importo = ($qta * $result['Article']['prezzo']);
	
		$tmp  .= '<td style="white-space:nowrap;width:100px;">';
		$tmp  .= '<div id="prezzoNew-'.$rowId.'" class="prezzoNew">';
	
		$tmp  .= "\n";
		if(!empty($importo))
			$tmp  .= number_format($importo,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'&nbsp;&euro;';
	
		$tmp  .= '</div>';
		$tmp  .= "\n";
		$tmp  .= '<img alt="" src="'.Configure::read('App.img.cake').'/blank32x32.png" id="submitEcomm-'.$rowId.'" class="buttonCarrello submitEcomm" />';
		$tmp  .= "\n";
		$tmp  .= '<div id="msgEcomm-'.$rowId.'" class="msgEcomm"></div>';
		$tmp  .= "\n";
		$tmp  .= '</td>';
	
		return $tmp;
	}
	
	/*
	 * crea un ID per identificare la riga univoca
	* $numResult = numero incrementale della riga, serve quando ricostruisco la riga dopo il save
	*/
	private function __getRowId($numResult, $result) {
			
		$rowId = $result['Article']['id'].'_'.$numResult;
			
		return $rowId;
	}
	
	/*
	 * viene ri-caricata solo la righa dopo il salvataggio
	*/
	private function __draw_js_frontend($rowId) {
		$tmp = "";
	
		$tmp  .= '<script type="text/javascript">';
		$tmp  .= "\r\n";
		$tmp  .= 'jQuery(document).ready(function() {';
		$tmp  .= "\r\n";
		$tmp  .= '	activeSubmitEcommBookMmarks(jQuery("#row-'.$rowId.'"));';
		$tmp  .= "\r\n";
		$tmp  .= '	activeEcommRows(jQuery("#row-'.$rowId.'"));';
		$tmp  .= "\r\n";
		$tmp  .= '	actionTrView(jQuery("#actionTrView-'.$rowId.'"));';
		$tmp  .= "\r\n";
		$tmp  .= '	actionNotaView(jQuery("#actionNotaView-'.$rowId.'"));';
		$tmp  .= "\r\n";
		$tmp  .= '});';
		$tmp  .= '</script>';
	
		return $tmp;
	}
	
	/*
	 * viene ri-caricata solo la righa dopo il salvataggio
	*/
	private function __draw_js_backoffice($rowId) {
		$tmp = "";
	
		$tmp  .= '<script type="text/javascript">';
		$tmp  .= "\r\n";
		$tmp  .= 'jQuery(document).ready(function() {';
		$tmp  .= "\r\n";
		$tmp  .= '	activeSubmitEcommBookMmarks(jQuery("#row-'.$rowId.'"));';
		$tmp  .= "\r\n";
		$tmp  .= '	activeEcommRows(jQuery("#row-'.$rowId.'"));';
		$tmp  .= "\r\n";
		$tmp  .= '	actionTrView(jQuery("#actionTrView-'.$rowId.'"));';
		$tmp  .= "\r\n";
		$tmp  .= '	actionNotaView(jQuery("#actionNotaView-'.$rowId.'"));';
		$tmp  .= "\r\n";
		$tmp  .= '});';
		$tmp  .= '</script>';
	
		return $tmp;
	}
}		
?>