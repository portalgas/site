
	<div class="footer" role="contentinfo">
		<div class="copyright">Copyright &copy; <?php echo date('Y');?> PortAlGas. All Rights Reserved.</div>
	</div>

	<p>

	<div class="container">
		<div class="col-sm-3 box-intro">
			<a target="_blank" href="https://itunes.apple.com/us/app/portalgas/id1133263691">
        	<img title="vai allo store di Itunes" src="https://www.portalgas.it/images/appstore.png" border="0"></a>
		</div>
		<div class="col-sm-2 box-intro">
			<a href="https://play.google.com/store/apps/details?id=com.ionicframework.portalgas">
        	<img border="0" title="vai allo store di Google" src="https://www.portalgas.it/images/googleplay.png"></a>
		</div>

		<div class="col-sm-2 box-intro">
			<a target="_blank" href="https://facebook.com/portalgas.it"><img src="https://www.portalgas.it/images/cake/ico-social-fb.png" alt="PortAlGas su facebook" title="PortAlGas su facebook" border="0"> Facebook</a>
		</div>
		<div class="col-sm-2 box-intro">
							<a target="_blank" href="https://www.youtube.com/channel/UCo1XZkyDWhTW5Aaoo672HBA"><img src="https://www.portalgas.it/images/cake/ico-social-youtube.png" alt="PortAlGas su YouTube" title="PortAlGas su YouTube" border="0"> YouTube</a>
		</div>
		<div class="col-sm-3 box-intro">
							<a target="_blank" href="https://www.portalgas.it/mobile"><img src="https://www.portalgas.it/images/cake/ico-mobile.png" alt="PortAlGas per tablet e mobile" title="PortAlGas per tablet e mobile" border="0"> Mobile</a>
		</div>
    </div>
	
	</p>
	

<div id="modalImg" class="modal fade">
 <div class="modal-dialog modal-lg">
  <div class="modal-content">
   <!-- div class="modal-header">
    <button type="button" class="close" data-dismiss="modal">&times;</button>
    <h4 class="modal-title">Dettaglio immagine</h4>
   </div -->
   <div class="modal-body" style="overflow: auto;">
    <p><img src="" id="modalImgOrig" /></p>
   </div>
   <div class="modal-footer">
    <button type="button" class="btn btn-default" data-dismiss="modal">Chiudi</button>
   </div>
  </div>
 </div>
</div>


<div id="scroll_to_top"><a href="#top" style="z-index: 9; position: fixed; top: 80%; width: 110px; margin-top: -30px; right: 0px; margin-left: -50px; height: 40px; padding: 3px; font-size: 18px; text-align: center; color: rgb(255, 255, 255); background-color: rgb(98, 80, 67); opacity: 0.8; text-decoration: none;">Torna su</a></div>

    <script src="js/jquery-1.11.2.min.js"></script>
	<script src="js/bootstrap.min.js"></script>

<script type="text/javascript">
$(document).ready(function() {
	$('.img_orig').click(function () {
		var src = $(this).find('img').attr('src');
    	console.log(src);
    	$('#modalImgOrig').attr('src', src);
  	})

	        /* torna in alto */
    		$("body").append("<div id=\"scroll_to_top\"><a href=\"#top\">Torna su</a></div>");
    		$("#scroll_to_top a").css({'display':'none','z-index':'9','position':'fixed','top':'80%','width':'110px','margin-top' : '-30px', 'right' : '0', 'margin-left' : '-50px', 'height' : '40px', 'padding' : '3px 5px', 'font-size' : '18px', 'text-align' : 'center', 'padding' : '3px', 'color' : '#FFFFFF', 'background-color' : '#625043', '-moz-border-radius' : '5px', '-khtml-border-radius' : '5px', '-webkit-border-radius' : '5px', 'opacity' : '.8', 'text-decoration' : 'none'});
    		$('#scroll_to_top a').click(function(){
				$('html, body').animate({scrollTop:0}, 'slow');
			});

});
		
    $(function () {
		var scroll_timer;
		var displayed = false;
		var top = $(document.body).children(0).position().top;
		$(window).scroll(function () {
			window.clearTimeout(scroll_timer);
			scroll_timer = window.setTimeout(function () {
				if($(window).scrollTop() <= top)
				{
					displayed = false;
					$('#scroll_to_top a').fadeOut(500);
				}
				else if(displayed == false)
				{
					displayed = true;
					$('#scroll_to_top a').stop(true, true).show().click(function () { $('#scroll_to_top a').fadeOut(500); });
				}
			}, 100);
		});
	});		
</script>
  </body>
</html>