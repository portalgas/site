<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>PortAlGas - manuale produttori</title>

	<link rel="stylesheet" href="http://www.portalgas.it/components/com_cake/app/webroot/css/styleBackoffice-v05-min.css">

	<link rel="stylesheet" href="css/bootstrap.css">
	<!-- link rel="stylesheet" href="css/bootstrap-theme.min.css" -->
	
    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->

<style>
body {
	min-height: 2000px;
}
.footer {
    background-color: #000;
    color: #fff;
    height: 60px;
    padding: 20px;
}
.footer .copyright {
	text-align: right;
	color: #777;
}

/*
 *  barra di navigazione
 */
.navbar {
    background-color: #0a659e;
}
.navbar-default .navbar-nav > li > a  {
	color: #fff;
    text-decoration: none;
}
.navbar-default .navbar-nav > li > a:hover {
	color: #fff;
	background: none repeat scroll 0 0 rgba(255, 255, 255, 0.2);
	text-decoration: none;
}
.navbar-default .navbar-nav > li[class*="active"] > a {
    color: #fff;
	background: none repeat scroll 0 0 rgba(255, 255, 255, 0.4);
}
.navbar-default .navbar-brand {
    color: #fff;
}

/*
 * bootstrap
 */
h1, .h1 {
    font-size: 36px !important;
}
h3, .h3 {
    font-size: 24px !important;
}
h1, .h1, h2, .h2, h3, .h3 {
    margin-bottom: 10px !important;
    margin-top: 20px !important;
}
h1, h2, h3, h4, h5, h6, .h1, .h2, .h3, .h4, .h5, .h6 {
    color: inherit !important;
    font-family: inherit !important;
    font-weight: 500 !important;
    line-height: 1.1 !important;
}

/*
 *  style.css
 */
.cakeContainer th {
	border-bottom:2px solid #555;
	background-color: #F5F5F5;
}
.cakeContainer h2 {
    color: #000;
    font-size: 30px;
    height:auto;
}
.cakeContainer .action {
    display: inline-block;
    float: none;
    height: 32px;
    margin-right: 0;
    width: 32px;
}
.cakeContainer .action img {
	border: 0;
}
.cakeContainer .actionTrView {
    background: url("http://www.portalgas.it/images/cake/actions/32x32/viewmag+.png") no-repeat scroll 0 0 transparent;
}
.cakeContainer .actionTrConfig {
    background: url("http://www.portalgas.it/images/cake/actions/32x32/configure.png") no-repeat scroll 0 0 transparent;
}
.cakeContainer .actionOpen {
    background: url("http://www.portalgas.it/images/cake/actions/32x32/open_store.png") no-repeat scroll 0 0 transparent !important;
    cursor: pointer;
}
.cakeContainer .stato_si, .cakeContainer .stato_0, .cakeContainer .stato_y, .cakeContainer .stato_open {
    background: url("http://www.portalgas.it/images/cake/icons/16x16/tick.png") no-repeat scroll center center transparent;
    height: 16px;
    padding-left: 8px;
}
.cakeContainer .stato_no, .cakeContainer .stato_1, .cakeContainer .stato_n, .cakeContainer .stato_close {
    background: url("http://www.portalgas.it/images/cake/icons/16x16/cross.png") no-repeat scroll center center transparent;
    height: 16px;
    padding-left: 8px;
}
.cakeContainer .stato_t, .cakeContainer .stato_temporaneo {
    background: url("http://www.portalgas.it/images/cake/icons/16x16/eye.png") no-repeat scroll center center transparent;
    height: 16px;
    padding-left: 8px;
}
/*
 * menu laterale
 */
ul.nav-contenitore {
    border: 1px solid #ddd;
    border-radius: 4px;
    box-shadow: 0 1px 4px rgba(0, 0, 0, 0.067);
}
ul.nav-contenitore li {
	margin: 0;
	border-top: 1px solid #ddd;
}
ul.nav-contenitore li:first-child {
	border-top: none;
}
ul.nav-contenitore li a {
	margin: 0;
	padding: 8px 16px;
	border-radius: 0;
}
ul.nav-contenitore li.active > a, ul.nav li.active > a:hover {
	color: #fff;
	background: #0088cc;
	border: 1px solid #0088cc;
}
ul.nav-contenitore li:first-child a {
	border-radius: 4px 4px 0 0;
}
ul.nav-contenitore li:last-child a {
	border-radius: 0 0 4px 4px;
}
ul.nav-contenitore.affix {
	top: 30px; /* Set the top position of pinned element */
}
ul.nav-contenitore > .active > ul {
    display: block;
}
ul.nav-contenitore .nav > li > a {
    font-size: 12px;
    font-weight: 400;
    padding-bottom: 1px;
    padding-left: 30px;
    padding-top: 1px;
}
ul.nav-contenitore > .active > a, ul.nav-contenitore > .active:hover > a, ul.nav-contenitore > .active:focus > a {
    background-color: transparent;
    border-left: 2px solid #563d7c;
    color: #563d7c;
    font-weight: 700;
    padding-left: 18px;
}
</style>

  </head>
  <body data-spy="scroll" data-target="#myScrollspy">

  


  
   <nav role="navigation" class="navbar navbar-default">
   
		<div class="container">
		
          <div class="navbar-header">
            <button type="button" data-target="#navbarCollapse" data-toggle="collapse" class="navbar-toggle">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand hidden-xs" href="index.php" style="padding: 0px; margin: 0px;">
				<img src="http://www.portalgas.it/images/cake/loghi/0/150h50.png" alt="Gestionale web per G.A.S. (GAS gruppo d'acquisto solidale) e D.E.S. (DES distretto economia solidale)" class="img-responsive"></a>
        </div>
        <div id="navbarCollapse" class="collapse navbar-collapse">

            <ul class="nav navbar-nav navbar-right">
				<!-- li class="dropdown">
					<a aria-expanded="true" aria-haspopup="true" role="button" data-toggle="dropdown" class="dropdown-toggle" href="#">Menù <span class="caret"></span></a>
					<ul class="dropdown-menu">
						<li><a href="gestione_degli_articoli.php">Articoli</a></li>
					</ul>
				</li -->
				<li class="dropdown">
					<a aria-expanded="true" aria-haspopup="true" role="button" data-toggle="dropdown" class="dropdown-toggle" href="#">Link <span class="caret"></span></a>
					<ul class="dropdown-menu">           
						<li><a target="_blank" href="http://www.portalgas.it/">Version desktop</a></li>
						<li><a target="_blank" href="http://www.portalgas.it/mobile/#/app/home">Version mobile</a></li> 
						<li><a target="_blank" href="https://www.facebook.com/portalgas.it">Facebook</a></li> 
						<li><a target="_blank" href="https://www.youtube.com/channel/UCo1XZkyDWhTW5Aaoo672HBA">YouTube</a></li> 
						<li><a target="_blank" href="https://play.google.com/store/apps/details?id=com.ionicframework.portalgas">GoogleStore</a></li>	
						<li><a target="_blank" href="https://itunes.apple.com/us/app/portalgas/id1133263691">AppleStrore</a></li>		
					</ul>
				</li>				
				<li></li>
            </ul>
          <!-- ul class="nav navbar-nav navbar-right">
            <li class="active"><a href="index.php">Home <span class="sr-only">(current)</span></a></li>
          </ul -->            
         </div>
		
		</div>
		
    </nav>
    
      