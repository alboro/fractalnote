/**
 * NextCloud - fractalnote
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
            this._requestProcessing = false;
        };

        NodeRepository.prototype = {

            createNode: function (parentId, title, position, modifiedTime) {
                return this.makeRequest({
                    url: this._baseUrl + '/notes?' + this._filePath,
                    method: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify({
                        mtime   : modifiedTime,
                        parentId: this.getParentId(parentId),
                        title   : title,
                        position: position
                    })
                });
            },

            moveNode: function (nodeId, newParentId, position, modifiedTime) {
                return this.updateNode({
                        id         : nodeId,
                        newParentId: this.getParentId(newParentId),
                        position   : position
                }, modifiedTime);
            },

            updateNode: function (nodeData, modifiedTime) {
                return this.makeRequest({
                    url: this._baseUrl + '/notes/' + nodeData.id  + '?' + this._filePath,
                    method: 'PUT',
                    contentType: 'application/json',
                    data: JSON.stringify({
                        mtime   : modifiedTime,
                        nodeData: nodeData
                    })
                });
            },

            deleteNode: function (nodeModel, modifiedTime) {
                return this.makeRequest({
                    url: this._baseUrl + '/notes/' + nodeModel.id  + '?' + this._filePath,
                    method: 'DELETE',
                    contentType: 'application/json',
                    data: JSON.stringify({
                        mtime : modifiedTime,
                        nodeId: nodeModel.id
                    })
                });
            },

            getParentId: function (parentId) {
                return parentId === '#' ? 0 : parentId;
            },

            makeRequest: function (request) {
                var self = this;
                if (this._requestProcessing) {
                    alert('Cannot make new request. Previous request is still being processed!');
                    return $.Deferred(); // todo: trigger .fail callback, pass error instance with message
                }
                this._requestProcessing = true;
                return $.ajax(request).always(function () { self._requestProcessing = false; });
            }
        };

        /**
         * @param {NodeRepository} object
         */
        var View = function (noteRepo) {
            this.nodeRepo           = noteRepo;
            this.activeNode         = null;
            this.allNodes           = null;
            this.firstNode          = null;

            this.contentTplElement  = $('#note-content-tpl');
            this.editorElement      = $('#note-editor');

            this.selectorText       = '#note-editor textarea';
            this.selectorSaveButton = '#note-editor [name=save]';
            this.selectorTitle      = '#note-editor #note-node-title';

            this.searchElement      = $('#note-searchbar');
            this.navigationElement  = $('#note-navigation');

            var treeData            = $('#js-tree-data');

            this.themeName          = treeData.data('themeName');
            this.mtime              = treeData.data('mtime');
            this.treeDataSource     = treeData;
        };

        View.prototype = {

            parseNodes: function () {
                this.allNodes = JSON.parse(this.treeDataSource.text());
                this.firstNode = this.allNodes[0].id;
            },

            setTime: function (time) {
                this.mtime = time;

                return this;
            },

            getTime: function () {
                return this.mtime;
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
            renderEditor: function (nodeToActivate) {
                this.setActiveNode(nodeToActivate);
                var areaTemplate = Handlebars.compile(this.contentTplElement.html());
                this.editorElement.html(
                    areaTemplate({note: this.getActiveNode()})
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
                        if (!response) {
                            return;
                        }
                        self.setTime(response[0]);
                        var node = self.getTreeInstance().get_node(requestNode.id);
                        node.data.content = requestNode.content;
                    })
                    .fail(function (e) {
                        $(button).removeClass('loading');
                        alert(e.responseJSON && e.responseJSON.message ? e.responseJSON.message : 'Action failed');
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
            menuRenameNode: function (data) {
                var title, oldTitle,
                    inst = $.jstree.reference(data.reference),
                    obj = inst.get_node(data.reference);
                title = prompt('Provide new node title:', obj.text);
                if (title) {
                    oldTitle = obj.text;
                    inst.set_text(obj, title);
                    this.afterNodeRename(obj, title, oldTitle); // inst.edit(obj);
                }
                return true;
            },

            /**
             * @access {public}
             */
            afterNodeMove: function (node, newParentId, newPosition) {
                var self = this;
                self.nodeRepo.moveNode(node.id, newParentId, newPosition, self.getTime())
                    .done(function (response) {
                        self.setTime(response[0]);
                    })
                    .fail(function (e) {
                        alert(e.responseJSON && e.responseJSON.message ? e.responseJSON.message : 'Action failed');
                    });
            },

            /**
             * @access {public}
             */
            afterNodeRename: function (node, old) {
                var requestModel, self = this;
                requestModel = {
                    id: node.id,
                    title: node.text,
                    content: null
                };
                self.nodeRepo.updateNode(requestModel, self.getTime())
                    .done(function (response) {
                        self.setTime(response[0]);
                        if (self.getActiveNode().id === node.id) {
                            $(self.selectorTitle).html(node.text);
                        }
                    })
                    .fail(function (e) {
                        var node, jstree = self.getTreeInstance();
                        node = jstree.get_node(requestModel.id);
                        jstree.set_text(node, old);
                        alert(e.responseJSON && e.responseJSON.message ? e.responseJSON.message : 'Action failed');
                    });
            },

            /**
             * @access {public}
             * @returns {undefined}
             */
            menuCreateNode: function (isSubNodeCreation, data) {
                var title, newNode,
                    inst = $.jstree.reference(data.reference),
                    obj = inst.get_node(data.reference);
                title = prompt('Provide new node title:', 'New node');
                if (!title) {
                    return;
                }
                newNode = {
                    id: null,
                    type: 'txt',
                    text: title,
                    data: {
                        content: '',
                        isEditable: true,
                        isReadonly: false,
                        isRich: false
                    },
                    children: []
                };
                inst.create_node(isSubNodeCreation ? obj : obj.parent, newNode, 'last');
            },

            /**
             * @access {public}
             */
            afterNodeCreate: function (parent, node, position) {
                var self = this;
                self.nodeRepo.createNode(parent, node.text, position, self.getTime())
                    .done(function (response) {
                        var inst = self.getTreeInstance();
                        self.setTime(response[0]);
                        inst.set_id(node, response[1]);
                        inst.select_node(node);
                    })
                    .fail(function (e) {
                        self.getTreeInstance().delete_node(node);
                        alert(e.responseJSON && e.responseJSON.message ? e.responseJSON.message : 'Action failed');
                    });
            },

            /**
             * @access {public}
             * @returns {undefined}
             */
            menuDeleteNode: function (data) {
                var ok, self = this, inst = $.jstree.reference(data.reference),
                    node = inst.get_node(data.reference);
                ok = confirm('Are you sure to delete?');
                if (!ok) {
                    return;
                }
                this.nodeRepo.deleteNode(node, self.getTime())
                    .done(function (response) {
                        var prevNode;
                        self.setTime(response[0]);
                        inst.delete_node(node);
                        if (self.nodeRepo.getParentId(node.parent)) {
                            prevNode = inst.get_node(node.parent);
                            inst.select_node(prevNode);
                        } else {
                            self.renderEditor(null);
                        }
                    })
                    .fail(function (e, type, statusText) {
                        alert(e.responseJSON && e.responseJSON.message ? e.responseJSON.message : 'Action failed');
                    })
            },

            /**
             * @access {public}
             * @returns {undefined}
             */
            render: function () {
                var self = this;
                this.parseNodes();
                this.getNavigation().jstree({
                    "core": {
                        "themes": {
                            "name": this.themeName,
                            "responsive": true
                        },
                        "multiple" : false,
                        "animation": 0,
                        "check_callback": true,
                        "data": this.allNodes
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
                            "create" : {
                                "separator_before"	: false,
                                "separator_after"	: false,
                                "_disabled"			: false,
                                "label"				: "Add Node",
                                "action"			: this.menuCreateNode.bind(this, false)
                            },
                            "sub-create" : {
                                "separator_before"	: false,
                                "separator_after"	: false,
                                "_disabled"			: false,
                                "label"				: "Add SubNode",
                                "action"			: this.menuCreateNode.bind(this, true)
                            },
                            "rename" : {
                                "separator_before"	: false,
                                "separator_after"	: false,
                                "_disabled"			: false,
                                "label"				: "Rename Node",
                                "shortcut"			: 113,
                                "shortcut_label"	: 'F2',
                                "icon"				: "glyphicon glyphicon-leaf",
                                "action"			: this.menuRenameNode.bind(this)
                            },
                            "remove" : {
                                "separator_before"	: true,
                                "icon"				: false,
                                "separator_after"	: false,
                                "_disabled"			: false,
                                "label"				: "Delete Node",
                                "action"			: this.menuDeleteNode.bind(this)
                            }
                        }
                    }
                })
                .on('create_node.jstree', function (e, data) {
                    self.afterNodeCreate(data.parent, data.node, data.position + 1);
                })
                .on('rename_node.jstree', function (e, data) {
                     self.afterNodeRename(data.node, data.old);
                })
                .on('state_ready.jstree', function (e, data) {
                    if (data.instance.get_state().core.selected.length == 0) {
                        data.instance.select_node(self.firstNode);
                    }
                })
                .on('set_state.jstree', function (e, data) {
                    var selected = data.instance.get_selected();
                    if (selected.length) {
                        var node = data.instance.get_node(selected[0]);
                        self.renderEditor({
                            id:      node.id,
                            title:   node.text,
                            content: node.data.content,
                            isEditable: node.data.isEditable,
                            isReadonly: node.data.isReadonly,
                            isRich: node.data.isRich
                        });
                    }
                })
                .on('select_node.jstree', function (e, data) {
                    self.checkChanged();
                    self.renderEditor({
                        id:      data.node.id,
                        title:   data.node.text,
                        content: data.node.data.content,
                        isEditable: data.node.data.isEditable,
                        isReadonly: data.node.data.isReadonly,
                        isRich: data.node.data.isRich
                    });
                })
                .on('move_node.jstree', function (e, data) {
                    self.afterNodeMove(data.node, data.parent, data.position + 1);
                });
                this.allNodes = null;
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
            OC.generateUrl('/apps/fractalnote'),
            window.document.location.search.substring(1)
        );
        new View(noteRepo).render();
    });

})(OC, window, jQuery, Handlebars);