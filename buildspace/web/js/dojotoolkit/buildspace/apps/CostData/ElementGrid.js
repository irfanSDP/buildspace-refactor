define('buildspace/apps/CostData/ElementGrid',[
    'dojo/_base/declare',
    'dojo/number',
    'dojo/currency',
    'dojox/grid/EnhancedGrid',
    'buildspace/widget/grid/cells/Textarea',
    './LinkBillItems/ProjectListingGrid',
    './LinkVariationOrderItems/ProjectListingGrid',
    './ComparisonReport/ComparisonReportDialog',
    './TenderComparisonReport/TenderComparisonReportDialog',
    './RowInclusion/RowInclusionDialog',
    'dojo/i18n!buildspace/nls/CostData'
], function(declare, number, currency, EnhancedGrid, Textarea, ProjectListingGrid, VariationOrderProjectListingGrid, ComparisonReportDialog, TenderComparisonReportDialog, RowInclusionDialog, nls){

    var Grid = declare('buildspace.apps.CostData.ElementGrid', EnhancedGrid, {
        style: "border:none;",
        region: 'center',
        keepSelection: true,
        rowSelector: '0px',
        escapeHTMLInData: false,
        costData: null,
        workCategory: null,
        container: null,
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
                cellValue = '<span style="color: #42b449;"><strong>' + cellValue + '</strong></span>';
                cell.customClasses.push('light-blue-cell');
            }

            return cellValue;
        }
    };

    return declare('buildspace.apps.CostData.ElementsContainer', dijit.layout.BorderContainer, {
        region: "center",
        title: '',
        style:"padding:0px;border:none;width:100%;height:100%;",
        gutters: false,
        costData: null,
        workCategory: null,
        costComparisonProjectParticulars: [],
        grid: null,
        stackContainer: null,
        editable: false,
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

                        var grid = self.createBreakdownGrid();

                        var gridContainer = new dijit.layout.ContentPane( {
                            style:"padding:0px;border:none;width:100%;height:100%;",
                            region: 'center',
                            content: grid,
                            grid: grid
                        });

                        self.addChild(self.createToolbar());
                        self.addChild(gridContainer);
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
        createToolbar: function(){
            var self = this;
            var toolbar = new dijit.Toolbar({
                region: "top",
                style: "outline:none!important;border:none;padding:2px;width:100%;"
            });

            toolbar.addChild(
                new dijit.form.Button({
                    id: this.costData.id + 'element-refresh-button',
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
                        id: this.costData.id + '-element-row-inclusion-button',
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
            var columnsPerSumGroup = 2 + this.costComparisonProjectParticulars.length;

            var firstRowColumns = [{
                name: 'No.',
                field: 'count',
                width:'30px',
                styles:'text-align:center;',
                rowSpan: 2,
                formatter: Formatter.rowCountCellFormatter
            },{
                name: nls.element,
                field: 'description',
                rowSpan: 2,
                width:'500px',
                formatter: Formatter.descriptionFormatter
            },{
                name: nls.elementalCost,
                field: 'approved_cost',
                showHideGroup: 'show_hide_group_approved',
                width:'140px',
                styles:'text-align:right;',
                cellType: 'buildspace.widget.grid.cells.Textarea',
                editable: this.editable,
                showInCtxMenu: true,
                hidden:getCookieBoolean('CostData.hiddenColumns.approved_cost'),
                formatter: Formatter.amountFormatter
            }];

            for(var i in this.costComparisonProjectParticulars)
            {
                firstRowColumns.push({
                    name: nls.elementalCost+'/'+((this.costComparisonProjectParticulars[i]['uom_symbol'].trim().length === 0) ? nls.unit : this.costComparisonProjectParticulars[i]['uom_symbol'].trim()),
                    field: 'approved-'+this.costComparisonProjectParticulars[i]['id'],
                    showHideGroup: 'show_hide_group_approved',
                    width:'120px',
                    styles:'text-align:right;',
                    hidden:getCookieBoolean('CostData.hiddenColumns.approved_cost'),
                    formatter: Formatter.unEditableCurrencyCellFormatter
                });
            }

            firstRowColumns = firstRowColumns.concat([{
                name: nls.percentage,
                field: 'approved_percentage',
                showHideGroup: 'show_hide_group_approved',
                width:'70px',
                styles:'text-align:right;',
                hidden:getCookieBoolean('CostData.hiddenColumns.approved_cost'),
                formatter: Formatter.unEditablePercentageCellFormatter
            },{
                name: nls.elementalCost,
                field: 'awarded_cost',
                showHideGroup: 'show_hide_group_awarded',
                width:'140px',
                styles:'text-align:right;',
                cellType: 'buildspace.widget.grid.cells.Textarea',
                editable: this.editable,
                showInCtxMenu: true,
                hidden:getCookieBoolean('CostData.hiddenColumns.awarded_cost'),
                formatter: Formatter.amountFormatter
            }]);

            for(var i in this.costComparisonProjectParticulars)
            {
                firstRowColumns.push({
                    name: nls.elementalCost+'/'+((this.costComparisonProjectParticulars[i]['uom_symbol'].trim().length === 0) ? nls.unit : this.costComparisonProjectParticulars[i]['uom_symbol'].trim()),
                    field: 'awarded-'+this.costComparisonProjectParticulars[i]['id'],
                    showHideGroup: 'show_hide_group_awarded',
                    width:'120px',
                    styles:'text-align:right;',
                    hidden:getCookieBoolean('CostData.hiddenColumns.awarded_cost'),
                    formatter: Formatter.unEditableCurrencyCellFormatter
                });
            }

            firstRowColumns = firstRowColumns.concat([{
                name: nls.percentage,
                field: 'awarded_percentage',
                showHideGroup: 'show_hide_group_awarded',
                width:'70px',
                styles:'text-align:right;',
                hidden:getCookieBoolean('CostData.hiddenColumns.awarded_cost'),
                formatter: Formatter.unEditablePercentageCellFormatter
            },{
                name: nls.elementalCost,
                field: 'adjusted_cost',
                showHideGroup: 'show_hide_group_adjusted',
                width:'140px',
                styles:'text-align:right;',
                showInCtxMenu: true,
                hidden:getCookieBoolean('CostData.hiddenColumns.approved_cost'),
                formatter: Formatter.unEditableCurrencyCellFormatter
            }]);

            for(var i in this.costComparisonProjectParticulars)
            {
                firstRowColumns.push({
                    name: nls.elementalCost+'/'+((this.costComparisonProjectParticulars[i]['uom_symbol'].trim().length === 0) ? nls.unit : this.costComparisonProjectParticulars[i]['uom_symbol'].trim()),
                    field: 'adjusted-'+this.costComparisonProjectParticulars[i]['id'],
                    showHideGroup: 'show_hide_group_adjusted',
                    width:'120px',
                    styles:'text-align:right;',
                    hidden:getCookieBoolean('CostData.hiddenColumns.adjusted_cost'),
                    formatter: Formatter.unEditableCurrencyCellFormatter
                });
            }

            firstRowColumns = firstRowColumns.concat([{
                name: nls.percentage,
                field: 'adjusted_percentage',
                showHideGroup: 'show_hide_group_adjusted',
                width:'70px',
                styles:'text-align:right;',
                hidden:getCookieBoolean('CostData.hiddenColumns.adjusted_cost'),
                formatter: Formatter.unEditablePercentageCellFormatter
            },{
                name: nls.variationOrderCost,
                field: 'variation_order_cost',
                width:'140px',
                styles:'text-align:right;',
                rowSpan: 2,
                cellType: 'buildspace.widget.grid.cells.Textarea',
                editable: this.editable,
                showInCtxMenu: true,
                hidden:getCookieBoolean('CostData.hiddenColumns.variation_order_cost'),
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
        createBreakdownGrid: function(){
            var self = this;
            self.grid = Grid({
                id: 'costData-elementGrid',
                costData: self.costData,
                workCategory: self.workCategory,
                container: self,
                structure: self.getGridStructure(),
                store: new dojo.data.ItemFileWriteStore({
                    clearOnClose:true,
                    url:"costData/getElementList/costData/"+this.costData.id+"/id/"+this.workCategory.id
                }),
                onRowDblClick: function(e) {
                    var me = this,
                        item = me.getItem(e.rowIndex);

                    if(item.id[0] > 0 && self.editable){
                        var colField = e.cell.field;

                        if(colField == 'awarded_cost'){
                            self.createLinkBillItemsContainer(item);
                        }

                        if(colField == 'variation_order_cost'){
                            self.createLinkVariationOrderItemsContainer(item);
                        }
                    }
                }
            });

            return self.grid;
        },
        createComparisonReportDialog: function(){
            var self = this;

            var comparisonReportDialog = new ComparisonReportDialog({
                gridUrl: "costData/getCostDataList/costData/"+self.costData.id,
                title: nls.comparisonReport,
                exportUrl: "comparisonReport/export/costData/"+self.costData.id+'/parent_id/'+this.workCategory.id+'/level/'+buildspace.apps.CostData.Levels.element+'/_csrf_token/'+self.costData._csrf_token,
                costData: self.costData,
                parentItemId: this.workCategory.id,
                level: buildspace.apps.CostData.Levels.element,
                type: buildspace.apps.CostData.ItemTypes.STANDARD
            });

            comparisonReportDialog.show();
        },
        createTenderComparisonReportDialog: function(){
            var self = this;

            var tenderComparisonReportDialog = new TenderComparisonReportDialog({
                exportUrl: "tenderComparison/export/costData/"+self.costData.id+'/parent_id/'+this.workCategory.id+'/level/'+buildspace.apps.CostData.Levels.element,
                costData: self.costData,
                parentItemId: this.workCategory.id,
                level: buildspace.apps.CostData.Levels.element,
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
                gridUrl: "costData/getAllCostDataItemRecords/costData/"+self.costData.id+'/parent_id/'+self.workCategory.id,
                saveUrl: "costData/updateItemVisibility/costData/"+self.costData.id+'/_csrf_token/'+self.workCategory._csrf_token,
                selectedItemIds: selectedItemIds,
                deselectedItemIds: [],
                onSave:function(){
                    self.grid.reload();
                }
            });


            rowInclusionDialog.show();
        },
        createLinkBillItemsContainer: function(item){
            var self = this;
            var pb = buildspace.dialog.indeterminateProgressBar({
                title:nls.pleaseWait+'...'
            });

            pb.show().then(function(){
                dojo.xhrGet({
                    url: "costData/getLinkedItems/cost_data_id/"+self.costData.id+"/id/"+item.id+"/id_type/bill_item_id",
                    content: {
                        id: item.id,
                        cost_data_id: self.costData.id,
                        _csrf_token: self._csrf_token
                    },
                    handleAs: "json",
                    load: function(data) {
                        if( data.success ) {
                            var id = 'linkBillItems-'+self.costData.id+'-projectListingGrid';
                            var container = dijit.byId(id);
                            if(container)
                            {
                                self.removeChild(container);
                                container.destroy();
                            }
                            var projectListingGrid = new ProjectListingGrid({
                                id: id,
                                title: buildspace.truncateString(item.description, 60),
                                region: 'bottom',
                                parentContainer: self,
                                costData: self.costData,
                                item: item,
                                style:"padding:0px;margin:0px;width:100%;height:80%;",
                                selectedItemIds: data.ids,
                                linkedProjectsUrl: "costData/getLinkedItems/cost_data_id/"+self.costData.id+"/id/"+item.id+"/id_type/project_id",
                                linkedBillsUrl: "costData/getLinkedItems/cost_data_id/"+self.costData.id+"/id/"+item.id+"/id_type/bill_id",
                                linkedElementsUrl: "costData/getLinkedItems/cost_data_id/"+self.costData.id+"/id/"+item.id+"/id_type/element_id",
                                conversionFactor: data.conversion_factor,
                                updateUrl: "costData/linkBillItems/id/"+item.id+"/_csrf_token/"+item._csrf_token,
                                onSave: function(){
                                    self.grid.reload();
                                }
                            });

                            self.addChild(projectListingGrid);
                        }
                        pb.hide();
                    },
                    error: function(error) {
                    }
                });
            });
        },
        createLinkVariationOrderItemsContainer: function(item){
            var self = this;

            var id = 'linkVariationOrderItems-'+self.costData.id+'-projectListingGrid';
            var container = dijit.byId(id);
            if(container)
            {
                self.removeChild(container);
                container.destroy();
            }
            var projectListingGrid = new VariationOrderProjectListingGrid({
                id: id,
                title: nls.variationOrders,
                region: 'bottom',
                parentContainer: self,
                costData: self.costData,
                masterItem: item,
                style:"padding:0px;margin:0px;width:100%;height:80%;",
                onSave: function(){
                    self.grid.reload();
                }
            });

            self.addChild(projectListingGrid);
        }
    });
});