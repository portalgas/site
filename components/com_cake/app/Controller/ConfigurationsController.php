<?php
App::uses('AppController', 'Controller');

class ConfigurationsController extends AppController {

	public function beforeFilter() {
		parent::beforeFilter();

		if(!$this->isRoot()) {
			$this->Session->setFlash(__('msg_not_permission'));
			$this->myRedirect(Configure::read('routes_msg_stop'));
		}
	}
	public function admin_php_info() {
		$this->layout = 'ajax';
	}

	public function admin_apc_info() {
		$this->layout = 'ajax';
	}
	
	public function admin_apc_clean() {

		$results = [];
		if (function_exists('apcu_clear_cache')) {
			apcu_clear_cache('user');
			apcu_clear_cache('opcode');
			$results = ['success' => true];
		}
		else {
			$results = ['success' => false, 'msg' => '!function_exists(apcu_clear_cache'];
		}

		echo json_encode($results);

  	    $this->layout = 'ajax';
		$this->render('admin_apc_info');
	}
	
	public function admin_db_change_collation($new_collation = 'utf8_general_ci', $debug=true)
	{
		// Make sure we have at least MySQL 4.1.2
		$db = JFactory::getDbo();
		$old_collation = $db->getCollation();
		
		self::d($old_collation,$debug);
		
		if ($old_collation == 'N/A (mySQL < 4.1.2)')
		{
			// We can't change the collation on MySQL versions earlier than 4.1.2
			return false;
		}
	
		// Get this database's name and try to change its collation
		$conf = JFactory::getConfig();	
		if (version_compare(JVERSION, '3.0', 'ge'))
		{
			$dbname = $conf->get('db');
		}
		else
		{
			$dbname = $conf->getValue('config.db');
		}
		$sql = "ALTER DATABASE `$dbname` DEFAULT COLLATE $new_collation";
		if($debug) echo '<br /> '.$sql;
		$db->setQuery($sql);
		$db->execute();
	
		// Get all tables
		$tables = $this->_findTables($db, $debug);
		$queryStack = '';
		if (!empty($tables))
		{
			foreach ($tables as $tableName)
			{
				$tableName = $tableName['Tables_in_portalgas'];
				
				$sql = 'SHOW FULL COLUMNS FROM `' . $tableName . '`';
				if($debug) echo '<br /> '.$sql;
				$db->setQuery($sql);
				$columns = $db->loadAssocList();
				$mods = []; // array to hold individual MODIFY COLUMN commands
				if (is_array($columns))
				{
					foreach ($columns as $column)
					{
						// Make sure we are redefining only columns which do support a collation
						$col = (object)$column;
						if (empty($col->Collation))
						{
							continue;
						}
	
						$null = $col->Null == 'YES' ? 'NULL' : 'NOT NULL';
						$default = is_null($col->Default) ? '' : "DEFAULT '" . $db->escape($col->Default) . "'";
						$mods[] = "MODIFY COLUMN `{$col->Field}` {$col->Type} $null $default COLLATE $new_collation";
					}
				}
	
				// Begin the modification statement
				$sql = "ALTER TABLE `$tableName` ";
	
				// Add commands to modify columns
				if (!empty($mods))
				{
					$sql .= implode(', ', $mods) . ', ';
				}
	
				// Add commands to modify the table collation
				$sql .= 'DEFAULT CHARACTER SET UTF8 COLLATE utf8_general_ci;';
				$queryStack .= "$sql\n";
			}
		}
	
		if (!empty($queryStack))
		{
			if (version_compare(JVERSION, '3.0', 'ge'))
			{
				// Joomla! 3.0 and later... God help us!
				$queries = explode(';', $queryStack);
				foreach ($queries as $q)
				{
					$q = trim($q);
					if (!empty($q))
					{
						if($debug) echo '<br /> '.$q;
						$db->setQuery($q);
						$db->execute();
					}
				}
			}
			else
			{
				// Execute the stacked queries in a transaction
				if($debug) echo '<br />Execute the stacked queries in a transaction '.$queryStack;
				$db->setQuery($queryStack);
				$db->queryBatch(false, true);
			}
		}
		
		$this->render('admin_index');
	}
	
	private function _findTables($db, $debug) {
	
		$sql = "show tables";
		if($debug) echo '<br /> '.$sql;
		$db->setQuery($sql);
	
		$results = $db->loadAssocList();
		//$results = $db->loadResult();
	
		return $results;
	}
		
	public function admin_db_change_prefix() {
		
		echo "
		 administrator/components/com_admintools/models/dbprefix.php<br />
			Database table prefix editor<br />
			Model::UserGroupMap<br />
			Trigger j_users_Trigger<br />
			file configurazione joomla<br />
			dump db => rename table<br />
		 ";
		
		$this->render('admin_index');
	}

}