<table class="table table-responsive table-hover">
	<thead>
	<tr>
		<th style="width: 50px"></th>
		<th style="width: 70px">Status</th>
		<th style="width: 120px;">Date</th>
		<th>Source</th>
		<th>Destination</th>
		<th style="width: 120px;">Services</th>
		<th style="width: 100px">Duration</th>
		<th></th>
	</tr>
	</thead>
	<tbody>
	<? foreach($tasks as $task): ?>
		<tr <?=$task['viewed'] == 0 ? 'class="new"' : ''?>>
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
				<? if(in_array($task['type'], array(\app\models\TasksModel::TYPE_SHARE, \app\models\TasksModel::TYPE_CLEAN))): ?>
					-
				<? else: ?>
					<img src="<?=Util::profileImageUrl(array('avatar' => $task['destination_avatar']), 16);?>" width="16" alt="avatar" class="small-avatar" />
					<?=Util::listingEmail($task['destination_email'])?>
				<? endif; ?>
			</td>

			<td>
				<? if($task['services']): ?>
					<? foreach($task['services'] as $service): ?>
						<div class="service service-small <?=$service['image_css']?>-small js-tooltip" title="<?=ucfirst($service['name'])?>"></div>
					<? endforeach; ?>
				<? endif; ?>
			</td>
			<td><?=Util::elapsed($task['duration'])?></td>
			<td>
				<?
					switch($task['type']) {
						case \app\models\TasksModel::TYPE_MIGRATE:case \app\models\TasksModel::TYPE_MOVE:case \app\models\TasksModel::TYPE_SYNC:
							if(in_array($task['status'], array(\app\models\TasksModel::STATUS_FINISHED, \app\models\TasksModel::STATUS_REVERTED))) {
								echo '<a href="' . Render::link('migrate/details/' . $task['id']) .'" class="btn btn-xs btn-info pull-right margin-left-xs margin-bottom-xs">Details</a>';
							}
							if(in_array($task['status'], array(\app\models\TasksModel::STATUS_FINISHED))) {
								echo '<a href="' . Render::link('migrate/revert/' . $task['id']) .'" class="btn btn-xs btn-default pull-right margin-left-xs margin-bottom-xs">Revert</a>';
							}
							break;

						case \app\models\TasksModel::TYPE_CLEAN:
							if(in_array($task['status'], array(\app\models\TasksModel::STATUS_FINISHED, \app\models\TasksModel::STATUS_REVERTED))) {
								echo '<a href="' . Render::link('clean/details/' . $task['id']) .'" class="btn btn-xs btn-info pull-right margin-left-xs margin-bottom-xs">Details</a>';
							}
							if(in_array($task['status'], array(\app\models\TasksModel::STATUS_FINISHED))) {
								echo '<a href="' . Render::link('clean/revert/' . $task['id']) .'" class="btn btn-xs btn-default pull-right margin-left-xs margin-bottom-xs">Revert</a>';
							}
							break;

						case \app\models\TasksModel::TYPE_SHARE:
							if(in_array($task['status'], array(\app\models\TasksModel::STATUS_FINISHED))) {
								echo '<a href="' . Render::link('share/remove/' . $task['id']) . '" class="btn btn-xs btn-danger pull-right margin-left-xs margin-bottom-xs">Delete</a>';
							}
							if(in_array($task['status'], array(\app\models\TasksModel::STATUS_FINISHED, \app\models\TasksModel::STATUS_REVERTED))) {
								echo '<a href="' . Render::link('share/details/' . $task['id']) .'" class="btn btn-xs btn-info pull-right margin-left-xs margin-bottom-xs">Details</a>';
							}
							break;
					}
				?>
			</td>
		</tr>
	<? endforeach; ?>
	</tbody>
</table>