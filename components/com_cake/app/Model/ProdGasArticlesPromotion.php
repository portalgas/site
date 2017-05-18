<?php
App::uses('AppModel', 'Model');

class ProdGasArticlesPromotion extends AppModel {

   public $name = 'ProdGasArticlesPromotion';
   
	public $belongsTo = array(
		'ProdGasArticle' => array(
			'className' => 'ProdGasArticle',
			'foreignKey' => 'prod_gas_article_id',
			'conditions' => 'ProdGasArticle.supplier_id = ProdGasArticlesPromotion.supplier_id',
			'fields' => '',
			'order' => ''
		),
		'ProdGasPromotion' => array(
			'className' => 'ProdGasPromotion',
			'foreignKey' => 'prod_gas_promotion_id',
			'conditions' => 'ProdGasPromotion.supplier_id = ProdGasArticlesPromotion.supplier_id',
			'fields' => '',
			'order' => ''
		)
	);

	public function afterFind($results, $primary = true) {
		
		foreach ($results as $key => $val) {
			if(!empty($val)) {
				if (isset($val['ProdGasArticlesPromotion']['importo'])) {
					$results[$key]['ProdGasArticlesPromotion']['importo_'] = number_format($val['ProdGasArticlesPromotion']['importo'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
					$results[$key]['ProdGasArticlesPromotion']['importo_e'] = $results[$key]['ProdGasArticlesPromotion']['importo_'].' &euro;';
				}
				else
				/*
				 * se il find() arriva da $hasAndBelongsToMany
				*/
				if(isset($val['importo'])) {
					$results[$key]['importo_'] = number_format($val['importo'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
					$results[$key]['importo_e'] = $results[$key]['importo_'].' &euro;';
				}	

				if (isset($val['ProdGasArticlesPromotion']['prezzo_unita'])) {
					$results[$key]['ProdGasArticlesPromotion']['prezzo_unita_'] = number_format($val['ProdGasArticlesPromotion']['prezzo_unita'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
					$results[$key]['ProdGasArticlesPromotion']['prezzo_unita_e'] = $results[$key]['ProdGasArticlesPromotion']['prezzo_unita_'].' &euro;';
				}
				else
				/*
				 * se il find() arriva da $hasAndBelongsToMany
				*/
				if(isset($val['prezzo_unita'])) {
					$results[$key]['prezzo_unita_'] = number_format($val['prezzo_unita'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
					$results[$key]['prezzo_unita_e'] = $results[$key]['prezzo_unita_'].' &euro;';
				}				
			}
		}
		return $results;
	}
			
	public function beforeSave($options = array()) {
		if (!empty($this->data['ProdGasArticlesPromotion']['importo']))
			$this->data['ProdGasArticlesPromotion']['importo'] = $this->importoToDatabase($this->data['ProdGasArticlesPromotion']['importo']);

		if (!empty($this->data['ProdGasArticlesPromotion']['prezzo_unita']))
			$this->data['ProdGasArticlesPromotion']['prezzo_unita'] = $this->importoToDatabase($this->data['ProdGasArticlesPromotion']['prezzo_unita']);

	    return true;
	}
	
}