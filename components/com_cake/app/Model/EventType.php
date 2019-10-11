<?php
App::uses('AppModel', 'Model');

 
class EventType extends AppModel {
	var $name = 'EventType';
	var $displayField = 'name';
	var $validate = array(
		'name' => array(
			'notempty' => array(
				'rule' => ['notBlank'],
			),
		),
	);

	var $hasMany = array(
		'Event' => array(
			'className' => 'Event',
			'foreignKey' => 'event_type_id',
			'dependent' => false,
		)
	);
}
?>