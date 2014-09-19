<?=Render::view('common/notifications')?>

<?=Render::view('common/navigation', $templateData)?>

<div class="jumbotron">
	<div class="container no-jumbo">

		<?=Render::view('common/notifications')?>

		<h1>Import</h1>
		<p>You could use either one of these accounts to login in the application. However your primary account will be the first one you used to access this application.You can add as many accounts as you want in order to move data between them.</p>

		<!-- Modal -->
		<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
						<h4 class="modal-title" id="myModalLabel">Select services</h4>
					</div>
					<div class="modal-body clearfix">
						<? if($services): ?>
							<p>Please select which of the following service you want to sync</p>
							<? foreach($services as $service): ?>
								<div class="list-group pull-left" style="width: 150px; margin-right: 10px;">
									<a href="#" class="list-group-item clearfix js-select-service text-center little"  data-id="<?=$service['id']?>" data-name="<?=$service['name']?>" data-scopes="<?=$service['scopes']?>">
										<div class="service service-small <?=$service['image_css']?>-small" title="<?=$service['name']?>"></div>
										<h5 class="list-group-item-heading" style="padding-top: 3px;"><strong><?=$service['name']?></strong></h5>
									</a>
								</div>
							<? endforeach; ?>
						<? else: ?>
							<div class="alert alert-info text-center alert-dismissable">
								<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
								You have no common enabled services between these two accounts.
							</div>
						<? endif; ?>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
						<button type="button" class="btn btn-primary disabled js-do-it">Do it!</button>
					</div>
				</div>
			</div>
		</div>

		<div class="btn-group clearfix">
			<button type="button" class="btn btn-lg btn-primary" data-toggle="button" title="Import" style="width: 310px;">
				<i class="fa fa-cloud-upload"></i>
			</button>
		</div>
	</div>
</div>

<div class="container">
	<? if($history): ?>
		<table class="table table-responsive table-hover">
			<thead>
			<tr>
				<th style="width: 70px">Status</th>
				<th>Title</th>
				<th>Contains</th>
				<th style="width: 120px;">Services</th>
				<th style="width: 120px;">Date</th>
				<th style="width: 120px">Duration</th>
			</tr>
			</thead>
			<tbody>
			<? foreach($history as $task): ?>
				<tr>
					<td style="position: relative">
						<div class="pull-left percentage">
							<span class="percent text-center"><?=Util::dropdownStatus($task['status'], 'text')?></span>
							<div class="progress progress<?=Util::dropdownStatus($task['status'], 'class') == 'warning' ? '-striped active' : ''?>">
								<div class="progress-bar progress-bar-<?=Util::dropdownStatus($task['status'], 'class')?>"  role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="width: 100%">
								</div>
							</div>
						</div>
					</td>
					<td><?=$task['title']?></td>
					<td><?=implode('<br />', json_decode($task['contains'], true))?></td>
					<td><?=implode('<br />', $task['services'])?></td>
					<td><?=Util::countdown($task['started_at'])?></td>
					<td><?=($task['duration'] ? round($task['duration']) . ' seconds' : '-' )?></td>
				</tr>
			<? endforeach; ?>
			</tbody>
		</table>
	<? else: ?>
		<div class="alert alert-warning text-center alert-dismissable">
			<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
			Your history is clean, you did not use this feature before.
		</div>
	<? endif; ?>
</div>

<?=Render::view('common/copyright')?>