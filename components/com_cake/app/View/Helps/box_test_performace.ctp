<h1>Test performace</h1><pre class="cake-debug">[Soluzione] in PHP setting settare zlib.output_compression a On</pre><pre class="cake-error">[Attenzione] installando l'ultima versione di php 5.4.29-1~dotdeb.0, apache2filter non supporta zlib.output_compression</pre>			<pre class="shell" rel="test per zlib"> # curl -I "https://www.portalgas.it/phpinfo.php" -H "Accept-Encoding: gzip,deflate" HTTP/1.1 200 OK Server: Apache Accept-Ranges: bytes Vary: Accept-Encoding,User-Agent Content-Encoding: gzip Content-Length: 20 Content-Type: text/html; charset=utf-8 # curl -I "https://www.portalgas.it/phpinfo.php" -H "Accept-Encoding: gzip,deflate" HTTP/1.1 200 OK Server: Apache Accept-Ranges: bytes Transfer-Encoding: chunked</pre>		<h2>Tabs delle consegne - path: /home-cavagnetta/consegne-gas-cavagnetta</h2><table>	<tr>		<th>Configurazione</th>		<th>Richieste</th>		<th>Dimensione</th>		<th>Time</th>		<th>Esito</th>	</tr>	<tr>		<td>Template completo</td>		<td>44</td>		<td>844.2 kb</td>		<td>2.1s (onloads 6.14s)</td>		<td class="stato_close"></td>	</tr>	<tr>		<td>Template completo<br />			css-min js-min		</td>		<td>36</td>		<td>478.3 kb</td>		<td>1.36s (onloads 5.9s)</td>		<td class="stato_close"></td>	</tr>	<tr>		<td>Template solo &lt;jdoc:include type="component" / &gt;</td>		<td>9</td>		<td>424.3 kb</td>		<td>1.04s (onloads 1.16s)</td>		<td class="stato_open"></td>		</tr>	<tr>		<td>Template solo &lt;jdoc:include type="component" / &gt; 			<br />con css/js in default.ctp		</td>		<td>23</td>		<td>433.9 kb</td>		<td>1.63s (onloads 1.71s)</td>		<td class="stato_open"></td>	</tr>	<tr>		<td>Template completo <br />			url ?tmpl=component		</td>		<td>32</td>		<td>502.7 kb</td>		<td>1.9s (onloads 1.95s)</td>		<td class="stato_open"></td>	</tr></table><h2>Tabs delle consegne - path: /home-cavagnetta/consegne-gas-cavagnetta</h2><table>	<tr>		<th>Configurazione</th>		<th>Richieste</th>		<th>Dimensione</th>		<th>Time</th>		<th>Esito</th>	</tr>	<tr>		<td>Template completo<br />		2 tabs per 11 produttori</td>		<td>37</td>		<td>476.0 kb</td>		<td>1.49s (onloads 1.65s)</td>		<td class="stato_open"></td>	</tr>	<tr>		<td>Template completo<br />		3 tabs per 27 produttori</td>		<td>37</td>		<td>477.9 kb</td>		<td>1.09s (onloads 5.65s)</td>		<td class="stato_close"></td>	</tr></table>	<h2>Tabs degli acquisti - path:/home-cavagnetta/carrello-gas-cavagnetta?tmpl=component</h2><table>	<tr>		<th>Configurazione</th>		<th>Richieste</th>		<th>Dimensione</th>		<th>Time</th>		<th>Esito</th>	</tr>	<tr>		<td>Template completo <br />			url ?tmpl=component		</td>		<td>20</td>		<td>521.1 kb</td>		<td>1.4s (onloads 6.89s)</td>		<td class="stato_close"></td>	</tr></table><h2>Pagina Admin elenco articoli /administrator/index.php?option=com_cake&controller=Articles&action=context_articles_index</h2>	<table>	<tr>		<th>Configurazione</th>		<th>Richieste</th>		<th>Dimensione</th>		<th>Time</th>		<th>Esito</th>	</tr>	<tr>		<td>Tutti gli articoli</td>		<td>77</td>		<td>893.2 kb</td>		<td>2.72s (onloads 6.22s)</td>		<td class="stato_close"></td>	</tr>	<tr>		<td>Filtri bio: 2 articoli</td>		<td>77</td>		<td>889.5 kb</td>		<td>2.35s (onloads 5.83s)</td>		<td class="stato_close"></td>	</tr></table><h2>Pagina interna senza cake - path:/home-cavagnetta/per-chi-non-e-socio</h2><table>	<tr>		<th>Configurazione</th>		<th>Richieste</th>		<th>Dimensione</th>		<th>Time</th>		<th>Esito</th>	</tr>	<tr>		<td>Template completo</td>		<td>35</td>		<td>844.5 kb</td>		<td>1.76s (onloads 1.93s)</td>		<td class="stato_open"></td>	</tr>	<tr>		<td>Template completo<br />			css-min js-min		</td>		<td>31</td>		<td>570.0 kb</td>		<td>1.27s (onloads 1.37s)</td>		<td class="stato_open"></td>	</tr></table><h2>Pagina produttore - path:/home-cavagnetta/produttori/31-pesce/12-bio-e-mare<br />e chiamate Ajax: <ul>	<li>/?option=com_cake&controller=Ajax&action=modules_suppliers_organization_details&organization_id=1&j_content_id=12&format=notmpl (219ms)</li>	<li>/?option=com_cake&controller=Ajax&action=modules_suppliers_articles&organization_id=1&j_content_id=12&format=notmpl (406 ms)</li></ul></h2><table>	<tr>		<th>Configurazione</th>		<th>Richieste</th>		<th>Dimensione</th>		<th>Time</th>		<th>Esito</th>	</tr>	<tr>		<td>Template completo</td>		<td>20</td>		<td>540.3 kb</td>		<td>1.66s (onloads 1.19s)</td>		<td class="stato_open"></td>	</tr></table>