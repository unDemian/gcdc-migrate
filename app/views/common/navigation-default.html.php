<div class="navbar-background"></div>
<div class="navbar navbar-inverse navbar-fixed-top" role="navigation">
	<div class="container">

		<div class="navbar-header">
			<button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
				<span class="sr-only">Toggle navigation</span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
			</button>
			<a class="navbar-brand <?=(Router::$class == 'dashboard') ? 'active' : ''?>" href="<?=Render::link('dashboard')?>"><img src="<?=Render::image('icon.png')?>"/></a>
		</div>

		<div class="navbar-collapse collapse">
			<ul class="nav navbar-nav">
				<li <?=(Router::$class == 'dashboard') ? 'class="active"' : ''?>><a href="<?=Render::link('dashboard')?>">Dashboard</a></li>
				<li <?=(Router::$class == 'migrate') ? 'class="active"' : ''?>><a href="<?=Render::link('migrate')?>">Migrate</a></li>
				<li <?=(Router::$class == 'clean') ? 'class="active"' : ''?>><a href="<?=Render::link('clean')?>">Clean</a></li>
				<li <?=(Router::$class == 'share') ? 'class="active"' : ''?>><a href="<?=Render::link('share')?>">Share</a></li>
				<li <?=(Router::$class == 'backup') ? 'class="active"' : ''?>><a href="#" class="js-tooltip transparent" data-placement="bottom" data-title="Soon">Export</a></li>
				<li <?=(Router::$class == 'backup') ? 'class="active"' : ''?>><a href="#" class="js-tooltip transparent" data-placement="bottom" data-title="Soon">Import</a></li>
				<li <?=(Router::$class == 'activity') ? 'class="active"' : ''?>>
					<a href="<?=Render::link('activity')?>">
						<span class="badge pull-right badge-danger" style="display: none">2</span>
						Activity
					</a>
				</li>
			</ul>

			<ul class="nav navbar-nav navbar-right">
				<li class="dropdown profile-align clearfix">
					<a class="dropdown-toggle clearfix js-resize-dropdown" data-toggle="dropdown" href="#">
						<span><?=$_SESSION['current']['username']['email']?></span>
						<span class="caret down-arrow"></span>
						<img src="<?=Util::profileImageUrl($_SESSION['current']['userProfile'], 28);?>" alt="avatar" class="avatar" />
					</a>
					<ul class="dropdown-menu">
						<? if(count($_SESSION['usernames']) > 1): ?>
							<li role="presentation" class="dropdown-header">Accounts</li>
							<? foreach($_SESSION['usernames'] as $key => $username): ?>
								<? if($key != $_SESSION['current']['username']['id']): ?>
									<li><a href="<?=Render::link('accounts/select/' . $username['username']['id'])?>"><?=$username['username']['email']?></a></li>
								<? endif; ?>
							<? endforeach; ?>
							<li class="divider"></li>
						<? endif; ?>

						<li role="presentation" class="dropdown-header">Manage</li>
						<li><a href="<?=Render::link('accounts/details')?>">Profile</a></li>
						<li><a href="<?=Render::link('accounts/permissions')?>">Permissions</a></li>
						<li><a href="<?=Render::link('accounts')?>">Accounts</a></li>

						<li class="divider"></li>

						<li><a href="<?=Render::link('accounts/add')?>">Add Account</a></li>
					</ul>
				</li>

				<li><a href="<?=Render::link('logout')?>">Logout</a></li>
			</ul>

		</div><!--/.navbar-collapse -->
	</div>
</div>