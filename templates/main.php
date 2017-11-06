<?php
namespace OCA\FractalNote;

use OCA\FractalNote\AppInfo\Application;

$jsTreeThemeName = 'proton'; //default

vendor_style(Application::APP_NAME,
    'orangehill/jstree-bootstrap-theme/dist/themes/' . $jsTreeThemeName . '/style.min');
style(Application::APP_NAME, 'main');

vendor_script(Application::APP_NAME,	'vakata/jstree/dist/jstree.min');
vendor_script(Application::APP_NAME,	'components/handlebars.js/handlebars.min');
script(Application::APP_NAME, 'main');

?>
<div id="app" class="note-app">
    <div id="app-navigation" class="note-navigation">
        <?php print_unescaped($this->inc('part.navigation', ['theme_name' => $jsTreeThemeName])); ?>
        <?php print_unescaped($this->inc('part.settings')); ?>
	</div>

	<div id="app-content" class="note-app-content">
		<div id="app-content-wrapper" class="note-app-content-wrapper">
            <script id="note-content-tpl" type="text/x-handlebars-template"><?php
                print_unescaped($this->inc('frontend/editor.handlebars'));
            ?></script>
            <div id="note-editor" class="note-editor"></div>
		</div>
	</div>
</div>
