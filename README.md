#What is it?
_CherryCloud_ is CherryTree file format editor for Nextcloud/owncloud (just for .ctb extension for now).

#How to use?
* Create any _[filename].ctb_ with CherryTree program
* Upload _[filename].ctb_ to nextcloud/owncloud, edit file there: ``https://[your-cloud-server]/index.php/apps/cherrycloud?f=[filename].ctb``
* Recommended CherryTree file size should not be big (about ``<= 5MB``), because for now every page refresh in browser full file is getting loaded
* Current development phase is ``pre-alpha``. Just nodes of plain text are supported.  

# Common set up
* Use windows/linux supported [CherryTree program](www.giuspen.com/cherrytree/), [download here](www.giuspen.com/cherrytree/#downl)
* Use on your own server self-hosted nextcloud/owncloud solution for private files synchronisation to all your devices, [download link](https://nextcloud.com/install)
* Install _CherryCloud_ in nextcloud/owncloud settings section

# Recommended Desktop set up  
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

## Running tests
After [Installing PHPUnit](http://phpunit.de/getting-started.html) run:
```
    phpunit -c phpunit.xml
```