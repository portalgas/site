<?php
App::uses('AppModel', 'Model');

class Sql extends AppModel {
	
	public $useTable = false;
	
	public function getQuerys($user) {
		
		$results = [];
		$i=0;
		$results[$i]['name'] = "Elenco produttori e GAS associati";
		$results[$i]['sql'] = "SELECT count(*) totGasAssociati, so.name as produttore, so.supplier_id as idProduttore, if(oso.name is null,'-','Si') AccountProduttore FROM ".Configure::read('DB.prefix')."suppliers_organizations so LEFT JOIN ".Configure::read('DB.prefix')."organizations oso on (oso.id = so.owner_organization_id and so.owner_articles='SUPPLIER'), ".Configure::read('DB.prefix')."organizations o WHERE so.supplier_id>0 and o.id = so.organization_id and o.stato = 'Y' and so.stato = 'Y' GROUP BY so.supplier_id, so.name, so.supplier_id, AccountProduttore having count(totGasAssociati) > 1 ORDER BY totGasAssociati desc;";
		$results[$i]['params'] = [];
		$i++;
		$results[$i]['name'] = "Produttore scelto e dettaglio GAS associati";
		$results[$i]['sql'] = "SELECT o.name as GAS, so.name as produttore, s.localita, s.mail, s.www, so.owner_articles as ChiGestisceListino FROM ".Configure::read('DB.prefix')."suppliers_organizations so, ".Configure::read('DB.prefix')."suppliers s, ".Configure::read('DB.prefix')."organizations o WHERE o.id = so.organization_id and s.id = so.supplier_id and so.stato = 'Y' and s.stato IN ('Y', 'T') and so.supplier_id = %s ORDER BY o.name;";
		$results[$i]['params'] = ['supplier_id' => 'ProduttoreId'];
		$i++;
		$results[$i]['name'] = "Produttore scelto e totale acquisti per GAS";
		$results[$i]['sql'] = "SELECT if(c.importo_forzato=0, sum(if(c.qta_forzato=0, c.qta, c.qta_forzato) * ao.prezzo),c.importo_forzato) as totaleAcquistiPerOrdine, ao.order_id as orderId, ao.organization_id as gasIdDellOrdine, o.name as GAS, o.id as GasId FROM ".Configure::read('DB.prefix')."suppliers_organizations so, ".Configure::read('DB.prefix')."carts c, ".Configure::read('DB.prefix')."articles_orders ao, ".Configure::read('DB.prefix')."articles a, ".Configure::read('DB.prefix')."organizations o WHERE o.stato = 'Y' and so.stato = 'Y' and a.organization_id = so.organization_id and o.id = so.owner_organization_id and a.organization_id = so.owner_organization_id and a.supplier_organization_id = so.owner_supplier_organization_id and ao.article_organization_id = a.organization_id and ao.article_id = a.id and c.organization_id = ao.organization_id and c.order_id = ao.order_id and c.article_id = ao.article_id and c.article_organization_id = ao.article_organization_id and c.deleteToReferent = 'N' and so.supplier_id = %s GROUP BY ao.order_id ORDER BY o.name;";
		$results[$i]['params'] = ['supplier_id' => 'ProduttoreId'];
		$i++;
		$results[$i]['name'] = "Elenco produttori con account da produttori";
		$results[$i]['sql'] = "SELECT s.name, s.localita, s.provincia, s.mail, s.nome, s.cognome, s.telefono, s.www FROM ".Configure::read('DB.prefix')."suppliers s, ".Configure::read('DB.prefix')."suppliers_organizations so, ".Configure::read('DB.prefix')."organizations o WHERE o.id = so.organization_id and s.owner_organization_id = o.id and so.supplier_id = s.id and o.type = 'PRODGAS'  ORDER BY s.name;";
		$results[$i]['params'] = [];
		$i++;
		$results[$i]['name'] = "Totale produttori";
		$results[$i]['sql'] = "SELECT count(*) totale from ".Configure::read('DB.prefix')."suppliers WHERE stato in ('Y', 'T');";
		$results[$i]['params'] = [];
		$i++;
		$results[$i]['name'] = "Totale utenti";
		$results[$i]['sql'] = "SELECT count(*) totale from ".Configure::read('DB.portalPrefix')."users WHERE block = 0 and email not like '%portalgas.it';";
		$results[$i]['params'] = [];
		$i++;
		$results[$i]['name'] = "Totale G.A.S.";
		$results[$i]['sql'] = "SELECT count(*) totale FROM ".Configure::read('DB.prefix')."organizations WHERE type = 'GAS' and stato = 'Y';";
		$results[$i]['params'] = [];
		$i++;
		$results[$i]['name'] = "Totale importo degli ordini in statistiche";
		$results[$i]['sql'] = "SELECT sum(importo) as totaleImportoOrdine FROM ".Configure::read('DB.prefix')."stat_orders WHERE DATE_FORMAT(data_inizio,'%Y') = %s;";
		$results[$i]['params'] = ['year' => 'AnnoOrdini'];
		$i++;
		$results[$i]['name'] = "Ultimi articoli modificati di un produttore";
		$results[$i]['sql'] = "SELECT o.name, s.name, s.owner_articles, s.owner_organization_id, s.owner_organization_id, s.owner_supplier_organization_id, a.name, a.modified FROM `k_suppliers_organizations` s, `k_articles` a , k_organizations o WHERE supplier_id = %s and s.id = a.supplier_organization_id and s.organization_id = o.id order by a.modified DESC;";
		$results[$i]['params'] = ['supplier_id' => 'ProduttoreId'];
	
		return $results;
	}
}