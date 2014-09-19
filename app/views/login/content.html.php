<div class="header navbar navbar-fixed-top ">
	<div class="container relative">
		<a href="#" title="Migrate" class="logo block clearfix centered">
			<img src="<?=Render::image('logo.png')?>" alt="Migrate" />
		</a>
	</div>
</div>

<div class="container clearfix">

	<div class="action text-center">
		<h1>Sync & Backup</h1>
		<p class="lead">Quickly and easily download, upload and migrate google data from one account to another.Currently we support all major Google Products.</p>

		<div id="signinButton" class="blurred">
			<span class="g-signin"
				  data-scope="<?=Auth::oAuthScopes()?>"
				  data-clientid="<?=OAUTH_CLIENT_ID?>"
				  data-redirecturi="postmessage"
				  data-accesstype="offline"
				  data-approvalprompt="auto"
				  data-cookiepolicy="single_host_origin"
				  data-width="wide"
				  data-height="tall"
				  data-callback="signInCallback">
			</span>
		</div>
	</div>

	<div class="screenshots container clearfix">
		<img src="<?=Render::image('login/screen-left.png')?>" alt="screen" class="img-responsive visible-sm visible-md visible-lg screen-left" />
		<img src="<?=Render::image('login/screen-center.png')?>" alt="screen" class="img-responsive visible-sm visible-md visible-lg screen-center" />
		<img src="<?=Render::image('login/screen-right.png')?>" alt="screen" class="img-responsive visible-sm visible-md visible-lg screen-right" />
	</div>

</div>

<footer class="clearfix">
	<div class="container">
		<div class="clearfix centered copyright">
			<div class="pull-left">With love from <strong>Timisoara</strong>, Romania</div>
			<div class="pull-left"><img src="<?=Render::image('copyright.png')?>" alt="copyright" title="copyright" /></div>
			<div class="pull-left">2013 <strong>Andrei Demian</strong>. All rights reserved.</div>
		</div>
	</div>
</footer>