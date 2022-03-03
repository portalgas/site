<div class="container-fluid text-center">
  <div class="row">

  		<div class="col-xs-12 col-sm-4 col-md-3">
			<div class="box-container">
				<a href="/administrator/index.php?option=com_cake&amp;controller=ProdGasSuppliers&action=index">
					<div class="box">
						<span class="fa fa-3x fa-users"></span>
						<h4>Elenco G.A.S.</h4>
						<p>Elenco dei G.A.S. associati al produttore</p>
					</div>	
				</a>
			</div>
		</div>
		<div class="col-xs-12 col-sm-4 col-md-3">
			<div class="box-container">
				<a href="/administrator/index.php?option=com_cake&amp;controller=Articles&amp;action=context_articles_index">
					<div class="box">
						<span class="fa fa-3x fa-cubes"></span>
						<h4>Articoli</h4>
						<p>Gestisci l'anagrafica degli articoli</p>
					</div>	
				</a>
			</div>
		</div>	
		<div class="col-xs-12 col-sm-4 col-md-3">
			<div class="box-container">
				<a href="/administrator/index.php?option=com_cake&amp;controller=ProdGasPromotions&amp;action=index_gas">
					<div class="box">
						<span class="fa fa-3x fa-magic"></span>
						<h4>Promozioni</h4>
						<p>Gestisci le promozioni ai G.A.S.</p>
					</div>	
				</a>
			</div>
		</div>
		<div class="col-xs-12 col-sm-4 col-md-3">
			<div class="box-container">
				<a href="/administrator/index.php?option=com_cake&amp;controller=ProdGasPromotions&amp;action=index_gas_users">
					<div class="box">
						<span class="fa fa-3x fa-magic"></span>
						<h4>Promozioni</h4>
						<p>Gestisci le promozioni ai singoli utenti</p>
					</div>	
				</a>
			</div>
		</div>
		<?php
		if(isset($user->organization['Organization']['hasArticlesGdxp']) && 
		  $user->organization['Organization']['hasArticlesGdxp']=='Y') {
		?>
				<div class="col-xs-12 col-sm-4 col-md-3">
					<div class="box-container">
						<a href="/administrator/index.php?option=com_cake&controller=Connects&action=index&c_to=admin/gdxps&a_to=articlesSendIndex">
							<div class="box">
								<span class="fa fa-3x fa-send"></span>
								<h4>Trasmetti listino</h4>
								<p>a <a target="_blank" href="https://produttore.portalgas.it/#trasmissione">www.economiasolidale.net</a></p>
							</div>	
						</a>
					</div>
				</div>
		<?php
		  }
		?>			
		<div class="col-xs-12 col-sm-4 col-md-3">
			<div class="box-container">
				<a target="_blank" href="https://www.facebook.com/portalgas.it">
					<div class="box">
						<span class="fa fa-3x fa-facebook"></span>
						<h4>Facebook</h4>
						<p>Rimani aggiornato seguendoci su Facebook</p>
					</div>	
				</a>
			</div>
		</div>
    <div class="col-xs-12 col-sm-4 col-md-3">
	    <div class="box-container">
    		<a href="https://produttore.portalgas.it" target="_blank">
	    		<div class="box">
    		  		<span class="fa fa-3x fa-info-circle"></span>
      				<h4>Manuale</h4>
      				<p>Se alcuni passaggi non vi sono chiari</p>
    			</div>	
    		</a>
    	</div>
    </div>
		<div class="col-xs-12 col-sm-4 col-md-3">
			<div class="box-container">
				<a target="_blank" href="https://www.youtube.com/channel/UCo1XZkyDWhTW5Aaoo672HBA">
					<div class="box">
						<span class="fa fa-3x fa-youtube"></span>
						<h4>YouTube</h4>
						<p>I video tutorial sul canale YouTube</p>
					</div>	
				</a>
			</div>
		</div>

	</div>
</div>