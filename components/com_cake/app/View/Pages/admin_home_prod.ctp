<div class="cpanel">
	<div class="icon-wrapper">
		<div class="icon"><a href="/administrator/index.php?option=com_admin&amp;task=profile.edit&amp;id=<?php echo $user->id;?>"><img alt="" src="/administrator/templates/bluestork/images/header/icon-48-user-profile.png"><span>Il mio profilo</span></a></div>
	</div>
	
	<?php 
	echo '<div class="icon-wrapper">';	echo '<div class="icon"><a href="/administrator/index.php?option=com_users"><img alt="" src="/administrator/templates/bluestork/images/header/icon-48-user.png"><span>Gestione utenti</span></a></div>';	echo '</div>';
	
	echo '<div class="icon-wrapper">';
	echo '<div class="icon"><a href="/administrator/index.php?option=com_cake&amp;controller=ProdGroups&amp;action=index"><img alt="" src="/administrator/templates/bluestork/images/header/cake/users.png"><span>Gruppi di utenti</span></a></div>';
	echo '</div>';
	
	echo '<div class="icon-wrapper">';	echo '<div class="icon"><a href="/administrator/index.php?option=com_cake&amp;controller=ProdDeliveries&amp;action=index"><img alt="" src="/administrator/templates/bluestork/images/header/cake/calendar_date.png"><span>Consegne</span></a></div>';
	echo '</div>';
	
	echo '<div class="icon-wrapper">';
	echo '<div class="icon"><a href="/administrator/index.php?option=com_cake&amp;controller=Mails&amp;action=send"><img alt="" class="img-responsive-disabled" src="/administrator/templates/bluestork/images/header/cake/icon-48-writemess.png"><span>Mail</span></a></div>';
	echo '</div>';
	
	echo '<div class="icon-wrapper">';
	echo '<div class="icon"><a target="_blank" href="https://www.facebook.com/portalgas.it"><img class="img-responsive-disabled" src="/administrator/templates/bluestork/images/header/cake/social-fb.png" alt=""><span>Facebook</span></a></div>';
	echo '</div>';
	
	echo '<div class="icon-wrapper">';
	echo '<div class="icon"><a target="_blank" href="https://www.youtube.com/channel/UCo1XZkyDWhTW5Aaoo672HBA"><img class="img-responsive-disabled" src="/administrator/templates/bluestork/images/header/cake/social-youtube.png" alt=""><span>YouTube</span></a></div>';
	echo '</div>';	
		
echo '</div>';	
?>