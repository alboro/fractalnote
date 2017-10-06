# What is it?
_FractalNote_ is online editor of hierarchical notes for Nextcloud/Owncloud server.

You can view/edit CherryTree (*.ctb) files with it in browser.

Current development phase is ``pre-alpha``. Just nodes of plain text are editable, others are read-only.

[See FractalNote in action](http://cloud.aldem.ru/index.php/apps/fractalnote?f=/demo.ctb)

# About Nextcloud/Owncloud
This is self-hosted Dropbox/Google Drive analog.
It is able to synchronise your private files between all your devices. [Download link.](https://nextcloud.com/install)

# How to use _FractalNote_?
* Use [CherryTree program](https://www.giuspen.com/cherrytree/#downl) for windows/linux desktop computers to to create hierarchical notes.
* Save your hierarchical note tree as _[filename].ctb_ with [CherryTree program.](https://www.giuspen.com/cherrytree/)
* Upload _[filename].ctb_ to Nextcloud/Owncloud
* Edit uploaded file online: ``https://[your-cloud-server]/index.php/apps/fractalnote?f=[filename].ctb``
* Install Nextcloud/Owncloud desktop [synchronisation client.](https://nextcloud.com/install/#install-clients)
* Put _[filename].ctb_ under Nextcloud/Owncloud synchronisation folder of your desktop computer. From now the file will be uploaded into server automatically after every saving.
* In CherryTree program preferences keep checked autosave option and Edit->Preferences->Miscellaneous->``Reload after external update to CT* file`` option. From now file changes made online will be downloaded automatically even while you navigating through the file with CherryTree program.
* _[filename].ctb_ size should not be big (about ``<= 5MB``), because for now the full file gets loaded with every page refresh in browser. 

# Installation
## Server requirements
* PHP ``>= 5.6``
* Nextcloud ``>= 12.0`` _OR_ OwnCloud ``>= 8.1``
## Steps
* Install into your web server [Nextcloud/Owncloud.](https://nextcloud.com/install)
* (This step is not required.) To open _[filename].ctb_ from file list nextcloud/owncloud app you need:

Add ``.ctb`` file type to the ``[nextcloud/owncloud installation folder]/config/mimetypemapping.json`` like that:
```
{
    "ctb": ["application/cherrytree-ctb"]
}
```
Run in the command line:
```
occ maintenance:mimetype:update-db --repair-filecache
```
* Place _FractalNote_ in ``[nextcloud/owncloud installation folder]/apps/fractalnote``
* Enable _FractalNote_ in Nextcloud/Owncloud settings UI

# Running tests
After [Installing PHPUnit](http://phpunit.de/getting-started.html) run:
```
    phpunit -c phpunit.xml
```

# Tehnologies used
* JQuery jsTree plugin, that provides interactive trees: https://github.com/vakata/jstree
* Reponsive jsTree Twitter Bootstrap 3 Compatible Theme: https://github.com/orangehill/jstree-bootstrap-theme
* Handlebars.js https://github.com/wycats/handlebars.js/
* Inspired by awesome CherryTree: https://github.com/giuspen/cherrytree 
