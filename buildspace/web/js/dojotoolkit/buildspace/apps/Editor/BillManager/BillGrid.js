define('buildspace/apps/Editor/BillManager/BillGrid',[
    'dojo/_base/declare',
    'dojo/_base/lang',
    "dojo/_base/array",
    "dojo/dom-attr",
    "dijit/TooltipDialog",
    "dijit/popup",
    "dijit/Menu",
    'dojo/number',
    "dijit/CheckedMenuItem",
    "dojox/grid/enhanced/plugins/Menu",
    "dojox/grid/enhanced/plugins/Selector",
    "dojox/grid/enhanced/plugins/Rearrange",
    "buildspace/widget/grid/plugins/FormulatedColumn",
    './editBillNoteDialog',
    './PrintBillDialog',
    'dojo/_base/event',
    'dojo/keys',
    "dijit/focus",
    'dojo/_base/connect',
    'dojo/_base/html',
    'dojo/request/xhr',
    "dijit/form/DropDownButton",
    "dijit/DropDownMenu",
    "dijit/MenuItem",
    'dijit/PopupMenuItem',
    "dijit/MenuSeparator",
    'buildspace/widget/grid/cells/Select',
    'buildspace/widget/grid/cells/Textarea',
    'buildspace/widget/grid/cells/FormulaTextBox',
    "buildspace/apps/Tendering/BillManager/primeCostRateDialog",
    "buildspace/widget/grid/cells/Formatter",
    'dojo/i18n!buildspace/nls/Tendering',
    'dojo/on',
    'dojox/grid/_Grid',
    "dojox/widget/PlaceholderMenuItem",
    'buildspace/widget/grid/cells/TextBox'
], function(declare, lang, array, domAttr, TooltipDialog, popup, Menu, number, MenuCheckedItem, MenuPlugin, Selector, Rearrange, FormulatedColumn, EditBillNoteDialog, PrintBillDialog, evt, keys, focusUtil, connect, html, xhr, DropDownButton, DropDownMenu, MenuItem, PopupMenuItem, MenuSeparator, Select, Textarea, FormulaTextBox, PrimeCostRateDialog, GridFormatter, nls, on) {

    var BillGrid = declare('buildspace.apps.Editor.BillManager.BillGrid', dojox.grid.EnhancedGrid, {
        type: null,
        bill: null,
        elementId: 0,
        itemId: -1,
        style: "border-top:none;",
        selectedItem: null,
        borderContainerWidget: null,
        keepSelection: true,
        rowSelector: '0px',
        rowUpdateUrl: null,
        headerMenu: null,
        typeColumns: null,
        parentGrid: null,
        elementGridStore: null,
        currentPrintableRevision: null,
        currentBillVersion: 0,
        currentGridType: 'element',
        currentBillType: null,
        typeColumsToQtyUsed: [],
        constructor:function(args){
            this.bill                    = args.bill;
            this.type                    = args.type;
            this.columnGroup             = args.columnGroup;
            this.typeColumns             = args.typeColumns;
            this.unitOfMeasurements      = args.unitOfMeasurements;
            this.currencySetting         = (buildspace.billCurrencyAbbreviation) ? buildspace.billCurrencyAbbreviation : buildspace.currencyAbbreviation;
            this.currentPrintableVersion = args.currentPrintableRevision;
            this.currentBillVersion      = args.currentBillVersion;
            this.currentGridType         = args.currentGridType;

            var formatter = this.formatter = new GridFormatter();

            if(this.type != 'tree'){
                this.typeColumnChildren = [
                    {name: '% '+nls.job, field_name: 'total', width: '60px', styles: "text-align:right;", editable: false, formatter: formatter.elementTypeJobPercentageCellFormatter},
                    {name: nls.costPerMetreSquare, field_name: 'total_cost', width: '100px', styles: "text-align:right;", editable: false, formatter: formatter.unEditableCurrencyCellFormatter},
                    {name: nls.total+'/'+nls.unit, field_name: 'total_per_unit', width: '100px', styles: "text-align:right;", editable: false, formatter: formatter.elementTotalPerUnitCellFormatter}
                ];

            }else{

                var customFormatter = {
                    billQuantityCellFormatter: function(cellValue, rowIdx, cell){
                        var item = this.grid.getItem(rowIdx),
                            value = number.parse(cellValue),
                            fieldConstantName = this.field.replace("-value", ""),
                            finalValue = item[fieldConstantName+'-final_value'][0],
                            val = '&nbsp;';

                        if(isNaN(finalValue) || finalValue == 0 || finalValue == null){
                            var formattedValue = "&nbsp;";
                        }else{
                            var formattedValue = number.format(finalValue, {places: 2});
                        }

                        val = finalValue >= 0 ? formattedValue : '<span style="color:#FF0000">'+formattedValue+'</span>';

                        var str = this.field.split('-');

                        if (item.type != undefined && item.type < 1) {
                            cell.customClasses.push('invalidTypeItemCell');
                            val = "&nbsp;";
                        } else if(item.type != buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_NOT_LISTED) {
                            cell.customClasses.push('disable-cell');
                            if(item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER || item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER_N || item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_NOID || item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_RATE_ONLY){
                                val = (item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_RATE_ONLY) ? nls.rateOnly : '&nbsp;'
                            }
                        }

                        if (item.version != undefined  && item.version > 0){
                            if (this.grid.currentBillVersion != undefined && this.grid.currentBillVersion == item.version){
                                cell.customClasses.push('hasCurrentAddendumTypeItemCell');
                            }else{
                                cell.customClasses.push('hasAddendumTypeItemCell');
                            }
                        }

                        return val;
                    }
                };

                this.typeColumnChildren = [
                    {name: nls.qty+'/'+nls.unit, field_name: 'quantity_per_unit-value', width: '70px', styles: "text-align:right;", editable: true, cellType: 'buildspace.widget.grid.cells.FormulaTextBox', formatter: customFormatter.billQuantityCellFormatter},
                    {name: nls.total+'/'+nls.unit, field_name: 'total_per_unit', width: '100px', styles: "text-align:right;", editable: false, formatter: formatter.itemTotalPerUnitCellFormatter}
                ];
            }

            this.rearranger = Rearrange(this, {});
            this.formulatedColumn = FormulatedColumn(this,{});

            this.setColumnStructure();

            this.createHeaderCtxMenu();
        },
        canSort: function(inSortInfo){
            return false;
        },
        postCreate: function() {
            this.inherited(arguments);

            var self = this;
            var tooltipDialog = null;

            if(this.type != 'tree'){
                this._connects.push(connect.connect(this, 'onCellMouseOver', function(e) {
                    var colField = e.cell.field,
                        rowIndex = e.rowIndex,
                        item = this.getItem(rowIndex);
                    
                    // will show tooltip for formula, if available
                    if (!item || colField != 'addendum_version' || (!item.hasOwnProperty('addendum_version')) || typeof item['addendum_version'] === 'undefined' || ! parseInt(item.addendum_version)) {
                        return;
                    }
    
                    if(tooltipDialog === null) {
                        // Call the asynchronous xhrGet
                        var deferred = dojo.xhrGet({
                            url: "addendumInfoByElement/"+String(item.id),
                            handleAs: "json",
                            sync:true,
                            preventCache: true
                        });
                        
                        // Now add the callbacks
                        deferred.then(function(data){
                            if(data.length){
                                var content = '<table class="buildspace-table"><thead><tr>'
                                + '<th class="gridCell" style="text-align:center;padding:2px;">'+nls.version+'</th>'
                                + '<th class="gridCell" style="text-align:center;padding:2px;">'+nls.revision+'</th>'
                                + '</tr><tbody>';
    
                                for (var i = 0; i < data.length; i++){
                                    content += '<tr><td class="gridCell" style="text-align:center;">'+data[i].version + '</td><td class="gridCell" style="text-align:center;padding-left:4px;padding-right:4px;">'+ data[i].revision +'</td></tr>';
                                }
    
                                content +='</tbody></table>';
                                tooltipDialog = new TooltipDialog({
                                    content: content,
                                    onMouseLeave: function() {
                                        popup.close(tooltipDialog);
                                    }
                                });
    
                                popup.open({
                                    popup: tooltipDialog,
                                    around: e.cellNode
                                });
                            }
                        });
                    }
                }));
            }

            this._connects.push(connect.connect(this, 'onCellMouseOut', function() {
                if(tooltipDialog !== null){
                    popup.close(tooltipDialog);
                    tooltipDialog = null;
                }
            }));

            this._connects.push(connect.connect(this, 'onStartEdit', function() {
                if(tooltipDialog !== null){
                    popup.close(tooltipDialog);
                    tooltipDialog = null;
                }
            }));
        },
        doApplyCellEdit: function(val, rowIdx, inAttrName){
            var self = this, item = this.getItem(rowIdx), store = this.store;
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
                    _csrf_token: item._csrf_token ? item._csrf_token : null
                }, url = this.rowUpdateUrl;

                var updateCell = function(data, store){
                    for(var property in data){
                        if(item.hasOwnProperty(property) && property != store._getIdentifierAttribute()){
                            store.setValue(item, property, data[property]);
                        }
                    }

                    if(data.affected_nodes.hasOwnProperty('affected_bill_items')){
                        dojo.forEach(data.affected_nodes['affected_bill_items'], function(node){
                            store.fetchItemByIdentity({ 'identity' : node.id,  onItem : function(affectedItem){
                                for(var property in node){
                                    if(affectedItem.hasOwnProperty(property) && property != store._getIdentifierAttribute()){
                                        store.setValue(affectedItem, property, node[property]);
                                    }
                                }
                            }});
                        });
                    }

                    if(data.affected_nodes.hasOwnProperty('affected_bill_item_type_references')){
                        dojo.forEach(data.affected_nodes['affected_bill_item_type_references'], function(node){
                            store.fetchItemByIdentity({ 'identity' : node.id,  onItem : function(affectedItem){
                                for(var property in node){
                                    if(affectedItem.hasOwnProperty(property) && property != store._getIdentifierAttribute()){
                                        store.setValue(affectedItem, property, node[property]);
                                    }
                                }
                            }});
                        });
                    }

                    dojo.forEach(data.affected_nodes, function(node){
                        store.fetchItemByIdentity({ 'identity' : node.id,  onItem : function(affectedItem){
                            for(var property in node){
                                if(affectedItem.hasOwnProperty(property) && property != store._getIdentifierAttribute()){
                                    store.setValue(affectedItem, property, node[property]);
                                }
                            }
                        }});
                    });

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

                var xhrArgs = {
                    url: url,
                    content: params,
                    handleAs: 'json',
                    load: function(resp) {
                        if(resp.success){
                            if(item && !isNaN(parseInt(item.id[0]))){
                                updateCell(resp.data, store);
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
                };

                pb.show().then(function(){
                    dojo.xhrPost(xhrArgs);
                });
            }

            this.inherited(arguments);
        },
        canEdit: function(inCell, inRowIndex){
            var self = this;

            if(this.type=='tree' && inCell != undefined){
                var item = this.getItem(inRowIndex),
                    field = inCell.field,
                    splittedFieldName = field.split("-");

                if(item && item.project_revision_deleted_at !== undefined && item.project_revision_deleted_at[0]){
                    window.setTimeout(function() {
                        self.edit.cancel();
                        self.focus.setFocusIndex(inRowIndex, inCell.index);
                    }, 10);
                    return;
                }

                if(splittedFieldName.length == 3){
                    var colId = splittedFieldName[0];
                    field = splittedFieldName[1];
                }

                if(item && !isNaN(parseInt(item.id.toString()))){
                    switch(field){
                        case 'rate-value':
                            var rateDisabledItemType = [
                                buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_LUMP_SUM_EXCLUDE,
                                buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_LUMP_SUM_PERCENT,
                                buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_PC_RATE,
                                buildspace.widget.grid.constants.HIERARCHY_TYPE_NOID,
                                buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER,
                                buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER_N
                            ];

                            if(rateDisabledItemType.indexOf(item.type.toString()) >= 0){
                                if(item.type.toString()==buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_PC_RATE){
                                    new PrimeCostRateDialog({
                                        itemObj: item,
                                        billGridStore: self.store,
                                        elementGridStore: self.elementGridStore,
                                        currentBillLockedStatus: self.currentBillLockedStatus,
                                        currentBillVersion: self.currentBillVersion,
                                        currentItemVersion: item.version.toString(),
                                        disableEditingMode: true
                                    }).show();
                                }

                                window.setTimeout(function() {
                                    self.edit.cancel();
                                    self.focus.setFocusIndex(inRowIndex, inCell.index);
                                }, 10);
                                return;
                            }
                            break;
                        case 'quantity_per_unit':
                        case 'description':
                        case 'uom_id':
                            if(item.type.toString()!=buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_NOT_LISTED){
                                window.setTimeout(function() {
                                    self.edit.cancel();
                                    self.focus.setFocusIndex(inRowIndex, inCell.index);
                                }, 10);
                                return;
                            }
                            break;
                    }
                }else{
                    window.setTimeout(function() {
                        self.edit.cancel();
                        self.focus.setFocusIndex(inRowIndex, inCell.index);
                    }, 10);
                    return;
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
            var descriptionWidth = 'auto';

            if(this.typeColumns.length > 2){
                descriptionWidth = '600px';
            }

            if(this.type == 'tree'){
                var fixedColumns = this.fixedColumns = {
                    noscroll: false,
                    width: 57.8,
                    cells: [
                        [{
                            name: 'No',
                            field: 'id',
                            styles: "text-align:center;",
                            width: '30px',
                            formatter: formatter.rowCountCellFormatter,
                            noresize: true,
                            rowSpan: 2,
                            showInCtxMenu: true
                        },{
                            name: nls.billReference,
                            field: 'bill_ref',
                            styles: "text-align:center; color: red;",
                            width: '80px',
                            noresize: true,
                            hidden: true,
                            rowSpan: 2,
                            showInCtxMenu: true,
                            formatter: formatter.billRefCellFormatter
                        },{
                            name: nls.description,
                            field: 'description',
                            width: descriptionWidth,
                            editable: true,
                            cellType: 'buildspace.widget.grid.cells.Textarea',
                            formatter: formatter.treeCellFormatter,
                            noresize: true,
                            rowSpan: 2,
                            showInCtxMenu: true
                        },{
                            name: nls.type,
                            field: 'type',
                            width: '70px',
                            styles: 'text-align:center;',
                            formatter: formatter.typeCellFormatter,
                            noresize: true,
                            rowSpan: 2,
                            showInCtxMenu: true
                        },{
                            name: nls.unit,
                            field: 'uom_id',
                            width: '70px',
                            editable: true,
                            styles: 'text-align:center;',
                            type: 'dojox.grid.cells.Select',
                            options: this.unitOfMeasurements.options,
                            values: this.unitOfMeasurements.values,
                            formatter: formatter.unitIdCellFormatter,
                            noresize: true,
                            rowSpan: 2,
                            showInCtxMenu: true
                        },{
                            name: nls.grandTotalQty,
                            field: 'grand_total_quantity',
                            styles: "text-align:right;color:blue;",
                            width: '90px',
                            formatter: formatter.unEditableNumberCellFormatter,
                            noresize: true,
                            hidden: true,
                            rowSpan: 2,
                            showInCtxMenu: true
                        }]
                    ]
                };

                var hideGrandTotalColumn = false;

                var fixedColumnsAfterTypeColumns = [{
                    name: nls.rate,
                    field: 'rate-value',
                    field_name: 'rate-value',
                    styles: "text-align:right;",
                    width: '75px',
                    editable: true,
                    cellType: 'buildspace.widget.grid.cells.FormulaTextBox',
                    formatter: formatter.formulaCurrencyCellFormatter,
                    noresize: true,
                    rowSpan: 2
                },{
                    name: nls.grandTotal,
                    field: 'grand_total',
                    styles: "text-align:right;color:blue;",
                    width: '100px',
                    formatter: formatter.unEditableCurrencyCellFormatter,
                    noresize: true,
                    hidden: hideGrandTotalColumn,
                    rowSpan: 2,
                    showInCtxMenu: true
                }];
                var columnToDisplay = this.constructTypeColumnStructure(fixedColumns);
            }else{

                var hideGrandTotalColumn = false;

                descriptionWidth = 'auto';

                if(this.typeColumns.length > 2){
                    descriptionWidth = '600px';
                }

                var fixedColumns = this.fixedColumns = {
                    noscroll: false,
                    width: '50',
                    cells: [
                        [{
                            name: 'No',
                            field: 'id',
                            styles: "text-align:center;",
                            width: '30px',
                            formatter: formatter.rowCountCellFormatter,
                            noresize: true,
                            rowSpan: 2
                        }, {
                            name: nls.description,
                            field: 'description',
                            width: descriptionWidth,
                            formatter: formatter.treeCellFormatter,
                            noresize: true,
                            rowSpan: 2
                        }]
                    ]
                },
                fixedColumnsAfterTypeColumns = [{
                    name: nls.grandTotal,
                    field: 'grand_total',
                    styles: "text-align:right;color:blue;",
                    width: '100px',
                    formatter: formatter.unEditableCurrencyCellFormatter,
                    hidden: hideGrandTotalColumn,
                    rowSpan: 2,
                    noresize: true,
                    showInCtxMenu: true
                }];

                if(parseInt(String(this.bill.addendum_version))){
                    fixedColumns.cells[0].push({name: nls.addendum, field: 'addendum_version', styles:'text-align: center;', width:'68px', formatter: formatter.addendumInfoCellFormatter, noresize: true, rowSpan: 2});
                }

                var columnToDisplay = this.constructTypeColumnStructure(fixedColumns);
            }

            //Generate After Type Column
            dojo.forEach(fixedColumnsAfterTypeColumns,function(column){
                columnToDisplay.cells[0].push(column);
            });

            this.structure = columnToDisplay;
        },
        constructTypeColumnStructure: function(fixedColumns){
            var self = this,
                typeColumns = this.typeColumns,
                typeColumnChildren = this.typeColumnChildren,
                parentCells = [];
                var colCount = 0;

            dojo.forEach(typeColumns, function(typeColumn){
                self.typeColumsToQtyUsed[typeColumn.id] = typeColumn.use_original_quantity;

                var colspan = typeColumnChildren.length;

                colCount++;
                //rename Total Cost Name
                if(self.type != 'tree'){
                    if(typeColumn.floor_area_display_metric){
                        typeColumnChildren[1].name = nls.costPerMetreSquare;
                    }else{
                        typeColumnChildren[1].name = nls.costPerSquareFeet;
                    }
                }

                parentCells.push({
                    name: typeColumn.name + "<br>"+nls.totalUnit+":" + typeColumn.quantity,
                    styles:'text-align:center;',
                    headerClasses: "staticHeader typeHeader"+colCount,
                    colSpan: colspan,
                    headerId: typeColumn.id,
                    hidden: typeColumn.is_hidden
                });

                var field = null;
                for(i=0;i<typeColumnChildren.length;i++){
                    field = typeColumn.id+'-'+typeColumnChildren[i].field_name;
                    var cellStructure = {
                        field: field,
                        columnType: "typeColumn",
                        billColumnSettingId: typeColumn.id,
                        headerClasses: "typeHeader"+colCount
                    };
                    lang.mixin(cellStructure, typeColumnChildren[i]);
                    fixedColumns.cells[0].push(cellStructure);
                }
            });

            fixedColumns.cells.push(parentCells);

            return fixedColumns;
        },
        createHeaderCtxMenu: function(){
            if (typeof this.fixedColumns !== 'undefined') {
                var columnGroup = this.fixedColumns.cells[0],
                    self = this,
                    menusObject = {
                        headerMenu: new dijit.Menu()
                    };
                dojo.forEach(columnGroup, function(data, index){
                    if(data.showInCtxMenu){
                        menusObject.headerMenu.addChild(new dijit.CheckedMenuItem({
                            label: data.name,
                            checked: (typeof data.hidden === 'undefined' || data.hidden === false) ? true : false,
                            onChange: function(val){

                                var show = false;

                                if (val){
                                    show = true;
                                }

                                self.showHideMergedColumn(show, index);
                            }
                        }));
                    }
                });

                this.plugins = {menus: menusObject};
            }
        },
        showHideMergedColumn: function(show, index) {
            this.beginUpdate();
            this.layout.setColumnVisibility(index, show);
            this.endUpdate();
        },
        refreshGrid: function(){
            this.beginUpdate();

            this.set('structure', this.structure);
            this.set('headerMenu', this.createHeaderCtxMenu());
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
        onHeaderCellClick: function(e) {
           if (!dojo.hasClass(e.cell.id, "staticHeader")) {
               e.grid.setSortIndex(e.cell.index);
               e.grid.onHeaderClick(e);
           }
        },
        onHeaderCellMouseOver: function(e) {
           if (!dojo.hasClass(e.cell.id, "staticHeader")) {
               dojo.addClass(e.cellNode, this.cellOverClass);
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
                    var btn = dijit.byId(String(_this.bill.id)+_this.elementId+label+'Row-button');
                    if(btn)
                        btn._setDisabledAttr(false);
                });
            }
        },
        destroy: function(){
            this.inherited(arguments);
            dojo.forEach(this._connects, connect.disconnect);
            delete this._connects;
        }
    });

    return declare('buildspace.apps.Editor.BillManager.BillGridBuilder', dijit.layout.BorderContainer, {
        pageId: 'page-00',
        style: "padding:0px;width:100%;height:100%;",
        gutters: false,
        stackContainerTitle: '',
        bill: null,
        project: null,
        elementId: 0,
        itemId: -1,
        rowSelector: null,
        gridOpts: {},
        type: null,
        postCreate: function(){
            var self = this;
            this.inherited(arguments);
            lang.mixin(this.gridOpts, { bill: this.bill, elementId: this.elementId, type: this.type, region: "center", borderContainerWidget: this });
            var grid = this.grid = new BillGrid(this.gridOpts);

            if (this.type !== 'tree') {
                var toolbar = new dijit.Toolbar({region: "top", style:"padding:2px;border-bottom:none;width:100%;"});

                var printButtonLabel, bqCSRFToken = this.gridOpts.bqCSRFToken;


                if ( this.gridOpts.currentPrintableRevision.version > 0 ) {
                    printButtonLabel = nls.printAddendum;
                    var addendumOptions = ['printWithPrice', 'printWithoutPrice'];

                    var menu = new DropDownMenu({
                        style: "display: none;"
                    });

                    dojo.forEach(addendumOptions, function(opt) {
                        var withPrice;

                        if ( opt === 'printWithoutPrice' ) {
                            withPrice = 0;
                        } else {
                            withPrice = 1;
                        }

                        return menu.addChild(new MenuItem({
                            label: nls[opt],
                            onClick: function() {
                                window.open('BQPrintAddendum/'+String(self.bill.id)+'/'+bqCSRFToken+'/'+withPrice, '_blank');
                                return window.focus();
                            }
                        }));
                    });

                    toolbar.addChild(new DropDownButton({
                        label: nls.printAddendum,
                        iconClass: "icon-16-container icon-16-print",
                        name: "printAddendum",
                        dropDown: menu
                    }));
                } else {
                    printButtonLabel = nls.printBQ;
                    toolbar.addChild(
                        new dijit.form.Button({
                            label: printButtonLabel,
                            iconClass: "icon-16-container icon-16-print",
                            onClick: function() {
                            	new PrintBillDialog({
                                    billId: String(self.bill.id),
                                    bqCSRFToken: bqCSRFToken
                                }).show();
                            }
                        })
                    );
                }
                this.addChild(toolbar);
            }

            this.addChild(grid);

            var container = dijit.byId('billGrid'+String(this.bill.id)+'-stackContainer');
            if(container){
                container.addChild(new dojox.layout.ContentPane( {
                    title: buildspace.truncateString(this.stackContainerTitle, 60),
                    content: this,
                    id: this.pageId,
                    grid: grid
                }));
                container.selectChild(this.pageId);
            }
        }
    });
});
