<?php
App::uses('AppModel', 'Model');

class LoopsDelivery extends AppModel {

	public $validate = array(
		'organization_id' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'user_id' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'luogo' => array(
				'rule' => array('notempty'),
				'message' => 'Indica il luogo della consegna',
				'allowEmpty' => false
		),
		'data' => array(
				'date' => array(
						'rule' => array('date'),
						'message' => 'Indica la data della consegna',
						'allowEmpty' => false
				),
		),			
	);

	
	public $belongsTo = array(
		'User' => array(
			'className' => 'User',
			'foreignKey' => 'user_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		)
	);
	
	/*
	 * da una data di partenza (data_master) e dei filtri di ricorsione ($data) ottengo la nuova data di ricorsione
	*/
	public function get_data_copy($data_master, $data, $debug=false) {
	
		if($debug) {
			echo '<h2>__get_data_copy</h2>';
			echo 'data_master '.$data_master;
		}
	
		switch ($data['LoopsDelivery']['type']) {
			case 'WEEK':
				$week_every_week = $data['LoopsDelivery']['week_every_week'];
	
				if($debug) echo '<br />nuova copia: di '.$week_every_week.' settimana/e dopo';
	
				$data_copy = date('Y-m-d', strtotime('+'.$week_every_week.' weeks', strtotime($data_master)));
				break;
			case "MONTH":
	
				switch ($data['LoopsDelivery']['type_month']) {
					case 'MONTH1':
						$month1_day = $data['LoopsDelivery']['month1_day'];
						$month1_every_month = $data['LoopsDelivery']['month1_every_month'];
							
						if($debug) echo '<br />nuova copia: il giorno '.$month1_day.' ogni '.$month1_every_month.' mese/i';
							
						$data_copy = date('Y-m-d', strtotime('+'.$month1_every_month.' months', strtotime($data_master)));
						$data_copy = date('Y', strtotime($data_copy)).'-'.date('m', strtotime($data_copy)).'-'.$month1_day;
							
						$giorni_mese = date('t', strtotime($data_copy));
						if($debug) echo '<br />ctrl se il giorno nel mese ('.$month1_day.') esiste: totale giorni del mese '.$giorni_mese;
						if($month1_day > $giorni_mese)
							$data_copy = date('Y', strtotime($data_copy)).'-'.date('m', strtotime($data_copy)).'-'.$giorni_mese;
	
						break;
					case 'MONTH2':
						$month2_every_type = $data['LoopsDelivery']['month2_every_type'];
						$month2_day_week = $data['LoopsDelivery']['month2_day_week'];
						$month2_every_month = $data['LoopsDelivery']['month2_every_month'];
							
						if($debug) echo '<br />nuova copia: il '.$month2_every_type.' giorno '.$month2_day_week.' ogni '.$month2_every_month.' mese/i';
							
						switch ($month2_day_week) {
							case 'SUN':
								$month2_day_week = 0;
								break;
							case 'MON':
								$month2_day_week = 1;
								break;
							case 'TUE':
								$month2_day_week = 2;
								break;
							case 'WED':
								$month2_day_week = 3;
								break;
							case 'THU':
								$month2_day_week = 4;
								break;
							case 'FRI':
								$month2_day_week = 5;
								break;
							case 'SAT':
								$month2_day_week = 6;
								break;
									
						}
							
						if($debug) echo '<br />mese di partenza '.date('m', strtotime($data_master));
						
						/*
						 * calcolo anno
						 */ 
						$year_start = date('Y', strtotime($data_master)); // anno di partenza 
						$month_start = date('m', strtotime($data_master)); // mese di partenza 
						if($month_start+$month2_every_month > 12)
							$year = ($year_start+1);
						else
							$year = $year_start;
						
						if($debug) echo '<br />Anno di partenza '.$year_start.' - dal mese di partenza '.$month_start.' aggiungo '.$month2_every_month.' mesi: '.$year;
						
						$mese_copy = $year.'-'.date('m', strtotime('+'.$month2_every_month.' months', strtotime($data_master))).'-1';
						if($debug) echo '<br />mese copy '.$mese_copy;
						if($debug) echo '<br />il nuovo mese ha '.date('t', strtotime($mese_copy)).' giorni';
						/*
						 * ciclo per trovare il giorno della settimana esatta
						* 	ex il 3 lunedi' del mese
						*/
						for($i=1; $i <= (int)date('t', strtotime($mese_copy)); $i++) {
	
							if($i < 10)
								$giorno_doppia_cifra = '0'.$i;
							else
								$giorno_doppia_cifra = $i;
								
							$mese_copy = $year.'-'.date('m', strtotime('+'.$month2_every_month.' months', strtotime($data_master))).'-'.$giorno_doppia_cifra;
							// if($debug) echo '<br />'.$i.') mese new '.$mese_copy;
	
							$giorno = date('w', strtotime($mese_copy));
							// if($debug) echo '<br />'.$i.') giorno delle settimana '.$giorno.' da confrontare con '.$month2_day_week;
							if($giorno==$month2_day_week) {
								$giorni_della_settimana[] = $mese_copy;
	
								/*
								 if($debug) {
								echo '<br />Trovato!';
								echo "<pre>";
								print_r($giorni_della_settimana);
								echo "</pre>";
								}
								*/
							}
						}
	
						if($month2_every_type=='FIRST')
							$data_copy = $giorni_della_settimana[0];
						else
						if($month2_every_type=='SECOND')
							$data_copy = $giorni_della_settimana[1];
						else
						if($month2_every_type=='THIRD')
							$data_copy = $giorni_della_settimana[2];
						else
						if($month2_every_type=='FOURTH')
							$data_copy = $giorni_della_settimana[3];
						else
						if($month2_every_type=='LAST')
							$data_copy = $giorni_della_settimana[(count($giorni_della_settimana)-1)];
	
						break;
				} // end switch ($data['LoopsDelivery']['type_month'])
				break;
		} // end switch ($data['LoopsDelivery']['type'])
	
		if($debug) echo '<br />data_copy '.$data_copy;
	
		return $data_copy;
	}	
}