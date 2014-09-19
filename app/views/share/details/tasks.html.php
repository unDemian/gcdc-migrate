<section class="block clearfix clear">
	<h3>Shared Data</h3>

	<!-- HEADING -->
	<hr class="no-margin-top"/>
	<? $stats = json_decode($service['stats'], true); ?>
	<div class="col-xs-4 no-padding"><i>Shared lists: <strong><?=count($migratedData['lists'])?></strong></i></div>
	<br />
	<hr class="clear"/>

	<div class="sub-block clearfix clear">
		<? if( isset($migratedData['lists'])): ?>
			<? foreach($data['lists'] as $list): ?>
				<? if(in_array($list['id'], $migratedData['lists'])): ?>
					<div class="col-xs-2 no-padding margin-bottom-md clearfix">
						<img src="<?=$list['picture']?>" width="24" class="img-responsive img-rounded pull-left" />
						&nbsp;<strong><?=Util::wrap($list['name'])?></strong>
					</div>
				<? endif; ?>
			<? endforeach; ?>
		<? endif; ?>
	</div>
</section>