<?phpApp::uses('AppController', 'Controller');class SqlsController extends AppController {    public function beforeFilter() {        parent::beforeFilter();        /* ctrl ACL */        if (!$this->isRoot()) {            $this->Session->setFlash(__('msg_not_permission'));            $this->myRedirect(Configure::read('routes_msg_stop'));        }    }    public function admin_index() {				$debug = false;		$sqlResults = [];		$currentResults = [];				$results = $this->Sql->getQuerys($this->user);		$this->set(compact('results'));				if ($this->request->is('post') || $this->request->is('put')) {						self::d($this->request->data, $debug);						$sql_id = $this->request->data['Sql']['id'];			$currentResults = $results[$sql_id];			$sql = $currentResults['sql'];			$param = $this->request->data['Sql']['param'];			self::d($param, $debug);			if(!empty($param)) {				if(strpos($sql, '%Y')===false)					$sql = sprintf($sql, $param);				else {					/*					 * gestione di DATE_FORMAT(data_inizio,'%Y') 					 */					$sql = str_replace('%s', $param, $sql);				}			}			self::d($sql, $debug);						try {				$sqlTmpResults = $this->Sql->query($sql);				self::d($sql, $debug);				if(!empty($sqlTmpResults)) 					foreach($sqlTmpResults as $numResult => $sqlTmpResult) {						foreach($sqlTmpResult as $numResult2 => $sqlTmpRes) 							foreach($sqlTmpRes as $key => $sqlTmpRe) 								$sqlResults[$numResult][$key] = $sqlTmpRe;					}							} catch (Exception $e) {				CakeLog::write('error', $sql);				CakeLog::write('error', $e);				$this->Session->setFlash("Error to execute query ".$sql);			}						} // end post					$this->set(compact('sqlResults', 'currentResults'));	}}