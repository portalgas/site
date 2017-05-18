<?php
App::uses('AppModel', 'Model');

class ProdGasArticlesSyncronize extends AppModel {

    public $useTable = 'prod_gas_articles';
   
	public $hasMany = array(
		'ProdGasArticlesPromotion' => array(
				'className' => 'ProdGasArticlesPromotion',
				'foreignKey' => 'prod_gas_article_id',
				'dependent' => false,
				'conditions' =>  '',
				'fields' => '',
				'order' => '',
				'limit' => '',
				'offset' => '',
				'exclusive' => '',
				'finderQuery' => '',
				'counterQuery' => ''
		)
	);

	/*
	 * articolo del produttore gia' presente nel GAS => UPDATE Article
	 * 	 	
	 * copia un articolo da ProdGasArticle a Article
	 * copia l'img da da ProdGasArticle a Article
	 */
	public function syncronize_update($user, $organization_id, $prod_gas_article_id, $category_article_id=0, $debug=false) {

		$msg_esito = "";
		
        if(empty($prod_gas_article_id) || empty($category_article_id)) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }
		
		App::import('Model', 'Article');
		$Article = new Article;

		$Article->bindModel(array('belongsTo' => array('ProdGasArticle' => array(
														'className' => 'ProdGasArticle',
														'foreignKey' => 'prod_gas_article_id'))));
		$Article->unbindModel(array('belongsTo' => array('SuppliersOrganization', 'CategoriesArticle')));
		$Article->unbindModel(array('hasOne' => array('ArticlesArticlesType', 'ArticlesOrder')));
		$Article->unbindModel(array('hasMany' => array('ArticlesArticlesType', 'ArticlesOrder'))); 
		$Article->unbindModel(array('hasAndBelongsToMany' => array('ArticlesType', 'Order'))); 
		$options = array();
		$options['conditions'] = array('Article.organization_id' => $organization_id,
									   'ProdGasArticle.id' => $prod_gas_article_id,
									   'ProdGasArticle.supplier_id' => $user->supplier['Supplier']['id'],
									   'Article.supplier_id' => $user->supplier['Supplier']['id']);
		$options['recursive'] = 0;
		$results = $Article->find('first', $options);	

		if($debug) {
			echo "<pre>Article Originale \n";
			print_r($options['conditions']);
			print_r($results);
			echo "</pre>";			
		}
		
		if(empty($results)) {
			$msg_esito = "Articolo del produttore con ID $prod_gas_article_id non trovato!";

			if(!$debug) 
				return $msg_esito;				
			else
				die($msg_esito);
		}
		
		/*
		 * override Article
		 */
		$row = array();
		$row['Article'] = $results['Article']; 
		
		$row['Article']['category_article_id'] = $category_article_id;
		$row['Article']['name'] = $results['ProdGasArticle']['name'];
		$row['Article']['codice'] = $results['ProdGasArticle']['codice'];
		$row['Article']['nota'] = $results['ProdGasArticle']['nota'];
		$row['Article']['ingredienti'] = $results['ProdGasArticle']['ingredienti'];
		$row['Article']['prezzo'] = $results['ProdGasArticle']['prezzo'];
		$row['Article']['qta'] = $results['ProdGasArticle']['qta'];
		$row['Article']['um'] = $results['ProdGasArticle']['um'];
		$row['Article']['um_riferimento'] = $results['ProdGasArticle']['um_riferimento'];
		$row['Article']['pezzi_confezione'] = $results['ProdGasArticle']['pezzi_confezione'];
		$row['Article']['qta_minima'] = $results['ProdGasArticle']['qta_minima'];
		$row['Article']['qta_multipli'] = $results['ProdGasArticle']['qta_multipli'];
		$row['Article']['bio'] = $results['ProdGasArticle']['bio'];
		
		$row['Article']['flag_presente_articlesorders'] = 'Y';
		
		$Article->set($row);
		if(!$Article->validates()) {
			$errors = $Article->validationErrors;
			
			foreach($errors as $key => $value) 
				foreach($value as $key2 => $msg) 
					$msg_esito .= $msg.'<br />';
				
			if(!$debug) 
				return $msg_esito;				
			else
				die($msg_esito);
		}
		
		/* 
		 * img1
		*/
		if(!empty($results['ProdGasArticle']['img1'])) {
			$newFile = $Article->copy_img_prod_gas_supplier($user, $results, $debug);
			if($newFile!=false) 
				$row['Article']['img1'] = $newFile;
			else {
				$msg_esito = "Errore nella copia dell'immagine dell'articolo con ID $prod_gas_article_id <br />";
				
				/* vado cmq avanti
				if(!$debug) 
					return $msg_esito;				
				else
					die($msg_esito);
				*/
				if($debug) 
					die($msg_esito);			
			}
		}
		
		if($debug) {
			echo "<pre>Article SAVE to UPDATE \n";
			print_r($row);
			echo "</pre>";			
		}
		
		$Article->create();
		if(!$Article->save($row)) {
			$msg_esito .= "Articolo del produttore con ID $prod_gas_article_id non salvato!";
			
			if(!$debug) 
				return $msg_esito;				
			else
				die($msg_esito);
		}
				
		if($debug)
			exit;
		
		return true;
	} 

	/*
	 * nuovo articolo del produttore => INSERT Article
	 * 	 
	 * copia un articolo da ProdGasArticle a Article
	 * copia l'img da da ProdGasArticle a Article
	 */
	public function syncronize_insert($user, $organization_id, $prod_gas_article_id, $category_article_id=0, $debug=false) {

		$msg_esito = "";
		
        if(empty($organization_id) || empty($prod_gas_article_id) || empty($category_article_id)) {
            return false;
        }

		App::import('Model', 'Article');
		$Article = new Article;
		
		App::import('Model', 'ProdGasSupplier');
		$ProdGasSupplier = new ProdGasSupplier;
		
		// non utilizzo il Model ProdGasArticlesSyncronize perche' in Article::copy_img_prod_gas_supplier() utilizzo ProdGasArticle
 		App::import('Model', 'ProdGasArticle');
		$ProdGasArticle = new ProdGasArticle;
		
		$options = array();
		$options['conditions'] = array('ProdGasArticle.id' => $prod_gas_article_id,
									   'ProdGasArticle.supplier_id' => $user->supplier['Supplier']['id']);
		$options['recursive'] = -1;
		$results = $ProdGasArticle->find('first', $options);	

		if($debug) {
			echo "<pre>ProdGasArticle del produttore \n";
			print_r($options['conditions']);
			print_r($results);
			echo "</pre>";			
		}
		
		if(empty($results)) {
			$msg_esito = "Articolo del produttore con ID $prod_gas_article_id non trovato!";

			if(!$debug) 
				return $msg_esito;				
			else
				die($msg_esito);
		}
		
		/*
		 * ctrl che l'articolo del produttore non sia gia' associato
		 */	
		$options = array();
		$options['conditions'] = array('Article.prod_gas_article_id' => $prod_gas_article_id,
									   'Article.supplier_id' => $user->supplier['Supplier']['id'],
									   'Article.organization_id' => $organization_id);
		$options['recursive'] = -1;
		$articlesCtrlResults = $Article->find('first', $options);		
		if(!empty($articlesCtrlResults)) {			
			$msg_esito = "Articolo già presente!";

			if(!$debug) 
				return $msg_esito;				
			else
				die($msg_esito);			
		} 
		
		/*
		 *
		 */
		$suppliersOrganizationsResults = $ProdGasSupplier->getSuppliersOrganization($user, $organization_id, $debug);
		if(empty($suppliersOrganizationsResults['SuppliersOrganization']['id'])) {
			$msg_esito = "Non trovato il produttore associato al GAS!";
			if(!$debug) 
				return $msg_esito;				
			else
				die($msg_esito);			
		}
		
		/*
		 * popolo Article
		 */		
		$row = array();
		$row['Article']['id'] = $this->__getMaxIdOrganizationId($organization_id);
		$row['Article']['organization_id'] = $organization_id;
		$row['Article']['supplier_organization_id'] = $suppliersOrganizationsResults['SuppliersOrganization']['id'];
		$row['Article']['prod_gas_article_id'] = $results['ProdGasArticle']['id'];
		$row['Article']['supplier_id'] = $results['ProdGasArticle']['supplier_id'];
		
		$row['Article']['category_article_id'] = $category_article_id;
		$row['Article']['name'] = $results['ProdGasArticle']['name'];
		$row['Article']['codice'] = $results['ProdGasArticle']['codice'];
		$row['Article']['nota'] = $results['ProdGasArticle']['nota'];
		$row['Article']['ingredienti'] = $results['ProdGasArticle']['ingredienti'];
		$row['Article']['prezzo'] = $results['ProdGasArticle']['prezzo'];
		$row['Article']['qta'] = $results['ProdGasArticle']['qta'];
		$row['Article']['um'] = $results['ProdGasArticle']['um'];
		$row['Article']['um_riferimento'] = $results['ProdGasArticle']['um_riferimento'];
		$row['Article']['pezzi_confezione'] = $results['ProdGasArticle']['pezzi_confezione'];
		$row['Article']['qta_minima'] = $results['ProdGasArticle']['qta_minima'];
		$row['Article']['qta_multipli'] = $results['ProdGasArticle']['qta_multipli'];
		$row['Article']['bio'] = $results['ProdGasArticle']['bio'];
		
		// inalterati
		$row['Article']['qta_massima'] = 0;
		$row['Article']['qta_minima_order'] = 0;
		$row['Article']['qta_massima_order'] = 0;
		$row['Article']['alert_to_qta'] = 0;
		$row['Article']['stato'] = 'Y';

		$row['Article']['flag_presente_articlesorders'] = 'Y';
				
		$Article->set($row);
		if(!$Article->validates()) {
			$errors = $Article->validationErrors;
			
			foreach($errors as $key => $value) 
				foreach($value as $key2 => $msg) 
					$msg_esito .= $msg.'<br />';
				
			if(!$debug) 
				return $msg_esito;				
			else
				die($msg_esito);
		}
			
		/* 
		 * img1
		*/
		if(!empty($results['ProdGasArticle']['img1'])) {
			$newFile = $Article->copy_img_prod_gas_supplier($user, array_merge($results, $row), $debug);
			if($newFile!=false) 
				$row['Article']['img1'] = $newFile;
			else {
				$msg_esito = "Errore nella copia dell'immagine dell'articolo con ID $prod_gas_article_id <br />";
				
				/* vado cmq avanti
				if(!$debug) 
					return $msg_esito;				
				else
					die($msg_esito);
				*/
				if($debug) 
					die($msg_esito);
			}			
		}
		
		if($debug) {
			echo "<pre>Article SAVE to INSERT \n";
			print_r($row);
			echo "</pre>";			
		}
		
		$Article->create();
		if(!$Article->save($row)) {
			$msg_esito = "Articolo del produttore con ID $prod_gas_article_id non salvato!";
			
			if(!$debug) 
				return $msg_esito;				
			else
				die($msg_esito);
		}
		
		if($debug)
			exit;
		
		return true;
	} 	

	/*
	 * articolo del produttore non + presente nel suo archivio => DELETE Article
	 */
	public function syncronize_delete($user, $organization_id, $article_id, $debug=false) {

		$msg_esito = "";
		
        if(empty($organization_id) || empty($article_id)) {
            return false;
        }

		$tmp_user->organization['Organization']['id'] =  $organization_id; 
		
		App::import('Model', 'Article');
		$Article = new Article;
		
		/*
 		 * ctrl gli eventuali acquisti gia' effettuati, se true non posso cancellarlo
		 */
		$isArticleInCart = $Article->isArticleInCart($tmp_user, $organization_id, $article_id);
		if($isArticleInCart)  {
			$msg_esito = "Articolo acquistato, non posso cancellarlo!";

			if(!$debug) 
				return $msg_esito;				
			else
				die($msg_esito);			
		}
					
		/*
		 * Article prima del salvataggio
		*/
		$options = array();
		$options['conditions'] = array('Article.organization_id' => $organization_id,
									  'Article.id' => $article_id);
		$options['recursive'] = -1;
		$options['fields'] = array('img1');
		$results = $Article->find('first', $options);

		/*
		 * il delete lo faccio dopo se no non lo trovo!
		 */
		if($Article->syncronizeArticlesOrder($tmp_user, $article_id, 'DELETE', $debug)) {
			$msg_esito = "Articolo non sincronizzato con gli articoli degli ordini associati!";

			if(!$debug) 
				return $msg_esito;				
			else
				die($msg_esito);			
		}
		
		if(!$Article->delete($organization_id, $article_id)) {
			$msg_esito = "Articolo non cancellato!";

			if(!$debug) 
				return $msg_esito;				
			else
				die($msg_esito);			
		}
					
						
		/*
		 * IMG1 delete
		*/
		if(!empty($results['Article']['img1'])) {
			$img_path = Configure::read('App.root').Configure::read('App.img.upload.article').DS.$organization_id.DS;
			$file1 = new File($img_path.$results['Article']['img1'], false, 0777);
		
			if($debug) {
				echo "<pre>Article.img1 \n ";
				print_r($file1);
				echo "</pre>";
			}
			
			if(!$file1->delete()) {
				$msg_esito = "Immagine dell'articolo non eliminata";

				if(!$debug) 
					return $msg_esito;				
				else
					die($msg_esito);			
			} 
		} // if(!empty($results['Article']['img1']))
		
		if($debug)
			exit;
		
		return true;
	} 
	
	/*
	 * articolo del produttore non + presente nel suo archivio => e' acquistato e non posso DELETE Article => Article.flag_presente_articlesorder = 'N'
	 */
	public function syncronize_flag_presente_articlesorders($user, $organization_id, $article_id, $debug=false) {

		$msg_esito = "";
		
        if(empty($organization_id) || empty($article_id)) {
            return false;
        }

		$tmp_user->organization['Organization']['id'] =  $organization_id; 
		
		App::import('Model', 'Article');
		$Article = new Article;
					
		/*
		 * Article prima del salvataggio
		*/
		$options = array();
		$options['conditions'] = array('Article.organization_id' => $organization_id,
									  'Article.id' => $article_id);
		$options['recursive'] = -1;
		$results = $Article->find('first', $options);
				
		$results['Article']['flag_presente_articlesorders'] = 'N';
		
		$Article->set($results);
		if(!$Article->validates()) {
			$errors = $Article->validationErrors;
			
			foreach($errors as $key => $value) 
				foreach($value as $key2 => $msg) 
					$msg_esito .= $msg.'<br />';
				
			if(!$debug) 
				return $msg_esito;				
			else
				die($msg_esito);
		}
		
		if($debug) {
			echo "<pre>Article SAVE to UPDATE flag_presente_articlesorders \n";
			print_r($results);
			echo "</pre>";			
		}
		
		$Article->create();
		if(!$Article->save($results)) {
			$msg_esito = "Articolo del produttore con ID $prod_gas_article_id non salvato!";
			
			if(!$debug) 
				return $msg_esito;				
			else
				die($msg_esito);
		}
				
		if($debug)
			exit;
		
		return true;
		
		
		if($debug)
			exit;
		
		return true;
	} 
	
	public function import_article_gas($user, $organization_id, $prod_gas_article_id, $article_id, $debug=false) {

		$msg_esito = "";
		
        if(empty($organization_id) || empty($prod_gas_article_id) || empty($article_id)) {
            return false;
        }

		$sql = "UPDATE 
					".Configure::read('DB.prefix')."articles
				SET
					prod_gas_article_id = ".$prod_gas_article_id.",
					supplier_id = ".$user->supplier['Supplier']['id']." 
				WHERE
				    organization_id = ".(int)$organization_id."
					and id = ".$article_id;
		if($debug) echo '<br />'.$sql;
		try {
			$results = $this->query($sql);
		}
		catch (Exception $e) {
			CakeLog::write('error',$sql);
			CakeLog::write('error',$e);
			if($debug) echo '<br />'.$e;
		}

		if($debug)
			exit;
		
		return true;
	} 

	
	private function __getMaxIdOrganizationId($organization_id) {
    	
    	$maxId = 1;
    	
		App::import('Model', 'Article');
		$Article = new Article;
		
		$options = array();
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

				if(isset($val['ProdGasArticlesSyncronize']['prezzo'])) {
					$results[$key]['ProdGasArticlesSyncronize']['prezzo_'] = number_format($val['ProdGasArticlesSyncronize']['prezzo'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
					$results[$key]['ProdGasArticlesSyncronize']['prezzo_e'] = $results[$key]['ProdGasArticlesSyncronize']['prezzo_'].' &euro;';
				}
				else
					/*					 * se il find() arriva da $hasAndBelongsToMany					*/
				 if(isset($val['prezzo'])) {					$results[$key]['prezzo_'] = number_format($val['prezzo'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));					$results[$key]['prezzo_e'] = $results[$key]['prezzo_'].' &euro;';				}				
				/*
				 * qta, da 1.00 a 1
				 * 		da 0.75 a 0,75  
				 * */
				if(isset($val['ProdGasArticlesSyncronize']['qta'])) {
					$qta = str_replace(".", ",", $val['ProdGasArticlesSyncronize']['qta']);
					$arrCtrlTwoZero = explode(",",$qta);
					if($arrCtrlTwoZero[1]=='00') $qta = $arrCtrlTwoZero[0];
					$results[$key]['ProdGasArticlesSyncronize']['qta_'] = $qta;
				}
				else
				/*				 * se il find() arriva da $hasAndBelongsToMany				*/	
				if(isset($val['qta'])) {					$qta = str_replace(".", ",", $val['qta']);					$arrCtrlTwoZero = explode(",",$qta);					if($arrCtrlTwoZero[1]=='00') $qta = $arrCtrlTwoZero[0];					$results[$key]['qta_'] = $qta;				}
			}
		}
		
		return $results;
	}	
}