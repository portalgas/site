<?php

App::uses('AppController', 'Controller');

class DesUserGroupMapsController extends AppController {

    public function beforeFilter() {
        parent::beforeFilter();

        if ($this->user->organization['Organization']['hasDes'] == 'N') {
            $this->Session->setFlash(__('msg_not_organization_config'));
            $this->myRedirect(Configure::read('routes_msg_stop'));
        }

		if(empty($this->user->des_id)) {
            $this->Session->setFlash(__('Devi scegliere il tuo DES'));
			$url = Configure::read('App.server').'/administrator/index.php?option=com_cake&controller=Des&action=index';
			$this->myRedirect($url);
        }
		
        /* ctrl ACL */
        if (!$this->isManagerDes()) {
            $this->Session->setFlash(__('msg_not_permission'));
            $this->myRedirect(Configure::read('routes_msg_stop'));
        }
        /* ctrl ACL */

        /*
         * elenco di tutti i gruppi dell'organization UserGroupsComponent escludendo i DES
         */
        foreach ($this->userGroups as $group_id => $data) {
            if ($data['type'] == 'GAS')
                unset($this->userGroups[$group_id]);
        }

        if (!isset($this->user->organization['Organization']['hasDesReferentAllGas']) || $this->user->organization['Organization']['hasDesReferentAllGas'] == 'N') {
            unset($this->userGroups[Configure::read('group_id_des_supplier_all_gas')]);
	}
                
        $this->set('userGroups', $this->userGroups);
    }

    public function admin_intro() {

        $debug = false;

        $this->set('isManager', $this->isManager());

        /*
         * totale utenti associati ad un ruolo
         */
        foreach ($this->userGroups as $group_id => $data) {

            if ($data['join'] == 'DesSupplier') {
                /*
                 * group_id_titolare_des_supplier / group_id_des_supplier_all_gas / group_id_referent_des 
                 * faccio join con DesSuppliersReferent
                 */
                $sql = "SELECT count(User.id) as tot
						FROM
							" . Configure::read('DB.portalPrefix') . "user_usergroup_map m,
							" . Configure::read('DB.portalPrefix') . "usergroups g,
							" . Configure::read('DB.portalPrefix') . "users User,
							" . Configure::read('DB.prefix') . "des_organizations DesOrganizations, 
							" . Configure::read('DB.prefix') . "des_suppliers_referents DesSuppliersReferent 
						WHERE
							m.user_id = User.id
							and m.group_id = g.id
							and m.group_id = $group_id
							and User.block = 0
							and DesSuppliersReferent.organization_id = User.organization_id
							and DesSuppliersReferent.user_id = User.id
							and DesSuppliersReferent.group_id = m.group_id
							and DesSuppliersReferent.des_id = DesOrganizations.des_id 
							and DesOrganizations.organization_id = User.organization_id
							and DesOrganizations.des_id = " . $this->user->des_id;
            } else {
                /*
                 * group_id_manager_des / group_id_super_referent_des
                 */
                $sql = "SELECT count(User.id) as tot
						FROM
							" . Configure::read('DB.portalPrefix') . "user_usergroup_map m,
							" . Configure::read('DB.portalPrefix') . "usergroups g,
							" . Configure::read('DB.portalPrefix') . "users User,
							" . Configure::read('DB.prefix') . "des_organizations DesOrganizations  
						WHERE
							m.user_id = User.id
							and m.group_id = g.id
							and m.group_id = $group_id
							and User.block = 0
							and DesOrganizations.organization_id = User.organization_id
							and DesOrganizations.des_id = " . $this->user->des_id;
            }

            /*
             * utenti del gruppo di tutto il DES
             */
            if ($debug)
                echo '<br />' . $sql;
            try {
                $results = current($this->DesUserGroupMap->query($sql));
                $this->userGroups[$group_id]['tot_users_all_des'] = $results[0]['tot'];
            } catch (Exception $e) {
                CakeLog::write('error', $sql);
                CakeLog::write('error', $e);
            }

            /*
             * utenti del gruppo del proprio GAS
             */
            $sql .= " and User.organization_id = " . (int) $this->user->organization['Organization']['id'];
            if ($debug)
                echo '<br />' . $sql;
            try {
                $results = current($this->DesUserGroupMap->query($sql));
                $this->userGroups[$group_id]['tot_users'] = $results[0]['tot'];
            } catch (Exception $e) {
                CakeLog::write('error', $sql);
                CakeLog::write('error', $e);
            }
        }

        $this->set('userGroups', $this->userGroups);
    }

    /*
     * elenco di tutti gli users associati al ruolo
     */

    public function admin_index($group_id) {

        if (empty($group_id)) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }

        App::import('Model', 'User');
        $User = new User;

        $conditions = array('UserGroup.id' => $group_id);
        $results = $User->getUsers($this->user, $conditions);

        $this->set('results', $results);
        $this->set('group_id', $group_id);
    }

    /*
     * aggiungo un utente al gruppo passato (UserGroups)
     */

    public function admin_edit($group_id = null) {

        $debug = false;

        App::import('Model', 'User');
        $User = new User;

        if ($this->request->is('post') || $this->request->is('put')) {

            $group_id = $this->request->data['UserGroupMap']['group_id'];
            if ($debug)
                echo '<br />group_id ' . $group_id;

            $user_id = $this->request->data['UserGroupMap']['users'];
            if ($debug)
                echo '<br />user_id ' . $user_id;

            if (!empty($user_id)) {
                /*
                 * aggiungo gruppo joomla gasUserGroupMap se non ci appartiene gia'
                 */
                App::import('Model', 'User');
                $User = new User;

                if ($debug)
                    echo '<br />group_id ' . $group_id;
                $User->joomlaBatchUser($group_id, $user_id, 'add', $debug);

                $this->Session->setFlash(__('The UserGroups has been saved') . ' ' . $this->userGroups[$group_id]['name']);
            } else
                $this->Session->setFlash(__('The UserGroup could not be saved. Please, try again.') . ' ' . $this->userGroups[$group_id]['name']);

            if (!$debug)
                $this->myRedirect(Configure::read('App.server') . '/administrator/index.php?option=com_cake&controller=DesUserGroupMaps&action=index&group_id=' . $group_id);
        }  // end if ($this->request->is('post') || $this->request->is('put'))

        /*
         * ids dei utenti gia' associti per escluderli dalla lista degli utenti
         */
        $conditions = array('UserGroup.id' => $group_id);
        $usersUserGroups = $User->getUsers($this->user, $conditions);

        $user_ids = '';
        foreach ($usersUserGroups as $usersUserGroups) {
            $user_ids = $user_ids . $usersUserGroups['User']['id'] . ',';
        }

        if (!empty($user_ids)) {
            $user_ids = substr($user_ids, 0, (strlen($user_ids) - 1));

            $conditions = array('UserGroupMap.group_id' => Configure::read('group_id_user'),
                'UserGroupMap.user_id NOT IN' => '(' . $user_ids . ')');
        } else
            $conditions = array('UserGroupMap.group_id' => Configure::read('group_id_user'));

        $users = $User->getUsersList($this->user, $conditions);
        $this->set(compact('users'));

        $this->set('group_id', $group_id);
    }

    /*
     * cancello un utente dal ruolo
     */

    public function admin_delete($user_id, $group_id) {

        if (!empty($user_id)) {

            /*
             * aggiungo gruppo joomla se non ci appartiene gia'
             */
            App::import('Model', 'User');
            $User = new User;

            $User->joomlaBatchUser($group_id, $user_id, 'del');

            $this->Session->setFlash(__('Delete UserGroup') . ' ' . $this->userGroups[$group_id][$name]);
        } else
            $this->Session->setFlash(__('UserGroup was not deleted'));

        $this->myRedirect(Configure::read('App.server') . '/administrator/index.php?option=com_cake&controller=DesUserGroupMaps&action=index&group_id=' . $group_id);
    }

    /*
     *  ctrl se uno user e' referente-des e non referent
     */

    public function admin_ctrl_roles_assigned() {

        // $debug = true;

        $roles = [Configure::read('group_id_super_referent_des'),
            Configure::read('group_id_referent_des'),
            Configure::read('group_id_titolare_des_supplier'),
            Configure::read('group_id_des_supplier_all_gas')
        ];

        /*
         * estraggo i PRODUTTORI
         */
        App::import('Model', 'DesSupplier');
        $DesSupplier = new DesSupplier;

        $options = array();
        $options['recursive'] = -1;
        $options['conditions'] = array('DesSupplier.des_id' => $this->user->des_id);
        $ACLsuppliersIdsDes = $this->user->get('ACLsuppliersIdsDes');
        if (empty($ACLsuppliersIdsDes))
            $options['conditions'] += array('DesSupplier.id IN (0)');
        else
            $options['conditions'] += array('DesSupplier.id IN (' . $this->user->get('ACLsuppliersIdsDes') . ')');
        $options['recursive'] = 1;

        $results = $DesSupplier->find('all', $options);

        if ($debug) {
            echo "<pre>UsersGroupMapsController::ctrl_roles_assigned \r ";
            print_r($options);
            print_r($results);
            echo "</pre>";
        }

        $this->set('results', $results);
    }

    public function admin_ctrl_roles_assigned_details_users($des_supplier_id) {
        $debug = false;

        $roles = [Configure::read('group_id_super_referent_des'),
            Configure::read('group_id_referent_des'),
            Configure::read('group_id_titolare_des_supplier'),
            Configure::read('group_id_des_supplier_all_gas')
        ];

        App::import('Model', 'DesSuppliersReferent');
        App::import('Model', 'DesSupplier');
        App::import('Model', 'SuppliersOrganization');
        App::import('Model', 'SuppliersOrganizationsReferent');
        App::import('Model', 'UserGroupMap');

        $DesSuppliersReferent = new DesSuppliersReferent;
        $results = $DesSuppliersReferent->getUsersRoles($this->user, $this->user->organization['Organization']['id'], $roles, $des_supplier_id);

        /*
         * per ogni user ctrl se e' referente o super-referente del produttore
         */
        if (!empty($results)) {

            /*
             * ricavo il produttore del gas SuppliersOrganization.id per poi verificare i referenti (SuppliersOrganizationsReferent)
             */
            $options = array();
            $options['conditions'] = array('DesSupplier.des_id' => $this->user->des_id,
                'DesSupplier.id' => $des_supplier_id);
            $options['fields'] = array('DesSupplier.supplier_id');
            $options['recursive'] = -1;
            $DesSupplier = new DesSupplier;
            $desSupplierResults = $DesSupplier->find('first', $options);

            $supplier_id = $desSupplierResults['DesSupplier']['supplier_id'];

            $options = array();
            $options['conditions'] = array('SuppliersOrganization.organization_id' => $this->user->organization['Organization']['id'],
                'SuppliersOrganization.supplier_id' => $supplier_id);
            $options['fields'] = array('SuppliersOrganization.id');
            $options['recursive'] = -1;
            $SuppliersOrganization = new SuppliersOrganization;
            $supplierOrganizationResults = $SuppliersOrganization->find('first', $options);

            $supplier_organization_id = $supplierOrganizationResults['SuppliersOrganization']['id'];

            foreach ($results as $user_id => $value) {

                /*
                 * group_id_referent
                 */
                $options = array();
                $options['conditions'] = array('SuppliersOrganizationsReferent.organization_id' => $this->user->organization['Organization']['id'],
                    'SuppliersOrganizationsReferent.supplier_organization_id' => $supplier_organization_id,
                    'SuppliersOrganizationsReferent.user_id' => $user_id,
                    'SuppliersOrganizationsReferent.group_id' => Configure::read('group_id_referent'));
                $options['recursive'] = -1;
                $SuppliersOrganizationsReferent = new SuppliersOrganizationsReferent;
                $suppliersOrganizationsReferentResults = $SuppliersOrganizationsReferent->find('first', $options);

                if ($debug) {
                    echo "<pre>UsersGroupMapsController::ctrl_roles_assigned_details_users group_id_referent \r ";
                    print_r($options);
                    print_r($suppliersOrganizationsReferentResults);
                    echo "</pre>";
                }

                if (!empty($suppliersOrganizationsReferentResults))
                    $results[$user_id]['User']['GroupOrder'] = array(Configure::read('group_id_referent'));


                /*
                 * group_id_super_referent
                 */
                $options = array();
                $options['conditions'] = array('UserGroupMap.user_id' => $user_id,
                    'UserGroupMap.group_id' => Configure::read('group_id_super_referent'));
                $options['recursive'] = -1;
                $UserGroupMap = new UserGroupMap;
                $UserGroupMapResults = $UserGroupMap->find('first', $options);

                if ($debug) {
                    echo "<pre>UsersGroupMapsController::ctrl_roles_assigned_details_users group_id_super_referent \r ";
                    print_r($options);
                    print_r($UserGroupMapResults);
                    echo "</pre>";
                }

                if (!empty($UserGroupMapResults)) {
                    if (isset($results[$user_id]['GroupOrder']))
                        array_push($results[$user_id]['User']['GroupOrder'], Configure::read('group_id_super_referent'));
                    else
                        $results[$user_id]['User']['GroupOrder'] = array(Configure::read('group_id_super_referent'));
                }
            } // loop users 
        }  // end if(!empty($results))

        if ($debug) {
            echo "<pre>UsersGroupMapsController::ctrl_roles_assigned_details_users \r ";
            print_r($results);
            echo "</pre>";
        }
        $this->set('results', $results);

        $this->set('userGroups', $this->userGroups);

        $this->layout = 'ajax';
    }

}
