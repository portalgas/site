<div class="input required">

	<label for="">Come apparirà agli utenti</label>
		
	<div style="width:75%;float: right;">
	 
	 	<?php 
	 	if($modalita=='ADD') {
	 	?>
		<table>	
			<tr>
				<td>
					Modalità <b>senza</b> le immagini
				</td>
				<td>
					Se <b><?php echo Configure::read('ArticlesOrderWithImgToTypeDrawComplete');?>%</b> degli articoli che assocerai all'ordine <b>non</b> avranno un immagine associata, gli utenti potanno acquistare gli articoli nella modalità <b>senza</b> le immagini
				</td>
				<td>
					<img width="150" class="print_screen" id="print_screen_type_draw_simple" src="<?php echo Configure::read('App.img.cake');?>/print_screen_type_draw_simple.jpg" title="" border="0" />
				</td>
			</tr>			
			<tr>
				<td>
					Modalità <b>con</b> le immagini
				</td>
				<td>
					Se <b><?php echo Configure::read('ArticlesOrderWithImgToTypeDrawComplete');?>%</b> degli articoli che assocerai all'ordine avranno un immagine associata, gli utenti potanno acquistare gli articoli nella modalità <b>con</b> le immagini
				</td>
				<td>
					<img width="150" class="print_screen" id="print_screen_type_draw_complete" src="<?php echo Configure::read('App.img.cake');?>/print_screen_type_draw_complete.jpg" title="" border="0" />
				</td>
			</tr>			
		</table>	 	
		<?php 
		}
	 	else 
		if($modalita=='EDIT') {
		?>
		
		<table>	
			<tr>
				<td>
					<input type="radio" 
					<?php 
						if($value=='SIMPLE') echo 'checked="checked" ';
					?>
					value="SIMPLE" id="" name="data[Order][type_draw]" />
				</td>
				<td>
					Modalità <b>senza</b> le immagini
				</td>
				<td>
					<img width="150" class="print_screen" id="print_screen_type_draw_simple" src="<?php echo Configure::read('App.img.cake');?>/print_screen_type_draw_simple.jpg" title="" border="0" />
				</td>
			</tr>			
			<tr>
				<td>
					<input type="radio" 
					<?php 
						if($value=='COMPLETE') echo 'checked="checked" ';
					?>
					value="COMPLETE" id="" name="data[Order][type_draw]" />
				</td>
				<td>
					Modalità <b>con</b> le immagini
				</td>
				<td>
					<img width="150" class="print_screen" id="print_screen_type_draw_complete" src="<?php echo Configure::read('App.img.cake');?>/print_screen_type_draw_complete.jpg" title="" border="0" />
				</td>
			</tr>			
		</table>
		<?php 		
		}	
	 	else 
		if($modalita=='VIEW') {
		?>
		
		<table>	
			<tr>
				<td>
					<?php 
						if($value=='SIMPLE') echo '<input type="radio" value="SIMPLE" id="" name="data[Order][type_draw]" checked="checked" />';
					?>
				</td>
				<td>
					Modalità <b>senza</b> le immagini
				</td>
				<td>
					<img width="150" class="print_screen" id="print_screen_type_draw_simple" src="<?php echo Configure::read('App.img.cake');?>/print_screen_type_draw_simple.jpg" title="" border="0" />
				</td>
			</tr>			
			<tr>
				<td>
					<?php 
						if($value=='COMPLETE') echo '<input type="radio" value="COMPLETE" id="" name="data[Order][type_draw]" checked="checked" />';
					?>
				</td>
				<td>
					Modalità <b>con</b> le immagini
				</td>
				<td>
					<img width="150" class="print_screen" id="print_screen_type_draw_complete" src="<?php echo Configure::read('App.img.cake');?>/print_screen_type_draw_complete.jpg" title="" border="0" />
				</td>
			</tr>			
		</table>
		<?php 		
		}
		?>			
	</div>
</div>