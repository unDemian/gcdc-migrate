<div class="legend clear">
	<span>LATEST ACTIVITY</span>
	<hr />
</div>
<table class="table table-responsive table-hover">
	<thead>
	<tr>
		<th style="width: 70px">Status</th>
		<th style="width: 120px;">Date</th>
		<th style="width: 120px;">Services</th>
		<th>Contains</th>
		<th style="width: 120px">Duration</th>
		<th style="width: 70px"></th>
	</tr>
	</thead>
	<tbody>
	<? foreach($tasks as $task): ?>
		<tr>
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
						&nbsp;<?=$service['name']?><br />
					<? endforeach; ?>
				<? endif; ?>
			</td>
			<td><?=implode('<br />', json_decode($task['contains'], true))?></td>
			<td><?=Util::elapsed($task['duration'])?></td>
			<td><button class="btn btn-xs btn-success js-tooltip" data-title="Soon!" disabled="disabled">Download (Soon)</button></td>
		</tr>
	<? endforeach; ?>
	</tbody>
</table>