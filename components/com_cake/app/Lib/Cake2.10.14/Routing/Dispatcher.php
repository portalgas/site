<?php
/**
 * Dispatcher takes the URL information, parses it for parameters and
 * tells the involved controllers what to do.
 *
 * This is the heart of CakePHP's operation.
 *
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link          https://cakephp.org CakePHP(tm) Project
 * @package       Cake.Routing
 * @since         CakePHP(tm) v 0.2.9
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 */

/*
 fractis 
 App::uses('Router', 'Routing');
*/
App::uses('Router', 'Lib'.DS.'Routing');
App::uses('CakeRequest', 'Network');
App::uses('CakeResponse', 'Network');
App::uses('Controller', 'Controller');
App::uses('Scaffold', 'Controller');
App::uses('View', 'View');
App::uses('Debugger', 'Utility');
App::uses('CakeEvent', 'Event');
App::uses('CakeEventManager', 'Event');
App::uses('CakeEventListener', 'Event');

/**
 * Dispatcher converts Requests into controller actions. It uses the dispatched Request
 * to locate and load the correct controller. If found, the requested action is called on
 * the controller.
 *
 * @package       Cake.Routing
 */
class Dispatcher implements CakeEventListener {

/**
 * Event manager, used to handle dispatcher filters
 *
 * @var CakeEventManager
 */
	protected $_eventManager;

/**
 * Constructor.
 *
 * @param string $base The base directory for the application. Writes `App.base` to Configure.
 */
	public function __construct($base = false) {
		if ($base !== false) {
			Configure::write('App.base', $base);
		}
	}

/**
 * Returns the CakeEventManager instance or creates one if none was
 * created. Attaches the default listeners and filters
 *
 * @return CakeEventManager
 */
	public function getEventManager() {
		if (!$this->_eventManager) {
			$this->_eventManager = new CakeEventManager();
			$this->_eventManager->attach($this);
			$this->_attachFilters($this->_eventManager);
		}
		return $this->_eventManager;
	}

/**
 * Returns the list of events this object listens to.
 *
 * @return array
 */
	public function implementedEvents() {
		return array('Dispatcher.beforeDispatch' => 'parseParams');
	}

/**
 * Attaches all event listeners for this dispatcher instance. Loads the
 * dispatcher filters from the configured locations.
 *
 * @param CakeEventManager $manager Event manager instance.
 * @return void
 * @throws MissingDispatcherFilterException
 */
	protected function _attachFilters($manager) {
		$filters = Configure::read('Dispatcher.filters');
		if (empty($filters)) {
			return;
		}

		foreach ($filters as $index => $filter) {
			$settings = array();
			if (is_array($filter) && !is_int($index) && class_exists($index)) {
				$settings = $filter;
				$filter = $index;
			}
			if (is_string($filter)) {
				$filter = array('callable' => $filter);
			}
			if (is_string($filter['callable'])) {
				list($plugin, $callable) = pluginSplit($filter['callable'], true);
				App::uses($callable, $plugin . 'Routing/Filter');
				if (!class_exists($callable)) {
					throw new MissingDispatcherFilterException($callable);
				}
				$manager->attach(new $callable($settings));
			} else {
				$on = strtolower($filter['on']);
				$options = array();
				if (isset($filter['priority'])) {
					$options = array('priority' => $filter['priority']);
				}
				$manager->attach($filter['callable'], 'Dispatcher.' . $on . 'Dispatch', $options);
			}
		}
	}

/**
 * Dispatches and invokes given Request, handing over control to the involved controller. If the controller is set
 * to autoRender, via Controller::$autoRender, then Dispatcher will render the view.
 *
 * Actions in CakePHP can be any public method on a controller, that is not declared in Controller. If you
 * want controller methods to be public and in-accessible by URL, then prefix them with a `_`.
 * For example `public function _loadPosts() { }` would not be accessible via URL. Private and protected methods
 * are also not accessible via URL.
 *
 * If no controller of given name can be found, invoke() will throw an exception.
 * If the controller is found, and the action is not found an exception will be thrown.
 *
 * @param CakeRequest $request Request object to dispatch.
 * @param CakeResponse $response Response object to put the results of the dispatch into.
 * @param array $additionalParams Settings array ("bare", "return") which is melded with the GET and POST params
 * @return string|null if `$request['return']` is set then it returns response body, null otherwise
 * @triggers Dispatcher.beforeDispatch $this, compact('request', 'response', 'additionalParams')
 * @triggers Dispatcher.afterDispatch $this, compact('request', 'response')
 * @throws MissingControllerException When the controller is missing.
 */
 
	/* 
	 * fractis override  
	public function dispatch(CakeRequest $request, CakeResponse $response, $additionalParams = array()) {
		$beforeEvent = new CakeEvent('Dispatcher.beforeDispatch', $this, compact('request', 'response', 'additionalParams'));
		$this->getEventManager()->dispatch($beforeEvent);

		$request = $beforeEvent->data['request'];
		if ($beforeEvent->result instanceof CakeResponse) {
			if (isset($request->params['return'])) {
				return $beforeEvent->result->body();
			}
			$beforeEvent->result->send();
			return null;
		}

		$controller = $this->_getController($request, $response);

		if (!($controller instanceof Controller)) {
			throw new MissingControllerException(array(
				'class' => Inflector::camelize($request->params['controller']) . 'Controller',
				'plugin' => empty($request->params['plugin']) ? null : Inflector::camelize($request->params['plugin'])
			));
		}

		$response = $this->_invoke($controller, $request);
		if (isset($request->params['return'])) {
			return $response->body();
		}

		$afterEvent = new CakeEvent('Dispatcher.afterDispatch', $this, compact('request', 'response'));
		$this->getEventManager()->dispatch($afterEvent);
		$afterEvent->data['response']->send();
	}
	*/
	
	

/**
 * Initializes the components and models a controller will be using.
 * Triggers the controller action, and invokes the rendering if Controller::$autoRender
 * is true and echo's the output. Otherwise the return value of the controller
 * action are returned.
 *
 * @param Controller $controller Controller to invoke
 * @param CakeRequest $request The request object to invoke the controller for.
 * @return CakeResponse the resulting response object
 */
	protected function _invoke(Controller $controller, CakeRequest $request) {
		$controller->constructClasses();
		$controller->startupProcess();

		$response = $controller->response;
		$render = true;
		$result = $controller->invokeAction($request);
		if ($result instanceof CakeResponse) {
			$render = false;
			$response = $result;
		}

		if ($render && $controller->autoRender) {
			$response = $controller->render();
		} elseif (!($result instanceof CakeResponse) && $response->body() === null) {
			$response->body($result);
		}
		$controller->shutdownProcess();

		return $response;
	}

/**
 * Applies Routing and additionalParameters to the request to be dispatched.
 * If Routes have not been loaded they will be loaded, and app/Config/routes.php will be run.
 *
 * @param CakeEvent $event containing the request, response and additional params
 * @return void
 */
	public function parseParams($event) {
		$request = $event->data['request'];
		Router::setRequestInfo($request);
		$params = Router::parse($request->url);
		$request->addParams($params);

		if (!empty($event->data['additionalParams'])) {
			$request->addParams($event->data['additionalParams']);
		}
	}

/**
 * Get controller to use, either plugin controller or application controller
 *
 * @param CakeRequest $request Request object
 * @param CakeResponse $response Response for the controller.
 * @return mixed name of controller if not loaded, or object if loaded
 */
	protected function _getController($request, $response) {
		$ctrlClass = $this->_loadController($request);
		if (!$ctrlClass) {
			return false;
		}
		$reflection = new ReflectionClass($ctrlClass);
		if ($reflection->isAbstract() || $reflection->isInterface()) {
			return false;
		}
		return $reflection->newInstance($request, $response);
	}

/**
 * Load controller and return controller class name
 *
 * @param CakeRequest $request Request instance.
 * @return string|bool Name of controller class name
 */
	protected function _loadController($request) {
		$pluginName = $pluginPath = $controller = null;
		if (!empty($request->params['plugin'])) {
			$pluginName = $controller = Inflector::camelize($request->params['plugin']);
			$pluginPath = $pluginName . '.';
		}
		if (!empty($request->params['controller'])) {
			$controller = Inflector::camelize($request->params['controller']);
		}
		if ($pluginPath . $controller) {
			$class = $controller . 'Controller';
			App::uses('AppController', 'Controller');
			App::uses($pluginName . 'AppController', $pluginPath . 'Controller');
			App::uses($class, $pluginPath . 'Controller');
			if (class_exists($class)) {
				return $class;
			}
		}
		return false;
	}



	/*
	 * fractis custom INI
	 * 		public function dispatch(CakeRequest $request, CakeResponse $response, $additionalParams = array())
	 * 		private function __prepareCakeRequestFromSEO($request)
	 * 		private function __isUrlToFrontEndValide($request)  
	 */
	public $prefix = ""; 
	
	public function dispatch(CakeRequest $request, CakeResponse $response, $additionalParams = array()) {

		$debug=false;
		
		/*
		 * codice originale prima parte
		 */
		$beforeEvent = new CakeEvent('Dispatcher.beforeDispatch', $this, compact('request', 'response', 'additionalParams'));
		$this->getEventManager()->dispatch($beforeEvent);
		
		$request = $beforeEvent->data['request'];
		if ($beforeEvent->result instanceof CakeResponse) {
			if (isset($request->params['return'])) {
				return $beforeEvent->result->body();
			}
			$beforeEvent->result->send();
			return;
		}
		/*
		 * codice originale prima parte
		*/		
		
		
		if($debug) $requestBefore = clone $request; 
		
		$RoutingPrefixes = Configure::read('Routing.prefixes');
		$this->prefix = $RoutingPrefixes[0];
		
		if(strpos($_SERVER['PHP_SELF'],'/administrator/') === false) // chiamate da front-end
			$request = $this->__prepareCakeRequestFromSEO($request);                     
		
		
		if(strpos($_SERVER['PHP_SELF'],'/administrator/') !== false ||  // chiamate da back-office
			$this->__isUrlToFrontEndValide($request))                     // chiamate da front-end
		{
			// com_cake
			unset($request->query['option']); 
			
			// C O N T R O L L E R
			$request->params['controller'] = ucfirst($request->query['controller']);
			unset($request->query['controller']);
				
			// AC T I O N con eventuale P R E F I X  (admin)
			if(!empty($this->prefix)) {
				$request->params['prefix'] = $this->prefix;
				$request->params['action'] = $request->params['prefix'].'_'.$request->query['action'];
			}	
			else
				$request->params['action'] = $request->query['action'];
			unset($request->query['action']);
				
			/*
			 * PASS   FilterArticleSupplierId=99&_method=_POST
			 * NAMED  page:1&limit100=&sort:name&direction=asc
			 * 
			 *     [FilterArticleBio] => ALL
    		 *     [sort:name] =>  
			 */ 
			$arrNamed = [];
			$arrPass = [];
			foreach($request->query as $key => $value) {
				
				/*
				 * NAMED, [sort:name] =>  
				*/
				if(strpos($key,':') !== false) {
					list($keyNamed,$valueNamed) = explode(':',$key);
					$arrNamed[$keyNamed] = $valueNamed;
				}
				/*
				 * PASS, [FilterArticleBio] => ALL
				 * 
				 * nota: non ctrl che if(!empty($value)) perche' se scelgo come filtro "tutti gli utenti" value e' vuoto
				 */
				else 
					$arrPass[$key] = $value;
			}
			unset($request->query);
			if(!empty($arrNamed)) $request->params['named'] = $arrNamed;
			if(!empty($arrPass))  $request->params['pass'] = $arrPass;
		}
		else
		if(strpos($_SERVER['PHP_SELF'],'/admin/') !== false) {
			die("Dispatcher - Not direct access to cake!!");
		} 

		if($debug) {
			echo '<table style="width:100%;font-size:12px;">';
			echo '<tr>';
			echo '<td colspan="2">PHP_SELF: '.$_SERVER['PHP_SELF'].'</td>';
			echo '</tr>';
				
			echo '<tr>';
			echo '<td colspan="2">REQUEST_URI: '.$_SERVER['REQUEST_URI'].'</td>';
			echo '</tr>';
			echo '<tr>';
			echo '<th style="width:50%;font-weight:bold;">Before</th>';
			echo '<th style="font-weight:bold;">Post</th>';
			echo '</tr>';
				
			echo '<tr>';
			echo '<td style="vertical-align:top;">';
			echo '<pre style="background: none repeat scroll 0 0 #1D1F21;color: white;overflow: auto;font-size:13px;padding:5px;height:100%;">';
			echo '[params]';
			print_r($requestBefore->params);
			echo '[data]';
			print_r($requestBefore->data);
			echo '[query]';
			print_r($requestBefore->query);
			echo '</pre>';
			echo '</td>';
			
			echo '<td style="vertical-align:top;">';
			echo '<pre style="background: none repeat scroll 0 0 #1D1F21;color: white;overflow: auto;font-size:13px;padding:5px;height:100%;">';
			echo '[params]';
			print_r($request->params);
			echo '[data]';
			print_r($request->data);
			echo '[query]';
			print_r($request->query);
			echo '</pre>';
			echo '</td>';
			echo '</tr>';
			echo '</table>';
		}
		
		
		//exit;
	

		/*
		 * codice originale seconda parte
		*/		
		$controller = $this->_getController($request, $response);
		
		if (!($controller instanceof Controller)) {
			throw new MissingControllerException(array(
					'class' => Inflector::camelize($request->params['controller']) . 'Controller',
					'plugin' => empty($request->params['plugin']) ? null : Inflector::camelize($request->params['plugin'])
			));
		}
		
		$response = $this->_invoke($controller, $request);
		if (isset($request->params['return'])) {
			return $response->body();
		}
		
		$afterEvent = new CakeEvent('Dispatcher.afterDispatch', $this, compact('request', 'response'));
		$this->getEventManager()->dispatch($afterEvent);
		$afterEvent->data['response']->send();
		/*
		 * codice originale seconda parte
		*/		
	}

	/*
	 * FRONT-END la request puo' arrivare
	*
	*  [option] => com_cake
	*  [controller] => deliveries
	*  [action] => tabs
	*  se /?option=com_cake&controller=Deliveries&action=tabs
	*
	*  oppure con SEO
	*  [option] => com_cake
	*  [view] => deliveries
	*  [layout] => tabs
	*  se /consegne-gas-
	*
	* se SEO la trasformo da [view] a [controller]
	* 					  da [layout] a [action]
	* */
	private function __prepareCakeRequestFromSEO($request) {
		
		$debug=false;
	
		$urlFrontEndToRewriteCakeRequest = Configure::read('urlFrontEndToRewriteCakeRequest');
	
		if($debug) {
			echo "<pre> __prepareCakeRequestFromSEO request->query ";
			print_r($request->query);
			echo "</pre>";
		}
	
		if(!isset($request->query['controller']) && !isset($request->query['action'])) {
			/*
			 * $key controller   $value deliveries
			* $key action       $value tabs
			* $key admin        $value false
			* $key SEO          $value gas-tabs
			*
			* ctrl se REQUEST_URI inzia per SEO- (ex consegne-gas-)
			* */
			foreach($urlFrontEndToRewriteCakeRequest as $keys => $values) {
		
				$arrSEO = preg_split('[\/]',$_SERVER['REQUEST_URI']);
				$SEO = $arrSEO[count($arrSEO)-1];
				if(strpos($SEO, '?')!==false) 
					$SEO = substr($SEO,0,strpos($SEO, '?'));
				
				if($debug) {
					echo "<br> REQUEST_URI ".$_SERVER['REQUEST_URI'];
					echo "<br> SEO ".$SEO;
					echo "</pre>";
				}
				
				/*
				 * se e' un URL di tipo SEO e inivia per 
				 */
				foreach($values as $key => $value) {
					if(isset($values['SEO']) && $this->__string_starts_with($SEO, $values['SEO'])) {

						$request->query['option'] = 'com_cake';
						$request->query['controller'] = $values['controller'];
						$request->query['action'] = $values['action'];
						unset($request->query['view']);    // $values['controller']
						unset($request->query['layout']);  // values['action']						
						break;
					}
				}
			}
		}

		if($debug) {
			echo "<pre> __prepareCakeRequestFromSEO request->query ";
			print_r($request->query);
			echo "</pre>";
		}
		
		return $request;
	}

	private function __string_starts_with($string, $search)
	{
		return (strncmp($string, $search, strlen($search)) == 0);
	}
		
	private function __isUrlToFrontEndValide($request) {
		
		$debug=false;
		
		$urlFrontEndToRewriteCakeRequest = Configure::read('urlFrontEndToRewriteCakeRequest');
		
		if($debug) {
			echo "<pre> this->request->query ";
			print_r($request->query);
			echo "</pre>";
			echo "<pre> urlFrontEndToRewriteCakeRequest";
			print_r($urlFrontEndToRewriteCakeRequest);
			echo "</pre>";
		}	

		$isValide=false;
		foreach($urlFrontEndToRewriteCakeRequest as $keys => $values) {
			$urlValido=0;
			if($debug) echo '<br />--------------------------------------';
							
			foreach($values as $key => $value) {
				/*
				 * $key controller   $value deliveries
				 * $key action       $value tabs
				 * $key admin        $value false
				 * se la chiamata ($request->query['controller'], $request->query['action']) 
				 * 		e' questa allora idValide
				 * */
				if(isset($request->query[$key]) &&  // verifico se isset perche' $request->query['admin'] non esiste
						strtoupper($request->query[$key])==strtoupper($value)) $urlValido++;
				
				if($debug) {
					if(isset($request->query[$key]))
						echo '<br />key ['.$key.'] value ['.$value.'] - $request->query[key] '.$request->query[$key].' - urlValido '.$urlValido;
					else
						echo '<br />key ['.$key.'] value ['.$value.'] - urlValido '.$urlValido;
				}
				
			}
			
			if($urlValido==2) { // dev'essere 2 perche' deve essere uguale $request->query['controller'] e $request->query['action']
				$isValide=true;
				if(!$values['admin']) $this->prefix = "";  // sempre false
				
				break;
			}
		}

		return $isValide;
	}
	
	/*
	 * fractis custom END
	 */	

}