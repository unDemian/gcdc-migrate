<section class="block clearfix clear">
	<h3>Shared Data</h3>

	<!-- HEADING -->
	<hr class="no-margin-top"/>
	<? $stats = json_decode($service['stats'], true); ?>
	<div class="col-xs-4 no-padding"><i>Shared Calendars: <strong><?=count($migratedData['calendars'])?></strong></i></div>
	<br />
	<hr class="clear"/>

	<div class="sub-block clearfix clear">
		<? if( isset($migratedData['calendars'])): ?>
			<? foreach($data['calendars'] as $calendar): ?>
				<? if(in_array($calendar['id'], $migratedData['calendars'])): ?>
					<div class="col-xs-2 no-padding margin-bottom-md clearfix">
						<img src="<?=$calendar['picture']?>" width="24" class="img-responsive img-rounded pull-left" />
						&nbsp;<strong><?=Util::wrap($calendar['name'])?></strong>
					</div>
				<? endif; ?>
			<? endforeach; ?>
		<? endif; ?>
	</div>
</section>