<?php
App::uses('AppModel', 'Model');

/**
 * DROP TRIGGER IF EXISTS `k_categories_articles_Trigger`;
 * DELIMITER |
 * CREATE TRIGGER `k_categories_articles_Trigger` AFTER DELETE ON `k_categories_articles`
 *  FOR EACH ROW BEGIN 
 * update k_articles set category_article_id = 0 where category_article_id = old.id and organization_id = organization_id; 
 * END
 * |
 * DELIMITER ;
 */

class CategoriesArticle extends AppModel {

    public $name = 'CategoriesArticle';
    public $actsAs = array('Tree');

	//The Associations below have been created with all possible keys, those that are not needed can be removed

/**
 * belongsTo associations
 *
 * @var array
 */
	public $belongsTo = array(
		'Parent' => array(
			'className' => 'CategoriesArticle',
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
			'className' => 'CategoriesArticle',
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