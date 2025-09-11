<?php
App::uses('AppModel', 'Model');

class Sql extends AppModel {
	
	public $useTable = false;
	
	public function getQuerys($user) {
		
		$results = [];
		$i=0;
		$results[$i]['name'] = "Elenco produttori e dati anagrafici";
		$results[$i]['sql'] = "SELECT Supplier.id,Supplier.name,Supplier.provincia,Supplier.telefono,Supplier.mail,Supplier.www FROM ".Configure::read('DB.prefix')."suppliers_organizations as SuppliersOrganization, ".Configure::read('DB.prefix')."suppliers as Supplier, ".Configure::read('DB.prefix')."organizations Organization WHERE SuppliersOrganization.supplier_id = Supplier.id and Supplier.stato = 'Y' and SuppliersOrganization.stato = 'Y' and Organization.id = SuppliersOrganization.organization_id and Organization.type = 'GAS' GROUP BY Supplier.id,Supplier.name,Supplier.provincia,Supplier.telefono,Supplier.mail,Supplier.www ORDER BY SuppliersOrganization.name;";
		$results[$i]['params'] = [];
        $i++;
        $results[$i]['name'] = "Elenco produttori e totale GAS associati";
        $results[$i]['sql'] = "SELECT count(*) totGasAssociati, so.name as produttore, so.supplier_id as idProduttore, if(oso.name is null,'-','Si') AccountProduttore FROM ".Configure::read('DB.prefix')."suppliers_organizations so LEFT JOIN ".Configure::read('DB.prefix')."organizations oso on (oso.id = so.owner_organization_id and so.owner_articles='SUPPLIER'), ".Configure::read('DB.prefix')."organizations o WHERE so.supplier_id>0 and o.id = so.organization_id and o.stato = 'Y' and so.stato = 'Y' GROUP BY so.supplier_id, so.name, so.supplier_id, AccountProduttore having count(totGasAssociati) > 1 ORDER BY totGasAssociati desc;";
        $results[$i]['params'] = [];
        $i++;
        $results[$i]['name'] = "Elenco produttori che gestiscono il listino e totale GAS associati";
        $results[$i]['sql'] = "SELECT count(*) totGasAssociati, so.name as produttore, s.mail, s.localita, s.provincia FROM ".Configure::read('DB.prefix')."suppliers_organizations so INNER JOIN ".Configure::read('DB.prefix')."organizations oso on (oso.id = so.owner_organization_id and so.owner_articles='SUPPLIER'), ".Configure::read('DB.prefix')."organizations o, ".Configure::read('DB.prefix')."suppliers s WHERE so.supplier_id>0 and o.id = so.organization_id and o.stato = 'Y' and so.stato = 'Y' and so.supplier_id = s.id GROUP BY so.supplier_id, so.name, so.supplier_id, s.mail, s.localita, s.provincia ORDER BY totGasAssociati desc;";
        $results[$i]['params'] = [];
        $i++;
        $results[$i]['name'] = "Elenco produttori che gestiscono il listino e GAS associati";
        $results[$i]['sql'] = "SELECT so.name as produttore, s.mail as mailProduttore, s.localita as localitaProduttore, s.provincia as provProduttore, o.name as gas, o.localita as gasLocalita, o.provincia as gasProv, so.supplier_id as idProduttore , so.id as idSupplierOrg FROM ".Configure::read('DB.prefix')."suppliers_organizations so, ".Configure::read('DB.prefix')."suppliers s, ".Configure::read('DB.prefix')."organizations oso, ".Configure::read('DB.prefix')."organizations o WHERE oso.id = so.owner_organization_id and so.owner_articles='SUPPLIER' and so.supplier_id = s.id and so.supplier_id>0 and o.id = so.organization_id and o.stato = 'Y' and so.stato = 'Y' ORDER BY so.name;";
        $results[$i]['params'] = [];
		$i++;
		$results[$i]['name'] = "Produttore scelto e dettaglio GAS associati";
		$results[$i]['sql'] = "SELECT o.name as GAS, so.name as produttore, s.localita, s.mail, s.www, so.owner_articles as ChiGestisceListino FROM ".Configure::read('DB.prefix')."suppliers_organizations so, ".Configure::read('DB.prefix')."suppliers s, ".Configure::read('DB.prefix')."organizations o WHERE o.id = so.organization_id and s.id = so.supplier_id and so.stato = 'Y' and s.stato IN ('Y', 'T') and so.supplier_id = %s ORDER BY o.name;";
		$results[$i]['params'] = ['supplier_id' => 'ProduttoreId'];
		$i++;
		$results[$i]['name'] = "Produttore scelto e totale acquisti per GAS";
		$results[$i]['sql'] = "SELECT sum(if(c.importo_forzato=0, (if(c.qta_forzato=0, c.qta, c.qta_forzato) * ao.prezzo),c.importo_forzato)) as totaleAcquistiPerOrdine, ao.order_id as orderId, ao.organization_id as gasIdDellOrdine, o.name as GAS, o.id as GasId FROM ".Configure::read('DB.prefix')."suppliers_organizations so, ".Configure::read('DB.prefix')."carts c, ".Configure::read('DB.prefix')."articles_orders ao, ".Configure::read('DB.prefix')."articles a, ".Configure::read('DB.prefix')."organizations o WHERE o.stato = 'Y' and so.stato = 'Y' and a.organization_id = so.organization_id and o.id = so.owner_organization_id and a.organization_id = so.owner_organization_id and a.supplier_organization_id = so.owner_supplier_organization_id and ao.article_organization_id = a.organization_id and ao.article_id = a.id and c.organization_id = ao.organization_id and c.order_id = ao.order_id and c.article_id = ao.article_id and c.article_organization_id = ao.article_organization_id and c.deleteToReferent = 'N' and so.supplier_id = %s GROUP BY ao.order_id ORDER BY o.name;";
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
		$results[$i]['name'] = "Totale utenti attivi";
		$results[$i]['sql'] = "SELECT count(*) totale from ".Configure::read('DB.portalPrefix')."users WHERE block = 0 and email not like '%portalgas.it';";
		$results[$i]['params'] = [];
		$i++;
		$results[$i]['name'] = "Totale utenti attivi per G.A.S.";
		$results[$i]['sql'] = "SELECT count(u.id) as totale, o.name , o.id FROM ".Configure::read('DB.portalPrefix')."users u, ".Configure::read('DB.prefix')."organizations o WHERE u.block = 0 and u.email not like '%portalgas.it' and o.type = 'GAS' and o.stato = 'Y' and u.organization_id = o.id  GROUP BY o.name, o.id ORDER BY totale desc;";
		$results[$i]['params'] = [];
		$i++;
        $results[$i]['name'] = "Totale utenti attivi per G.A.S. filtrati per gruppi (group_id 10 GasManager, 11 gasTesoriere)";
        $results[$i]['sql'] = "SELECT o.name, u.id, u.username, u.email FROM ".Configure::read('DB.portalPrefix')."users u,  ".Configure::read('DB.portalPrefix')."user_usergroup_map g , ".Configure::read('DB.prefix')."organizations o where g.user_id = u.id and u.email not like '%portalgas.it' and g.group_id IN (10,11) and o.id = u.organization_id and u.block = 0 and o.stato = 'Y' and o.type = 'GAS' GROUP BY o.name, u.id, u.username, u.email  ORDER BY o.name , u.email DESC;";  // and u.email not like '\%portalgas.it'
        $results[$i]['params'] = []; // non posso parametrizzarlo perche' sprintf in conflitto con u.email not like '%portalgas.it'  ['group_id' => 'GroupIds'];
		$i++;
		$results[$i]['name'] = "Totale G.A.S. attivi";
		$results[$i]['sql'] = "SELECT count(*) totale FROM ".Configure::read('DB.prefix')."organizations WHERE type = 'GAS' and stato = 'Y';";
		$results[$i]['params'] = [];
		$i++;
		$results[$i]['name'] = "Totale importo degli ordini in statistiche";
		$results[$i]['sql'] = "SELECT sum(importo) as totaleImportoOrdine FROM ".Configure::read('DB.prefix')."stat_orders WHERE DATE_FORMAT(data_inizio,'%Y') = %s;";
		$results[$i]['params'] = ['year' => 'AnnoOrdini'];
		$i++;
		$results[$i]['name'] = "Totale Ordini eliminati";
		$results[$i]['sql'] = "SELECT count(b.id) as totale, o.id, o.name FROM ".Configure::read('DB.prefix')."backup_orders_orders b, ".Configure::read('DB.prefix')."organizations o WHERE b.organization_id = o.id GROUP BY o.id, o.name ORDER BY totale;";
		$results[$i]['params'] = [];
		$i++;
		$results[$i]['name'] = "Ultimi articoli modificati di un produttore";
		$results[$i]['sql'] = "SELECT o.name, s.name, s.owner_articles, s.owner_organization_id, s.owner_organization_id, s.owner_supplier_organization_id, a.name, a.modified FROM ".Configure::read('DB.prefix')."suppliers_organizations s, ".Configure::read('DB.prefix')."articles a , ".Configure::read('DB.prefix')."organizations o WHERE supplier_id = %s and s.id = a.supplier_organization_id and s.organization_id = o.id order by a.modified DESC;";
		$results[$i]['params'] = ['supplier_id' => 'ProduttoreId'];
		$i++;
		$results[$i]['name'] = "Invio mail a tutti con totali utenti";
		$results[$i]['sql'] = "SELECT o.name, o.id, count(u.id) as tot_users, dest_options, m.created FROM k_mails m, k_organizations o, j_users u where m.organization_id = o.id and o.type = 'GAS' and o.stato = 'Y' and o.id = u.organization_id and u.block = 0 and dest_options_qta = 'ALL' and year(m.created) = %s group by  m.created, o.id, o.name, dest_options, dest_options_qta order by m.created desc;";
		$results[$i]['params'] = ['year' => 'AnnoMailSend'];
		$i++;
		$results[$i]['name'] = "Ctrl - Ordini DES senza + ordine, se si trovano eliminarli da k_des_orders_organizations";
		$results[$i]['sql'] = "SELECT organization_id, order_id FROM ".Configure::read('DB.prefix')."des_orders_organizations WHERE  order_id NOT IN (SELECT ID FROM k_orders);";
		$results[$i]['params'] = [];
        $i++;
        $results[$i]['name'] = "Ordini raggruppati per anno di creazione";
        $results[$i]['sql'] = "SELECT count(id), year(created) FROM ".Configure::read('DB.prefix')."orders GROUP BY year(created) ORDER BY year(created) ASC;";
        $results[$i]['params'] = [];
        $i++;
        $results[$i]['name'] = "SocialMarket - Produttori in socialmarket";
        $results[$i]['sql'] = "SELECT id, supplier_id, name, owner_articles, owner_organization_id, owner_supplier_organization_id FROM ".Configure::read('DB.prefix')."suppliers_organizations where organization_id = 142 and stato = 'Y' order by name;";
        $results[$i]['params'] = [];
        $i++;
        $results[$i]['name'] = "SocialMarket - G.A.S. per socialmarket";
        $results[$i]['sql'] = "SELECT id, name, indirizzo, localita, cap, provincia, telefono, mail FROM ".Configure::read('DB.prefix')."organizations where stato = 'Y' and type = 'GAS' order by name;";
        $results[$i]['params'] = [];
        $i++;
        $results[$i]['name'] = "SocialMarket - Produttori abilitati per i G.A.S. (sono gia' esclusi quelli associati al G.A.S.)";
        $results[$i]['sql'] = "SELECT s.localita as supplierLocalita, s.provincia as supplierProv, so.name as supplierName, o.name, o.localita as gasLocalita, o.provincia as gasProv FROM socialmarket_organizations, ".Configure::read('DB.prefix')."suppliers_organizations as so, ".Configure::read('DB.prefix')."suppliers as s, ".Configure::read('DB.prefix')."organizations as o where socialmarket_organizations.supplier_organization_id = so.id and socialmarket_organizations.organization_id = o.id and s.id = so.supplier_id;";
        $results[$i]['params'] = [];
        $i++;
        $results[$i]['name'] = "Consegne e gruppi di consenge senza ordini, di 100gg trigger gas_group_deliveries_AFTER_DELETE => k_deliveries";
        $results[$i]['sql'] = "SELECT gas_group_deliveries.id, k_deliveries.data, k_deliveries.luogo, k_organizations.name, k_organizations.id FROM k_organizations, gas_group_deliveries, k_deliveries left join k_orders on (k_deliveries.organization_id = k_orders.organization_id and k_deliveries.id = k_orders.delivery_id) where k_organizations.type = 'GAS' and k_organizations.id = 156 and gas_group_deliveries.delivery_id = k_deliveries.id and gas_group_deliveries.organization_id = k_deliveries.organization_id and k_deliveries.sys = 'N' and DATE(k_deliveries.data) <= CURDATE() - INTERVAL %s DAY and k_orders.id is null order by k_deliveries.data desc;";
        $results[$i]['params'] = ['days' => 'days'];
        $i++;
        $results[$i]['name'] = "Consegne senza ordini, di 100gg ";
        $results[$i]['sql'] = "SELECT k_deliveries.id, k_deliveries.data, k_deliveries.luogo, k_organizations.name, k_organizations.id FROM k_organizations, k_deliveries left join k_orders on (k_deliveries.organization_id = k_orders.organization_id and k_deliveries.id = k_orders.delivery_id) where k_organizations.type = 'GAS' and k_organizations.id != 156 and k_organizations.id = k_deliveries.organization_id and k_deliveries.sys = 'N' and DATE(k_deliveries.data) <= CURDATE() - INTERVAL %s DAY and k_orders.id is null order by k_deliveries.data desc;";
        $results[$i]['params'] = ['days' => 'days'];
        $i++;
        $results[$i]['name'] = "Users gmap ripulire i campi per eseguire update j_user_profiles set profile_value = '\"\"' where (profile_key= 'profile.lat' or profile_key= 'profile.lng') and user_id in (); e /var/portalgas/cron/usersGmaps.sh e localizzarli";
        $results[$i]['sql'] = "SELECT * FROM j_users, j_user_profiles where ornanization_id = %s and user_id = j_users.id and profile_key= 'profile.lng' and profile_value = '\"0.0\"';";
        $results[$i]['params'] = ['ornanization_id' => 'ornanization_id'];

		return $results;
	}
}