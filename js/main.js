/**
 * NextCloud / ownCloud - cherrycloud
 *
 * Licensed under the Apache License, Version 2.0
 *
 * @author Alexander Demchenko <https://github.com/alboro>
 * @copyright Alexander Demchenko 2017
 */
(function (OC, window, $, Handlebars, undefined) {
    'use strict';

    $(function () {

        // this notes object holds all our notes
        var NodeRepository = function (baseUrl, filePath) {
            this._baseUrl  = baseUrl;
            this._filePath = filePath;
        };

        NodeRepository.prototype = {

            /*create: function (noteModel) {
                var deferred = $.Deferred();
                var self = this;
                $.ajax({
                    url: this._baseUrl + '?f=' + this._filePath,
                    method: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify(noteModel)
                }).done(function (response) {
                    self.setActiveNode(response);
                    deferred.resolve();
                }).fail(function () {
                    deferred.reject();
                });
                return deferred.promise();
            },*/

            updateNode: function (nodeModel, modifiedTime) {
                return $.ajax({
                    url: this._baseUrl + '/' + nodeModel.id  + '?f=' + this._filePath,
                    method: 'PUT',
                    contentType: 'application/json',
                    data: JSON.stringify({
                        id      : nodeModel.id,
                        title   : nodeModel.title,
                        content : nodeModel.content,
                        mtime   : modifiedTime
                    })
                });
            }

        };

        /**
         * @param {NodeRepository} object
         */
        var View = function (noteRepo) {
            this.nodeRepo           = noteRepo;
            this.activeNode         = undefined;

            this.contentTplElement  = $('#note-content-tpl');
            this.editorElement      = $('#note-editor');

            this.selectorText       = '#note-editor textarea';
            this.selectorSaveButton = '#note-editor [name=save]';
            this.selectorTitle      = '#note-editor #note-node-title';

            this.searchElement      = $('#note-searchbar');
            this.navigationElement  = $('#note-navigation');

            var treeData = $('#js-tree-data');

            this.themeName          = treeData.data('themeName');
            this.mtime              = treeData.data('mtime');
            this.treeDataSource     = treeData;
        };

        View.prototype = {

            /**
             * @returns {Array}
             */
            getNodes: function () {
                return JSON.parse(this.treeDataSource.text());
            },

            setTime: function (time) {
                this.mtime = time;

                return this;
            },

            getTime: function () {
                return this.mtime;
            },

            /**
             * @returns object
             */
            getActiveOrEmptyNodeModel: function () {
                var model = this.getActiveNode();
                return model ? model : {
                    id:      '',
                    title:   null,
                    content: null,
                    isEditable: null,
                    isReadonly: null,
                    isRich: null
                }
            },

            getActiveNode: function () {
                return this.activeNode;
            },

            setActiveNode: function (nodeModel) {
                this.activeNode = nodeModel;
            },

            /**
             * @access {public}
             * @returns {jQuery element}
             */
            getNavigation: function () {
                return this.navigationElement;
            },
            /**
             * @access {public}
             * @returns {undefined}
             */
            renderContent: function () {
                var areaTemplate = Handlebars.compile(this.contentTplElement.html());
                this.editorElement.html(
                    areaTemplate({note: this.getActiveOrEmptyNodeModel()})
                );
                // handle saves
                $(this.selectorSaveButton).click(this.saveClick.bind(this));
            },

            /**
             * @access {public}
             */
            saveClick: function () {
                var self = this, button = $(this.selectorSaveButton), requestNode = this.getActiveNode();
                requestNode.content = $(this.selectorText).val();
                $(button).addClass('loading');
                this.nodeRepo.updateNode(requestNode, this.getTime())
                    .done(function (response) {
                        $(button).removeClass('loading');
                        var jsTreeNode = self.getTreeInstance().get_node(requestNode.id);
                        jsTreeNode.data.content = requestNode.content;
                        self.setTime(response[0]);
                    })
                    .fail(function (e) {
                        alert(e.responseJSON.message);
                    });
            },

            /**
             * @access {public}
             */
            getTreeInstance: function () {
                return this.getNavigation().jstree(true);
            },

            checkChanged: function () {
                var node = this.getActiveNode();
                if (node) {
                    var currentValue = $(this.selectorText).val();
                    if (node.content != currentValue) {
                        $(this.selectorSaveButton).click();
                    }
                }
            },
            /**
             * @access {public}
             * @returns {undefined}
             */
            render: function () {
                var self = this, nodes = this.getNodes();
                this.renderContent();
                this.getNavigation()
                    .jstree({
                        "core": {
                            "themes": {
                                "name": this.themeName,
                                // "variant" : "large",
                                "responsive": true
                            },
                            "multiple" : false,
                            "animation": 0,
                            // "li_height": "20px",
                            "check_callback": true,
                            "data": nodes
                        },
                        "plugins": ["contextmenu", "dnd", "search", "state", "types", "wholerow"],
                        "search": {
                            "show_only_matches_children": true
                            //"search_callback": true
                        },
                        "state": {
                            "key": this.nodeRepo._filePath
                        },
                        "types" : {
                            "txt": {
                                // "icon": "file", -> i -> jstree-icon jstree-themeicon file jstree-themeicon-custom
                                // no icon set -> i -> jstree-icon jstree-themeicon
                                "a_attr": {"class": "app-navigation-noclose"} // nextcloud config
                            },
                            "readonly": {
                                "icon" : "jstree-icon-no-edit",
                                "a_attr": {"class": "app-navigation-noclose"} // nextcloud config
                            },
                            "rich": {
                                "icon" : "jstree-icon-no-edit",
                                "a_attr": {"class": "app-navigation-noclose"} // nextcloud config
                            }
                        },
                        "contextmenu": {
                            "items": {
                                "rename" : {
                                    "separator_before"	: false,
                                    "separator_after"	: false,
                                    "_disabled"			: false, //(this.check("rename_node", data.reference, this.get_parent(data.reference), "")),
                                    "label"				: "Rename",
                                    "shortcut"			: 113,
                                    "shortcut_label"	: 'F2',
                                    "icon"				: "glyphicon glyphicon-leaf",
                                    "action"			: $.jstree.defaults.contextmenu.items().rename.action
                                }/*,
                                "create" : {
                                    "separator_before"	: false,
                                    "separator_after"	: true,
                                    "_disabled"			: false, //(this.check("create_node", data.reference, {}, "last")),
                                    "label"				: "Create",
                                    "action"			: $.jstree.defaults.contextmenu.items().create.action
                                },
                                "remove" : {
                                    "separator_before"	: false,
                                    "icon"				: false,
                                    "separator_after"	: false,
                                    "_disabled"			: false, //(this.check("delete_node", data.reference, this.get_parent(data.reference), "")),
                                    "label"				: "Delete",
                                    "action"			: $.jstree.defaults.contextmenu.items().remove.action
                                },
                                "ccp" : {
                                    "separator_before"	: true,
                                    "icon"				: false,
                                    "separator_after"	: false,
                                    "label"				: "Edit",
                                    "action"			: false,
                                    "submenu" : {
                                        "cut" : {
                                            "separator_before"	: false,
                                            "separator_after"	: false,
                                            "label"				: "Cut",
                                            "action"			: $.jstree.defaults.contextmenu.items().ccp.submenu.cut.action
                                        },
                                        "copy" : {
                                            "separator_before"	: false,
                                            "icon"				: false,
                                            "separator_after"	: false,
                                            "label"				: "Copy",
                                            "action"			: $.jstree.defaults.contextmenu.items().ccp.submenu.copy.action
                                        },
                                        "paste" : {
                                            "separator_before"	: false,
                                            "icon"				: false,
                                            "_disabled"			: $.jstree.defaults.contextmenu.items().ccp.submenu.paste._disabled,
                                            "separator_after"	: false,
                                            "label"				: "Paste",
                                            "action"			: $.jstree.defaults.contextmenu.items().ccp.submenu.paste.action
                                        }
                                    }
                                }*/
                            }
                        }
                    })
                    .on('rename_node.jstree', function (e, data) {
                        var requestModel = {
                            id: data.node.id,
                            title: data.text,
                            content: null
                        };
                        $(self.selectorSaveButton).addClass('loading');
                        self.nodeRepo.updateNode(requestModel, self.getTime())
                            .done(function (response) {
                                self.setTime(response[0]);
                                if (self.getActiveNode().id === data.node.id) {
                                    $(self.selectorTitle).html(data.text);
                                }
                                $(self.selectorSaveButton).removeClass('loading');
                            })
                            .fail(function (e) {
                                var jsTreeNode = data.instance.get_node(requestModel.id);
                                data.instance.set_text(jsTreeNode, data.old);
                                $(self.selectorSaveButton).removeClass('loading');
                                alert(e.responseJSON.message);
                            });
                    })
                    .on('state_ready.jstree', function (e, data) {
                        if (data.instance.get_state().core.selected.length == 0) {
                            data.instance.select_node(nodes[0].id);
                        }
                    })
                    .on('set_state.jstree', function (e, data) {
                        var selected = data.instance.get_selected();
                        if (selected.length) {
                            var node = data.instance.get_node(selected[0]);
                            self.setActiveNode({
                                id:      node.id,
                                title:   node.text,
                                content: node.data.content,
                                isEditable: node.data.isEditable,
                                isReadonly: node.data.isReadonly,
                                isRich: node.data.isRich
                            });
                            self.renderContent();
                        }
                    })
                    .on('select_node.jstree', function (e, data) {
                        self.checkChanged();
                        self.setActiveNode({
                            id:      data.node.id,
                            title:   data.node.text,
                            content: data.node.data.content,
                            isEditable: data.node.data.isEditable,
                            isReadonly: data.node.data.isReadonly,
                            isRich: data.node.data.isRich
                        });
                        self.renderContent();
                    });
                var to = false;
                self.searchElement.keyup(function () {
                    if (to) {
                        clearTimeout(to);
                    }
                    to = setTimeout(function () {
                        self.getTreeInstance().search(self.searchElement.val());
                    }, 250);
                });
            }
        };

        var noteRepo = new NodeRepository(
            OC.generateUrl('/apps/cherrycloud') + '/notes',
            window.document.location.search.substring(3) // @todo
        );
        new View(noteRepo).render();
    });

})(OC, window, jQuery, Handlebars);