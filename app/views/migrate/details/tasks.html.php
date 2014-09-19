<section class="block clearfix clear">
	<h3><?=($task['type'] == 'sync' ? ucfirst($task['type']) . 'ed' : ucfirst($task['type']) . 'd')?> Data</h3>

	<!-- HEADING -->
	<hr class="no-margin-top"/>
	<? $stats = json_decode($service['stats'], true); ?>
	<div class="col-xs-4 no-padding"><i><?=($task['type'] == 'sync' ? ucfirst($task['type']) . 'ed' : ucfirst($task['type']) . 'd')?> Lists: <strong><?=$stats['lists']?></strong></i></div>
	<br />
	<hr class="clear"/>

	<div class="sub-block clearfix clear">
		<? if( isset($migratedData['lists'])): ?>
			<? foreach($migratedData['lists'] as $list): ?>
				<div class="col-xs-2 no-padding margin-bottom-md clearfix">
					<img src="<?=$list['entity_picture']?>" width="24" class="img-responsive img-rounded pull-left" />
					&nbsp;<strong><?=Util::wrap($list['entity_title'])?></strong>
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
		<? if( isset($migratedData['lists'])): ?>
			<!-- SOURCE -->
			<div class="col-xs-5 no-padding text-center">
				<strong>SOURCE</strong>
				<ul class="<?=($task['type'] != 'move' ? 'from' : 'cut')?> top" data-id="1">
					<? foreach($migratedData['listsGraph'][$task['user_id']] as $playlist): ?>
						<li><?=$playlist['entity_title']?></li>
					<? endforeach ?>
				</ul>

				<? if($task['type'] == 'sync'): ?>
					<ul class="to" data-id="2">
						<? foreach($migratedData['listsGraph'][$task['user_affected_id']] as $playlist): ?>
							<li><?=$playlist['entity_title']?></li>
						<? endforeach ?>
					</ul>
				<? endif; ?>

				<ul class="other bottom">
					<? foreach($graphData['source']['lists'] as $playlist): ?>
						<? if( ! in_array($playlist['id'], $migratedData['listsIds'])): ?>
							<li><?=$playlist['entity_title']?></li>
						<? endif; ?>
					<? endforeach ?>
				</ul>
			</div>

			<!-- ACTION -->
			<div class="col-xs-2 no-padding text-center relative">
				<div class="<?=($task['type'] != 'move' ? 'linking' : 'linking-cut')?> " data-id="1">
					<i class="fa fa-long-arrow-right"></i>
				</div>

				<? if($task['type'] == 'sync' && ($migratedData['listsGraph'][$task['user_affected_id']])): ?>
					<div class="linking-inverse" data-id="2">
						<i class="fa fa-long-arrow-left"></i>
					</div>
				<? endif; ?>
			</div>

			<!-- DESTINATION -->
			<div class="col-xs-5 no-padding text-center">
				<strong>DESTINATION</strong>
				<ul class="to top">
					<? foreach($migratedData['listsGraph'][$task['user_id']] as $playlist): ?>
						<li><?=$playlist['entity_title']?></li>
					<? endforeach ?>
				</ul>

				<? if($task['type'] == 'sync'): ?>
					<ul class="from">
						<? foreach($migratedData['listsGraph'][$task['user_affected_id']] as $playlist): ?>
							<li><?=$playlist['entity_title']?></li>
						<? endforeach ?>
					</ul>
				<? endif; ?>

				<ul class="other bottom">
					<? foreach($graphData['destination']['lists'] as $playlist): ?>
						<? if( ! in_array($playlist['id'], $migratedData['listsIds'])): ?>
							<li><?=$playlist['entity_title']?></li>
						<? endif; ?>
					<? endforeach ?>
				</ul>
			</div>
		<? endif; ?>
	</div>

</section>