<?php
/**
 * Behavior with useful functionality around models containing an enum type field
 *
 * Copyright (c) Debuggable, http://debuggable.com
 *
 * @package default
 * @access public
 */
class EnumerableBehavior extends ModelBehavior {
/**
 * Fetches the enum type options for a specific field
 *
 * @param string $field
 * @return void
 * @access public
 */
  function enumOptions($model, $field) {
    $cacheKey = $model->alias . '_' . $field . '_enum_options';
    $options = Cache::read($cacheKey);

    if (!$options) {
      $sql = "SHOW COLUMNS FROM `".Configure::read('DB.prefix')."{$model->useTable}` LIKE '{$field}'";
      $enumData = $model->query($sql);

      $options = false;
      if (!empty($enumData)) {
        $patterns = array('enum(', ')', '\'');
        $enumData = str_replace($patterns, '', $enumData[0]['COLUMNS']['Type']);
        $optionsTmp = explode(',', $enumData);

		  // fractis: sostituisco la chiave numerica con il suo valore
		  foreach ($optionsTmp as $key => $value) {
				 $options[$value] = $this->__traslateEnum($value);
		  }

      }
      Cache::write($cacheKey, $options);
    }
    return $options;
  }

  protected	function __traslateEnum($str) {

		$traslateEnum = Configure::read('traslateEnum');
		foreach($traslateEnum as $key => $value) {
			if($str==$key) $str = $value;
		}

		return $str;
	}
}
?>