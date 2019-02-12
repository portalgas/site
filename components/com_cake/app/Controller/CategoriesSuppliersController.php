<?php
App::uses('AppController', 'Controller');
jimport( 'joomla.application.categories' );	
/*
 * Categorie utilizzate da $this->user->organization['Organization']['hasFieldSupplierCategoryId'] = Y
 */
class CategoriesSuppliersController extends AppController {

	private $jCategories = [];
	
	public $name = 'CategoriesSuppliers';

	public function beforeFilter() {
		 parent::beforeFilter();
		 		 
		 /* ctrl ACL */
		 if(!$this->isRoot() && !$this->isRootSupplier()) {
			$this->Session->setFlash(__('msg_not_permission'));
			$this->myRedirect(Configure::read('routes_msg_stop'));
		 }
		 /* ctrl ACL */
		 		
	}

    public function admin_index() {        
    	
    	$totSuppliers = 0;  	
    	$results = [];
    	$resultsAdd = [];
    	$resultsTotSupplier = [];
        
    	$results = $this->CategoriesSupplier->generateTreeList(null, null, null, '&nbsp;&nbsp;&nbsp;');    	
    	foreach ($results as $key => $value) {
        	
        	/*
        	 * ottengo gli eventuali corrispettivi su j_categories
        	 */
        	$sql = "SELECT 
        				CategoriesSupplier.j_category_id, JCategory.id, JCategory.title
        			FROM 
        				".Configure::read('DB.prefix')."categories_suppliers CategoriesSupplier, 
        				".Configure::read('DB.portalPrefix')."categories JCategory   
        			WHERE 
        				CategoriesSupplier.j_category_id = JCategory.id 
        				AND CategoriesSupplier.id = ".$key;
        	self::d($sql, false);
        	try {        		$jResults = $this->CategoriesSupplier->query($sql);
        		if(!empty($jResults)) {        			$jResults = current($jResults);        			$resultsAdd[$key]['j_id'] = $jResults['JCategory']['id'];        			$resultsAdd[$key]['j_title'] = $jResults['JCategory']['title'];        		}        		else {        			$resultsAdd[$key]['j_id'] = "";        			$resultsAdd[$key]['j_title'] = "";        		}	        	}        	catch (Exception $e) {        		CakeLog::write('error',$sql);        		CakeLog::write('error',$e);        	}
        	/*        	 * ottengo il totale dei produttori associati        	*/        	
        	$sql = "SELECT        				count(Supplier.id) as totSupplier         			FROM        				".Configure::read('DB.prefix')."categories_suppliers CategoriesSupplier,        				".Configure::read('DB.prefix')."suppliers Supplier         			WHERE        				Supplier.category_supplier_id = CategoriesSupplier.id           				AND CategoriesSupplier.id = ".$key;        	self::d($sql, false);
        	try {        		$totResults = $this->CategoriesSupplier->query($sql);
        		if(!empty($totResults)) {        			$totResults = current($totResults);        			$resultsTotSupplier[$key]['totSupplier'] = $totResults[0]['totSupplier'];        		}        		else {        			$resultsTotSupplier[$key]['totSupplier'] = 0;        		}        		        	}        	catch (Exception $e) {        		CakeLog::write('error',$sql);        		CakeLog::write('error',$e);        	}        	 
        }        
        /*         * ottengo il totale dei produttori        */        $sql = "SELECT        			count(Supplier.id) as totSupplier         		FROM        			".Configure::read('DB.prefix')."suppliers Supplier";        self::d($sql, false);
        try {			$totResults = $this->CategoriesSupplier->query($sql);
	        if(!empty($totResults)) {
	        	$totResults = current($totResults);
	        	$totSuppliers = $totResults[0]['totSupplier'];
	        }
	        else
	        	$totSuppliers = 0;        }        catch (Exception $e) {        	CakeLog::write('error',$sql);        	CakeLog::write('error',$e);        }                            $this->set('totSuppliers', $totSuppliers);
		$this->set('results', $results);
		$this->set('resultsAdd', $resultsAdd);
		$this->set('resultsTotSupplier', $resultsTotSupplier);
    }

	public function admin_view($id = null) {		
		$results = $this->CategoriesSupplier->read(0, null, $id);
		if (empty($results)) {			$this->Session->setFlash(__('msg_error_params'));			$this->myRedirect(Configure::read('routes_msg_exclamation'));		}		
		$this->set('category', $results);
	}

	public function admin_add() {
		if ($this->request->is('post') || $this->request->is('put')) {
			
			$this->CategoriesSupplier->create();
			if ($this->CategoriesSupplier->save($this->request->data)) {
				$this->Session->setFlash(__('The category has been saved'));
				$this->myRedirect(['action' => 'index']);
			} else {
				$this->Session->setFlash(__('The category could not be saved. Please, try again.'));
			}
		}
		$parents = $this->CategoriesSupplier->generateTreeList(null, null, null, '&nbsp;&nbsp;&nbsp;');
		$this->set(compact('parents'));
		
		/* parametri per joomla */		$j_categories = $this->_getJCategoryItems(Configure::read('JCategoryIdRoot'));		$this->set(compact('j_categories'));
	}

	public function admin_edit($id = null) {
		
		$this->CategoriesSupplier->id = $id;		if (!$this->CategoriesSupplier->exists()) {			$this->Session->setFlash(__('msg_error_params'));			$this->myRedirect(Configure::read('routes_msg_exclamation'));		}
				
		if ($this->request->is('post') || $this->request->is('put')) {
			
			$this->CategoriesSupplier->create();
			if ($this->CategoriesSupplier->save($this->request->data)) {
				$this->Session->setFlash(__('The category has been saved'));
				$this->myRedirect(['action' => 'index']);
			} else {
				$this->Session->setFlash(__('The category could not be saved. Please, try again.'));
			}
		} else {
			$this->request->data = $this->CategoriesSupplier->read(0, null, $id);
			if (empty($this->request->data)) {				$this->Session->setFlash(__('msg_error_params'));				$this->myRedirect(Configure::read('routes_msg_exclamation'));			}
		}	
		$parents = $this->CategoriesSupplier->generateTreeList(null, null, null, '&nbsp;&nbsp;&nbsp;');
		$this->set(compact('parents'));
		
		/* parametri per joomla */		$j_categories = $this->_getJCategoryItems(Configure::read('JCategoryIdRoot'));		$this->set(compact('j_categories'));
	}

	/*
	 * categories_Trigger
	 * 		update suppliers a zero
	 * */
	public function admin_delete($id=0) {
	
		$this->CategoriesSupplier->id = $id;		if (!$this->CategoriesSupplier->exists()) {			$this->Session->setFlash(__('msg_error_params'));			$this->myRedirect(Configure::read('routes_msg_exclamation'));		}				if ($this->request->is('post') || $this->request->is('put')) {
			$id = $this->request->data['CategorySupplier']['id'];
			
			if ($this->CategoriesSupplier->delete())
				$this->Session->setFlash(__('Delete Category'));
			else
				$this->Session->setFlash(__('Category was not deleted'));
			$this->myRedirect(['action' => 'index']);
		}
	
		$options = [];
		$options['conditions'] = array('CategoriesSupplier.id' => $id);		$options['recursive'] = 1;		$results = $this->CategoriesSupplier->find('first', $options);		$this->set(compact('results'));
	}	

	private function _getJCategoryItems($j_category_id=0, $recursive = false)	{		$categories = JCategories::getInstance('Content');		$this->_parent = $categories->get($j_category_id);		if(is_object($this->_parent))		{			$this->_items = $this->_parent->getChildren($recursive);		}		else		{			$this->_items = false;		}			return $this->_loadJCats($this->_items);	}	
	
	private function _loadJCats($cats = [])	{		if(is_array($cats))		{			$return = [];			foreach($cats as $JCatNode)			{				$this->jCategories[$JCatNode->id] = str_repeat('-',$JCatNode->level).$JCatNode->title;				if($JCatNode->hasChildren()) {					$JCatNodeChild = $this->_loadJCats($JCatNode->getChildren());					if(!empty($JCatNodeChild->id))						$this->jCategories[$JCatNodeChild->id] = str_repeat('-',$JCatNodeChild->level).$JCatNodeChild->title;				}			}				return $this->jCategories;		}			return false;	}		private function _loadJCatsDisabled($cats = [])	{		if(is_array($cats))		{			$i = 0;			$return = [];			foreach($cats as $JCatNode)			{				$return[$i]->id    = $JCatNode->id;				$return[$i]->title = $JCatNode->title;				$return[$i]->level = $JCatNode->level;				if($JCatNode->hasChildren())					$return[$i]->children = $this->_loadJCatsDisabled($JCatNode->getChildren());				else					$return[$i]->children = false;					$i++;			}				return $return;		}			return false;	}	
}