<h1>ProdGas</h1><h2>Migrazione da gasisti a produttore</h2><h3>login Gas </h3><ul>	<li>- per ogni user SuppliersOrganizationsReferents::index togliere la referenza</li>	<li>- per ogni user aggiungere gruppo prodGasSupplierManager, togliere gruppo GasPagesGas...., lasciare gruppo Registered</li></ul><h3>Database </h3><ul>	<li>- supplier_id del produttore</li>	<li>- per ogni user settare organization_id = 0 / supplier_id  = {{supplier_id}}</li></ul><h3>login Root, non filtrando per GAS</h3><ul>	<li>- per ogni user aggiungere gruppo prodGasSupplierManager, togliere gruppo GasPagesGas...., lasciare gruppo Registered</li></ul><h3>Filesystem</h3><ul>	<li>/var/www/portalgas/images/prod_gas_articles/{{supplier_id}} 775</li></ul><h2>Importare articoli dal GAS al produttore</h2><h3>login Root, filtrando per il GAS</h3><ul>	<li>- Root => Produttori => Listini articoli gestiti dai produttori</li></ul><h2>Comunicare al GAS</h2><ul>	<li>- Abilitare il produttore a vedere gli ordini</li>	<li>- http://produttore.portalgas.it/</li></ul>