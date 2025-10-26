define('buildspace/apps/CostData/PrimeCostSumBreakdown',[
    'dojo/_base/declare',
    'dojo/number',
    'dojo/currency',
    'dojox/grid/EnhancedGrid',
    'buildspace/widget/grid/cells/Textarea',
    'buildspace/widget/grid/cells/DateTextBox',
    './LinkBillItems/ProjectListingGrid',
    './RowInclusion/RowInclusionDialog',
    'dojo/i18n!buildspace/nls/CostData'
], function(declare, number, currency, EnhancedGrid, Textarea, DateTextBox, ProjectListingGrid, RowInclusionDialog, nls){

    var Grid = declare('buildspace.apps.CostData.PrimeCostSumBreakdown', EnhancedGrid, {
        style: "border:none;",
        region: 'center',
        keepSelection: true,
        escapeHTMLInData: false,
        costData: null,
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
        doApplyCellEdit: function(val, rowIdx, inAttrName){
            var self = this, item = self.getItem(rowIdx), store = self.store;

            var url = 'primeCostSum/updatePrimeCostSumItem';

            if(inAttrName.includes('additional_column-')) url = 'primeCostSum/updatePrimeCostSumColumn';

            if(val !== item[inAttrName][0]){
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
                    url: url,
                    content: params,
                    handleAs: 'json',
                    load: function(resp) {
                        if(resp.success){
                            if(item.id > 0){
                                updateCell(resp.data, store);
                            }
                            var cell = self.getCellByField(inAttrName);
                            window.setTimeout(function() {
                                self.focus.setFocusIndex(rowIdx, cell.index);
                            }, 10);

                        }
                        pb.hide();
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
        rowCountCellFormatter: function(cellValue, rowIdx){
            return parseInt(rowIdx)+1;
        },
        amountFormatter: function(cellValue, rowIdx, cell){
            var item = this.grid.getItem(rowIdx);
            var value = number.parse(cellValue);

            if(isNaN(value) || value == 0 || value == null){
                cellValue = "&nbsp;";
            }else{
                cellValue = currency.format(cellValue);
            }

            var derived = false;

            if( this.field == 'awarded_cost' && item.awarded_cost_derived && item.awarded_cost_derived[ 0 ] ) {
                derived = true;
            }
            else if( item[this.field + '-derived'] !== undefined && item[this.field + '-derived'][ 0 ] ) {
                derived = true;
            }
            if( derived ){
                cellValue = '<span style="color: #42b449;"><strong>' + cellValue + '</strong></span>';
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

    return declare('buildspace.apps.CostData.PrimeCostSumContainer', dijit.layout.BorderContainer, {
        region: "center",
        title: '',
        style:"padding:0px;border:none;width:100%;height:100%;",
        gutters: false,
        costData: null,
        grid: null,
        mainBreakdownGrid: null,
        columns: [],
        editable: false,
        postCreate: function(){
            this.inherited(arguments);
            var self = this;

            var pb = buildspace.dialog.indeterminateProgressBar({
                title: nls.pleaseWait + '...'
            });

            pb.show();
            dojo.xhrPost({
                url: 'primeCostSum/getColumns/costData/'+self.costData.id,
                handleAs: 'json',
                load: function(resp) {
                    self.columns = resp.columns;
                },
                error: function(error) {
                }
            }).then(function(){

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
            });
        },
        createToolbar: function(){
            var self = this;
            var toolbar = new dijit.Toolbar({
                region: "top",
                style: "outline:none!important;border:none;padding:2px;width:100%;"
            });

            toolbar.addChild(
                new dijit.form.Button({
                    id: this.costData.id + 'prime-cost-sum-refresh-button',
                    label: nls.refresh,
                    iconClass: "icon-16-container icon-16-reload",
                    style: "outline:none!important;",
                    onClick: function () {
                        self.mainBreakdownGrid.reloadPrimeCostSumGrid();
                    }
                })
            );

            if(self.editable){
                toolbar.addChild(new dijit.ToolbarSeparator());

                toolbar.addChild(
                    new dijit.form.Button({
                        id: this.costData.id + '-prime-cost-sum-row-inclusion-button',
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
            var columns = this.columns;

            var descriptionWidth = 'auto';

            if(columns.length >= 2) descriptionWidth = '500px';

            var row1Header = [{
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
                width: descriptionWidth,
                cellType: 'buildspace.widget.grid.cells.Textarea'
            },{
                name: nls.budget,
                field: 'approved_cost',
                width:'140px',
                rowSpan: 2,
                cellType: 'buildspace.widget.grid.cells.Textarea',
                editable: this.editable,
                styles:'text-align:right;',
                formatter: Formatter.amountFormatter
            },{
                name: nls.contractSum,
                field: 'awarded_cost',
                width:'140px',
                rowSpan: 2,
                cellType: 'buildspace.widget.grid.cells.Textarea',
                editable: this.editable,
                styles:'text-align:right;',
                formatter: Formatter.amountFormatter
            }];

            for(var i in columns) {
                row1Header.push({
                    name: columns[i]['column_name'],
                    field: 'additional_column-'+columns[i]['id'],
                    width:'140px',
                    cellType: 'buildspace.widget.grid.cells.Textarea',
                    editable: this.editable,
                    styles:'text-align:right;',
                    formatter: Formatter.amountFormatter
                });
            }

            row1Header.push({
                name: nls.totalAmount,
                field: 'nominated_sub_contractor_total_amount',
                width:'140px',
                styles:'text-align:right;',
                formatter: Formatter.unEditableAmountCellFormatter
            });

            row1Header.push({
                name: nls.awardedContractor,
                field: 'awarded_nominated_sub_contractor',
                width:'140px',
                cellType: 'buildspace.widget.grid.cells.Textarea',
                editable: this.editable,
                styles:'text-align:center;'
            });

            row1Header.push({
                name: nls.awardedDate,
                field: 'awarded_date',
                width:'120px',
                cellType: 'buildspace.widget.grid.cells.DateTextBox',
                editable: this.editable,
                styles:'text-align:center;'
            });

            return {
                noscroll: false,
                cells: [
                    row1Header,
                    [{
                        name: nls.awardedNominatedSubContractor,
                        styles:'text-align:center;',
                        headerClasses: "staticHeader",
                        colSpan : row1Header.length-4
                    }]
                ]
            };
        },
        createBreakdownGrid: function(){
            var self = this;
            self.grid = Grid({
                costData: self.costData,
                structure: self.getGridStructure(),
                store: new dojo.data.ItemFileWriteStore({
                    clearOnClose:true,
                    url:"primeCostSum/getPrimeCostSumBreakdown/costData/"+this.costData.id
                }),
                onRowDblClick: function(e) {
                    var me = this,
                        item = me.getItem(e.rowIndex);

                    if(item.id[0] > 0 && self.editable){
                        var colField = e.cell.field;

                        var linkableColumns = [];

                        var columns = self.columns;

                        for(var i in columns) {
                            linkableColumns.push('additional_column-'+columns[i]['id']);
                        }

                        if(colField == 'awarded_cost'){
                            self.createItemLinkBillItemsGrid(item);
                        }
                        if(linkableColumns.includes(colField)){
                            self.createColumnLinkBillItemsGrid(item, colField.replace('additional_column-', ''));
                        }
                    }
                }
            });

            return self.grid;
        },
        createRowVisibilityDialog: function(){
            var self = this;

            var selectedItemIds = [];
            dojo.forEach(self.grid.store._arrayOfAllItems, function(item, index){
                if(item.id[0] > 0) selectedItemIds.push(item.id[0]);
            });
            var rowInclusionDialog = new RowInclusionDialog({
                title: nls.showHideRows,
                gridUrl: "primeCostSum/getAllPrimeCostSumItemRecords/costData/"+self.costData.id,
                saveUrl: "primeCostSum/updateItemVisibility/costData/"+self.costData.id+"/_csrf_token/"+self.costData._csrf_token,
                selectedItemIds: selectedItemIds,
                deselectedItemIds: [],
                onSave:function(){
                    self.grid.reload();
                }
            });

            rowInclusionDialog.show();
        },
        createItemLinkBillItemsStackContainer: function(item, selectedItemIds, conversionFactor, updateUrl, linkedProjectsUrl, linkedBillsUrl, linkedElementsUrl){
            var self = this;
            var id = 'linkBillItems-'+self.costData.id+'-projectListingGrid';
            var container = dijit.byId(id);
            if(container)
            {
                self.removeChild(container);
                container.destroy();
            }
            return new ProjectListingGrid({
                id: id,
                title: buildspace.truncateString(item.description, 60),
                region: 'bottom',
                parentContainer: self,
                costData: self.costData,
                linkItem: item,
                item: item,
                style:"padding:0px;margin:0px;width:100%;height:80%;",
                selectedItemIds: selectedItemIds,
                conversionFactor: conversionFactor,
                updateUrl: updateUrl,
                linkedProjectsUrl: linkedProjectsUrl,
                linkedBillsUrl: linkedBillsUrl,
                linkedElementsUrl: linkedElementsUrl,
                onSave: function(){
                    self.grid.reload();
                }
            });
        },
        createItemLinkBillItemsGrid: function(item){
            var self = this;
            var pb = buildspace.dialog.indeterminateProgressBar({
                title:nls.pleaseWait+'...'
            });

            pb.show().then(function(){
                dojo.xhrGet({
                    url: "primeCostSum/getItemLinkedItems/cost_data_id/"+self.costData.id+"/id/"+item.id+"/id_type/bill_item_id",
                    handleAs: "json",
                    load: function(data) {
                        if( data.success ) {
                            var updateUrl = "primeCostSum/linkItemToBillItems/id/"+item.id+"/_csrf_token/"+item._csrf_token;
                            var linkedProjectsUrl = "primeCostSum/getItemLinkedItems/cost_data_id/"+self.costData.id+"/id/"+item.id+"/id_type/project_id";
                            var linkedBillsUrl = "primeCostSum/getItemLinkedItems/cost_data_id/"+self.costData.id+"/id/"+item.id+"/id_type/bill_id";
                            var linkedElementsUrl = "primeCostSum/getItemLinkedItems/cost_data_id/"+self.costData.id+"/id/"+item.id+"/id_type/element_id";
                            self.addChild(self.createItemLinkBillItemsStackContainer(item, data.ids, data.conversion_factor, updateUrl, linkedProjectsUrl, linkedBillsUrl, linkedElementsUrl));
                        }
                        pb.hide();
                    },
                    error: function(error) {
                    }
                });
            });
        },
        createColumnLinkBillItemsGrid: function(item, columnId){
            var self = this;
            var pb = buildspace.dialog.indeterminateProgressBar({
                title:nls.pleaseWait+'...'
            });

            pb.show().then(function(){
                dojo.xhrGet({
                    url: "primeCostSum/getColumnLinkedItems/id/"+item.id+"/column_id/"+columnId+"/id_type/bill_item_id",
                    handleAs: "json",
                    load: function(data) {
                        if( data.success ) {
                            var updateUrl = "primeCostSum/linkColumnToBillItems/id/"+item.id+"/column_id/"+columnId+"/_csrf_token/"+item._csrf_token;
                            var linkedProjectsUrl = "primeCostSum/getColumnLinkedItems/id/"+item.id+"/column_id/"+columnId+"/id_type/project_id";
                            var linkedBillsUrl = "primeCostSum/getColumnLinkedItems/id/"+item.id+"/column_id/"+columnId+"/id_type/bill_id";
                            var linkedElementsUrl = "primeCostSum/getColumnLinkedItems/id/"+item.id+"/column_id/"+columnId+"/id_type/element_id";
                            self.addChild(self.createItemLinkBillItemsStackContainer(item, data.ids, data.conversion_factor, updateUrl, linkedProjectsUrl, linkedBillsUrl, linkedElementsUrl));
                        }
                        pb.hide();
                    },
                    error: function(error) {
                    }
                });
            });
        }
    });
});