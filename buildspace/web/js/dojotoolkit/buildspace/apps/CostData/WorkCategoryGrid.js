define('buildspace/apps/CostData/WorkCategoryGrid',[
    'dojo/_base/declare',
    'dojo/number',
    'dojo/currency',
    'dojox/grid/EnhancedGrid',
    'buildspace/widget/grid/cells/Formatter',
    './ElementGrid',
    './ComparisonReport/ComparisonReportDialog',
    './TenderComparisonReport/TenderComparisonReportDialog',
    './RowInclusion/RowInclusionDialog',
    'dojo/i18n!buildspace/nls/CostData'
], function(declare, number, currency, EnhancedGrid, GridFormatter, ElementGrid, ComparisonReportDialog, TenderComparisonReportDialog, RowInclusionDialog, nls){

    var Grid = declare('buildspace.apps.CostData.WorkCategoryGrid', EnhancedGrid, {
        style: "border:none;",
        region: 'center',
        keepSelection: true,
        rowSelector: '0px',
        escapeHTMLInData: false,
        costData: null,
        projectCostingItem: null,
        container: null,
        addUrl: 'costData/addNewWorkCategory',
        updateUrl: 'costData/updateItem',
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
                        dojo.forEach(self.container.stackContainer.getChildren(), function(data, index){
                            if(data.grid.id == self.id) return;
                            if(data.grid) data.grid.reload();
                        });
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
                else{
                    self.updateRecord(val, rowIdx, inAttrName);
                }
            }

            self.inherited(arguments);
        },
        canEdit: function(inCell, inRowIndex){
            if(inCell != undefined) {
                var item = this.getItem(inRowIndex);
                return item.id[0] > 0;
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
            this.setStructure(this.container.getGridStructure());
        }
    });

    var Formatter = {
        rowCountCellFormatter: function(cellValue, rowIdx, cell){
            var item = this.grid.getItem(rowIdx);
            if(item.type == 'summary'){
                cell.customClasses.push('disable-cell');
                return null;
            }
            return parseInt(rowIdx)+1;
        },
        descriptionFormatter: function(cellValue, rowIdx, cell){
            var item = this.grid.getItem(rowIdx);
            if(item.type == 'summary'){
                cell.customClasses.push('disable-cell');
                cell.customClasses.push('pull-right');
                cellValue = '<strong>' + cellValue + '</strong>';
            }
            return cellValue;
        },
        remarksFormatter: function(cellValue, rowIdx, cell){
            var item = this.grid.getItem(rowIdx);
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

            cell.customClasses.push('disable-cell');

            return cellValue;
        },
        unEditablePercentageCellFormatter: function(cellValue, rowIdx, cell){
            var value = number.parse(cellValue);

            cell.customClasses.push('disable-cell');

            if(isNaN(value) || value == 0 || value == null){
                cellValue = "&nbsp;";
            }else{
                cellValue = number.format(value, {places:2})+"%";
            }
            return '<span style="color: blue;">' + cellValue + '</span>';
        },
        amountFormatter: function(cellValue, rowIdx, cell){
            var item = this.grid.getItem(rowIdx);
            var value = number.parse(cellValue);

            if(item.type == 'summary'){
                cell.customClasses.push('disable-cell');
            }

            if(isNaN(value) || value == 0 || value == null){
                cellValue = "&nbsp;";
            }else{
                cellValue = currency.format(cellValue);
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
        },
        unEditableAmountCellFormatter: function(cellValue, rowIdx, cell){
            var value = number.parse(cellValue);

            cell.customClasses.push('disable-cell');

            if(isNaN(value) || value == 0 || value == null){
                cellValue = "&nbsp;";
            }else{
                cellValue = currency.format(cellValue);
            }

            return cellValue;
        }
    };

    return declare('buildspace.apps.CostData.WorkCategoriesContainer', dijit.layout.BorderContainer, {
        region: "center",
        title: '',
        style:"padding:0px;border:none;width:100%;height:100%;",
        gutters: false,
        costData: null,
        projectCostingItem: null,
        projectParticulars: [],
        grid: null,
        stackContainer: null,
        hideColumnGroup: [],
        editable: false,
        postCreate: function(){
            this.inherited(arguments);

            var self = this;
            var pb = buildspace.dialog.indeterminateProgressBar({
                title:nls.pleaseWait+'...'
            });

            var xhrArgs = {
                url: 'costData/getWorkCategoryParticulars',
                content: { cost_data_id: this.costData.id, id: this.projectCostingItem.id },
                handleAs: 'json',
                load: function(resp) {
                    if(resp.success){
                        self.projectParticulars = resp.data;

                        var grid = self.createBreakdownGrid();

                        var gridContainer = new dijit.layout.ContentPane( {
                            style:"padding:0px;border:none;width:100%;height:100%;",
                            region: 'center',
                            content: grid,
                            grid: grid
                        });

                        self.addChild(self.createToolbar());
                        self.addChild(gridContainer);

                        pb.hide();
                    }
                },
                error: function(error) {
                    pb.hide();
                }
            };

            pb.show();
            dojo.xhrPost(xhrArgs);
        },
        createToolbar: function(){
            var self = this;
            var toolbar = new dijit.Toolbar({
                region: "top",
                style: "outline:none!important;border:none;padding:2px;width:100%;"
            });

            toolbar.addChild(
                new dijit.form.Button({
                    id: this.costData.id + 'work-category-refresh-button',
                    label: nls.refresh,
                    iconClass: "icon-16-container icon-16-reload",
                    style: "outline:none!important;",
                    onClick: function () {
                        self.grid.reload();
                    }
                })
            );

            toolbar.addChild(new dijit.ToolbarSeparator());

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
                        id: this.costData.id + '-work-category-row-inclusion-button',
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
        getGridStructure: function(){
            var gridFormatter = new GridFormatter;

            var firstRowHeaders = [{
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
            }];

            var firstRowHeaderApproved = [];
            var firstRowHeaderAwarded = [];
            var firstRowHeaderAdjusted = [];
            var secondRowHeaders = [];
            var variableColumnNumber = 2 + this.projectParticulars.length;
            for(var index in this.projectParticulars) {
                firstRowHeaders.push({
                    name: this.projectParticulars[index]['description'],
                    field: 'column-'+this.projectParticulars[index]['id'],
                    width:'120px',
                    styles:'text-align:right;',
                    rowSpan: 2,
                    formatter: Formatter.unEditableAmountCellFormatter
                });
                firstRowHeaderApproved.push({
                    name: nls.cost+"/"+this.projectParticulars[index]['description'],
                    field: 'approved_column-'+this.projectParticulars[index]['id'],
                    showHideGroup: 'show_hide_group_approved',
                    width:'120px',
                    styles:'text-align:right;',
                    hidden:getCookieBoolean('CostData.hiddenColumns.approved_cost'),
                    formatter: Formatter.unEditableAmountCellFormatter
                });
                firstRowHeaderAwarded.push({
                    name: nls.cost+"/"+this.projectParticulars[index]['description'],
                    field: 'awarded_column-'+this.projectParticulars[index]['id'],
                    width:'120px',
                    styles:'text-align:right;',
                    showHideGroup: 'show_hide_group_awarded',
                    hidden:getCookieBoolean('CostData.hiddenColumns.awarded_cost'),
                    formatter: Formatter.unEditableAmountCellFormatter
                });
                firstRowHeaderAdjusted.push({
                    name: nls.cost+"/"+this.projectParticulars[index]['description'],
                    field: 'adjusted_column-'+this.projectParticulars[index]['id'],
                    width:'120px',
                    styles:'text-align:right;',
                    showHideGroup: 'show_hide_group_adjusted',
                    hidden:getCookieBoolean('CostData.hiddenColumns.adjusted_cost'),
                    formatter: Formatter.unEditableAmountCellFormatter
                });
            }

            firstRowHeaders.push({
                name: nls.amount,
                field: 'approved_cost',
                width:'140px',
                styles:'text-align:right;',
                editable: this.editable,
                showInCtxMenu: true,
                showHideGroup: 'show_hide_group_approved',
                hidden:getCookieBoolean('CostData.hiddenColumns.approved_cost'),
                cellType: 'buildspace.widget.grid.cells.Textarea',
                formatter: Formatter.amountFormatter
            });
            firstRowHeaders = firstRowHeaders.concat(firstRowHeaderApproved);
            firstRowHeaders.push({
                name: nls.percentage,
                field: 'approved_percentage',
                width:'70px',
                styles:'text-align:right;',
                showHideGroup: 'show_hide_group_approved',
                hidden:getCookieBoolean('CostData.hiddenColumns.approved_cost'),
                formatter: Formatter.unEditablePercentageCellFormatter
            });

            firstRowHeaders.push({
                name: nls.amount,
                field: 'awarded_cost',
                width:'140px',
                styles:'text-align:right;',
                editable: this.editable,
                showInCtxMenu: true,
                showHideGroup: 'show_hide_group_awarded',
                hidden:getCookieBoolean('CostData.hiddenColumns.awarded_cost'),
                cellType: 'buildspace.widget.grid.cells.Textarea',
                formatter: Formatter.amountFormatter
            });
            firstRowHeaders = firstRowHeaders.concat(firstRowHeaderAwarded);
            firstRowHeaders.push({
                name: nls.percentage,
                field: 'awarded_percentage',
                width:'70px',
                styles:'text-align:right;',
                showHideGroup: 'show_hide_group_awarded',
                hidden:getCookieBoolean('CostData.hiddenColumns.awarded_cost'),
                formatter: Formatter.unEditablePercentageCellFormatter
            });

            firstRowHeaders.push({
                name: nls.amount,
                field: 'adjusted_cost',
                width:'140px',
                styles:'text-align:right;',
                showInCtxMenu: true,
                showHideGroup: 'show_hide_group_adjusted',
                hidden:getCookieBoolean('CostData.hiddenColumns.adjusted_cost'),
                formatter: Formatter.unEditableCurrencyCellFormatter
            });
            firstRowHeaders = firstRowHeaders.concat(firstRowHeaderAdjusted);
            firstRowHeaders.push({
                name: nls.percentage,
                field: 'adjusted_percentage',
                width:'70px',
                styles:'text-align:right;',
                showHideGroup: 'show_hide_group_adjusted',
                hidden:getCookieBoolean('CostData.hiddenColumns.adjusted_cost'),
                formatter: Formatter.unEditablePercentageCellFormatter
            });

            firstRowHeaders.push({
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
            });

            secondRowHeaders = secondRowHeaders.concat([{
                name: this.costData.approved_date ? nls.budget+' ('+this.costData.approved_date+')' : nls.budget,
                styles:'text-align:center;',
                headerClasses: "staticHeader",
                showHideGroup: 'show_hide_group_approved',
                hidden:getCookieBoolean('CostData.hiddenColumns.approved_cost'),
                colSpan : variableColumnNumber
            },{
                name: this.costData.awarded_date ? nls.contractSum+' ('+this.costData.awarded_date+')' : nls.contractSum,
                styles:'text-align:center;',
                headerClasses: "staticHeader",
                showHideGroup: 'show_hide_group_awarded',
                hidden:getCookieBoolean('CostData.hiddenColumns.awarded_cost'),
                colSpan : variableColumnNumber
            },{
                name: this.costData.adjusted_date ? nls.adjustedSum+' ('+this.costData.adjusted_date+')' : nls.adjustedSum,
                styles:'text-align:center;',
                headerClasses: "staticHeader",
                showHideGroup: 'show_hide_group_adjusted',
                hidden:getCookieBoolean('CostData.hiddenColumns.adjusted_cost'),
                colSpan : variableColumnNumber
            }]);

            return {
                noscroll: false,
                cells: [
                    firstRowHeaders,
                    secondRowHeaders
                ]
            };
        },
        createBreakdownGrid: function(){
            var self = this;
            self.grid = Grid({
                id: 'costData-workCategoryGrid',
                costData: self.costData,
                projectCostingItem: self.projectCostingItem,
                container: self,
                structure: self.getGridStructure(),
                onRowDblClick: function(e) {
                    var me = this,
                        item = me.getItem(e.rowIndex);

                    if(item.id[0] > 0) self.createElementGrid(item);
                },
                store: new dojo.data.ItemFileWriteStore({
                    clearOnClose:true,
                    url:"costData/getWorkCategoryList/costData/"+this.costData.id+"/id/"+this.projectCostingItem.id
                })
            });

            return self.grid;
        },
        createComparisonReportDialog: function(){
            var self = this;

            var comparisonReportDialog = new ComparisonReportDialog({
                gridUrl: "costData/getCostDataList/costData/"+self.costData.id,
                title: nls.comparisonReport,
                exportUrl: "comparisonReport/export/costData/"+self.costData.id+'/parent_id/'+this.projectCostingItem.id+'/level/'+buildspace.apps.CostData.Levels.workCategory+'/_csrf_token/'+self.costData._csrf_token,
                costData: self.costData,
                level: buildspace.apps.CostData.Levels.workCategory,
                parentItemId: this.projectCostingItem.id,
                type: buildspace.apps.CostData.ItemTypes.STANDARD
            });

            comparisonReportDialog.show();
        },
        createTenderComparisonReportDialog: function(){
            var self = this;

            var tenderComparisonReportDialog = new TenderComparisonReportDialog({
                exportUrl: "tenderComparison/export/costData/"+self.costData.id+'/parent_id/'+this.projectCostingItem.id+'/level/'+buildspace.apps.CostData.Levels.workCategory,
                costData: self.costData,
                parentItemId: this.projectCostingItem.id,
                level: buildspace.apps.CostData.Levels.workCategory,
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
                gridUrl: "costData/getAllCostDataItemRecords/costData/"+self.costData.id+'/parent_id/'+self.projectCostingItem.id,
                saveUrl: "costData/updateItemVisibility/costData/"+self.costData.id+'/_csrf_token/'+self.projectCostingItem._csrf_token,
                selectedItemIds: selectedItemIds,
                deselectedItemIds: [],
                onSave:function(){
                    self.grid.reload();
                }
            });

            rowInclusionDialog.show();
        },
        createElementGrid: function(item){
            var elementGrid = new ElementGrid({
                title: buildspace.truncateString(item.description, 60),
                stackContainer: this.stackContainer,
                editable: this.editable,
                costData: this.costData,
                workCategory: item
            });

            this.stackContainer.addChild(elementGrid);
            this.stackContainer.selectChild(elementGrid);
        }
    });
});