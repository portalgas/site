<?php
echo '<p>'; 
echo '<div class="alert alert-warning alert-dismissable">';
echo ' <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>';
echo "Gli articoli appartengono a \"".$ownOrganizationResults['OwnOrganization']['name']."\" che fa parte del DES \"".$ownOrganizationResults['De']['name']."\"";
echo '</div>';
echo '</p>';
?>