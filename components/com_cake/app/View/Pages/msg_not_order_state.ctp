<p style="text-align:center;">
	<img alt="exclamation" src="<?php echo Configure::read('App.img.cake'); ;?>/msg_exclamation.png" style="float: none;" />
</p>	

<?php
if(!empty($results))
	echo $this->element('boxOrder', ['results' => $results]);
?>