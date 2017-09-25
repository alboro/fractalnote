# What is it?
_CherryCloud_ is CherryTree file format editor for Nextcloud/owncloud (just for .ctb extension for now).

# How to use?
* Create any _[filename].ctb_ with CherryTree program
* Upload _[filename].ctb_ to nextcloud/owncloud, edit file there: ``https://[your-cloud-server]/index.php/apps/cherrycloud?f=[filename].ctb``
* Recommended CherryTree file size should not be big (about ``<= 5MB``), because for now every page refresh in browser full file is getting loaded
* Current development phase is ``pre-alpha``. Just nodes of plain text are supported.  

# Requirements
* Use windows/linux supported [CherryTree program](https://www.giuspen.com/cherrytree/), [download here](https://www.giuspen.com/cherrytree/#downl)
* Use self-hosted such Dropbox/Google Drive analog as Nextcloud/Owncloud. It synchronises your private files between all your devices. [Download link.](https://nextcloud.com/install)
* _CherryCloud_ installed and enabled in Nextcloud/Owncloud settings UI

# Recommended Desktop computer set up  
* In CherryTree program preferences keep checked option ``reload after external update to CT* file``
* Check also autosave
* Install nextcloud/owncloud desktop synchronisation client 
* Put _[filename].ctb_ under nextcloud/owncloud synchronisation folder 

# Server requirements
* PHP ``>= 5.6``
* Nextcloud ``>= 12.0`` _OR_ ownCloud ``>= 8.1``

# CherryCloud installation
* Place this app in ``[nextcloud installation folder]/apps/``
* To be able to open files from file list app, add ``.ctb`` file type to the ``[nextcloud installation folder]/config/mimetypemapping.json`` like that:
```
{
    "ctb": ["application/cherrytree-ctb"]
}
```
And run in the command line:
```
maintenance:mimetype:update-db --repair-filecache
```
* Enable _CherryCloud_ in Nextcloud/Owncloud settings UI

## Running tests
After [Installing PHPUnit](http://phpunit.de/getting-started.html) run:
```
    phpunit -c phpunit.xml
```