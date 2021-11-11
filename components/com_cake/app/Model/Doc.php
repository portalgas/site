<?php
App::uses('AppModel', 'Model');

class Doc extends AppModel {

	public $useTable = 'deliveries';
	public $actsAs = ['Data'];
	public $virtualFields = ['luogoData' => "CONCAT_WS(' - ',Doc.luogo,DATE_FORMAT(Doc.data, '%W, %e %M %Y'))"];
	
	/**
	 * hasMany associations
	 *
	 * @var array
	 */
	public $hasMany = [
			'Order' => [
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
			]
	];
}