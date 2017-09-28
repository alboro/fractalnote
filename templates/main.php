<?php
namespace OCA\CherryCloud;

use OCA\CherryCloud\AppInfo\Application;

$jsTreeThemeName = 'proton'; //default

script(Application::APP_NAME,	'vendor/vakata-jstree/dist/jstree');
style(Application::APP_NAME,	"vendor/vakata-jstree/$jsTreeThemeName/style.min");

style(Application::APP_NAME, 'main');
script(Application::APP_NAME,	'vendor/handlebars-v4.0.10'); // @todo: vendor_script()
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
                print_unescaped($this->inc('dynamic-area.handlebars'));
            ?></script>
            <div id="note-editor" class="note-editor"></div>
		</div>
	</div>
</div>
