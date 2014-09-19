<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="description" content="Quickly and easily download, upload and move google data from one account to another.">
	<meta name="author" content="Andrei Demian">
	<link rel="shortcut icon" href="<?=Render::image('favicon.ico')?>">

	<title><?=(isset($title) ? $title : 'Migrate Google Data')?></title>

	<!-- Facebook -->
	<meta property="og:title" content="Migrate your google data"/>
	<meta property="og:description" content="Quickly and easily download, upload and move google data from one account to another."/>
	<meta property="og:image" content="<?=Render::image('logo.png')?>"/>
	<meta property="og:url" content="<?=Render::image('logo.png')?>"/>
	<meta property="og:site_name" content="Migrate"/>
	<meta property="og:type" content="tool"/>

	<!-- Bootstrap core CSS -->
	<link href="//netdna.bootstrapcdn.com/bootstrap/3.0.2/css/bootstrap.min.css" rel="stylesheet">
	<link href="//netdna.bootstrapcdn.com/font-awesome/4.0.3/css/font-awesome.css" rel="stylesheet">
	<link href="//cdnjs.cloudflare.com/ajax/libs/intro.js/0.5.0/introjs.css" rel="stylesheet">
	<link href="//fonts.googleapis.com/css?family=Roboto" rel="stylesheet" type="text/css">
	<link href="<?=Render::css('common/common.css')?>" rel="stylesheet">
	<link href="<?=Render::css('common/loader.css')?>" rel="stylesheet">

	<!-- Custom styles for this template -->
	<?
		if(isset($styles) && $styles) {
			foreach($styles as $style) {
				if(stripos($style, '://') !== false) {
					echo '<link href="' . $style . '" rel="stylesheet">' . PHP_EOL;
				} else {
					echo '<link href="' . Render::css($style) . '" rel="stylesheet">' . PHP_EOL;
				}
			}
		}
	?>
	<script type="text/javascript">
		paceOptions = {
			ajax: {
				trackMethods: ['GET', 'POST']
			}
		};
	</script>
	<script src="<?=Render::js('libs/pace.js')?>"></script>


</head>

<body <?=isset($bodyId) ? 'id="' . $bodyId . '"': ''?>>

	<?=Render::view('common/notifications')?>
