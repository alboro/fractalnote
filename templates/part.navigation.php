<script type="application/json" id="js-tree-data"
        data-mtime="<?php print_unescaped($_['mtime']); ?>"
        data-theme-name="<?php print_unescaped($_['theme_name']); ?>"><?php
    print_unescaped(json_encode($_['tree']));
?></script>

<input type="text" id="cherrycloud-searchbar" style="width: 100%;" />
<div id="cherrycloud-navigation" class="cherrycloud-navigation"></div>
