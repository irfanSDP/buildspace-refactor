define('buildspace/apps/TenderingReport/BillManager/buildUpFloorAreaDialog',[
    'dojo/_base/declare',
    'dojo/_base/lang',
    "dojo/_base/array",
    "dojo/dom-attr",
    "dojox/grid/enhanced/plugins/Menu",
    "dojox/grid/enhanced/plugins/Selector",
    "dojox/grid/enhanced/plugins/Rearrange",
    "buildspace/widget/grid/plugins/FormulatedColumn",
    'dojo/_base/event',
    'dojo/keys',
    'dojo/currency',
    "dijit/focus",
    'dojo/request',
    'dijit/PopupMenuItem',
    'buildspace/widget/grid/cells/Select',
    'buildspace/widget/grid/cells/Textarea',
    'buildspace/widget/grid/cells/FormulaTextBox',
    "dojo/when",
    "dojo/html",
    "dojo/dom",
    "dojo/dom-style",
    "buildspace/widget/grid/cells/Formatter",
    "./buildUpFloorAreaSummary",
    "./PrintPreviewFormDialog",
    'dojo/i18n!buildspace/nls/BuildUpFloorArea'
], function(declare, lang, array, domAttr, Menu, Selector, Rearrange, FormulatedColumn, evt, keys, currency, focusUtil, request, PopupMenuItem, Select, Textarea, FormulaTextBox, when, html, dom, domStyle, GridFormatter, BuildUpFloorAreaSummary, PrintPreviewFormDialog, nls){

    var BuildUpFloorAreaGrid = declare('buildspace.apps.TenderingReport.BillManager.BuildUpFloorArea.grid', dojox.grid.EnhancedGrid, {
        billColumnSettingId: null,
        style: "border-top:none;",
        selectedItem: null,
        keepSelection: true,
        rowSelector: '0px',
        addUrl: null,
        updateUrl: null,
        deleteUrl: null,
        pasteUrl: null,
        buildUpSummaryWidget: null,
        type: null,
        constructor:function(args){
            this.rearranger = Rearrange(this, {});
            this.formulatedColumn = FormulatedColumn(this,{});
        },
        canSort: function(inSortInfo){
            return false;
        },
        dodblclick: function(e){
            //this.onRowDblClick(e);
        },
        updateTotalBuildUp: function(){
            this.buildUpSummaryWidget.refreshTotalFloorArea();
        }
    });

    var BuildUpFloorAreaGridContainer = declare('buildspace.apps.TenderingReport.BillManager.BuildUpFloorAreaGrid', dijit.layout.BorderContainer, {
        style: "padding:0px;width:100%;height:100%;border:0px;",
        gutters: false,
        billColumnSettingId: null,
        type: null,
        gridOpts: {},
        postCreate: function(){
            var self = this;
            self.inherited(arguments);

            lang.mixin(self.gridOpts, { billColumnSettingId: self.billColumnSettingId, region:"center", type: self.type });
            var grid = this.grid = new BuildUpFloorAreaGrid(self.gridOpts);

            self.addChild(new dijit.layout.ContentPane({style: 'width:100%', content:grid, region:'center'}));
        }
    });

    var Dialog = declare('buildspace.apps.TenderingReport.BillManager.BuildUpFloorAreaDialog', dijit.Dialog, {
        style:"padding:0px;margin:0px;",
        title: nls.appName,
        billId: 0,
        billColumnSettingId: 0,
        columnSettingForm: null,
        unit: null,
        buildRendering: function(){
            var content = this.createContent();
            content.startup();
            this.content = content;

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
            key = e.keyCode;
            if (key == keys.ESCAPE) {
                dojo.stopEvent(e);
            }
        },
        onHide: function() {
            this.columnSettingForm.refreshColumn();
            this.destroyRecursive();
        },
        createContent: function(){
            var self = this;
            var borderContainer = new dijit.layout.BorderContainer({
                style:"padding:0px;width:900px;height:450px;",
                gutters: false
            });

            var toolbar = new dijit.Toolbar({
                region: "top",
                style: "outline:none!important;padding:2px;overflow:hidden;"
            });

            toolbar.addChild(
                new dijit.form.Button({
                    label: nls.print,
                    iconClass: "icon-16-container icon-16-print",
                    style:"outline:none!important;",
                    onClick: function(e) {
                        self.openPrintingDialog();
                    }
                })
            );

            toolbar.addChild(new dijit.ToolbarSeparator());

            toolbar.addChild(
                new dijit.form.Button({
                    label: nls.close,
                    iconClass: "icon-16-container icon-16-close",
                    style:"outline:none!important;",
                    onClick: dojo.hitch(this, 'hide')
                })
            );

            var formatter = new GridFormatter(),
                sign = {
                    options: [
                        buildspace.constants.SIGN_POSITIVE_TEXT,
                        buildspace.constants.SIGN_NEGATIVE_TEXT
                    ],
                    values: [
                        buildspace.constants.SIGN_POSITIVE,
                        buildspace.constants.SIGN_NEGATIVE
                    ]
                };

            var structure = [{
                name: 'No',
                field: 'id',
                styles: "text-align:center;",
                width: '40px',
                formatter: formatter.rowCountCellFormatter
            }, {
                name: nls.description,
                field: 'description',
                width: 'auto'
            },{
                name: nls.factor,
                field: 'factor-value',
                width:'100px',
                styles:'text-align:right;',
                formatter: formatter.formulaNumberCellFormatter
            },{
                name: nls.length,
                field: 'length-value',
                width:'100px',
                styles:'text-align:right;',
                formatter: formatter.formulaNumberCellFormatter
            },{
                name: nls.width,
                field: 'width-value',
                width:'100px',
                styles:'text-align:right;',
                formatter: formatter.formulaNumberCellFormatter
            },{
                name: nls.total,
                field: 'total',
                width:'100px',
                styles:'text-align:right;',
                formatter: formatter.numberCellFormatter
            },{
                name: nls.sign,
                field: 'sign',
                width: '70px',
                styles: 'text-align:center;',
                formatter: formatter.signCellFormatter
            }];

            var buildUpFloorAreaStore = dojo.data.ItemFileWriteStore({
                url:"billBuildUpFloorArea/getBuildUpFloorAreaItemList/bill_column_setting_id/"+self.billColumnSettingId,
                clearOnClose: true
            });

            var buildUpSummaryWidget = BuildUpFloorAreaSummary({
                billId: self.billId,
                billColumnSettingId: self.billColumnSettingId,
                container: borderContainer,
                columnSettingForm: self.columnSettingForm,
                buildUpGridStore: buildUpFloorAreaStore
            });

            var content = BuildUpFloorAreaGridContainer({
                region: 'center',
                billColumnSettingId: self.billColumnSettingId,
                gridOpts: {
                    store: buildUpFloorAreaStore,
                    structure: structure,
                    buildUpSummaryWidget: buildUpSummaryWidget
                }
            });

            var gridContainer = this.makeGridContainer(content,nls.appName);
            borderContainer.addChild(toolbar);
            borderContainer.addChild(buildUpSummaryWidget);
            borderContainer.addChild(gridContainer);

            return borderContainer;
        },
        makeGridContainer: function(content, title){
            var id = this.billId;
            var stackContainer = dijit.byId('billManager-buildUpFloorArea_'+id+'-stackContainer');
            if(stackContainer){
                dijit.byId('billManager-buildUpFloorArea_'+id+'-stackContainer').destroyRecursive();
            }

            stackContainer = new dijit.layout.StackContainer({
                style:'width:100%;height:100%;border:0px;',
                region: "center",
                id: 'billManager-buildUpFloorArea_'+id+'-stackContainer'
            });

            var stackPane = new dijit.layout.ContentPane({
                title: title,
                content: content
            });

            stackContainer.addChild(stackPane);

            var controller = new dijit.layout.StackController({
                region: "top",
                containerId: 'billManager-buildUpFloorArea_'+id+'-stackContainer'
            });

            var controllerPane = new dijit.layout.ContentPane({
                style: "padding:0px;overflow:hidden;",
                class: 'breadCrumbTrail',
                region: 'top',
                content: controller
            });

            var borderContainer = new dijit.layout.BorderContainer({
                style:"padding:0px;width:100%;height:100%;border:0px;",
                gutters: false,
                region: 'center'
            });

            borderContainer.addChild(stackContainer);
            borderContainer.addChild(controllerPane);

            dojo.subscribe('billManager-buildUpFloorArea_'+id+'-stackContainer-selectChild',"",function(page){
                var widget = dijit.byId('billManager-buildUpFloorArea_'+id+'-stackContainer');
                if(widget){
                    var children = widget.getChildren();
                    var index = dojo.indexOf(children, dijit.byId(page.id));
                    index = index+1;
                    while( children.length > index ){
                        widget.removeChild(children[ index ]);
                        children[ index ].destroyRecursive(true);
                        index = index + 1;
                    }
                }
            });

            return borderContainer;
        },
        openPrintingDialog: function() {
            var self = this;

            var pb = buildspace.dialog.indeterminateProgressBar({ title: nls.pleaseWait + "..." });

            pb.show();

            return request.get("viewTendererReporting/getPrintingInformation", {
                handleAs: 'json'
            }).then(function(response) {
                var dialog = new PrintPreviewFormDialog({
                    title: nls.printFloorArea + ' (' + self.unit + ')',
                    printURL: 'billBuildUpFloorAreaPrinting/printFloorAreaReport',
                    exportURL: 'billBuildUpFloorAreaPrinting/exportFloorAreaReport',
                    billColumnSettingId: self.billColumnSettingId,
                    _csrf_token: response._csrf_token
                });

                pb.hide();
                return dialog.show();
            }, function(error) {
                return pb.hide();
            });
        }
    });

    return Dialog;
});