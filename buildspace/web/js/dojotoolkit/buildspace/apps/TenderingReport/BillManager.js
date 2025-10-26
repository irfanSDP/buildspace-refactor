define('buildspace/apps/TenderingReport/BillManager',[
    'dojo/_base/declare',
    "dojo/dom-style",
    "dojo/when",
    "dojo/request",
    "dojo/currency",
    'dojo/store/Memory',
    "dijit/layout/AccordionContainer",
    "dijit/layout/ContentPane",
    'dojox/grid/EnhancedGrid',
    './BillManager/BillPropertiesForm',
    './BillManager/BillGrid',
    "./BillManager/buildUpGrid",
    "buildspace/apps/ProjectBuilder/BillManager/ScheduleOfQuantityGrid",
    "./BillManager/buildUpQuantityGrid",
    "./BillManager/buildUpRateSummary",
    "./BillManager/buildUpQuantitySummary",
    "./BillManager/itemHtmlEditorDialog",
    "./BillManager/primeCostRateDialog",
    "./BillManager/lumpSumPercentDialog",
    "buildspace/widget/grid/cells/Formatter",
    'dojo/aspect',
    'dojo/i18n!buildspace/nls/Tendering'],
    function(declare, domStyle, when, request, currency, Memory, AccordionContainer, ContentPane, EnhancedGrid, BillPropertiesForm, BillGrid, BuildUpGrid, ScheduleOfQuantityGrid, BuildUpQuantityGrid, BuildUpRateSummary, BuildUpQuantitySummary, ItemHtmlEditorDialog, PrimeCostRateDialog, LumpSumPercentDialog, GridFormatter, aspect, nls) {

    var BillPropertiesContainer = declare('buildspace.apps.TenderingReport.BillPropertiesContainer', dijit.layout.BorderContainer, {
        style: "padding:0px;width:100%;margin:0px;border:0px;height:100%;",
        gutters: false,
        billId: null,
        explorer: null,
        postCreate: function() {
            this.inherited(arguments);

            if(this.explorer.rootProject.status_id != buildspace.constants.STATUS_PRETENDER)
                var locked = true;

            var billPropertiesForm = this.billPropertiesForm = BillPropertiesForm({
                billId: this.billId,
                explorer: this.explorer,
                locked: (locked) ? locked : false
            });
            this.addChild(billPropertiesForm);
        }
    });

    var BillElementContainer = declare('buildspace.apps.TenderingReport.BillElementContainer', dijit.layout.BorderContainer, {
        style: "padding:0px;border:0px;width:100%;height:100%;",
        gutters: false,
        billId: null,
        rootProject: null,
        bqVersion: 0,
        columnData: null,
        explorer: null,
        currentBQAddendumId: -1,
        currentBillLockedStatus: false,
        currentBillVersion: 0,
        currentPrintableVersion: 0,
        selectedElementStore: [],
        selectedItemStore: [],
        elementItemStore: [],
        postCreate: function() {
            this.inherited(arguments);
            var self = this;
            self.createElementGrid();

            dojo.subscribe('tenderingReportBillGrid' + self.billId + '-stackContainer-selectChild', "", function(page) {
                var widget = dijit.byId('tenderingReportBillGrid' + self.billId + '-stackContainer');
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

                            //remove any add-resource button from stack container if any
                            var addResourceCatBtn = dijit.byId('add_resource_category_'+self.billId+'-btn');
                            if(addResourceCatBtn)
                                addResourceCatBtn.destroy();
                        }
                    }
                }
            });
        },
        createElementGrid: function(){
            this.selectedElementStore = new Memory({ idProperty: 'id' });
            this.selectedItemStore    = new Memory({ idProperty: 'id' });
            this.elementItemStore     = [];

            var stackContainer = dijit.byId('tenderingReportBillGrid' + this.billId + '-stackContainer');

            if(stackContainer) {
                dijit.byId('tenderingReportBillGrid' + this.billId + '-stackContainer').destroyRecursive(true);
            }

            stackContainer = this.stackContainer = new dijit.layout.StackContainer({
                style: 'border:0px;width:100%;height:100%;',
                region: "center",
                id: 'tenderingReportBillGrid' + this.billId + '-stackContainer'
            });

            var store = dojo.data.ItemFileWriteStore({
                url: "billManager/getElementList/id/" + this.billId,
                clearOnClose: true,
                urlPreventCache:true
            }),
            billInfoQuery = dojo.xhrGet({
                url: "billManager/getBillInfo",
                handleAs: "json",
                content: {
                    id: this.billId
                }
            }),
            me = this;

            billInfoQuery.then(function(billInfo) {
                try {
                    // assign current BQ Addendum's ID
                    me.currentBQAddendumId     = billInfo.project_revision_status.id;

                    me.currentBillLockedStatus = billInfo.project_revision_status.locked_status;

                    // current bill version
                    me.currentBillVersion      = billInfo.project_revision_status.version;

                    // current BQ printable version
                    me.currentPrintableVersion = billInfo.printable_project_revision_status.version;

                    me.currentBillType         = billInfo.bill_type.type;

                    var grid = new BillGrid({
                        stackContainerTitle: "Element",
                        billId: me.billId,
                        rootProject: me.rootProject,
                        pageId: 'element-page-' + me.billId,
                        id: 'tenderingReport-element-page-container-' + me.billId,
                        gridOpts: {
                            gridContainer: me,
                            store: store,
                            typeColumns : billInfo.column_settings,
                            markupSettings: billInfo.markup_settings,
                            bqCSRFToken: billInfo.bqCSRFToken,
                            currentBQAddendumId: me.currentBQAddendumId,
                            currentBillLockedStatus: me.currentBillLockedStatus,
                            currentBillVersion: me.currentBillVersion,
                            currentPrintableVersion: me.currentPrintableVersion,
                            currentBillType: me.currentBillType,
                            currentGridType: 'element',
                            onRowDblClick: function(e) {
                                var self = this,
                                    item = self.getItem(e.rowIndex);
                                if(item.id[0] > 0 && item.description[0] !== null && item.description[0] !== '') {
                                    me.createItemGrid(item, billInfo, grid);
                                }
                            },
                            singleCheckBoxSelection: function(e) {
                                var self = this,
                                    rowIndex = e.rowIndex,
                                    checked = this.selection.selected[rowIndex],
                                    item = this.getItem(rowIndex);

                                // used to store removeable selection
                                self.removedIds = [];

                                if ( checked ) {
                                    self.gridContainer.selectedElementStore.put({ id: item.id[0] });

                                    return self.getAffectedItemsByElement(item, 'add');
                                } else {
                                    self.gridContainer.selectedElementStore.remove(item.id[0]);

                                    self.removedIds.push(item.id[0]);

                                    return self.getAffectedItemsByElement(item, 'remove');
                                }
                            },
                            toggleAllSelection: function(checked) {
                                var self = this, selection = this.selection, storeName;

                                // used to store removeable selection
                                self.removedIds = [];

                                if (checked) {
                                    selection.selectRange(0, self.rowCount-1);
                                    self.store.fetch({
                                        onComplete: function (items) {
                                            dojo.forEach(items, function (item, index) {
                                                if(item.id > 0) {
                                                    self.gridContainer.selectedElementStore.put({ id: item.id[0] });
                                                }
                                            });
                                        }
                                    });

                                    return self.getAffectedItemsByElement(null , 'add');
                                } else {
                                    selection.deselectAll();

                                    self.store.fetch({
                                        onComplete: function (items) {
                                            dojo.forEach(items, function (item, index) {
                                                if(item.id > 0) {
                                                    self.gridContainer.selectedElementStore.remove(item.id[0]);

                                                    self.removedIds.push(item.id[0]);
                                                }
                                            });
                                        }
                                    });

                                    return self.getAffectedItemsByElement(null, 'remove');
                                }
                            },
                            getAffectedItemsByElement: function(element, type) {
                                var self = this,
                                    elements = [];

                                var pb = buildspace.dialog.indeterminateProgressBar({
                                    title: nls.pleaseWait+'...'
                                });

                                pb.show();

                                if (type === 'add') {
                                    // if single element, then only push affected element only
                                    if (element) {
                                        elements.push(element.id[0]);
                                    } else {
                                        self.gridContainer.selectedElementStore.query().forEach(function(element) {
                                            elements.push(element.id);
                                        });
                                    }
                                } else {
                                    for (var itemKeyIndex in self.removedIds) {
                                        elements.push(self.removedIds[itemKeyIndex]);
                                    }
                                }

                                request.post('tenderingReport/getAffectedItemsByElements', {
                                    handleAs: 'json',
                                    data: {
                                        bill_id: self.billId,
                                        element_ids: JSON.stringify(self.gridContainer.arrayUnique(elements))
                                    }
                                }).then(function(data) {
                                    // create default placeholder for storing item(s) associated with element
                                    for (var elementId in data) {
                                        if ( ! self.gridContainer.elementItemStore[elementId] ) {
                                            self.gridContainer.elementItemStore[elementId] = new Memory({ idProperty: 'id' });
                                        }
                                    }

                                    if ( type === 'add' ) {
                                        for (var elementId in data) {
                                            for (var itemIdIndex in data[elementId]) {
                                                self.gridContainer.elementItemStore[elementId].put({ id: data[elementId][itemIdIndex] });
                                                self.gridContainer.selectedItemStore.put({ id: data[elementId][itemIdIndex] });
                                            }
                                        }
                                    } else {
                                        for (var elementId in data) {
                                            self.gridContainer.selectedElementStore.remove(elementId);

                                            for (var itemIdIndex in data[elementId]) {
                                                self.gridContainer.elementItemStore[elementId].remove(data[elementId][itemIdIndex]);
                                                self.gridContainer.selectedItemStore.remove(data[elementId][itemIdIndex]);
                                            }
                                        }
                                    }

                                    pb.hide();
                                }, function(error) {
                                    pb.hide();
                                    console.log(error);
                                });
                            }
                        }
                    });

                    var controller = new dijit.layout.StackController({
                        region: "top",
                        containerId: 'tenderingReportBillGrid' + me.billId + '-stackContainer'
                    });

                    var controllerPane = new dijit.layout.ContentPane({
                        style: "padding:0px;overflow:hidden;",
                        baseClass: 'breadCrumbTrail',
                        region: 'top',
                        id: 'tenderingReportBillGrid'+me.billId+'-controllerPane',
                        content: controller
                    });

                    me.addChild(stackContainer);
                    me.addChild(controllerPane);
                }
                catch(e){
                    console.debug(e);
                }
            });
        },
        createItemGrid: function(element, billInfo, elementGridStore){
            var self = this,
                hierarchyTypes = {
                    options: [
                        buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER_TEXT,
                        buildspace.widget.grid.constants.HIERARCHY_TYPE_WORK_ITEM_TEXT,
                        buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_HTML_EDITOR_TEXT,
                        buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_PROVISIONAL_TEXT,
                        buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_PC_RATE_TEXT,
                        buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_RATE_ONLY_TEXT,
                        buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_NOT_LISTED_TEXT,
                        buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_LUMP_SUM_TEXT,
                        buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_LUMP_SUM_PERCENT_TEXT,
                        buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_LUMP_SUM_EXCLUDE_TEXT,
                        buildspace.widget.grid.constants.HIERARCHY_TYPE_NOID_TEXT
                    ],
                    values: [
                        buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER,
                        buildspace.widget.grid.constants.HIERARCHY_TYPE_WORK_ITEM,
                        buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_HTML_EDITOR,
                        buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_PROVISIONAL,
                        buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_PC_RATE,
                        buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_RATE_ONLY,
                        buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_NOT_LISTED,
                        buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_LUMP_SUM,
                        buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_LUMP_SUM_PERCENT,
                        buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_LUMP_SUM_EXCLUDE,
                        buildspace.widget.grid.constants.HIERARCHY_TYPE_NOID
                    ]
                },
                hierarchyTypesForHead = {
                    options: [
                        buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER_TEXT
                    ],
                    values: [
                        buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER
                    ]
                },
                store = new dojo.data.ItemFileWriteStore({
                    url:"billManager/getItemList/id/"+element.id+"/bill_id/"+self.billId,
                    clearOnClose: true
                }),
                unitQuery = dojo.xhrGet({
                    url: "billManager/getUnits/billId/"+ self.billId,
                    handleAs: "json"
                }),
                pb = buildspace.dialog.indeterminateProgressBar({
                    title:nls.pleaseWait+'...'
                });

            pb.show();

            return when(unitQuery, function(uom){
                pb.hide();
                try{
                    var grid = new BillGrid({
                        stackContainerTitle: element.description,
                        billId: self.billId,
                        rootProject: self.rootProject,
                        id: 'tenderingReport-item-page-container-' + self.billId,
                        elementId: element.id,
                        pageId: 'item-page-' + self.billId,
                        type: 'tree',
                        gridOpts: {
                            gridContainer: self,
                            store: store,
                            escapeHTMLInData: false,
                            typeColumns : billInfo.column_settings,
                            markupSettings: billInfo.markup_settings,
                            elementGridStore: elementGridStore,
                            hierarchyTypes: hierarchyTypes,
                            hierarchyTypesForHead: hierarchyTypesForHead,
                            unitOfMeasurements: uom,
                            currentBQAddendumId: self.currentBQAddendumId,
                            currentBillLockedStatus: self.currentBillLockedStatus,
                            currentBillVersion: self.currentBillVersion,
                            currentBillType: self.currentBillType,
                            currentGridType: 'item',
                            onRowDblClick: function(e) {
                                var colField = e.cell.field,
                                    rowIndex = e.rowIndex,
                                    item = this.getItem(rowIndex),
                                    billGridStore = this.store;

                                if (item && (item.project_revision_deleted_at !== undefined && item.project_revision_deleted_at[0])) {
                                    return false;
                                }

                                if(colField == "rate-value" && item.id > 0){
                                    if(item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_WORK_ITEM ||
                                        item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_HTML_EDITOR ||
                                        item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_PROVISIONAL ||
                                        item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_RATE_ONLY ||
                                        item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_NOT_LISTED ||
                                        (item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_LUMP_SUM ) ||
                                        (item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_LUMP_SUM_EXCLUDE && self.rootProject.status_id == buildspace.constants.STATUS_PRETENDER )
                                        ){
                                        self.createBuildUpRateContainer(item, billGridStore);
                                    }else if(item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_PC_RATE){
                                        var pcRateDialog = new PrimeCostRateDialog({
                                            itemObj: item,
                                            billGridStore: billGridStore,
                                            elementGridStore: elementGridStore,
                                            currentBillLockedStatus: self.currentBillLockedStatus,
                                            currentBillVersion: self.currentBillVersion,
                                            currentItemVersion: item.version[0]
                                        });
                                        pcRateDialog.show();
                                    }else if(item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_LUMP_SUM_PERCENT){
                                        var lumpSumPercentDialog = new LumpSumPercentDialog({
                                            itemObj: item,
                                            billGridStore: billGridStore,
                                            elementGridStore: elementGridStore,
                                            currentBillLockedStatus: self.currentBillLockedStatus,
                                            currentBillVersion: self.currentBillVersion,
                                            currentItemVersion: item.version[0]
                                        });
                                        lumpSumPercentDialog.show();
                                    }

                                }else{
                                    var type = false,
                                        billColumnSettingId = e.cell.billColumnSettingId;

                                    var isOriginalColumn = false;

                                    if(colField.match(/-quantity_per_unit-value/gi)){
                                        type = buildspace.constants.QUANTITY_PER_UNIT_ORIGINAL;
                                        isOriginalColumn = true;
                                    }else if(colField.match(/-quantity_per_unit_remeasurement-value/gi)){
                                        type = buildspace.constants.QUANTITY_PER_UNIT_REMEASUREMENT;
                                    }

                                    if(type && item.id > 0 && item[billColumnSettingId+'-include'][0]=='true'){
                                        if(item.type != buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER &&
                                            item.type != buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER_N &&
                                            item.type != buildspace.widget.grid.constants.HIERARCHY_TYPE_NOID &&
                                            item.type != buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_LUMP_SUM &&
                                            item.type != buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_LUMP_SUM_EXCLUDE &&
                                            item.type != buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_RATE_ONLY){
                                            if(item.uom_id[0] > 0){
                                                var dimensionColumnQuery = dojo.xhrPost({
                                                    url: "billBuildUpQuantity/getDimensionColumnStructure",
                                                    content:{uom_id: item.uom_id[0]},
                                                    handleAs: "json"
                                                });
                                                var pb = buildspace.dialog.indeterminateProgressBar({
                                                    title:nls.pleaseWait+'...'
                                                });
                                                pb.show();
                                                dimensionColumnQuery.then(function(dimensionColumns){
                                                    self.createBuildUpQuantityContainer(item, dimensionColumns, billColumnSettingId, type, billGridStore, elementGridStore, isOriginalColumn);
                                                    pb.hide();
                                                });
                                            }else{
                                                buildspace.dialog.alert(nls.buildUpQtyAlert, nls.pleaseSetUOM, 60, 300);
                                            }
                                        }
                                    }

                                    if(colField == 'description' && item.id > 0 && (item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_HTML_EDITOR || item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_NOID)){
                                        var editor = new ItemHtmlEditorDialog({
                                            itemObj: item,
                                            billId: self.billId,
                                            billGridStore: billGridStore,
                                            currentBillLockedStatus: self.currentBillLockedStatus,
                                            currentBillVersion: self.currentBillVersion,
                                            currentItemVersion: item.version[0]
                                        });
                                        editor.show();
                                    }
                                }
                            },
                            singleCheckBoxSelection: function(e) {
                                var self = this,
                                    rowIndex = e.rowIndex,
                                    checked = this.selection.selected[rowIndex],
                                    item = this.getItem(rowIndex);

                                // used to store removeable selection
                                self.removedIds = [];

                                if ( checked ) {
                                    self.gridContainer.selectedItemStore.put({ id: item.id[0] });

                                    return self.getAffectedElementsByItems(item, 'add');
                                } else {
                                    self.gridContainer.selectedItemStore.remove(item.id[0]);

                                    self.removedIds.push(item.id[0]);

                                    return self.getAffectedElementsByItems(item, 'remove');
                                }
                            },
                            toggleAllSelection: function(checked) {
                                var self = this, selection = this.selection, storeName;

                                // used to store removeable selection
                                self.removedIds = [];

                                if (checked) {
                                    selection.selectRange(0, self.rowCount-1);
                                    self.store.fetch({
                                        onComplete: function (items) {
                                            dojo.forEach(items, function (item, index) {
                                                if(item.id > 0) {
                                                    self.gridContainer.selectedItemStore.put({ id: item.id[0] });
                                                }
                                            });
                                        }
                                    });

                                    return self.getAffectedElementsByItems(null , 'add');
                                } else {
                                    selection.deselectAll();

                                    self.store.fetch({
                                        onComplete: function (items) {
                                            dojo.forEach(items, function (item, index) {
                                                if(item.id > 0) {
                                                    self.gridContainer.selectedItemStore.remove(item.id[0]);

                                                    self.removedIds.push(item.id[0]);
                                                }
                                            });
                                        }
                                    });

                                    return self.getAffectedElementsByItems(null, 'remove');
                                }
                            },
                            getAffectedElementsByItems: function(item, type) {
                                var self = this,
                                    items = [];

                                var pb = buildspace.dialog.indeterminateProgressBar({
                                    title: nls.pleaseWait+'...'
                                });

                                pb.show();

                                if (type === 'add') {
                                    self.gridContainer.selectedItemStore.query().forEach(function(item) {
                                        items.push(item.id);
                                    });
                                } else {
                                    for (var itemKeyIndex in self.removedIds) {
                                        items.push(self.removedIds[itemKeyIndex]);
                                    }
                                }

                                request.post('tenderingReport/getAffectedElementsByItems', {
                                    handleAs: 'json',
                                    data: {
                                        bill_id: self.billId,
                                        item_ids: JSON.stringify(self.gridContainer.arrayUnique(items))
                                    }
                                }).then(function(data) {
                                    // create default placeholder for storing item(s) associated with element
                                    for (var elementId in data) {
                                        if ( ! self.gridContainer.elementItemStore[elementId] ) {
                                            self.gridContainer.elementItemStore[elementId] = new Memory({ idProperty: 'id' });
                                        }
                                    }

                                    var elementGrid = dijit.byId('tenderingReport-element-page-container-' + self.billId);

                                    if ( type === 'add' ) {
                                        for (var elementId in data) {
                                            for (var itemIdIndex in data[elementId]) {
                                                self.gridContainer.elementItemStore[elementId].put({ id: data[elementId][itemIdIndex] });
                                                self.gridContainer.selectedItemStore.put({ id: data[elementId][itemIdIndex] });
                                            }

                                            // checked element selection if there is item(s) selected in the current element
                                            elementGrid.grid.store.fetchItemByIdentity({
                                                identity: elementId,
                                                onItem: function(node) {
                                                    if ( ! node ) {
                                                        return;
                                                    }

                                                    self.gridContainer.selectedElementStore.put({ id: elementId });

                                                    return elementGrid.grid.rowSelectCell.toggleRow(node._0, true);
                                                }
                                            });
                                        }
                                    } else {
                                        for (var elementId in data) {
                                            self.gridContainer.selectedElementStore.remove(elementId);

                                            for (var itemIdIndex in data[elementId]) {
                                                self.gridContainer.elementItemStore[elementId].remove(data[elementId][itemIdIndex]);
                                                self.gridContainer.selectedItemStore.remove(data[elementId][itemIdIndex]);
                                            }

                                            // remove checked element selection if there is no item(s) in the current element
                                            elementGrid.grid.store.fetchItemByIdentity({
                                                identity: elementId,
                                                onItem: function(node) {
                                                    if ( ! node ) {
                                                        return;
                                                    }

                                                    if ( self.gridContainer.elementItemStore[elementId].data.length === 0 ) {
                                                        self.gridContainer.selectedElementStore.remove(elementId);
                                                        return elementGrid.grid.rowSelectCell.toggleRow(node._0, false);
                                                    }
                                                }
                                            });
                                        }
                                    }

                                    pb.hide();
                                }, function(error) {
                                    pb.hide();
                                    console.log(error);
                                });
                            }
                        }
                    });
                }catch(e){console.debug(e); }
            },function(error){
                /* got fucked */
            });
        },
        createBuildUpRateContainer: function(item, billGridStore){
            var self = this,
                currencySetting = (buildspace.billCurrencyAbbreviation) ? buildspace.billCurrencyAbbreviation : buildspace.currencyAbbreviation,
                baseContainer = new dijit.layout.BorderContainer({
                    style:"padding:0px;width:100%;height:100%;border:0px;outline:none;",
                    gutters: false
                }),
                aContainer = new dijit.layout.AccordionContainer({
                    id: "accordian_"+self.billId+"_"+item.id+"-container",
                    region: "center",
                    style:"padding:0px;width:100%;height:100%;border:0px;outline:none;"
                }),
                resourceQuery = dojo.xhrGet({
                    url: "billBuildUpRate/resourceList/item_id/"+item.id,
                    handleAs: "json"
                }),
                formatter = new GridFormatter(),
                unitQuery = dojo.xhrGet({
                    url: "billBuildUpRate/getUnits",
                    handleAs: "json"
                }),
                pb = buildspace.dialog.indeterminateProgressBar({
                    title:nls.pleaseWait+'...'
                });

            pb.show();

            unitQuery.then(function(uom){
                when(resourceQuery, function(resources){
                    if(resources.length == 0){
                        aContainer.addChild(new dijit.layout.ContentPane({
                            title: nls.emptyResourceCategoryTitle,
                            style: "padding:0px;border:0px;",
                            doLayout: false,
                            id: 'accPane-empty_resource-'+item.id,
                            content: '<div style="text-align:center;"><p><h1>'+nls.emptyResourceCategory+'</h1></p></div> '
                        }));
                    }else{
                        var buildUpSummaryWidget = new BuildUpRateSummary({
                            id: 'buildUpRateSummary-'+item.id,
                            itemId: item.id,
                            container: baseContainer,
                            billGridStore: billGridStore,
                            _csrf_token: item._csrf_token,
                            currentBillLockedStatus: self.currentBillLockedStatus
                        });

                        dojo.forEach(resources, function(resource){
                            var store = new dojo.data.ItemFileWriteStore({
                                url:"billBuildUpRate/getBuildUpRateItemList/bill_item_id/"+item.id+"/resource_id/"+resource.id,
                                clearOnClose: true
                            });
                            try{
                                var grid = new BuildUpGrid({
                                    resource: resource,
                                    BQItem: item,
                                    gridOpts: {
                                        itemId: item.id,
                                        store: store,
                                        buildUpSummaryWidget: buildUpSummaryWidget,
                                        structure: [
                                            {name: 'No.', field: 'id', width:'30px', styles:'text-align:center;', formatter: formatter.rowCountCellFormatter },
                                            {name: nls.description, field: 'description', width:'auto', formatter: formatter.linkedCellFormatter },
                                            {name: nls.number, field: 'number-value', width:'100px', styles:'text-align:right;', formatter: formatter.formulaCurrencyCellFormatter},
                                            {name: nls.constant, field: 'constant-value', width:'100px', styles:'text-align:right;', formatter: formatter.formulaCurrencyCellFormatter},
                                            {name: nls.qty, field: 'quantity-value', width:'70px', styles:'text-align:right;', formatter: formatter.formulaNumberCellFormatter},
                                            {name: nls.unit, field: 'uom_id', width:'70px', styles:'text-align:center;', formatter: formatter.linkedUnitIdCellFormatter},
                                            {name: nls.rate, field: 'rate-value', width:'120px', styles:'text-align:right;', formatter: formatter.formulaCurrencyCellFormatter},
                                            {name: nls.total, field: 'total', width:'120px', styles:'text-align:right;', formatter: formatter.linkedCurrencyCellFormatter},
                                            {name: nls.wastage+" (%)", field: 'wastage-value', width:'70px', styles:'text-align:right;', formatter: formatter.formulaCurrencyCellFormatter},
                                            {name: nls.lineTotal, field: 'line_total', width:'120px', styles:'text-align:right;', formatter: formatter.linkedCurrencyCellFormatter}
                                        ],
                                        currentBillLockedStatus: self.currentBillLockedStatus
                                    }
                                });
                                aContainer.addChild(new dijit.layout.ContentPane({
                                    title: resource.name+'<span style="color:blue;float:right;">'+currencySetting+'&nbsp;'+currency.format(resource.total_build_up)+'</span>',
                                    style: "padding:0px;border:0px;",
                                    doLayout: false,
                                    id: 'accPane-'+resource.id+'-'+item.id,
                                    content: grid
                                }));
                            }catch(e){console.log(e);}
                        });
                        baseContainer.addChild(buildUpSummaryWidget);
                    }

                    baseContainer.addChild(aContainer);

                    var container = dijit.byId('tenderingReportBillGrid' + self.billId + '-stackContainer');
                    if(container){
                        var node = document.createElement("div");
                        var child = new dojox.layout.ContentPane({
                                title: buildspace.truncateString(item.description, 60)+' ('+nls.buildUpRate+')',
                                style: "padding:0px;border:0px;",
                                id: 'buildUpRatePage-'+item.id,
                                executeScripts: true },
                            node );
                        container.addChild(child);
                        child.set('content', baseContainer);
                        container.selectChild('buildUpRatePage-'+item.id);
                    }

                    pb.hide();
                });
            });
        },
        createBuildUpQuantityContainer: function(item, dimensionColumns, billColumnSettingId, type, billGridStore, elementGridStore, isOriginalColumn){
            var self = this,
                scheduleOfQtyGrid,
                baseContainer = new dijit.layout.BorderContainer({
                    style:"padding:0;margin:0;width:100%;height:100%;border:none;outline:none;",
                    gutters: false
                }),
                tabContainer = new dijit.layout.TabContainer({
                    nested: true,
                    style: "padding:0;border:none;margin:0;width:100%;height:100%;",
                    region: 'center'
                }),
                formatter = new GridFormatter(),
                scheduleOfQtyQuery = dojo.xhrGet({
                    url: "billBuildUpQuantity/getLinkInfo/id/"+item.id+"/bcid/"+billColumnSettingId+"/t/"+type,
                    handleAs: "json"
                }),
                store = new dojo.data.ItemFileWriteStore({
                    url:"billBuildUpQuantity/getBuildUpQuantityItemList/bill_item_id/"+item.id+"/bill_column_setting_id/"+billColumnSettingId+"/type/"+type,
                    clearOnClose: true
                }),
                sign = {
                    options: [
                        buildspace.constants.SIGN_POSITIVE_TEXT,
                        buildspace.constants.SIGN_NEGATIVE_TEXT
                    ],
                    values: [
                        buildspace.constants.SIGN_POSITIVE,
                        buildspace.constants.SIGN_NEGATIVE
                    ]
                },
                hasLinkedQty = false,
                pb = buildspace.dialog.indeterminateProgressBar({
                    title:nls.pleaseWait+'...'
                });

            var disableEditingMode = true;

            pb.show();

            scheduleOfQtyQuery.then(function(linkInfo){
                var structure = [{
                    name: 'No.',
                    field: 'id',
                    styles: "text-align:center;",
                    width: '30px',
                    formatter: formatter.rowCountCellFormatter
                }, {
                    name: nls.description,
                    field: 'description',
                    width: 'auto',
                    cellType: 'buildspace.widget.grid.cells.Textarea'
                },{
                    name: nls.factor,
                    field: 'factor-value',
                    width:'100px',
                    styles:'text-align:right;',
                    cellType:'buildspace.widget.grid.cells.FormulaTextBox',
                    formatter: formatter.formulaNumberCellFormatter
                }];

                dojo.forEach(dimensionColumns, function(dimensionColumn){
                    structure.push({
                        name: dimensionColumn.title,
                        field: dimensionColumn.field_name,
                        width:'100px',
                        styles:'text-align:right;',
                        cellType:'buildspace.widget.grid.cells.FormulaTextBox',
                        formatter: formatter.formulaNumberCellFormatter
                    });
                });

                structure.push({
                    name: nls.total,
                    field: 'total',
                    width:'100px',
                    styles:'text-align:right;',
                    formatter: formatter.numberCellFormatter
                });

                structure.push({
                    name: nls.sign,
                    field: 'sign',
                    width: '70px',
                    styles: 'text-align:center;',
                    cellType: 'dojox.grid.cells.Select',
                    options: sign.options,
                    values: sign.values,
                    formatter: formatter.signCellFormatter
                });

                var buildUpSummaryWidget = new BuildUpQuantitySummary({
                    itemId: item.id,
                    billColumnSettingId: billColumnSettingId,
                    type: type,
                    hasLinkedQty: linkInfo.has_linked_qty,
                    container: baseContainer,
                    _csrf_token: item._csrf_token,
                    disableEditingMode: disableEditingMode
                });

                if(linkInfo.has_linked_qty){
                    hasLinkedQty = true;
                    scheduleOfQtyGrid = ScheduleOfQuantityGrid({
                        title: nls.scheduleOfQuantities,
                        BillItem: item,
                        billColumnSettingId: billColumnSettingId,
                        disableEditingMode: disableEditingMode,
                        stackContainerId: 'tenderingReportBillGrid' + self.billId + '-stackContainer',
                        gridOpts: {
                            qtyType: type,
                            buildUpSummaryWidget: buildUpSummaryWidget,
                            store: new dojo.data.ItemFileWriteStore({
                                url:"billBuildUpQuantity/getScheduleOfQuantities/id/"+item.id+"/bcid/"+billColumnSettingId+"/type/"+type,
                                clearOnClose: true
                            }),
                            structure: [
                                {name: 'No.', field: 'id', width:'30px', styles:'text-align:center;', formatter: formatter.rowCountCellFormatter },
                                {name: nls.description, field: 'description', width:'auto', formatter: formatter.treeCellFormatter },
                                {name: nls.type, field: 'type', width:'70px', styles:'text-align:center;', formatter: formatter.typeCellFormatter },
                                {name: nls.unit, field: 'uom_id', width:'70px', styles:'text-align:center;', formatter: formatter.unitIdCellFormatter},
                                {name: nls.qty, field: 'quantity-value', width:'100px', styles:'text-align:right;', formatter: formatter.formulaNumberCellFormatter}
                            ]
                        }
                    });
                }

                tabContainer.addChild(new BuildUpQuantityGrid({
                    title: nls.manualQtyItems,
                    billColumnSettingId: billColumnSettingId,
                    BillItem: item,
                    type: type,
                    disableEditingMode: disableEditingMode,
                    gridOpts: {
                        store: store,
                        structure: structure,
                        buildUpSummaryWidget: buildUpSummaryWidget
                    }
                }));

                if(hasLinkedQty){
                    tabContainer.addChild(scheduleOfQtyGrid);
                }

                baseContainer.addChild(tabContainer);
                baseContainer.addChild(buildUpSummaryWidget);

                var container = dijit.byId('tenderingReportBillGrid' + self.billId + '-stackContainer');
                if(container){
                    var node = document.createElement("div");
                    var child = new dojox.layout.ContentPane( {
                            title: buildspace.truncateString(item.description, 60)+' ('+nls.buildUpQuantity+' - '+item.uom_symbol+')',
                            id: 'buildUpQuantityPage-'+item.id,
                            content: baseContainer,
                            style: "padding:0px;border:0px;",
                            grid: hasLinkedQty ? scheduleOfQtyGrid.grid : null,
                            executeScripts: true },
                        node );
                    container.addChild(child);
                    container.selectChild('buildUpQuantityPage-'+item.id);
                }

                pb.hide();
            });
        },
        reconstructBillContainer: function() {
            var controllerPane = dijit.byId('tenderingReportBillGrid'+this.billId+'-controllerPane'),
                stackContainer = dijit.byId('tenderingReportBillGrid'+this.billId+'-stackContainer');

            controllerPane.destroyRecursive(true);
            stackContainer.destroyRecursive(true);

            this.createElementGrid();
        },
        markedCheckBoxObject: function(grid, selectedRowStore) {
            var store = grid.store;

            selectedRowStore.query().forEach(function(item) {
                if (item.id == buildspace.constants.GRID_LAST_ROW) {
                    return;
                }

                store.fetchItemByIdentity({
                    identity: item.id,
                    onItem: function(node) {
                        if ( ! node ) {
                            return;
                        }

                        return grid.rowSelectCell.toggleRow(node._0, true);
                    }
                });
            });
        },
        arrayUnique: function(array) {
            return array.reverse().filter(function (e, i, arr) {
                return arr.indexOf(e, i+1) === -1;
            }).reverse();
        }
    });

    var BillManagerContainer = declare('buildspace.apps.TenderingReport.BillManager', dijit.layout.TabContainer, {
        region: "center",
        rootProject: null,
        style: "padding:0px;border:0px;margin:0px;width:100%;height:100%;",
        billId: null,
        billLayoutSettingId: null,
        explorer: null,
        nested: true,
        postCreate: function() {
            this.inherited(arguments);
            var billElementContainer = BillElementContainer({
                    id: 'bill_element_container_'+this.rootProject.id+'-bill-'+this.billId,
                    rootProject: this.rootProject,
                    billId: this.billId,
                    bqVersion: 0,
                    explorer: this.explorer
                }),
                billPropertiesContainer = BillPropertiesContainer({
                    billId: this.billId,
                    explorer: this.explorer
                });

            this.addChild(new ContentPane({
                style: "padding:0px;border:0px;width:100%;height:100%;",
                title: nls.elementTradeList,
                content: billElementContainer
            }));

            // pass the Bill Element Container's object into Bill Properties Container Object
            billPropertiesContainer.billPropertiesForm.billElementObj = billElementContainer;

            this.addChild(new ContentPane({
                style: "padding:0px;border:0px;width:100%;height:100%;",
                title: nls.billProperties,
                content: billPropertiesContainer
            }));
        }
    });

    return BillManagerContainer;
});