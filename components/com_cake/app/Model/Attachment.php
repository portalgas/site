<?php
App::uses('AppModel', 'Model');


/**
 * Attachment Model
 * 
 * @category Plugin
 * @package  Attachment.Model
 * @author   Vinícius Krolow <krolow@gmail.com>
 * @license  GNU GENERAL PUBLIC LICENSE
 * @link     https://github.com/krolow/Attach
 */
class Attachment extends AppModel
{
    /**
     * Display field
     *
     * @var string
     */
    public $displayField = 'id';

    /**
     * Validation rules
     *
     * @var array
     */
    public $validate = array(
        'filename' => array(
            'notempty' => array(
                'rule' => ['notBlank'],
                'message' => 'Filename cannot be empty'
            ),
        ),
        'model' => array(
            'notempty' => array(
                'rule' => ['notBlank']
            ),
        ),
        'foreign_key' => array(
            'numeric' => array(
                'rule' => ['numeric']
            ),
        ),
    );
}

