<table class="table table-responsive table-hover">
	<thead>
		<tr>
			<th style="width: 50px"></th>
			<th style="width: 70px">Status</th>
			<th style="width: 120px;">Date</th>
			<th>Source</th>
			<th>Destination</th>
			<th>Services</th>
			<th></th>
		</tr>
	</thead>
	<tbody>
		<? foreach($tasks as $task): ?>
			<tr data-status="<?=Util::dropdownStatus($task['status'], 'class')?>">
				<td class="text-center"><i class="fa <?=Util::actionIcon($task['type'])?> js-tooltip" title="<?=ucfirst($task['type'])?>"></i></td>
				<td style="position: relative">
					<div class="pull-left percentage">
						<div class="progress progress<?=Util::dropdownStatus($task['status'], 'class') == 'warning' ? '-striped active' : ''?>">
							<span class="percent text-center"><?=Util::dropdownStatus($task['status'], 'text')?></span>
							<div class="progress-bar progress-bar-<?=Util::dropdownStatus($task['status'], 'class')?>"  role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="width: 100%">
							</div>
						</div>
					</div>
				</td>
				<td><?=($task['started_at'] == '0000-00-00 00:00:00' ? Util::countdown($task['created_at']) : Util::countdown($task['started_at']) )?></td>
				<td>
					<img src="<?=Util::profileImageUrl(array('avatar' => $task['source_avatar']), 16);?>" width="16" alt="avatar" class="small-avatar" />
					<?=Util::listingEmail($task['source_email'])?>
				</td>
				<td>
					<img src="<?=Util::profileImageUrl(array('avatar' => $task['destination_avatar']), 16);?>" width="16" alt="avatar" class="small-avatar" />
					<?=Util::listingEmail($task['destination_email'])?>
				</td>
				<td>
					<? if($task['services']): ?>
						<? foreach($task['services'] as $service): ?>
							<div class="service service-small <?=$service['image_css']?>-small js-tooltip" title="<?=ucfirst($service['name'])?>"></div>
						<? endforeach; ?>
					<? endif; ?>
				</td>
				<td>
					<? if($task['status'] > 1): ?>
						<? if($task['status'] == 2): ?>
							<a href="<?=Render::link('migrate/revert/' . $task['id'])?>" class="btn btn-xs btn-default pull-right margin-left-xs margin-bottom-xs">Revert</a>
						<? endif; ?>
						<a href="<?=Render::link('migrate/details/' . $task['id'])?>" class="btn btn-xs btn-info pull-right margin-left-xs margin-bottom-xs">Details</a>
					<? endif; ?>
				</td>
			</tr>
		<? endforeach; ?>
	</tbody>
</table>