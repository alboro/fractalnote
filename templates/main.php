<?php
$jsTreeThemeName = 'proton'; //default

script('cherrycloud',	'vendor/vakata-jstree/dist/jstree');
style('cherrycloud',	"vendor/vakata-jstree/$jsTreeThemeName/style.min");

style('cherrycloud', 'cherrycloud');
//script('cherrycloud',	'vendor/handlebars'); // @todo: vendor_script()
script('cherrycloud',	'vendor/handlebars-v4.0.10'); // @todo: vendor_script()
script('cherrycloud', 'cherrycloud');
?>

<div id="app" class="cherrycloud-app">
    <div id="app-navigation" class="cherrycloud-navigation">
        <?php print_unescaped($this->inc('part.navigation', ['theme_name' => $jsTreeThemeName])); ?>
        <?php print_unescaped($this->inc('part.settings')); ?>
	</div>

	<div id="app-content" class="cherrycloud-app-content">
		<div id="app-content-wrapper" class="cherrycloud-app-content-wrapper">
			<?php print_unescaped($this->inc('part.content')); ?>
		</div>
	</div>
</div>
