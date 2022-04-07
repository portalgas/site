<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en-gb" dir="ltr" lang="en-gb"><head>

<link rel="stylesheet" type="text/css" media="all" href="/components/com_cake/app/webroot/fonts/css/whhg.css">
		
<style type="text/css">
/*
scuro
0A659E  menu
1E83C2  h2
4FB4F3
chiaro

grigio chiaro #F5F5F5
grigio scuro #D1D3D4
blu scurissimo #002050
blu scuro #1570A6
blu acceso #1076C1
blu link #0072C6 #0060A6

footer #EEEEEE
*/

.clearafter:after {
  clear: both;
  display: block;
  content: ".";
  height: 0;
  visibility: hidden;
}
.clearbreak,
.clear {
  clear: both;
}

.clearfix:before, .clearfix:after {
	content: "";
	display: table;
	line-height: 0;
}

/*
 * struttura
 */
.wrapper{
	width: 960px;
	margin: auto;
}
			
#header {
    background: none repeat scroll 0 0 #FFF;
    padding: 5px 0;
    height: 75px;
}

#header-top-left {
	width: 25%;
	float: left;
}
#header-top-right {
	width: 50%;
	float: right;
}
#header .logo {
    background: url("/images/cake/loghi/0/150h50.png") no-repeat scroll 0 0 rgba(0, 0, 0, 0);
    min-height: 75px;
    width: 100%;
}
#header .logo a {
    display: block;
    min-height: 45px;
    text-indent: -99999em;
    width: 100%;
}
#header-menu {
    background: none repeat scroll 0 0 #0A659E;
    height: 50px;
}
.header-menu-green {
    background: none repeat scroll 0 0 #4B6A38 !important;
}

#header-menu-left {
	width: 70%;
	float: left;
}
#header-menu-right {
	width: 30%;
	float: left;
	text-align:right;
	line-height:24px;
	display:inline-block;
}

#body-content {
    background: none repeat scroll 0 0 rgba(255, 255, 255, 0.6);
    padding: 0 0 20px;
    position: relative;
}
#main-content {
	width: 100%;
    float: left;
}						
#content-left {
	width: 30%;
}
#content-right {
	width: 30%;
}
#content-middle {
	width: 70%;
}
#content-left, #content-middle, #content-right {
    float: left;
}
#content-left, #content-right {
    height: auto;
}
#content-right-inner {
    margin-left: 20px;
}	


#footer-top {
    background: none repeat scroll 0 0 #222222;
    padding: 30px 0 20px;
}
#footer-top-right {
	width: 33.33%;
	float: right;
}
#footer-tops-left {
	width: 33.33%;
	float: left;
}
#footer-top-middle {
	width: 33.34%;
	float: left;
}

#footer {
    background: none repeat scroll 0 0 #000000;
    padding: 20px 0;
}
#footer .copyright {
    padding-top: 10px;
    text-align: right;
}

#header-menu [class*="menu"] > li:hover {
    background: none repeat scroll 0 0 rgba(255, 255, 255, 0.2);
}
#header-menu [class*="menu"] li.parent {
    position: relative;
}
#header-menu .icons[class*="menu"] > li {
	margin:0px;
    padding: 15px 10px 15px 0;
}
#header-menu [class*="menu"] > li {
    float: left;
    font-size: 14px;
    padding: 18px 22px;
    transition: all 0.4s ease-in-out 0s;
}
#header-menu ul[class*="menu"] > li > a, 
#header-menu ul[class*="menu"] > li > span {
    font-weight: bold;
    line-height: 24px;
    margin: 0;
    padding: 1px 10px;
    text-transform: uppercase;
}
#header-menu [class*="menu"] li span, 
#header-menu [class*="menu"] li a {
    color: #FFFFFF;
    display: block;
    line-height: 40px;
    padding: 0 10px;
    text-decoration: none;
    text-transform: capitalize;
    transition: all 0.2s ease-in-out 0s;
}
#header-menu [class*="menu"] li span {
    cursor: default;
}
#header-menu [class*="menu"] > li {
    font-size: 14px;
}
#header-menu [class*="menu"] [class*="menu"], 
#header-menu [class*="menu"] ul {
    font-family: "oswaldbook",Arial,Helvetica,sans-serif;
    list-style: none outside none;
}

html body, html select, html input, html form, html textarea {
    font-family: Segoe UI,Arial,Verdana,Tahoma,sans-serif;
}
html, body {
    line-height: 1.5;
    margin: 0;
    padding: 0;
}
body {
    background: none repeat scroll 0 0 #FFFFFF;
    color: #777777;
    font-size: 85%;
    overflow-x: hidden;
}
			
.breadcrumbs {
    color: #555555;
    font-size: 15px;
    margin: 0 0 15px 0;
    padding: 0;
    text-align: left;
}
.breadcrumbs span {
    color: #555555;
    padding: 0;
}
.readmore {
	margin:10px;
	text-align:right;
}


/* 
 * legenda 
 */
.cakeContainer .legenda {
    border: 3px solid #84A7DB;
    border-radius: 8px 8px 8px 8px;
    clear: both;
    font-size: 14px;
    margin: 10px 0;
    padding: 8px;
    min-height: 30px;    
}
.cakeContainer .legenda-message {
    border: 3px solid #84A7DB;
}
.cakeContainer .legenda-notice {
    border: 3px solid yellow;
}
.cakeContainer .legenda-alert {
    border: 3px solid red;
}
.cakeContainer .legenda div {
    margin: 5px 0 5px 0;
}
.cakeContainer .legenda ul {
	float: right;
}
.cakeContainer .legenda ul, .cakeContainer .legenda li {
	margin: 0 5px;
	list-style: none outside none;
}
.cakeContainer .legenda li {
	float: left;
}
.cakeContainer .legenda div[class|='icon'] {
	font-size: 24px !important;
}
.cakeContainer .legenda #type-message {
	color: #84A7DB;
	float:left;
	font-size:40px !important;
}
.cakeContainer .legenda .nota {
	width: 80%;
    font-size: 12px  !important;
	padding-left: 60px;
	font-style:italic;
}
.cakeContainer .legenda a {
	text-decoration: none;
}
.cakeContainer .legenda table tr td {
    border:none;
}
.cakeContainer .legenda table tr td h3 {
	text-align:center;
    background-color: #C3D2E5;
    border-bottom: 3px solid #84A7DB;
    border-top: 3px solid #84A7DB;
    color: #FFFFFF;
}


ol li, ul li {
    list-style-position: inside;
}
ul, ul li {
    list-style: none outside none;
}
ul, ul li, ol, ol li, p, form, input {
    margin: 0;
    padding: 0;
}
li {
    line-height: 20px;
}
a, a:link, a:visited {
    color: #0060A6;  // #1B57B1
    outline: medium none;
    text-decoration: none;
    transition: all 0.2s ease-in-out 0s;
}
a.green, a.green:link, a.green:visited {
    color: #3C851B; 
}
h2 {
    background: none repeat scroll 0 0 #1E83C2;
    color: #FFFFFF;
    font-size: 20px;
    line-height: 24px;
    margin: 0 0 20px;
    padding: 10px;
    text-transform: capitalize;
}
.h2-green {
    background: none repeat scroll 0 0 #7BAA5F !important;
}
h3 {
    background: none repeat scroll 0 0 #D1D3D4;
    color: #FFFFFF;
    font-size: 18px;
    line-height: 24px;
    margin: 0 0 20px;
    padding: 10px;
    text-transform: capitalize;
}
h1, h2, h3, h4, h5, h6, .componentheading {
    font-weight: normal;
}
#organizationChoiceForm select, 
#organizationChoiceForm option {
font-family: "oswaldbook",Arial,Helvetica,sans-serif;
  font-size: 15px;
    margin: 5px 0;
    padding: 1px 5px;
    width: 90%;
}

legend {
	display:none;
}
select, option {
	font-family: "oswaldbook",Arial,Helvetica,sans-serif;
    font-size: 15px;
    margin: 3px 0;
    padding: 1px 5px;
    color: #002050;
}
fieldset {
	border:none;
	margin:0;
	padding:0;
}
 
/*
 * T O O L T I P
 */
.tip
{
     font-size:0.9em;
     text-align:left;
     padding:5px;
     max-width:400px;
     background-color:#F5F5F5;
	 border:1px solid #002050;
}

.tip-title
{
     font-weight:bold;
}
.invalid {
	border-color:red !important;
}
label.invalid {
	color:red;
}
.invalid { border-color: #ff0000; }
label.invalid { color: #ff0000; }

#jform_profile_tos {
    margin: 0 !important;
    padding: 0 2px !important;
    width: 18em;
}
#jform_profile_tos input {
    margin: 0 5px 0 0 !important;
    width: 25px !important;
}

label.required:after
{
	font-family:'WebHostingHub-Glyphs';
	background:none;
	width:auto;
	height:auto;
	font-style:normal;
	font-weight:bold;
	color:#1076C1;
	margin:0;
	content:'\f0a3';
	padding:10px;
}
input[type="password"], 
input[type="text"], 
input[type="email"]  {
    -moz-appearance: none;
    -moz-border-bottom-colors: none;
    -moz-border-left-colors: none;
    -moz-border-right-colors: none;
    -moz-border-top-colors: none;
    -moz-box-sizing: border-box;
    background: none repeat scroll 0 0 #FFFFFF;
    border-color: #C0C0C0 #D9D9D9 #D9D9D9;
    border-image: none;
    border-radius: 1px;
    border-style: solid;
    border-width: 1px;
    color: #404040;
    display: inline-block;
    font-size: 15px;
    height: 32px;
    margin: 0 0 5px 0;
    padding: 0 4px;
}
input[type="file"], input[type="image"], input[type="submit"], input[type="reset"], input[type="button"], input[type="radio"], input[type="checkbox"] {
    width: auto;
}
label, select, button, input[type="button"], input[type="reset"], input[type="submit"], input[type="radio"], input[type="checkbox"] {
    cursor: pointer;
}
button.btn, input.btn[type="submit"] {
	cursor: pointer;
}
.btn:first-child {
}
.btn {
    border: medium none;
	border-radius: 0;
    padding: 8px 12px;
    font-size: 14px;
    cursor: pointer;
}
.btn-orange {
    background: none repeat scroll 0 0 #FA824F;
    color: #FFFFFF !important;
}
.btn-blue {
    background: none repeat scroll 0 0 #006DCC;
    color: #FFFFFF !important;
}
.btn-blue:hover {
    background: none repeat scroll 0 0 #357AE8;
    border-color: #2F5BB7;
}
.btn-green {
    background: none repeat scroll 0 0 #76BF6B;
    color: #FFFFFF !important;
}
.btn-green:hover {
    background: none repeat scroll 0 0 #3B8230;
    border-color: #2F5BB7;
}
.content-btn {
    border-color: #3079ED;
    color: #FFFFFF;
    display: inline-block;
    font-weight: bold;
    margin: 10px 0 0;
    text-align: right;
    vertical-align: top;
    width: 100%;    
}

/* System Messages */
.error {
   padding:0px;
   margin-bottom: 20px;
}

.error h2 {
        color:#000 !important;
        font-size:1.4em !important;
        text-transform:uppercase;
        padding:0 0 0 0px !important
}

#system-message
{
	margin:10px 0 20px 0;
	border-left:0;
	border-right:0;
}

#system-message dt
{
	font-weight: bold;
}
#system-message dd
{
	margin: 0 0 15px 0;
	font-weight: bold;
	text-indent: 0px;
	padding:0
}
#system-message dd ul
{
   color: #FFFFFF;
    font-size: 120%;
    list-style: none outside none;
	padding: 0px;
}
#system-message dd ul li
{
	line-height:1.5em
}

/* System Standard Messages */
#system-message dt.message
{
	position:absolute;
	top:-2000px;
	left:-3000px;
}
#system-message dd.message ul {
    background: url("/images/cake/system-message-green.jpg") no-repeat scroll 0 50% #3FB724;
}

#system-message dt.error
{
	position:absolute;
	top:-2000px;
	left:-3000px;
}
#system-message dt.notice
{
	position:absolute;
	top:-2000px;
	left:-3000px;
}
#system-message dd.notice ul { color: #000;margin:10px 0 }

#system-message
{
    margin-bottom: 0px;
    padding: 0;
}
#system-message dt
{
    font-weight: normal;
}
#system-message dd
{
    font-weight: normal;
    padding: 0;
}
#system-message dd.error ul {
    background: url("/images/cake/system-message-red.jpg") no-repeat scroll 0 50% #F63031;
    border-bottom: medium none;
    border-top: medium none;
}
#system-message dd.notice ul {
    background: url("/images/cake/system-message-yellow.jpg") no-repeat scroll 0 50% #FFDE00;
    border-bottom: medium none;
    border-top: medium none;
}
#system-message dd.message ul li {
    padding: 15px 10px 0 135px;
    text-align: left;
}
#system-message dd ul {
    height: 91px;
    margin: 0;
    padding: 0;
}

/*
 * contatti
 */
.panel
{
	border:solid 1px #ddd;
	margin-top:-1px

}
.contact .panel h3
{
	margin:0px 0 0px 0;
	padding: 0;
	background:#4FB4F3;
	border:0
}
.panel h3 a
{
	display:block;
	padding:6px;
	text-decoration:none;
	color:#444;
	padding:6px;
}
.panel h3.pane-toggler a
{
	background:url(/images/slider_plus.png) right top no-repeat #f5f5f5;
}
.panel h3.pane-toggler-down a
{
	background: url(/images/slider_minus.png) right top no-repeat #f5f5f5;
	border-bottom:solid 1px #ddd;
}
.pane-slider
{
	border:solid 0px;
	padding:0px 10px !important;
	margin:0;
}

.panel .contact-form,
.panel .contact-miscinfo {
	padding:10px
}
.contact-contactinfo {margin:0 0 20px 0}
.contact .panel .contact-form form,
.contact .panel .contact-address {
	margin:20px 0 0 0
}
</style>
</head>
<body>
	<a name="top" id="top"></a>
	<div class="clearfix">

			<div id="header">
				<div class="wrapper">
					<div id="header-top-left">
						<div>
							<div class="logo">
								<a href="/index.php">.</a>
							</div>
							<div class="clearbreak"></div>
						</div>
					</div>	

					<div id="header-top-right">
					</div>		
				</div>
			</div>

			<div id="header-menu">
				<div class="wrapper">
					<div class="clearfix">
						<div id="header-menu-left">
						</div>
		
						<div id="header-menu-right">
						</div>
					</div>
				</div>
			</div>

	</div>




	<div class="" id="body-content">
		<div class="wrapper">
	
				<div class="clearbreak"></div>

				
				

		<div id="main-content">
			<div id="content_inner">
				
				<div style="font-size:22px;height:350px;padding: 50px 0;">
				Si &egrave; verificato un errore!<br>
				<p style="font-size:16px;padding: 25px 0;">Contatta l'amministratore <a href="info@portalgas.it" title="info@portalgas.it">info@portalgas.it</a></p>
				</div>
				
			</div>		
		</div>

			<div class="clearbreak"></div>
	</div>
</div>



				<div id="footer">
					<div class="wrapper">
						<div>
							<div class="">
								<div class="copyright">Copyright © <?echo date('Y');?>> PortAlGas. All Rights Reserved.</div>
							</div>
						</div>
					</div>
				</div>
				
</body></html>