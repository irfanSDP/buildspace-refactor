define('buildspace/apps/Editor/BillManager',[
    'dojo/_base/declare',
    "dojo/dom-style",
    "dojo/when",
    "dojo/currency",
    "dijit/layout/AccordionContainer",
    "dijit/layout/ContentPane",
    'dojox/grid/EnhancedGrid',
    './BillManager/BillPropertiesForm',
    './BillManager/BillGrid',
    "buildspace/apps/Tendering/BillManager/primeCostRateDialog",
    "buildspace/apps/Tendering/BillManager/lumpSumPercentDialog",
    "buildspace/widget/grid/cells/Formatter",
    'dojo/aspect',
    'dojo/i18n!buildspace/nls/Tendering'],
    function(declare, domStyle, when, currency, AccordionContainer, ContentPane, EnhancedGrid, BillPropertiesForm, BillGrid, PrimeCostRateDialog, LumpSumPercentDialog, GridFormatter, aspect, nls) {

    var BillPrintingSettingContainer = declare('buildspace.apps.Editor.BillPrintingSettingContainer', dijit.layout.BorderContainer, {
        style: "padding:0px;width:100%;margin:0px;border:0px;height:100%;",
        gutters: false,
        billId: null,
        postCreate: function() {
            this.inherited(arguments);
        }
    });

    var BillPropertiesContainer = declare('buildspace.apps.Editor.BillPropertiesContainer', dijit.layout.BorderContainer, {
        style: "padding:0px;width:100%;margin:0px;border:0px;height:100%;",
        gutters: false,
        bill: null,
        projectBreakdownGrid: null,
        billElementObj: null,
        postCreate: function() {
            this.inherited(arguments);

            var billPropertiesForm = this.billPropertiesForm = BillPropertiesForm({
                billId: String(this.bill.id),
                projectBreakdownGrid: this.projectBreakdownGrid,
                billElementObj: this.billElementObj,
                locked: true
            });
            this.addChild(billPropertiesForm);
        }
    });

    var BillElementContainer = declare('buildspace.apps.Editor.BillElementContainer', dijit.layout.BorderContainer, {
        style: "padding:0;margin:0;border:none;width:100%;height:100%;",
        gutters: false,
        bill: null,
        project: null,
        columnData: null,
        projectBreakdownGrid: null,
        currentPrintableRevision: null,
        currentBillVersion: 0,
        postCreate: function() {
            this.inherited(arguments);
            this.createElementGrid();
            var billId = String(this.bill.id);

            dojo.subscribe('billGrid' + billId + '-stackContainer-selectChild', "", function(page) {
                var widget = dijit.byId('billGrid' + billId + '-stackContainer');
                if(widget) {
                    var children = widget.getChildren(),
                        index = dojo.indexOf(children, page);

                    index = index + 1;

                    if(children.length > index){
                        while(children.length > index) {
                            widget.removeChild(children[index]);
                            children[index].destroyDescendants();
                            children[index].destroyRecursive();

                            index = index + 1;
                        }

                        if(page.grid){
                            var selectedIndex = page.grid.selection.selectedIndex;

                            page.grid.store.save();
                            page.grid.store.close();

                            var handle = aspect.after(page.grid, "_onFetchComplete", function() {
                                handle.remove();
                                if(selectedIndex > -1){
                                    this.scrollToRow(selectedIndex);
                                    this.selection.setSelected(selectedIndex, true);
                                }
                            });

                            page.grid.sort();
                        }
                    }
                }
            });
        },
        createElementGrid: function(){
            var stackContainer = dijit.byId('billGrid' + String(this.bill.id) + '-stackContainer');
            if(stackContainer) {
                dijit.byId('billGrid' + String(this.bill.id) + '-stackContainer').destroyRecursive();
            }
            stackContainer = this.stackContainer = new dijit.layout.StackContainer({
                style: 'border:0px;width:100%;height:100%;',
                region: "center",
                id: 'billGrid' + String(this.bill.id) + '-stackContainer'
            });
            var store = dojo.data.ItemFileWriteStore({
                url: "billElements/" + String(this.bill.id),
                clearOnClose: true,
                urlPreventCache:true
            }),
            billInfoQuery = dojo.xhrGet({
                url: "billInfo/"+String(this.bill.id),
                handleAs: "json"
            }),
            me = this;

            billInfoQuery.then(function(billInfo) {
                me.currentPrintableRevision = billInfo.printable_project_revision;
                me.currentBillVersion       = billInfo.current_bill_version;
                me.currentBillType          = billInfo.bill_type.type;

                var grid = new BillGrid({
                    stackContainerTitle: "Element",
                    bill: me.bill,
                    project: me.project,
                    pageId: 'element-page-' + String(me.bill.id),
                    id: 'element-page-container-' + String(me.bill.id),
                    gridOpts: {
                        store: store,
                        typeColumns : billInfo.column_settings,
                        bqCSRFToken: billInfo._csrf_token,
                        currentPrintableRevision: me.currentPrintableRevision,
                        currentBillVersion: me.currentBillVersion,
                        currentBillType: me.currentBillType,
                        currentGridType: 'element',
                        onRowDblClick: function(e) {
                            var self = this,
                                item = self.getItem(e.rowIndex);
                            if(item && !isNaN(parseInt(item.id.toString())) && item.description.toString() !== null && item.description.toString() !== '') {
                                me.createItemGrid(item, billInfo, grid);
                            }
                        }
                    }
                });

                var controller = new dijit.layout.StackController({
                    region: "top",
                    containerId: 'billGrid' + String(me.bill.id) + '-stackContainer'
                });

                me.addChild(stackContainer);

                me.addChild(new dijit.layout.ContentPane({
                    style: "padding:0px;overflow:hidden;",
                    baseClass: 'breadCrumbTrail',
                    region: 'top',
                    id: 'billGrid'+String(me.bill.id)+'-controllerPane',
                    content: controller
                }));
            });
        },
        createItemGrid: function(element, billInfo, elementGridStore){
            var self = this,
                store = new dojo.data.ItemFileWriteStore({
                    url:"billItems/"+element.id,
                    clearOnClose: true
                }),
                unitQuery = dojo.xhrGet({
                    url: "billUnits/"+ String(self.bill.id),
                    handleAs: "json"
                }),
                pb = buildspace.dialog.indeterminateProgressBar({
                    title:nls.pleaseWait+'...'
                });

                pb.show().then(function(){
                    when(unitQuery, function(uom){
                        pb.hide();
                        new BillGrid({
                            stackContainerTitle: element.description,
                            bill: self.bill,
                            project: self.project,
                            id: 'item-page-container-' + String(self.bill.id),
                            elementId: element.id,
                            pageId: 'item-page-' + String(self.bill.id),
                            type: 'tree',
                            gridOpts: {
                                store: store,
                                escapeHTMLInData: false,
                                typeColumns : billInfo.column_settings,
                                unitOfMeasurements: uom,
                                elementGridStore: elementGridStore,
                                rowUpdateUrl: 'billManager/itemUpdate',
                                currentBillType: self.currentBillType,
                                currentBillVersion: self.currentBillVersion,
                                currentGridType: 'item',
                                editableCellDblClick: function(e) {
                                    var colField = e.cell.field,
                                        rowIndex = e.rowIndex,
                                        item = this.getItem(rowIndex),
                                        billGridStore = this.store;

                                    if (item && (item.project_revision_deleted_at !== undefined && item.project_revision_deleted_at[0])){
                                        return false;
                                    }

                                    if(item && !isNaN(parseInt(item.id.toString())) && colField == "rate-value"){
                                        var dialog;
                                        switch(item.type.toString()){
                                            case buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_PC_RATE:
                                                dialog = new PrimeCostRateDialog({
                                                    itemObj: item,
                                                    billGridStore: billGridStore,
                                                    elementGridStore: elementGridStore,
                                                    currentBillLockedStatus: self.currentBillLockedStatus,
                                                    currentBillVersion: self.currentBillVersion,
                                                    currentItemVersion: item.version.toString(),
                                                    disableEditingMode: true
                                                });
                                                break;
                                            case buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_LUMP_SUM_PERCENT:
                                                dialog = new LumpSumPercentDialog({
                                                    itemObj: item,
                                                    billGridStore: billGridStore,
                                                    elementGridStore: elementGridStore,
                                                    currentBillLockedStatus: self.currentBillLockedStatus,
                                                    currentBillVersion: self.currentBillVersion,
                                                    currentItemVersion: item.version.toString(),
                                                    disableEditingMode: true
                                                });
                                                break;
                                        }

                                        if(dialog){
                                            dialog.show();
                                        }
                                    }
                                }
                            }
                        });
                    });
                });
        },
        reconstructBillContainer: function() {
            dijit.byId('billGrid'+String(this.bill.id)+'-controllerPane').destroyRecursive();
            dijit.byId('billGrid'+String(this.bill.id)+'-stackContainer').destroyRecursive();

            this.createElementGrid();
        }
    });

    return declare('buildspace.apps.Editor.BillManager', dijit.layout.TabContainer, {
        region: "center",
        project: null,
        nested: true,
        style: "padding:0;border:none;margin:0;width:100%;height:100%;",
        bill: null,
        billLayoutSettingId: null,
        projectBreakdownGrid: null,
        postCreate: function() {
            this.inherited(arguments);

            this.addChild(new BillElementContainer({
                id: 'bill_element_container_'+this.project.id+'-bill-'+String(this.bill.id),
                title: nls.elementTradeList,
                project: this.project,
                bill: this.bill,
                projectBreakdownGrid: this.projectBreakdownGrid
            }));

            this.addChild(new BillPropertiesContainer({
                title: nls.billProperties,
                bill: this.bill,
                projectBreakdownGrid: this.projectBreakdownGrid,
                billType: this.billType
            }));
        }
    });
});
