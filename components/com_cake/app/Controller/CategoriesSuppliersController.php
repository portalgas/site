<?php
App::uses('AppController', 'Controller');
jimport( 'joomla.application.categories' );
/*
 * Categorie utilizzate da $this->user->organization['Organization']['hasFieldSupplierCategoryId'] = Y
 */
class CategoriesSuppliersController extends AppController {

	private $jCategories = Array();
	
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
    	$results = array();
    	$resultsAdd = array();
    	$resultsTotSupplier = array();
        
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
        	// echo '<br />'.$sql;
        	try {
        		if(!empty($jResults)) {

        	$sql = "SELECT
        	try {
        		if(!empty($totResults)) {
        }
        /*
        try {
	        if(!empty($totResults)) {
	        	$totResults = current($totResults);
	        	$totSuppliers = $totResults[0]['totSupplier'];
	        }
	        else
	        	$totSuppliers = 0;
		$this->set('results', $results);
		$this->set('resultsAdd', $resultsAdd);
		$this->set('resultsTotSupplier', $resultsTotSupplier);
    }

	public function admin_view($id = null) {		
		$results = $this->CategoriesSupplier->read(0, null, $id);
		if (empty($results)) {
		$this->set('category', $results);
	}

	public function admin_add() {
		if ($this->request->is('post') || $this->request->is('put')) {
			
			$this->CategoriesSupplier->create();
			if ($this->CategoriesSupplier->save($this->request->data)) {
				$this->Session->setFlash(__('The category has been saved'));
				$this->myRedirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The category could not be saved. Please, try again.'));
			}
		}
		$parents = $this->CategoriesSupplier->generateTreeList(null, null, null, '&nbsp;&nbsp;&nbsp;');
		$this->set(compact('parents'));
		
		/* parametri per joomla */
	}

	public function admin_edit($id = null) {
		
		$this->CategoriesSupplier->id = $id;
				
		if ($this->request->is('post') || $this->request->is('put')) {
			
			$this->CategoriesSupplier->create();
			if ($this->CategoriesSupplier->save($this->request->data)) {
				$this->Session->setFlash(__('The category has been saved'));
				$this->myRedirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The category could not be saved. Please, try again.'));
			}
		} else {
			$this->request->data = $this->CategoriesSupplier->read(0, null, $id);
			if (empty($this->request->data)) {
		}	
		$parents = $this->CategoriesSupplier->generateTreeList(null, null, null, '&nbsp;&nbsp;&nbsp;');
		$this->set(compact('parents'));
		
		/* parametri per joomla */
	}

	/*
	 * categories_Trigger
	 * 		update suppliers a zero
	 * */
	public function admin_delete($id=0) {
	
		$this->CategoriesSupplier->id = $id;
			$id = $this->request->data['CategorySupplier']['id'];
			
			if ($this->CategoriesSupplier->delete())
				$this->Session->setFlash(__('Delete Category'));
			else
				$this->Session->setFlash(__('Category was not deleted'));
			$this->myRedirect(array('action' => 'index'));
		}
	
		$options = array();
		$options['conditions'] = array('CategoriesSupplier.id' => $id);
	}	

	private function __getJCategoryItems($j_category_id=0, $recursive = false)
	
	private function __loadJCats($cats = array())
}