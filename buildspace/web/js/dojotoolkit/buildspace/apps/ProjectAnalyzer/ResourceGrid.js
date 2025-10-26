define('buildspace/apps/ProjectAnalyzer/ResourceGrid',[
    'dojo/_base/declare',
    'dojo/_base/lang',
    "buildspace/widget/grid/plugins/FormulatedColumn",
    'buildspace/apps/ProjectAnalyzer/plugins/TotalPane',
    'buildspace/apps/ProjectAnalyzer/MultiRateAnalyzerDialog',
    'dojo/i18n!buildspace/nls/ProjectAnalyzer'
], function(declare, lang, FormulatedColumn, TotalPane, MultiRateAnalyzerDialog, nls){

    var ResourceGrid = declare('buildspace.apps.ProjectAnalyzer.ResourceEnhancedGrid', dojox.grid.EnhancedGrid, {
        type: null,
        style: "border-top:none;",
        rowSelector: '0px',
        region: 'center',
        updateUrl: null,
        project: null,
        unsorted: false,
        canSort: function(inSortInfo){
            return false;
        },
        postCreate: function(){
            this.formulatedColumn = FormulatedColumn(this,{});
            this.inherited(arguments);
        },
        canEdit: function(inCell, inRowIndex){
            var self = this;

            if(this.type=='editable'){
                if(inCell != undefined){
                    var item = this.getItem(inRowIndex);
                    if(item.type < 1 || item.id[0] <= 0 || (inCell.field == 'rate-value' && item.hasOwnProperty('multi-rate') && item['multi-rate'][0]) || (inCell.field == 'wastage-value' && item.hasOwnProperty('multi-wastage') && item['multi-wastage'][0]) || (item.hasOwnProperty('type') && (item.type[0] == buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER || item.type[0] == buildspace.widget.grid.constants.HIERARCHY_TYPE_NOID))){
                        if((inCell.field == 'rate-value' && item.hasOwnProperty('multi-rate') && item['multi-rate'][0]) || (inCell.field == 'wastage-value' && item.hasOwnProperty('multi-wastage') && item['multi-wastage'][0]))
                        {
                            var title = inCell.field == 'rate-value' ? nls.ratesForResource : nls.wastageForResource;
                            var dialog = MultiRateAnalyzerDialog({
                                resourceStore: self.store,
                                resourceId: item.id,
                                projectId: self.project.id,
                                columnName: inCell.field == 'rate-value' ? 'rate' : 'wastage',
                                title: title+' '+item.description
                            });
                            dialog.show();
                        }
                        window.setTimeout(function() {
                            self.edit.cancel();
                            self.focus.setFocusIndex(inRowIndex, inCell.index);
                        }, 10);
                        return;
                    }
                }
            }
            return this._canEdit;
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
                    project_id: self.project.id,
                    unsorted: self.unsorted,
                    attr_name: attrNameParsed,
                    val: val,
                    _csrf_token: item._csrf_token ? item._csrf_token : null
                }, url = this.updateUrl;

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
                            pb.hide();
                        }
                    },
                    error: function(error) {
                        pb.hide();
                    }
                };
                pb.show();
                dojo.xhrPost(xhrArgs);
            }
            self.inherited(arguments);
        }
    });

    return declare('buildspace.apps.ProjectAnalyzer.ResourceGrid', dijit.layout.BorderContainer, {
        stackContainerTitle: '',
        style: "padding:0px;width:100%;height:100%;",
        gutters: false,
        project: null,
        gridOpts: {},
        pageId: 0,
        postCreate: function(){
            this.inherited(arguments);
            lang.mixin(this.gridOpts, {project: this.project });

            var grid = this.grid = new ResourceGrid(this.gridOpts);
            this.addChild(new dijit.layout.ContentPane({style: 'width:100%', content:grid, region:'center'}));

            var container = dijit.byId('projectAnalyzer-resource_project_'+this.project.id+'-stackContainer');
            if(container){
                var node = document.createElement("div");
                var child = new dojox.layout.ContentPane({
                    title: buildspace.truncateString(this.stackContainerTitle, 60),
                    id: this.pageId,
                    executeScripts: true,
                    content: this
                },node );
                container.addChild(child);
                lang.mixin(child, {grid: grid});
                container.selectChild(this.pageId);
            }
        }
    });
});