<?php
class TreemenuBehavior extends TreeBehavior {

	/**
	 * A convenience method for returning a hierarchical array used for HTML select boxes
	 *
	 * @param Model $Model Model instance
	 * @param mixed $conditions SQL conditions as a string or as an array('field' =>'value',...)
	 * @param string $keyPath A string path to the key, i.e. "{n}.Post.id"
	 * @param string $valuePath A string path to the value, i.e. "{n}.Post.title"
	 * @param string $spacer The character or characters which will be repeated
	 * @param integer $recursive The number of levels deep to fetch associated records
	 * @return array An associative array of records, where the id is the key, and the display field is the value
	 * @link http://book.cakephp.org/2.0/en/core-libraries/behaviors/tree.html#TreeBehavior::generateTreeList
	 */
	public function generateTreeListMenu(Model $Model, $conditions = null, $keyPath = null, $valuePath = null, $spacer = '_', $recursive = null) {
		$overrideRecursive = $recursive;
		extract($this->settings[$Model->alias]);
		if (!is_null($overrideRecursive)) {
			$recursive = $overrideRecursive;
		}

		if ($keyPath == null && $valuePath == null && $Model->hasField($Model->displayField)) {
			$fields = array($Model->primaryKey, $Model->displayField, $left, $right, 'link');
		} else {
			$fields = null;
		}

		if ($keyPath == null) {
			$keyPath = '{n}.' . $Model->alias . '.' . $Model->primaryKey;
		}

		if ($valuePath == null) {
			$valuePath = array('{0}{1}', '{n}.tree_prefix', '{n}.' . $Model->alias . '.' . $Model->displayField);

		} elseif (is_string($valuePath)) {
			$valuePath = array('{0}{1}', '{n}.tree_prefix', $valuePath);

		} else {
			$valuePath[0] = '{' . (count($valuePath) - 1) . '}' . $valuePath[0];
			$valuePath[] = '{n}.tree_prefix';
		}
		$order = $Model->alias . '.' . $left . ' asc';
		$results = $Model->find('all', compact('conditions', 'fields', 'order', 'recursive'));
		$stack = array();

		/* calcola depth */
		foreach ($results as $i => $result) {
			while ($stack && ($stack[count($stack) - 1] < $result[$Model->alias][$right])) {
				array_pop($stack);
			}
			//$results[$i]['tree_prefix'] = str_repeat($spacer, count($stack));
			$results[$i]['depth'] = count($stack);
			$stack[] = $result[$Model->alias][$right];
		}
		if (empty($results)) {
			return array();
		}
	
		return $results; // Set::combine($results, $keyPath, $valuePath);
	}
}