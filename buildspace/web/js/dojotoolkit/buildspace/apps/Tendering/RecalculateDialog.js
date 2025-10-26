define('buildspace/apps/Tendering/RecalculateDialog',[
    'dojo/_base/declare',
    'dojo/_base/lang',
    'dojo/keys',
    "dojo/dom-style",
    "dojo/store/Memory",
    "dojo/data/ObjectStore",
    "buildspace/widget/grid/cells/Formatter",
    'dojo/i18n!buildspace/nls/Tendering'
], function(declare, lang, keys, domStyle, Memory, ObjectStore, GridFormatter, nls){

    var RecalculateGrid = declare('buildspace.apps.Tendering.RecalculateDialogGrid', dojox.grid.EnhancedGrid, {
        rootProject: null,
        billId: 0,
        region: 'center',
        _csrf_token: null,
        dialog: null,
        projectBreakDownGrid: null,
        style: "border-top:none;",
        canSort: function(inSortInfo){
            return false;
        },
        postCreate: function(){
            var self = this;
            this.inherited(arguments);
            this.on("RowClick", function(e){
                var colField = e.cell.field,
                    rowIndex = e.rowIndex,
                    _item = this.getItem(rowIndex);
                if(colField == 'recalculate' && _item.recalculate){

                    var pb = buildspace.dialog.indeterminateProgressBar({
                        title:nls.recalculating+'. '+nls.pleaseWait+'...'
                    });

                    pb.show();
                    dojo.xhrPost({
                        url: 'projectAnalyzer/recalculate',
                        content: {bill_id: self.billId, level: _item.id, _csrf_token: self._csrf_token },
                        handleAs: 'json',
                        load: function(resp) {
                            if(resp.success){
                                var recalculateStore = self.store;

                                self.projectBreakDownGrid.reload();

                                recalculateStore.setValue(_item, 'recalculate', false);
                                recalculateStore.setValue(_item, 'bill_status', resp.bill_status);

                                if(_item.id == 'item' || _item.id == 'element'){
                                    var id = _item.id == 'item' ? 'element' : 'bill';
                                    recalculateStore.fetchItemByIdentity({ 'identity' : id,  onItem : function(item){
                                        recalculateStore.setValue(item, 'recalculate', true);
                                    }});
                                }

                                recalculateStore.save();

                                if(self.dialog && resp.bill_status == buildspace.apps.Tendering.ProjectStructureConstants.BILL_STATUS_OPEN){
                                    var resourceAnalysisMenuItm = dijit.byId('resourceAnalysis-'+self.rootProject.id+'-menuItem'),
                                        scheduleOfRateAnalysisMenuItm = dijit.byId('scheduleOfRateAnalysis-'+self.rootProject.id+'-menuItem');

                                    if(resourceAnalysisMenuItm)
                                        resourceAnalysisMenuItm.set("disabled",false);

                                    if(scheduleOfRateAnalysisMenuItm)
                                        scheduleOfRateAnalysisMenuItm.set("disabled",false);

                                    self.dialog.hide();
                                }
                            }
                            pb.hide();
                        },
                        error: function(error) {
                            pb.hide();
                        }
                    });

                }
            });
        },
        dodblclick: function(e){
            this.onRowDblClick(e);
        }
    });

    return declare('buildspace.apps.Tendering.RecalculateDialog', dijit.Dialog, {
        style:"padding:0px;margin:0px;",
        rootProject: null,
        bill: null,
        projectBreakDownGrid: null,
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
            var key = e.keyCode;
            if (key == keys.ESCAPE) {
                dojo.stopEvent(e);
            }
        },
        onHide: function() {
            this.destroyRecursive();
        },
        createContent: function(){
            var bill = this.bill,
                itemRecalculate = bill['bill_status'][0] == buildspace.apps.Tendering.ProjectStructureConstants.BILL_STATUS_RESOURCE_ANALYSIS_RECALCULATE_ITEM || bill['bill_status'][0] == buildspace.apps.Tendering.ProjectStructureConstants.BILL_STATUS_SCHEDULE_OF_RATE_ANALYSIS_RECALCULATE_ITEM ? true : false,
                elementRecalculate = bill['bill_status'][0] == buildspace.apps.Tendering.ProjectStructureConstants.BILL_STATUS_RESOURCE_ANALYSIS_RECALCULATE_ELEMENT || bill['bill_status'][0] == buildspace.apps.Tendering.ProjectStructureConstants.BILL_STATUS_SCHEDULE_OF_RATE_ANALYSIS_RECALCULATE_ELEMENT ? true : false,
                billRecalculate = bill['bill_status'][0] == buildspace.apps.Tendering.ProjectStructureConstants.BILL_STATUS_RESOURCE_ANALYSIS_RECALCULATE_BILL || bill['bill_status'][0] == buildspace.apps.Tendering.ProjectStructureConstants.BILL_STATUS_SCHEDULE_OF_RATE_ANALYSIS_RECALCULATE_BILL ? true : false,
                borderContainer = new dijit.layout.BorderContainer({
                    style:"padding:0px;width:500px;height:200px;",
                    gutters: false
                });

            var toolbar = new dijit.Toolbar({
                region: "top",
                style: "outline:none!important;border:none;padding:2px;overflow:hidden;"
            });

            toolbar.addChild(
                new dijit.form.Button({
                    label: nls.close,
                    iconClass: "icon-16-container icon-16-close",
                    style:"outline:none!important;",
                    onClick: dojo.hitch(this, 'hide')
                })
            );

            var formatter = new GridFormatter();

            borderContainer.addChild(toolbar);
            borderContainer.addChild(new RecalculateGrid({
                rootProject: this.rootProject,
                billId: bill.id,
                _csrf_token: bill._csrf_token,
                store: new ObjectStore({
                    objectStore: Memory({
                        data: [
                            {id: 'item', level: nls.levelBillItems, bill_status: bill['bill_status'][0], recalculate: itemRecalculate },
                            {id: 'element', level: nls.levelBillElements, bill_status: bill['bill_status'][0], recalculate: elementRecalculate },
                            {id: 'bill', level: nls.levelBills, bill_status: bill['bill_status'][0], recalculate: billRecalculate }
                        ]
                    })
                }),
                dialog: this,
                projectBreakDownGrid: this.projectBreakDownGrid,
                structure: [
                    {name: 'No.', field: 'id', width:'30px', styles:'text-align:center;', formatter: formatter.rowCountCellFormatter },
                    {name: nls.level, field: 'level', width:'auto' },
                    {name: nls.recalculate, field: 'recalculate', width:'100px', styles:'text-align:center;', formatter: formatter.recalculateCellFormatter }
                ]
            }));

            return borderContainer;
        }
    });
});