<?php
App::uses('AppModel', 'Model');


class DesArticlesSyncronize extends AppModel {

    public $useTable = 'articles';
   
	public $belongsTo = array(
		'CategoriesArticle' => array(
				'className' => 'CategoriesArticle',
				'foreignKey' => 'category_article_id',
				'conditions' =>  'CategoriesArticle.organization_id = Article.organization_id',
				'fields' => '',
				'order' => '',
		)
	);

	/*
	 * articolo del GAS master gia' presente nel GAS => UPDATE Article
	 * 	 	
	 * copia un articolo da Master.Article a My.Article
	 * copia l'img da da Master.Article a My.Article
	 */
	public function syncronize_update($user, $master_organization_id, $master_article_id, $article_id, $debug=false) {

		$msg_esito = "";
		
        if(empty($master_organization_id) || empty($master_article_id) || empty($article_id)) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }
		
		App::import('Model', 'Article');

		$options = [];
		$options['conditions'] = array('Article.organization_id' => $master_organization_id,
									   'Article.id' => $master_article_id);
		$options['recursive'] = -1;
		$Article = new Article;
		$masterResults = $Article->find('first', $options);	

		if($debug) {
			echo "<pre>Article Originale \n";
			print_r($options['conditions']);
			print_r($masterResults);
			echo "</pre>";			
		}
		
		if(empty($masterResults)) {
			$msg_esito = "Articolo del produttore con ID $article_id non trovato!";

			if(!$debug) 
				return $msg_esito;				
			else
				self::x($msg_esito);
		}
		
		/*
		 * my Article
		 */
		$options = [];
		$options['conditions'] = array('Article.organization_id' => $user->organization['Organization']['id'],
									   'Article.id' => $article_id);
		$options['recursive'] = -1;
		$Article = new Article;
		$myResults = $Article->find('first', $options);	

		/*
		 * override Article
		 */
		$myResults['Article']['name'] = $masterResults['Article']['name'];
		$myResults['Article']['codice'] = $masterResults['Article']['codice'];
		$myResults['Article']['nota'] = $masterResults['Article']['nota'];
		$myResults['Article']['ingredienti'] = $masterResults['Article']['ingredienti'];
		$myResults['Article']['prezzo'] = $masterResults['Article']['prezzo'];
		$myResults['Article']['qta'] = $masterResults['Article']['qta'];
		$myResults['Article']['um'] = $masterResults['Article']['um'];
		$myResults['Article']['um_riferimento'] = $masterResults['Article']['um_riferimento'];
		$myResults['Article']['pezzi_confezione'] = $masterResults['Article']['pezzi_confezione'];
		$myResults['Article']['qta_minima'] = $masterResults['Article']['qta_minima'];
		$myResults['Article']['qta_multipli'] = $masterResults['Article']['qta_multipli'];
		$myResults['Article']['bio'] = $masterResults['Article']['bio'];
		
		$myResults['Article']['flag_presente_articlesorders'] = 'Y';
		
		$Article->set($myResults);
		$Article->validator()->remove('prezzo'); // remove 'rule' => 'decimalIT' perche' gia' corretto
		if(!$Article->validates()) {
			$errors = $Article->validationErrors;
			
			foreach($errors as $key => $value) 
				foreach($value as $key2 => $msg) 
					$msg_esito .= $msg.'<br />';
				
			if(!$debug) 
				return $msg_esito;				
			else
				self::x($msg_esito);
		}
		
		/* 
		 * img1
		*/
		if(1==2 &&
		
		
		
		
		!empty($masterResults['Article']['img1'])) {
			$newFile = $Article->copy_img_prod_gas_supplier($user, $masterResults, $debug);
			if($newFile!=false) 
				$myResults['Article']['img1'] = $newFile;
			else {
				$msg_esito = "Errore nella copia dell'immagine dell'articolo con ID $article_id <br />";
				
				/* vado cmq avanti
				if(!$debug) 
					return $msg_esito;				
				else
					self::x($msg_esito);
				*/
				if($debug) 
					self::x($msg_esito);			
			}
		}
		
		if($debug) {
			echo "<pre>Article SAVE to UPDATE \n";
			print_r($myResults);
			echo "</pre>";			
		}
		
		$Article->create();
		if(!$Article->save($myResults)) {
			$msg_esito .= "Articolo del produttore con ID $article_id non salvato!";
			
			if(!$debug) 
				return $msg_esito;				
			else
				self::x($msg_esito);
		}
				
		if($debug)
			exit;
		
		return true;
	} 


	/*
	 * nuovo articolo del GAS Master => INSERT Article
	 * 	 
	 * copia un articolo da Master.Article a My.Article
	 * copia l'img da da MasterArticle a My.Article
	 */
	public function syncronize_insert($user, $master_organization_id, $master_article_id, $supplier_id, $category_article_id=0, $debug=false) {

		$msg_esito = "";
		
        if(empty($master_organization_id) || empty($master_article_id) || empty($supplier_id)) {
            return false;
        }

		App::import('Model', 'Article');
		$Article = new Article;
		
		App::import('Model', 'SuppliersOrganization');
		$SuppliersOrganization = new SuppliersOrganization;	
		
		$options = [];
		$options['conditions'] = array('Article.organization_id' => $master_organization_id,
									   'Article.id' => $master_article_id);
		$options['recursive'] = -1;
		$Article = new Article;
		$masterResults = $Article->find('first', $options);	

		if(empty($masterResults)) {
			$msg_esito = "Articolo del produttore con ID $article_id non trovato!";

			if(!$debug) 
				return $msg_esito;				
			else
				self::x($msg_esito);
		}


		/*
		 * get elenco propri Articles 
		 */	
   		$options = [];
   		$options['conditions'] = array('SuppliersOrganization.organization_id' => $user->organization['Organization']['id'],
										'SuppliersOrganization.supplier_id' => $supplier_id);
   		$options['fields'] = array('SuppliersOrganization.id');
   		$options['recursive'] = -1;
   		$suppliersOrganizationResults = $SuppliersOrganization->find('first', $options);
		
		/*
		 * popolo Article
		 */		
		$row = [];
		
		$row['Article'] = $masterResults['Article'];

		$row['Article']['id'] = $this->_getMaxIdOrganizationId($user->organization['Organization']['id']);
		$row['Article']['organization_id'] = $user->organization['Organization']['id'];
		$row['Article']['supplier_organization_id'] = $suppliersOrganizationResults['SuppliersOrganization']['id'];		
		$row['Article']['category_article_id'] = $category_article_id;
				
		$Article->set($row);
		$Article->validator()->remove('prezzo'); // remove 'rule' => 'decimalIT' perche' gia' corretto
		if(!$Article->validates()) {
			$errors = $Article->validationErrors;
			
			foreach($errors as $key => $value) 
				foreach($value as $key2 => $msg) 
					$msg_esito .= $msg.'<br />';
				
			if(!$debug) 
				return $msg_esito;				
			else
				self::x($msg_esito);
		}
			
		/* 
		 * img1
		*/
		if(1==2 &&
		
		
		
		!empty($masterResults['Article']['img1'])) {
			$newFile = $Article->copy_img_prod_gas_supplier($user, array_merge($masterResults, $row), $debug);
			if($newFile!=false) 
				$row['Article']['img1'] = $newFile;
			else {
				$msg_esito = "Errore nella copia dell'immagine dell'articolo con ID $article_id <br />";
				
				/* vado cmq avanti
				if(!$debug) 
					return $msg_esito;				
				else
					self::x($msg_esito);
				*/
				if($debug) 
					self::x($msg_esito);
			}			
		}
		
		if($debug) {
			echo "<pre>Article SAVE to INSERT \n";
			print_r($row);
			echo "</pre>";			
		}
		
		$Article->create();
		if(!$Article->save($row)) {
			$msg_esito = "Articolo del produttore con ID $article_id non salvato!";
			
			if(!$debug) 
				return $msg_esito;				
			else
				self::x($msg_esito);
		}
		
		if($debug)
			exit;
		
		return true;
	} 	

	/*
	 * articolo del GAS Master non + presente nel suo archivio => e' acquistato e non posso DELETE Article => Article.flag_presente_articlesorder = 'N'
	 */
	public function syncronize_flag_presente_articlesorders($user, $article_id, $debug=false) {

		$msg_esito = "";
		
        if(empty($article_id)) {
            return false;
        }

        $tmp_user = $this->utilsCommons->createObjUser(['organization_id' => $organization_id]);
		
		App::import('Model', 'Article');
		$Article = new Article;
					
		/*
		 * Article prima del salvataggio
		*/
		$options = [];
		$options['conditions'] = array('Article.organization_id' => $user->organization['Organization']['id'],
									  'Article.id' => $article_id);
		$options['recursive'] = -1;
		$results = $Article->find('first', $options);
				
		$results['Article']['flag_presente_articlesorders'] = 'N';
		
		$Article->set($results);
		$Article->validator()->remove('prezzo'); // remove 'rule' => 'decimalIT' perche' gia' corretto
		if(!$Article->validates()) {
			$errors = $Article->validationErrors;
			
			foreach($errors as $key => $value) 
				foreach($value as $key2 => $msg) 
					$msg_esito .= $msg.'<br />';
				
			if(!$debug) 
				return $msg_esito;				
			else
				self::x($msg_esito);
		}
		
		if($debug) {
			echo "<pre>Article SAVE to UPDATE flag_presente_articlesorders \n";
			print_r($results);
			echo "</pre>";			
		}
		
		$Article->create();
		if(!$Article->save($results)) {
			$msg_esito = "Articolo del produttore con ID $article_id non salvato!";
			
			if(!$debug) 
				return $msg_esito;				
			else
				self::x($msg_esito);
		}
				
		if($debug)
			exit;
		
		return true;
		
		
		if($debug)
			exit;
		
		return true;
	} 
	
	private function _getMaxIdOrganizationId($organization_id) {
    	
    	$maxId = 1;
    	
		App::import('Model', 'Article');
		$Article = new Article;
		
		$options = [];
     	$options['fields'] = array('MAX(Article.id)+1 AS maxId');
    	$options['conditions'] = array('Article.organization_id' => $organization_id);
    	$options['recursive'] = -1;
    	$results = $Article->find('first', $options);
    	if(!empty($results)) {
    		$results = current($results);
    		$maxId = $results['maxId'];
    		if(empty($maxId)) $maxId = 1;
    	}

    	return $maxId;
    }
	
	public function afterFind($results, $primary = false) {

		foreach ($results as $key => $val) {
			if(!empty($val)) {

				if(isset($val['DesArticlesSyncronize']['prezzo'])) {
					$results[$key]['DesArticlesSyncronize']['prezzo_'] = number_format($val['DesArticlesSyncronize']['prezzo'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
					$results[$key]['DesArticlesSyncronize']['prezzo_e'] = $results[$key]['DesArticlesSyncronize']['prezzo_'].' &euro;';
				}
				else
					/*
					 * se il find() arriva da $hasAndBelongsToMany
					*/
				 if(isset($val['prezzo'])) {
					$results[$key]['prezzo_'] = number_format($val['prezzo'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
					$results[$key]['prezzo_e'] = $results[$key]['prezzo_'].' &euro;';
				}
				
				/*
				 * qta, da 1.00 a 1
				 * 		da 0.75 a 0,75  
				 * */
				if(isset($val['DesArticlesSyncronize']['qta'])) {
					$qta = str_replace(".", ",", $val['DesArticlesSyncronize']['qta']);
					$arrCtrlTwoZero = explode(",",$qta);
					if($arrCtrlTwoZero[1]=='00') $qta = $arrCtrlTwoZero[0];
					$results[$key]['DesArticlesSyncronize']['qta_'] = $qta;
				}
				else
				/*
				 * se il find() arriva da $hasAndBelongsToMany
				*/	
				if(isset($val['qta'])) {
					$qta = str_replace(".", ",", $val['qta']);
					$arrCtrlTwoZero = explode(",",$qta);
					if($arrCtrlTwoZero[1]=='00') $qta = $arrCtrlTwoZero[0];
					$results[$key]['qta_'] = $qta;
				}
			}
		}
		
		return $results;
	}	
}