<?php
$carousels = [];
$i=0;
$carousels[$i]['img'] = '/images/cake/print_screen_type_draw_complete2.jpg';
$carousels[$i]['title'] = '111111111111';
$carousels[$i]['text'] = 'AAAAAAAAAAAAA';
$i++;
$carousels[$i]['img'] = '/images/cake/print_screen_type_draw_complete2.jpg';
$carousels[$i]['title'] = '22222222222222';
$carousels[$i]['text'] = 'bbbbbbbbbbbbbbbbb';
$i++;
$carousels[$i]['img'] = '/images/cake/print_screen_type_draw_complete2.jpg';
$carousels[$i]['title'] = '33333333333333';
$carousels[$i]['text'] = 'ccccccccccccc';
?>
<style>
.carousel-control.left, .carousel-control.right {
  background-image: none;
}
.carousel .item img {
  display: initial;
}
.carousel-caption {
  color: #000;
}
.carousel-caption h3 {
    color: #2c6877;
    font-size: 20px;
}
.carousel-caption p {
}
</style>

<?php
echo '<div class="col-xs-12 col-sm-12 col-md-12">';
echo '<div class="box-container">';


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
    echo '<h3>'.$carousel['title'].'</h3>';
    echo '<p>'.$carousel['text'].'</p>';
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
echo '</div>';

echo '</div>';
echo '</div>';
?>