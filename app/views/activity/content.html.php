<?=Render::view('common/notifications')?>

<?=Render::view('common/navigation', $templateData)?>

<div class="jumbotron">
	<div class="container no-jumbo">

		<h1>Activity</h1>
		<p>Here you can see your latest and in progress activity. An opertaion is a sync, migrate, move, clean or share process. There are details about what the operation contains, which services are affected and what was the operation duration.</p>

	</div>
</div>

<div class="container">
	<div id="results" data-polling="<?=$polling?>">
		<? if($tasks): ?>
			<? \Render::view('activity/table', compact('tasks')); ?>
		<? else: ?>
			<? \Render::view('common/empty', false); ?>
		<? endif; ?>
	</div>
</div>

<?=Render::view('common/copyright')?>