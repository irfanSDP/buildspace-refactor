define('buildspace/apps/ProjectImportRates/ScheduleOfRateGrid',[
    'dojo/_base/declare',
    'dojo/i18n!buildspace/nls/ProjectImportRates'
], function(declare, nls){

    return declare('buildspace.apps.ProjectImportRates.ScheduleOfRateGrid', dojox.grid.EnhancedGrid, {
        type: null,
        style: "border-top:none;",
        rowSelector: '0px',
        region: 'center',
        canSort: function(inSortInfo){
            return false;
        },
        postCreate: function(){
            var self = this;
            this.inherited(arguments);

            if(this.type == 'item'){
                this.on("RowClick", function(e){
                    var colField = e.cell.field,
                        rowIndex = e.rowIndex,
                        _item = this.getItem(rowIndex);
                    if(colField == 'import_rate' && parseInt(String(_item.id)) > 0 && String(_item.description) !== null && String(_item['rate-value']) !== null && parseInt(String(_item.type)) != buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER && parseInt(String(_item.type)) != buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER_N && parseInt(String(_item.type)) != buildspace.widget.grid.constants.HIERARCHY_TYPE_NOID && (_item.recalculate_resources_library_status != undefined && !_item.recalculate_resources_library_status[0])){
                        self.importRates(_item);
                    }
                }, true);
            }
        },
        importRates: function(scheduleOfRateItem){
            var billItemGrid = dijit.byId('project_bill_item-grid');
            if(typeof billItemGrid == 'undefined'){
                buildspace.dialog.alert(nls.noBillItemAlert, nls.pleaseOpenBillItem+'.', 90, 320);
            }else{
                if(billItemGrid.selection.getSelected().length > 0){
                    var ids = [];
                    dojo.forEach(billItemGrid.selection.getSelected(), function(item){
                        if(item && parseInt(String(item.id)) > 0 &&
                            parseInt(String(item.type)) != buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER &&
                            parseInt(String(item.type)) != buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER_N &&
                            parseInt(String(item.type)) != buildspace.widget.grid.constants.HIERARCHY_TYPE_NOID &&
                            parseInt(String(item.type)) != buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_PC_RATE){
                            ids.push(parseInt(String(item.id)));
                        }
                    });

                    if(ids.length > 0 && billItemGrid.element){
                        var pb = buildspace.dialog.indeterminateProgressBar({
                            title:nls.savingData+'. '+nls.pleaseWait+'...'
                        });
                        pb.show().then(function(){
                            dojo.xhrPost({
                                url: 'billManagerImportRate/import',
                                content: {element_id: parseInt(String(billItemGrid.element.id)), schedule_of_rate_id: parseInt(String(scheduleOfRateItem.id)), _csrf_token: billItemGrid.element._csrf_token, 'ids': ids.toString()},
                                handleAs: 'json',
                                load: function(resp) {
                                    if(resp.success){
                                        var store = billItemGrid.store;
                                        dojo.forEach(resp.items, function(node){
                                            store.fetchItemByIdentity({ 'identity' : node.id,  onItem : function(item){
                                                for(var property in node){
                                                    if(item.hasOwnProperty(property) && property != store._getIdentifierAttribute()){
                                                        store.setValue(item, property, node[property]);
                                                    }
                                                }
                                            }});
                                        });
                                        store.save();
                                    }
                                    pb.hide();
                                    billItemGrid.selection.clear();
                                },
                                error: function(error) {
                                    pb.hide();
                                    billItemGrid.selection.clear();
                                }
                            });
                        });

                    }else{
                        billItemGrid.selection.clear();
                    }
                }else{
                    buildspace.dialog.alert(nls.noItemSelectedAlert, nls.pleaseSelectItem+'.', 90, 320);
                }
            }
        }
    });
});
