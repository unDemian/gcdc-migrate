<?
	if( Auth::showWizard()) {
		Render::view('common/navigation-wizard', $templateData);
	} else {
		Render::view('common/navigation-default', $templateData);
	}