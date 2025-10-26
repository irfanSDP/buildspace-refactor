define('buildspace/apps/ProjectBuilder/SupplyOfMaterialBillManager',[
    'dojo/_base/declare',
    "dojo/dom-style",
    "dojo/when",
    "dijit/layout/ContentPane",
    'dojox/grid/EnhancedGrid',
    './SupplyOfMaterial/BillPropertiesForm',
    "./SupplyOfMaterialBillPrintoutSetting/masterContainer",
    './SupplyOfMaterial/BillGrid',
    "buildspace/widget/grid/cells/Formatter",
    'dojo/aspect',
    'dojo/i18n!buildspace/nls/ProjectBuilder'],
    function(declare, domStyle, when, ContentPane, EnhancedGrid, BillPropertiesForm, BillPrintoutSettingMasterContainer, BillGrid, GridFormatter, aspect, nls) {

        var BillPropertiesContainer = declare('buildspace.apps.ProjectBuilder.SupplyOfMaterialBillPropertiesContainer', dijit.layout.BorderContainer, {
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

        var BillElementContainer = declare('buildspace.apps.ProjectBuilder.SupplyOfMaterialBillElementContainer', dijit.layout.BorderContainer, {
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

                dojo.subscribe('supplyOfMaterialGrid' + billId + '-stackContainer-selectChild', "", function(page) {
                    var widget = dijit.byId('supplyOfMaterialGrid' + billId + '-stackContainer');
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
                var stackContainer = dijit.byId('supplyOfMaterialGrid' + this.billId + '-stackContainer');
                if(stackContainer) {
                    dijit.byId('supplyOfMaterialGrid' + this.billId + '-stackContainer').destroyRecursive();
                }
                stackContainer = this.stackContainer = new dijit.layout.StackContainer({
                    style: 'border:0px;width:100%;height:100%;',
                    region: "center",
                    id: 'supplyOfMaterialGrid' + this.billId + '-stackContainer'
                });
                var store = dojo.data.ItemFileWriteStore({
                        url: "supplyOfMaterial/getElementList/id/" + this.billId,
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
                        pageId: 'som_element-page-' + me.billId,
                        id: 'som_element-page-container-' + me.billId,
                        gridOpts: {
                            store: store,
                            addUrl: 'supplyOfMaterial/elementAdd',
                            updateUrl: 'supplyOfMaterial/elementUpdate',
                            rowUpdateUrl: 'supplyOfMaterial/elementRowUpdate',
                            deleteUrl: 'supplyOfMaterial/elementDelete',
                            pasteUrl: 'supplyOfMaterial/elementPaste',
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
                        containerId: 'supplyOfMaterialGrid' + me.billId + '-stackContainer'
                    });

                    me.addChild(stackContainer);

                    me.addChild(new dijit.layout.ContentPane({
                        style: "padding:0px;overflow:hidden;",
                        baseClass: 'breadCrumbTrail',
                        region: 'top',
                        id: 'supplyOfMaterialGrid'+me.billId+'-controllerPane',
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
                        url:"supplyOfMaterial/getItemList/id/"+element.id+"/bill_id/"+this.billId,
                        clearOnClose: true
                    }),
                    unitQuery = dojo.xhrGet({
                        url: "supplyOfMaterial/getUnits/billId/"+ this.billId,
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
                                id: 'som_item-page-container-' + self.billId,
                                elementId: element.id,
                                pageId: 'som_item-page-' + self.billId,
                                type: 'tree',
                                gridOpts: {
                                    store: store,
                                    elementGridStore: elementGridStore,
                                    hierarchyTypes: hierarchyTypes,
                                    hierarchyTypesForHead: hierarchyTypesForHead,
                                    unitOfMeasurements: uom,
                                    addUrl: 'supplyOfMaterial/itemAdd',
                                    updateUrl: 'supplyOfMaterial/itemUpdate',
                                    rowUpdateUrl: 'supplyOfMaterial/itemRowUpdate',
                                    deleteUrl: 'supplyOfMaterial/itemDelete',
                                    pasteUrl: 'supplyOfMaterial/itemPaste',
                                    indentUrl: 'supplyOfMaterial/itemIndent',
                                    outdentUrl: 'supplyOfMaterial/itemOutdent',
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
                dijit.byId('supplyOfMaterialGrid'+this.billId+'-controllerPane').destroyRecursive();
                dijit.byId('supplyOfMaterialGrid'+this.billId+'-stackContainer').destroyRecursive();

                this.createElementGrid();
            }
        });

        var BillPrintoutSettingContainer = declare('buildspace.apps.ProjectBuilder.SupplyOfMaterialBillPrintOutSettingContainer', dijit.layout.BorderContainer, {
            style: "padding:0px;width:100%;margin:0px;border:0px;height:100%;",
            gutters: false,
            somBillLayoutSettingId: null,
            postCreate: function() {
                this.inherited(arguments);
                this.addChild(new BillPrintoutSettingMasterContainer({
                    somBillLayoutSettingId: String(this.somBillLayoutSettingId)
                }));
            }
        });

        return declare('buildspace.apps.ProjectBuilder.SupplyOfMaterialBillManager', dijit.layout.TabContainer, {
            region: "center",
            rootProject: null,
            nested: true,
            style: "padding:0;border:none;margin:0;width:100%;height:100%;",
            billId: null,
            somBillLayoutSettingId: null,
            projectBreakdownGrid: null,
            tenderAlternativeBillGridId: null,
            postCreate: function() {
                this.inherited(arguments);
                var billElementContainer = BillElementContainer({
                    id: 'som_element_container_'+this.rootProject.id+'-bill-'+this.billId,
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
                        somBillLayoutSettingId: this.somBillLayoutSettingId
                    })
                }));
            }
        });
    });