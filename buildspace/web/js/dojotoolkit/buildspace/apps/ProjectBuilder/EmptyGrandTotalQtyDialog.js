define('buildspace/apps/ProjectBuilder/EmptyGrandTotalQtyDialog',[
    'dojo/_base/declare',
    "dojo/html",
    "dojo/dom",
    'dojo/keys',
    "dojo/dom-style",
    "dijit/layout/ContentPane",
    "dojox/grid/DataGrid",
    "dojo/store/Memory",
    "dojo/data/ObjectStore",
    "buildspace/widget/grid/cells/Formatter",
    'dojo/i18n!buildspace/nls/ProjectBuilder'
], function(declare, html, dom, keys, domStyle, ContentPane, DataGrid, Memory, ObjectStore, GridFormatter, nls){

    return declare('buildspace.apps.ProjectBuilder.EmptyGrandTotalQtyDialog', dijit.Dialog, {
        style:"padding:0px;margin:0px;",
        title: nls.errorEmptyGrandTotalQty,
        project: null,
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
            var borderContainer = new dijit.layout.BorderContainer({
                style:"padding:0;width:780px;height:480px;",
                gutters: false
            });

            var toolbar = new dijit.Toolbar({
                region: "top",
                style: "outline:none!important;padding:2px;overflow:hidden;border-bottom:none;"
            });

            toolbar.addChild(
                new dijit.form.Button({
                    label: nls.close,
                    iconClass: "icon-16-container icon-16-close",
                    style:"outline:none!important;",
                    onClick: dojo.hitch(this, 'hide')
                })
            );

            var dataStore = new ObjectStore({ objectStore:new Memory({ data: this.data.items }) });

            var formatter = new GridFormatter();

            var grid = new DataGrid({
                style: "border-top:none;",
                region: "center",
                store: dataStore,
                items: this.data.items,
                structure: [
                    { name: "No.", field: "id", width:'30px', styles:'text-align:center;', formatter: formatter.rowCountCellFormatter },
                    { name: nls.description, field: "description", width:'auto', formatter: formatter.treeCellFormatter },
                    {name: nls.unit, field: 'uom_id', width:'70px', styles:'text-align:center;', formatter: formatter.unitIdCellFormatter}
                ]
            });

            var msgContainer = new ContentPane({
                content: '<div style="background-color:#cc1313;color:white;overflow:hidden;">'+nls.errorEmptyGrandTotalQtyDesc+'</div>',
                region: "top",
                style:"padding:5px;text-align:left;height:28px;background-color:#cc1313;"
            });

            var contentBorderContainer = new dijit.layout.BorderContainer({
                region: "center",
                gutters: false
            });

            contentBorderContainer.addChild(grid);
            contentBorderContainer.addChild(msgContainer);

            borderContainer.addChild(toolbar);
            borderContainer.addChild(contentBorderContainer);

            return borderContainer;
        }
    });
});