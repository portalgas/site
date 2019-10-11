<h1>Upgrade Cakephp 2</h1>

<ul>
	<li>MERGE /var/www/portalgas/components/com_cake/app/Lib/ con il nuovo codice</li>
	<li>
		<pre class="shell" rel="cakelib/Model/Datasource/DboSource.php on line 445">
	protected function _execute($sql, $params = array(), $prepareOptions = array()) {
		$sql = trim($sql);
		
		/*
		* fractis
		*/
		//echo '&lt;br /&gt;'.$sql;
		if(Configure::read('developer.mode')) CakeLog::write('debug', $sql);
		
		if (preg_match('/^(?:CREATE|ALTER|DROP)\s+(?:TABLE|INDEX)/i', $sql)) {
			$statements = array_filter(explode(';', $sql));
			if (count($statements) > 1) {
				$result = array_map(array($this, '_execute'), $statements);
				return array_search(false, $result) === false;
			}
		}</pre>
		

		<pre class="shell" rel="cakelib/Model/Datasource/DboSource.php on line 1042">
		public function read(Model $Model, $queryData = array(), $recursive = null) {
		
		. . .  
		 
		$query = $this->buildAssociationQuery($Model, $queryData);

		/*
		* fractis
		*/
		//echo '&lt;br /&gt;'.$query;
		if(Configure::read('developer.mode')) CakeLog::write('debug', $query);
		</pre>
				
	</li>
	<li>
		<pre class="shell" rel="cakelib/Cache/CacheEngine.php on line 50">
	/*
	* fractis
	*/
	date_default_timezone_set('Europe/Rome');
	
	per errore strtotime($this->settings['duration']) - time();
		</pre>
				
	</li>
		
</ul>


	
<h1>Upgrade Cakephp 3</h1>