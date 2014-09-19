<div class="step-one">
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
				<h3><strong>Available Services</strong></h3>
				<p>Please select which one of the following service's data you want to share:</p> <br />
				<ul class="services">

					<? foreach($services as $key => $service): ?>
						<? if($service['sync']): ?>
							<?
								$has = true;
								$selected = 'select';
								if( $_SESSION['share']['service']) {
									if($service['id'] == $_SESSION['share']['service']) {
										$selected = 'selected';
									}
								}
							?>
							<li class="<?=$selected?>">
								<div class="list-group">
									<a href="#" class="list-group-item clearfix select-one-service <?=$selected?>"  data-id="<?=$service['id']?>">
										<div class="service <?=$service['image_css']?>" title="<?=$service['name']?>"></div>
										<div class="service-text">
											<h5 class="list-group-item-heading"><strong><?=$service['name']?></strong></h5>
											<p class="list-group-item-text"><?=$selected?></p>
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
				You have no services enabled that support sharing . Please refine your permissions <a href="<?=Render::link('accounts/permissions')?>">here</a>.
			</div>
		<? endif; ?>
	<? else: ?>
		<div class="alert alert-info text-center alert-dismissable no-common">
			You have no services enabled that support sharing . Please refine your permissions <a href="<?=Render::link('accounts/permissions')?>">here</a>.
		</div>
	<? endif; ?>
</div>