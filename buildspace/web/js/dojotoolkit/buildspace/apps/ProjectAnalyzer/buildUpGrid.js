define('buildspace/apps/ProjectAnalyzer/buildUpGrid',[
    'dojo/_base/declare',
    'dojo/_base/lang',
    "dojo/_base/array",
    "dojo/dom-attr",
    "dojox/grid/enhanced/plugins/Menu",
    "buildspace/widget/grid/plugins/FormulatedColumn",
    'dojo/_base/event',
    'dojo/keys',
    'dojo/currency',
    "dijit/focus",
    'dojo/_base/html',
    'dojo/request/xhr',
    'buildspace/widget/grid/cells/Textarea',
    'buildspace/widget/grid/cells/FormulaTextBox',
    'dojo/i18n!buildspace/nls/BuildUpGrid'
], function(declare, lang, array, domAttr, Menu, FormulatedColumn, evt, keys, currency, focusUtil, html, xhr, Textarea, FormulaTextBox, nls ){

    return declare('buildspace.apps.ProjectAnalyzer.BuildUpGrid', dojox.grid.EnhancedGrid, {
        resource: null,
        billItem: null,
        style: "border-top:none;",
        selectedItem: null,
        keepSelection: true,
        rowSelector: '0px',
        updateUrl: null,
        pasteUrl: null,
        buildUpSummaryWidget: null,
        constructor:function(args){
            this.formulatedColumn = FormulatedColumn(this,{});
            this.currencySetting  = (buildspace.billCurrencyAbbreviation) ? buildspace.billCurrencyAbbreviation : buildspace.currencyAbbreviation;
        },
        canSort: function(inSortInfo){
            return false;
        },
        canEdit: function(inCell, inRowIndex) {
            var self = this,
                item = this.getItem(inRowIndex);
            if(inCell != undefined && item.hasOwnProperty('id') && item.id[0] < 0){
                window.setTimeout(function() {
                    self.edit.cancel();
                    self.focus.setFocusIndex(inRowIndex, inCell.index);
                }, 10);
                return;
            }

            return this._canEdit;
        },
        postCreate: function(){
            var self = this;
            self.inherited(arguments);
        },
        doApplyCellEdit: function(val, rowIdx, inAttrName){
            var self = this, item = self.getItem(rowIdx), store = self.store;
            if(val !== item[inAttrName][0]){
                var attrNameParsed = inAttrName.replace("-value","");//for any formulated column

                if(inAttrName.indexOf("-value") !== -1){
                    val = this.formulatedColumn.convertRowIndexToItemId(val, rowIdx);
                }

                var params = {
                    id: item.id,
                    attr_name: attrNameParsed,
                    val: val,
                    _csrf_token: item._csrf_token ? item._csrf_token : null
                }, url = this.updateUrl;

                if(item.id==buildspace.constants.GRID_LAST_ROW){
                    var prevItem = rowIdx > 0 ? self.getItem(rowIdx-1):false;
                    lang.mixin(params, {
                        prev_item_id: prevItem ? prevItem.id : 0,
                        relation_id: item.relation_id,
                        resource_id: self.resource.id
                    });
                    url = this.addUrl;
                }

                var updateCell = function(data, store){
                    for(var property in data){
                        if(item.hasOwnProperty(property) && property != store._getIdentifierAttribute()){
                            store.setValue(item, property, data[property]);
                        }
                        dojo.forEach(data.affected_nodes, function(node){
                            store.fetchItemByIdentity({ 'identity' : node.id,  onItem : function(affectedItem){
                                for(var property in node){
                                    if(item.hasOwnProperty(property) && property != store._getIdentifierAttribute()){
                                        store.setValue(affectedItem, property, node[property]);
                                    }
                                }
                            }});
                        });
                    }
                    store.save();
                }

                var pb = buildspace.dialog.indeterminateProgressBar({
                    title: nls.pleaseWait+'...'
                });

                pb.show();

                var xhrArgs = {
                    url: url,
                    content: params,
                    handleAs: 'json',
                    load: function(resp) {
                        if(resp.success){
                            if(item.id > 0){
                                updateCell(resp.data, store);
                            }else{
                                store.deleteItem(item);
                                store.save();
                                dojo.forEach(resp.items, function(item){
                                    store.newItem(item);
                                });
                                store.save();
                            }
                            self.updateTotalBuildUp(resp.total_build_up);
                            var cell = self.getCellByField(inAttrName);
                            window.setTimeout(function() {
                                self.focus.setFocusIndex(rowIdx, cell.index);
                            }, 10);
                        }
                        pb.hide();
                    },
                    error: function(error) {
                        pb.hide()
                    }
                }
                dojo.xhrPost(xhrArgs);
            }
            self.inherited(arguments);
        },
        updateTotalBuildUp: function(totalBuildUp){
            var accContainer = dijit.byId('accPane-'+this.resource.id+'-'+this.billItem.id);
            accContainer.set('title', this.resource.name+'<span style="color:blue;float:right;">'+this.currencySetting+'&nbsp;'+currency.format(totalBuildUp)+'</span>');
            this.buildUpSummaryWidget.refreshTotalCost();
        }
    });
});