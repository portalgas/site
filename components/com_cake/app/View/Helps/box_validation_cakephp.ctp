<h1>CakePhp Validation</h1>

<pre class="shell" rel="/var/cakephp/Cake/Utility/Validation.php">
	 forzo
		 $data['thousands_sep'] = '.';
		 $data['decimal_point'] = ','; 

	'prezzo' => [
	   'rule' => ['decimalIT', 2],
	   'message' => "Indica il prezzo dell'articolo con un valore numerico con 2 decimali (1,00)",
	  ], 	
</pre>