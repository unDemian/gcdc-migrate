<?=Render::view('common/notifications')?>

<?=Render::view('common/navigation', $templateData)?>

<div class="jumbotron" xmlns="http://www.w3.org/1999/html">
	<div class="container">
		<h1><?=ucfirst($task['type']) . ' #' . $task['id']?> </h1>
		<p>Here you can copy your google data from one account to another. Choose destination and source account, and then hit the migrate button in order to select which of the services you'll want to migrate. A migration may take several minutes depending on how many services you selected and the amount of related data.</p>
		<br />

		<div class="row no-jumbo">
			<div class="col-lg-4">
				<div class="list-group">
					<div class="list-group-item heading">
						General
					</div>

					<div class="list-group-item clearfix">
						<div class="col-xs-3 tabel-label">Status</div>

						<div class="col-xs-9 tabel-data">
							<div class="percentage">
								<span class="percent text-center"><?=Util::dropdownStatus($task['status'], 'text')?></span>
								<div class="progress progress<?=Util::dropdownStatus($task['status'], 'class') == 'warning' ? '-striped active' : ''?>">
									<div class="progress-bar progress-bar-<?=Util::dropdownStatus($task['status'], 'class')?>"  role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="width: 100%">
									</div>
								</div>
							</div>
						</div>
					</div>

					<div class="list-group-item clearfix">
						<div class="col-xs-3 tabel-label">Type</div>

						<div class="col-xs-9 tabel-data">
							<?=ucfirst($task['type'])?>
							<i class="fa fa-expand  pull-right <?=$task['type'] != 'migrate' ? 'hide' : ''?>" title="Migrate"></i>
							<i class="fa fa-retweet pull-right <?=$task['type'] != 'sync' ? 'hide' : ''?>" title="Sync"></i>
							<i class="fa fa-long-arrow-right pull-right <?=$task['type'] != 'move' ? 'hide' : ''?>" title="Move"></i>
						</div>
					</div>
				</div>
			</div>
			<div class="col-lg-4">
				<div class="list-group">
					<div class="list-group-item heading">
						Users
					</div>

					<div class="list-group-item clearfix">
						<div class="col-xs-3 tabel-label">Source</div>

						<div class="col-xs-9 tabel-data">
							<img src="<?=Util::profileImageUrl($source['userProfile'], 16);?>" alt="avatar" class="small-avatar" />
							<?=Util::listingEmail($source['username']['email'])?>
						</div>
					</div>

					<div class="list-group-item clearfix">
						<div class="col-xs-3 tabel-label">Destination</div>

						<div class="col-xs-9 tabel-data">
							<img src="<?=Util::profileImageUrl($destination['userProfile'], 16);?>" alt="avatar" class="small-avatar" />
							<?=Util::listingEmail($destination['username']['email'])?>
						</div>
					</div>
				</div>
			</div>
			<div class="col-lg-4">
				<div class="list-group">
					<div class="list-group-item heading">
						Stats
					</div>

					<div class="list-group-item clearfix">
						<div class="col-xs-3 tabel-label">Started</div>

						<div class="col-xs-9 tabel-data">
							<?=($task['started_at'] == '0000-00-00 00:00:00' ? Util::countdown($task['created_at']) : Util::countdown($task['started_at']) )?>
						</div>
					</div>

					<div class="list-group-item clearfix">
						<div class="col-xs-3 tabel-label">Duration</div>

						<div class="col-xs-9 tabel-data">
							<?=Util::elapsed($task['duration'])?>
						</div>
					</div>
				</div>
			</div>

		</div>

		<!-- Buttons -->
		<a href="<?=Render::link('migrate')?>" class="btn btn-md btn-default" title="Back">
			<i class="fa fa-caret-left"></i> Back
		</a>

		<? if($task['status'] == 2): ?>
			<a href="<?=Render::link('migrate/revert/' . $task['id'])?>" class="btn btn-md btn-primary pull-right" title="Revert">
				<i class="fa fa-repeat"></i> Revert
			</a>
		<? endif; ?>
	</div>
</div>

<div class="container">

	<? if($task['status'] == \app\models\TasksModel::STATUS_REVERTED): ?>
		<div class="alert alert-info text-center ">
			This action was reverted. The data below represents the initial operation.
		</div>
	<? endif; ?>

	<? if($task['services']): ?>
		<div id="wizard" class="tabbable" data-id="<?=$task['id']?>">
			<ul>
				<? foreach($task['services'] as $key => $service): ?>
					<li class="head-tab-<?=$key?> <?=(!$key) ? 'active' : ''?>" data-id="<?=$service['id']?>">
						<a href="#tab<?=$key?>" data-toggle="tab">
							<div class="service service-small margin-right-xs <?=$service['image_css']?>-small" title="<?=$service['name']?>"></div>
							<?=$service['name']?>
						</a>
					</li>
				<? endforeach ?>
			</ul>

			<div class="tab-content clearfix">

				<? foreach($task['services'] as $key => $service): ?>
					<div class="tab-pane <?=(!$key) ? 'fade in active' : ''?>" id="tab<?=$key?>">

					</div>
				<? endforeach ?>

			</div>
		</div>
	<? endif; ?>

</div>

<?=Render::view('common/copyright')?>
