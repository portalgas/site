<?php
App::uses('AppModel', 'Model');

class GeoProvince extends AppModel {			
	
	public $name = 'GeoProvince';

	/*
	 * la lista di province ha come id la sigla
	 */
	public function getList($geo_region_id=0, $debug=false) {
        
		$geoProvinces = [];
		
		$options = [];
        $options['order'] = ['GeoProvince.name'];
		if(isset($geo_region_id) && !empty($geo_region_id))
			$options['conditions'] = ['GeoProvince.geo_region_id' => $geo_region_id];
        $results = $this->find('all', $options);
		if(!empty($results)) {
			foreach($results as $result) {
				$geoProvinces[$result['GeoProvince']['sigla']] = $result['GeoProvince']['name'].' ('.$result['GeoProvince']['sigla'].')';
			}
		}
        return $geoProvinces;	
	}
	
	public function getByIdGeoRegion($geo_region_id, $debug=false) {
		
        $options = [];
        $options['conditions'] = ['GeoProvince.geo_region_id' => $geo_region_id];
        $options['recursive'] = -1;
        $geoProvinces = $this->find('all', $options);

		return $geoProvinces;
	}

	public function getIdsByIdGeoRegion($geo_region_id, $debug=false) {
		
		$geoProvinces = [];
		
        $results = $this->getByIdGeoRegion($geo_region_id);
		if(!empty($results)) {
			foreach($results as $result) {
				array_push($geoProvinces, $result['GeoProvince']['id']);
			}
		}
		return $geoProvinces;
	}
	
	public function getSiglaByIdGeoRegion($geo_region_id, $debug=false) {
		
		$geoProvinces = [];
		
        $results = $this->getByIdGeoRegion($geo_region_id);
		if(!empty($results)) {
			foreach($results as $result) {
				array_push($geoProvinces, $result['GeoProvince']['sigla']);
			}
		}
		return $geoProvinces;
	}
	
	public $validate = [
		'name' => [
			'notEmpty' => [
				'rule' => ['notBlank']
			],
		],
	];

	public $belongsTo = [
		'GeoRegion' => [
			'className' => 'GeoRegion',
			'foreignKey' => 'geo_region_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		],		
	];	
}