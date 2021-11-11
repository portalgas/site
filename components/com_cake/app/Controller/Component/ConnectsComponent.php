<?php 
App::uses('Component', 'Controller');
App::uses('Security', 'Utility');

/*
 * creo l'url per andare su 
 * Connects e con lo user autenticao creare il salt
 * redirect neo con c_to / a_to 
 */
class ConnectsComponent extends Component {
	
	private $_prefix_url_bo = '';
	private $_prefix_url_fe = '';
	
    public function initialize(Controller $controller) 
    {
    	$this->_prefix_url_bo = Configure::read('App.server').'/administrator/index.php?option=com_cake&controller=Connects&action=index';    

    	$this->_prefix_url_fe = Configure::read('App.server').'?option=com_cake&controller=Connects&action=index';
    }
		
	/*
	 * creo url per redirect BO
	 */
	public function createUrlBo($c_to, $a_to='', $params=[]) {

		$url = '';
		$url .= $this->_prefix_url_bo;
		$url .= $this->_createQueryParams($c_to, $a_to, $params);
		
		return $url;
	}       
   
	/*
	 * creo url per redirect FE
	 */   
	public function createUrlFe($c_to, $a_to='', $params=[]) {

		$url = '';
		$url .= $this->_prefix_url_fe;
		$url .= $this->_createQueryParams($c_to, $a_to, $params);

		return $url;
	}

	/*
	 * return c_to=admin/orders&a_to=add&order_id=999;
	*/
	private function _createQueryParams($c_to, $a_to, $params) {
		
		$url = '';
		$url .= '&c_to='.$c_to;
		$url .= '&a_to='.$a_to;
		if(!empty($params)) {
			$q = '';
			foreach ($params as $key => $value) {
				$q .= $key.'='.$value.'&';
			}
			$q = substr($q, 0, strlen($q)-1);				
			$url .= '&'.$q;
		}
		
		return $url;
	}

	public function createQueryParams($c_to, $a_to='', $params=[]) {
		
		return $this->_createQueryParams($c_to, $a_to, $params);
	}
}
?>