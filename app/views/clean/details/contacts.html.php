<section class="block clearfix clear">
	<h3>Cleaned Data</h3>

	<!-- HEADING -->
	<hr class="no-margin-top"/>
	<? $stats = json_decode($service['stats'], true); ?>
	<div class="col-xs-4 no-padding"><i>Cleaned Contacts: <strong><?=count($migratedData['contacts'])?></strong></i></div>
	<br />
	<hr class="clear"/>

	<div class="sub-block clearfix clear">
		<? if( isset($migratedData['contacts'])): ?>
			<? foreach($data['contacts'] as $contact): ?>
				<? if(in_array($contact['id'], $migratedData['contacts'])): ?>
					<div class="col-xs-2 no-padding margin-bottom-md clearfix">
						<img src="<?=$contact['picture']?>" width="24" class="img-responsive img-rounded pull-left" />
						&nbsp;<strong><?=Util::wrap($contact['name'])?></strong>
					</div>
				<? endif; ?>
			<? endforeach; ?>
		<? endif; ?>
	</div>
</section>