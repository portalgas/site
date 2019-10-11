<h1>tcpdf</h1>

<a href="http://www.tcpdf.org/" target="_blank">www.tcpdf.org</a>

<p>per fare test</p>

<a href="<?php echo Configure::read('App.server');?>/components/com_cake/app/Vendor/tcpdf/examples/index.php" target="_blank">example sul server</a>

<h2>Problemi</h2>

<p>Aggiunto il codice</p>

<pre class="shell">
	if($this->layout=='pdf') 
		ob_end_clean();
	echo $output->Output($fileData['fileName'].'.pdf', 'D');
</pre>

<ul>
	<li>I: send the file inline to the browser. The plug-in is used if available. The name given by name is used when one selects the "Save as" option on the link generating the PDF.</li>
    <li>D: send to the browser and force a file download with the name given by name.</li>
    <li>F: save to a local file with the name given by name (may include a path).</li>
    <li>S: return the document as a string. name is ignored.</li>
</ul>	

