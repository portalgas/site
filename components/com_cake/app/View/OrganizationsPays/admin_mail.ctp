<div class="organizations_pays">
	<h2 class="ico-organizations">
		<?php echo __('Organizations');?>
		<div class="actions-img">
			<ul>
				<li><?php echo $this->Html->link(__('List Organizations'), array('controller' => 'Organizations', 'action' => 'index'),array('class' => 'action actionReload','title' => __('List Organizations'))); ?></li>
			</ul>
		</div>
	</h2>
	
	<div>

<pre class="shell" rel="creare cartella per pdf">
mkdir /var/www/portalgas/images/pays/<?php echo date('Y');?>
</pre>

<pre class="shell" rel="sql per attivare il messaggio">
update k_organizations set hasMsg='Y' where `type` = 'GAS' AND `hasMsg` = 'Y' AND `stato` = 'Y';
</pre>

<pre class="shell" rel="sql per estrarre i manager (10) e tesorieri (11)">
select o.name, u.organization_id, u.email, u.name  
from j_users u,
j_user_usergroup_map g, k_organizations o
where g.group_id in (10, 11)
and g.user_id = u.id
and u.organization_id = o.id
and o.stato = 'Y' and o.type = 'GAS'
and u.email not like '%.portalgas.it'
group by u.organization_id, u.email, u.name;</pre>

	</div>

<pre class="shell" rel="mail">
pagamento PortAlGas <?php echo date('Y');?>

Ciao,  ( 😉 )

Vorremmo procedere con la richiesta della vostra quota per il <?php echo date('Y');?>.
Vi chiediamo pertanto di verificare i Codici Fiscali; Indirizzi ed Email, Nominativi etc, se da modificare lo potete fare in autonomia in Portalgas da Home Gas\Il proprio Gas\Dati per il Pagamento ) 

Ricordo le fasce per numero di utenti che sono: 
da 1 a 25 utenti 25 €
da 26 a 50 utenti 50 €
da 51 a 75 utenti 75 €
da 76 a 100 gasisti 100 €
da 101 a 150 gasisti 125 €
da 151 a 175 gasisti 150 €
da 176 a 200 gasisti 175 €
da 201 a 225 gasisti 200 €
da 226 a 250 gasisti 225 €
da 251 a 275 gasisti 250 €
e così via

a breve i Manager e i tesorieri, quando si loggano al sito lato amministrativo https://www.portalgas.it/my, vedranno un banner contenente il Link per scaricare la Ricevuta, l'indicazione a chi effettuare il pagamento IBAN oppure la possibilità di pagare tramite Satispay.

Nota: al pagamento seguirà una ricevuta di pagamento.
Se avete bisogno della ritenuta di pagamento dovrete versare tramite F24 la ritenuta del 20% (le ritenute vanno versate dai datori di lavoro (sostituti d'imposta) <b>entro il 16 del mese successivo a quello del pagamento</b>), l'anno successivo dovrete inviarci La Certificazione unica (c.d. “modello Cu“) <b>è il documento con il quale i sostituti d'imposta (committenti) sono chiamati a certificare le ritenute di acconto</b>.

Grazie 
Ciao a tutti 
Marco & Francesco 
</div>


</div>