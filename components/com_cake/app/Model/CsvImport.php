<?php
App::uses('AppModel', 'Model');


class CsvImport extends AppModel {

	public $useTable = false;

	/*
	 * in base all'action
	 * restituisce la struttura del file: campi e tipologia 
	 */
	public function getStrutturaFile($user, $action, $version='COMPLETE', $debug=false) {
	
		$file_fields = [];
		
		/*
		 * compongo la struttura del file per ID 
		 */ 
		switch($action) {
			case "admin_articles":
			case "admin_articles_prepare":
			case "admin_articles_insert":
				array_push($file_fields,'article_name');
				if($user->organization['Organization']['hasFieldArticleCodice']=='Y')
					array_push($file_fields,'codice');
				array_push($file_fields,'nota');
				if($user->organization['Organization']['hasFieldArticleIngredienti']=='Y')
					array_push($file_fields,'ingredienti');
				array_push($file_fields,'prezzo');
				array_push($file_fields,'qta');
				array_push($file_fields,'um');
				array_push($file_fields,'um_riferimento');
				array_push($file_fields,'pezzi_confezione');
				array_push($file_fields,'qta_minima');
				array_push($file_fields,'qta_massima');
				array_push($file_fields,'qta_minima_order');
				array_push($file_fields,'qta_massima_order');
				array_push($file_fields,'qta_multipli');
				array_push($file_fields,'bio');
			break;
			case "admin_articles_form_export":
			case "admin_articles_export": 
			case "admin_articles_form_import":
			case "admin_articles_prepare_import":
			case "admin_articles_import":
				array_push($file_fields,'id');
				if($user->organization['Organization']['hasFieldArticleCodice']=='Y')
					array_push($file_fields,'codice');				
				array_push($file_fields,'article_name');
				array_push($file_fields,'qta');
				array_push($file_fields,'um');
				array_push($file_fields,'um_riferimento');
				array_push($file_fields,'prezzo');			
				array_push($file_fields,'nota');
				if($user->organization['Organization']['hasFieldArticleIngredienti']=='Y')
					array_push($file_fields,'ingredienti');	
				array_push($file_fields,'pezzi_confezione');
				array_push($file_fields,'qta_minima');
				array_push($file_fields,'qta_massima');
				array_push($file_fields,'qta_multipli');
				array_push($file_fields,'qta_minima_order');
				array_push($file_fields,'qta_massima_order');
				array_push($file_fields,'flag_presente_articlesorders');							
			break;
			case "admin_users":
			case "admin_users_prepare":
			case "admin_users_insert":
				array_push($file_fields,'user_name');
				array_push($file_fields,'username');
				array_push($file_fields,'email');
				array_push($file_fields,'phone');
				array_push($file_fields,'phone2');
				array_push($file_fields,'address');
				array_push($file_fields,'city');
				array_push($file_fields,'region');
				array_push($file_fields,'country');
				array_push($file_fields,'postal_code');
				array_push($file_fields,'codice');      
			break;
			default:
				self::x("CsvImport::getStrutturaFile() - $action non valida!");
			break;
		}

		self::d($file_fields, $debug);
		
		/*
		 * dagli ID ottengo i diverrsi items (tipologia, caratteristiche del campo) 
		 */ 
		$fields = $this->instanziaFields($version);	 
		$i=0;
		$results = [];
		foreach($file_fields as $file_field) {
			self::d($file_field, $debug);
			if(isset($fields[$file_field])) {
				$results[$i] = $fields[$file_field];
			
				$i++;
			}
		}
		
		self::d($results, $debug);
		
		return $results;
	}
	
	/*
	 * elenco di tutti i possibili campi di un 
	 */
	private function instanziaFields($version='COMPLETE') {
		
		$fields['id']['LABEL'] = 'Id';
		$fields['id']['INPUT_NAME'] = 'id';
		$fields['id']['INPUT_TYPE'] = 'int';
		$fields['id']['REQUEST'] = 'Y';
		$fields['id']['UPPERCASE'] = 'N';
		$fields['id']['UCWORDS'] = 'N';
		$fields['id']['EXAMPLE_VALUE1'] = '1';
		$fields['id']['EXAMPLE_VALUE2'] = '2';
		$fields['id']['VERSION_SIMPLE'] = false;
		$fields['id']['DEFAULT'] = 0;
		
		$fields['article_name']['LABEL'] = 'Nome';
		$fields['article_name']['INPUT_NAME'] = 'name';
		$fields['article_name']['INPUT_TYPE'] = 'text';
		$fields['article_name']['REQUEST'] = 'Y';
		$fields['article_name']['UPPERCASE'] = 'N';
		$fields['article_name']['UCWORDS'] = 'Y';
		$fields['article_name']['EXAMPLE_VALUE1'] = 'Farina';
		$fields['article_name']['EXAMPLE_VALUE2'] = 'Soia';
		$fields['article_name']['VERSION_SIMPLE'] = true;
		$fields['article_name']['DEFAULT'] = '';		
		
		$fields['codice']['LABEL'] = 'Codice';
		$fields['codice']['INPUT_NAME'] = 'codice';
		$fields['codice']['INPUT_TYPE'] = 'text';
		$fields['codice']['REQUEST'] = 'N';
		$fields['codice']['UPPERCASE'] = 'N';
		$fields['codice']['UCWORDS'] = 'N';
		$fields['codice']['EXAMPLE_VALUE1'] = '001';
		$fields['codice']['EXAMPLE_VALUE2'] = 'SO02';
		$fields['codice']['VERSION_SIMPLE'] = false;
		$fields['codice']['DEFAULT'] = '';
		
		$fields['nota']['LABEL'] = 'Nota';
		$fields['nota']['INPUT_NAME'] = 'nota';
		$fields['nota']['INPUT_TYPE'] = 'text';
		$fields['nota']['REQUEST'] = 'N';
		$fields['nota']['UPPERCASE'] = 'N';
		$fields['nota']['UCWORDS'] = 'N';
		$fields['nota']['EXAMPLE_VALUE1'] = 'è macinata a mano';
		$fields['nota']['EXAMPLE_VALUE2'] = '';
		$fields['nota']['VERSION_SIMPLE'] = false;
		$fields['nota']['DEFAULT'] = '';
			
		$fields['ingredienti']['LABEL'] = 'Ingredienti';
		$fields['ingredienti']['INPUT_NAME'] = 'ingredienti';
		$fields['ingredienti']['INPUT_TYPE'] = 'text';
		$fields['ingredienti']['REQUEST'] = 'N';
		$fields['ingredienti']['UPPERCASE'] = 'N';
		$fields['ingredienti']['UCWORDS'] = 'N';
		$fields['ingredienti']['EXAMPLE_VALUE1'] = '-';
		$fields['ingredienti']['EXAMPLE_VALUE2'] = 'tutti naturali';
		$fields['ingredienti']['VERSION_SIMPLE'] = false;
		$fields['ingredienti']['DEFAULT'] = '';
	
		$fields['prezzo']['LABEL'] = 'Prezzo';
		$fields['prezzo']['INPUT_NAME'] = 'prezzo';
		$fields['prezzo']['INPUT_TYPE'] = 'double';
		$fields['prezzo']['REQUEST'] = 'Y';
		$fields['prezzo']['UPPERCASE'] = 'N';
		$fields['prezzo']['UCWORDS'] = 'N';
		$fields['prezzo']['EXAMPLE_VALUE1'] = '10,50';
		$fields['prezzo']['EXAMPLE_VALUE2'] = '1,00';
		$fields['prezzo']['VERSION_SIMPLE'] = true;
		$fields['prezzo']['DEFAULT'] = '';
		
		$fields['qta']['LABEL'] = 'Quantità';
		$fields['qta']['INPUT_NAME'] = 'qta';
		$fields['qta']['INPUT_TYPE'] = 'double';
		$fields['qta']['REQUEST'] = 'Y';
		$fields['qta']['UPPERCASE'] = 'N';
		$fields['qta']['UCWORDS'] = 'N';
		$fields['qta']['EXAMPLE_VALUE1'] = '1';
		$fields['qta']['EXAMPLE_VALUE2'] = '1,50';
		$fields['qta']['VERSION_SIMPLE'] = true;
		$fields['qta']['DEFAULT'] = '';
		
		$fields['um']['LABEL'] = 'Unità di misura';
		$fields['um']['INPUT_NAME'] = 'um';
		$fields['um']['INPUT_TYPE'] = 'array_um';
		$fields['um']['REQUEST'] = 'Y';
		$fields['um']['UPPERCASE'] = 'Y';
		$fields['um']['UCWORDS'] = 'N';
		$fields['um']['EXAMPLE_VALUE1'] = 'Kg';
		$fields['um']['EXAMPLE_VALUE2'] = 'Lt';
		$fields['um']['VERSION_SIMPLE'] = true;
		$fields['um']['DEFAULT'] = '';
		
		$fields['um_riferimento']['LABEL'] = 'Unità di misura<br/>di riferimento';
		$fields['um_riferimento']['INPUT_NAME'] = 'um_riferimento';
		$fields['um_riferimento']['INPUT_TYPE'] = 'array_um';
		$fields['um_riferimento']['REQUEST'] = 'Y';
		$fields['um_riferimento']['UPPERCASE'] = 'Y';
		$fields['um_riferimento']['UCWORDS'] = 'N';
		$fields['um_riferimento']['EXAMPLE_VALUE1'] = 'Kg';
		$fields['um_riferimento']['EXAMPLE_VALUE2'] = 'Lt';
		$fields['um_riferimento']['VERSION_SIMPLE'] = true;
		$fields['um_riferimento']['DEFAULT'] = '';
		
		$fields['pezzi_confezione']['LABEL'] = 'Pezzi in conf';
		$fields['pezzi_confezione']['INPUT_NAME'] = 'pezzi_confezione';
		$fields['pezzi_confezione']['INPUT_TYPE'] = 'int_max_zero';
		$fields['pezzi_confezione']['REQUEST'] = 'Y';
		$fields['pezzi_confezione']['UPPERCASE'] = 'N';
		$fields['pezzi_confezione']['UCWORDS'] = 'N';
		$fields['pezzi_confezione']['EXAMPLE_VALUE1'] = '1';
		$fields['pezzi_confezione']['EXAMPLE_VALUE2'] = '1';
		$fields['pezzi_confezione']['VERSION_SIMPLE'] = false;
		$fields['pezzi_confezione']['DEFAULT'] = '1';
		 
		$fields['qta_minima']['LABEL'] = 'Qta minima per ogni gasista';
		$fields['qta_minima']['INPUT_NAME'] = 'qta_minima';
		$fields['qta_minima']['INPUT_TYPE'] = 'int_max_zero';
		$fields['qta_minima']['REQUEST'] = 'Y';
		$fields['qta_minima']['UPPERCASE'] = 'N';
		$fields['qta_minima']['UCWORDS'] = 'N';
		$fields['qta_minima']['EXAMPLE_VALUE1'] = '1';
		$fields['qta_minima']['EXAMPLE_VALUE2'] = '1';
		$fields['qta_minima']['VERSION_SIMPLE'] = false;
		$fields['qta_minima']['DEFAULT'] = '1';
		
		$fields['qta_massima']['LABEL'] = 'Qta massima per ogni gasista';
		$fields['qta_massima']['INPUT_NAME'] = 'qta_massima';
		$fields['qta_massima']['INPUT_TYPE'] = 'int';
		$fields['qta_massima']['REQUEST'] = 'Y';
		$fields['qta_massima']['UPPERCASE'] = 'N';
		$fields['qta_massima']['UCWORDS'] = 'N';
		$fields['qta_massima']['EXAMPLE_VALUE1'] = '0';
		$fields['qta_massima']['EXAMPLE_VALUE2'] = '0';
		$fields['qta_massima']['VERSION_SIMPLE'] = false;
		$fields['qta_massima']['DEFAULT'] = '0';
		
		$fields['qta_minima_order']['LABEL'] = 'Qta minima rispetto all\'oridne';
		$fields['qta_minima_order']['INPUT_NAME'] = 'qta_minima_order';
		$fields['qta_minima_order']['INPUT_TYPE'] = 'int';
		$fields['qta_minima_order']['REQUEST'] = 'Y';
		$fields['qta_minima_order']['UPPERCASE'] = 'N';
		$fields['qta_minima_order']['UCWORDS'] = 'N';
		$fields['qta_minima_order']['EXAMPLE_VALUE1'] = '0';
		$fields['qta_minima_order']['EXAMPLE_VALUE2'] = '0';
		$fields['qta_minima_order']['VERSION_SIMPLE'] = false;
		$fields['qta_minima_order']['DEFAULT'] = '0';
		
		$fields['qta_massima_order']['LABEL'] = 'Qta massima rispetto all\'oridne';
		$fields['qta_massima_order']['INPUT_NAME'] = 'qta_massima_order';
		$fields['qta_massima_order']['INPUT_TYPE'] = 'int';
		$fields['qta_massima_order']['REQUEST'] = 'Y';
		$fields['qta_massima_order']['UPPERCASE'] = 'N';
		$fields['qta_massima_order']['UCWORDS'] = 'N';
		$fields['qta_massima_order']['EXAMPLE_VALUE1'] = '0';
		$fields['qta_massima_order']['EXAMPLE_VALUE2'] = '0';
		$fields['qta_massima_order']['VERSION_SIMPLE'] = false;
		$fields['qta_massima_order']['DEFAULT'] = '0';
		
		$fields['qta_multipli']['LABEL'] = 'Multipli';
		$fields['qta_multipli']['INPUT_NAME'] = 'qta_multipli';
		$fields['qta_multipli']['INPUT_TYPE'] = 'int_max_zero';
		$fields['qta_multipli']['REQUEST'] = 'Y';
		$fields['qta_multipli']['UPPERCASE'] = 'N';
		$fields['qta_multipli']['UCWORDS'] = 'N';
		$fields['qta_multipli']['EXAMPLE_VALUE1'] = '1';
		$fields['qta_multipli']['EXAMPLE_VALUE2'] = '2';
		$fields['qta_multipli']['VERSION_SIMPLE'] = false;
		$fields['qta_multipli']['DEFAULT'] = '1';
		
		$fields['bio']['LABEL'] = 'Bio';
		$fields['bio']['INPUT_NAME'] = 'bio';
		$fields['bio']['INPUT_TYPE'] = 'array_y_n';
		$fields['bio']['REQUEST'] = 'N';
		$fields['bio']['UPPERCASE'] = 'Y';
		$fields['bio']['UCWORDS'] = 'N';
		$fields['bio']['EXAMPLE_VALUE1'] = 'Y';
		$fields['bio']['EXAMPLE_VALUE2'] = 'N';
		$fields['bio']['VERSION_SIMPLE'] = false;
		$fields['bio']['DEFAULT'] = 'N';
			
		$fields['flag_presente_articlesorders']['LABEL'] = "Presente nell'elenco degli articoli che si possono associare ad un ordine";
		$fields['flag_presente_articlesorders']['INPUT_NAME'] = 'flag_presente_articlesorders';
		$fields['flag_presente_articlesorders']['INPUT_TYPE'] = 'array_y_n';
		$fields['flag_presente_articlesorders']['REQUEST'] = 'Y';
		$fields['flag_presente_articlesorders']['UPPERCASE'] = 'N';
		$fields['flag_presente_articlesorders']['UCWORDS'] = 'N';
		$fields['flag_presente_articlesorders']['EXAMPLE_VALUE1'] = 'Y';
		$fields['flag_presente_articlesorders']['EXAMPLE_VALUE2'] = 'N';
		$fields['flag_presente_articlesorders']['VERSION_SIMPLE'] = false;
		$fields['flag_presente_articlesorders']['DEFAULT'] = 'Y';
			
		/*
		 * user
		 */
		$fields['user_name']['LABEL'] = 'Nome';
		$fields['user_name']['INPUT_NAME'] = 'name';
		$fields['user_name']['INPUT_TYPE'] = 'text';
		$fields['user_name']['REQUEST'] = 'Y';
		$fields['user_name']['UPPERCASE'] = 'N';
		$fields['user_name']['UCWORDS'] = 'Y';
		$fields['user_name']['EXAMPLE_VALUE1'] = 'Rossi Mario';
		$fields['user_name']['EXAMPLE_VALUE2'] = 'Verdi Maria ';
		
		$fields['username']['LABEL'] = 'Username';
		$fields['username']['INPUT_NAME'] = 'username';
		$fields['username']['INPUT_TYPE'] = 'text';
		$fields['username']['REQUEST'] = 'Y';
		$fields['username']['UPPERCASE'] = 'N';
		$fields['username']['UCWORDS'] = 'N';
		$fields['username']['EXAMPLE_VALUE1'] = 'mario.rossi@gmail.com';
		$fields['username']['EXAMPLE_VALUE2'] = 'maria.verdi@hotmail.com';
	
		$fields['email']['LABEL'] = 'Mail';
		$fields['email']['INPUT_NAME'] = 'email';
		$fields['email']['INPUT_TYPE'] = 'email';
		$fields['email']['REQUEST'] = 'Y';
		$fields['email']['UPPERCASE'] = 'N';
		$fields['email']['UCWORDS'] = 'N';
		$fields['email']['EXAMPLE_VALUE1'] = 'mario.rossi@gmail.com';
		$fields['email']['EXAMPLE_VALUE2'] = 'maria.verdi@hotmail.com';
			
		$fields['phone']['LABEL'] = 'Telefono';
		$fields['phone']['INPUT_NAME'] = 'phone';
		$fields['phone']['INPUT_TYPE'] = 'text';
		$fields['phone']['REQUEST'] = 'N';
		$fields['phone']['UPPERCASE'] = 'N';
		$fields['phone']['UCWORDS'] = 'N';
		$fields['phone']['EXAMPLE_VALUE1'] = '3494543535';
		$fields['phone']['EXAMPLE_VALUE2'] = '3395974200';
		
		$fields['phone2']['LABEL'] = 'Telefono';
		$fields['phone2']['INPUT_NAME'] = 'phone2';
		$fields['phone2']['INPUT_TYPE'] = 'text';
		$fields['phone2']['REQUEST'] = 'N';
		$fields['phone2']['UPPERCASE'] = 'N';
		$fields['phone2']['UCWORDS'] = 'N';
		$fields['phone2']['EXAMPLE_VALUE1'] = '011 43563535';
		$fields['phone2']['EXAMPLE_VALUE2'] = '';
		
		$fields['address']['LABEL'] = 'Indirizzo';
		$fields['address']['INPUT_NAME'] = 'address';
		$fields['address']['INPUT_TYPE'] = 'text';
		$fields['address']['REQUEST'] = 'N';
		$fields['address']['UPPERCASE'] = 'N';
		$fields['address']['UCWORDS'] = 'N';
		$fields['address']['EXAMPLE_VALUE1'] = 'Via Roma 13';
		$fields['address']['EXAMPLE_VALUE2'] = 'Largo Saluzzo 34/bis';
		
		$fields['city']['LABEL'] = 'Città';
		$fields['city']['INPUT_NAME'] = 'city';
		$fields['city']['INPUT_TYPE'] = 'text';
		$fields['city']['REQUEST'] = 'Y';
		$fields['city']['UPPERCASE'] = 'N';
		$fields['city']['UCWORDS'] = 'Y';
		$fields['city']['EXAMPLE_VALUE1'] = 'Torino';
		$fields['city']['EXAMPLE_VALUE2'] = 'Avigliana';
		
		$fields['region']['LABEL'] = 'Provincia';
		$fields['region']['INPUT_NAME'] = 'region';
		$fields['region']['INPUT_TYPE'] = 'text';
		$fields['region']['REQUEST'] = 'N';
		$fields['region']['UPPERCASE'] = 'Y';
		$fields['region']['UCWORDS'] = 'N';
		$fields['region']['EXAMPLE_VALUE1'] = 'TO';
		$fields['region']['EXAMPLE_VALUE2'] = 'TO';
		
		$fields['country']['LABEL'] = 'Paese';
		$fields['country']['INPUT_NAME'] = 'country';
		$fields['country']['INPUT_TYPE'] = 'text';
		$fields['country']['REQUEST'] = 'N';
		$fields['country']['UPPERCASE'] = 'N';
		$fields['country']['UCWORDS'] = 'Y';
		$fields['country']['EXAMPLE_VALUE1'] = 'Italia';
		$fields['country']['EXAMPLE_VALUE2'] = 'Italia';
		
		$fields['postal_code']['LABEL'] = 'CAP';
		$fields['postal_code']['INPUT_NAME'] = 'postal_code';
		$fields['postal_code']['INPUT_TYPE'] = 'text';
		$fields['postal_code']['REQUEST'] = 'N';
		$fields['postal_code']['UPPERCASE'] = 'N';
		$fields['postal_code']['UCWORDS'] = 'N';
		$fields['postal_code']['EXAMPLE_VALUE1'] = '10125';
		$fields['postal_code']['EXAMPLE_VALUE2'] = '10343';
			
		$newFields = [];
		if($version=='SIMPLE') {
			foreach($fields as $key => $field) {
				if($field['VERSION_SIMPLE'])
					$newFields[$key] = $field;
			}
		}
		else
			$newFields = $fields;
		
		self::d($newFields);
		
		return $newFields;
	}	
}