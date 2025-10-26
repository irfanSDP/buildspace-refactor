define('buildspace/apps/CostData/Breakdown',[
    'dojo/_base/declare',
    'dojo/number',
    'dojo/currency',
    "dojo/_base/array",
    'dojox/grid/EnhancedGrid',
    './WorkCategoryGrid',
    './ProvisionalSumGrid',
    './PrimeCostSumWorkArea',
    './PrimeCostRateGrid',
    './ComparisonReport/ComparisonReportDialog',
    './TenderComparisonReport/TenderComparisonReportDialog',
    './RowInclusion/RowInclusionDialog',
    'buildspace/widget/grid/cells/Textarea',
    'dojo/i18n!buildspace/nls/CostData'
], function(declare, number, currency, array, EnhancedGrid, WorkCategoryGrid, ProvisionalSumGrid, PrimeCostSumWorkArea, PrimeCostRateGrid, ComparisonReportDialog, TenderComparisonReportDialog, RowInclusionDialog, Textarea, nls){

    var Grid = declare('buildspace.apps.CostData.BreakdownGrid', EnhancedGrid, {
        style: "border:none;",
        region: 'center',
        keepSelection: true,
        rowSelector: '0px',
        escapeHTMLInData: false,
        costData: null,
        borderContainerWidget: null,
        constructor:function(args){
            this.inherited(arguments);
            this.createHeaderCtxMenu();
        },
        canSort: function(inSortInfo){
            return false;
        },
        postCreate: function(){
            this.inherited(arguments);
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
        dodblclick: function(e){
            this.onRowDblClick(e);
        },
        focusCell: function(rowIdx, inAttrName){
            var self = this;
            var cell = self.getCellByField(inAttrName);
            window.setTimeout(function() {
                self.focus.setFocusIndex(rowIdx, cell.index);
            }, 10);
        },
        updateRecord: function(val, rowIdx, inAttrName){
            var self = this, item = self.getItem(rowIdx), store = self.store;

            var pb = buildspace.dialog.indeterminateProgressBar({
                title:nls.savingData+'. '+nls.pleaseWait+'...'
            });
            var params = {
                id: item.id,
                attr_name: inAttrName,
                val: val,
                cost_data_id: self.costData.id,
                item_id: item.id,
                _csrf_token: item._csrf_token ? item._csrf_token : null
            };

            var updateCell = function(data, store){
                for(var property in data){
                    if(item.hasOwnProperty(property) && property != store._getIdentifierAttribute()){
                        store.setValue(item, property, data[property]);
                    }
                }
                store.save();
            };

            var xhrArgs = {
                url: 'costData/updateItem',
                content: params,
                handleAs: 'json',
                load: function(resp) {
                    if(resp.success){
                        if(item.id > 0){
                            updateCell(resp.data, store);
                        }
                        self.focusCell(rowIdx, inAttrName);
                    }
                    pb.hide();
                },
                error: function(error) {
                    pb.hide();
                }
            };

            pb.show();
            dojo.xhrPost(xhrArgs);
        },
        updateNodelessItemRemarks: function(val, rowIdx, type){
            var self = this, item = self.getItem(rowIdx), store = self.store;

            var pb = buildspace.dialog.indeterminateProgressBar({
                title:nls.savingData+'. '+nls.pleaseWait+'...'
            });
            var params = {
                cost_data_id: self.costData.id,
                val: val,
                type: type,
                _csrf_token: item._csrf_token ? item._csrf_token : null
            };

            var updateCell = function(data, store){
                for(var property in data){
                    if(item.hasOwnProperty(property) && property != store._getIdentifierAttribute()){
                        store.setValue(item, property, data[property]);
                    }
                }
                store.save();
            };

            var xhrArgs = {
                url: 'costData/updateNodelessItemRemarks',
                content: params,
                handleAs: 'json',
                load: function(resp) {
                    if(resp.success){
                        updateCell(resp.data, store);
                        self.focusCell(rowIdx, 'remarks');
                    }
                    pb.hide();
                },
                error: function(error) {
                    pb.hide();
                }
            };

            pb.show();
            dojo.xhrPost(xhrArgs);
        },
        doApplyCellEdit: function(val, rowIdx, inAttrName){
            var self = this, item = self.getItem(rowIdx), store = self.store;

            var originalValue = item[inAttrName][0];

            if(val !== item[inAttrName][0]){
                if((inAttrName == 'approved_cost' && item.approved_cost_derived[0]) || (inAttrName == 'awarded_cost' && item.awarded_cost_derived[0]) || (inAttrName == 'variation_order_cost' && item.variation_order_cost_derived[0])){
                    buildspace.dialog.confirm(nls.confirmation,nls.itemBreakdownWillBeLost+'<br/>'+nls.cannotBeUndone,60,280, function(){
                        self.updateRecord(val, rowIdx, inAttrName);
                    }, function(){
                        store.setValue(item, inAttrName, originalValue);
                        store.save();
                        self.focusCell(rowIdx, inAttrName);
                    });
                }
                else if (item.id[0] > 0){
                    self.updateRecord(val, rowIdx, inAttrName);
                }
                else if (inAttrName == 'remarks' && (item.type[0] == buildspace.apps.CostData.ItemTypes.PROVISIONAL_SUM || buildspace.apps.CostData.ItemTypes.PRIME_COST_SUM || buildspace.apps.CostData.ItemTypes.PRIME_COST_RATE))
                {
                    self.updateNodelessItemRemarks(val, rowIdx, item.type[0]);
                }
            }

            self.inherited(arguments);
        },
        canEdit: function(inCell, inRowIndex){
            if(inCell != undefined) {
                var item = this.getItem(inRowIndex);
                if(item.id[0] > 0)
                {
                    return true;
                }
                else if (inCell.field == 'remarks' && (item.type[0] == buildspace.apps.CostData.ItemTypes.PROVISIONAL_SUM || item.type[0] == buildspace.apps.CostData.ItemTypes.PRIME_COST_SUM || item.type[0] == buildspace.apps.CostData.ItemTypes.PRIME_COST_RATE))
                {
                    return true;
                }

                return false;
            }

            return this._canEdit;
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
                            case 'adjusted_cost':
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
        getCellIndexesByAttribute: function(attribute, value){
            var cellIndexes = [];
            dojo.forEach(this.layout.cells, function(data, index){
                if(data[attribute] && data[attribute] == value) cellIndexes.push(index);
            });
            return cellIndexes;
        },
        showHideColumn: function(show, index, field) {
            this.beginUpdate();

            var columnIndexes = [];

            switch(field){
                case 'approved_cost':
                    columnIndexes = this.getCellIndexesByAttribute('showHideGroup', 'show_hide_group_approved');
                    break;
                case 'awarded_cost':
                    columnIndexes = this.getCellIndexesByAttribute('showHideGroup', 'show_hide_group_awarded');
                    break;
                case 'adjusted_cost':
                    columnIndexes = this.getCellIndexesByAttribute('showHideGroup', 'show_hide_group_adjusted');
                    break;
            }
            columnIndexes.push(index);
            for(var i in columnIndexes){
                this.layout.setColumnVisibility(columnIndexes[i], show);
            }
            this.endUpdate();
        },
        reload: function(){
            this.store.close();
            this._refresh();
            this.setStructure(this.borderContainerWidget.getBreakdownGridStructure());
        }
    });

    var Formatter = {
        rowCountCellFormatter: function(cellValue, rowIdx, cell){
            var item = this.grid.getItem(rowIdx);
            if(item.id[0] == 'row_separator') cell.customClasses.push('invalidTypeItemCell');
            if(item.type == 'summary'){
                cell.customClasses.push('disable-cell');
            }
            if(!((item.id[0] > 0) || (item.id[0] == buildspace.constants.GRID_LAST_ROW))) return null;
            return parseInt(rowIdx)-3;
        },
        descriptionFormatter: function(cellValue, rowIdx, cell){
            var item = this.grid.getItem(rowIdx);
            if(item.id[0] == 'row_separator') cell.customClasses.push('invalidTypeItemCell');
            if(item.type == 'summary'){
                cell.customClasses.push('disable-cell');
                cell.customClasses.push('pull-right');
                cellValue = '<strong>' + cellValue + '</strong>';
            }
            return cellValue;
        },
        remarksFormatter: function(cellValue, rowIdx, cell){
            var item = this.grid.getItem(rowIdx);
            if(item.id[0] == 'row_separator') cell.customClasses.push('invalidTypeItemCell');
            if(item.type == 'summary'){
                cell.customClasses.push('disable-cell');
            }
            if(!cellValue) cellValue = '&nbsp;';

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

            if (item.type != undefined && item.type < 1){
                cell.customClasses.push('invalidTypeItemCell');
            }else{
                cell.customClasses.push('disable-cell');
            }

            return cellValue;
        },
        unEditablePercentageCellFormatter: function(cellValue, rowIdx, cell){
            var value = number.parse(cellValue);
            var item = this.grid.getItem(rowIdx);

            if (item.type != undefined && item.type < 1){
                cell.customClasses.push('invalidTypeItemCell');
            }else{
                cell.customClasses.push('disable-cell');
            }

            if(isNaN(value) || value == 0 || value == null){
                cellValue = "&nbsp;";
            }else{
                cellValue = number.format(value, {places:2})+"%";
            }
            return '<span style="color: blue;">' + cellValue + '</span>';
        },
        amountFormatter: function(cellValue, rowIdx, cell){
            var item = this.grid.getItem(rowIdx);

            if(item.id[0] == 'row_separator') cell.customClasses.push('invalidTypeItemCell');

            if(item.type == 'summary'){
                cell.customClasses.push('disable-cell');
            }

            if((item.id > 0 || item.id == 'provisional_sum' || item.type == 'summary') && cellValue != 0 && cellValue && !isNaN(cellValue)){
                cellValue = currency.format(cellValue);
            }
            else{
                cellValue = '&nbsp;';
            }

            var derived = false;
            if( this.field == 'approved_cost' && item.approved_cost_derived && item.approved_cost_derived[ 0 ] ) {
                derived = true;
            }
            else if( this.field == 'awarded_cost' && item.awarded_cost_derived && item.awarded_cost_derived[ 0 ] ) {
                derived = true;
            }
            else if( this.field == 'variation_order_cost' && item.variation_order_cost_derived && item.variation_order_cost_derived[ 0 ] ) {
                derived = true;
            }
            if( derived ){
                cellValue = '<span style="color: blue;"><strong>' + cellValue + '</strong></span>';
                cell.customClasses.push('light-blue-cell');
            }

            return cellValue;
        }
    };

    return declare('buildspace.apps.CostData.BreakdownContainer', dijit.layout.BorderContainer, {
        region: "center",
        style:"padding:0px;border:none;width:100%;height:100%;",
        gutters: false,
        costData: null,
        grid: null,
        editable: false,
        storeUrl: null,
        costComparisonProjectParticulars: [],
        postCreate: function(){
            this.inherited(arguments);

            var self = this;
            var pb = buildspace.dialog.indeterminateProgressBar({
                title:nls.pleaseWait+'...'
            });

            var xhrArgs = {
                url: 'costData/getCostComparisonProjectParticulars',
                content: { cost_data_id: this.costData.id },
                handleAs: 'json',
                load: function(resp) {
                    if(resp.success){
                        self.costComparisonProjectParticulars = resp.data;

                        self.storeUrl = "costData/getBreakdown/id/"+self.costData.id;

                        var stackContainer = self.createStackContainer();

                        var grid = self.createBreakdownGrid();

                        var gridContainer = new dijit.layout.ContentPane( {
                            style:"padding:0px;border:none;width:100%;height:100%;",
                            region: 'center',
                            content: grid,
                            grid: grid
                        });

                        var child = new dijit.layout.BorderContainer({
                            style: "padding:0px;width:100%;height:100%;",
                            gutters: false,
                            grid: grid,
                            title: buildspace.truncateString(nls.overallProjectCosting, 60)
                        });

                        child.addChild(self.createToolbar());
                        child.addChild(gridContainer);

                        stackContainer.addChild(child);
                    }
                    pb.hide();
                },
                error: function(error) {
                    pb.hide();
                }
            };

            pb.show();
            dojo.xhrPost(xhrArgs);
        },
        createStackContainer: function(){
            var self = this;
            var stackContainerId = 'costDataBreakdown' + self.costData.id + '-stackContainer';

            var stackContainer = self.stackContainer = new dijit.layout.StackContainer({
                style: 'border:0px;width:100%;height:100%;',
                region: "center",
                id: stackContainerId
            });

            dojo.subscribe(stackContainerId+'-selectChild', "", function(page) {
                var widget = dijit.byId(stackContainerId);
                if(widget) {
                    var children = widget.getChildren(),
                        index = dojo.indexOf(children, page);

                    index = index + 1;

                    if(children.length > index){
                        while(children.length > index) {
                            widget.removeChild(children[index]);
                            children[index].destroyDescendants();
                            children[index].destroyRecursive();

                            index = index + 1;
                        }
                    }

                    if(page.grid) page.grid.reload();
                }
            });

            var controller = new dijit.layout.StackController({
                region: "top",
                containerId: stackContainerId
            });

            var controllerPane = new dijit.layout.ContentPane({
                style: "padding:0px;overflow:hidden;",
                baseClass: 'breadCrumbTrail',
                region: 'top',
                id: 'costDataBreakdown'+self.costData.id+'-controllerPane',
                content: controller
            });

            self.addChild(stackContainer);
            self.addChild(controllerPane);

            return stackContainer;
        },
        createBreakdownGrid: function(){
            var self = this;
            self.grid = Grid({
                id: 'costData-breakdownGrid',
                costData: self.costData,
                borderContainerWidget: self,
                structure: this.getBreakdownGridStructure(),
                onRowDblClick: function(e) {
                    var me = this,
                        item = me.getItem(e.rowIndex);
                    switch(item.type[0]){
                        case buildspace.apps.CostData.ItemTypes.STANDARD:
                            if(item.id[0] > 0) self.createWorkCategoryGrid(item);
                            break;
                        case buildspace.apps.CostData.ItemTypes.PROVISIONAL_SUM:
                            self.createProvisionalSumGrid();
                            break;
                        case buildspace.apps.CostData.ItemTypes.PRIME_COST_SUM:
                            self.createPrimeCostSumGrid();
                            break;
                        case buildspace.apps.CostData.ItemTypes.PRIME_COST_RATE:
                            self.createPrimeCostRateGrid(item, 1);
                            break;
                    }
                },
                store: new dojo.data.ItemFileWriteStore({
                    clearOnClose:true,
                    url:this.storeUrl
                })
            });

            return self.grid;
        },
        getBreakdownGridStructure: function(){
            var columnsPerSumGroup = 2 + this.costComparisonProjectParticulars.length;
            var firstRowColumns = [{
                    name: 'No.',
                    field: 'count',
                    width:'30px',
                    rowSpan: 2,
                    styles:'text-align:center;',
                    formatter: Formatter.rowCountCellFormatter
                },{
                    name: nls.description,
                    field: 'description',
                    width:'500px',
                    rowSpan: 2,
                    formatter: Formatter.descriptionFormatter
                },{
                    name: nls.amount,
                    field: 'approved_cost',
                    showHideGroup: 'show_hide_group_approved',
                    width:'150px', styles:'text-align: right;',
                    cellType: 'buildspace.widget.grid.cells.Textarea',
                    editable: this.editable,
                    showInCtxMenu: true,
                    hidden:getCookieBoolean('CostData.hiddenColumns.approved_cost'),
                    formatter : Formatter.amountFormatter
                }];

                for(var i in this.costComparisonProjectParticulars)
                {
                    firstRowColumns.push({
                        name: nls.cost+'/'+((this.costComparisonProjectParticulars[i]['uom_symbol'].trim().length === 0) ? nls.unit : this.costComparisonProjectParticulars[i]['uom_symbol'].trim()),
                        field: 'approved-'+this.costComparisonProjectParticulars[i]['id'],
                        showHideGroup: 'show_hide_group_approved',
                        width:'120px',
                        styles:'text-align:right;',
                        hidden:getCookieBoolean('CostData.hiddenColumns.approved_cost'),
                        formatter: Formatter.unEditableCurrencyCellFormatter
                    });
                }

                firstRowColumns = firstRowColumns.concat([{
                    name: nls.percentageOfTotalCost,
                    field: 'percentage_of_approved_sum',
                    showHideGroup: 'show_hide_group_approved',
                    width:'150px', styles:'text-align: right;',
                    hidden:getCookieBoolean('CostData.hiddenColumns.approved_cost'),
                    formatter : Formatter.unEditablePercentageCellFormatter
                },{
                    name: nls.amount,
                    field: 'awarded_cost',
                    showHideGroup: 'show_hide_group_awarded',
                    width:'150px', styles:'text-align: right;',
                    cellType: 'buildspace.widget.grid.cells.Textarea',
                    editable: this.editable,
                    showInCtxMenu: true,
                    hidden:getCookieBoolean('CostData.hiddenColumns.awarded_cost'),
                    formatter : Formatter.amountFormatter
                }]);

                for(var i in this.costComparisonProjectParticulars)
                {
                    firstRowColumns.push({
                        name: nls.cost+'/'+((this.costComparisonProjectParticulars[i]['uom_symbol'].trim().length === 0) ? nls.unit : this.costComparisonProjectParticulars[i]['uom_symbol'].trim()),
                        field: 'awarded-'+this.costComparisonProjectParticulars[i]['id'],
                        showHideGroup: 'show_hide_group_awarded',
                        width:'120px',
                        styles:'text-align:right;',
                        hidden:getCookieBoolean('CostData.hiddenColumns.awarded_cost'),
                        formatter: Formatter.unEditableCurrencyCellFormatter
                    });
                }

                firstRowColumns = firstRowColumns.concat([{
                    name: nls.percentageOfTotalCost,
                    field: 'percentage_of_awarded_sum',
                    showHideGroup: 'show_hide_group_awarded',
                    width:'150px', styles:'text-align: right;',
                    hidden:getCookieBoolean('CostData.hiddenColumns.awarded_cost'),
                    formatter : Formatter.unEditablePercentageCellFormatter
                },{
                    name: nls.amount,
                    field: 'adjusted_cost',
                    showHideGroup: 'show_hide_group_adjusted',
                    width:'140px',
                    styles:'text-align:right;',
                    showInCtxMenu: true,
                    hidden:getCookieBoolean('CostData.hiddenColumns.adjusted_cost'),
                    formatter: Formatter.unEditableCurrencyCellFormatter
                }]);

                for(var i in this.costComparisonProjectParticulars)
                {
                    firstRowColumns.push({
                        name: nls.cost+'/'+((this.costComparisonProjectParticulars[i]['uom_symbol'].trim().length === 0) ? nls.unit : this.costComparisonProjectParticulars[i]['uom_symbol'].trim()),
                        field: 'adjusted-'+this.costComparisonProjectParticulars[i]['id'],
                        showHideGroup: 'show_hide_group_adjusted',
                        width:'120px',
                        styles:'text-align:right;',
                        hidden:getCookieBoolean('CostData.hiddenColumns.adjusted_cost'),
                        formatter: Formatter.unEditableCurrencyCellFormatter
                    });
                }

                firstRowColumns = firstRowColumns.concat([{
                    name: nls.percentageOfTotalCost,
                    field: 'percentage_of_adjusted_sum',
                    showHideGroup: 'show_hide_group_adjusted',
                    width:'140px',
                    styles:'text-align:right;',
                    hidden:getCookieBoolean('CostData.hiddenColumns.adjusted_cost'),
                    formatter: Formatter.unEditablePercentageCellFormatter
                },{
                    name: nls.variationOrderCost,
                    field: 'variation_order_cost',
                    width:'140px',
                    rowSpan: 2,
                    cellType: 'buildspace.widget.grid.cells.Textarea',
                    editable: this.editable,
                    styles:'text-align:right;',
                    showInCtxMenu: true,
                    hidden:getCookieBoolean('CostData.hiddenColumns.variation_order_cost'),
                    formatter: Formatter.amountFormatter
                },{
                    name: nls.updatedBy,
                    field: 'updated_by',
                    styles:'text-align: center;',
                    width:'140px',
                    rowSpan: 2,
                    showInCtxMenu: true,
                    hidden:getCookieBoolean('CostData.hiddenColumns.updated_by'),
                    formatter: Formatter.remarksFormatter
                },{
                    name: nls.updatedAt,
                    field: 'updated_at',
                    width:'120px',
                    styles:'text-align: center;',
                    rowSpan: 2,
                    showInCtxMenu: true,
                    hidden:getCookieBoolean('CostData.hiddenColumns.updated_at'),
                    formatter: Formatter.remarksFormatter
                },{
                    name: nls.remarks,
                    field: 'remarks',
                    width:'300px',
                    rowSpan: 2,
                    cellType: 'buildspace.widget.grid.cells.Textarea',
                    editable: this.editable,
                    showInCtxMenu: true,
                    hidden:getCookieBoolean('CostData.hiddenColumns.remarks'),
                    formatter: Formatter.remarksFormatter
                }]);
            return {
                noscroll: false,
                cells: [
                    firstRowColumns,
                    [{
                        name: this.costData.approved_date ? nls.budget+' ('+this.costData.approved_date+')' : nls.budget,
                        showHideGroup: 'show_hide_group_approved',
                        hidden:getCookieBoolean('CostData.hiddenColumns.approved_cost'),
                        styles:'text-align:center;',
                        headerClasses: "staticHeader",
                        colSpan : columnsPerSumGroup
                    },{
                        name: this.costData.awarded_date ? nls.contractSum+' ('+this.costData.awarded_date+')' : nls.contractSum,
                        showHideGroup: 'show_hide_group_awarded',
                        hidden:getCookieBoolean('CostData.hiddenColumns.awarded_cost'),
                        styles:'text-align:center;',
                        headerClasses: "staticHeader",
                        colSpan : columnsPerSumGroup
                    },{
                        name: this.costData.adjusted_date ? nls.adjustedSum+' ('+this.costData.adjusted_date+')' : nls.adjustedSum,
                        showHideGroup: 'show_hide_group_adjusted',
                        hidden:getCookieBoolean('CostData.hiddenColumns.adjusted_cost'),
                        styles:'text-align:center;',
                        headerClasses: "staticHeader",
                        colSpan : columnsPerSumGroup
                    }]
                ]
            };
        },
        createToolbar: function(){
            var self = this;
            var toolbar = new dijit.Toolbar({
                region: "top",
                style: "outline:none!important;border:none;padding:2px;width:100%;"
            });

            toolbar.addChild(
                new dijit.form.Button({
                    id: this.costData.id + 'project-overall-costing-refresh-button',
                    label: nls.refresh,
                    iconClass: "icon-16-container icon-16-reload",
                    style: "outline:none!important;",
                    onClick: function () {
                        self.grid.reload();
                    }
                })
            );

            toolbar.addChild(new dijit.ToolbarSeparator());

            console.log(self.costData);

            if(!self.costData.isEditor)
            {
                toolbar.addChild(
                    new dijit.form.Button({
                        label: nls.comparisonReport,
                        iconClass: "icon-16-container icon-16-print",
                        style: "outline:none!important;",
                        onClick: function () {
                            self.createComparisonReportDialog();
                        }
                    })
                );

                toolbar.addChild(new dijit.ToolbarSeparator());
            }

            toolbar.addChild(
                new dijit.form.Button({
                    label: nls.tenderComparisonReport,
                    iconClass: "icon-16-container icon-16-print",
                    style: "outline:none!important;",
                    onClick: function () {
                        self.createTenderComparisonReportDialog();
                    }
                })
            );

            if(self.editable){
                toolbar.addChild(new dijit.ToolbarSeparator());

                toolbar.addChild(
                    new dijit.form.Button({
                        id: this.costData.id + '-row-inclusion-button',
                        label: nls.showHideRows,
                        iconClass: "icon-16-container icon-16-dial",
                        style: "outline:none!important;",
                        onClick: function () {
                            self.createRowVisibilityDialog();
                        }
                    })
                );
            }

            return toolbar;
        },
        createComparisonReportDialog: function(){
            var self = this;

            var comparisonReportDialog = new ComparisonReportDialog({
                gridUrl: "costData/getCostDataList/costData/"+self.costData.id,
                title: nls.comparisonReport,
                exportUrl: "comparisonReport/export/costData/"+self.costData.id+'/parent_id/0/level/'+buildspace.apps.CostData.Levels.overallProjectCosting,
                costData: self.costData,
                level: buildspace.apps.CostData.Levels.overallProjectCosting,
                type: buildspace.apps.CostData.ItemTypes.STANDARD
            });

            comparisonReportDialog.show();
        },
        createTenderComparisonReportDialog: function(){
            var self = this;

            var tenderComparisonReportDialog = new TenderComparisonReportDialog({
                exportUrl: "tenderComparison/export/costData/"+self.costData.id+'/level/'+buildspace.apps.CostData.Levels.overallProjectCosting,
                costData: self.costData,
                level: buildspace.apps.CostData.Levels.overallProjectCosting
            });

            tenderComparisonReportDialog.show();
        },
        createRowVisibilityDialog: function(){
            var self = this;

            var selectedItemIds = [];
            dojo.forEach(self.grid.store._arrayOfAllItems, function(item, index){
                if(item.id[0] > 0) selectedItemIds.push(item.id[0]);
            });

            var rowInclusionDialog = new RowInclusionDialog({
                title: nls.showHideRows,
                gridUrl: "costData/getAllCostDataItemRecords/costData/"+self.costData.id+'/parent_id/0',
                saveUrl: "costData/updateItemVisibility/costData/"+self.costData.id+'/_csrf_token/'+self.costData._csrf_token,
                selectedItemIds: selectedItemIds,
                deselectedItemIds: [],
                onSave:function(){
                    self.grid.reload();
                }
            });

            rowInclusionDialog.show();
        },
        createWorkCategoryGrid: function(item){
            var workCategoryGrid = new WorkCategoryGrid({
                title: buildspace.truncateString(item.description, 60),
                stackContainer: this.stackContainer,
                editable: this.editable,
                costData: this.costData,
                projectCostingItem: item
            });

            this.stackContainer.addChild(workCategoryGrid);
            this.stackContainer.selectChild(workCategoryGrid);
        },
        reloadPrimeCostSumGrid: function(){
            this.stackContainer.removeChild(this.primeCostSumContainer);
            this.primeCostSumContainer.destroyDescendants();
            this.primeCostSumContainer.destroyRecursive();
            this.createPrimeCostSumGrid();
        },
        createPrimeCostSumGrid: function(){
            var primeCostSumContainer = this.primeCostSumContainer = new PrimeCostSumWorkArea({
                title: buildspace.truncateString(nls.primeCostSum, 60),
                stackContainer: this.stackContainer,
                editable: this.editable,
                costData: this.costData,
                mainBreakdownGrid: this
            });

            this.stackContainer.addChild(primeCostSumContainer);
            this.stackContainer.selectChild(primeCostSumContainer);
        },
        createProvisionalSumGrid: function(){
            var provisionalSumGrid = new ProvisionalSumGrid({
                title: buildspace.truncateString(nls.provisionalSum, 60),
                stackContainer: this.stackContainer,
                editable: this.editable,
                costData: this.costData
            });

            this.stackContainer.addChild(provisionalSumGrid);
            this.stackContainer.selectChild(provisionalSumGrid);
        },
        createPrimeCostRateGrid: function(item, level){
            if(!(item.id > 0)) item.id = 0;
            var primeCostRateGrid = new PrimeCostRateGrid({
                title: buildspace.truncateString(item.description, 60),
                editable: this.editable,
                costData: this.costData,
                gridCreator: this,
                parentItem: item,
                level: level
            });

            this.stackContainer.addChild(primeCostRateGrid);
            this.stackContainer.selectChild(primeCostRateGrid);
        }
    });
});