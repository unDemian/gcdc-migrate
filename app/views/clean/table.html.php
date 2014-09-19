<table class="table table-responsive table-hover">
	<thead>
		<tr>
			<th style="width: 70px">Status</th>
			<th style="width: 120px;">Date</th>
			<th style="width: 100px">Service</th>
			<th>Cleaned Data</th>
			<th style="width: 200px"></th>
		</tr>
	</thead>
	<tbody>
		<? foreach($tasks as $task): ?>
			<tr data-status="<?=Util::dropdownStatus($task['status'], 'class')?>">
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
					<? if($task['services']): ?>
						<? foreach($task['services'] as $service): ?>
							<div class="service service-small <?=$service['image_css']?>-small js-tooltip" title="<?=ucfirst($service['name'])?>"></div>
							&nbsp;<?=ucfirst($service['name'])?>
						<? endforeach; ?>
					<? endif; ?>
				</td>
				<td>
					<? if($task['share']['data']): ?>
						<? $data = json_decode($task['share']['data'], true); ?>
						<? foreach($data as $type => $items): ?>
							<?=ucfirst($type) . ':' . count($items)?><br />
						<? endforeach; ?>
					<? endif; ?>
				</td>
				<td>
					<a href="<?=Render::link('clean/details/' . $task['id'])?>" class="btn btn-xs btn-info pull-right margin-left-xs margin-bottom-xs">Details</a>
					<? if($task['status'] == \app\models\TasksModel::STATUS_FINISHED): ?>
						<a href="<?=Render::link('clean/revert/' . $task['id'])?>" class="btn btn-xs btn-default pull-right margin-left-xs margin-bottom-xs">Revert</a>
					<? endif; ?>
				</td>
			</tr>
		<? endforeach; ?>
	</tbody>
</table>