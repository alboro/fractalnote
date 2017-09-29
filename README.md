# What is it?
_FractalNote_ is hierarchical notes web-editor for Nextcloud/Owncloud with CherryTree *.ctb files support. Demo: http://cloud.aldem.ru/index.php/apps/fractalnote?f=/demo.ctb

# How to use?
* Create any _[filename].ctb_ with CherryTree program
* Upload _[filename].ctb_ to Nextcloud/Owncloud, edit file there: ``https://[your-cloud-server]/index.php/apps/fractalnote?f=[filename].ctb``
* Recommended CherryTree file size should not be big (about ``<= 5MB``), because for now every page refresh in browser full file is getting loaded
* Current development phase is ``pre-alpha``. Just nodes of plain text are editable, others are read-only.  

# Requirements
* Use windows/linux supported [CherryTree program](https://www.giuspen.com/cherrytree/), [download here](https://www.giuspen.com/cherrytree/#downl)
* Use such Dropbox/Google Drive analog as self-hosted Nextcloud/Owncloud. It synchronises your private files between all your devices. [Download link.](https://nextcloud.com/install)
* _FractalNote_ installed and enabled in Nextcloud/Owncloud

# Recommendations  
* In CherryTree program preferences keep checked option Edit->Preferences->Miscellaneous->``Reload after external update to CT* file``
* Check also autosave
* Install Nextcloud/Owncloud desktop synchronisation client 
* Put _[filename].ctb_ under Nextcloud/Owncloud synchronisation folder 

# Tehnologies used
* jquery jsTree plugin, that provides interactive trees: https://github.com/vakata/jstree
* Reponsive jsTree Twitter Bootstrap 3 Compatible Theme: https://github.com/orangehill/jstree-bootstrap-theme
* Handlebars.js https://github.com/wycats/handlebars.js/
* Inspired by awesome CherryTree: https://github.com/giuspen/cherrytree 

# Installation
## Server requirements
* PHP ``>= 5.6``
* Nextcloud ``>= 12.0`` _OR_ OwnCloud ``>= 8.1``
## Steps
* Place this app in ``[nextcloud/owncloud installation folder]/apps/fractalnote``
* To be able to open files from file list app, add ``.ctb`` file type to the ``[nextcloud/owncloud installation folder]/config/mimetypemapping.json`` like that:
```
{
    "ctb": ["application/cherrytree-ctb"]
}
```
And run in the command line:
```
occ maintenance:mimetype:update-db --repair-filecache
```
* Enable _FractalNote_ in Nextcloud/Owncloud settings UI

## Running tests
After [Installing PHPUnit](http://phpunit.de/getting-started.html) run:
```
    phpunit -c phpunit.xml
```
