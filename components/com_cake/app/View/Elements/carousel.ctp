<?php
echo $this->Html->script('jquery/jquery.cookie');

$link = [];
$link['label'] = 'Vai alla pagina dei produttori';
$link['url'] = Configure::read('Neo.portalgas.url').'site/produttori';
$link['target'] = '_blank';

$carousels = [];
$i=0;
$carousels[$i]['img'] = '/images/cake/carousel/produttori-ricerca.png';
$carousels[$i]['title'] = 'Nuova pagina per i produttori';
$carousels[$i]['text'] = "Accedi alla nuova pagina per ricercare nuovi produttori che collarorano già con altri G.A.S.";
$i++;
$carousels[$i]['img'] = '/images/cake/carousel/produttori-listino.png';
$carousels[$i]['title'] = 'Filtra tra quelli che gestiscono il proprio listino articoli';
$carousels[$i]['text'] = "Così non dovrai più pensare ad aggiornare il listino articoli, lo farà il produttore. Il referente dovrà solo occuparsi di gestire l'ordine!";
$i++;
$carousels[$i]['img'] = '/images/cake/carousel/produttore.png';
$carousels[$i]['title'] = 'Scegli il produttori';
$carousels[$i]['text'] = "Hai le informazioni relative al produttore, i riferimenti per contattarlo, l'elenco dei suoi prodotti e l'elenco dei G.A.S. con il quale collabora";
$i++;
$carousels[$i]['img'] = '/images/cake/carousel/import.png';
$carousels[$i]['title'] = 'Con un semplice click';
$carousels[$i]['text'] = "Il produttore sarà presente nell'elenco dei produttori del tuo G.A.S. e con il listino aggiornato! Contattalo e apri un ordine!";
?>
<style>
.box-carousel {
  background-color: #efefef;
}
.box-carousel .btn-close {
   position: absolute;
   top:  15px;
   right: 25px;
   color: #2c6877;
   z-index: 50;
   border:  none;
}
.box-carousel .btn-close:hover {
   color: #fa824f;
}
.box-carousel .link {
  padding: 10px;
}
.carousel-indicators {
    top: 20px;
}
.carousel-indicators li {
    width: 20px;
    height: 20px;
    border: 2px solid #fa824f;
    margin: 3px;
}
.carousel-indicators .active {
    width: 20px;
    height: 20px;
    margin: 3px;
    background-color: #337ab7;
}
.box-carousel .btn-primary {
    color: #fff !important;
}
.carousel-control.left, .carousel-control.right {
  background-image: none;
}
.carousel .item img {
  display: initial;
}
.carousel-caption {
  padding-bottom: 0px !important;;
  color: #000;
  margin-left: 5%;
  margin-right: 5%;
  bottom: 0px !important;  
  text-shadow: none;
}    
.carousel-caption .text {
  background-color: #efefef;
}
.carousel-caption h3 {
  color: #fa824f;
  font-size: 30px;
}
.carousel-caption p {
  color: #2c6877;
  font-size: 18px;  
  line-height: 1.6;
}
</style>

<?php
echo '<div class="col-xs-12 col-sm-12 col-md-12">';
echo '<div class="box-container">';

echo '<div class="box-carousel" style="display: none">';

echo '<button  href="#" class="btn-close fa fa-3x fa-close"></button>';

echo '<div id="myCarousel" class="carousel slide" data-ride="carousel">';

echo '<ol class="carousel-indicators">';
foreach($carousels as $numResult => $carousel) {
  echo '<li data-target="#myCarousel" data-slide-to="'.$numResult.'" ';
  if($numResult==0) echo ' class="active"';
  echo '>';  
  echo '</li>';
}
echo '</ol>';

echo '<div class="carousel-inner">';
foreach($carousels as $numResult => $carousel) {

    echo '<div class="item ';
    if($numResult==0) echo ' active';
    echo '">';
    echo '<img src="'.$carousel['img'].'" alt="'.$carousel['title'].'">';
    echo '<div class="carousel-caption">';

    echo '<div class="text">';
    echo '<h3>'.$carousel['title'];
    echo '</h3>';
    echo '<p>'.$carousel['text'].'</p>';
    echo '</div>';

    echo '</div>';
    echo '</div>';

}
echo '</div>';

echo '<a class="left carousel-control" href="#myCarousel" data-slide="prev">';
echo '<span class="glyphicon glyphicon-chevron-left"></span>';
echo '<span class="sr-only">Previous</span>';
echo '</a>';
echo '<a class="right carousel-control" href="#myCarousel" data-slide="next">';
echo '<span class="glyphicon glyphicon-chevron-right"></span>';
echo '<span class="sr-only">Next</span>';
echo '</a>';


echo '</div>'; // myCarousel

if(isset($link)) {
  echo '<p class="link">';
  echo '<a href="'.$link['url'].'" class="btn btn-primary" target="'.$link['target'].'">'.$link['label'].'</a>';
  echo '</p>';
}

echo '</div>'; // box-carousel

echo '</div>'; // box-container
echo '</div>';
?>

<script type="text/javascript">
$(function() {

  /* $.cookie("<?php echo Configure::read('Cookies.carousel.close');?>", "", { expires: <?php echo Configure::read('Cookies.expire');?>, path: '<?php echo Configure::read('Cookies.path');?>/' }); */
  var hasCarouselClose = $.cookie("<?php echo Configure::read('Cookies.carousel.close');?>");

  console.log(hasCarouselClose);
  if(typeof hasCarouselClose !== 'undefined' && hasCarouselClose == <?php echo $user->id;?>) {
      $('.box-carousel').hide();
  }
  else
    $('.box-carousel').show();

  $('.btn-close').click(function(e) {
    
    e.preventDefault();

    $('.box-carousel').hide('slow');

    $.cookie("<?php echo Configure::read('Cookies.carousel.close');?>", "<?php echo $user->id;?>", { expires: <?php echo Configure::read('Cookies.expire');?>, path: '<?php echo Configure::read('Cookies.path');?>/' });
  });
});
</script>