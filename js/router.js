/**
 * NextCloud / ownCloud - cherrycloud
 *
 * Licensed under the Apache License, Version 2.0
 *
 * @author Alexander Demchenko <https://github.com/alboro>
 * @copyright Alexander Demchenko 2017
 */
OCA = OCA || {};
OCA.CherryCloudRouter = {
    /**
     * Handles the FileAction click event
     */
    _onEditorTrigger: function (filename, context) {
        window.document.location.href = OC.generateUrl('/apps/cherrycloud') + '?f=' + context.dir + '/' + filename;
    },
    /**
     * Registers the file actions
     */
    registerFileActions: function () {
        var mime = 'application/cherrytree-ctb';

        OCA.Files.fileActions.registerAction({
            name: 'Open',
            mime: mime,
            actionHandler: _.bind(this._onEditorTrigger, this),
            permissions: OC.PERMISSION_UPDATE,
            icon: function () {
                return OC.imagePath('core', 'actions/edit');
            }
        });
        OCA.Files.fileActions.setDefault(mime, 'Open');
    }
};

$(document).ready(function () {
    OCA.CherryCloudRouter.registerFileActions();
});
