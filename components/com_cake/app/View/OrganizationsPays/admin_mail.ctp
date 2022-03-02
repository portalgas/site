<div class="organizations_pays">
	<h2 class="ico-organizations">
		<?php echo __('Organizations');?>
		<div class="actions-img">
			<ul>
				<li><?php echo $this->Html->link(__('List Organizations'), array('controller' => 'Organizations', 'action' => 'index'),array('class' => 'action actionReload','title' => __('List Organizations'))); ?></li>
			</ul>
		</div>
	</h2>
	
	<div>

<pre class="shell" rel="sql per estrarre i manager (10) e tesorieri (11)">
select o.name, u.organization_id, u.email, u.name  
from j_users u,
j_user_usergroup_map g, k_organizations o
where g.group_id in (10, 11)
and g.user_id = u.id
and u.organization_id = o.id
and o.stato = 'Y' and o.type = 'GAS'
and u.email not like '%.portalgas.it'
group by u.organization_id, u.email, u.name;</pre>

	</div>

</div>