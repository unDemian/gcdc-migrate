<div class="header navbar navbar-fixed-top ">
	<div class="container relative">
		<a href="<?=Render::link('')?>" title="Migrate" class="logo block clearfix centered">
			<img src="<?=Render::image('logo.png')?>" alt="Migrate" />
		</a>
	</div>
</div>

<div class="jumbotron">
	<div class="container no-jumbo text-center">
		<?=Render::view('common/notifications')?>
		<br />

		<? if($profile): ?>
			<h1>Sharing is caring</h1>
			<p>Your friend <strong><?=$profile['username']['name']?></strong> has shared the following <strong><?=$service['name']?></strong> data with you. Click the big orange button to import it to your Google Account.</p>
			<br />
		<? else: ?>
			<h1>Ooopsie!</h1>
			<p>It seems that the link you tried to access is either incorrect or expired.</p>
		<? endif; ?>

	</div>
</div>

<div class="container clearfix">

	<div id="the-task" data-id="<?=$share['link']?>"></div>
	<div class="the-service" data-scopes="<?=$service['scopes']?>"></div>

	<div class="action">
		<div class="clear clearfix">
			<? if($backups): ?>
				<? foreach($backups as $type => $items): ?>
					<? if(isset($data[$type]) && $data[$type]): ?>
						<div class="legend clear">
							<span><?=strtoupper($type)?></span>
							<hr />
						</div>

						<div class="services-little clear clearfix">
							<? foreach($items as $item): ?>
								<? if(in_array($item['id'], $data[$type])): ?>
									<div class="list-group pull-left clearfix little margin-bottom-xs" style="width: 180px; margin-right: 10px;">
										<a href="#" class="list-group-item clearfix selected little">
											<img src="<?=$item['picture']?>" width="16" alt="avatar" class="small-avatar" />
											<h5 class="list-group-item-heading" style="padding-top: 3px;"><?=$item['name']?></h5>
										</a>
									</div>
								<? endif; ?>
							<? endforeach; ?>
						</div>
					<? endif; ?>
				<? endforeach; ?>
			<? endif; ?>
		</div>

		<? if($profile): ?>
			<div id="gSignInWrapper">
				<div id="customBtn" class="customGPlusSignIn">
					<span class="icon"></span>
					<span class="buttonText">Save to my Google account</span>
				</div>
			</div>
		<? else: ?>
			<a href="<?=@Render::link($_SERVER['HTTP_REFERRER'])?>" class="btn btn-lg btn-primary" title="Back" style="width: 310px; margin: 0px auto; display: block">
				Back
			</a>
		<? endif; ?>
	</div>

</div>

<footer class="clearfix">
	<div class="container">
		<div class="clearfix centered copyright">
			<div class="pull-left">With love from <strong>Timisoara</strong>, Romania</div>
			<div class="pull-left"><img src="<?=Render::image('copyright.png')?>" alt="copyright" title="copyright" /></div>
			<div class="pull-left">2013 <strong>Andrei Demian</strong>. All rights reserved.</div>
		</div>
	</div>
</footer>