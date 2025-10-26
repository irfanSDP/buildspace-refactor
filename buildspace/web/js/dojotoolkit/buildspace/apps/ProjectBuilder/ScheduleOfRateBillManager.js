define('buildspace/apps/ProjectBuilder/ScheduleOfRateBillManager',[
    'dojo/_base/declare',
    "dojo/dom-style",
    "dojo/when",
    "dijit/layout/ContentPane",
    'dojox/grid/EnhancedGrid',
    './ScheduleOfRateBill/BillPropertiesForm',
    "./ScheduleOfRateBill/BillPrintoutSetting",
    './ScheduleOfRateBill/BillGrid',
    "buildspace/widget/grid/cells/Formatter",
    'dojo/aspect',
    'dojo/i18n!buildspace/nls/ProjectBuilder'],
    function(declare, domStyle, when, ContentPane, EnhancedGrid, BillPropertiesForm, BillPrintoutSetting, BillGrid, GridFormatter, aspect, nls) {

        var BillPropertiesContainer = declare('buildspace.apps.ProjectBuilder.ScheduleOfRateBillPropertiesContainer', dijit.layout.BorderContainer, {
            style: "padding:0px;width:100%;margin:0px;border:0px;height:100%;",
            gutters: false,
            billId: null,
            billType: null,
            projectBreakdownGrid: null,
            tenderAlternativeBillGridId: null,
            billElementGrid: null,
            postCreate: function() {
                this.inherited(arguments);

                var billPropertiesForm = this.billPropertiesForm = BillPropertiesForm({
                    billId: this.billId,
                    projectBreakdownGrid: this.projectBreakdownGrid,
                    tenderAlternativeBillGridId: this.tenderAlternativeBillGridId,
                    billElementGrid: this.billElementGrid
                });
                this.addChild(billPropertiesForm);
            }
        });

        var BillElementContainer = declare('buildspace.apps.ProjectBuilder.ScheduleOfRateBillElementContainer', dijit.layout.BorderContainer, {
            style: "padding:0;margin:0px;border:none;width:100%;height:100%;",
            gutters: false,
            billId: null,
            rootProject: null,
            projectBreakdownGrid: null,
            tenderAlternativeBillGridId: null,
            currentBillLockedStatus: false,
            postCreate: function() {
                this.inherited(arguments);
                this.createElementGrid();
                var billId = this.billId;

                dojo.subscribe('scheduleOfRateBillGrid' + billId + '-stackContainer-selectChild', "", function(page) {
                    var widget = dijit.byId('scheduleOfRateBillGrid' + billId + '-stackContainer');
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
                var stackContainer = dijit.byId('scheduleOfRateBillGrid' + this.billId + '-stackContainer');
                if(stackContainer) {
                    dijit.byId('scheduleOfRateBillGrid' + this.billId + '-stackContainer').destroyRecursive();
                }
                stackContainer = this.stackContainer = new dijit.layout.StackContainer({
                    style: 'border:0px;width:100%;height:100%;',
                    region: "center",
                    id: 'scheduleOfRateBillGrid' + this.billId + '-stackContainer'
                });
                var store = dojo.data.ItemFileWriteStore({
                        url: "scheduleOfRateBill/getElementList/id/" + this.billId,
                        clearOnClose: true,
                        urlPreventCache:true
                    }),
                    me = this;

                try {
                    // current bill lock status
                    if(me.rootProject.status_id != buildspace.constants.STATUS_PRETENDER)
                        me.currentBillLockedStatus = true;

                    var grid = new BillGrid({
                        stackContainerTitle: "Element",
                        billId: me.billId,
                        pageId: 'sorb_element-page-' + me.billId,
                        id: 'sorb_element-page-container-' + me.billId,
                        gridOpts: {
                            store: store,
                            addUrl: 'scheduleOfRateBill/elementAdd',
                            updateUrl: 'scheduleOfRateBill/elementUpdate',
                            rowUpdateUrl: 'scheduleOfRateBill/elementRowUpdate',
                            deleteUrl: 'scheduleOfRateBill/elementDelete',
                            pasteUrl: 'scheduleOfRateBill/elementPaste',
                            currentBillLockedStatus: me.currentBillLockedStatus,
                            currentGridType: 'element',
                            onRowDblClick: function(e) {
                                var self = this,
                                    item = self.getItem(e.rowIndex);
                                if(item.id[0] > 0 && item.description[0] !== null && item.description[0] !== '') {
                                    me.createItemGrid(item, grid);
                                }
                            }
                        }
                    });

                    var controller = new dijit.layout.StackController({
                        region: "top",
                        containerId: 'scheduleOfRateBillGrid' + me.billId + '-stackContainer'
                    });

                    me.addChild(stackContainer);

                    me.addChild(new dijit.layout.ContentPane({
                        style: "padding:0px;overflow:hidden;",
                        baseClass: 'breadCrumbTrail',
                        region: 'top',
                        id: 'scheduleOfRateBillGrid'+me.billId+'-controllerPane',
                        content: controller
                    }));
                }catch(e){
                    console.debug(e)
                }
            },
            createItemGrid: function(element, elementGridStore){
                var self = this,
                    hierarchyTypes = {
                        options: [
                            buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER_N_TEXT,
                            buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER_TEXT,
                            buildspace.widget.grid.constants.HIERARCHY_TYPE_WORK_ITEM_TEXT
                        ],
                        values: [
                            buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER_N,
                            buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER,
                            buildspace.widget.grid.constants.HIERARCHY_TYPE_WORK_ITEM
                        ]
                    },
                    hierarchyTypesForHead = {
                        options: [
                            buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER_N_TEXT,
                            buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER_TEXT
                        ],
                        values: [
                            buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER_N,
                            buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER
                        ]
                    },
                    store = new dojo.data.ItemFileWriteStore({
                        url:"scheduleOfRateBill/getItemList/id/"+element.id+"/bill_id/"+this.billId,
                        clearOnClose: true
                    }),
                    unitQuery = dojo.xhrGet({
                        url: "scheduleOfRateBill/getUnits/billId/"+ this.billId,
                        handleAs: "json"
                    }),
                    pb = buildspace.dialog.indeterminateProgressBar({
                        title:nls.pleaseWait+'...'
                    });

                pb.show().then(function(){
                    when(unitQuery, function(uom){
                        pb.hide();
                        try{
                            new BillGrid({
                                stackContainerTitle: element.description,
                                billId: self.billId,
                                id: 'sorb_item-page-container-' + self.billId,
                                elementId: element.id,
                                pageId: 'sorb_item-page-' + self.billId,
                                type: 'tree',
                                gridOpts: {
                                    store: store,
                                    elementGridStore: elementGridStore,
                                    hierarchyTypes: hierarchyTypes,
                                    hierarchyTypesForHead: hierarchyTypesForHead,
                                    unitOfMeasurements: uom,
                                    addUrl: 'scheduleOfRateBill/itemAdd',
                                    updateUrl: 'scheduleOfRateBill/itemUpdate',
                                    rowUpdateUrl: 'scheduleOfRateBill/itemRowUpdate',
                                    deleteUrl: 'scheduleOfRateBill/itemDelete',
                                    pasteUrl: 'scheduleOfRateBill/itemPaste',
                                    indentUrl: 'scheduleOfRateBill/itemIndent',
                                    outdentUrl: 'scheduleOfRateBill/itemOutdent',
                                    currentBillLockedStatus: self.currentBillLockedStatus,
                                    currentGridType: 'item'
                                }
                            });
                        }catch(e){console.debug(e)}
                    },function(error){
                        //got fucked
                    });
                });
            },
            reconstructBillContainer: function() {
                dijit.byId('scheduleOfRateBillGrid'+this.billId+'-controllerPane').destroyRecursive();
                dijit.byId('scheduleOfRateBillGrid'+this.billId+'-stackContainer').destroyRecursive();

                this.createElementGrid();
            }
        });

        var BillPrintoutSettingContainer = declare('buildspace.apps.ProjectBuilder.ScheduleOfRateBillPrintOutSettingContainer', dijit.layout.BorderContainer, {
            style: "padding:0px;width:100%;margin:0px;border:0px;height:100%;",
            gutters: false,
            sorBillLayoutSettingId: null,
            postCreate: function() {
                this.inherited(arguments);
                this.addChild(new BillPrintoutSetting({
                    sorBillLayoutSettingId: String(this.sorBillLayoutSettingId)
                }));
            }
        });

        return declare('buildspace.apps.ProjectBuilder.ScheduleOfRateBillManager', dijit.layout.TabContainer, {
            region: "center",
            rootProject: null,
            nested: true,
            style: "padding:0;border:none;margin:0;width:100%;height:100%;",
            billId: null,
            projectBreakdownGrid: null,
            tenderAlternativeBillGridId: null,
            sorBillLayoutSettingId: null,
            postCreate: function() {
                this.inherited(arguments);
                var billElementContainer = BillElementContainer({
                    id: 'sorb_element_container_'+this.rootProject.id+'-bill-'+this.billId,
                    rootProject: this.rootProject,
                    billId: this.billId,
                    bqVersion: 0,
                    projectBreakdownGrid: this.projectBreakdownGrid,
                    tenderAlternativeBillGridId: this.tenderAlternativeBillGridId
                });

                this.addChild(new ContentPane({
                    style: "padding:0px;margin:0px;border:0px;width:100%;height:100%;",
                    title: nls.elementTradeList,
                    content: billElementContainer
                }));

                this.addChild(new ContentPane({
                    style: "padding:0px;margin:0px;border:0px;width:100%;height:100%;",
                    title: nls.billProperties,
                    content: new BillPropertiesContainer({
                        billId: this.billId,
                        projectBreakdownGrid: this.projectBreakdownGrid,
                        tenderAlternativeBillGridId: this.tenderAlternativeBillGridId,
                        billType: this.billType,
                        billElementGrid: null
                    })
                }));

                this.addChild(new ContentPane({
                    style: "padding:0px;margin:0px;border:0px;width:100%;height:100%;",
                    title: nls.printOutSettingTabDesc,
                    content: new BillPrintoutSettingContainer({
                        rootProject: this.rootProject,
                        sorBillLayoutSettingId: this.sorBillLayoutSettingId
                    })
                }));
            }
        });
    });