<div class="step-four clearfix">
	<? if($services): ?>
		<div class="clear clearfix">
			<h3><strong>This is it</strong></h3>
			<p>
				Remember that all your actions are reversible. After the <strong><?=Util::action()?></strong> process is done, you can undo it from the <strong>activity</strong> tab.<br />
				Below you can review your data and if everything is alright hit the <strong>Start</strong> button.
			</p>
			<br />

			<div class="clear">
				<h4>Accounts & Action</h4>
				<div class="pull-left clearfix">
					<div class="list-group pull-left selected" style="margin-right: 10px;">
						<a href="#" class="list-group-item clearfix selected text-center little">
							<img src="<?=Util::profileImageUrl($_SESSION['wizard']['source']['userProfile'], 16);?>" alt="avatar" class="small-avatar pull-left" />
							<h5 class="list-group-item-heading pull-left" style="padding-top: 3px;"><?=$_SESSION['wizard']['source']['username']['email']?></h5>
						</a>
					</div>
				</div>

				<div class="pull-left clearfix">
					<div class="list-group pull-left selected text-center" style="margin-right: 10px;">
						<a href="#" class="list-group-item clearfix selected text-center little">
							<i class="fa fa-expand  pull-left <?=(isset($_SESSION['wizard']['action']) && $_SESSION['wizard']['action'] != '0') ? 'hide' : ''?>" title="Migrate" data-id="0"></i>
							<i class="fa fa-retweet pull-left <?=(isset($_SESSION['wizard']['action']) && $_SESSION['wizard']['action'] != '1') ? 'hide' : ''?>" title="Sync" data-id="1"></i>
							<i class="fa fa-long-arrow-right pull-left <?=(isset($_SESSION['wizard']['action']) && $_SESSION['wizard']['action'] != '2') ? 'hide' : ''?>" title="Move" data-id="2"> </i>
						</a>
					</div>
				</div>

				<div class="pull-left clearfix">
					<div class="list-group pull-left selected" style="margin-right: 10px;">
						<a href="#" class="list-group-item clearfix selected text-center little">
							<img src="<?=Util::profileImageUrl($_SESSION['wizard']['destination']['userProfile'], 16);?>" alt="avatar" class="small-avatar pull-left" />
							<h5 class="list-group-item-heading pull-left" style="padding-top: 3px;"><?=$_SESSION['wizard']['destination']['username']['email']?></h5>
						</a>
					</div>
				</div>
			</div>

			<div class="clear clearfix">
				<h4>Services</h4>
				<? foreach($services as $service): ?>
					<div class="list-group pull-left selected" style="width: 150px; margin-right: 10px;">
						<a href="#" class="list-group-item clearfix selected text-center little">
							<div class="service service-small <?=$service['image_css']?>-small" title="<?=$service['name']?>"></div>
							<h5 class="list-group-item-heading" style="padding-top: 3px;"><?=$service['name']?></h5>
						</a>
					</div>
				<? endforeach; ?>
			</div>
		</div>

	<? else: ?>
		<div class="alert alert-info text-center alert-dismissable no-common">
			Please go back and select at least one service.</a>.
		</div>
	<? endif; ?>
</div>