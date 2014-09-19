<section class="block clearfix clear">
	<h3>Shared Data</h3>

	<!-- HEADING -->
	<hr class="no-margin-top"/>
	<? if(isset($migratedData['playlists'])): ?>
		<div class="col-xs-4 no-padding"><i>Shared Playlists: <strong><?=count($migratedData['playlists'])?></strong></i></div>
	<? endif; ?>

	<? if(isset($migratedData['subscriptions'])): ?>
		<div class="col-xs-4 no-padding"><i>Shared Subscriptions: <strong><?=count($migratedData['subscriptions'])?></strong></i></div>
	<? endif; ?>
	<br />
	<hr class="clear"/>

	<div class="sub-block clearfix clear">
		<? if( isset($migratedData['playlists'])): ?>
			<h5 class="legend"><span>PLAYLISTS</span></h5><br />
			<? foreach($data['playlists'] as $playlist): ?>
				<? if(in_array($playlist['id'], $migratedData['playlists'])): ?>
					<div class="col-xs-2 no-padding margin-bottom-md">
						<a href="http://www.youtube.com/playlist?list=<?=$playlist['id']?>" target="blank">
							<img src="<?=$playlist['picture']?>" width="64" class="img-responsive img-rounded" />
							<strong><?=Util::wrap($playlist['name'])?></strong>
						</a>
					</div>
				<? endif; ?>
			<? endforeach; ?>
		<? endif; ?>
	</div>

	<div class="sub-block clearfix clear">
		<? if( isset($migratedData['subscriptions'])): ?>
			<h5 class="legend"><span>SUBSCRIPTIONS</span></h5><br />
			<? foreach($data['subscriptions'] as $subscription): ?>
				<? if(in_array($subscription['id'], $migratedData['subscriptions'])): ?>
					<div class="col-xs-2 no-padding margin-bottom-md">
						<a href="http://www.youtube.com/channel/<?=$subscription['id']?>" target="blank">
							<img src="<?=$subscription['picture']?>" width="64" class="img-responsive img-rounded" />
							<strong><?=Util::wrap($subscription['name'])?></strong>
						</a>
					</div>
				<? endif; ?>
			<? endforeach; ?>
		<? endif; ?>
	</div>
</section>