<?=Render::view('common/navigation', $templateData)?>


<div class="jumbotron">
	<div class="container no-jumbo">
		<?=Render::view('common/notifications')?>

		<h1><span class=".glyphicon .glyphicon-user"></span>You have <?=count($_SESSION['usernames'])?> google account(s) linked to Migrate.</h1>
		<p>This list contains all your google accounts that are linked to the Migrate Application. You can add as many as you want also you can revoke access to a certain account any time you feel so. You can use either one of these accounts to login in the application.</p>

	</div>
</div>

<div class="container">

	<div class="row clearfix remove-bottom-margin">
		<? foreach($_SESSION['usernames'] as $key => $username): ?>
			<div class="col-sm-6 col-md-3">
				<div class="thumbnail <?=($key == $_SESSION['current']['username']['id'] ? 'primary' : '')?> clearfix">
					<div class="big-avatar pull-left">
						<a href="<?=Render::link('accounts/details/' . $key)?>">
							<img src="<?=Util::profileImageUrl($username['userProfile'], 300);?>" alt="avatar" class="img-rounded img-responsive" title="Account Details">
							<? if($key == $_SESSION['current']['username']['id']): ?>
								<span class="label label-primary">Selected</span>
							<? else: ?>
								<a href="<?=Render::link('accounts/select/' . $key)?>" title="Select Account">
									<span class="label label-default">Select</span>
								</a>
							<? endif; ?>
						</a>
					</div>

					<div class="caption pull-left">
						<h3><?=$username['username']['name']?></h3>
						<h6><?=$username['username']['email']?></h6>
						<h6><a href="<?=$username['userProfile']['url']?>" target="_blank">Google+ Profile</a></h6>
						<br />

						<p>
							<a href="<?=Render::link('accounts/details/' . $key)?>" class="btn btn-xs <?=$key == $_SESSION['current']['username']['id'] ? 'btn-info' : 'btn-default' ?>">Account Details</a>
							<a href="<?=Render::link('accounts/unlink/' . $key)?>" class="btn btn-xs <?=$key == $_SESSION['current']['username']['id'] ? 'btn-danger' : 'btn-dangerish' ?>">Remove</a>
						</p>
					</div>
				</div>
			</div>
		<? endforeach; ?>

		<div class="col-sm-6 col-md-3">
			<div class="thumbnail new clearfix">
				<div class="big-avatar pull-left">
					<a href="<?=Render::link('accounts/add')?>">
						<img src="<?=Render::image('new-user.png')?>" alt="avatar" class="img-rounded img-responsive" title="Choose New Account">
					</a>
				</div>

				<div class="caption pull-left">
					<h3>Not done?</h3>

					<h6>Add as many accounts as you want</h6>
					<h6>Click the button below to add another one</h6>
					<br />

					<p>
						<a href="<?=Render::link('accounts/add')?>" class="btn btn-xs btn-success">Add Account </a>
					</p>
				</div>
			</div>
		</div>
	</div>
</div>

<?=Render::view('common/copyright')?>

