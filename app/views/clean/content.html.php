<?=Render::view('common/notifications')?>

<?=Render::view('common/navigation', $templateData)?>

<div class="jumbotron">
	<div class="container no-jumbo" <?= !$intro ? 'data-intro="Hit me when you feel the need to cleanup your accounts." data-step="1"' : ''?>>

		<h1>Clean</h1>
		<p>Now you can bulk delete data from your Google account. Hit the clean button and follow the all the steps in the wizard. This operation is reversible, if something went wrong you can get your data back in no time.</p>

		<div class="btn-group clearfix">
			<button type="button" id="show-clean" class="btn btn-lg btn-primary" data-toggle="button" title="Clean" style="width: 310px;">
				<i class="fa fa-trash-o"></i>
			</button>
		</div>
	</div>
</div>

<div class="container">
	<div id="wizard" class="tabbable">
		<ul>
			<li class="head-tab-1 active"><a href="#tab1" data-id="1" data-toggle="tab">Choose Service</a></li>
			<li class="head-tab-2"><a href="#tab2" data-id="2" data-toggle="tab">Choose Data</a></li>
			<li class="head-tab-3"><a href="#tab3" data-id="3" data-toggle="tab">Go!</a></li>
		</ul>

		<div class="tab-content clearfix">

			<div class="tab-pane fade in active" id="tab1">
				<? Render::view('clean/services', array('services' => $services)) ?>
			</div>

			<div class="tab-pane fade" id="tab2">
			</div>

			<div class="tab-pane fade" id="tab3">
			</div>

			<div class="row">
				<div class="col-xs-1"><a class="btn btn-default previous" href="#">Previous</a></div>
				<div class="col-xs-10 bar-wrapper">
					<div id="bar" class="progress pull-left centered">
						<div class="progress-bar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%">
						</div>
					</div>
				</div>
				<div class="col-xs-1"><a class="btn btn-primary next pull-left" href="#">Next</a></div>
				<div class="col-xs-1"><button class="btn btn-success pull-left start">Start</button></div>
			</div>
		</div>
	</div>

	<div id="results" data-polling="<?=$polling?>" <?= !$intro ? 'data-intro="Keep track of all your actions. Something went wrong? no worries you can revert your actions." data-step="2"' : ''?>>
		<? if($tasks): ?>
			<? \Render::view('clean/table', compact('tasks')); ?>
		<? else: ?>
			<? \Render::view('common/empty', false); ?>
		<? endif; ?>
	</div>
</div>

<?=Render::view('common/copyright')?>