<?php
App::uses('AppModel', 'Model');
/**
 * Doc Model
 *
 * @property Order $Order
 */
class Doc extends AppModel {

	public $useTable = 'deliveries';
	public $actsAs = array('Data');
	public $virtualFields = array('luogoData' => "CONCAT_WS(' - ',Doc.luogo,DATE_FORMAT(Doc.data, '%W, %e %M %Y'))");
	
	/**
	 * hasMany associations
	 *
	 * @var array
	 */
	public $hasMany = array(
			'Order' => array(
					'className' => 'Order',
					'foreignKey' => 'delivery_id',
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