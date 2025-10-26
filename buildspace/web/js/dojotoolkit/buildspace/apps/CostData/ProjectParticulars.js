define('buildspace/apps/CostData/ProjectParticulars',[
    'dojo/_base/declare',
    'dojox/grid/EnhancedGrid',
    'buildspace/widget/grid/cells/Formatter',
    'dojo/currency',
    'buildspace/widget/grid/cells/Textarea',
    'buildspace/widget/grid/cells/Select',
    'dojo/i18n!buildspace/nls/CostData'
], function(declare, EnhancedGrid, GridFormatter, currency, Textarea, Select, nls){

    var Grid = declare('buildspace.apps.CostData.ProjectParticularsGrid', EnhancedGrid, {
        costData: null,
        style: "border:none;",
        region: 'center',
        keepSelection: true,
        rowSelector: '0px',
        escapeHTMLInData: false,
        borderContainerWidget: null,
        constructor:function(args){
            this.inherited(arguments);
        },
        canSort: function(inSortInfo){
            return false;
        },
        postCreate: function(){
            this.inherited(arguments);
        },
        dodblclick: function(e){
            this.onRowDblClick(e);
        },
        doApplyCellEdit: function(val, rowIdx, inAttrName){
            var self = this, item = self.getItem(rowIdx), store = self.store;

            if(val !== item[inAttrName][0]){
                var pb = buildspace.dialog.indeterminateProgressBar({
                    title:nls.savingData+'. '+nls.pleaseWait+'...'
                });
                var params = {
                    id: item.id,
                    val: val,
                    cost_data_id: self.costData.id,
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
                    url: 'costData/updateProjectParticularValue',
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

    var formatter = {
        valueFormatter: function(cellValue, rowIdx, cell){
            var item = this.grid.getItem(rowIdx);

            if(item.id > 0 && cellValue != 0) return currency.format(cellValue);

            return '&nbsp;';
        }
    };

    return declare('buildspace.apps.CostData.ProjectParticularsContainer', dijit.layout.BorderContainer, {
        region: "center",
        style:"padding:0px;border:none;width:100%;height:100%;",
        gutters: false,
        costData: null,
        grid: null,
        editable: false,
        postCreate: function(){
            this.inherited(arguments);

            this.gridFormatter = new GridFormatter();

            var self = this;

            var grid = self.createBreakdownGrid();

            var gridContainer = new dijit.layout.ContentPane( {
                style:"padding:0px;border:none;width:100%;height:100%;",
                region: 'center',
                content: grid,
                grid: grid
            });
            self.addChild(gridContainer);
        },
        getGridStructure: function(){
            return [
                {
                    name: 'No.',
                    field: 'count',
                    width:'30px',
                    styles:'text-align:center;',
                    formatter: this.gridFormatter.rowCountCellFormatter
                },{
                    name: nls.description,
                    field: 'description',
                    width:'auto'
                },{
                    name: nls.summaryDescription,
                    field: 'summary_description',
                    width:'auto',
                    formatter: this.gridFormatter.unEditableCellFormatter,
                },{
                    name: nls.value,
                    field: 'value',
                    width:'140px',
                    styles: 'text-align:right;',
                    cellType: 'buildspace.widget.grid.cells.Textarea',
                    editable: this.editable,
                    formatter: formatter.valueFormatter
                },{
                    name: nls.unit,
                    field: 'uom_id',
                    width: '70px',
                    styles: 'text-align:center;',
                    formatter: this.gridFormatter.unitCellFormatter
                }
            ];
        },
        createBreakdownGrid: function(){
            var self = this;

            self.grid = Grid({
                costData: self.costData,
                borderContainerWidget: self,
                structure: self.getGridStructure(),
                store: new dojo.data.ItemFileWriteStore({
                    clearOnClose:true,
                    url:"costData/getProjectParticularList/costData/"+this.costData.id
                })
            });

            return self.grid;
        }
    });
});