<? if($services): ?>
	<div class="legend">
		<a href="#" class="js-services-select-all pull-right text-info"><?=(count($selectedServices) > 1) ? 'DESELECT ALL' : 'SELECT ALL'?></a>
		<br />
	</div>

	<? $count = 0; ?>
	<div class="row clearfix add-top-padding">
		<ul class="services clearfix">
			<? foreach($services as $key => $service): ?>
				<? $count++; ?>
				<li class="js-popoverish <?=(($count % 4 == 0) ? 'last-per-row' : '')?> <?=in_array($service['id'], $selectedServices) ? 'selected' : '' ?> <?=$service['mandatory'] ? 'selected mandatory' : '' ?>" data-title="Implied Permissions" data-trigger="hover" data-placement="auto" data-id="<?=$service['id']?>">
					<div id="service-<?=$service['id']?>" class="hide">

						<? if($service['mandatory']): ?>
							<div class="alert alert-warning">This service is mandatory for login purposes.</div>
						<? endif; ?>

						<? if(isset($service['actions'])): ?>
							<table class="table">
								<thead>
								<tr>
									<th>Actual Actions</th>
								</tr>
								</thead>
								<tbody>
								<? foreach($service['actions'] as $action): ?>
									<tr>
										<td>
											<?
												strtok($action['content'], ':');
												echo strtok(':');
											?>
										</td>
									</tr>
								<? endforeach; ?>
								</tbody>
							</table>
						<? endif; ?>

						<table class="table">
							<thead>
							<tr>
								<th>API Permission</th>
								<th>Read</th>
								<th>Write</th>
							</tr>
							</thead>
							<tbody>
							<? foreach($service['permissions'] as $permission): ?>
								<tr>
									<td><?=$permission['title']?></td>
									<td class="text-center"><span class="glyphicon glyphicon-<?=$permission['read'] ? 'ok text-success' : 'remove text-danger'?>"></span></td>
									<td class="text-center"><span class="glyphicon glyphicon-<?=$permission['write'] ? 'ok text-success' : 'remove text-danger'?>"></span></td>
								</tr>
							<? endforeach; ?>
							</tbody>
						</table>
					</div>

					<div class="list-group">
						<a href="#" class="list-group-item clearfix js-select-service <?=(in_array($service['id'], $selectedServices) || $service['mandatory']) ? 'selected' : '' ?>"  data-id="<?=$service['id']?>" data-scopes="<?=$service['scopes']?>">
							<div class="service <?=$service['image_css']?>" title="<?=$service['name']?>"></div>
							<div class="service-text">
								<h5 class="list-group-item-heading"><strong><?=$service['name']?></strong></h5>
								<p class="list-group-item-text"><?=($service['mandatory'] || in_array($service['id'], $selectedServices)) ? 'selected' : 'select' ?></p>
							</div>
						</a>
					</div>
				</li>
			<? endforeach; ?>

			<? if($servicesSoon): ?>
				<? foreach($servicesSoon as $key => $service): ?>
					<? $count++; ?>
					<li class="<?=(($count % 4 == 0) ? 'last-per-row' : '')?> soon">
						<div class="list-group">
							<a href="#" class="list-group-item clearfix">
								<div class="service <?=$service['image_css']?>" title="<?=$service['name']?>"></div>
								<div class="service-text">
									<h5 class="list-group-item-heading"><strong><?=$service['name']?></strong></h5>
									<p class="list-group-item-text">Soon...</p>
								</div>
							</a>
						</div>
					</li>
				<? endforeach; ?>
			<? endif; ?>
		</ul>
	</div>
<? endif; ?>