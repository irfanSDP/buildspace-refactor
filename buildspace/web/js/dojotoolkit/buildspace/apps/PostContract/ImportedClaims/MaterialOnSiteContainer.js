define('buildspace/apps/PostContract/ImportedClaims/MaterialOnSiteContainer',[
    'dojo/_base/declare',
    './BaseContainer',
    'buildspace/widget/grid/cells/Formatter',
    'dojo/i18n!buildspace/nls/PostContract'
], function(declare, BaseContainer, GridFormatter, nls){

    return declare('buildspace.apps.PostContract.ImportedClaims.MaterialOnSiteContainer', BaseContainer, {
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
                field: 'final_amount',
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
                field: 'uom_symbol',
                width:'90px',
                styles:'text-align: center;',
                formatter: this.formatter.unEditableCellFormatter
            },{
                name: nls.qty,
                field: 'quantity',
                width:'90px',
                styles:'text-align: right;',
                formatter: this.formatter.unEditableNumberCellFormatter
            },{
                name: nls.rate,
                field: 'rate',
                width:'90px',
                styles:'text-align: right;',
                formatter: this.formatter.unEditableCurrencyCellFormatter
            },{
                name: nls.amount,
                field: 'final_amount',
                width:'90px',
                styles:'text-align: right;',
                formatter: this.formatter.unEditableCurrencyCellFormatter
            },{
                name: nls.reductionPercentage,
                field: 'reduction_percentage',
                width:'90px',
                styles:'text-align: right;',
                formatter: this.formatter.unEditablePercentageCellFormatter
            },{
                name: nls.reductionAmount,
                field: 'reduction_amount',
                width:'90px',
                styles:'text-align: right;',
                formatter: this.formatter.unEditableCurrencyCellFormatter
            }];
        },
        getBreakdownGridUrl: function(){
            var url = 'postContractClaimMaterialOnSite/getImportedMaterialOnSiteList/pid/'+this.project.id;

            if(this.claimCertificate) url += "/claimRevisionId/"+this.claimCertificate.post_contract_claim_revision_id;

            return url;
        },
        getItemGridUrl: function(item){
            var url = 'postContractClaimMaterialOnSite/getImportedMaterialOnSiteItemList/pid/'+this.project.id+'/id/'+item.id;

            if(this.claimCertificate) url += "/claimRevisionId/"+this.claimCertificate.post_contract_claim_revision_id;

            return url;
        }
    });
});