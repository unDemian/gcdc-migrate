		<!-- Global Variables -->
		<script type="text/javascript">
			var general = {
					base: {
						path: '<?=BASE_PATH?>',
						url: '<?=BASE_URL?>'
					},
					oauth: {
						clientId: '<?=OAUTH_CLIENT_ID?>',
						state: '<?=session_id()?>'
					},
					redirect: {
						logout: '<?=(isset($_SESSION['logout'])) ? 'stop' : 'dashboard'?>'
					}
			};

			<? if($bodyId != 'login'): ?>

				// Follow G+ Button
				(function() {
					var po = document.createElement('script'); po.type = 'text/javascript'; po.async = true;
					po.src = 'https://apis.google.com/js/plusone.js';
					var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(po, s);
				})();

				// Facebook Follow
				(function(d, s, id) {
					var js, fjs = d.getElementsByTagName(s)[0];
					if (d.getElementById(id)) return;
					js = d.createElement(s); js.id = id;
					js.src = "//connect.facebook.net/en_US/all.js#xfbml=1";
					fjs.parentNode.insertBefore(js, fjs);
				}(document, 'script', 'facebook-jssdk'));

				general.login = false;
			<? else: ?>
				general.login = true;
			<? endif; ?>

			// Remove logout switch
			<? if(isset($_SESSION['logout'])) unset($_SESSION['logout']); ?>

		</script>

		<!-- General scripts -->
		<script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
		<script src="//netdna.bootstrapcdn.com/bootstrap/3.0.0/js/bootstrap.min.js"></script>
		<script src="//cdnjs.cloudflare.com/ajax/libs/favico.js/0.3.4/favico.js"></script>
		<script src="//cdnjs.cloudflare.com/ajax/libs/intro.js/0.5.0/intro.min.js"></script>
		<script src="<?=Render::js('libs/jquery.ui.min.js')?>"></script>
		<script src="<?=Render::js('common/common.js')?>"></script>
		<script src="<?=Render::js('common/request.js')?>"></script>
		<script src="<?=Render::js('common/modernizr.custom.js')?>"></script>

		<!-- Custom scripts for this template -->
		<?
			if(isset($scripts) && $scripts) {
				foreach($scripts as $script) {
					if(stripos($script, '://') !== false) {
						echo '<script src="' . $script . '"></script>' . PHP_EOL;
					} else {
						echo '<script src="' . Render::js($script) . '"></script>' . PHP_EOL;
					}
				}
			}
		?>
	</body>
</html>