define('buildspace/apps/PostContract/VariationOrder/ImportedVariationOrderContainer',[
    'dojo/_base/declare',
    '../ImportedClaims/BaseContainer',
    'dojox/grid/EnhancedGrid',
    'buildspace/widget/grid/cells/Formatter',
    'dojo/i18n!buildspace/nls/PostContract'
], function(declare, BaseContainer, EnhancedGrid, GridFormatter, nls){

    return declare('buildspace.apps.PostContract.VariationOrder.ImportedVariationOrderContainer', BaseContainer, {
        region: "center",
        title: '',
        style:"padding:0px;border:none;width:100%;height:40%;",
        gutters: false,
        splitter: true,
        grid: null,
        stackContainer: null,
        parentContainer: null,
        project: null,
        claimCertificate: null,
        getBreakdownGridStructure: function(){
            return [{
                name: "No",
                field: "id",
                width: '30px',
                styles: 'text-align: center;',
                formatter: this.formatter.rowCountCellFormatter
            },{
                name: nls.description,
                field: "description",
                width: 'auto'
            },{
                name: nls.attachment,
                field: 'attachment',
                width: '128px',
                styles: 'text-align:center;',
                formatter: this.formatter.attachmentsCellFormatter,
                noresize: true
            },{
                name: nls.amount,
                field: 'total_amount',
                width:'90px',
                styles:'text-align: right;',
                formatter: this.formatter.unEditableCurrencyCellFormatter
            },{
                name: nls.upToDateClaim,
                field: 'up_to_date_amount',
                width:'90px',
                styles:'text-align: right;',
                formatter: this.formatter.unEditableCurrencyCellFormatter
            }];
        },
        getItemGridStructure: function(){
            return [{
                name: "No",
                field: "id",
                width: '30px',
                styles: 'text-align: center;',
                formatter: this.formatter.rowCountCellFormatter
            },{
                name: nls.totalUnit,
                field: 'total_unit',
                styles: "text-align:center;",
                width: '70px',
                formatter: this.formatter.unEditableIntegerCellFormatter,
                noresize: true,
            },{
                name: nls.description,
                field: "description",
                width: 'auto',
                formatter: this.formatter.treeCellFormatter,
                noresize: true
            },{
                name: nls.attachment,
                field: 'attachment',
                width: '128px',
                styles: 'text-align:center;',
                formatter: this.formatter.attachmentsCellFormatter,
                noresize: true
            },{
                name: nls.type,
                field: 'type',
                width: '70px',
                styles: 'text-align:center;',
                editable: false,
                type: 'dojox.grid.cells.Select',
                options: this.hierarchyTypes.options,
                values: this.hierarchyTypes.values,
                formatter: this.formatter.typeCellFormatter
            },{
                name: nls.unit,
                field: 'unit',
                width: '70px',
                editable: false,
                styles: 'text-align:center;',
                formatter: this.formatter.unEditableNumberAndTextCellFormatter,
                noresize: true,
            },{
                name: nls.rate,
                field: 'rate',
                styles: "text-align:right;",
                width: '120px',
                editable: false,
                formatter: this.formatter.unEditableCurrencyCellFormatter,
                noresize: true,
            },{
                name: nls.qty,
                field: 'quantity',
                styles: "text-align:right;",
                width: '90px',
                formatter: this.formatter.unEditableNumberCellFormatter,
                noresize: true
            },{
                name: nls.amount,
                field: 'total_amount',
                width:'90px',
                styles:'text-align: right;',
                formatter: this.formatter.unEditableCurrencyCellFormatter
            },{
                name: nls.upToDateClaim,
                field: 'up_to_date_amount',
                width:'90px',
                styles:'text-align: right;',
                formatter: this.formatter.unEditableCurrencyCellFormatter
            }];
        },
        getBreakdownGridUrl: function(){
            var url = "variationOrder/getImportedVariationOrders/pid/"+this.project.id;

            if(this.claimCertificate) url += "/claimRevisionId/"+this.claimCertificate.post_contract_claim_revision_id;

            return url;
        },
        getItemGridUrl: function(item){
            var url = "variationOrder/getImportedVariationOrderItems/pid/"+this.project.id+"/void/"+item.id;

            if(this.claimCertificate) url += "/claimRevisionId/"+this.claimCertificate.post_contract_claim_revision_id;

            return url;
        }
    });
});