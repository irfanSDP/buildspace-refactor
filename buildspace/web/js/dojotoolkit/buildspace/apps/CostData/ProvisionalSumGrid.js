define('buildspace/apps/CostData/ProvisionalSumGrid',[
    'dojo/_base/declare',
    'dojo/_base/lang',
    'dojo/number',
    'dojo/currency',
    'dojox/grid/EnhancedGrid',
    'buildspace/widget/grid/cells/DateTextBox',
    'dojo/i18n!buildspace/nls/CostData'
], function(declare, lang, number, currency, EnhancedGrid, DateTextBox, nls){

    var Grid = declare('buildspace.apps.CostData.ProvisionalSumGrid', EnhancedGrid, {
        style: "border:none;",
        region: 'center',
        keepSelection: true,
        rowSelector: '0px',
        escapeHTMLInData: false,
        costData: null,
        addUrl: 'provisionalSum/addNewItem',
        updateUrl: 'provisionalSum/updateItem',
        constructor:function(args){
            this.inherited(arguments);
            this.createHeaderCtxMenu();
        },
        canSort: function(inSortInfo){
            return false;
        },
        postCreate: function(){
            var self = this;
            this.inherited(arguments);

            this.on('RowClick', function (e) {
                if (e.cell) {
                    var item = this.getItem(e.rowIndex);
                    var enable = false;
                    if(item.id[0] > 0) {
                        enable = true;
                    }

                    if(this.editable) self.enableToolbarButtons(enable);
                }
            });
            this.createHeaderCtxMenuItems();
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
        enableToolbarButtons: function(enable){
            var deleteButton = dijit.byId(this.costData.id + 'provisional-sum-item-delete-button');
            deleteButton._setDisabledAttr(!enable);
        },
        doApplyCellEdit: function(val, rowIdx, inAttrName){
            var self = this, item = self.getItem(rowIdx), store = self.store;

            if(val !== item[inAttrName][0]){
                var pb = buildspace.dialog.indeterminateProgressBar({
                    title:nls.savingData+'. '+nls.pleaseWait+'...'
                });
                var params = {
                    id: item.id,
                    attr_name: inAttrName,
                    val: val,
                    cost_data_id: self.costData.id,
                    _csrf_token: item._csrf_token ? item._csrf_token : null
                }, url = this.updateUrl;

                if(item.id==buildspace.constants.GRID_LAST_ROW){
                    var prevItem = rowIdx > 0 ? self.getItem(rowIdx-1):false;
                    lang.mixin(params, {
                        prev_item_id: prevItem ? prevItem.id : 0
                    });
                    url = this.addUrl;
                }

                var updateCell = function(data, store){
                    for(var property in data){
                        if(item.hasOwnProperty(property) && property != store._getIdentifierAttribute()){
                            store.setValue(item, property, data[property]);
                        }
                    }
                    store.save();
                };

                var xhrArgs = {
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

                pb.show();
                dojo.xhrPost(xhrArgs);
            }
            self.inherited(arguments);
        },
        canEdit: function(inCell, inRowIndex){
            return true;
        },
        createHeaderCtxMenu: function(){
            this.plugins = {menus: {
                headerMenu: new dijit.Menu()
            }};
        },
        createHeaderCtxMenuItems: function(){
            menusObject = this.plugins.menus;

            if (typeof this.structure !== 'undefined') {
                var column = this.structure.cells[0],
                    self = this;
                dojo.forEach(column, function(data, index){
                    if(data.showInCtxMenu){
                        var label = data.name;
                        var field = data.field;
                        switch(field){
                            case 'approved_cost':
                                label = nls.budget;
                                break;
                            case 'awarded_cost':
                                label = nls.contractSum;
                                break;
                            case 'adjusted_sum':
                                label = nls.adjustedSum;
                                break;
                            default:
                                label = data.name;
                        }
                        menusObject.headerMenu.addChild(new dijit.CheckedMenuItem({
                            label: label,
                            checked: (typeof data.hidden === 'undefined' || data.hidden === false) ? true : false,
                            onChange: function(val){
                                var show = false;
                                if (val){
                                    show = true;
                                }
                                self.showHideColumn(show, index, field);
                                setCookie('CostData.hiddenColumns.'+field, !show);
                            }
                        }));
                    }
                });
            }
        },
        getCellIndexByAttribute: function(attribute, value){
            var cellIndex;
            dojo.forEach(this.layout.cells, function(data, index){
                if(data[attribute] && data[attribute] == value) cellIndex = index;
            });
            return cellIndex;
        },
        showHideColumn: function(show, index, field) {
            this.beginUpdate();

            var indexes = [index];

            if(field == 'awarded_cost'){
                indexes.push(this.getCellIndexByAttribute('field', 'awarded_date'));
                indexes.push(this.getCellIndexByAttribute('originalName', nls.contractSum));
            }
            for(var i in indexes){
                this.layout.setColumnVisibility(indexes[i], show);
            }
            this.endUpdate();
        },
        reload: function(){
            this.store.close();
            this._refresh();
        }
    });

    var Formatter = {
        rowCountCellFormatter: function(cellValue, rowIdx){
            return parseInt(rowIdx)+1;
        },
        amountFormatter: function(cellValue, rowIdx, cell){
            var value = number.parse(cellValue);

            if(isNaN(value) || value == 0 || value == null){
                cellValue = "&nbsp;";
            }else{
                cellValue = currency.format(cellValue);
            }

            return cellValue;
        },
        unEditableCurrencyCellFormatter: function(cellValue, rowIdx, cell){
            var item = this.grid.getItem(rowIdx),
                value = number.parse(cellValue);
            if(isNaN(value) || value == 0 || value == null){
                cellValue = "&nbsp;";
            }else{
                var cellValue = currency.format(value);
            }

            cell.customClasses.push('disable-cell');

            return cellValue;
        },
    };

    return declare('buildspace.apps.CostData.ProvisionalSumContainer', dijit.layout.BorderContainer, {
        region: "center",
        title: '',
        style:"padding:0px;border:none;width:100%;height:100%;",
        gutters: false,
        costData: null,
        grid: null,
        stackContainer: null,
        editable: false,
        postCreate: function(){
            this.inherited(arguments);
            var self = this;

            var grid = this.createBreakdownGrid();

            var gridContainer = new dijit.layout.ContentPane( {
                style:"padding:0px;border:none;width:100%;height:100%;",
                region: 'center',
                content: grid,
                grid: grid
            });

            this.addChild(self.createToolbar());
            this.addChild(gridContainer);
        },
        createToolbar: function(){
            var self = this;
            var toolbar = new dijit.Toolbar({
                region: "top",
                style: "outline:none!important;border:none;padding:2px;width:100%;"
            });

            toolbar.addChild(
                new dijit.form.Button({
                    id: this.costData.id + 'provisional-sum-item-delete-button',
                    label: nls.delete,
                    iconClass: "icon-16-container icon-16-delete",
                    disabled: true,
                    style: "outline:none!important;",
                    onClick: function () {
                        if (self.grid.selection.selectedIndex > -1) {
                            var item = self.grid.getItem(self.grid.selection.selectedIndex);
                            if (item.id[0] > 0) {
                                self.deleteRow(item);
                            }
                        }
                    }
                })
            );

            toolbar.addChild(new dijit.ToolbarSeparator());

            toolbar.addChild(
                new dijit.form.Button({
                    id: this.costData.id + 'provisional-sum-refresh-button',
                    label: nls.refresh,
                    iconClass: "icon-16-container icon-16-reload",
                    style: "outline:none!important;",
                    onClick: function () {
                        self.grid.reload();
                    }
                })
            );

            return toolbar;
        },
        getGridStructure: function(){
            return {
                nosroll: false,
                cells: [
                    [{
                        name: 'No.',
                        field: 'count',
                        width:'30px',
                        styles:'text-align:center;',
                        rowSpan: 2,
                        formatter: Formatter.rowCountCellFormatter
                    },{
                        name: nls.description,
                        field: 'description',
                        width:'auto',
                        rowSpan: 2,
                        cellType: 'buildspace.widget.grid.cells.Textarea',
                        editable: this.editable
                    },{
                        name: this.costData.approved_date ? nls.budget+' ('+this.costData.approved_date+')' : nls.budget,
                        field: 'approved_cost',
                        width:'140px',
                        rowSpan: 2,
                        cellType: 'buildspace.widget.grid.cells.Textarea',
                        editable: this.editable,
                        showInCtxMenu: true,
                        hidden:getCookieBoolean('CostData.hiddenColumns.approved_cost'),
                        styles:'text-align:right;',
                        formatter: Formatter.amountFormatter
                    },{
                        name: nls.totalAmount,
                        field: 'awarded_cost',
                        width:'140px',
                        cellType: 'buildspace.widget.grid.cells.Textarea',
                        editable: this.editable,
                        showInCtxMenu: true,
                        hidden:getCookieBoolean('CostData.hiddenColumns.awarded_cost'),
                        styles:'text-align:right;',
                        formatter: Formatter.amountFormatter
                    },{
                        name: nls.awardedDate,
                        field: 'awarded_date',
                        width:'120px',
                        cellType: 'buildspace.widget.grid.cells.DateTextBox',
                        editable: this.editable,
                        hidden:getCookieBoolean('CostData.hiddenColumns.awarded_cost'),
                        styles:'text-align:center;'
                    },{
                        name: this.costData.adjusted_date ? nls.adjustedSum+' ('+this.costData.adjusted_date+')' : nls.adjustedSum,
                        originalName: nls.adjustedSum,
                        field: 'adjusted_sum',
                        width:'150px',
                        rowSpan: 2,
                        showInCtxMenu: true,
                        styles:'text-align:right;',
                        hidden:getCookieBoolean('CostData.hiddenColumns.adjusted_cost'),
                        formatter: Formatter.unEditableCurrencyCellFormatter
                    },{
                        name: nls.variationOrderCost,
                        field: 'variation_order_cost',
                        width:'140px',
                        rowSpan: 2,
                        cellType: 'buildspace.widget.grid.cells.Textarea',
                        editable: this.editable,
                        showInCtxMenu: true,
                        hidden:getCookieBoolean('CostData.hiddenColumns.variation_order_cost'),
                        styles:'text-align:right;',
                        formatter: Formatter.amountFormatter
                    }],
                    [{
                        name: this.costData.awarded_date ? nls.contractSum+' ('+this.costData.awarded_date+')' : nls.contractSum,
                        originalName: nls.contractSum,
                        hidden:getCookieBoolean('CostData.hiddenColumns.awarded_cost'),
                        styles:'text-align:center;',
                        headerClasses: "staticHeader",
                        colSpan : 2
                    }]
                ]
            };
        },
        createBreakdownGrid: function(){
            var self = this;
            self.grid = Grid({
                id: 'costData-provisionalSumGrid',
                costData: self.costData,
                structure: self.getGridStructure(),
                store: new dojo.data.ItemFileWriteStore({
                    clearOnClose:true,
                    url:"provisionalSum/getProvisionalSumBreakdown/costData/"+this.costData.id
                })
            });

            return self.grid;
        },
        deleteRow: function (item) {
            var self = this,
                pb = buildspace.dialog.indeterminateProgressBar({
                    title: nls.deleting + '. ' + nls.pleaseWait + '...'
                });

            var onYes = function () {
                pb.show().then(function(){
                    dojo.xhrPost({
                        url: 'provisionalSum/deleteItem',
                        content: {id: item.id, _csrf_token: item._csrf_token},
                        handleAs: 'json',
                        load: function (resp) {
                            if (resp.success) {
                                self.grid.reload();
                            }
                            pb.hide();
                            self.grid.selection.clear();
                            self.grid.enableToolbarButtons(false);
                        },
                        error: function (error) {
                            pb.hide();
                            self.grid.selection.clear();
                            self.grid.enableToolbarButtons(false);
                        }
                    });
                });
            };

            buildspace.dialog.confirm(nls.confirmation, nls.confirmationMessage + '<br/>' + nls.cannotBeUndone, 80, 300, onYes);
        }
    });
});