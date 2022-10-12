<?php
App::uses('Helper', 'View');
App::uses('View', 'View');
App::uses('UtilsCommons', 'Lib');

/**
 * Application helper
 *
 * Add your application-wide methods in the class below, your helpers
 * will inherit them.
 *
 * @package       app.View.Helper
 */
class AppHelper extends Helper {

	public $helpers = ['Time','Html','Form','Ajax']; 
	public $utilsCommons;
	
	public function __construct(View $View, $settings = []) {
		parent::__construct($View, $settings);	

		$this->utilsCommons = new UtilsCommons($this->Time);		
	}

	public static function d($var, $debug=false) { // idem in AppController / AppModel / AppHelper $this->App->d
		if($debug) {
			if(is_array ($var)) {
				foreach($var as $k => $v) {
					echo "<pre>";
					print_r($k);
					echo '  ';
					print_r($v);
					echo "</pre>";
				}
			}
			else {			
				echo "<pre>";
				print_r($var);
				echo "</pre>";
			}
		}
	}
	
	public static function dd($var, $debug=true) { // idem in AppController / AppModel / AppHelper $this->App->dd
		self::d($var, true);
	}
		
	public static function l($var, $debug=false) { // idem in AppController / AppModel / AppHelper $this->App->l
		if(Configure::read('developer.mode') || $debug) {
			if(is_array ($var)) 
				CakeLog::write('debug', print_r($var, true), ['myDebug']);
			else 
				CakeLog::write('debug', $var, ['myDebug']);
		}
	}
	
	public static function x($var) { // idem in AppController / AppModel / AppHelper $this->App->x
		die($var);
	}
		
	public function _organizationNameError($organization) {
		if($organization['Organization']['id']==10)
			$organization_name = "Colibrì";
		else
			$organization_name = $organization['Organization']['name'];
			
		
		return $organization_name;
	}
	
	/*
	 * nota da aggiungere se i valori qta o importo sono stati settati dal referente
	 */
	public function traslateQtaImportoModificati($value) {
		if($value) return '<span>*</span>';
	}

    public function traslateQtaImportoModificatiDescri($value) {
        if($value) return 'Modificato dal referente';
    }

	public function traslateEnum($str) {

		return $this->utilsCommons->traslateEnum($str);
	}

	/*
	 * stesso codice AppController
	*/
	public function traslateWww($str) {
			
    	if(strpos($str,'http://')===false && strpos($str,'https://')===false)
    		$str = 'http://'.$str;
			
		return $str;
	}

	/*
	 * 'Y', 'N', 'LOCK', 'QTAMAXORDER'
	*/
	public function traslateArticlesOrderStato($results) {
			
		$tmp = '';
	
		if($results['ArticlesOrder']['stato']=='Y')
			$tmp .= "Articolo può essere acquistato";
		else
		if($results['ArticlesOrder']['stato']=='N')
			$tmp .= "Articolo non può essere acquistato";
		else
		if($results['ArticlesOrder']['stato']=='LOCK')
			$tmp .= "Articolo è temporaneamente bloccato";
		else
		if($results['ArticlesOrder']['stato']=='QTAMAXORDER') {
			$tmp .= "Articolo ha raggiunto la quantità massima ";
			if(isset($results['ArticlesOrder']['qta_massima_order']))
				$tmp .= "(".$results['ArticlesOrder']['qta_massima_order'].")";
		}
	
		return $tmp;
	}
	
	/*
	 * 'Y', 'N', 'LOCK', 'QTAMAXORDER'
	 */
	public function traslateProdDeliveriesArticleStato($results) {
			
		$tmp = '';
		
		if($results['ProdDeliveriesArticle']['stato']=='Y')
			$tmp .= "Articolo può essere acquistato";
		else
		if($results['ProdDeliveriesArticle']['stato']=='N')
			$tmp .= "Articolo non può essere acquistato";
		else	
		if($results['ProdDeliveriesArticle']['stato']=='LOCK')
			$tmp .= "Articolo è temporaneamente bloccato";
		else	
		if($results['ProdDeliveriesArticle']['stato']=='QTAMAXORDER') {
			$tmp .= "Articolo ha raggiunto la quantità massima ";
			if(isset($results['ProdDeliveriesArticle']['qta_massima_order']))
				$tmp .= "(".$results['ProdDeliveriesArticle']['qta_massima_order'].")";
		}
		
		return $tmp;
	}
	
	function formatSizeUnits($bytes)
	{
		if ($bytes >= 1073741824) {
			$bytes = number_format($bytes / 1073741824, 2) . ' GB';
		}
		elseif ($bytes >= 1048576) {
			$bytes = number_format($bytes / 1048576, 2) . ' MB';
		}
		elseif ($bytes >= 1024) {
			$bytes = number_format($bytes / 1024, 2) . ' KB';
		}
		elseif ($bytes > 1) {
			$bytes = $bytes . ' bytes';
		}
		elseif ($bytes == 1) {
			$bytes = $bytes . ' byte';
		}
		else {
			$bytes = '0 bytes';
		}
	
		return $bytes;
	}

	public function formatDateCreatedModifier($data) {
		if(!empty($data))  {
			if($data != Configure::read('DB.field.datetime.empty'))
				$data = $this->Time->i18nFormat($data,"%e %b %Y");
			else $data = 'Mai';
		}
	
		return $data;
	}

	public function formatOrario($orario) {
		if(!empty($orario))  
			$orario = substr($orario, 0, 5);
		
		return $orario;
	}
	
	public function drawVote($voto, $nota='') {

		if(!empty($nota)) 
			$id = uniqid('vote_');
		
		$tmp = '';		
		$tmp .= '<div ';
		if(!empty($nota)) 
			$tmp .= ' id="vote_supplier_vote_id_'.$id.'" style="cursor:pointer;" ';
		$tmp .= '>';
		$tmp .= str_repeat('<span style="background:#fff url(/images/cake/actions/16x16/bookmark.png) no-repeat scroll 0px 0px;padding-left:16px;margin-right:2px;"></span>', ($voto+1));
		$tmp .= '<span style="margin-left:5px;">'.__('vote_'.$voto).'</span>';
		$tmp .= '</div>';
		
		if(!empty($nota)) {
						
			$tmp .= '<div id="vote_supplier_vote_id_'.$id.'_modal" class="modal fade" role="dialog">';
			$tmp .= '  <div class="modal-dialog">';
			$tmp .= '<div class="modal-content">';
			$tmp .= '<div class="modal-header"><button type="button" class="close" data-dismiss="modal">&times;</button><h4 class="modal-title">Giudizio</h4></div>';
			$tmp .= '      <div class="modal-body"><p>'.$nota.'</p></div>';
      		$tmp .= '<div class="modal-footer"><button type="button" class="btn btn-primary" data-dismiss="modal">Close</button></div>';
    		$tmp .= '</div></div></div>';
			
			$tmp .= '<script>';
			$tmp .= "\n";
			$tmp .= '$(document).ready(function () {';
			$tmp .= "\n";
			$tmp .= '$("#vote_supplier_vote_id_'.$id.'").click(function() {';
			$tmp .= "\n";
			$tmp .= '$("#vote_supplier_vote_id_'.$id.'_modal").modal("show");';
			$tmp .= "\n";
			$tmp .= 'return false;';
			$tmp .= "\n";
			$tmp .= '});';
			$tmp .= '});';
			$tmp .= '</script>';
		}
		
		return $tmp;
	}
			
	/*
	 * $options['title']
	 * $options['width']
	 */
	public function drawUserAvatar($user, $user_id, $userResult=[], $options=[]) {
		
		$this->d($userResult);
		
		$tmp = '';
		$userAvatar = '';
		
		if(empty($user_id)) return $tmp;
		if(!empty($userResult)) 
			$userName = $userResult['name'];
		else
			$userName = "Avatar dell'utente";
		
		foreach (Configure::read('App.web.img.upload.extension') as $estensione) {
			if(file_exists(Configure::read('App.root').Configure::read('App.img.upload.user').'/'.$user->organization['Organization']['id'].'/'.$user_id.'.'.$estensione)) {
				$userAvatar = $user_id.'.'.$estensione;
				break;
			}
		}

		/*
		 * options
		 */
		if(isset($options['width']))
			$width = $options['width'];
		else
			$width = Configure::read('App.web.img.upload.width.userview');
		
		if(isset($options['title']))
			$title = $options['title'];
		else
			$title = $userName;
		
		if(isset($options['alt']))
			$alt = $options['alt'];
		else
			$alt = $userName;

		
		if(!empty($userAvatar))	
			$tmp .= '<img width="'.$width.'" class="userAvatar" src="'.Configure::read('App.server').Configure::read('App.web.img.upload.user').'/'.$user->organization['Organization']['id'].'/'.$userAvatar.'" alt="'.$alt.'" title="'.$title.'" />';
		
		return $tmp;
	}
	
    public function drawOrdersStateDiv($order) {

   		$str = '';   	
   		if(isset($order['Order']))	
   			$str .= '<div class="action orderStato'.$order['Order']['state_code'].'" title="'.__($order['Order']['state_code'].'-intro').'"></div>';
   		else 
   			$str .= '<div class="action orderStato'.$order['state_code'].'" title="'.__($order['state_code'].'-intro').'"></div>';
	 	   	
	   	return $str;
   }
   
   public function drawDesOrdersStateDiv($des_order) {

   		$str = '';   	
   		if(isset($des_order['DesOrder']))	
   			$str .= '<div class="action orderStato'.$des_order['DesOrder']['state_code'].'" title="'.__($des_order['DesOrder']['state_code'].'-intro').'"></div>';
   		else 
   			$str .= '<div class="action orderStato'.$des_order['state_code'].'" title="'.__($des_order['state_code'].'-intro').'"></div>';
	 	   	
	   	return $str;
   }


    public function drawProdGasPromotionsStateDiv($prod_gas_promotion) {

   		$str = '';   	
   		if(isset($prod_gas_promotion['ProdGasPromotion']))	
   			$str .= '<div class="action orderStato'.$prod_gas_promotion['ProdGasPromotion']['state_code'].'" title="'.__($prod_gas_promotion['ProdGasPromotion']['state_code'].'-intro').'"></div>';
   		else 
   			$str .= '<div class="action orderStato'.$prod_gas_promotion['state_code'].'" title="'.__($prod_gas_promotion['state_code'].'-intro').'"></div>';
	 	   	
	   	return $str;
   }
   
   public function drawProdDeliveriesStateDiv($prod_delivery) {
   
   	$str = '';
   	$str .= '<div class="action orderStato'.$prod_delivery['ProdDeliveriesState']['code'].'" title="'.$prod_delivery['ProdDeliveriesState']['intro'].'"></div>';
   	 
   	return $str;
   }
    
   public function drawTooltip($title=null,$text,$type='INFO',$pos='LEFT') {
		if($pos=='LEFT') $pos = 'tooltip-lft';
		else $pos = '';

		$tmp = '';
		$tmp .= '<span class="tooltip-box hidden-xs">';
		if($type=='CRITICAL') {
			if(empty($title)) $title = 'Informazione critica';
			$tmp .= '<a class="mytooltip '.$pos.' tooltip-critical-img" href="#">';
			$tmp .= '<span class="tooltip-custom tooltip-critical"><img src="'.Configure::read('App.img.cake').'/tooltips/48x48/critical.png" alt="'.$title.'" height="48" width="48" /><em>'.$title.'</em>'.$text.'</span></a>';
		}
		else 
		if($type=='HELP') {
			if(empty($title)) $title = 'Informazione per aiutarti';
			$tmp .= '<a class="mytooltip '.$pos.' tooltip-help-img" href="#">';
			$tmp .= '<span class="tooltip-custom tooltip-help"><img src="'.Configure::read('App.img.cake').'/tooltips/48x48/help.png" alt="'.$title.'" height="48" width="48" /><em>'.$title.'</em>'.$text.'</span></a>';
		}
		else 
		if($type=='INFO') {
			if(empty($title)) $title = 'Informazione';
			$tmp .= '<a class="mytooltip '.$pos.' tooltip-info-img" href="#">';
			$tmp .= '<span class="tooltip-custom tooltip-info"><img src="'.Configure::read('App.img.cake').'/tooltips/48x48/info.png" alt="'.$title.'" height="48" width="48" /><em>'.$title.'</em>'.$text.'</span></a>';
		}
		else 
		if($type=='WARNING') {
			if(empty($title)) $title = 'Attenzione';
			$tmp .= '<a class="mytooltip '.$pos.' tooltip-warning-img" href="#">';
			$tmp .= '<span class="tooltip-custom tooltip-warning"><img src="'.Configure::read('App.img.cake').'/tooltips/48x48/warning.png" alt="'.$title.'" height="48" width="48" /><em>'.$title.'</em>'.$text.'</span></a>';
		}
		$tmp .= '</span>';

		return $tmp;
	}

	/*
	 * crea un icona dall'estensione di un file
	 */
	public function drawDocumentIco($file_name, $dim='32x32') {

		$estensione = '';

		if(strpos($file_name,'.')!==false)
			$estensione = substr($file_name, strpos($file_name,'.')+1, strlen($file_name));
		
		switch ($estensione) {
			case "pdf":
				$ico = 'pdf.png';
			break;
			case "jpg":
			case "jpeg":
			case "png":
			case "gif":
				$ico = 'image.png';		
			break;
			case "txt":
				$ico = 'txt.png';
			break;
			case "zip":
				$ico = 'tar.png';
			break;
			case "csv":
			case "xsl":
			case "xlsx":
				$ico = 'spreadsheet.png';
			break;
			default :
				$ico = 'misc.png';
			break;
		}

		$tmp = Configure::read('App.img.cake').'/minetypes/'.$dim.'/'.$ico;
				
		return $tmp;
	}

	/*
	 * $options = ['BO_FE' => 'FE', 'view_coreferente' => 'N']  il front-end non li visualizza
	 */
	public function drawListSuppliersOrganizationsReferents($user, $referents, $options=[]) {
	
		$tmp = '';
	
		$this->d($options);
		$this->d($user);
		
		if(isset($options['BO_FE']))
			$bo_fe = $options['BO_FE'];
		else
			$bo_fe = 'BO';
		
		/*
		 * gestisco la visibilita dei dati dei referenti
		 */
		$view_data = false;
		switch ($bo_fe) {
			case 'BO':
				$view_data = true;
			break;
			case 'FE':
				if($user->org_id == $user->organization['Organization']['id'])  // $user->org_id valorizzato in FE, GAS scelto
					$view_data = true;
				else
					$view_data = false;
			break;
		}
		if($user->id==0)
			$view_data = false;
		
		if(isset($options['view_coreferente']) && $options['view_coreferente']=='N')
			$view_coreferente = 'N';
		else
			$view_coreferente = 'Y';
		
		if(isset($referents) && !empty($referents))
		foreach ($referents as $referent) {
		
			/*
			 * visualizzo solo i SuppliersOrganizationsReferent.type == REFERENTE' nel front-end
			 */
			if($view_coreferente=='Y' || 
			  ($view_coreferente=='N' && $referent['SuppliersOrganizationsReferent']['type']=='REFERENTE')) {
				
				$tmp .= "\n";
				$tmp .= '<span style="cursor:pointer;" title="'.$this->traslateEnum($referent['SuppliersOrganizationsReferent']['type']).'">';
				
				if($view_data) { 
					$userOptions = [];
					$userOptions['title'] = $this->traslateEnum($referent['SuppliersOrganizationsReferent']['type']).' '.$referent['User']['name'];;
					$userOptions['width'] = '20';
					$avatar = $this->drawUserAvatar($user, $referent['User']['id'], $referent['User'], $userOptions);
					if(!empty($avatar))
						$tmp .= $avatar;
					else {
						if($referent['SuppliersOrganizationsReferent']['type']=='REFERENTE')
							$tmp .= '<img style="margin-right:5px;" alt="'.$this->traslateEnum($referent['SuppliersOrganizationsReferent']['type']).' '.$referent['User']['name'].'" title="'.$this->traslateEnum($referent['SuppliersOrganizationsReferent']['type']).' '.$referent['User']['name'].'" src="'.Configure::read('App.img.cake').'/icons/16x16/user.png" />';
						else
						if($referent['SuppliersOrganizationsReferent']['type']=='COREFERENTE')
							$tmp .= '<img style="margin-right:5px;" alt="'.$this->traslateEnum($referent['SuppliersOrganizationsReferent']['type']).' '.$referent['User']['name'].'" title="'.$this->traslateEnum($referent['SuppliersOrganizationsReferent']['type']).' '.$referent['User']['name'].'" src="'.Configure::read('App.img.cake').'/icons/16x16/user_add.png" />';
					}	

					
					if(!empty($referent['User']['email'])) {
						$tmp .= ' <a class="fa fa-envelope-o fa-lg" title="'.__('Email send').' '.$referent['User']['name'].'" target="_blank" href="mailto:'.$this->getPublicMail($user,$referent['User']['email']).'"></a>';
					}
					else
						$tmp .= $referent['User']['name'];	
					
				}
				else {
					if($referent['SuppliersOrganizationsReferent']['type']=='REFERENTE')
						$tmp .= '<img style="margin-right:5px;" alt="'.$this->traslateEnum($referent['SuppliersOrganizationsReferent']['type']).'" src="'.Configure::read('App.img.cake').'/icons/16x16/user.png" />';
					else
					if($referent['SuppliersOrganizationsReferent']['type']=='COREFERENTE')
						$tmp .= '<img style="margin-right:5px;" alt="'.$this->traslateEnum($referent['SuppliersOrganizationsReferent']['type']).'" src="'.Configure::read('App.img.cake').'/icons/16x16/user_add.png" />';
	
					if(!empty($referent['User']['email'])) 
						$tmp .= ' <a class="fa fa-envelope-o fa-lg" title="Loggati per conoscere la sua mail"></a>';				
				}
					
				$tmp .= '</span>';
				$tmp .= '<br />';
			} // end if($referent['SuppliersOrganizationsReferent']['type']=='REFERENTE')
		} // end loop referenti
	
		return $tmp;
	}

	public function drawFormRadioOwnerArticles($user, $model, $fieldName, $options=[]) {

		$disabled_options = [];
		$supplier_owner_articles = $options['options']; 
		$value = $options['value'];
		
		switch($value) {
			case 'SUPPLIER':
				// $disabled_options = ['SUPPLIER', 'REFERENT'];
				$options['after'] = $this->drawTooltip(null,__('toolTipProdGasSupplierOwnerArticles'),$type='HELP');
			break;
			case 'REFERENT':
			case 'DES':
				// $disabled_options = ['SUPPLIER'];
				$options['after'] = $this->drawTooltip(null,__('toolTipProdGasSupplierOwnerArticles'),$type='HELP');
			break;
		}
		$this->d($supplier_owner_articles);
		
		if($user->organization['Organization']['hasDes']=='N') {
			unset($supplier_owner_articles['DES']);
		}
	
		foreach($supplier_owner_articles as $key => $value) 
			$supplier_owner_articles[$key] = $this->traslateEnum('ProdGasSupplier'.$value);
			
		$tmp = '';				
		$tmp .= '<div class="input ';
		if(in_array('required', $options) && $options['required']=='required') {
			$tmp .= 'radio-required'; // mette * rosso dopo la label
			unset($options['required']);
		}			
		$tmp .= '">';
			
		if(isset($options['label']) && $options['label']!=false)
			$tmp .= '<label class="control-label" for="'.$model.'">'.$options['label'].' </label>';
		
		foreach($supplier_owner_articles as $key => $value) {
		
			if(in_array($key, $disabled_options)) 
				$disabled = true;
			else
				$disabled = false;
							
			$tmp .= '<label class="radio-inline" for="'.$model.$fieldName.$key.'" >';
			$tmp .= '<input type="radio" ';
			if(isset($options['class']))
				$tmp .= 'class="'.$options['class'].'" ';
			if(isset($options['data-attr-id']))
				$tmp .= 'data-attr-id="'.$options['data-attr-id'].'" ';
			if($disabled) $tmp .= 'disabled="disabled" ';
			if($options['value']==$key) $tmp .= 'checked="checked" ';
			$tmp .= 'value="'.$key.'" id="'.$model.$fieldName.$key.'" name="data['.$model.']';
			if(strpos($fieldName, '[') === false)
				$tmp .= '['.$fieldName.']';
			else
				$tmp .= $fieldName;
			$tmp .= '" /> ';
			$tmp .= __($value).'</label>';
			
			// if(isset($options['separator'])) $tmp .= $options['separator'];
		}
		if(isset($options['after'])) $tmp .= $options['after'];
		$tmp .= '</div>';

		return $tmp;
	}
	
	public function drawFormRadio($model, $fieldName, $options=[]) {
		$tmp = '';
				
		$this->d($options);
		
		if(isset($options['options'])) {
			$tmp .= '<div class="input ';
			if(in_array('required', $options) && $options['required']=='required') {
				$tmp .= 'radio-required'; // mette * rosso dopo la label
				unset($options['required']);
			}			
			$tmp .= '">';
			
			if(isset($options['label']) && $options['label']!=false)
				$tmp .= '<label class="control-label" for="'.$model.'">'.$options['label'].' </label>';
			
			if(isset($options['disabled']) && $options['disabled']=='disabled')
				$disabled = true;
			else
				$disabled = false;
			
			if(!isset($options['inline']))
				$inline = false;
			else
				$inline = true;
			
			foreach($options['options'] as $key => $value) {
			
				if($inline)
					$tmp .= '<div class="radio"><label for="'.$model.$fieldName.$key.'" >';
				else
					$tmp .= '<label class="radio-inline" for="'.$model.$fieldName.$key.'" >';
					
				$tmp .= '<input type="radio" ';
				if(isset($options['class']))
					$tmp .= 'class="'.$options['class'].'" ';
				if(isset($options['data-attr-id']))
					$tmp .= 'data-attr-id="'.$options['data-attr-id'].'" ';
				if($disabled) $tmp .= 'disabled="disabled" ';
				if($options['value']==$key) $tmp .= 'checked="checked" ';
				$tmp .= 'value="'.$key.'" id="'.$model.$fieldName.$key.'" name="data['.$model.']';
				if(strpos($fieldName, '[') === false)
					$tmp .= '['.$fieldName.']';
				else
					$tmp .= $fieldName;
				$tmp .= '" /> ';
				$tmp .= __($value);

				if($inline)
					$tmp .= '</label></div>';
				else
					$tmp .= '</label>';
				
				// if(isset($options['separator'])) $tmp .= $options['separator'];
			}
			if(isset($options['after'])) $tmp .= $options['after'];
			$tmp .= '</div>';
		}
		return $tmp;
	}

	public function drawFormCheckbox($model,$fieldName,$options = array()) {
		
		/*
		 * $fieldName se inizia per Filter tratto i chechbox dei filtri 
		 */
		if($this->utilsCommons->string_starts_with($fieldName, Configure::read('Filter.prefix'))) {
			$id = $model.$fieldName;
		 	$name = $fieldName;
		 	$nameHidden = $fieldName.'_hidden';
		 	$valueHidden = '';
		 	if(!empty($options['selected'])) { // 1,2,3
		 		$valueHidden = $options['selected'];
		 		$options['selected'] = explode(",",$options['selected']);
		 	}
		}	
		else {
			$id = $model.$fieldName;
			$name = "data[$model][$fieldName]";
			$nameHidden = "data[$model][".$fieldName."_hidden]";
			$valueHidden = '';
			
			if(!empty($options['selected'])) {
				foreach($options['selected'] as $articlesArticlesType) 
					$valueHidden .= $articlesArticlesType['id'].',';
					
					$valueHidden = substr($valueHidden, 0, strlen($valueHidden)-1);
			}
		}

		$tmp = '';
		if(isset($options['options'])) {
			$tmp .= '<div class="input ';
			if(in_array('required', $options) && $options['required']=='required') {
				$tmp .= 'radio-required'; // mette * rosso dopo la label
				unset($options['required']);
			}
			$tmp .= '">';
			
			$tmp .= '<label class="control-label">'.$options['label'].'</label>';
			
			foreach($options['options'] as $key => $value) {

				$tmp .= '<label class="checkbox-inline" for="'.$id.$key.'">';
			
				$tmp .= '<input type="checkbox" ';
				
				if(!empty($options['selected']))
					foreach($options['selected'] as $articlesArticlesType) {
						if(isset($articlesArticlesType['id'])) {
							/* in Article::admin_edit */
							if($articlesArticlesType['id']==$key) $tmp .= 'checked="checked" ';
						}
						else { 
							/* in Article::admin_index, filtro di ricerca */
							if($articlesArticlesType==$key) $tmp .= 'checked="checked" ';
						}
					}
				
				$tmp .= 'value="'.$key.'" id="'.$id.$key.'" name="'.$name.'"> ';
				$tmp .= ' '.$value.'</label>';
							}
			if(isset($options['after'])) $tmp .= $options['after'];
			$tmp .= '</div>';
		}
		
		$tmp .= '<input type="hidden" id="'.$id.$key.'_hidden" name="'.$nameHidden.'" value="'.$valueHidden.'" />';
		
		$tmp .= '<script type="text/javascript">';
		$tmp .= '$(document).ready(function() { ';
		$tmp .= "\r\n";
		$tmp .= '$("input[name=\''.$name.'\']").click(function() { ';
		$tmp .= "\r\n";
		$tmp .= 'var checkbox_id_selected = "";';
		$tmp .= "\r\n";
		$tmp .= 'for(i = 0; i < $("input[name=\''.$name.'\']:checked").length; i++) {';
		$tmp .= "\r\n";
		$tmp .= 'checkbox_id_selected += $("input[name=\''.$name.'\']:checked").eq(i).val()+","; ';
		$tmp .= "\r\n";
		$tmp .= '}';
		$tmp .= "\r\n";
		$tmp .= 'checkbox_id_selected = checkbox_id_selected.substring(0, checkbox_id_selected.length-1);';
		$tmp .= "\r\n";
		$tmp .= '$("#'.$id.$key.'_hidden").val(checkbox_id_selected);';
		$tmp .= "\r\n";
		$tmp .= '});';
		$tmp .= '});';
		$tmp .= '</script>';
		
		return $tmp;		
	}
	
	/*
	 * $fields_name = data_inizio
	 * id = OrderDataInizio
	 */
	public function drawDate($model, $fields_name, $fields_label='', $fields_value='', $options=[]) {

		if(isset($options['required']))
			$required = $options['required'];
		else
			$required = 'false';
		
		$id = str_replace("_"," ",$fields_name);
		$id = ucwords($id);
		$id = str_replace(" ","",$id);
		$id = $model.$id;
		
		$fields_value_label = '';
		if(!empty($fields_value))
			$fields_value_label = $this->Time->i18nFormat($fields_value,"%A, %e %B %Y");
		
		$tmp = '';
		$tmp .= $this->Form->input($fields_name, array('type' => 'text', 'size'=>'30', 'label' => $fields_label, 'value' => $fields_value_label, 'required' => $required, 'autocomplete' => 'off', 'escape' => false));
		$tmp .= $this->Ajax->datepicker($id ,array('dateFormat' => 'DD, d MM yy','altField' => '#'.$id.'Db', 'altFormat' => 'yy-mm-dd'));
		// converto in data perche' se arrivo da drawDateTime ho le ore/minuti
		$fields_value = date('Y-m-d', strtotime($fields_value));
		$tmp .= '<input type="hidden" id="'.$id.'Db" name="data['.$model.']['.$fields_name.'_db]" value="'.$fields_value.'" />';

		return $tmp;		
	}

	public function drawHour($model, $fields_name, $fields_label='', $fields_value='', $options=[]) {
		
		if(isset($options['required']))
			$required = $options['required'];
		else
			$required = 'false';
		
		$tmp = '';
		$tmp .= '<div class="col-md-2">';
		$tmp .= $this->Form->label($fields_label);
		$tmp .= $this->Form->hour($fields_name, true, array('label' => $fields_label, 'value' => $fields_value, 'required' => $required, 'timeFormat'=>'24', 'class' => 'form-control'));
		$tmp .= '</div>';
		$tmp .= '<div class="col-md-3">';
		$tmp .= $this->Form->label('&nbsp;');
		$tmp .= $this->Form->minute($fields_name, array('label' => $fields_label, 'value' => $fields_value, 'required' => $required, 'interval' => 15, 'class' => 'form-control'));
		$tmp .= '</div>';

		return $tmp;		
	}
	
	public function drawDateTime($model, $fields_name, $fields_label='', $fields_value='', $options=[]) {
		
		$tmp = '';
		$tmp .= '<div class="col-md-6">';
		$tmp .= $this->drawDate($model, $fields_name, $fields_label, $fields_value, $options);
		$tmp .= '</div>';
		$tmp .= $this->drawHour($model, $fields_name, '&nbsp;', $fields_value, $options);

		return $tmp;		
	}
	
	public function drawLegenda($user, $states, $debug=false) {	

		$htmlLegenda = '';

		$this->d($states, $debug);

		if(empty($states))
			return $htmlLegenda;
		
		/*
		 * tipologie di legende Orders / DesOrders / ProdGasPromotions
		 */
		$isTemplatesOrdersState = false;
		foreach($states as $numResult => $state) {
			if(isset($state['TemplatesOrdersState'])) {
				$isTemplatesOrdersState = true;
				$states[$numResult] = $state['TemplatesOrdersState'];			
			}
			else
			if(isset($state['TemplatesDesOrdersState'])) 
				$states[$numResult] = $state['TemplatesDesOrdersState'];
			else
			if(isset($state['TemplatesProdGasPromotionsState'])) 
				$states[$numResult] = $state['TemplatesProdGasPromotionsState'];
			else
			if(isset($state['TemplatesProdGasPromotionsGasUsersState'])) 
				$states[$numResult] = $state['TemplatesProdGasPromotionsGasUsersState'];			
		}
		
		$this->d($states, $debug);
		
		
		$colsWidth = floor(100/count($states));
			
		$htmlLegenda = '<div class="legenda">';
		$htmlLegenda .= '<div class="table-responsive"><table class="table">';
		
		/*
		 * solo per gli ordini
 		 */
		if($isTemplatesOrdersState) {
			$htmlLegenda .= "\r\n";
			$htmlLegenda .= '<tr>';
			foreach($states as $state) {
				
				/*
				 * differenzio lo stato CLOSE tra Tesoriere e Cassiere passandogli il group_id
				 */
				$target = __($state['state_code'].'-target');
				if($target==$state['state_code'].'-target')  // e' == perche' non viene trovato
					$target = __($state['state_code'].'-target-PAY'.$user->organization['Organization']['payToDelivery']);
					
				$htmlLegenda .= "\r\n";
				$htmlLegenda .= '<td width="'.$colsWidth.'%"><h3>';
				$htmlLegenda .= $target;
				$htmlLegenda .= '</h3></td>';
			}
			$htmlLegenda .= '</tr>';			
		}
	
		$htmlLegenda .= "\r\n";
		$htmlLegenda .= '<tr>';
		foreach($states as $state) {
			$htmlLegenda .= "\r\n";
			$htmlLegenda .= '<td id="icoOrder'.$state['state_code'].'" class="tdLegendaOrdersStateIco">';
			$htmlLegenda .= '<div style="padding-left:45px;width:80%;cursor:pointer;height:auto;min-height:48px;" class="action orderStato'.$state['state_code'].'" title="'.__($state['state_code'].'-intro').'">'.__($state['state_code'].'-label').'</div>&nbsp;';
			$htmlLegenda .= '</td>';
	
		}
		$htmlLegenda .= '</tr>';
	
		$htmlLegenda .= '<tr>';
		$htmlLegenda .= '<td id="tdLegendaOrdersStateTesto" colspan="'.count($states).'" style="border-bottom:none;background-color:#FFFFFF;height:50px;">';
	
		$htmlLegenda .= "\r\n";
		foreach($states as $state) {
			$htmlLegenda .= "\r\n";
			$htmlLegenda .= '<div class="alert alert-info testoLegendaTesoriereStato" role="alert" id="testoOrder'.$state['state_code'].'" style="display:none;">';
			$htmlLegenda .= __($state['state_code'].'-descri');
			$htmlLegenda .= '</div>';
		}
		$htmlLegenda .= '</td>';
		$htmlLegenda .= '</tr>';
	
		$htmlLegenda .= '</table></div>';
	
	
		$htmlLegenda .= "\r\n";
		$htmlLegenda .= '<script type="text/javascript">';
		$htmlLegenda .= "\r\n";
		$htmlLegenda .= 'function bindLegenda() {';
		$htmlLegenda .= "\r\n";
		foreach($states as $state) {
			$htmlLegenda .= "\r\n";
			$htmlLegenda .= '$( ".orderStato'.$state['state_code'].'" ).mouseenter(function () {';
			$htmlLegenda .= "\r\n";
			$htmlLegenda .= '	$(".tdLegendaOrdersStateIco").css("background-color","#ffffff").css("border-radius","0px");';
			$htmlLegenda .= "\r\n";
			$htmlLegenda .= '	$(".testoLegendaTesoriereStato").hide();';
			$htmlLegenda .= "\r\n";
			$htmlLegenda .= '	$("#icoOrder'.$state['state_code'].'").css("background-color","yellow").css("border-radius","15px 15px 15px 15px");';
			$htmlLegenda .= "\r\n";
			$htmlLegenda .= '	$(".tdLegendaOrdersStateTesto").css("background-color","#F0F0F0");';
			$htmlLegenda .= "\r\n";
			$htmlLegenda .= '	$("#testoOrder'.$state['state_code'].'").show();';
			$htmlLegenda .= "\r\n";
			$htmlLegenda .= '});';
	
			$htmlLegenda .= "\r\n";
			$htmlLegenda .= '$( ".orderStato'.$state['state_code'].'" ).mouseleave(function () {';
			$htmlLegenda .= "\r\n";
			$htmlLegenda .= '	$(".tdLegendaOrdersStateIco").css("background-color","#ffffff");';
			$htmlLegenda .= "\r\n";
			$htmlLegenda .= '	$(".testoLegendaTesoriereStato").hide();';
			$htmlLegenda .= "\r\n";
			$htmlLegenda .= '});';
	
		}
		$htmlLegenda .= "\r\n";
		$htmlLegenda .= '}</script>';
		$htmlLegenda .= '</div>';
		$htmlLegenda .= "\r\n";
		$htmlLegenda .= '<script type="text/javascript">';
		$htmlLegenda .= '$(document).ready(function() {bindLegenda();});';
		$htmlLegenda .= "\r\n";
		$htmlLegenda .= '</script>';
		$htmlLegenda .= "\r\n";
		
	
		return $htmlLegenda;
	}
	
	/*
	 * action = articles_order-'.$result['ArticlesOrder']['order_id'].'-'.$result['ArticlesOrder']['article_id'].$result['ArticlesOrder']['stato']
	 */
	public function drawArticleNotaAjax($rowId, $nota) {
		$str = '';
		
		if(!empty($nota)) {
			if(strlen($nota) > 100) {
				$nota = substr($nota, 0, 100);
				$nota .= '<a id="actionNotaView-'.$rowId.'" class="actionNotaView" href="#" title="'.__('Href_title_expand').'">...</a>';
			}
			$str .= '<div class="small">'.$nota.'</div>';
		} 
		return $str;
	}	

	public function drawOrderStateNext($results) {
		
		$str = '';
		
		if(isset($results['orderStateNext']) && !empty($results['orderStateNext'])) {
			foreach($results['orderStateNext'] as $orderStateNext)
				$str .= $this->Html->link($orderStateNext['label'], $orderStateNext['action'], $orderStateNext['options']);
		}
		
		return $str;
	}
	
	/*
	 * riporta l'ordine allo stato PROCESSED-TESORIERE => annullo i pagamenti, solo per root da TestLifeClycle
	 */
	public function drawOrderStateBackProcessedTesoriere($user, $results) {
		
		$str = '';
		
		if(!empty($results['Order']['request_payment_id']) && ($user->organization['Template']['payToDelivery'] == 'POST' || $user->organization['Template']['payToDelivery']=='ON-POST') &&
			($results['Order']['state_code']=='TO-PAYMENT' || 
			 $results['Order']['state_code']=='USER-PAID' || 
			 $results['Order']['state_code']=='SUPPLIER-PAID' || 
			 $results['Order']['state_code']=='WAIT-REQUEST-PAYMENT-CLOSE' || 
			 $results['Order']['state_code']=='CLOSE'))  {
			$str .= $this->Html->link(__('OrderGoBackStateCodeProcessedTesoriere').' - '.__('OrderGoBackStateCodeProcessedTesoriereNote'), ['controller' => 'OrderLifeCycles', 'action' => 'back_processed_tesoriere', $results['Order']['id']], ['escape' => false, 'class' => 'label label-danger']).'<br /><br />';
		}
		
		return $str;
	}
	
	public function drawOrderBtnPaid($results, $isRoot=false, $isTesoriereGeneric=false) {
		
		$str = '';

		/*
		 * saldato da gasisti 
		 * solo per Organization.orderUserPaid = 'Y'
		 */
		if(isset($results['PaidUsers']['totalSummaryOrder'])) {
			if($results['PaidUsers']['totalSummaryOrder']>0) {
				if($results['PaidUsers']['totalSummaryOrderNotPaid']==0) {
					$label = __('Saldato da tutti i gasisti ').' ('.$results['PaidUsers']['totalSummaryOrderPaid'].')';
					$str .= $this->Html->link($label, ['controller' => 'OrderLifeCycles', 'action' => 'summary_order', $results['Order']['id']], ['class' => 'label label-info','title' => $label]);
				}
				else {
					if($results['PaidUsers']['totalSummaryOrderNotPaid']==$results['PaidUsers']['totalSummaryOrder']) {
						$label = __('Devono saldare tutti i gasisti');
						$str .= $this->Html->link($label, ['controller' => 'OrderLifeCycles', 'action' => 'summary_order', $results['Order']['id']], ['class' => 'label label-danger','title' => $label]);
					}	
					else {
						if($results['PaidUsers']['totalSummaryOrderNotPaid']==1) 
							$label = 'Deve saldare ancora '.$results['PaidUsers']['totalSummaryOrderNotPaid'].' gasista';
						else 
							$label = 'Devono saldare ancora '.$results['PaidUsers']['totalSummaryOrderNotPaid'].' gasisti';
						
						$str .= $this->Html->link($label, ['controller' => 'OrderLifeCycles', 'action' => 'summary_order', $results['Order']['id']], ['class' => 'label label-danger','title' => $label]);
					}
				}
			}
			else {
				$label = __('Non ci sono acquisti');
				$str .= '<span class="label label-danger" title="'.$label.'">'.$label.'</span>';
			}
		}	
					
		/*
		 * pagamento al produttore
		 * solo per Organization.orderSupplierPaid = 'Y'
		 */
		if(isset($results['PaidSupplier']['isPaid'])) { 
		
			$str .= '<p></p>';
		
			if($results['PaidSupplier']['isPaid']) {	
				$label = __('Pagato al produttore');
				if($isTesoriereGeneric) 
					$str .= $this->Html->link($label, ['controller' => 'Tesoriere', 'action' => 'pay_suppliers', null, 'delivery_id='.$results['Order']['delivery_id'], 'order_id='.$results['Order']['id']], ['class' => 'label label-info','title' => $label]);
				else
					$str .= $this->Html->link($label, ['controller' => 'OrderLifeCycles', 'action' => 'pay_suppliers', $results['Order']['id']], ['class' => 'label label-info','title' => $label]);
			}
			else {
				$label = __('Non pagato al produttore');
				if($isTesoriereGeneric) 
					$str .= $this->Html->link($label, ['controller' => 'Tesoriere', 'action' => 'pay_suppliers', null, 'delivery_id='.$results['Order']['delivery_id'], 'order_id='.$results['Order']['id']], ['class' => 'label label-danger','title' => $label]);
				else
					$str .= $this->Html->link($label, ['controller' => 'OrderLifeCycles', 'action' => 'pay_suppliers', $results['Order']['id']], ['class' => 'label label-danger','title' => $label]);
			}
		}
		
		return $str;
	}
		
	public function drawOrderMsgGgArchiveStatics($results) {
		
		$str = '';
		$label = $results['Order']['msgGgArchiveStatics']['mgs'];

		if(isset($results['Order']['msgGgArchiveStatics']['mailto']))
			$str .= '<a href="mailto:'.$results['Order']['msgGgArchiveStatics']['mailto'].'">';
		
		$str .= '<span class="'.$results['Order']['msgGgArchiveStatics']['class'].'" title="'.$label.'">'.$label.'</span>';

		if(isset($results['Order']['msgGgArchiveStatics']['mailto']))
			$str .= '</a>';
		
		return $str;
	}
	
	public function drawArticleNota($id, $nota) {
		$str = '';
		
		//return '<div class="small">'.$nota.'</div>';
		
		if(!empty($nota)) {
			if(strlen($nota) > 100) {
				$notaIntro = substr($nota, 0, 150);
				$notaEnd = substr($nota, 150);
				$notaIntro .= '<a id="articleNotaContinue-'.$id.'" class="actionNotaDetail" title="'.__('Href_title_expand').'">...</a><span id="articleNota-'.$id.'" style="display:none;">'.$notaEnd.'</span>';
				
				$str .= '<div class="small">'.$notaIntro.'</div>';
			}
			else
				$str .= '<div class="small">'.$nota.'</div>';
		} 
		return $str;
	}
	
	public function getArticlePrezzo($prezzo) {
		
		$prezzo = number_format($prezzo,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'&nbsp;&euro;';
		return $prezzo;
	}
	
	public function getArticleConf($qta, $um) {

		/*
		 * qta, da 1.00 a 1
		* 		da 0.75 a 0,75
		* */
		$qta = str_replace(".", ",", $qta);
		if (strpos($qta, ',') !== false) {
			$arrCtrlTwoZero = explode(",",$qta);
			if($arrCtrlTwoZero[1]=='00') $qta = $arrCtrlTwoZero[0];
		}
		
		$um = $this->traslateEnum($um);
				
		$tmp = '';
		$tmp .= $qta.'&nbsp;'.$um;
		
		return $tmp;
	}
	
	public function getArticleImporto($prezzo, $qta) {
		
		if(empty($prezzo)) return $prezzo;
		
		$importo = ($prezzo * $qta); 
		$importo = number_format($importo,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
		
		$tmp = '';
		$tmp .= $importo;
		$tmp .= '&nbsp;&euro;';
		
		return $tmp;
		
	}
	
	/*
	 * il prezzo puo' essere Article.prezzo, ArticleOrder.prezzo, Storeroom.prezzo
	 * qta Article.qta
	 */
	public function getArticlePrezzoUM($prezzo, $qta, $um, $um_riferimento, $debug=false) {
	
		return $this->utilsCommons->getArticlePrezzoUM($prezzo, $qta, $um, $um_riferimento, $debug);
    }
	
	/*
	 * 14 (1 colli da 12 e 2)	
	 */
	public function getColli($tot_qta_single_article, $pezzi_confezione, $debug=false) {
	
		$tmp = '';
		
		if($pezzi_confezione>1) {
			$colli_completi = intval($tot_qta_single_article / $pezzi_confezione);
			if($colli_completi>0) {
				
				if($colli_completi==1) 
					$tmp = " (%s collo da %s %s)";
				else
					$tmp = " (%s colli da %s %s)";
				
				$differenza_da_ordinare = ($tot_qta_single_article - ($pezzi_confezione * $colli_completi));
				if($differenza_da_ordinare != $pezzi_confezione)
					$differenza_da_ordinare = ' e '.$differenza_da_ordinare;
				
				if($tot_qta_single_article==$pezzi_confezione)
					$tmp = " (1 collo da $pezzi_confezione)";
				else
					$tmp = sprintf($tmp, $colli_completi, $pezzi_confezione, $differenza_da_ordinare);
			}
			else {
				if($tot_qta_single_article < $pezzi_confezione)
					$tmp = " (0 colli completati)";
			}
		}
		
		return $tmp;
    }
	
	public function getPublicMail($user, $mail) {
		/*
		 switch ($user->organization['Organization']['id']) {
			case 1:			
			case 2:

			break;
			default:
				$mail = substr($mail,0,strpos($mail,'@')+1)." ".substr($mail,strpos($mail,'@')+1,strlen($mail));
				$mail .= "?body=Attenzione: togliere lo spazio dopo @";
			break;
		}
		*/
		return $mail;
	 }
	 
	 /*
	  * simile codice in ModelArticleType
	  * $results = $results['ArticlesType']
	  */
	 public function isArticlesTypeBio($results) {
	 	 
	 	$count = 0;
	 	$isArticlesTypeBio = false;

	 	if(!empty($results)) {
			foreach($results as $articleType) {
				if($articleType['code']=='BIO' || $articleType['code']=='BIODINAMICO') 
					$count++;
			}
	 	}
	 	
	 	if($count>0) $isArticlesTypeBio = true;
	 	
	 	return $isArticlesTypeBio;
	 }
	 
	 /*
	  * verifica se un utente ha la gestione degli articoli sugli ordini
	 * dipende da
	 * 		- Organization.hasArticlesOrder
	 * 		- User.hasArticlesOrder
	 *
	 * anche in AppController, AppModel
	 */
	public function isUserPermissionArticlesOrder($user) {
	 	if($user->organization['Organization']['hasArticlesOrder']=='Y' && $user->user['User']['hasArticlesOrder']=='Y')
	 		return true;
	 	else
	 		return false;
	}
	 
	function formatBytes($bytes, $precision = 2) {
	 	$units = array('B', 'KB', 'MB', 'GB', 'TB');
	 
	 	$bytes = max($bytes, 0);
	 	$pow = floor(($bytes ? log($bytes) : 0) / log(1024));
	 	$pow = min($pow, count($units) - 1);
	 
	 	// Uncomment one of the following alternatives
	 	//$bytes /= pow(1024, $pow);
	 	 $bytes /= (1 << (10 * $pow));
	 
	 	return round($bytes, $precision) . ' ' . $units[$pow];
	}
}