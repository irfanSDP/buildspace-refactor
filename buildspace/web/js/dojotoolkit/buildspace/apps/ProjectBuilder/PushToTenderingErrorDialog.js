define('buildspace/apps/ProjectBuilder/PushToTenderingErrorDialog',[
    'dojo/_base/declare',
    'dojo/_base/lang',
    "dojo/_base/connect",
    "dojo/when",
    "dojo/html",
    "dojo/dom",
    'dojo/keys',
    "dojo/dom-style",
    "dijit/_WidgetBase",
    "dijit/_OnDijitClickMixin",
    "dijit/_TemplatedMixin",
    "dijit/_WidgetsInTemplateMixin",
    "dijit/layout/ContentPane",
    "buildspace/widget/grid/cells/Formatter",
    "dijit/form/DropDownButton",
    "dijit/Menu",
    "dijit/DropDownMenu",
    "dijit/MenuItem",
    "dijit/PopupMenuItem",
    'buildspace/apps/AssignUser/assignGroupProjectGrid',
    'buildspace/apps/Tendering/newPostContractFormDialog',
    'dojo/i18n!buildspace/nls/ProjectBuilder'
], function(declare, lang, connect, when, html, dom, keys, domStyle, _WidgetBase, _OnDijitClickMixin, _TemplatedMixin, _WidgetsInTemplateMixin, ContentPane, GridFormatter, DropDownButton, Menu, DropDownMenu, MenuItem, PopupMenuItem, AssignGroupProjectGrid, NewPostContractFormDialog, nls){

    var SegmentedItemsGrid = declare('buildspace.apps.ProjectBuilder.SegmentedItemsGrid', dojox.grid.EnhancedGrid, {
        type: null,
        selectedItem: null,
        dialogWidget: null,
        escapeHTMLInData: false,
        gridData: null,
        style: "border-top:none;",
        constructor: function(args){
            this.inherited(arguments);
        },
        canSort: function(inSortInfo){
            return false;
        },
        postCreate: function(){
            this.inherited(arguments);
        },
        destroy: function(){
            this.inherited(arguments);
        }
    });

    var SegmentedItemsGridContainer = declare('buildspace.apps.ProjectBuilder.SegmentedItemsGridContainer', dijit.layout.BorderContainer, {
        style: "padding:0px;width:100%;height:100%;",
        gutters: false,
        region: "center",
        gridOpts: {},
        postCreate: function(){
            this.inherited(arguments);
            lang.mixin(this.gridOpts, { region:"center" });
            var grid = this.grid = new SegmentedItemsGrid(this.gridOpts);

            this.addChild(grid);
        }
    });

    return declare('buildspace.apps.ProjectBuilder.PushToTenderingErrorDialog', dijit.Dialog, {
        style:"padding:0px;margin:0px; height: 600px; width: 800px;",
        title: nls.warning,
        rootProject: null,
        builderObj: null,
        tempPublishToPostContractOptions: {},
        data: null,
        buildRendering: function(){
            var form = this.createForm();
            form.startup();
            this.content = form;
            this.inherited(arguments);
        },
        postCreate: function(){
            domStyle.set(this.containerNode, {
                padding:"0px",
                margin:"0px"
            });
            this.closeButtonNode.style.display = "none";
            this.inherited(arguments);
        },
        _onKey: function(e){
            var key = e.keyCode;
            if (key == keys.ESCAPE) {
                dojo.stopEvent(e);
            }
        },
        onHide: function() {
            this.destroyRecursive();
        },
        createForm: function(){
            var self = this;

            var borderContainer = new dijit.layout.BorderContainer({
                style:"padding:0px;height: 600px; width: 800px;",
                gutters: false
            });

            var toolbar = new dijit.Toolbar({
                region: "top",
                style: "outline:none!important;padding:2px;overflow:hidden;"
            });

            toolbar.addChild(
                new dijit.form.Button({
                    label: nls.close,
                    iconClass: "icon-16-container icon-16-close",
                    style:"outline:none!important;",
                    onClick: dojo.hitch(this, 'hide')
                })
            );

            var msgBorderContainer = new dijit.layout.BorderContainer({
                style:"padding:0px;width:400px;height:100px;",
                gutters: false,
                region: 'top'
            });

            var msgContainer = new ContentPane({
                content: nls.segmentedItemsError,
                region: "center",
                style:"padding:20px 30px 20px 30px; text-align:center"
            });

            var formatter = new GridFormatter(),
                store = dojo.data.ItemFileWriteStore({
                    data: {
                        identifier: 'id',
                        items: self.data.segmentedItems
                    }
                });

            var content = SegmentedItemsGridContainer({
                rootProject: self.rootProject,
                gridOpts: {
                    store: store,
                    dialogWidget: self,
                    structure: [
                        {name: 'No.', field: 'count', width:'30px', styles:'text-align:center;', formatter: formatter.rowCountCellFormatter },
                        {name: nls.description, field: 'description', width:'auto', formatter: formatter.treeCellFormatter }
                    ]
                }
            });

            msgBorderContainer.addChild(toolbar);
            msgBorderContainer.addChild(msgContainer);

            borderContainer.addChild(msgBorderContainer);
            borderContainer.addChild(content);

            return borderContainer;
        }
    });
});