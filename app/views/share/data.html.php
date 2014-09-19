<div class="step-two">
	<div class="clearfix clear">
		<h3><strong>Available Data</strong></h3>
		<p>Please select which data items you want to share</p>

		<? foreach($data as $key => $val): ?>



			<div class="clearfix clear">
				<div class="legend">
					<span><?=strtoupper($key)?></span>
					<hr />
				</div>

				<? if( !$val): ?>
					<div class="alert alert-info text-center alert-dismissable no-common">
						Sorry, you have no <?=$key?>.
					</div>
				<? endif; ?>

				<ul class="services-little">
					<? foreach($val as $service): ?>
						<?
						$selected = 'select';
						if($selectedData) {
							if(isset($selectedData[$key][$service['id']])) {
								$selected = 'selected';
							}
						}
						?>
						<li class="<?=$selected?> clearfix">
							<div class="list-group pull-left clearfix" style="min-width: 180px; margin-right: 10px;">
								<a href="#" class="list-group-item clearfix select-data <?=$selected?>" data-id="<?=$service['id']?>" data-type="<?=$key?>" style="width: auto; display: block;" class="clearfix">
									<div class="col-xs-1 no-padding">
										<img src="<?=$service['picture']?>" width="16" alt="avatar" class="small-avatar" />
									</div>
									<div class="col-xs-11">
										<h5 class="list-group-item-heading clearfix" style="display: block; width: auto !important; padding-top: 3px; white-space:nowrap;"><?=Util::wrap($service['name'])?></h5>
									</div>
								</a>
							</div>
						</li>
					<? endforeach; ?>
				</ul>
			</div>
		<? endforeach; ?>
	</div>
</div>