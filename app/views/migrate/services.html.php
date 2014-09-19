<div class="step-three">
	<? if($services): ?>
		<?
		$has = false;
		foreach($services as $service) {
			if($service['sync']) {
				$has = true;
			}
		}
		?>

		<? if($has): ?>
			<div class="clear clearfix">
				<h3><strong>Available Common Services</strong></h3>
				<p>Please select which of the following service's data you want to <?=Util::action()?>:</p> <br />
				<ul class="services">
					<? foreach($services as $service): ?>
						<? if($service['sync']): ?>
							<? $has = true; ?>
							<li class="<?=(in_array($service['id'], array_keys($_SESSION['wizard']['services'])) ? 'selected' : '')?>">
								<div class="list-group">
									<a href="#" class="list-group-item clearfix select-service <?=(in_array($service['id'], array_keys($_SESSION['wizard']['services'])) ? 'selected' : '')?>"  data-id="<?=$service['id']?>">
										<div class="service <?=$service['image_css']?>" title="<?=$service['name']?>"></div>
										<div class="service-text">
											<h5 class="list-group-item-heading"><strong><?=$service['name']?></strong></h5>
											<p class="list-group-item-text"><?=(in_array($service['id'], array_keys($_SESSION['wizard']['services'])) ? 'selected' : 'select')?></p>
										</div>
									</a>
								</div>
							</li>
						<? endif; ?>
					<? endforeach; ?>
				</ul>
			</div>
		<? else: ?>
			<div class="alert alert-info text-center alert-dismissable no-common">
				You have no common enabled services between these two accounts. Please refine your permissions <a href="<?=Render::link('accounts/permissions')?>">here</a>.
			</div>
		<? endif; ?>
	<? else: ?>
		<div class="alert alert-info text-center alert-dismissable no-common">
			You have no common enabled services between these two accounts. Please refine your permissions <a href="<?=Render::link('accounts/permissions')?>">here</a>.
		</div>
	<? endif; ?>
</div>