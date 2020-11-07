<?php
/*
 * Controller/EventsController.php
 * CakePHP Full Calendar Plugin
 *
 * Copyright (c) 2010 Silas Montgomery
 * http://silasmontgomery.com
 *
 * Licensed under MIT
 * http://www.opensource.org/licenses/mit-license.php
 */

class EventsController extends AppController {

	public function beforeFilter() {
		parent::beforeFilter();
		
        /* ctrl ACL */
        $actionWithPermission = array('index');
        if (!in_array($this->action, $actionWithPermission)) {		
			if (!$this->isManager() && !$this->isManagerEvents()) {
				$this->Session->setFlash(__('msg_not_permission'));
				$this->myRedirect(Configure::read('routes_msg_stop'));
			}
		}
        /* ctrl ACL */		
	}
	
	var $components = array('Session');
	var $helpers = array('Html', 'Form', 'Session', 'Js'=>array('Jquery'));

	var $name = 'Events';

    function admin_index() {
    	$SqlLimit = 25;
	    $this->paginate = array('conditions' => array('Event.organization_id' => $this->user->organization['Organization']['id'], 
													  'DATE(Event.end) >= CURDATE()'),
					    		'recursive' => 1,
								'maxLimit' => $SqlLimit, 'limit' => $SqlLimit,
								'order' => array('Event.start' => 'asc', 'Event.end' => 'asc'));
	    $results = $this->paginate('Event');
		$results = $this->_getEventsUsers($results);
		$this->set('results', $results);
	}
	
    function admin_index_history() {
    	$SqlLimit = 25;
	    $this->paginate = array('conditions' => array('Event.organization_id' => $this->user->organization['Organization']['id'], 
													  'DATE(Event.end) < CURDATE()'),
					    		'recursive' => 1,
								'maxLimit' => $SqlLimit, 'limit' => $SqlLimit,
								'order' => array('Event.start' => 'desc', 'Event.end' => 'desc'));
	    $results = $this->paginate('Event');
		$results = $this->_getEventsUsers($results);
		$this->set('results', $results);
	}


    function index() {
		/*
		 * attivita del GAS
		 */
		$options = [];
		$options['conditions'] = array('Event.organization_id' => $this->user->organization['Organization']['id'], 
									   'Event.isVisibleFrontEnd' => 'Y',
										'DATE(Event.end) >= CURDATE()');
		$options['recursive'] = 0;
		$options['order'] = array('Event.start', 'Event.end');
	    
		$currentResults = $this->Event->find('all', $options);
		$currentResults = $this->_getEventsUsers($currentResults);
		
		/*
		 * attivita STORICHE
		 */
		$options = [];
		$options['conditions'] = array('Event.organization_id' => $this->user->organization['Organization']['id'], 
									   'Event.isVisibleFrontEnd' => 'Y', 
										'DATE(Event.end) < CURDATE()');
		$options['recursive'] = 0;
		$options['order'] = array('Event.start', 'Event.end');
	    
		$historyResults = $this->Event->find('all', $options);
		$historyResults = $this->_getEventsUsers($historyResults);
		
		$results['current'] = $currentResults;
		$results['history'] = $historyResults;
		$this->set('results', $results);
		
		self::d($results, false);
		
        /*
         * R E Q U E S T - P A Y M E N T S - dello U S E R 
         */
        if ($this->user->organization['Template']['payToDelivery'] == 'POST' || $this->user->organization['Template']['payToDelivery'] == 'ON-POST') {
            App::import('Model', 'SummaryPayment');
            $SummaryPayment = new SummaryPayment;

            $options = [];
            $options['conditions'] = array('SummaryPayment.organization_id' => $this->user->organization['Organization']['id'],
                'RequestPayment.organization_id' => $this->user->organization['Organization']['id'],
                'SummaryPayment.stato !=' => 'PAGATO',
                'RequestPayment.stato_elaborazione' => 'OPEN',
                'User.id' => $user_id);
            $options['order'] = 'RequestPayment.created DESC';
            $options['recursive'] = 1;
            $requestPaymentsResults = $SummaryPayment->find('all', $options);
            $this->set(compact('requestPaymentsResults'));
        }
		
		$this->layout = 'default_front_end';
	}
	
	function admin_add() {
			
		$debug = false;
		
		if ($this->request->is('post') || $this->request->is('put')) {
			
			$this->request->data['Event']['organization_id'] = $this->user->organization['Organization']['id'];
			
			self::d(["BEFORE this->request->data",$this->request->data],$debug);
						
			$this->request->data['Event']['start'] = $this->request->data['Event']['start_db'].' '.$this->request->data['Event']['start']['hour'].':'.$this->request->data['Event']['start']['min'].':00';
			$this->request->data['Event']['end'] = $this->request->data['Event']['end_db'].' '.$this->request->data['Event']['end']['hour'].':'.$this->request->data['Event']['end']['min'].':00';
			$this->request->data['Event']['date_alert_mail'] = $this->request->data['Event']['date_alert_mail_db'];
			$this->request->data['Event']['date_alert_fe'] = $this->request->data['Event']['date_alert_fe_db'];
			
			self::d($this->request->data,$debug);
			
			$this->Event->create();
			if ($this->Event->save($this->request->data)) {
				
				$event_id = $this->Event->getLastInsertId();
				
				/*
				 * inserisco gli Users associati all'evento
				 */
				if(!empty($this->request->data['Event']['event_user_ids'])) {
					App::import('Model', 'EventsUser');
					$EventsUser = new EventsUser;
					
					$EventsUser->insert($this->user, $event_id, $this->request->data['Event']['event_user_ids'], $debug);
				}  
		
				$this->Session->setFlash(__('The event has been saved', true));
				if(!$debug) $this->myRedirect(['action' => 'index']);
			} else {
				$this->Session->setFlash(__('The event could not be saved. Please, try again.', true));
			}
		} // end if ($this->request->is('post') || $this->request->is('put')) 
			
		$this->set('eventTypes', $this->Event->EventType->find('list'));

		App::import('Model', 'User');
		$User = new User;
		
		/*
		 *  User responsabile 
		 */
		$options = [];
		$options['conditions'] = array('User.organization_id' => $this->user->organization['Organization']['id'],
									   'User.block' => '0');
		$options['fields'] = array('User.id', 'User.name');							   
		$options['order'] = array('User.name');
		$options['recursive'] = -1;
		$usersRespResults = $User->find('list', $options);
		$this->set('usersRespResults', $usersRespResults);
		
		/*
		 * users
		 */					
		$options = [];
		$options['conditions'] = array('User.organization_id' => $this->user->organization['Organization']['id'],
									   'User.block' => '0');
		$options['fields'] = array('User.id', 'User.name');							   
		$options['order'] = array('User.name');
		$options['recursive'] = -1;
		$usersResults = $User->find('list', $options);
		$this->set('usersResults', $usersResults);
		$this->set('eventUsersResults', $eventUsersResults);

        $isVisibleFrontEnd = ClassRegistry::init('Event')->enumOptions('isVisibleFrontEnd');
        $this->set(compact('isVisibleFrontEnd'));		
	}

	function admin_edit($event_id = null) {
		
		$debug = false;

		if (empty($event_id)) {
			$event_id = $this->request->data['Event']['id'];
		}
		
		if (empty($event_id)) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
		}
		
		if ($this->request->is('post') || $this->request->is('put')) {
			
			$this->request->data['Event']['organization_id'] = $this->user->organization['Organization']['id'];
			
			$this->request->data['Event']['start'] = $this->request->data['Event']['start_db'].' '.$this->request->data['Event']['start']['hour'].':'.$this->request->data['Event']['start']['min'].':00';
			$this->request->data['Event']['end'] = $this->request->data['Event']['end_db'].' '.$this->request->data['Event']['end']['hour'].':'.$this->request->data['Event']['end']['min'].':00';
			$this->request->data['Event']['date_alert_mail'] = $this->request->data['Event']['date_alert_mail_db'];
			$this->request->data['Event']['date_alert_fe'] = $this->request->data['Event']['date_alert_fe_db'];
		
			self::d($this->request->data,$debug);
			
			if ($this->Event->save($this->request->data)) {
								
				/*
				 * inserisco gli Users associati all'evento
				 */
				if(!empty($this->request->data['Event']['event_user_ids'])) {
					App::import('Model', 'EventsUser');
					$EventsUser = new EventsUser;
					
					$EventsUser->insert($this->user, $event_id, $this->request->data['Event']['event_user_ids'], $debug);
				} 
		
				$this->Session->setFlash(__('The event has been saved', true));
				if(!$debug) $this->myRedirect(['action' => 'index']);
			} else {
				$this->Session->setFlash(__('The event could not be saved. Please, try again.', true));
			}
		} // end if ($this->request->is('post') || $this->request->is('put')) 
			
		$options = [];
		$options['conditions'] = array('Event.id' => $event_id);
		$this->request->data = $this->Event->find('first', $options);
		
		$this->set('eventTypes', $this->Event->EventType->find('list'));
		
		/*
		 * users gia' associati
		 */
		$newEventsUsersResults = [];
		App::import('Model', 'EventsUser');
		$EventsUser = new EventsUser;
		
		$options = [];
		$options['conditions'] = array('EventsUser.organization_id' => $this->user->organization['Organization']['id'],
									   'EventsUser.event_id' => $event_id);
		$eventsUsersResults = $EventsUser->find('all', $options);

		$events_users_ids = '';
		if(!empty($eventsUsersResults))
		foreach ($eventsUsersResults as $eventsUsersResult) {
			$events_users_ids .= $eventsUsersResult['EventsUser']['user_id'].',';
			
			$newEventsUsersResults[$eventsUsersResult['EventsUser']['user_id']] = $eventsUsersResult['User']['name']; // ricreo array perche' non ho potuto fare find(list) non essendoci EventsUser.id
		}
		
		App::import('Model', 'User');
		$User = new User;
		
		/*
		 *  User responsabile 
		 */
		$options = [];
		$options['conditions'] = array('User.organization_id' => $this->user->organization['Organization']['id'],
									   'User.block' => '0');
		$options['fields'] = array('User.id', 'User.name');
		$options['order'] = array('User.name');
		$options['fields'] = array('User.id', 'User.name');
		$options['recursive'] = -1;
		$usersRespResults = $User->find('list', $options);
		$this->set('usersRespResults', $usersRespResults);
		
		/*
		 * users da associare
		 */					
		$options = [];
		$options['conditions'] = array('User.organization_id' => $this->user->organization['Organization']['id'],
									   'User.block' => '0');
		if(!empty($events_users_ids)) {
			$events_users_ids = substr($events_users_ids, 0, (strlen($events_users_ids)-1));
			$options['conditions'] += array("User.id NOT IN ('$events_users_ids')");
		}
		$options['fields'] = array('User.id', 'User.name');
		$options['order'] = array('User.name');
		$options['recursive'] = -1;		
		$usersResults = $User->find('list', $options);
		
		$this->set('usersResults', $usersResults);
		$this->set('eventUsersResults', $newEventsUsersResults);	
		
        $isVisibleFrontEnd = ClassRegistry::init('Event')->enumOptions('isVisibleFrontEnd');
        $this->set(compact('isVisibleFrontEnd'));		
	}

	function admin_delete($event_id = null) {
		if (!$event_id) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
		}
		if ($this->Event->delete($event_id)) {
			$this->Session->setFlash(__('Delete Event', true));
			$this->myRedirect(array('action'=>'index'));
		}
		$this->Session->setFlash(__('Event was not deleted', true));
		$this->myRedirect(['action' => 'index']);
	}
	
    // The feed action is called from "webroot/js/ready.js" to get the list of events (JSON)
	function admin_feed($event_id=null) {

		$debug = false;
		
		$start = $this->request->params['pass']['start'];
		$end = $this->request->params['pass']['end'];
		
		$options = [];
		$options['conditions'] = array('Event.organization_id' => $this->user->organization['Organization']['id'],
									  'UNIX_TIMESTAMP(start) >=' => $start, 
									   'UNIX_TIMESTAMP(start) <=' => $end);
		$options['conditions'] = 1;
		$events = $this->Event->find('all', $options);
		
		self::d([$data,$events],$debug);

		foreach($events as $event) {
			if($event['Event']['all_day'] == 1) {
				$allday = true;
				$end = $event['Event']['start'];
			} else {
				$allday = false;
				$end = $event['Event']['end'];
			}
			$data[] = array(
					'id' => $event['Event']['id'],
					'title'=>$event['Event']['title'],
					'start'=>$event['Event']['start'],
					'end' => $end,
					'allDay' => $allday,
					//'url' => Router::url('/') . 'full_calendar/events/view/'.$event['Event']['id'],
					'url' => '?option=com_cake&controller=Events&action=edit&id='.$event['Event']['id'],
					'details' => $event['Event']['details'],
					'className' => $event['EventType']['color']
			);
		}

		self::d([$data,json_encode($data)],$debug);
				
		$this->layout = "ajax";
		$this->set("json", json_encode($data));
	}

     // The update action is called from "webroot/js/ready.js" to update date/time when an event is dragged or resized
	function admin_update() {
		$vars = $this->params['url'];
		$this->Event->id = $vars['id'];
		$this->Event->saveField('start', $vars['start']);
		$this->Event->saveField('end', $vars['end']);
		$this->Event->saveField('all_day', $vars['allday']);
	}
	
	/*
	*  ottengo gli users associati ad un evento
	*/
	private function _getEventsUsers($results, $debug=false) {

		App::import('Model', 'EventsUser');
		
		foreach($results as $numResult => $result) {

			$options = [];
			$options['conditions'] = array('EventsUser.organization_id' => $this->user->organization['Organization']['id'],
										   'EventsUser.event_id' => $result['Event']['id']);
			$options['order'] = array('User.name');
			$options['recursive'] = 0;

			$EventsUser = new EventsUser;
			
			$eventsUserResults = $EventsUser->find('all', $options);
			
			$results[$numResult]['Event']['EventsUser'] = $eventsUserResults;
		}
		
		self::d($results, $debug);
		
		return $results;
	}
}
?>