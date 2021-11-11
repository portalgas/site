<h1>CakePhp</h1>

Creare codice da shell
<pre class="shell" rel="windows">
	C:\xampp\htdocs\portalgas\components\com_cake\app o 
	C:\xampp\htdocs\portalgas\components\com_cake\app2
	Console\cake bake all
	in /components/com_cake/app/webroot/index.php commentare CAKE_CORE_INCLUDE_PATH
</pre>

<p>sul pc con window ho php 5.2 e gli array [] danno errore</p>
<p>
<b>Libreria di cake</b> in /var/www/portalgas/components/com_cake/lib e gitignore
	<br />
rinominata la classe <b>.lib/Cake/Core/Object</b> in MyObject per PHP Fatal error:  Cannot use 'Object' as class name as it is reserved 
</p>
<pre class="shell" rel="ubuntu">
	/var/www/portalgas/components/com_cake/app
	./Console/cake.sh bake
</pre>

