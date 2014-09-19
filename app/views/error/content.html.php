<?=Render::view('common/navigation', $templateData)?>

<div class="jumbotron">
	<div class="container no-jumbo">
		<h1>No, no! what you desire no es here...</h1>

		<iframe width="640" height="480" src="//www.youtube.com/embed/hc46l54Cz10" frameborder="0" allowfullscreen></iframe>

	</div>
</div>

<div class="container">

	<div class="btn-group clearfix">
		<a href="<?=@Render::link($_SERVER['HTTP_REFERRER'])?>" class="btn btn-lg btn-primary" title="Back" style="width: 310px;">
			Back
		</a>
	</div>

</div>

<?=Render::view('common/copyright')?>