define('buildspace/apps/ProjectAnalyzer/MultiRateAnalyzerDialog',[
    'dojo/_base/declare',
    'dojo/_base/lang',
    "dojo/html",
    'dojo/keys',
    "dojo/dom-style",
    "buildspace/widget/grid/plugins/FormulatedColumn",
    "buildspace/widget/grid/cells/Formatter",
    'dojo/i18n!buildspace/nls/ProjectAnalyzer'
], function(declare, lang, html, keys, domStyle, FormulatedColumn, GridFormatter, nls){

    var MultiRateAnalyzerGrid = declare('buildspace.apps.ProjectAnalyzer.MultiRateAnalyzerGrid', dojox.grid.EnhancedGrid, {
        resourceStore: null,
        projectId: 0,
        resourceId: 0,
        billGrid: null,
        style:"border-top:0px;",
        canSort: function(inSortInfo){
            return false;
        },
        postCreate: function(){
            this.formulatedColumn = FormulatedColumn(this,{});
            this.inherited(arguments);
        },
        doApplyCellEdit: function(val, rowIdx, inAttrName){
            var self = this, item = self.getItem(rowIdx), store = self.store;
            var attrNameParsed = inAttrName.replace("-value","");//for any formulated column

            if(val !== item[inAttrName][0]){
                var pb = buildspace.dialog.indeterminateProgressBar({
                    title:nls.mayTakeAWhile+'. '+nls.pleaseWait+'...'
                });
                var params = {
                    id: item.id,
                    pid: self.projectId,
                    rid: self.resourceId,
                    cname: attrNameParsed,
                    oval: item[attrNameParsed+'-value'],
                    val: val,
                    _csrf_token: item._csrf_token ? item._csrf_token : null
                };

                var updateCell = function(data, store){
                    for(var property in data){
                        if(item.hasOwnProperty(property) && property != store._getIdentifierAttribute()){
                            store.setValue(item, property, data[property]);
                        }
                    }
                    store.save();
                }

                var xhrArgs = {
                    url: 'projectAnalyzer/resourceMultiValueUpdate',
                    content: params,
                    handleAs: 'json',
                    load: function(resp) {
                        updateCell(resp.data, store);
                        var cell = self.getCellByField(inAttrName),
                            respStore = resp.resource_store;
                        window.setTimeout(function() {
                            self.focus.setFocusIndex(rowIdx, cell.index);
                        }, 10);

                        if(!respStore.multi){
                            var resourceStore = self.resourceStore,
                                respStoreData = respStore.data;
                            resourceStore.fetchItemByIdentity({ 'identity' : respStoreData.id,  onItem : function(resourceItem){
                                for(var property in respStoreData){
                                    if(resourceItem.hasOwnProperty(property) && property != resourceStore._getIdentifierAttribute()){
                                        resourceStore.setValue(resourceItem, property, respStoreData[property]);
                                    }
                                }
                            }});
                            store.save();
                            resourceStore.save();
                        }
                        pb.hide();
                    },
                    error: function(error) {
                        pb.hide();
                    }
                }
                pb.show();
                dojo.xhrPost(xhrArgs);
            }
            self.inherited(arguments);
        }
    });

    return declare('buildspace.apps.ProjectAnalyzer.MultiRateAnalyzerDialog', dijit.Dialog, {
        style:"padding:0px;margin:0px;",
        resourceStore: null,
        resourceId: 0,
        projectId: 0,
        columnName: 'rate',
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
        _onKey: function(e){
            key = e.keyCode;
            if (key == keys.ESCAPE) {
                dojo.stopEvent(e);
            }
        },
        onHide: function() {
            this.destroyRecursive();
        },
        createContent: function(){
            var borderContainer = new dijit.layout.BorderContainer({
                style:"padding:0px;width:350px;height:250px;",
                gutters: false
            });

            var toolbar = new dijit.Toolbar({
                region: "top",
                style: "outline:none!important;padding:2px;overflow:hidden;border-bottom:0px;"
            });

            toolbar.addChild(
                new dijit.form.Button({
                    label: nls.close,
                    iconClass: "icon-16-container icon-16-close",
                    style:"outline:none!important;",
                    onClick: dojo.hitch(this, 'hide')
                })
            );

            var formatter = new GridFormatter(),
                structure = this.columnName == 'rate' ? [
                    {name: 'No.', field: 'id', width:'30px', styles:'text-align:center;', formatter: formatter.rowCountCellFormatter },
                    {name: nls.rate, field: 'rate-value', width:'150px', styles:'text-align:right;', editable:true, cellType:'buildspace.widget.grid.cells.FormulaTextBox', formatter: formatter.formulaCurrencyCellFormatter},
                    {name: nls.noOfBillItems, field: 'no_bill_items', styles:'text-align:center;', width:'150px' }
                ] : [
                    {name: 'No.', field: 'id', width:'30px', styles:'text-align:center;', formatter: formatter.rowCountCellFormatter },
                    {name: nls.wastage, field: 'wastage-value', width:'150px', styles:'text-align:right;', editable:true, cellType:'buildspace.widget.grid.cells.FormulaTextBox', formatter: formatter.formulaNumberCellFormatter},
                    {name: nls.noOfBillItems, field: 'no_bill_items', styles:'text-align:center;', width:'150px' }
                ],
                store = dojo.data.ItemFileWriteStore({
                    url: "projectAnalyzer/getValuesFromResourceAndProject/cname/"+this.columnName+"/rid/"+this.resourceId+"/pid/"+this.projectId
                }),
                content = MultiRateAnalyzerGrid({
                    resourceStore: this.resourceStore,
                    resourceId: this.resourceId,
                    projectId: this.projectId,
                    store: store,
                    structure: structure
                });

            var contentPane = new dijit.layout.ContentPane({
                content: content,
                style:'width:100%;height:100%;border:0px;',
                region: 'center'
            });

            borderContainer.addChild(toolbar);
            borderContainer.addChild(contentPane);

            return borderContainer;
        }
    });
});