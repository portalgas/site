<?php require('_inc_header.php');?>
 
    <div class="container">
      

        <div class="col-sm-8 cakeContainer" role="main">

		<h1 id="ambienti">Elenco documenti da scaricare</h1>
			<div id="doc">
			<ul>
				<li>
					<a href="http://www.portalgas.it/manuali/doc/Presentazione-1-slide.pdf" target="_blank">
						<img src="http://www.portalgas.it/images/cake/minetypes/32x32/pdf.png" />
						<h3>Presentazione slide</h3>
						<p>Un breve volo sull gestionale web per Gruppi d'acquisto solidale e D.E.S.</p></a>
				</li>
				<li>
					<a href="http://www.portalgas.it/manuali/doc/Presentazione-3-Tesoriere.pptx" target="_blank">
						<img src="http://www.portalgas.it/images/cake/minetypes/32x32/schedule.png" />
						<h3>Tesoriere</h3>
						<p>Per la gestione dei GAS con pagamento dopo la consegna</p></a>
				</li>
				<li>
					<a href="http://www.portalgas.it/manuali/doc/Presentazione-DES.pptx" target="_blank">
						<img src="http://www.portalgas.it/images/cake/minetypes/32x32/schedule.png" />
						<h3>D.E.S.</h3>
						<p>Distretto economia sociale</p></a>
				</li>
				<li>
					<a href="http://www.portalgas.it/manuali/doc/Presentazione-Mobile.pptx" target="_blank">
						<img src="http://www.portalgas.it/images/cake/minetypes/32x32/schedule.png" />
						<h3>Versione mobile</h3>
						<p>Per Ios, Android e versione mobile</p></a>
				</li>
			</ul>
			</div>
			
		<p></p>
		
		</div> <!-- col-sm-8 -->
		
		<div class="col-sm-3" role="complementary">


		
		</div> <!-- col-sm-3 -->
		
	
	</div>	  <!-- container -->
 
 <style>
#doc ul li a {text-decoration: none;}

#doc ul {
  list-style-type: none;
  width: 500px;
}

#doc h3 {
  font-size: 16px;
}

#doc li img {
  float: left;
  margin: 0 15px 0 0;
}

#doc li p {
  font-size: 12px;
}

#doc li {
  padding: 10px;
  overflow: auto;
}

#doc li:hover {
  background: #eee;
  cursor: pointer;
}
 </style>

<?php require('_inc_footer.php');?>