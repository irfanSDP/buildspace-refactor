define('buildspace/apps/ProjectBuilder/HeadWithoutItemsDialog',[
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

    var CustomFormatter = declare("buildspace.apps.ProjectBuilder.HeadWithoutItemsDialog.CellFormatter", null, {
        warningCellFromatter: function(cellValue, rowIdx, cell){
            var item = this.grid.getItem(rowIdx);
            if(cellValue === true){
                cellValue = '<span style="color:#cc9813">'+nls.noItems+'</span>';
            }else{
                cellValue = "&nbsp;";
            }

            if (item.type != undefined && item.type < 1){
                cell.customClasses.push('invalidTypeItemCell');
            }else{
                cell.customClasses.push('disable-cell');
            }

            return cellValue;
        }
    });

    return declare('buildspace.apps.ProjectBuilder.HeadWithoutItemsDialog', dijit.Dialog, {
        style:"padding:0px;margin:0px;",
        title: nls.warningHeadWithoutItems,
        project: null,
        data: null,
        yesConn: null,
        noConn: null,
        onYes: null,
        onNo: null,
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
            dojo.disconnect(this.yesConn);
            dojo.disconnect(this.noConn);
            this.destroyRecursive();
        },
        createForm: function(){
            var self = this;

            var borderContainer = new dijit.layout.BorderContainer({
                style:"padding:0;width:780px;height:480px;",
                gutters: false
            });

            var dataStore = new ObjectStore({ objectStore:new Memory({ data: this.data.items }) });

            var formatter = new GridFormatter();
            var customFormatter = new CustomFormatter();

            var grid = new DataGrid({
                style: "border-top:none;",
                region: "center",
                store: dataStore,
                items: this.data.items,
                structure: [
                    { name: "No.", field: "id", width:'30px', styles:'text-align:center;', formatter: formatter.rowCountCellFormatter },
                    { name: nls.description, field: "description", width:'auto', formatter: formatter.treeCellFormatter },
                    { name: nls.warning, field: "warning", width:'80px', styles:'text-align:center;', formatter: customFormatter.warningCellFromatter }
                ]
            });

            var msgContainer = new ContentPane({
                content: '<div style="background-color:#cc9813;color:white;overflow:hidden;">'+nls.warningHeadWithoutItemsDesc+'</div>',
                region: "top",
                style:"padding:5px;text-align:left;height:28px;background-color:#cc9813;"
            });

            var contentBorderContainer = new dijit.layout.BorderContainer({
                region: "center",
                gutters: false
            });

            var buttonsPane = new dijit.Toolbar({
                region: "bottom",
                baseClass:'confirm-dialog',
                style: "background-color:white;text-align:right;outline:none !important;border:0;height:24px;overflow:hidden;margin:5px;"
            });
            var yesBtn = new dijit.form.Button({ label:nls.yes });
            var noBtn = new dijit.form.Button({ label:nls.no });

            buttonsPane.addChild(yesBtn);
            buttonsPane.addChild(new dijit.ToolbarSeparator());
            buttonsPane.addChild(noBtn);

            contentBorderContainer.addChild(grid);
            contentBorderContainer.addChild(msgContainer);

            borderContainer.addChild(buttonsPane);
            borderContainer.addChild(contentBorderContainer);

            self.yesConn = dojo.connect(yesBtn, "onClick", function(){
                if(typeof self.onYes === 'function'){
                    self.onYes();
                }
                self.hide();
            });

            self.noConn = dojo.connect(noBtn, "onClick", function(){
                if(typeof self.onNo === 'function'){
                    self.onNo();
                }
                self.hide();
            });

            return borderContainer;
        }
    });
});