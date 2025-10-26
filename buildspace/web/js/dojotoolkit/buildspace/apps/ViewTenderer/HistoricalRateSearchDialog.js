define('buildspace/apps/ViewTenderer/HistoricalRateSearchDialog',[
    'dojo/_base/declare',
    'dojo/_base/lang',
    "dojo/_base/connect",
    "dojo/when",
    "dojo/html",
    "dojo/dom",
    'dojo/keys',
    "dijit/TooltipDialog",
    "dijit/popup",
    "dojo/dom-style",
    'dijit/Toolbar',
    'dijit/ToolbarSeparator',
    'dijit/form/TextBox',
    'dijit/form/Button',
    "dijit/TitlePane",
    'dijit/layout/ContentPane',
    "dojo/store/Memory",
    "dojo/data/ObjectStore",
    "dojox/grid/enhanced/plugins/IndirectSelection",
    "buildspace/widget/grid/cells/Formatter",
    'dojo/i18n!buildspace/nls/Tendering'
], function(declare, lang, connect, when, html, dom, keys, TooltipDialog, popup, domStyle, Toolbar, ToolbarSeparator, TextBox, Button, TitlePane, ContentPane, Memory, ObjectStore, IndirectSelection, GridFormatter, nls){

    var CustomTooltipDialog = declare('buildspace.apps.ViewTenderer.HistoricalRateSearchDialog.TooltipDialog', TooltipDialog, {
        billItem: null,
        columnSettings: null,
        hierarchyInfo: null,
        buildRendering: function(){
            this.inherited(arguments);
            this.startup();     
        },
        postCreate: function(){
            this.inherited(arguments);
            
            domStyle.set(this.containerNode, {
                padding:"0",
                margin:"0"
            });
            
            var content = this.createContent();
                        
            this.set('content', content);
        },
        createContent: function(){
            var borderContainer = new dijit.layout.BorderContainer({
                design: "headline",
                style:"padding:0;margin:0;width:580px;height:280px;border:0;",
                gutters: false
            });

            var formatter = new GridFormatter();
            
            var structure1 = [];
            var gridData1 = [];
            var columnSettingData = {id: 1};
            dojo.forEach(this.columnSettings,function(column){                
                structure1.push({
                    name: column.name, field: 'qty_'+column.id, width: 'auto', styles:'text-align:center;'
                });
                
                columnSettingData['qty_'+column.id] = "<b>"+nls.totalUnit+":</b>&nbsp;"+column.qty;
            });
            
            gridData1.push(columnSettingData);
                        
            var centerPane = new dojox.layout.ContentPane({
                region: "center",
                style: "background-color:green;padding:0;margin:0;height:20%;border:0;",
                content: new dojox.grid.DataGrid({
                    escapeHTMLInData: false,
                    canSort: function(col) { return false; },
                    store: new ObjectStore({ objectStore:new Memory({ data: gridData1 }) }),
                    structure: structure1,
                    rowSelector: '0px',
                    style: "border:none;width:100%;height:100%;"
                }),
                executeScripts: true                    
            });
            
            var bottomPane = new dojox.layout.ContentPane({
                region: "bottom",
                style: "background-color:blue;padding:0;height:80%;margin:0;border:0;",
                content: new dojox.grid.DataGrid({
                    escapeHTMLInData: false,
                    canSort: function(col) { return false; },
                    store: new ObjectStore({ objectStore:new Memory({ data: this.hierarchyInfo }) }),
                    structure: [
                        {name: 'No',field: 'id',styles: "text-align:center;",width: '30px',formatter: formatter.rowCountCellFormatter},
                        {name: nls.description, field: 'description', width:'auto', formatter: formatter.treeCellFormatter},
                        {name: nls.type, field: 'type', styles:'text-align:center;', width:'70px', formatter: formatter.typeCellFormatter }
                    ],
                    rowSelector: '0px',
                    style: "border:none;width:100%;height:100%;"
                }),
                executeScripts: true
            });
            
            borderContainer.addChild(centerPane);
            borderContainer.addChild(bottomPane);
            
            return borderContainer;
        }
    });
    
    var ResultGrid = declare('buildspace.apps.ViewTenderer.HistoricalRateSearchDialog.ResultGrid', dojox.grid.EnhancedGrid, {
        type: null,
        style: "border-top:none;",
        element: null,
        region: 'center',
        constructor: function(args){
            this.connects = [];
            this.escapeHTMLInData = false;
            this.selectionMode = 'single';
            this.plugins = {indirectSelection: {headerSelector:false, width:"20px", styles:"text-align:center;"}}
            this.inherited(arguments);
        },
        postCreate: function(){
            var self = this;
            this.inherited(arguments);

            this.on('CellClick', function(e) {
                var colField = e.cell.field,
                    item     = self.getItem(e.rowIndex),
                    saveBtn = dijit.byId('ViewTendererHistoricalRateSaveBtn');
                
                var disabledSaveBtn;
                
                if(item.id[0] === parseInt(item.id[0], 10) && item.id[0] > 0 && self.selection.selectedIndex > -1){
                    disabledSaveBtn = false;
                }else{
                    disabledSaveBtn = true;
                    self.selection.deselectAll();
                }
                
                 if(saveBtn)
                    saveBtn._setDisabledAttr(disabledSaveBtn);
            });
            
            var myTooltipDialog = null;

            this._connects.push(connect.connect(this, 'onCellMouseOver', function(e){
                var item = self.getItem(e.rowIndex);
                if(e.cell.field == "description" && item.id[0] === parseInt(item.id[0], 10) && item.id[0] > 0){
                    if(myTooltipDialog == null){
                        dojo.xhrPost({
                            url:"viewTenderer/getHistoricalItemInfo",
                            content:{id: item.id},
                            handleAs: "json",
                            load: function(ret){                                
                                myTooltipDialog = new CustomTooltipDialog({
                                    billItem: item,
                                    columnSettings: ret.columns,
                                    hierarchyInfo: ret.items,
                                    onMouseLeave: function(){
                                        popup.close(myTooltipDialog);
                                        myTooltipDialog = null;
                                    }
                                });

                                popup.open({
                                    popup: myTooltipDialog,
                                    around: e.cellNode
                                });               
                            }
                        });
                    }                    
                }
            }));

            this._connects.push(connect.connect(this, 'onCellMouseOut', function(e){
                if(myTooltipDialog !== null){
                    popup.close(myTooltipDialog);
                    myTooltipDialog = null;
                }
            }));
        },
        canSort: function(inSortInfo){
            return false;
        },
        destroy: function(){
            this.inherited(arguments);
            dojo.forEach(this._connects, connect.disconnect);
            delete this._connects;
        }
    });
    
    var FilterToolbar = declare('buildspace.apps.ViewTenderer.HistoricalRateSearchDialog.FilterToolbar', Toolbar, {
        style: 'outline:none!important;padding:2px;overflow:hidden!important;border:none;background-color:#fff!important;',
        gutters: false,
        targetBillItem: null,
        resultGrid: null,
        tendererGrid: null,
        searchDialog: null,
        postCreate: function(){
            var projectTitleTextBox = this.projectTitleTextBox = new TextBox({
                style: 'width:250px;padding:3px;'
            }),
            itemDescTextBox = this.itemDescTextBox = new TextBox({
                style: 'width:250px;padding:3px;',
                value: this.targetBillItem.description[0]
            }),
            projectTitleLabel = new ContentPane({
                style: 'padding:3px;margin:0;',
                baseClass: 'dijitInline',
                content: nls.projectTitle
            }),
            itemDescLabel = new ContentPane({
                style: 'padding:3px;margin:0;',
                baseClass: 'dijitInline',
                content: nls.billItemDesc
            }),
            searchButton = this.nextButton = new Button({
                onClick: dojo.hitch(this, 'searchHistoricalRates'),
                label: nls.search,
                iconClass: 'icon-16-container icon-16-zoom'
            }),
            saveButton = this.nextButton = new Button({
                onClick: dojo.hitch(this, 'saveHistoricalRate'),
                id: "ViewTendererHistoricalRateSaveBtn",
                label: nls.save,
                disabled: true,
                iconClass: 'icon-16-container icon-16-save'
            });
            
            this.addChild(projectTitleLabel);
            this.addChild(projectTitleTextBox);
            this.addChild(new ToolbarSeparator());
            this.addChild(itemDescLabel);
            this.addChild(itemDescTextBox);
            this.addChild(new ToolbarSeparator());
            this.addChild(searchButton);
            this.addChild(new ToolbarSeparator());
            this.addChild(saveButton);
            
            this.searchHistoricalRates();
            
            this.inherited(arguments);
        },
        searchHistoricalRates: function(){
            if(this.resultGrid){
                var grid = this.resultGrid;
                var projectTitleTextBox = this.projectTitleTextBox;
                var itemDescTextBox = this.itemDescTextBox;
                var saveBtn = dijit.byId('ViewTendererHistoricalRateSaveBtn');
                
                grid.selection.deselectAll();
                
                if(saveBtn)
                    saveBtn._setDisabledAttr(true);
                    
                var pb = buildspace.dialog.indeterminateProgressBar({
                    title:nls.pleaseWait+'...'
                });
                
                pb.show().then(function(){
                    dojo.xhrPost({
                        url:"viewTenderer/historicalRateList",
                        content:{pt: projectTitleTextBox.get("value"), bid:itemDescTextBox.get("value")},
                        handleAs: "json",
                        load: function(ret){
                            pb.hide();
                            var store = new dojo.data.ItemFileReadStore({
                                data: ret,
                                clearOnClose: true
                            });

                            grid.setStore(store);
                        }
                    });
                });
            }
        },
        saveHistoricalRate: function(){
            if(this.targetBillItem.id[0] === parseInt(this.targetBillItem.id[0], 10) && this.targetBillItem.id[0] > 0 && this.resultGrid.selection.selectedIndex > -1){
                var item = this.resultGrid.selection.getFirstSelected();
                var dialog = this.searchDialog;
                var targetBillItem = this.targetBillItem;
                var tendererGrid = this.tendererGrid;
                var pb = buildspace.dialog.indeterminateProgressBar({
                    title:nls.pleaseWait+'...'
                });
                
                pb.show().then(function(){
                    dojo.xhrPost({
                        url:"viewTenderer/historicalRateUpdate",
                        content:{tid: targetBillItem.id, rid: item.rate_id, _csrf_token: targetBillItem._csrf_token},
                        handleAs: "json",
                        load: function(ret){

                            if(ret.success){
                                tendererGrid.store.setValue(targetBillItem, 'historical_rate', ret.rate);
                                tendererGrid.store.save();
                            }

                            pb.hide();
                            dialog.hide();
                        }
                    });
                });
            }
        }
    });
    
    return declare('buildspace.apps.ViewTenderer.HistoricalRateSearchDialog', dijit.Dialog, {
        style:"padding:0px;margin:0px;",
        title: nls.searchHistoricalRate,
        targetBillItem: null,
        tendererGrid: null,
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
                style:"padding:0;margin:0;width:900px;height:650px;",
                gutters: false
            });

            var toolbar = new Toolbar({
                region: "top",
                style: "outline:none!important;padding:2px;overflow:hidden;"
            });

            toolbar.addChild(
                new Button({
                    label: nls.close,
                    iconClass: "icon-16-container icon-16-close",
                    style:"outline:none!important;",
                    onClick: dojo.hitch(this, 'hide')
                })
            );
            
            var resultGrid = this.createResultGrid();
            var filterToolbar = new FilterToolbar({
                targetBillItem: this.targetBillItem,
                resultGrid: resultGrid,
                tendererGrid: this.tendererGrid,
                searchDialog: this,
                region: 'top'
            });
            
            var contentContainer = new dijit.layout.BorderContainer({
                style:"padding:0;margin:0;",
                gutters: false,
                region: 'center'
            });
            
            contentContainer.addChild(filterToolbar);
            contentContainer.addChild(resultGrid);
            
            borderContainer.addChild(toolbar);
            borderContainer.addChild(contentContainer);
            
            return borderContainer;
        },
        createResultGrid: function(){
            var store = new dojo.data.ItemFileReadStore({
                data:{
                    identifier: 'id',
                    items: [{id:buildspace.constants.GRID_LAST_ROW, description:"",type:'2', uom_id:'-1', uom_symbol:"", rate:0}]
                },
                clearOnClose: true
            });
            
            var formatter = GridFormatter();
            
            var customDescriptionFormatter = function(cellValue, rowIdx, cell){
                var item = this.grid.getItem(rowIdx),
                    level = item.level*16;

                cellValue = cellValue == null ? '&nbsp': cellValue;

                if(item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER || item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER_N){
                    cellValue =  '<b>'+cellValue+'</b>';
                }

                if(item.type < 1){
                    cell.customClasses.push('invalidTypeItemCell');
                }
                
                cellValue = '<div style="padding-left:'+level+'px;">'+cellValue+'&nbsp;</div>';
                
                return cellValue;
            };
            
            var customTypeFormatter = function(cellValue, rowIdx, cell){
                var item = this.grid.getItem(rowIdx);
                switch (cellValue) {
                    case buildspace.widget.grid.constants.HIERARCHY_TYPE_WORK_ITEM:
                        cellValue = buildspace.widget.grid.constants.HIERARCHY_TYPE_WORK_ITEM_TEXT;
                        break;
                    case buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_HTML_EDITOR:
                        cellValue = buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_HTML_EDITOR_TEXT;
                        break;
                    case buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_PROVISIONAL:
                        cellValue = buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_PROVISIONAL_TEXT;
                        break;
                    case buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_RATE_ONLY:
                        cellValue = buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_RATE_ONLY_TEXT;
                        break;
                    case buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_NOT_LISTED:
                        cellValue = buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_NOT_LISTED_TEXT;
                        break;
                    case buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_PC_RATE:
                        cellValue = buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_PC_RATE_TEXT;
                        break;
                    case buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_LUMP_SUM:
                        cellValue = buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_LUMP_SUM_TEXT;
                        break;
                    case buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_LUMP_SUM_EXCLUDE:
                        cellValue = buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_LUMP_SUM_EXCLUDE_TEXT;
                        break;
                    case buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_LUMP_SUM_PERCENT:
                        cellValue = buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_LUMP_SUM_PERCENT_TEXT;
                        break;
                    default:
                        cellValue = "&nbsp;";
                        break;
                }
                
                if(item.type < 0){
                    cell.customClasses.push('invalidTypeItemCell');
                }
                
                return item.id[0] > 0 ? cellValue : "&nbsp;";
            };
            
            var customCellFormatter = function(cellValue, rowIdx, cell){
                var item = this.grid.getItem(rowIdx);
                
                if(item.type < 0){
                    cell.customClasses.push('invalidTypeItemCell');
                }
                
                return item.id[0] > 0 ? cellValue : "&nbsp;";
            };
            
            /*set up layout*/
            var layout = [[
                {name: nls.description, field: 'description', noresize: true, width:'auto', formatter: customDescriptionFormatter },
                {name: nls.type, field: 'type', noresize: true, width:'70px', styles:'text-align:center;', formatter: customTypeFormatter},
                {name: nls.unit, field: 'uom_id', noresize: true, width:'50px', styles:'text-align:center;', formatter: formatter.unitIdCellFormatter},
                {name: nls.rate, field: 'rate', noresize: true, width:'100px', styles:'text-align:right;', formatter: formatter.currencyCellFormatter},
                {name: nls.publishedDate, field: 'published_at', width:'100px', styles:'text-align: center;', formatter: customCellFormatter}
            ]];

            /*create a new grid:*/
            return new ResultGrid({
                id: 'grid',
                store: store,
                structure: layout,
                region: 'center'});
        }
    });
});