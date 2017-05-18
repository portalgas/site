<?php

App::uses('AppController', 'Controller');
App::import('Vendor', 'ImageTool');

class UsersController extends AppController {

    public function beforeFilter() {
        parent::beforeFilter();
    }

    public function admin_index() {

        $this->set('isManager', $this->isManager());

        $FilterUserUserGroups = null;
        $FilterUserUsername = null;
        $FilterUserName = '';


        /*
         * filtri per gruppo
         */
        $userGroups = array(Configure::read('group_id_user') => __("UserGroupsUser"),
            Configure::read('group_id_manager') => __("UserGroupsManager"),
            Configure::read('group_id_manager_delivery') => __("UserGroupsManagerDelivery"),
            Configure::read('group_id_referent') => __("UserGroupsReferent"),
            Configure::read('group_id_super_referent') => __("UserGroupsSuperReferent"),
            Configure::read('group_id_tesoriere') => __("UserGroupsTesoriere"),
            Configure::read('group_id_generic') => __("UserGroupsGeneric"));

        /*
         * referente cassa (pagamento degli utenti alla consegna)
         */
        if ($this->user->organization['Organization']['payToDelivery'] == 'ON' || $this->user->organization['Organization']['payToDelivery'] == 'ON-POST')
            $userGroups += array(Configure::read('group_id_cassiere') => __("UserGroupsCassiere"));

        $this->set(compact('userGroups'));

        /*
         * conditions
         */
        $conditions = array();
        $conditions[] = array('User.organization_id' => (int) $this->user->organization['Organization']['id'],
            'User.block' => 1);

        /* recupero dati dalla Session gestita in appController::beforeFilter */
        if ($this->Session->check(Configure::read('Filter.prefix') . $this->modelClass . 'Username')) {
            $FilterUserUsername = $this->Session->read(Configure::read('Filter.prefix') . $this->modelClass . 'Username');
            $conditions['User.username'] = "User.username LIKE '%" . $FilterUserUsername . "%'";
        }
        if ($this->Session->check(Configure::read('Filter.prefix') . $this->modelClass . 'Name')) {
            $FilterUserName = $this->Session->read(Configure::read('Filter.prefix') . $this->modelClass . 'Name');
            $conditions['User.name'] = "User.name LIKE '%" . $FilterUserName . "%'";
        }
        if ($this->Session->check(Configure::read('Filter.prefix') . $this->modelClass . 'UserGroups')) {
            $FilterUserUserGroups = $this->Session->read(Configure::read('Filter.prefix') . $this->modelClass . 'UserGroups');
            /*
              echo "<pre>";
              print_r($FilterUserUserGroups);
              echo "</pre>";
             */
            $conditions['UserGroup.group_id'] = $FilterUserUserGroups;
        }


        if (empty($FilterUserUserGroups))
            $FilterUserUserGroups = Configure::read('group_id_user') . ',' .
                    Configure::read('group_id_manager') . ',' .
                    Configure::read('group_id_manager_delivery') . ',' .
                    Configure::read('group_id_referent') . ',' .
                    Configure::read('group_id_super_referent') . ',' .
                    Configure::read('group_id_tesoriere') . ',' .
                    Configure::read('group_id_generic');


        /* filtro */
        $this->set('FilterUserUsername', $FilterUserUsername);
        $this->set('FilterUserName', $FilterUserName);
        $this->set('FilterUserUserGroups', $FilterUserUserGroups);

        $results = $this->User->getUsersComplete($this->user, $conditions, Configure::read('orderUser'), false);
        $this->set('results', $results);
        /*
          echo "<pre>";
          print_r($results);
          echo "</pre>";
         */
    }

    public function admin_index_block() {

        $this->set('isManager', $this->isManager());

        $SqlLimit = 50;

        $conditions = array();
        $conditions[] = array('User.organization_id' => (int) $this->user->organization['Organization']['id'],
            'User.block' => 1);

        $this->User->recursive = 0;
        $this->paginate = array('conditions' => array($conditions), 'order' => Configure::read('orderUser'), 'limit' => $SqlLimit);
        $results = $this->paginate('User');

        /*
         * userprofile
         */
        jimport('joomla.user.helper');
        foreach ($results as $numResult => $result) {

            $userTmp = JFactory::getUser($result['User']['id']);
            $userProfile = JUserHelper::getProfile($userTmp->id);

            $results[$numResult]['Profile'] = $userProfile->profile;
        }
        $this->set('results', $results);
        $this->set('SqlLimit', $SqlLimit);
    }

    public function admin_index_date() {

        $this->set('isManager', $this->isManager());

        $FilterUserUsername = null;
        $FilterUserName = '';
        $FilterUserBlock = 'ALL';
        $FilterUserSort = Configure::read('orderUser');
        
        /*
         * conditions
         */
        $conditions = array();

        /* recupero dati dalla Session gestita in appController::beforeFilter */
        if ($this->Session->check(Configure::read('Filter.prefix') . $this->modelClass . 'Username')) {
            $FilterUserUsername = $this->Session->read(Configure::read('Filter.prefix') . $this->modelClass . 'Username');
            $conditions['User.username'] = "User.username LIKE '%" . $FilterUserUsername . "%'";
        }
        if ($this->Session->check(Configure::read('Filter.prefix') . $this->modelClass . 'Name')) {
            $FilterUserName = $this->Session->read(Configure::read('Filter.prefix') . $this->modelClass . 'Name');
            $conditions['User.name'] = "User.name LIKE '%" . $FilterUserName . "%'";
        }
        if ($this->Session->check(Configure::read('Filter.prefix') . $this->modelClass . 'Block')) {
            $FilterUserBlock = $this->Session->read(Configure::read('Filter.prefix') . $this->modelClass . 'Block');
            if ($FilterUserBlock != 'ALL')
                $conditions['User.block'] = "User.block = $FilterUserBlock";  // 0 attivi / 1 disattivati
            else
                $conditions['User.block'] = "User.block IN ('0','1')";
        }
        else {
            $FilterUserBlock = 'ALL';
            $conditions['User.block'] = "User.block IN ('0','1')"; // di default li prende tutti
        }
   
        if ($this->Session->check(Configure::read('Filter.prefix') . $this->modelClass . 'Sort')) 
            $FilterUserSort = $this->Session->read(Configure::read('Filter.prefix') . $this->modelClass . 'Sort');
        else 
            $FilterUserSort = Configure::read('orderUser');
        
        /* filtro */
        $this->set('FilterUserUsername', $FilterUserUsername);
        $this->set('FilterUserName', $FilterUserName);
        $this->set('FilterUserBlock', $FilterUserBlock);
        $this->set('FilterUserSort', $FilterUserSort);

        $block = array('ALL' => 'Tutti', '0' => 'Attivi', '1' => 'Disattivi');
        $this->set('block', $block);

        $sorts = array(Configure::read('orderUser') => __('Name'), 
                        'User.registerDate' => __('registerDate'), 
                       /* 'Profile.dataRichEnter' => __('dataRichEnter'), 
                        'Profile.dataEnter' => __('dataEnter'), 
                        'Profile.dataRichExit' => __('dataRichExit'), 
                        'Profile.dataExit' => __('dataExit')*/
                        );
        $this->set('sorts', $sorts);

        
                
        /*
          echo "<pre>";
          print_r($conditions);
          echo "</pre>";
         */

        $results = $this->User->getUsersComplete($this->user, $conditions, $FilterUserSort, false);
        $this->set('results', $results);
    }

    public function admin_index_date_update() {

        $debug = false;

        $user_id = $this->request->data['user_id'];
        $field_db = 'profile.' . $this->request->data['field_db'];
        $data_db = $this->request->data['data_db'];

        $sql = 'SELECT * from ' . Configure::read('DB.portalPrefix') . 'user_profiles WHERE user_id = ' . $user_id . ' and profile_key = "' . $field_db . '"';
        if ($debug)
            echo "\n " . $sql;
        $executeSql = $this->User->query($sql);

        if (empty($executeSql))
            $sql = 'INSERT INTO ' . Configure::read('DB.portalPrefix') . 'user_profiles (user_id,profile_key,profile_value) VALUES (' . $user_id . ', "' . $field_db . '", "\"' . $data_db . '\"")';
        else
            $sql = 'UPDATE ' . Configure::read('DB.portalPrefix') . 'user_profiles SET profile_value = "\"' . $data_db . '\"" WHERE user_id = ' . $user_id . ' AND profile_key = "' . $field_db . '"';
        if ($debug)
            echo "\n " . $sql;
        $executeSql = $this->User->query($sql);


        $this->layout = 'ajax';
        $this->render('/Layouts/ajax');
    }

    public function profile() {

        $debug = false;

        $user_id = $this->user->get('id');
        if (empty($user_id)) {
            $this->Session->setFlash(__('msg_not_permission_guest'));
            $this->myRedirect(Configure::read('routes_msg_stop'));
        }

        /*
         * upload img user
         */
        if ($this->request->is('post') || $this->request->is('put')) {

            $msg = '';

            if ($debug) {
                echo "<pre>";
                print_r($this->request->data);
                echo "</pre>";
            }

            /*
             * 	$file1 = array(
             * 		'name' => 'immagine.jpg',
             * 		'type' => 'image/jpeg',
             * 		'tmp_name' => /tmp/phpsNYCIB',
             * 		'error' => 0,
             * 		'size' => 41737,
             * 	);
             *
             * UPLOAD_ERR_OK (0): Non vi sono errori, l’upload e' stato eseguito con successo;
             * UPLOAD_ERR_INI_SIZE (1): Il file inviato eccede le dimensioni specificate nel parametro upload_max_filesize di php.ini;
             * UPLOAD_ERR_FORM_SIZE (2): Il file inviato eccede le dimensioni specificate nel parametro MAX_FILE_SIZE del form;
             * UPLOAD_ERR_PARTIAL (3): Upload eseguito parzialmente;
             * UPLOAD_ERR_NO_FILE (4): Nessun file e' stato inviato;
             * UPLOAD_ERR_NO_TMP_DIR (6): Mancanza della cartella temporanea;
             */
            if (!empty($this->request->data['Document']['file1']['name'])) {

                $file1 = $this->request->data['Document']['file1'];
                if ($file1['error'] == UPLOAD_ERR_OK && is_uploaded_file($file1['tmp_name'])) {

                    $path_upload = Configure::read('App.root') . Configure::read('App.img.upload.user') . DS . $this->user->organization['Organization']['id'] . DS;

                    /*
                     * ctrl exstension / content type
                     */
                    $ext = strtolower(pathinfo($file1['name'], PATHINFO_EXTENSION));

                    $finfo = finfo_open(FILEINFO_MIME_TYPE); // return mime type ala mimetype extension
                    $type = finfo_file($finfo, $file1['tmp_name']);
                    finfo_close($finfo);

                    $arr_extension = array_merge(Configure::read('App.web.pdf.upload.extension'), Configure::read('App.web.img.upload.extension'));
                    $arr_contentType = array_merge(Configure::read('ContentType.pdf'), Configure::read('ContentType.img'));
                    if (!in_array($ext, $arr_extension) || !in_array($type, $arr_contentType)) {
                        $msg = "Estensione .$ext non valida: si possono caricare file con la seguente estensione ";
                        foreach ($arr_extension as $estensione)
                            $msg .= '.' . $estensione . '&nbsp;';

                        if ($debug) {
                            echo "<br />ext " . $ext;
                            echo "<br />type " . $type;
                            echo "<br />msg " . $msg;
                            exit;
                        }
                    }

                    if (empty($msg)) {

                        $fileNewName = $user_id . '.' . $ext;
                        if ($debug) {
                            echo "<br />path_upload " . $path_upload;
                            echo "<br />ext " . $ext;
                            echo "<br />fileNewName " . $fileNewName;
                        }

                        if (!move_uploaded_file($file1['tmp_name'], $path_upload . $fileNewName))
                            $msg = $file1['error'];
                        else {

                            $info = getimagesize($path_upload . $fileNewName);
                            $width = $info[0];
                            $height = $info[1];
                            if ($debug) {
                                echo "<pre>";
                                print_r($info);
                                echo "</pre>";
                            }


                            /*
                             * ridimensiona img
                             */
                            if ($width > Configure::read('App.web.img.upload.width.user')) {
                                $status = ImageTool::resize(array(
                                            'input' => $path_upload . $fileNewName,
                                            'output' => $path_upload . $fileNewName,
                                            'width' => Configure::read('App.web.img.upload.width.user'),
                                            'height' => ''
                                ));

                                if ($debug)
                                    echo "<br />ridimensiono " . $status;
                            }

                            $msg = "Immagine caricata correttamente";
                        }
                    }  // end if(empty($msg))
                }
                else {
                    $msg = $file1['error'];
                }

                if ($msg == UPLOAD_ERR_OK)
                    $msg = "Immagine caricata correttamente";

                if ($debug)
                    echo "<br />msg " . $msg;

                $this->Session->setFlash($msg);
            } // end if(!empty($this->request->data['Document']['file1']['name']))
        } // end if ($this->request->is('post') || $this->request->is('put'))

        $options = array();
        $options['conditions'] = array('User.id' => $user_id,
            'User.organization_id' => (int) $this->user->organization['Organization']['id']);
        $options['recursive'] = -1;

        $this->User->unbindModel(array('hasMany' => array('Cart')));
        $results = $this->User->find('first', $options);

        /*
         * userprofile
         */
        jimport('joomla.user.helper');
        $userProfile = JUserHelper::getProfile($user_id);

        $results['Profile'] = $userProfile->profile;


        /*
         * G R O U P
         */
        $sql = "SELECT
                        `Group`.title
                FROM
                        " . Configure::read('DB.portalPrefix') . "users User,
                        " . Configure::read('DB.portalPrefix') . "user_usergroup_map UserGroup,
                        " . Configure::read('DB.portalPrefix') . "usergroups AS `Group`
                WHERE
                        User.organization_id = " . (int) $this->user->organization['Organization']['id'] . "
                        AND UserGroup.user_id = User.id
                        AND UserGroup.group_id = `Group`.id
                        AND User.id = $user_id 
                        ORDER BY `Group`.title ";
        // echo '<br />'.$sql;
        try {
            $groupResults = $this->User->query($sql);
        } catch (Exception $e) {
            CakeLog::write('error', $sql);
            CakeLog::write('error', $e);
        }

        foreach ($groupResults as $numGroupResult => $groupResult)
            $results['UserGroup'][$numGroupResult]['UserGroup'] = $groupResult['Group'];

        /*
         * R E F E R E N T I
         */
        $sql = "SELECT
                        SuppliersOrganizationsReferent.type,
                        SuppliersOrganization.*, 
                        Supplier.* 
                FROM
                        " . Configure::read('DB.portalPrefix') . "users User,
                        " . Configure::read('DB.prefix') . "suppliers_organizations_referents SuppliersOrganizationsReferent,
                        " . Configure::read('DB.prefix') . "suppliers_organizations SuppliersOrganization,
                        " . Configure::read('DB.prefix') . "suppliers Supplier  
                WHERE
                        User.organization_id = " . (int) $this->user->organization['Organization']['id'] . "
                        AND SuppliersOrganization.organization_id = " . (int) $this->user->organization['Organization']['id'] . "
                        AND SuppliersOrganizationsReferent.organization_id =  " . (int) $this->user->organization['Organization']['id'] . "
                        AND SuppliersOrganizationsReferent.user_id = User.id
                        AND SuppliersOrganizationsReferent.supplier_organization_id = SuppliersOrganization.id
                        AND Supplier.id = SuppliersOrganization.supplier_id
                        AND (Supplier.stato = 'Y' or Supplier.stato = 'T' or Supplier.stato = 'PG') 
                        AND SuppliersOrganization.stato = 'Y' 
                        AND User.id = $user_id 
                ORDER BY SuppliersOrganization.name ";
        // echo '<br />'.$sql;
        try {
            $supplierResults = $this->User->query($sql);
        } catch (Exception $e) {
            CakeLog::write('error', $sql);
            CakeLog::write('error', $e);
        }

        foreach ($supplierResults as $numSupplierResult => $supplierResult) {
            $results['SuppliersOrganization'][$numSupplierResult]['SuppliersOrganization'] = $supplierResult['SuppliersOrganization'];
            $results['SuppliersOrganization'][$numSupplierResult]['Supplier'] = $supplierResult['Supplier'];
            $results['SuppliersOrganization'][$numSupplierResult]['SuppliersOrganizationsReferent'] = $supplierResult['SuppliersOrganizationsReferent'];
        }

        $this->set('results', $results);

        $this->layout = 'default_front_end';
    }

    public function gmaps() {
        $options = array();
        $options['conditions'] = array('User.organization_id' => $this->user->organization['Organization']['id'],
            'User.block' => 0);
        $options['order'] = Configure::read('orderUser');
        $options['recursive'] = -1;
        $results = $this->User->find('all', $options);

        /*
         * userprofile
         */
        jimport('joomla.user.helper');
        $i = 0;
        $newResults = array();
        foreach ($results as $numResult => $result) {

            $userTmp = JFactory::getUser($result['User']['id']);
            $userProfile = JUserHelper::getProfile($userTmp->id);

            /*
             *  se il Cron non trova lat/lng perche' i dati non sono corretti, imposto a 0.0 se no non esegue i successivi
             */
            if (!empty($userProfile->profile['lat']) && $userProfile->profile['lat'] != Configure::read('LatLngNotFound') && !empty($userProfile->profile['lng']) && $userProfile->profile['lng'] != Configure::read('LatLngNotFound')) {

                $newResults[$i] = $result;
                $newResults[$i]['Profile'] = $userProfile->profile;



                /*
                 * R E F E R E N T I
                 */
                $sql = "SELECT
                                SuppliersOrganizationsReferent.type,
                                SuppliersOrganization.name, 
                                Supplier.img1,Supplier.descrizione,Supplier.localita,Supplier.provincia   
                        FROM
                                " . Configure::read('DB.portalPrefix') . "users User,
                                " . Configure::read('DB.prefix') . "suppliers_organizations_referents SuppliersOrganizationsReferent,
                                " . Configure::read('DB.prefix') . "suppliers_organizations SuppliersOrganization,
                                " . Configure::read('DB.prefix') . "suppliers Supplier  
                        WHERE
                                User.organization_id = " . (int) $this->user->organization['Organization']['id'] . "
                                AND SuppliersOrganization.organization_id = " . (int) $this->user->organization['Organization']['id'] . "
                                AND SuppliersOrganizationsReferent.organization_id =  " . (int) $this->user->organization['Organization']['id'] . "
                                AND SuppliersOrganizationsReferent.user_id = User.id
                                AND SuppliersOrganizationsReferent.supplier_organization_id = SuppliersOrganization.id
                                AND Supplier.id = SuppliersOrganization.supplier_id
                                AND (Supplier.stato = 'Y' or Supplier.stato = 'T' or Supplier.stato = 'PG')  
                                AND SuppliersOrganization.stato = 'Y' 
                                AND User.id = " . $result['User']['id'] . " 
                        ORDER BY SuppliersOrganization.name ";
                // echo '<br />'.$sql;
                try {
                    $supplierResults = $this->User->query($sql);
                } catch (Exception $e) {
                    CakeLog::write('error', $sql);
                    CakeLog::write('error', $e);
                }

                foreach ($supplierResults as $numSupplierResult => $supplierResult) {
                    $newResults[$i]['SuppliersOrganization'][$numSupplierResult]['SuppliersOrganization'] = $supplierResult['SuppliersOrganization'];
                    $newResults[$i]['SuppliersOrganization'][$numSupplierResult]['Supplier'] = $supplierResult['Supplier'];
                    $newResults[$i]['SuppliersOrganization'][$numSupplierResult]['SuppliersOrganizationsReferent'] = $supplierResult['SuppliersOrganizationsReferent'];
                }

                $i++;
            }
        } // foreach ($results as $numResult => $result)

        /* 	
          echo "<pre>";
          print_r($newResults);
          echo "</pre>";
         */
        $this->set('results', $newResults);

        $this->layout = 'default_front_end';
    }

    public function bookmarks_mails() {

        App::import('Model', 'BookmarksMail');
        $BookmarksMail = new BookmarksMail;

        $options = array();
        $options['conditions'] = array('BookmarksMail.organization_id' => (int) $this->user->organization['Organization']['id'],
            'BookmarksMail.user_id' => $this->user->id);

        $options['order'] = array('BookmarksMail.supplier_organization_id');
        $options['fields'] = array('BookmarksMail.supplier_organization_id', 'BookmarksMail.order_open', 'BookmarksMail.order_close');
        $options['recursive'] = -1;
        $bookmarksMailResults = $BookmarksMail->find('all', $options);

        /*
          echo "<pre>";
          print_r($bookmarksMailResults);
          echo "</pre>";
         */

        App::import('Model', 'SuppliersOrganization');
        $SuppliersOrganization = new SuppliersOrganization;
        $SuppliersOrganization->unbindModel(array('belongsTo' => array('Organization')));

        $options = array();
        $options['conditions'] = array('SuppliersOrganization.organization_id' => (int) $this->user->organization['Organization']['id'],
            'SuppliersOrganization.stato' => 'Y',
            'Supplier.stato' => 'Y'); // escludo i Temporanei perche' possono essere produttori di appoggio utilizzati dal gas
        $options['order'] = array('SuppliersOrganization.name');
        $options['recursive'] = 0;
        $suppliersOrganizationResults = $SuppliersOrganization->find('all', $options);

        $results = [];
        foreach ($suppliersOrganizationResults as $suppliersOrganizationResult) {
            $results[$suppliersOrganizationResult['SuppliersOrganization']['id']] = $suppliersOrganizationResult;

            /*
             * di default sono a Y
             */
            $results[$suppliersOrganizationResult['SuppliersOrganization']['id']]['BookmarksMail']['order_open'] = 'Y';
            $results[$suppliersOrganizationResult['SuppliersOrganization']['id']]['BookmarksMail']['order_close'] = 'Y';

            foreach ($bookmarksMailResults as $numResult => $bookmarksMailResult) {
                if ($suppliersOrganizationResult['SuppliersOrganization']['id'] == $bookmarksMailResult['BookmarksMail']['supplier_organization_id']) {
                    $results[$suppliersOrganizationResult['SuppliersOrganization']['id']]['BookmarksMail']['order_open'] = $bookmarksMailResult['BookmarksMail']['order_open'];
                    $results[$suppliersOrganizationResult['SuppliersOrganization']['id']]['BookmarksMail']['order_close'] = $bookmarksMailResult['BookmarksMail']['order_close'];

                    unset($bookmarksMailResults[$numResult]);
                }
            }
        }
        /*
          echo "<pre>";
          print_r($results);
          echo "</pre>";
         */
        $this->set(compact('results'));

        $this->layout = 'default_front_end';
    }

    /*
     * $field order_open / order_close
     * $value Y / N
     */

    public function bookmarks_mails_update($supplier_organization_id, $field, $value) {

        App::import('Model', 'BookmarksMail');
        $BookmarksMail = new BookmarksMail;

        $BookmarksMail->update($this->user, $supplier_organization_id, $field, $value);

        $this->layout = 'ajax';
        $this->render('/Layouts/ajax');
    }

}
