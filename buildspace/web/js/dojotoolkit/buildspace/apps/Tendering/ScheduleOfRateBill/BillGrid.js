define('buildspace/apps/Tendering/ScheduleOfRateBill/BillGrid',[
    'dojo/_base/declare',
    'dojo/_base/lang',
    "dojo/_base/array",
    "dojo/dom-attr",
    "dijit/Menu",
    'dojo/number',
    "dojox/grid/enhanced/plugins/Menu",
    "dojox/grid/enhanced/plugins/Selector",
    "dojox/grid/enhanced/plugins/Rearrange",
    "buildspace/widget/grid/plugins/FormulatedColumn",
    'dojo/_base/event',
    'dojo/keys',
    "dijit/focus",
    'dojo/_base/html',
    'dojo/request/xhr',
    'dijit/PopupMenuItem',
    "dijit/MenuSeparator",
    'buildspace/widget/grid/cells/Select',
    'buildspace/widget/grid/cells/Textarea',
    'buildspace/widget/grid/cells/FormulaTextBox',
    "buildspace/widget/grid/cells/Formatter",
    'dojo/i18n!buildspace/nls/ProjectBuilder'
], function(declare, lang, array, domAttr, Menu, number, MenuPlugin, Selector, Rearrange, FormulatedColumn, evt, keys, focusUtil, html, xhr, PopupMenuItem, MenuSeparator, Select, Textarea, FormulaTextBox, GridFormatter, nls){

    var BillGrid = declare('buildspace.apps.Tendering.ScheduleOfRateBill.BillGrid', dojox.grid.EnhancedGrid, {
        type: null,
        billId: -1,
        elementId: 0,
        itemId: -1,
        style: "border-top:none;",
        selectedItem: null,
        borderContainerWidget: null,
        keepSelection: true,
        rowSelector: '0px',
        addUrl: null,
        updateUrl: null,
        rowUpdateUrl: null,
        deleteUrl: null,
        pasteUrl: null,
        indentUrl: null,
        outdentUrl: null,
        headerMenu: null,
        parentGrid: null,
        elementGridStore: null,
        editable: false,
        currentGridType: 'element',
        constructor:function(args){
            this.type                    = args.type;
            this.hierarchyTypes          = args.hierarchyTypes;
            this.hierarchyTypesForHead   = args.hierarchyTypesForHead;
            this.unitOfMeasurements      = args.unitOfMeasurements;
            this.currencySetting         = (buildspace.billCurrencyAbbreviation) ? buildspace.billCurrencyAbbreviation : buildspace.currencyAbbreviation;

            this.editable = args.editable;
            this.currentGridType         = args.currentGridType;

            this.formatter = new GridFormatter();

            this.rearranger = Rearrange(this, {});
            this.formulatedColumn = FormulatedColumn(this,{});

            this.setColumnStructure();
        },
        canSort: function(inSortInfo){
            return false;
        },
        postCreate: function(){
            var self = this;
            this.inherited(arguments);

            this.on('RowClick', function(e){
                self.disableToolbarButtons(false);
            });
        },
        doApplyCellEdit: function(val, rowIdx, inAttrName){
            var self = this, item = self.getItem(rowIdx), store = self.store;
            var attrNameParsed = inAttrName.replace("-value","");//for any formulated column
            if(inAttrName.indexOf("-value") !== -1){
                val = this.formulatedColumn.convertRowIndexToItemId(val, rowIdx);
            }

            if(val !== item[inAttrName][0]){
                var pb = buildspace.dialog.indeterminateProgressBar({
                    title:nls.savingData+'. '+nls.pleaseWait+'...'
                });
                var params = {
                    id: item.id,
                    attr_name: attrNameParsed,
                    val: val,
                    bill_id : self.billId,
                    _csrf_token: item._csrf_token ? item._csrf_token : null
                }, url = this.updateUrl;

                if(item.id==buildspace.constants.GRID_LAST_ROW){
                    var prevItem = rowIdx > 0 ? self.getItem(rowIdx-1):false;
                    lang.mixin(params, {
                        prev_item_id: prevItem ? prevItem.id : 0,
                        relation_id: item.relation_id
                    });
                    url = this.addUrl;
                }

                var updateCell = function(data, store){
                    for(var property in data){
                        if(item.hasOwnProperty(property) && property != store._getIdentifierAttribute()){
                            store.setValue(item, property, data[property]);
                        }
                    }

                    if(self.type != 'tree'){
                        dojo.forEach(data.other_elements, function(node){
                            store.fetchItemByIdentity({ 'identity' : node.id,  onItem : function(element){
                                for(var property in node){
                                    if(element.hasOwnProperty(property) && property != store._getIdentifierAttribute()){
                                        store.setValue(element, property, node[property]);
                                    }
                                }
                            }});
                        });
                    }
                    store.save();
                };

                pb.show();
                dojo.xhrPost({
                    url: url,
                    content: params,
                    handleAs: 'json',
                    load: function(resp) {
                        if(resp.success){
                            if(item.id > 0){
                                updateCell(resp.data, store);
                            }else{
                                store.deleteItem(item);
                                store.save();
                                dojo.forEach(resp.items, function(item){
                                    store.newItem(item);
                                });
                                store.save();
                                self.disableToolbarButtons(true);
                            }
                            var cell = self.getCellByField(inAttrName);
                            window.setTimeout(function() {
                                self.focus.setFocusIndex(rowIdx, cell.index);
                            }, 10);
                            pb.hide();
                        }
                    },
                    error: function(error) {
                        pb.hide();
                    }
                });
                self.inherited(arguments);

            }else{
                self.inherited(arguments);
            }
        },
        canEdit: function(inCell, inRowIndex){
            var self = this;

            if(this.type=='tree'){
                if(inCell != undefined){
                    var item = this.getItem(inRowIndex),
                        field = inCell.field;

                    // if current bill has been set to locked status, don't allow user input
                    // into selected column
                    if (!this.editable) {
                        return false;
                    }

                    if(item.id[0] > 0){
                        if((item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER || item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER_N) && field != 'description' && field != 'type' && inCell.editable){
                            window.setTimeout(function() {
                                self.edit.cancel();
                                self.focus.setFocusIndex(inRowIndex, inCell.index);
                            }, 10);
                            return;
                        } else if (item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER && field != 'description' && field != 'type' && inCell.editable){
                            window.setTimeout(function() {
                                self.edit.cancel();
                                self.focus.setFocusIndex(inRowIndex, inCell.index);
                            }, 10);
                            return;
                        }

                        if(self.type == 'tree' && field === 'type'){
                            var nextItem = self.getItem(inRowIndex+1);

                            if((item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER || item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER_N) && inCell.editable && nextItem !== undefined && item.level[0] < nextItem.level[0]) {
                                inCell.options = self.hierarchyTypesForHead.options;
                                inCell.values  = self.hierarchyTypesForHead.values;
                            } else {
                                inCell.options = self.hierarchyTypes.options;
                                inCell.values  = self.hierarchyTypes.values;
                            }
                        }
                    } else if ( field !== 'description' && field !== 'type' ) {
                        return;
                    }
                }
            }
            return this._canEdit;
        },
        doCancelEdit: function(inRowIndex){
            this.inherited(arguments);
            this.views.renormalizeRow(inRowIndex);
            this.scroller.rowHeightChanged(inRowIndex, true);
        },
        setColumnStructure: function(){
            var formatter = this.formatter;
            var rateTitle = this.editable ? nls.contractorRate : nls.estimationRate ;
            if(this.type == 'tree'){
                this.structure = [{
                    name: 'No',
                    field: 'id',
                    styles: "text-align:center;",
                    width: '30px',
                    formatter: formatter.rowCountCellFormatter,
                    noresize: true
                },{
                    name: nls.description,
                    field: 'description',
                    width: 'auto',
                    formatter: formatter.treeCellFormatter,
                    noresize: true
                },{
                    name: nls.type,
                    field: 'type',
                    width: '70px',
                    styles: 'text-align:center;',
                    formatter: formatter.typeCellFormatter,
                    noresize: true
                },{
                    name: nls.unit,
                    field: 'uom_id',
                    width: '70px',
                    styles: 'text-align:center;',
                    formatter: formatter.unitIdCellFormatter,
                    noresize: true
                },{
                    name: rateTitle + " ("+this.currencySetting+")",
                    field: this.editable ? 'contractor_rate' : 'estimation_rate',
                    styles: "text-align:right;",
                    width: '128px',
                    editable: this.editable,
                    cellType: 'buildspace.widget.grid.cells.Textarea',
                    formatter: this.editable ? formatter.currencyCellFormatter : formatter.unEditableCurrencyCellFormatter,
                    noresize: true
                }];
            }else{
                this.structure = [{
                    name: 'No',
                    field: 'id',
                    styles: "text-align:center;",
                    width: '30px',
                    formatter: formatter.rowCountCellFormatter,
                    noresize: true
                },{
                    name: nls.description,
                    field: 'description',
                    width: 'auto',
                    noresize: true
                }];
            }
        },
        refreshGrid: function(){
            this.beginUpdate();

            this.set('structure', this.structure);
            this.store.close();

            this.pluginMgr = new this._pluginMgrClass(this);
            this.pluginMgr.preInit();
            this.pluginMgr.postInit();
            this.pluginMgr.startup();

            this._refresh();

            this.endUpdate();
        },
        editableCellDblClick: function(e){
            var event;
            if(this._click.length > 1 && has('ie')){
                event = this._click[1];
            }else if(this._click.length > 1 && this._click[0].rowIndex != this._click[1].rowIndex){
                event = this._click[0];
            }else{
                event = e;
            }
            this.focus.setFocusCell(event.cell, event.rowIndex);
            this.onRowClick(event);
            this.onRowDblClick(e);
        },
        dodblclick: function(e){
            if(e.cellNode){
                if(e.cell.editable){
                    this.editableCellDblClick(e);
                }else{
                    this.onCellDblClick(e);
                }
            }else{
                this.onRowDblClick(e);
            }
        },
        onStyleRow: function(e) {
            this.inherited(arguments);

            if(e.node.children[0]){
                if(e.node.children[0].children[0].rows.length >= 2){
                    var elemToHide = e.node.children[0].children[0].rows[1],
                        childElement = e.node.children[0].children[0].rows[0].children;

                    elemToHide.parentNode.removeChild(elemToHide);

                    dojo.forEach(childElement, function(child, i){
                        var rowSpan = dojo.attr(child, 'rowSpan');

                        if(!rowSpan || rowSpan < 2)
                            dojo.attr(child, 'rowSpan', 2);
                    });
                }
            }
        },
        disableToolbarButtons: function(isDisable, buttonsToEnable) {
            if(isDisable && buttonsToEnable instanceof Array ){
                var _this = this;
                dojo.forEach(buttonsToEnable, function(label){
                    var btn = dijit.byId(_this.billId+_this.elementId+label+'Row-button');
                    if(btn)
                        btn._setDisabledAttr(false);
                });
            }
        }
    });

    return declare('buildspace.apps.Tendering.ScheduleOfRateBill.BillGridBuilder', dijit.layout.BorderContainer, {
        pageId: 'page-00',
        style: "padding:0px;width:100%;height:100%;border:0px!important;",
        gutters: false,
        stackContainerTitle: '',
        billId: -1,
        elementId: 0,
        itemId: -1,
        rowSelector: null,
        gridOpts: {},
        editable: false,
        type: null,
        postCreate: function(){
            this.inherited(arguments);
            lang.mixin(this.gridOpts, {editable: this.editable, billId: this.billId, elementId: this.elementId, type: this.type, region: "center", borderContainerWidget: this });
            var grid = this.grid = new BillGrid(this.gridOpts);


            if(this.type != 'tree'){
                var billId = this.billId;
                var toolbar = new dijit.Toolbar({region: "top", style:"padding:2px;border-bottom:none;width:100%;"});

                toolbar.addChild(
                    new dijit.form.Button({
                        label: nls.printBill,
                        iconClass: "icon-16-container icon-16-print",
                        onClick: function(e) {
                            window.open('scheduleOfRateBill/printBill/id/' + billId, '_blank');
                        }
                    })
                );
                this.addChild(toolbar);
            }

            this.addChild(new dijit.layout.ContentPane({style: 'width:100%', content:grid, region:'center'}));

            var container = dijit.byId('scheduleOfRateBillGrid'+this.billId+'-stackContainer');
            if(container){
                var node = document.createElement("div");
                container.addChild(new dojox.layout.ContentPane( {
                    title: buildspace.truncateString(this.stackContainerTitle, 60),
                    content: this,
                    id: this.pageId,
                    grid: grid
                }, node));
                container.selectChild(this.pageId);
            }
        }
    });
});