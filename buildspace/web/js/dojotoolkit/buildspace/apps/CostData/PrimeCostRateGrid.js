define('buildspace/apps/CostData/PrimeCostRateGrid',[
    'dojo/_base/declare',
    'dojo/number',
    'dojo/currency',
    'dojox/grid/EnhancedGrid',
    'buildspace/widget/grid/cells/Formatter',
    'buildspace/widget/grid/cells/Textarea',
    './ComparisonReport/ComparisonReportDialog',
    './RowInclusion/RowInclusionDialog',
    './PrimeCostRateLinkBillItems/ProjectListingGrid',
    'dojo/i18n!buildspace/nls/CostData'
], function(declare, number, currency, EnhancedGrid, GridFormatter, Textarea, ComparisonReportDialog, RowInclusionDialog, ProjectListingGrid, nls){

    var Grid = declare('buildspace.apps.CostData.PrimeCostRateGrid', EnhancedGrid, {
        style: "border:none;",
        region: 'center',
        keepSelection: true,
        escapeHTMLInData: false,
        costData: null,
        parentItem: null,
        gridCreator: null,
        constructor:function(args){
            this.inherited(arguments);
        },
        canSort: function(inSortInfo){
            return false;
        },
        postCreate: function(){
            this.inherited(arguments);
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
                url: 'primeCostRate/updateItem',
                content: params,
                handleAs: 'json',
                load: function(resp) {
                    if(resp.success){
                        if(item.id > 0){
                            updateCell(resp.data, store);
                        }
                        self.focusCell(rowIdx, inAttrName);
                        dojo.forEach(self.gridCreator.stackContainer.getChildren(), function(data, index){
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

            if(val !== item[inAttrName][0]){
                self.updateRecord(val, rowIdx, inAttrName);
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
            if( this.field == 'approved_value' && item.approved_value_derived && item.approved_value_derived[ 0 ] ) {
                derived = true;
            }
            else if( this.field == 'awarded_value' && item.awarded_value_derived && item.awarded_value_derived[ 0 ] ) {
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
        },
        textFormatter: function(cellValue, rowIdx, cell){
            var item = this.grid.getItem(rowIdx);
            if(item.type == 'summary'){
                cell.customClasses.push('disable-cell');
            }
            return cellValue;
        }
    };

    return declare('buildspace.apps.CostData.PrimeCostRateContainer', dijit.layout.BorderContainer, {
        region: "center",
        title: '',
        style:"padding:0px;border:none;width:100%;height:100%;",
        gutters: false,
        costData: null,
        parentItem: null,
        grid: null,
        level: null,
        gridCreator: null,
        editable: false,
        postCreate: function(){
            this.inherited(arguments);

            var grid = this.createBreakdownGrid();

            var gridContainer = new dijit.layout.ContentPane( {
                style:"padding:0px;border:none;width:100%;height:100%;",
                region: 'center',
                content: grid,
                grid: grid
            });

            this.addChild(this.createToolbar());
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
                    id: this.parentItem.id + 'prime-cost-rate-refresh-button',
                    label: nls.refresh,
                    iconClass: "icon-16-container icon-16-reload",
                    style: "outline:none!important;",
                    onClick: function () {
                        self.grid.reload();
                    }
                })
            );

            toolbar.addChild(new dijit.ToolbarSeparator());

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

            if(self.editable){
                toolbar.addChild(new dijit.ToolbarSeparator());

                toolbar.addChild(
                    new dijit.form.Button({
                        id: this.parentItem.id + '-prime-cost-rate-row-inclusion-button',
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
            var self = this;
            this.gridFormatter = new GridFormatter();
            return {
                noscroll: false,
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
                        rowSpan: 2,
                        width:self.level != 3 ? 'auto' : '500px',
                        formatter: Formatter.descriptionFormatter
                    },{
                        name: nls.unit,
                        field: "uom_id",
                        rowSpan: 2,
                        width: '70px',
                        styles: 'text-align:center;',
                        hidden: self.level != 3,
                        formatter: this.gridFormatter.unEditableUnitCellFormatter
                    },{
                        name: self.level != 3 ? nls.totalUnits : nls.qty,
                        field: 'units',
                        rowSpan: 2,
                        width:'140px',
                        cellType: 'buildspace.widget.grid.cells.Textarea',
                        editable: this.editable,
                        styles:'text-align:right;',
                        formatter: Formatter.amountFormatter
                    },{
                        name: self.level != 3 ? nls.amountPerUnit : nls.unitRate,
                        field: 'approved_value',
                        width:'140px',
                        cellType: 'buildspace.widget.grid.cells.Textarea',
                        editable: this.editable,
                        styles:'text-align:right;',
                        formatter: Formatter.amountFormatter
                    },{
                        name: nls.totalAmount,
                        field: 'approved_total',
                        width:'120px',
                        styles:'text-align:right;',
                        formatter: Formatter.unEditableAmountCellFormatter
                    },{
                        name: nls.brand,
                        field: 'approved_brand',
                        width:'200px',
                        editable: this.editable,
                        hidden: self.level != 3,
                        cellType: 'buildspace.widget.grid.cells.Textarea',
                        styles:'text-align:center;',
                        formatter: Formatter.textFormatter
                    },{
                        name: self.level != 3 ? nls.amountPerUnit : nls.unitRate,
                        field: 'awarded_value',
                        width:'140px',
                        cellType: 'buildspace.widget.grid.cells.Textarea',
                        editable: this.editable,
                        styles:'text-align:right;',
                        formatter: Formatter.amountFormatter
                    },{
                        name: nls.totalAmount,
                        field: 'awarded_total',
                        width:'120px',
                        styles:'text-align:right;',
                        formatter: Formatter.unEditableAmountCellFormatter
                    },{
                        name: nls.brand,
                        field: 'awarded_brand',
                        width:'200px',
                        editable: this.editable,
                        hidden: self.level != 3,
                        cellType: 'buildspace.widget.grid.cells.Textarea',
                        styles:'text-align:center;',
                        formatter: Formatter.textFormatter
                    }],
                    [{
                        name: this.costData.approved_date ? nls.budget+' ('+this.costData.approved_date+')' : nls.budget,
                        styles:'text-align:center;',
                        headerClasses: "staticHeader",
                        colSpan : self.level != 3 ? 2 : 3
                    },{
                        name: this.costData.awarded_date ? nls.contractSum+' ('+this.costData.awarded_date+')' : nls.contractSum,
                        styles:'text-align:center;',
                        headerClasses: "staticHeader",
                        colSpan : self.level != 3 ? 2 : 3
                    }]
                ]
            };
        },
        createBreakdownGrid: function(){
            var self = this;
            self.grid = Grid({
                costData: self.costData,
                parentItem: self.parentItem,
                gridCreator: self.gridCreator,
                structure:self.getGridStructure(),
                store: new dojo.data.ItemFileWriteStore({
                    clearOnClose:true,
                    url:"primeCostRate/getBreakdown/costData/"+this.costData.id+"/parent_id/"+this.parentItem.id+"/level/"+self.level
                }),
                onRowDblClick: function(e) {
                    var me = this,
                        item = me.getItem(e.rowIndex);
                    if(self.level < 3 && item.id[0] > 0){
                        self.createPrimeCostRateGrid(item);
                    }
                    else if(self.level == 3 && item.id[0] > 0){
                        var colField = e.cell.field;

                        if(colField == 'awarded_value'){
                            self.createLinkBillItemsContainer(item);
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
                exportUrl: "primeCostRate/exportComparisonReport/costData/"+self.costData.id+'/parent_id/'+self.parentItem.id+'/level/'+self.level+'/_csrf_token/'+self.costData._csrf_token,
                costData: self.costData,
                level: self.level,
                type: buildspace.apps.CostData.ItemTypes.PRIME_COST_RATE,
                parentItemId: self.parentItem.id
            });

            comparisonReportDialog.show();
        },
        createRowVisibilityDialog: function(){
            var self = this;

            var selectedItemIds = [];
            dojo.forEach(self.grid.store._arrayOfAllItems, function(item, index){
                if(item.id[0] > 0) selectedItemIds.push(item.id[0]);
            });
            var rowInclusionDialog = new RowInclusionDialog({
                title: nls.showHideRows,
                gridUrl: "primeCostRate/getAllPrimeCostRateRecords/costData/"+self.costData.id+'/parent_id/'+self.parentItem.id,
                saveUrl: "primeCostRate/updateItemVisibility/costData/"+self.costData.id+"/_csrf_token/"+self.costData._csrf_token,
                selectedItemIds: selectedItemIds,
                deselectedItemIds: [],
                onSave:function(){
                    self.grid.reload();
                }
            });

            rowInclusionDialog.show();
        },
        createPrimeCostRateGrid: function(item){
            this.gridCreator.createPrimeCostRateGrid(item, this.level+1);
        },
        createLinkBillItemsContainer: function(item){
            var self = this;
            var pb = buildspace.dialog.indeterminateProgressBar({
                title:nls.pleaseWait+'...'
            });

            pb.show().then(function(){
                dojo.xhrGet({
                    url: "primeCostRate/getLinkedItems/cost_data_id/"+self.costData.id+"/id/"+item.id+"/id_type/bill_item_id",
                    content: {
                        id: item.id,
                        cost_data_id: self.costData.id,
                        _csrf_token: self._csrf_token
                    },
                    handleAs: "json",
                    load: function(data) {
                        if( data.success ) {
                            var id = 'primeCostRate-linkBillItems-'+self.costData.id+'-projectListingGrid';
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
                                linkedProjectsUrl: "primeCostRate/getLinkedItems/cost_data_id/"+self.costData.id+"/id/"+item.id+"/id_type/project_id",
                                linkedBillsUrl: "primeCostRate/getLinkedItems/cost_data_id/"+self.costData.id+"/id/"+item.id+"/id_type/bill_id",
                                linkedElementsUrl: "primeCostRate/getLinkedItems/cost_data_id/"+self.costData.id+"/id/"+item.id+"/id_type/element_id",
                                updateUrl: "primeCostRate/linkBillItems/id/"+item.id+"/_csrf_token/"+item._csrf_token,
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
    });
});