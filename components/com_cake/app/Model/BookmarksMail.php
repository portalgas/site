<?php
App::uses('AppModel', 'Model');


class BookmarksMail extends Model {

         /*
         * $field order_open / order_close
         * $value Y / N
         */
	public function update($user, $supplier_organization_id, $field, $value, $debug = false) {

                 /*
                 * ctrl se esiste gia'
                 */
                $options = [];
		$options['conditions'] = array('BookmarksMail.organization_id' => (int)$user->organization['Organization']['id'],
						'BookmarksMail.user_id' => $user->id,
                                                'BookmarksMail.supplier_organization_id' => $supplier_organization_id);
                $options['recursive'] = -1;
		$bookmarksMailResults = $this->find('first', $options);                
		
                if($debug) {
                    echo "<pre>BookmarksMail::update() - CTRL se INSERT/UPDATE \r ";
                    print_r($options['conditions']);
                    print_r($bookmarksMailResults);
                    echo "</pre>";
                }
                
                $data = [];
                if (!empty($bookmarksMailResults)) {
                    /*
                     * UPDATE
                     */
                    $data['BookmarksMail']['id'] = $bookmarksMailResults['BookmarksMail']['id'];
                    
                    if($field=='order_open')
                        $data['BookmarksMail']['order_close'] = $bookmarksMailResults['BookmarksMail']['order_close'];
                    else
                        $data['BookmarksMail']['order_open'] = $bookmarksMailResults['BookmarksMail']['order_open'];
                }
                else {
                    /*
                     * INSERT
                     */
               
                    if($field=='order_open')
                        $data['BookmarksMail']['order_close'] = 'Y';
                    else
                        $data['BookmarksMail']['order_open'] = 'Y';
                }
                
                $data['BookmarksMail']['organization_id'] = $user->organization['Organization']['id'];
                $data['BookmarksMail']['user_id'] = $user->id;
                $data['BookmarksMail']['supplier_organization_id'] = $supplier_organization_id;
                $data['BookmarksMail'][$field] = $value;

                if($debug) {
                    echo "<pre>BookmarksMail::update() - SAVE \r ";
                    print_r($data);
                    echo "</pre>";
                }
                
                $this->create();
                if($this->save($data))
                    return true;
                else
                    return false;
        }
        
	public $belongsTo = array(
		'User' => array(
			'className' => 'User',
			'foreignKey' => 'user_id',
			'conditions' => 'User.organization_id = BookmarksMail.organization_id',
			'fields' => '',
			'order' => ''
		),
		'SuppliersOrganization' => array(
			'className' => 'SuppliersOrganization',
			'foreignKey' => 'supplier_organization_id',
			'conditions' => 'SuppliersOrganization.organization_id = BookmarksMail.organization_id',
			'fields' => '',
			'order' => ''
		),		
	);	
}