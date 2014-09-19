<div class="notifications clearfix">
	<? if(Util::hasNotice()): ?>
		<? $notice = Util::notice(); ?>
		<div class="notification notification-<?=$notice['type']?> text-center alert-dismissable <?=isset($notice['persistent']) ? 'persistent' : ''?>">
			<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
			<?=$notice['text']?>
		</div>
	<? endif; ?>
</div>