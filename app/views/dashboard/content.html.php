<?=Render::view('common/navigation', $templateData)?>

<div class="jumbotron" xmlns="http://www.w3.org/1999/html">
	<div class="container no-jumbo" <?= !$intro ? 'data-intro="Quickly review all your account activity on our platform." data-step="1"' : ''?>>

		<h1>Hello, <?=ucwords($_SESSION['current']['username']['name'])?>!</h1>
		<p>This is your dashboard, here you can take a quick peek at your interactions with the Migrate application. On the below panels you can check out your latest activity, all your google accounts linked to the app and of course your approved Google Services for this account.</p>

		<br />

		<div class="row no-jumbo">

			<div class="col-lg-4">
				<div class="list-group">
					<div class="list-group-item heading">
						Activity
						<? if($queue): ?>
							<a href="#" class="js-info-sign info-sign" title="What's this?" data-src="1"><span class="glyphicon glyphicon-info-sign"></span></a>
						<? endif; ?>
					</div>
					<? if($queue): ?>

						<div class="list-group-item info" data-id="1">
							<p>An activity item can be a sync, migrate, move, clean or share operation.</p>
						</div>

						<? foreach($queue as $task): ?>
							<div class="list-group-item task-item">
								<div class="pull-left percentage">
									<span class="percent text-center"><?=Util::dropdownStatus($task['status'], 'text')?></span>
									<div class="progress progress<?=Util::dropdownStatus($task['status'], 'class') == 'warning' ? '-striped active' : ''?>">
										<div class="progress-bar progress-bar-<?=Util::dropdownStatus($task['status'], 'class')?>"  role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="width: 100%">
										</div>
									</div>
								</div>

								<div class="title"><?=$task['title']?></div>
								<div class="the-operation">
									<i class="fa <?=Util::actionIcon($task['type'])?> js-tooltip" title="<?=ucfirst($task['type'])?>"></i>
								</div>
							</div>
						<? endforeach; ?>

					<? else: ?>
						<div class="list-group-item info display" data-id="1">
							<p><strong>You have no activity items, yet!</strong></p>
							<p>Here you can see your latest activity. An activity item can be a sync, migrate, move, clean or share operation.</p>
						</div>
					<? endif; ?>

					<a href="<?=Render::link('activity')?>" class="list-group-item link clearfix"><span class="pull-right"><?=(count($queue) > 3 )? 'View All' : 'Manage Activity'?> &raquo;</span></a>
				</div>
			</div>

			<div class="col-lg-4">
				<div class="list-group">
					<div class="list-group-item heading">
						Linked Accounts
						<? if($usernames): ?>
							<a href="#" class="js-info-sign info-sign" title="What's this?" data-src="3"><span class="glyphicon glyphicon-info-sign"></span></a>
						<? endif; ?>
					</div>

					<? if($usernames): ?>
						<div class="list-group-item info" data-id="3">
							<p>Your Google accounts that are currently linked to the application.</p>
						</div>

						<? $key = 0; ?>
						<? foreach($usernames as $user): ?>
							<? if($key < 4): ?>
								<div class="list-group-item title">
									<img src="<?=Util::profileImageUrl($user['userProfile'], 16);?>" alt="avatar" class="small-avatar" />
									<?=$user['username']['email']?>
								</div>
							<? endif; ?>
							<? $key++ ?>
						<? endforeach; ?>

					<? else: ?>
						<div class="list-group-item info display" data-id="3">
							<p><strong>You have no Google accounts linked, yet!</strong></p>
							<p>Your Google accounts that are currently linked to the application.</p>
						</div>
					<? endif; ?>



					<a href="<?=Render::link('accounts')?>" class="list-group-item link clearfix"><span class="pull-right"><?=(count($usernames) > 3 )? 'View All' : 'Manage Accounts'?> &raquo;</span></a>
				</div>
			</div>

			<div class="col-lg-4">
				<div class="list-group">
					<div class="list-group-item heading">
						Approved Services
						<? if($services): ?>
							<a href="#" class="js-info-sign info-sign" title="What's this?" data-src="2"><span class="glyphicon glyphicon-info-sign"></span></a>
						<? endif; ?>
					</div>

					<? if($services): ?>
						<div class="list-group-item info" data-id="2">
							<p>These are the services that the application has access to.</p>
						</div>

						<? $k = 0; ?>
						<? foreach($services as $key => $service): ?>
							<? if($k < 4): ?>
								<div class="list-group-item title">
									<div class="service service-small <?=$service['image_css']?>-small" title="<?=$service['name']?>"></div>
									&nbsp;<?=$service['name']?>
								</div>
							<? endif; ?>
							<? $k++ ?>
						<? endforeach; ?>

					<? else: ?>
						<div class="list-group-item info display">
							<p><strong>You haven't approved any services, yet!</strong></p>
							<p>These are the services that the application has access to.</p>
						</div>
					<? endif; ?>

					<a href="<?=Render::link('accounts/permissions')?>" class="list-group-item link clearfix"><span class="pull-right"><?=(count($services) > 3 )? 'View All' : 'Manage Services'?> &raquo;</span></a>
				</div>
			</div>

		</div>
	</div>
</div>

<div class="container">

	<div class="row" <?= !$intro ? 'data-intro="Learn about our platform features." data-step="2"' : ''?> >
		<div class="col-lg-4">
			<h1 class="text-center"><i class="fa fa-retweet transparent fa-2x"></i></h1>
			<p><strong>Migrate</strong> operation consists in copying, syncing or moving your google account data from one account to another.</p>
			<p>You can choose your source account, destination account also select which of the services you'll want to migrate. </p>
		</div>

		<div class="col-lg-4">
			<h1 class="text-center"><i class="fa fa-trash-o transparent fa-2x"></i></h1>
			<p><strong>Clean</strong> operation allows you to delete certain data from your account.</p>
			<p>Basically this helps you if you want to manually delete unneeded data. Before each cleaning process a backup is made, so you can revert if you want your data back.</p>
		</div>

		<div class="col-lg-4">
			<h1 class="text-center"><i class="fa fa-share-square-o transparent fa-2x"></i></h1>
			<p><strong>Share</strong> your data with your friends. You can select which services and exactly what data you want to share and then we will create a link for you.</p>
			<p>By accessing that link, your friends can import your data to their accounts. How cool is that ?</p>
		</div>
	</div>
</div>

<?=Render::view('common/copyright')?>