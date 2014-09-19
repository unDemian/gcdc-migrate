<?=Render::view('common/notifications')?>

<?=Render::view('common/navigation', $templateData)?>

<div class="jumbotron" xmlns="http://www.w3.org/1999/html">
	<div class="container" <?= !$intro ? 'data-intro="Hit me if you need to migrate some data." data-step="1"' : ''?>>
		<h1>Migrate Data</h1>
		<p>Here you can copy, sync or move your google data from one account to another. Click on the migrate button and follow the instructions in the wizard. A migration may take several minutes depending on how many services you selected and the amount of related data.</p>

		<div class="btn-group clearfix">
			<button type="button" id="show-migrate" class="btn btn-lg btn-primary <?=$templateData['disabled']?>" data-toggle="button" title="Migrate" style="width: 310px;">
				<i class="fa fa-retweet"></i>
			</button>
		</div>
	</div>
</div>

<div class="container">

	<div id="wizard" class="tabbable <?=$disabled?>">
		<ul>
			<li class="head-tab-1 active"><a href="#tab1" data-id="0" data-toggle="tab">Choose Action</a></li>
			<li class="head-tab-2"><a href="#tab2" data-id="1" data-toggle="tab">Choose Accounts</a></li>
			<li class="head-tab-3"><a href="#tab3" data-id="2" data-toggle="tab">Choose Services</a></li>
			<li class="head-tab-4"><a href="#tab4" data-id="3" data-toggle="tab">Go!</a></li>
		</ul>

		<div class="tab-content clearfix">

			<div class="tab-pane fade in active" id="tab1">
				<div class="row step-one">
					<div class="col-md-4 clearfix action <?=(isset($_SESSION['wizard']['action']) && $_SESSION['wizard']['action'] == '0') ? 'selected' : ''?>" data-id="0">
						<div class="col-xs-1 checkmark clearfix"><i class="fa fa-3x fa-<?=(isset($_SESSION['wizard']['action']) && $_SESSION['wizard']['action'] == '0') ? 'check-' : ''?>square-o"></i></div>

						<div class="col-xs-10">
							<h3><strong>Migrate</strong></h3>
							<p>Copy your data from one account to another without affecting existing data. This is a one way operation from <strong>Source</strong> to <strong>Destination</strong>.</p>
						</div>
					</div>

					<div class="col-md-4 clearfix action <?=(isset($_SESSION['wizard']['action']) && $_SESSION['wizard']['action'] == '1') ? 'selected' : ''?>" data-id="1">
						<div class="col-xs-1 checkmark clearfix"><i class="fa fa-3x fa-<?=(isset($_SESSION['wizard']['action']) && $_SESSION['wizard']['action'] == '1') ? 'check-' : ''?>square-o"></i></div>

						<div class="col-xs-10">
							<h3><strong>Sync</strong></h3>
							<p>Copy your data between two accounts, until everything is matched. This is a two way operation from <strong>Source</strong> to <strong>Destination</strong> and <strong>vice-versa</strong>.</p>
						</div>
					</div>

					<div class="col-md-4 clearfix action <?=(isset($_SESSION['wizard']['action']) && $_SESSION['wizard']['action'] == '2') ? 'selected' : ''?>" data-id="2">
						<div class="col-xs-1 checkmark clearfix"><i class="fa fa-3x fa-<?=(isset($_SESSION['wizard']['action']) && $_SESSION['wizard']['action'] == '2') ? 'check-' : ''?>square-o"></i></div>

						<div class="col-xs-10">
							<h3><strong>Move</strong></h3>
							<p>Move your data from one account to another. After the data is copied it will be <strong>deleted</strong> from the source account.</p>
						</div>
					</div>
				</div>
			</div>

			<div class="tab-pane fade" id="tab2">
				<div class="row step-two clearfix">
					<div class="col-md-4 source-wrapper clearfix">
						<h3><strong>Source</strong></h3>
						<div class="accounts source clearfix">

							<? if(count($_SESSION['usernames']) > 1): ?>
								<? foreach($_SESSION['usernames'] as $username): ?>
									<div class="account clearfix <?=($username['username']['id'] == $source['username']['id']) ? 'selected' : ''?>" data-id="<?=$username['username']['id']?>">
										<div class="col-xs-3 image clearfix">
											<img src="<?=Util::profileImageUrl($username['userProfile'], 70);?>" alt="avatar" width="70" class="img-rounded " title="Account Details">
										</div>

										<div class="col-xs-9 details">
											<big class="pull-left"><strong><?=$username['username']['name']?></strong></big>
											<small class="clear pull-left"><?=$username['username']['email']?></small>
											<div class="mini-services clear clearfix">
												<? foreach($username['services'] as $service): ?>
													<? if($service['sync']): ?>
														<div class="service service-small <?=$service['image_css']?>-small" title="<?=$service['name']?>"></div>
													<? endif; ?>
												<? endforeach; ?>
											</div>
										</div>
									</div>
								<? endforeach; ?>
							<? endif; ?>
						</div>
					</div>

					<div class="col-md-3 operation clearfix">
						<div class="le-action clearfix text-center">
							<i class="fa fa-expand fa-5x js-tooltip <?=(isset($_SESSION['wizard']['action']) && $_SESSION['wizard']['action'] != '0') ? 'hide' : ''?>" title="Migrate" data-id="0"></i>
							<i class="fa fa-retweet fa-5x js-tooltip <?=(isset($_SESSION['wizard']['action']) && $_SESSION['wizard']['action'] != '1') ? 'hide' : ''?>" title="Sync" data-id="1"></i>
							<i class="fa fa-long-arrow-right fa-5x js-tooltip <?=(isset($_SESSION['wizard']['action']) && $_SESSION['wizard']['action'] != '2') ? 'hide' : ''?>" title="Move" data-id="2"></i>
						</div>
					</div>

					<div class="col-md-4 destination-wrapper clearfix">
						<h3><strong>Destination</strong></h3>
						<div class="accounts destination clearfix">

							<? if(count($_SESSION['usernames']) > 1): ?>
								<? foreach($_SESSION['usernames'] as $username): ?>
									<div class="account clearfix <?=($username['username']['id'] == $destination['username']['id']) ? 'selected' : ''?>" data-id="<?=$username['username']['id']?>">
										<div class="col-xs-9 details text-right">
											<div class="clear">
												<big class="pull-right"><strong><?=$username['username']['name']?></strong></big><br />
												<small class="pull-right clear"><?=$username['username']['email']?></small>
											</div>
											<div class="mini-services clear pull-right clearfix">
												<? foreach($username['services'] as $service): ?>
													<? if($service['sync']): ?>
														<div class="service service-small <?=$service['image_css']?>-small" title="<?=$service['name']?>"></div>
													<? endif; ?>
												<? endforeach; ?>
											</div>
										</div>
										<div class="col-xs-3 image clearfix">
											<img src="<?=Util::profileImageUrl($username['userProfile'], 70);?>" alt="avatar" width="70" class="img-rounded pull-right" title="Account Details">
										</div>
									</div>
								<? endforeach; ?>
							<? endif; ?>
						</div>
					</div>

				</div>
			</div>

			<div class="tab-pane fade" id="tab3">
				<? Render::view('migrate/services', array('services' => $services)) ?>
			</div>

			<div class="tab-pane fade" id="tab4">
			</div>

			<div class="row">
				<div class="col-xs-1"><a class="btn btn-default previous" href="#">Previous</a></div>
				<div class="col-xs-10 bar-wrapper">
					<div id="bar" class="progress pull-left centered">
						<div class="progress-bar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%">
						</div>
					</div>
				</div>
				<div class="col-xs-1"><a class="btn btn-primary next pull-left disabled" href="#">Next</a></div>
				<div class="col-xs-1"><button class="btn btn-success pull-left start">Start</button></div>
			</div>
		</div>
	</div>

	<div id="results" data-polling="<?=$polling?>" <?= !$intro ? 'data-intro="Keep track of all your actions. Something went wrong? no worries you can revert your actions." data-step="2"' : ''?>>
		<? if($tasks): ?>
			<? \Render::view('migrate/table', compact('tasks')); ?>
		<? else: ?>
			<? \Render::view('common/empty', false); ?>
		<? endif; ?>
	</div>

</div>

<?=Render::view('common/copyright')?>
