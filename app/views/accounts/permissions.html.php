<?=Render::view('common/notifications')?>

<?=Render::view('common/navigation', $templateData)?>

<div class="jumbotron">
	<div class="container no-jumbo">

		<h1><?=$heading?></h1>
		<p><?=$description?></p>

		<? if($bodyId == 'accounts-update'): ?>
			<? Render::view('accounts/services', array('services' => $services, 'servicesSoon' => $servicesSoon, 'selectedServices' => $selectedServices))?>
		<? endif; ?>
	</div>
</div>

<div class="container">

	<? if($bodyId == 'accounts-add'): ?>
		<? Render::view('accounts/services', array('services' => $services, 'servicesSoon' => $servicesSoon, 'selectedServices' => $selectedServices))?>
		<br />
	<? endif; ?>

	<div id="gSignInWrapper">
		<div id="customBtn" class="customGPlusSignIn">
			<span class="icon"></span>
			<span class="buttonText">Authorize Selected Services with Google</span>
		</div>
	</div>
</div>

<?=Render::view('common/copyright')?>
