define('buildspace/apps/CostData/ComparisonReport/ComparisonReportPreviewDialog',[
    'dojo/_base/declare',
    "dojo/dom-style",
    'dojo/number',
    'dojo/currency',
    'dojox/grid/EnhancedGrid',
    'buildspace/widget/grid/cells/Formatter',
    'dojo/i18n!buildspace/nls/CostData'
], function(declare, domStyle, number, currency, EnhancedGrid, GridFormatter, nls){

    var Grid = declare('buildspace.apps.CostData.ComparisonReportPreview.Grid', dojox.grid.EnhancedGrid, {
        region: 'center',
        style: "border-top:none;",
        canSort: function(inSortInfo){
            return false;
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
    });

    var Formatter = {
        overallProjectCostingRowCountCellFormatter: function(cellValue, rowIdx, cell){
            var item = this.grid.getItem(rowIdx);
            if(item.type == 'summary'){
                cell.customClasses.push('disable-cell');
            }
            if(!((item.id[0] > 0) || (item.id[0] == buildspace.constants.GRID_LAST_ROW))) return null;
            return parseInt(rowIdx);
        },
        rowCountCellFormatter: function(cellValue, rowIdx, cell){
            var item = this.grid.getItem(rowIdx);
            if(item.type == 'summary'){
                cell.customClasses.push('disable-cell');
            }
            if(!((item.id[0] > 0) || (item.id[0] == buildspace.constants.GRID_LAST_ROW))) return null;
            return parseInt(rowIdx)+1;
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

            if(item.type == 'summary'){
                cell.customClasses.push('disable-cell');
            }

            if((item.id > 0 || item.id == 'provisional_sum' || item.type == 'summary') && cellValue != 0 && cellValue && !isNaN(cellValue)){
                cellValue = currency.format(cellValue);
            }
            else{
                cellValue = '&nbsp;';
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

    return declare('buildspace.apps.CostData.ComparisonReportPreview.Dialog', dijit.Dialog, {
        style:"padding:0px;margin:0px;",
        title: nls.comparisonReport,
        exportUrl: null,
        gridUrl: null,
        costData: this.costData,
        selectedCostDataInfo: {},
        selectedItemIds: [],
        parent: null,
        parentItemId: null,
        level: null,
        type: null,
        projectParticulars: [],
        costComparisonProjectParticulars: [],
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
        onHide: function() {
            this.destroyRecursive();
        },
        createToolbar: function(){
            var self = this;

            var toolbar = new dijit.Toolbar( {
                region: "top",
                style: "outline:none!important;padding:2px;width:100%;"
            } );

            toolbar.addChild( new dijit.form.Button( {
                label: nls.close,
                iconClass: "icon-16-container icon-16-close",
                onClick: function() {
                    self.hide();
                }
            } ) );

            toolbar.addChild( new dijit.ToolbarSeparator() );

            toolbar.addChild( new dijit.form.Button( {
                label: nls.export,
                iconClass: "icon-16-container icon-16-print",
                onClick: function() {
                    self.parent.export();
                }
            } ) );

            return toolbar;
        },
        createContent: function(){
            this.formatter = new GridFormatter();

            var borderContainer = new dijit.layout.BorderContainer({
                style:"padding:0px;width:1200px;height:450px;",
                gutters: false
            });

            borderContainer.addChild(this.createToolbar());

            if(this.type == buildspace.apps.CostData.ItemTypes.STANDARD){
                switch(this.level)
                {
                    case buildspace.apps.CostData.Levels.overallProjectCosting:
                        borderContainer.addChild(this.createOverallProjectCostingPreviewGrid());
                        break;
                    case buildspace.apps.CostData.Levels.workCategory:
                        borderContainer.addChild(this.createWorkCategoryPreviewGrid());
                        break;
                    default:
                        borderContainer.addChild(this.createElementPreviewGrid());
                        break;
                }
            }
            else if(this.type == buildspace.apps.CostData.ItemTypes.PRIME_COST_RATE){
                borderContainer.addChild(this.createPrimeCostRatePreviewGrid());
            }

            return borderContainer;
        },
        getOverallProjectCostingGridStructure: function(){
            var columnsPerSumGroup = 2 + this.costComparisonProjectParticulars.length;

            var fixedHeaders = [{
                name: 'No.',
                field: 'count',
                width:'30px',
                styles:'text-align:center;',
                formatter: Formatter.overallProjectCostingRowCountCellFormatter
            },{
                name: nls.description,
                field: 'description',
                width:'500px',
                formatter: Formatter.descriptionFormatter
            }];

            var firstLevelHeaders = [];
            var secondLevelHeaders = [];
            var thirdLevelHeaders = [];

            var costDataIds = [this.costData.id].concat(this.selectedItemIds);

            for(var i in costDataIds){
                firstLevelHeaders = firstLevelHeaders.concat([{
                    name: nls.amount,
                    field: costDataIds[i]+'_approved_cost',
                    width:'150px', styles:'text-align: right;',
                    formatter : Formatter.amountFormatter
                }]);
                for(var particularIndex in this.costComparisonProjectParticulars)
                {
                    firstLevelHeaders.push({
                        name: nls.cost+'/'+((this.costComparisonProjectParticulars[particularIndex]['uom_symbol'].trim().length === 0) ? nls.unit : this.costComparisonProjectParticulars[particularIndex]['uom_symbol'].trim()),
                        field: costDataIds[i]+'_'+'approved_'+this.costComparisonProjectParticulars[particularIndex]['id'],
                        width:'120px',
                        styles:'text-align:right;',
                        formatter: Formatter.unEditableAmountCellFormatter
                    });
                }
                firstLevelHeaders = firstLevelHeaders.concat([{
                    name: nls.percentageOfTotalCost,
                    field: costDataIds[i]+'_approved_percentage',
                    width:'150px',
                    styles:'text-align: right;',
                    formatter : Formatter.unEditablePercentageCellFormatter
                },{
                    name: nls.amount,
                    field: costDataIds[i]+'_awarded_cost',
                    width:'150px',
                    styles:'text-align: right;',
                    formatter : Formatter.amountFormatter
                }]);
                for(var particularIndex in this.costComparisonProjectParticulars)
                {
                    firstLevelHeaders.push({
                        name: nls.cost+'/'+((this.costComparisonProjectParticulars[particularIndex]['uom_symbol'].trim().length === 0) ? nls.unit : this.costComparisonProjectParticulars[particularIndex]['uom_symbol'].trim()),
                        field: costDataIds[i]+'_'+'awarded_'+this.costComparisonProjectParticulars[particularIndex]['id'],
                        width:'120px',
                        styles:'text-align:right;',
                        formatter: Formatter.unEditableAmountCellFormatter
                    });
                }
                firstLevelHeaders = firstLevelHeaders.concat([{
                    name: nls.percentageOfTotalCost,
                    field: costDataIds[i]+'_awarded_percentage',
                    width:'150px',
                    styles:'text-align: right;',
                    formatter: Formatter.unEditablePercentageCellFormatter
                },{
                    name: nls.amount,
                    field: costDataIds[i]+'_adjusted_cost',
                    width:'140px',
                    styles:'text-align:right;',
                    formatter: Formatter.unEditableCurrencyCellFormatter
                }]);
                for(var particularIndex in this.costComparisonProjectParticulars)
                {
                    firstLevelHeaders.push({
                        name: nls.cost+'/'+((this.costComparisonProjectParticulars[particularIndex]['uom_symbol'].trim().length === 0) ? nls.unit : this.costComparisonProjectParticulars[particularIndex]['uom_symbol'].trim()),
                        field: costDataIds[i]+'_'+'adjusted_'+this.costComparisonProjectParticulars[particularIndex]['id'],
                        width:'120px',
                        styles:'text-align:right;',
                        formatter: Formatter.unEditableAmountCellFormatter
                    });
                }
                firstLevelHeaders = firstLevelHeaders.concat([{
                    name: nls.percentageOfTotalCost,
                    field: costDataIds[i]+'_adjusted_percentage',
                    width:'140px',
                    styles:'text-align:right;',
                    formatter: Formatter.unEditablePercentageCellFormatter
                },{
                    name: nls.variationOrderCost,
                    field: costDataIds[i]+'_variation_order_cost',
                    width:'140px',
                    rowSpan: 3,
                    styles:'text-align:right;',
                    formatter: Formatter.amountFormatter
                },{
                    name: nls.remarks,
                    field: costDataIds[i]+'_remarks',
                    width:'300px',
                    rowSpan: 3,
                    formatter: Formatter.remarksFormatter
                }]);
                secondLevelHeaders = secondLevelHeaders.concat([{
                    name: this.selectedCostDataInfo[costDataIds[i]]['approved_date'] ? nls.budget+' ('+this.selectedCostDataInfo[costDataIds[i]]['approved_date']+')' : nls.budget,
                    styles:'text-align:center;',
                    headerClasses: "staticHeader",
                    colSpan : columnsPerSumGroup
                },{
                    name: this.selectedCostDataInfo[costDataIds[i]]['awarded_date'] ? nls.contractSum+' ('+this.selectedCostDataInfo[costDataIds[i]]['awarded_date']+')' : nls.contractSum,
                    styles:'text-align:center;',
                    headerClasses: "staticHeader",
                    colSpan : columnsPerSumGroup
                },{
                    name: this.selectedCostDataInfo[costDataIds[i]]['adjusted_date'] ? nls.adjustedSum+' ('+this.selectedCostDataInfo[costDataIds[i]]['adjusted_date']+')' : nls.adjustedSum,
                    styles:'text-align:center;',
                    headerClasses: "staticHeader",
                    colSpan : columnsPerSumGroup
                }]);
                thirdLevelHeaders = thirdLevelHeaders.concat([{
                    name: this.selectedCostDataInfo[costDataIds[i]]['name'],
                    styles:'text-align:center;',
                    headerClasses: "staticHeader",
                    colSpan : 2+(columnsPerSumGroup*3)
                }]);
            }

            var cells = [
                firstLevelHeaders,
                secondLevelHeaders,
                thirdLevelHeaders,
            ];

            return [{
                noscroll: true,
                cells: fixedHeaders
            },{
                noscroll: false,
                cells: cells
            }];
        },
        getWorkCategoryGridStructure: function(){
            var fixedHeaders = [{
                    name: 'No.',
                    field: 'count',
                    width:'30px',
                    styles:'text-align:center;',
                    formatter: Formatter.rowCountCellFormatter
                },{
                    name: nls.description,
                    field: 'description',
                    width:'500px',
                    formatter: Formatter.descriptionFormatter
                }];

            var firstLevelHeaders = [];
            var secondLevelHeaders = [];
            var thirdLevelHeaders = [];

            var costDataIds = [this.costData.id].concat(this.selectedItemIds);

            columnGroups = [
                'approved',
                'awarded',
                'adjusted',
            ];

            var variableColumnNumber = 2 + this.projectParticulars.length;

            for(var i in costDataIds){
                for(var groupIndex in columnGroups){
                    firstLevelHeaders.push({
                        name: nls.amount,
                        field: costDataIds[i]+'_'+columnGroups[groupIndex]+'_cost',
                        width:'140px',
                        styles:'text-align:right;',
                        formatter: Formatter.amountFormatter
                    });
                    for(var index in this.projectParticulars) {
                        firstLevelHeaders.push({
                            name: nls.cost+"/"+this.projectParticulars[index]['description'],
                            field: costDataIds[i]+'_'+columnGroups[groupIndex]+'_'+this.projectParticulars[index]['id'],
                            width:'120px',
                            styles:'text-align:right;',
                            formatter: Formatter.unEditableAmountCellFormatter
                        });
                    }
                    firstLevelHeaders.push({
                        name: nls.percentage,
                        field: costDataIds[i]+'_'+columnGroups[groupIndex]+'_percentage',
                        width:'70px',
                        styles:'text-align:right;',
                        formatter: Formatter.unEditablePercentageCellFormatter
                    });
                }

                firstLevelHeaders.push({
                    name: nls.variationOrderCost,
                    field: costDataIds[i]+'_variation_order_cost',
                    width:'140px',
                    rowSpan: 3,
                    styles:'text-align:right;',
                    formatter: Formatter.amountFormatter
                });
                firstLevelHeaders.push({
                    name: nls.remarks,
                    field: costDataIds[i]+'_remarks',
                    width:'300px',
                    rowSpan: 3,
                    formatter: Formatter.remarksFormatter
                });
                secondLevelHeaders = secondLevelHeaders.concat([{
                    name: this.selectedCostDataInfo[costDataIds[i]]['approved_date'] ? nls.budget+' ('+this.selectedCostDataInfo[costDataIds[i]]['approved_date']+')' : nls.budget,
                    styles:'text-align:center;',
                    headerClasses: "staticHeader",
                    colSpan : variableColumnNumber
                },{
                    name: this.selectedCostDataInfo[costDataIds[i]]['awarded_date'] ? nls.contractSum+' ('+this.selectedCostDataInfo[costDataIds[i]]['awarded_date']+')' : nls.contractSum,
                    styles:'text-align:center;',
                    headerClasses: "staticHeader",
                    colSpan : variableColumnNumber
                },{
                    name: this.selectedCostDataInfo[costDataIds[i]]['adjusted_date'] ? nls.adjustedSum+' ('+this.selectedCostDataInfo[costDataIds[i]]['adjusted_date']+')' : nls.adjustedSum,
                    styles:'text-align:center;',
                    headerClasses: "staticHeader",
                    colSpan : variableColumnNumber
                }]);
                thirdLevelHeaders = thirdLevelHeaders.concat([{
                    name: this.selectedCostDataInfo[costDataIds[i]]['name'],
                    styles:'text-align:center;',
                    headerClasses: "staticHeader",
                    colSpan : 3*(variableColumnNumber)+2
                }]);
            }

            var cells = [
                firstLevelHeaders,
                secondLevelHeaders,
                thirdLevelHeaders,
            ];

            return [{
                noscroll: true,
                cells: fixedHeaders
            },{
                noscroll: false,
                cells: cells
            }];
        },
        getElementGridStructure: function(){
            var columnsPerSumGroup = 2 + this.costComparisonProjectParticulars.length;

            var fixedHeaders = [{
                    name: 'No.',
                    field: 'count',
                    width:'30px',
                    styles:'text-align:center;',
                    formatter: Formatter.rowCountCellFormatter
                },{
                    name: nls.description,
                    field: 'description',
                    width:'500px',
                    formatter: Formatter.descriptionFormatter
                }];

            var firstLevelHeaders = [];
            var secondLevelHeaders = [];
            var thirdLevelHeaders = [];

            var costDataIds = [this.costData.id].concat(this.selectedItemIds);

            for(var i in costDataIds){
                firstLevelHeaders.push({
                    name: nls.amount,
                    field: costDataIds[i]+'_approved_cost',
                    width:'150px', styles:'text-align: right;',
                    formatter : Formatter.amountFormatter
                });
                for(var particularIndex in this.costComparisonProjectParticulars)
                {
                    firstLevelHeaders.push({
                        name: nls.elementalCost+'/'+this.costComparisonProjectParticulars[particularIndex]['description']+' ('+this.costComparisonProjectParticulars[particularIndex]['uom_symbol']+')',
                        field: costDataIds[i]+'_'+'approved_'+this.costComparisonProjectParticulars[particularIndex]['id'],
                        width:'120px',
                        styles:'text-align:right;',
                        formatter: Formatter.unEditableAmountCellFormatter
                    });
                }
                firstLevelHeaders.push({
                    name: nls.percentageOfTotalCost,
                    field: costDataIds[i]+'_approved_percentage',
                    width:'150px',
                    styles:'text-align: right;',
                    formatter : Formatter.unEditablePercentageCellFormatter
                });
                firstLevelHeaders.push({
                    name: nls.amount,
                    field: costDataIds[i]+'_awarded_cost',
                    width:'150px',
                    styles:'text-align: right;',
                    formatter : Formatter.amountFormatter
                });
                for(var particularIndex in this.costComparisonProjectParticulars)
                {
                    firstLevelHeaders.push({
                        name: nls.elementalCost+'/'+this.costComparisonProjectParticulars[particularIndex]['description']+' ('+this.costComparisonProjectParticulars[particularIndex]['uom_symbol']+')',
                        field: costDataIds[i]+'_'+'awarded_'+this.costComparisonProjectParticulars[particularIndex]['id'],
                        width:'120px',
                        styles:'text-align:right;',
                        formatter: Formatter.unEditableAmountCellFormatter
                    });
                }
                firstLevelHeaders.push({
                    name: nls.percentageOfTotalCost,
                    field: costDataIds[i]+'_awarded_percentage',
                    width:'150px',
                    styles:'text-align: right;',
                    formatter: Formatter.unEditablePercentageCellFormatter
                });
                firstLevelHeaders.push({
                    name: nls.amount,
                    field: costDataIds[i]+'_adjusted_cost',
                    width:'140px',
                    styles:'text-align:right;',
                    formatter: Formatter.unEditableCurrencyCellFormatter
                });
                for(var particularIndex in this.costComparisonProjectParticulars)
                {
                    firstLevelHeaders.push({
                        name: nls.elementalCost+'/'+this.costComparisonProjectParticulars[particularIndex]['description']+' ('+this.costComparisonProjectParticulars[particularIndex]['uom_symbol']+')',
                        field: costDataIds[i]+'_'+'adjusted_'+this.costComparisonProjectParticulars[particularIndex]['id'],
                        width:'120px',
                        styles:'text-align:right;',
                        formatter: Formatter.unEditableAmountCellFormatter
                    });
                }
                firstLevelHeaders.push({
                    name: nls.percentageOfTotalCost,
                    field: costDataIds[i]+'_adjusted_percentage',
                    width:'140px',
                    styles:'text-align:right;',
                    formatter: Formatter.unEditablePercentageCellFormatter
                });
                firstLevelHeaders.push({
                    name: nls.variationOrderCost,
                    field: costDataIds[i]+'_variation_order_cost',
                    width:'140px',
                    rowSpan: 3,
                    styles:'text-align:right;',
                    formatter: Formatter.amountFormatter
                });
                firstLevelHeaders.push({
                    name: nls.remarks,
                    field: costDataIds[i]+'_remarks',
                    width:'300px',
                    rowSpan: 3,
                    formatter: Formatter.remarksFormatter
                });
                secondLevelHeaders = secondLevelHeaders.concat([{
                    name: this.selectedCostDataInfo[costDataIds[i]]['approved_date'] ? nls.budget+' ('+this.selectedCostDataInfo[costDataIds[i]]['approved_date']+')' : nls.budget,
                    styles:'text-align:center;',
                    headerClasses: "staticHeader",
                    colSpan : columnsPerSumGroup
                },{
                    name: this.selectedCostDataInfo[costDataIds[i]]['awarded_date'] ? nls.contractSum+' ('+this.selectedCostDataInfo[costDataIds[i]]['awarded_date']+')' : nls.contractSum,
                    styles:'text-align:center;',
                    headerClasses: "staticHeader",
                    colSpan : columnsPerSumGroup
                },{
                    name: this.selectedCostDataInfo[costDataIds[i]]['adjusted_date'] ? nls.adjustedSum+' ('+this.selectedCostDataInfo[costDataIds[i]]['adjusted_date']+')' : nls.adjustedSum,
                    styles:'text-align:center;',
                    headerClasses: "staticHeader",
                    colSpan : columnsPerSumGroup
                }]);
                thirdLevelHeaders = thirdLevelHeaders.concat([{
                    name: this.selectedCostDataInfo[costDataIds[i]]['name'],
                    styles:'text-align:center;',
                    headerClasses: "staticHeader",
                    colSpan : (columnsPerSumGroup*3)+2
                }]);
            }

            var cells = [
                firstLevelHeaders,
                secondLevelHeaders,
                thirdLevelHeaders,
            ];

            return [{
                noscroll: true,
                cells: fixedHeaders
            },{
                noscroll: false,
                cells: cells
            }];
        },
        getPrimeCostRateGridStructure: function(){
            var fixedHeaders = [{
                    name: 'No.',
                    field: 'count',
                    width:'30px',
                    styles:'text-align:center;',
                    formatter: Formatter.rowCountCellFormatter
                },{
                    name: nls.description,
                    field: 'description',
                    width:'500px',
                    formatter: Formatter.descriptionFormatter
                },{
                    name: nls.unit,
                    field: "uom_id",
                    width: '70px',
                    styles: 'text-align:center;',
                    hidden: this.level != 3,
                    formatter: this.formatter.unEditableUnitCellFormatter
                }];

            var firstLevelHeaders = [];
            var secondLevelHeaders = [];
            var thirdLevelHeaders = [];
            var cells = [];

            var costDataIds = [this.costData.id].concat(this.selectedItemIds);

            for(var i in costDataIds){
                firstLevelHeaders.push({
                    name: this.level != 3 ? nls.totalUnits : nls.qty,
                    field: costDataIds[i]+'_units',
                    width:'150px', styles:'text-align: right;',
                    rowSpan: 2,
                    formatter : Formatter.amountFormatter
                });
                firstLevelHeaders.push({
                    name: this.level != 3 ? nls.amountPerUnit : nls.unitRate,
                    field: costDataIds[i]+'_approved_value',
                    width:'150px', styles:'text-align: right;',
                    formatter : Formatter.amountFormatter
                });
                firstLevelHeaders.push({
                    name: nls.totalAmount,
                    field: costDataIds[i]+'_approved_total',
                    width:'150px', styles:'text-align: right;',
                    formatter : Formatter.amountFormatter
                });
                if(this.level == 3){
                    firstLevelHeaders.push({
                        name: nls.brand,
                        field: costDataIds[i]+'_approved_brand',
                        width:'150px', styles:'text-align: center;',
                        formatter : Formatter.remarksFormatter
                    });
                }
                firstLevelHeaders.push({
                    name: self.level != 3 ? nls.amountPerUnit : nls.unitRate,
                    field: costDataIds[i]+'_awarded_value',
                    width:'150px', styles:'text-align: right;',
                    formatter : Formatter.amountFormatter
                });
                firstLevelHeaders.push({
                    name: nls.totalAmount,
                    field: costDataIds[i]+'_awarded_total',
                    width:'150px', styles:'text-align: right;',
                    formatter : Formatter.amountFormatter
                });
                if(this.level == 3){
                    firstLevelHeaders.push({
                        name: nls.brand,
                        field: costDataIds[i]+'_awarded_brand',
                        width:'150px', styles:'text-align: center;',
                        formatter : Formatter.remarksFormatter
                    });
                }

                secondLevelHeaders.push({
                    name: this.selectedCostDataInfo[costDataIds[i]]['approved_date'] ? nls.budget+' ('+this.selectedCostDataInfo[costDataIds[i]]['approved_date']+')' : nls.budget,
                    styles:'text-align:center;',
                    headerClasses: "staticHeader",
                    colSpan : this.level != 3 ? 2 : 3
                });

                secondLevelHeaders.push({
                    name: this.selectedCostDataInfo[costDataIds[i]]['awarded_date'] ? nls.contractSum+' ('+this.selectedCostDataInfo[costDataIds[i]]['awarded_date']+')' : nls.contractSum,
                    styles:'text-align:center;',
                    headerClasses: "staticHeader",
                    colSpan : this.level != 3 ? 2 : 3
                });

                thirdLevelHeaders.push({
                    name: this.selectedCostDataInfo[costDataIds[i]]['name'],
                    styles:'text-align:center;',
                    headerClasses: "staticHeader",
                    colSpan : this.level != 3 ? 5 : 7
                });
            }

            cells = [
                firstLevelHeaders,
                secondLevelHeaders,
                thirdLevelHeaders
            ];

            return [{
                noscroll: true,
                cells: fixedHeaders
            },{
                noscroll: false,
                cells: cells
            }];
        },
        createOverallProjectCostingPreviewGrid: function(){
            var self = this;
            var url = "comparisonReport/overallProjectCostingPreview/cost_data_id/"+this.costData.id;

            if(this.selectedItemIds.length > 0){
                url += "?selected_ids="+this.selectedItemIds.join();
            }

            var gridContainer = new dijit.layout.BorderContainer({
                region: "center",
                style:"padding:0px;border:none;width:100%;height:100%;",
                gutters: false,
            });

            var pb = buildspace.dialog.indeterminateProgressBar({
                title:nls.pleaseWait+'...'
            });

            var xhrArgs = {
                url: 'costData/getCostComparisonProjectParticulars',
                content: { cost_data_id: self.costData.id },
                handleAs: 'json',
                load: function(resp) {
                    if(resp.success){
                        self.costComparisonProjectParticulars = resp.data;

                        var grid = Grid({
                            structure: self.getOverallProjectCostingGridStructure(),
                            store: dojo.data.ItemFileWriteStore({
                                url: url,
                                clearOnClose: true
                            })
                        });

                        gridContainer.addChild(grid);
                    }
                    pb.hide();
                },
                error: function(error) {
                    pb.hide();
                }
            };

            pb.show().then(function(){
                dojo.xhrPost(xhrArgs);
            });

            return gridContainer;
        },
        createWorkCategoryPreviewGrid: function(){
            var self = this;

            var gridContainer = new dijit.layout.BorderContainer({
                region: "center",
                style:"padding:0px;border:none;width:100%;height:100%;",
                gutters: false,
            });

            var pb = buildspace.dialog.indeterminateProgressBar({
                title:nls.pleaseWait+'...'
            });

            var xhrArgs = {
                url: 'costData/getWorkCategoryParticulars',
                content: { cost_data_id: self.costData.id, id: self.parentItemId },
                handleAs: 'json',
                load: function(resp) {
                    if(resp.success){
                        self.projectParticulars = resp.data;

                        var url = "comparisonReport/workCategoryPreview/cost_data_id/"+self.costData.id+"/parent_id/"+self.parentItemId;

                        if(self.selectedItemIds.length > 0){
                            url += "?selected_ids="+self.selectedItemIds.join();
                        }

                        var grid = Grid({
                            structure: self.getWorkCategoryGridStructure(),
                            store: dojo.data.ItemFileWriteStore({
                                url: url,
                                clearOnClose: true
                            })
                        });

                        gridContainer.addChild(grid);
                    }
                    pb.hide();
                },
                error: function(error) {
                    pb.hide();
                }
            };

            pb.show().then(function(){
                dojo.xhrPost(xhrArgs);
            });

            return gridContainer;
        },
        createElementPreviewGrid: function(){
            var self = this;

            var url = "comparisonReport/elementPreview/cost_data_id/"+this.costData.id+"/parent_id/"+this.parentItemId;

            if(this.selectedItemIds.length > 0){
                url += "?selected_ids="+this.selectedItemIds.join();
            }

            var gridContainer = new dijit.layout.BorderContainer({
                region: "center",
                style:"padding:0px;border:none;width:100%;height:100%;",
                gutters: false,
            });

            var pb = buildspace.dialog.indeterminateProgressBar({
                title:nls.pleaseWait+'...'
            });

            var xhrArgs = {
                url: 'costData/getCostComparisonProjectParticulars',
                content: { cost_data_id: self.costData.id },
                handleAs: 'json',
                load: function(resp) {
                    if(resp.success){
                        self.costComparisonProjectParticulars = resp.data;

                        var grid = Grid({
                            structure: self.getElementGridStructure(),
                            store: dojo.data.ItemFileWriteStore({
                                url: url,
                                clearOnClose: true
                            })
                        });

                        gridContainer.addChild(grid);
                    }
                    pb.hide();
                },
                error: function(error) {
                    pb.hide();
                }
            };

            pb.show().then(function(){
                dojo.xhrPost(xhrArgs);
            });

            return gridContainer;
        },
        createPrimeCostRatePreviewGrid: function(){
            var url = "comparisonReport/primeCostRatePreview/cost_data_id/"+this.costData.id+"/parent_id/"+this.parentItemId+"/level/"+this.level;

            if(this.selectedItemIds.length > 0){
                url += "?selected_ids="+this.selectedItemIds.join();
            }

            var gridContainer = new dijit.layout.BorderContainer({
                region: "center",
                style:"padding:0px;border:none;width:100%;height:100%;",
                gutters: false,
            });

            var grid = Grid({
                structure: this.getPrimeCostRateGridStructure(),
                store: dojo.data.ItemFileWriteStore({
                    url: url,
                    clearOnClose: true
                })
            });

            gridContainer.addChild(grid);

            return gridContainer;
        }
    });
});
