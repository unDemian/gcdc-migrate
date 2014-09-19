<?=Render::view('common/notifications')?>

<?=Render::view('common/navigation', $templateData)?>

<div class="jumbotron">
	<div class="container no-jumbo">

		<h1>Backups</h1>
		<p>Save your data offline. Click on the backup button to select which services you want to backup. This is still under development, for now your data is only backed up on our servers. The actual download part is comming soon!</p>

		<div class="btn-group clearfix">
			<button type="button" id="show-backup" class="btn btn-lg btn-primary" data-toggle="button" title="Backup" style="width: 310px;">
				<i class="fa fa-cloud-download"></i>
			</button>
		</div>

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
							<? $has = false; ?>
							<p>Please select which of the following service you want to backup:</p>
							<? foreach($services as $service): ?>
								<? if($service['backup']): ?>
									<? $has = true; ?>
									<div class="list-group pull-left" style="width: 150px; margin-right: 10px;">
										<a href="#" class="list-group-item clearfix js-select-service text-center little"  data-id="<?=$service['id']?>" data-name="<?=$service['name']?>" data-scopes="<?=$service['scopes']?>">
											<div class="service service-small <?=$service['image_css']?>-small" title="<?=$service['name']?>"></div>
											<h5 class="list-group-item-heading" style="padding-top: 3px;"><strong><?=$service['name']?></strong></h5>
										</a>
									</div>
								<? endif; ?>
							<? endforeach; ?>
							<? if( !$has): ?>
								<div class="alert alert-info text-center alert-dismissable">
									<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
									You have no services that support backup. Please refine your permissions <a href="<?=Render::link('accounts/permissions')?>">here</a>.
								</div>
							<? endif; ?>
						<? else: ?>
							<div class="alert alert-info text-center alert-dismissable">
								<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
								You have no services that support backup. Please refine your permissions <a href="<?=Render::link('accounts/permissions')?>">here</a>.
							</div>
						<? endif; ?>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
						<button type="button" class="btn btn-success disabled js-do-it">Do it!</button>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<div class="container">
	<div id="wizard" class="tabbable">
		<ul>
			<li class="head-tab-1 active"><a href="#tab1" data-id="1" data-toggle="tab">Choose Action</a></li>
			<li class="head-tab-2"><a href="#tab2" data-id="2" data-toggle="tab">Choose Services</a></li>
			<li class="head-tab-3"><a href="#tab3" data-id="3" data-toggle="tab">Go!</a></li>
		</ul>

		<div class="tab-content clearfix">

			<div class="tab-pane fade in active" id="tab1">
				<div class="row step-one">
					<div class="col-md-4 clearfix action <?=(isset($_SESSION['wizard']['action']) && $_SESSION['wizard']['action'] == '0') ? 'selected' : ''?>" data-id="0">
						<div class="col-xs-1 checkmark clearfix"><i class="fa fa-3x fa-<?=(isset($_SESSION['wizard']['action']) && $_SESSION['wizard']['action'] == '0') ? 'check-' : ''?>square-o"></i></div>

						<div class="col-xs-10">
							<h3><strong>Backup</strong></h3>
							<p>Copy your data from one account to another without affecting existing data. This is a one way operation from <strong>Source</strong> to <strong>Destination</strong>.</p>
						</div>
					</div>

					<div class="col-md-4 clearfix action <?=(isset($_SESSION['wizard']['action']) && $_SESSION['wizard']['action'] == '1') ? 'selected' : ''?>" data-id="1">
						<div class="col-xs-1 checkmark clearfix"><i class="fa fa-3x fa-<?=(isset($_SESSION['wizard']['action']) && $_SESSION['wizard']['action'] == '1') ? 'check-' : ''?>square-o"></i></div>

						<div class="col-xs-10">
							<h3><strong>Export</strong></h3>
							<p>Copy your data between two accounts, until everything is matched. This is a two way operation from <strong>Source</strong> to <strong>Destination</strong> and <strong>vice-versa</strong>.</p>
						</div>
					</div>

				</div>
			</div>

			<div class="tab-pane fade" id="tab2">
				<? Render::view('migrate/services', array('services' => $services)) ?>
			</div>

			<div class="tab-pane fade" id="tab3">
			</div>

			<div class="row">
				<div class="col-xs-1"><a class="btn btn-default previous" href="#">Previous</a></div>
				<div class="col-xs-10 bar-wrapper">
					<div id="bar" class="progress pull-left centered">
						<div class="progress-bar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%">
						</div>
					</div>
				</div>
				<div class="col-xs-1"><a class="btn btn-primary next pull-left" href="#">Next</a></div>
				<div class="col-xs-1"><button class="btn btn-success pull-left start">Start</button></div>
			</div>
		</div>
	</div>

	<div id="results"></div>
</div>

<?=Render::view('common/copyright')?>