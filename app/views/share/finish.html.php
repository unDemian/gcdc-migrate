<div class="step-four clearfix">
	<? if($services): ?>
		<div class="clear clearfix">
			<h3><strong>This is it</strong></h3>
			<p>
				Remember that you can block the sharing link whenever you want. Also by default the link will expire in <strong>24h</strong> after you shared it.<br />
				Below you can review your data and if everything is alright hit the <strong>Start</strong> button.
			</p>
			<br />
			<div class="clear clearfix">
				<h4>Service</h4>
				<? foreach($services as $service): ?>
					<? if($service['id'] == $selectedService): ?>
						<div class="list-group pull-left selected margin-bottom-xs" style="width: 150px; margin-right: 10px;">
							<a href="#" class="list-group-item clearfix selected text-center little">
								<div class="service service-small <?=$service['image_css']?>-small" title="<?=$service['name']?>"></div>
								<h5 class="list-group-item-heading" style="padding-top: 3px;"><?=$service['name']?></h5>
							</a>
						</div>
					<? endif; ?>
				<? endforeach; ?>
			</div>
			<div class="clear clearfix">
				<h4>Data</h4>
				<? if($data): ?>
					<? foreach($data as $type => $items): ?>
						<? if(isset($selectedData[$type]) && $selectedData[$type]): ?>
							<div class="legend clear">
								<span><?=strtoupper($type)?></span>
								<hr />
							</div>

							<? foreach($items as $item): ?>
								<? if(in_array($item['id'], $selectedData[$type])): ?>
									<div class="list-group pull-left clearfix little margin-bottom-xs" style="width: 180px; margin-right: 10px;">
										<a href="#" class="list-group-item clearfix selected little">
											<img src="<?=$item['picture']?>" width="16" alt="avatar" class="small-avatar" />
											<h5 class="list-group-item-heading" style="padding-top: 3px;"><?=Util::wrap($item['name'])?></h5>
										</a>
									</div>
								<? endif; ?>
							<? endforeach; ?>
						<? endif; ?>
					<? endforeach; ?>
				<? endif; ?>
			</div>
		</div>

	<? else: ?>
		<div class="alert alert-info text-center alert-dismissable no-common">
			Please go back and select at least one service.</a>.
		</div>
	<? endif; ?>
</div>