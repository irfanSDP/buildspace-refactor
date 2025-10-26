define('buildspace/apps/ProjectAnalyzer/ScheduleOfRateGrid',[
    'dojo/_base/declare',
    'dojo/_base/lang',
    "buildspace/widget/grid/plugins/FormulatedColumn",
    'dojo/i18n!buildspace/nls/ProjectAnalyzer'
], function(declare, lang, FormulatedColumn, nls){

    var ScheduleOfRateGrid = declare('buildspace.apps.ProjectAnalyzer.ScheduleOfRateEnhancedGrid', dojox.grid.EnhancedGrid, {
        type: null,
        style: "border-top:none;",
        rowSelector: '0px',
        region: 'center',
        updateUrl: null,
        project: null,
        unsorted: false,
        isScheduleOfRateItemGrid: false,
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
                    if(item.id[0] <= 0 || (item.hasOwnProperty('type') && (item.type[0] == buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER || item.type[0] == buildspace.widget.grid.constants.HIERARCHY_TYPE_NOID || item.type[0] < 1)) || (item.hasOwnProperty('rate-has_build_up') && item['rate-has_build_up'][0] && inCell.field == 'rate-value')){
                        window.setTimeout(function() {
                            self.edit.cancel();
                            self.focus.setFocusIndex(inRowIndex, inCell.index);
                        }, 10);
                        return;
                    }else if(item.id[0] > 0 && item.hasOwnProperty('rate-has_build_up') && item['rate-has_build_up'][0] && inCell.field == 'rate' && this.isScheduleOfRateItemGrid){
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
                        console.log(error);
                    }
                };
                pb.show();
                dojo.xhrPost(xhrArgs);
            }
            self.inherited(arguments);
        },
        onStyleRow: function(e) {
            this.inherited(arguments);
            if(e.node.children[0].children[0].rows.length >= 2){
                dojo.style(e.node.children[0].children[0].rows[1],'display','none');
            }
        }
    });

    return declare('buildspace.apps.ProjectAnalyzer.ScheduleOfRateGrid', dijit.layout.BorderContainer, {
        stackContainerTitle: '',
        style: "padding:0px;width:100%;height:100%;",
        gutters: false,
        project: null,
        gridOpts: {},
        pageId: 0,
        postCreate: function(){
            this.inherited(arguments);
            lang.mixin(this.gridOpts, {project: this.project });

            var grid = this.grid = new ScheduleOfRateGrid(this.gridOpts);
            this.addChild(new dijit.layout.ContentPane({style: 'width:100%', content:grid, region:'center'}));

            var container = dijit.byId('projectAnalyzer-sor_project_'+this.project.id+'-stackContainer');
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