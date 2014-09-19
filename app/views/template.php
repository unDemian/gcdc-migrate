<?
	Render::view('common/header', $templateData);

	Render::view($templateData['template'], $templateData);

	Render::view('common/footer', $templateData);