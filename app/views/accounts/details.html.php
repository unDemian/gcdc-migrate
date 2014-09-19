<?=Render::view('common/navigation', $templateData)?>

<div class="jumbotron">
	<div class="container no-jumbo">

		<?=Render::view('common/notifications')?>
		<h1><span class=".glyphicon .glyphicon-user"></span><?=ucwords($account['username']['name'])?></h1>
		<p>Beyond you profile data, there is a list with all the services that are authorized for this account. The API Token is used for backup and migrate operation, it will be automatically refresh at expiration.</p>

		<br />

		<div class="account-info" <?= !$intro ? 'data-intro="Data transparency is very important for us. This is what we know about you." data-step="1"' : ''?>>
			<img src="<?=Util::profileImageUrl($account['userProfile'], 274);?>" alt="avatar" class="img-rounded img-responsive pull-left" title="Profile Image">
			<strong>Name</strong><br />
			<?=ucwords($account['username']['name'])?><br />
			<br /><strong>Email</strong><br />
			<?=$account['username']['email']?><br />
			<br /><strong>Last Login</strong><br />
			<?=(isset($account['username']['last_login']) ? Util::countdown($account['username']['last_login']) : 'Now.' )?><br />
			<br /><strong>API Token Expires</strong><br />
			<? if( $account['credentials']['expires_at'] < date(DATE_TIME, time()) ): ?>
				<span class="text-danger">Already Expired</span><br /><br />
			<? else: ?>
				in <?=Util::countup($account['credentials']['expires_at'])?><br /><br />
			<? endif; ?>
			<p>
				<a href="<?=$account['userProfile']['url']?>" target="_blank" class="btn btn-info">Google+</a>
				<a href="<?=Render::link('accounts/unlink/' . $account['username']['id'])?>" class="btn btn-danger">Remove</a>
			</p>
		</div>

	</div>
</div>

<div class="container">

	<div class="legend clear">
		<span>ACTIVITY</span>
		<hr />
	</div>
	<div class="clearfix" <?= !$intro ? 'data-intro="A full history of your actions on our platform." data-step="2"' : ''?>>
		<? if($tasks): ?>
			<? \Render::view('activity/table', compact('tasks')); ?>
		<? else: ?>
			<? \Render::view('common/empty', false); ?>
		<? endif; ?>
	</div>

	<div class="legend clear margin-top">
		<span>YOUR PERMISSIONS</span>
		<a href="<?=Render::link('accounts/permissions')?>" class="pull-right text-info">MANAGE PERMISSIONS</a>
		<hr />
	</div>
	<table class="table table-responsive" <?= !$intro ? 'data-intro="These are all the google services that you approved." data-step="3"' : ''?>>
		<thead>
			<tr>
				<th style="width: 120px"></th>
				<th style="width: 170px">Title</th>
				<th>Description</th>
				<th style="width: 30px;">Read</th>
				<th style="width: 30px;">Write</th>
				<th style="width: 30px;"></th>
			</tr>
		</thead>
		<tbody>
		<? foreach($services as $service): ?>
			<? if(in_array($service['id'], $selectedServices)): ?>
				<tr>
					<td colspan="6" style="position: relative">
						<div class="service service-small <?=$service['image_css']?>-small" title="<?=$service['name']?>"></div>
						&nbsp;<strong><?=$service['name']?></strong>
						<a href="#" class="js-info-sign info-sign pull-right" title="What's this?" data-src="<?=$service['id']?>"><span class="glyphicon glyphicon-info-sign"></span></a>
					</td>
				</tr>
				<? foreach($service['permissions'] as $permission): ?>
					<tr class="info" data-id="<?=$service['id']?>">
						<td></td>
						<td><?=$permission['title']?></td>
						<td><?=$permission['description']?></td>
						<td class="text-center"><span class="glyphicon glyphicon-<?=$permission['read'] ? 'ok text-success' : 'remove text-danger'?>"></span></td>
						<td class="text-center"><span class="glyphicon glyphicon-<?=$permission['write'] ? 'ok text-success' : 'remove text-danger'?>"></span></td>
						<td></td>
					</tr>
				<? endforeach; ?>
			<? endif; ?>
		<? endforeach; ?>
		</tbody>
	</table>


</div>

<?=Render::view('common/copyright')?>

