<section class="block clearfix clear">
	<h3><?=($task['type'] == 'sync' ? ucfirst($task['type']) . 'ed' : ucfirst($task['type']) . 'd')?> Data</h3>

	<!-- HEADING -->
	<hr class="no-margin-top"/>
	<? $stats = json_decode($service['stats'], true); ?>
	<div class="col-xs-4 no-padding"><i><?=($task['type'] == 'sync' ? ucfirst($task['type']) . 'ed' : ucfirst($task['type']) . 'd')?> Playlists: <strong><?=$stats['playlists']?></strong></i></div>
	<div class="col-xs-4 no-padding"><i><?=($task['type'] == 'sync' ? ucfirst($task['type']) . 'ed' : ucfirst($task['type']) . 'd')?> Videos: <strong><?=$stats['videos']?></strong></i></div>
	<div class="col-xs-4 no-padding"><i><?=($task['type'] == 'sync' ? ucfirst($task['type']) . 'ed' : ucfirst($task['type']) . 'd')?> Subscriptions: <strong><?=$stats['subscriptions']?></strong></i></div>
	<br />
	<hr class="clear"/>

	<div class="sub-block clearfix clear">
		<? if( isset($migratedData['playlists'])): ?>
			<h5 class="legend"><span>PLAYLISTS</span></h5><br />
			<? foreach($migratedData['playlists'] as $playlist): ?>
				<div class="col-xs-2 no-padding margin-bottom-md">
					<a href="http://www.youtube.com/playlist?list=<?=$playlist['youtube_playlist_id']?>" target="blank">
						<img src="<?=$playlist['picture']?>" width="64" class="img-responsive img-rounded" />
						<strong><?=Util::wrap($playlist['title'])?></strong>
						<br />
						<i><?=$playlist['videos_count']?> video(s)</i>
					</a>
				</div>
			<? endforeach; ?>
		<? endif; ?>
	</div>

	<div class="sub-block clearfix clear">
		<? if( isset($migratedData['subscriptions'])): ?>
			<h5 class="legend"><span>SUBSCRIPTIONS</span></h5><br />
			<? foreach($migratedData['subscriptions'] as $subscription): ?>
				<div class="col-xs-2 no-padding margin-bottom-md">
					<a href="http://www.youtube.com/channel/<?=$subscription['channel_link']?>" target="blank">
						<img src="<?=$subscription['picture']?>" width="64" class="img-responsive img-rounded" />
						<strong><?=Util::wrap($subscription['title'])?></strong>
					</a>
				</div>
			<? endforeach; ?>
		<? endif; ?>
	</div>
</section>

<section class="block clearfix clear graph">
	<h3>Graph</h3>

	<!-- HEADING -->
	<hr class="no-margin-top"/>
	<div class="col-xs-3 no-padding"><div class="scheme scheme-from"></div><i>Copied from</i></div>
	<div class="col-xs-3 no-padding"><div class="scheme scheme-to"></div><i>Copied to</i></div>
	<div class="col-xs-3 no-padding"><div class="scheme scheme-move"></div><i>Moved from</i></div>
	<div class="col-xs-3 no-padding"><div class="scheme scheme-other"></div><i>Unaffected</i></div>
	<br />
	<hr class="clear"/>

	<div class="sub-block clearfix clear">
		<? if( isset($migratedData['playlists'])): ?>
			<h5 class="legend"><span>PLAYLISTS</span></h5>

			<!-- SOURCE -->
			<div class="col-xs-5 no-padding text-center">
				<strong>SOURCE</strong>
				<ul class="<?=($task['type'] != 'move' ? 'from' : 'cut')?> top" data-id="1">
					<? foreach($migratedData['playlistsGraph'][$task['user_id']] as $playlist): ?>
						<li><?=$playlist['title']?></li>
					<? endforeach ?>
				</ul>

				<? if($task['type'] == 'sync'): ?>
					<ul class="to" data-id="2">
						<? foreach($migratedData['playlistsGraph'][$task['user_affected_id']] as $playlist): ?>
							<li><?=$playlist['title']?></li>
						<? endforeach ?>
					</ul>
				<? endif; ?>

				<ul class="other bottom">
					<? foreach($graphData['source']['playlists'] as $playlist): ?>
						<? if( ! in_array($playlist['id'], $migratedData['playlistsIds'])): ?>
							<li><?=$playlist['title']?></li>
						<? endif; ?>
					<? endforeach ?>
				</ul>
			</div>

			<!-- ACTION -->
			<div class="col-xs-2 no-padding text-center relative">
				<div class="<?=($task['type'] != 'move' ? 'linking' : 'linking-cut')?> " data-id="1">
					<i class="fa fa-long-arrow-right"></i>
				</div>

				<? if($task['type'] == 'sync' && ($migratedData['subscriptionsGraph'][$task['user_affected_id']] || $migratedData['playlistsGraph'][$task['user_affected_id']])): ?>
					<div class="linking-inverse" data-id="2">
						<i class="fa fa-long-arrow-left"></i>
					</div>
				<? endif; ?>
			</div>

			<!-- DESTINATION -->
			<div class="col-xs-5 no-padding text-center">
				<strong>DESTINATION</strong>
				<ul class="to top">
					<? foreach($migratedData['playlistsGraph'][$task['user_id']] as $playlist): ?>
						<li><?=$playlist['title']?></li>
					<? endforeach ?>
				</ul>

				<? if($task['type'] == 'sync'): ?>
					<ul class="from">
						<? foreach($migratedData['playlistsGraph'][$task['user_affected_id']] as $playlist): ?>
							<li><?=$playlist['title']?></li>
						<? endforeach ?>
					</ul>
				<? endif; ?>

				<ul class="other bottom">
					<? foreach($graphData['destination']['playlists'] as $playlist): ?>
						<? if( ! in_array($playlist['id'], $migratedData['playlistsIds'])): ?>
							<li><?=$playlist['title']?></li>
						<? endif; ?>
					<? endforeach ?>
				</ul>
			</div>
		<? endif; ?>
	</div>

	<div class="sub-block clearfix clear">
		<? if( isset($migratedData['subscriptions'])): ?>
			<h5 class="legend"><span>SUBSCRIPTIONS</span></h5>

			<!-- SOURCE -->
			<div class="col-xs-5 no-padding text-center">
				<strong>SOURCE</strong>
				<ul class="<?=($task['type'] != 'move' ? 'from' : 'cut')?> top" data-id="3">
					<? foreach($migratedData['subscriptionsGraph'][$task['user_id']] as $subscription): ?>
						<li><?=$subscription['title']?></li>
					<? endforeach ?>
				</ul>

				<? if($task['type'] == 'sync'): ?>
					<ul class="to" data-id="4">
						<? foreach($migratedData['subscriptionsGraph'][$task['user_affected_id']] as $subscription): ?>
							<li><?=$subscription['title']?></li>
						<? endforeach ?>
					</ul>
				<? endif; ?>

				<ul class="other bottom">
					<? foreach($graphData['source']['subscriptions'] as $subscription): ?>
						<? if( ! in_array($subscription['id'], $migratedData['subscriptionsIds'])): ?>
							<li><?=$subscription['title']?></li>
						<? endif; ?>
					<? endforeach ?>
				</ul>
			</div>

			<!-- ACTION -->
			<div class="col-xs-2 no-padding text-center relative">
				<div class="<?=($task['type'] != 'move' ? 'linking' : 'linking-cut')?> " data-id="3">
					<i class="fa fa-long-arrow-right"></i>
				</div>

				<? if($task['type'] == 'sync'): ?>
					<div class="linking-inverse" data-id="4">
						<i class="fa fa-long-arrow-left"></i>
					</div>
				<? endif; ?>
			</div>

			<!-- DESTINATION -->
			<div class="col-xs-5 no-padding text-center">
				<strong>DESTINATION</strong>
				<ul class="to top">
					<? foreach($migratedData['subscriptionsGraph'][$task['user_id']] as $subscription): ?>
						<li><?=$subscription['title']?></li>
					<? endforeach ?>
				</ul>

				<? if($task['type'] == 'sync'): ?>
					<ul class="from">
						<? foreach($migratedData['subscriptionsGraph'][$task['user_affected_id']] as $subscription): ?>
							<li><?=$subscription['title']?></li>
						<? endforeach ?>
					</ul>
				<? endif; ?>

				<ul class="other bottom">
					<? foreach($graphData['destination']['subscriptions'] as $subscription): ?>
						<? if( ! in_array($subscription['id'], $migratedData['subscriptionsIds'])): ?>
							<li><?=$subscription['title']?></li>
						<? endif; ?>
					<? endforeach ?>
				</ul>
			</div>
		<? endif; ?>
	</div>
</section>