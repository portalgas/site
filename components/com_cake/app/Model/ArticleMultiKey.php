<?php
App::uses('AppModel', 'Model');

/*
class ArticleMultiKey extends AppModel {

   public $name = 'Article';
   
   var $primaryKeyArray = array('organization_id', 'id');
   var $id = 0;
	
	/*
	 * organization_id = 0 perche' exists richiamato in ModelVAlidation::errors()
	 * gli altri perche' nessun metodo puo' avere piu' parametri richiesti rispetto al suo metodo principale. 
	*/	
   public function exists($organization_id=0, $article_id=0) {

   		if(isset($this->data[$this->alias]['supplier_organization_id'])) $sql .= ",supplier_organization_id";
   		if(isset($this->data[$this->alias]['supplier_id'])) $sql .= ",supplier_id";
   		if(isset($this->data[$this->alias]['prod_gas_article_id'])) $sql .= ",prod_gas_article_id";
   		if(isset($this->data[$this->alias]['codice'])) $sql .= ",codice";
   		if(isset($this->data[$this->alias]['nota'])) $sql .= ",nota";
   		if(isset($this->data[$this->alias]['ingredienti'])) $sql .= ",ingredienti";
   		if(isset($this->data[$this->alias]['prezzo'])) $sql .= ",prezzo";
   		if(isset($this->data[$this->alias]['qta'])) $sql .= ",qta";
   		if(isset($this->data[$this->alias]['um'])) $sql .= ",um";
   		if(isset($this->data[$this->alias]['um_riferimento'])) $sql .= ",um_riferimento";
   		if(isset($this->data[$this->alias]['pezzi_confezione'])) $sql .= ",pezzi_confezione";
   		if(isset($this->data[$this->alias]['qta_minima'])) $sql .= ",qta_minima";
   		if(isset($this->data[$this->alias]['qta_massima'])) $sql .= ",qta_massima";
   		if(isset($this->data[$this->alias]['qta_minima_order'])) $sql .= ",qta_minima_order";
   		if(isset($this->data[$this->alias]['qta_massima_order'])) $sql .= ",qta_massima_order";
   		if(isset($this->data[$this->alias]['bio'])) $sql .= ",bio";
   		if(isset($this->data[$this->alias]['img1'])) $sql .= ",img1";
   		if(isset($this->data[$this->alias]['stato'])) $sql .= ",stato";
   		if(isset($this->data[$this->alias]['flag_presente_articlesorders'])) $sql .= ",flag_presente_articlesorders";
   		
   		if(isset($this->data[$this->alias]['supplier_organization_id'])) $sql .= ",".$this->data[$this->alias]['supplier_organization_id'];
   		if(isset($this->data[$this->alias]['supplier_id'])) $sql .= ",".$this->data[$this->alias]['supplier_id'];
   		if(isset($this->data[$this->alias]['prod_gas_article_id'])) $sql .= ",".$this->data[$this->alias]['prod_gas_article_id'];
   		if(isset($this->data[$this->alias]['prezzo'])) $sql .= ",".$this->importoToDatabase($this->data[$this->alias]['prezzo']);
   		if(isset($this->data[$this->alias]['qta'])) $sql .= ",".$this->importoToDatabase($this->data[$this->alias]['qta']);
   		if(isset($this->data[$this->alias]['um'])) $sql .= ",'".$this->data[$this->alias]['um']."'";
   		if(isset($this->data[$this->alias]['pezzi_confezione'])) $sql .= ",".$this->data[$this->alias]['pezzi_confezione'];
   		if(isset($this->data[$this->alias]['qta_minima'])) $sql .= ",".$this->data[$this->alias]['qta_minima'];
   		if(isset($this->data[$this->alias]['qta_massima'])) $sql .= ",".$this->data[$this->alias]['qta_massima'];
   		if(isset($this->data[$this->alias]['qta_minima_order'])) $sql .= ",".$this->data[$this->alias]['qta_minima_order'];
   		if(isset($this->data[$this->alias]['qta_massima_order'])) $sql .= ",".$this->data[$this->alias]['qta_massima_order'];
   		if(isset($this->data[$this->alias]['qta_multipli'])) $sql .= ",".$this->data[$this->alias]['qta_multipli'];
   		if(isset($this->data[$this->alias]['alert_to_qta'])) $sql .= ",".$this->data[$this->alias]['alert_to_qta'];
   		if(isset($this->data[$this->alias]['bio'])) $sql .= ",'".$this->data[$this->alias]['bio']."'";
   		if(isset($this->data[$this->alias]['img1'])) $sql .= ",'".$this->data[$this->alias]['img1']."'";
   		if(isset($this->data[$this->alias]['stato'])) $sql .= ",'".$this->data[$this->alias]['stato']."'";
   		if(isset($this->data[$this->alias]['flag_presente_articlesorders'])) $sql .= ",'".$this->data[$this->alias]['flag_presente_articlesorders']."'";
   		
   		
   			$success = true;
   		if(isset($this->data[$this->alias]['supplier_organization_id'])) $sql .= " supplier_organization_id = ".$this->data[$this->alias]['supplier_organization_id'].",";
   		if(isset($this->data[$this->alias]['supplier_id'])) $sql .= " supplier_id = ".$this->data[$this->alias]['supplier_id'].",";
   		if(isset($this->data[$this->alias]['prod_gas_article_id'])) $sql .= " prod_gas_article_id = ".$this->data[$this->alias]['prod_gas_article_id'].",";
   		if(isset($this->data[$this->alias]['qta_minima'])) $sql .= " qta_minima = ".$this->data[$this->alias]['qta_minima'].",";
   		if(isset($this->data[$this->alias]['qta_massima'])) $sql .= " qta_massima = ".$this->data[$this->alias]['qta_massima'].",";
   		if(isset($this->data[$this->alias]['qta_minima_order'])) $sql .= " qta_minima_order = ".$this->data[$this->alias]['qta_minima_order'].",";
   		if(isset($this->data[$this->alias]['qta_massima_order'])) $sql .= " qta_massima_order = ".$this->data[$this->alias]['qta_massima_order'].",";
   		if(isset($this->data[$this->alias]['img1'])) $sql .= " img1 = '".$this->data[$this->alias]['img1']."',";
   		if(isset($this->data[$this->alias]['stato'])) $sql .= " stato = '".$this->data[$this->alias]['stato']."',";
   		if(isset($this->data[$this->alias]['flag_presente_articlesorders'])) $sql .= " flag_presente_articlesorders = '".$this->data[$this->alias]['flag_presente_articlesorders']."',";
   		
   		// echo '<br />'.$sql;
   		   

	   	catch (Exception $e) {
}