<?php
class ExportDocsHelper extends AppHelper {
        
	var $helpers = ['Html','Time'];
	
	/*
	 * D E L I V E R Y
	*/
	public function delivery($delivery) {
		
		$tmp  = '';
		$tmp .= '<div class="h1Pdf">';
		$tmp .=  __('Delivery').' ';
		if($delivery['sys']=='N')
			$tmp .= $delivery['luogoData'];
		else
			$tmp .= $delivery['luogo'];
		$tmp .= '</div>';

		return $tmp;
	}		

	/*
	 * O R D E R
	*/
	public function order($order) {
	
		$tmp  = '';
		$tmp .= '<div class="h1Pdf">';
		$tmp .= __('Order');
		$tmp .= 'L\'ordine &egrave; stato aperto '.$this->Time->i18nFormat($order['Order']['data_inizio'],"%A %e %B").' e si è chiuso il '.$this->Time->i18nFormat($order['Order']['data_fine'],"%A %e %B");
		$tmp .= '</div>';
		
		return $tmp;
	}

	/*
	 * S U P P L I E R S _ O R G A N I Z A T I O N
	*/
	public function suppliersOrganizationShort($suppliersOrganization) {
	
		$tmp  = '';
		$tmp .= '<div class="h2Pdf">';	
		$tmp .= __('Supplier').' '.$suppliersOrganization['name'].', '.$suppliersOrganization['descrizione'];
		$tmp .= '</div>';
	
		return $tmp;
	}

	public function suppliersOrganization($suppliersOrganization) {
	
		$tmp  = '';
		$tmp .= '<div class="h2Pdf">';
		$tmp .= __('Supplier').' '.$suppliersOrganization['name'].', '.$suppliersOrganization['descrizione'];
		$tmp .= '<br />';
		$tmp .= '<span class="h4Pdf">';
		if(!empty($suppliersOrganization['indirizzo']) || !empty($suppliersOrganization['localita'])) 
			$tmp .= __('Address').' '.$suppliersOrganization['indirizzo'].' '.$suppliersOrganization['localita'];
		if(!empty($suppliersOrganization['provincia'])) $tmp .= ' ('.$suppliersOrganization['provincia'].') ';
		if(!empty($suppliersOrganization['telefono']) || !empty($suppliersOrganization['telefono2']))
			$tmp .= 'Tel '.$suppliersOrganization['telefono'].' '.$suppliersOrganization['telefono2'];
		$tmp .= '</span>';
		$tmp .= '</div>';
	
		return $tmp;
	}
	
	public function desSupplier($supplier) {
	
		$tmp  = '';
		$tmp .= '<div class="h2Pdf">';
		$tmp .= __('Supplier').' '.$supplier['name'].', '.$supplier['descrizione'];
		$tmp .= '<br />';
		$tmp .= '<span class="h4Pdf">';
		if(!empty($supplier['indirizzo']) || !empty($supplier['localita'])) 
			$tmp .= __('Address').' '.$supplier['indirizzo'].' '.$supplier['localita'];
		if(!empty($supplier['provincia'])) $tmp .= ' ('.$supplier['provincia'].') ';
		if(!empty($supplier['telefono']) || !empty($supplier['telefono2'])) 
			$tmp .= 'Tel '.$supplier['telefono'].' '.$supplier['telefono2'];
		$tmp .= '</span>';
		$tmp .= '</div>';
	
		return $tmp;
	}

	/*
	 * per il report degli articoli assocati all'ordine
	 */
	public function suppliersOrganizationDelivery($suppliersOrganization, $DeliveryLabel) {
	
		$tmp  = '';
		$tmp .= '<div class="h2Pdf">';
		$tmp .= __('Supplier').' '.$suppliersOrganization['name'].', '.$suppliersOrganization['descrizione'];
		$tmp .= '<br />';
		$tmp .= '<span class="h4Pdf">';
		$tmp .= __('Address').' '.$suppliersOrganization['indirizzo'].' '.$suppliersOrganization['localita'];
		if(!empty($suppliersOrganization['provincia'])) $tmp .= ' ('.$suppliersOrganization['provincia'].') ';
		$tmp .= 'Tel '.$suppliersOrganization['telefono'].' '.$suppliersOrganization['telefono2'];
		$tmp .= '<br />';
		$tmp .= 'Consegna '.$DeliveryLabel;
		$tmp .= '</span>';
		$tmp .= '</div>';
	
		return $tmp;
	}
	
	/*
	 * S U P P L I E R S _ O R G A N I Z A T I O N S _ R E F E R E N T S
	*/
	public function suppliersOrganizationsReferent($suppliersOrganizationsReferents) {

		$tmp  = '';
		if(!empty($suppliersOrganizationsReferents)) {
			$tmp .= '<div class="h4Pdf">'.__('Suppliers Organizations Referents').': ';
			foreach ($suppliersOrganizationsReferents as $i => $suppliersOrganizationReferent) {
				$tmp .= ' '.$suppliersOrganizationReferent['User']['name'].' '.$suppliersOrganizationReferent['User']['email'].' '.$suppliersOrganizationReferent['Profile']['phone'];

			if(isset($suppliersOrganizationReferent['UserProfile2']['satispay']) && $suppliersOrganizationReferent['UserProfile2']['satispay']=='"Y"')	
					$tmp .= ' <img src="/images/satispay-ico.png" title="Ha Satispay" style="margin-top:2px" />';

				if(($i+1) < count($suppliersOrganizationsReferents)) $tmp .= ' - ';
			}
			$tmp .= '</div>';
		}
		return $tmp;
	}
	
	/*
	 * ripulisce la stringa da caratteri che non servono per csv, excel
	 * 	&nbsp; &euro;
	 */
	public function prepareCsv($str) {
		
		$str = str_replace("&nbsp;", " ", $str);
		$str = str_replace("&euro;", "", $str);
		
		$str = str_replace("à", "a'", $str);
		
		return $str;
	}
	
	public function prepareCsvAccenti($str) {

		$str = str_replace("à", "a'", $str);
		$str = str_replace("è", "e'", $str);
		$str = str_replace("ì", "i'", $str);
		$str = str_replace("ò", "o'", $str);
		$str = str_replace("ù", "u'", $str);
	
		return $str;
	}
}
?>