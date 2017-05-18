<?php
App::uses('AppModel', 'Model');

/**
 * DROP TRIGGER IF EXISTS `k_categories_suppliers_Trigger`;
 * DELIMITER |
 * CREATE TRIGGER `k_categories_suppliers_Trigger` AFTER DELETE ON `k_categories_suppliers`
 * FOR EACH ROW BEGIN 
 * update k_suppliers set category_supplier_id = 0 where category_supplier_id = old.id; 
 * update k_suppliers_organizations set category_supplier_id = 0 where category_supplier_id = old.id; 
 * END
 * |
 * DELIMITER ;
 */

class CategoriesSupplier extends AppModel {

    public $name = 'CategoriesSupplier';
    public $actsAs = array('Tree');

	//The Associations below have been created with all possible keys, those that are not needed can be removed

/**
 * belongsTo associations
 *
 * @var array
 */
	public $belongsTo = array(
		'Parent' => array(
			'className' => 'CategoriesSupplier',
			'foreignKey' => 'parent_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		)
	);

/**
 * hasMany associations
 *
 * @var array
 */
	public $hasMany = array(
		'ChildCategory' => array(
			'className' => 'CategoriesSupplier',
			'foreignKey' => 'parent_id',
			'dependent' => false,
			'conditions' => '',
			'fields' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'exclusive' => '',
			'finderQuery' => '',
			'counterQuery' => ''
		)
	);
}