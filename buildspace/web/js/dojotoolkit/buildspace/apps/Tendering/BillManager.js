define('buildspace/apps/Tendering/BillManager',[
    'dojo/_base/declare',
    "dojo/dom-style",
    "dojo/when",
    "dojo/currency",
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
    "./BillManager/AddResourceCategoryDialog",
    "./BillPrintoutSetting/standardPhrases",
    "buildspace/widget/grid/cells/Formatter",
    'dojo/aspect',
    'dojo/i18n!buildspace/nls/Tendering'],
    function(declare, domStyle, when, currency, AccordionContainer, ContentPane, EnhancedGrid, BillPropertiesForm, BillGrid, BuildUpGrid, ScheduleOfQuantityGrid, BuildUpQuantityGrid, BuildUpRateSummary, BuildUpQuantitySummary, ItemHtmlEditorDialog, PrimeCostRateDialog, LumpSumPercentDialog, AddResourceCategoryDialog, StandardPhrasesForm, GridFormatter, aspect, nls) {

    var BillPrintingSettingContainer = declare('buildspace.apps.Tendering.BillPrintingSettingContainer', dijit.layout.BorderContainer, {
        style: "padding:0px;width:100%;margin:0px;border:0px;height:100%;",
        gutters: false,
        bill: null,
        postCreate: function() {
            this.inherited(arguments);

            var standardPhrasesForm = this.standardPhrasesForm = new StandardPhrasesForm({
                billId: String(this.bill.id)
            });

            this.addChild(standardPhrasesForm);
        }
    });

    var BillPropertiesContainer = declare('buildspace.apps.ProjectBuilder.BillPropertiesContainer', dijit.layout.BorderContainer, {
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

    var BillElementContainer = declare('buildspace.apps.Tendering.BillElementContainer', dijit.layout.BorderContainer, {
        style: "padding:0;margin:0;border:none;width:100%;height:100%;",
        gutters: false,
        bill: null,
        rootProject: null,
        bqVersion: 0,
        columnData: null,
        projectBreakdownGrid: null,
        currentBQAddendumId: -1,
        currentBillLockedStatus: false,
        currentBillVersion: 0,
        currentPrintableVersion: 0,
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

                            //remove any add-resource button from stack container if any
                            var addResourceCatBtn = dijit.byId('add_resource_category_'+billId+'-btn');
                            if(addResourceCatBtn)
                                addResourceCatBtn.destroy();
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
                url: "billManager/getElementList/id/" + String(this.bill.id),
                clearOnClose: true,
                urlPreventCache:true
            }),
            billInfoQuery = dojo.xhrGet({
                url: "billManager/getBillInfo",
                handleAs: "json",
                content: {
                    id: String(this.bill.id)
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
                        bill: me.bill,
                        rootProject: me.rootProject,
                        pageId: 'element-page-' + String(me.bill.id),
                        id: 'element-page-container-' + String(me.bill.id),
                        gridOpts: {
                            store: store,
                            typeColumns : billInfo.column_settings,
                            markupSettings: billInfo.markup_settings,
                            bqCSRFToken: billInfo.bqCSRFToken,
                            addUrl: 'billManager/elementAdd',
                            updateUrl: 'billManager/elementUpdate',
                            rowUpdateUrl: 'billManager/elementRowUpdate',
                            deleteUrl: 'billManager/elementDelete',
                            pasteUrl: 'billManager/elementPaste',
                            currentBQAddendumId: me.currentBQAddendumId,
                            currentBillLockedStatus: me.currentBillLockedStatus,
                            currentBillVersion: me.currentBillVersion,
                            currentPrintableVersion: me.currentPrintableVersion,
                            currentBillType: me.currentBillType,
                            currentGridType: 'element',
                            onRowDblClick: function(e) {
                                var self = this,
                                    item = self.getItem(e.rowIndex);
                                if(item && parseInt(String(item.id)) > 0 && String(item.description) !== null && String(item.description) !== '') {
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
                }
                catch(e){
                    console.debug(e)
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
                    url:"billManager/getItemList/id/"+element.id+"/bill_id/"+String(self.bill.id),
                    clearOnClose: true
                }),
                unitQuery = dojo.xhrGet({
                    url: "billManager/getUnits/billId/"+ String(self.bill.id),
                    handleAs: "json"
                }),
                pb = buildspace.dialog.indeterminateProgressBar({
                    title:nls.pleaseWait+'...'
                });

            pb.show();

            return when(unitQuery, function(uom){
                pb.hide();
                try{
                    new BillGrid({
                        stackContainerTitle: element.description,
                        bill: self.bill,
                        rootProject: self.rootProject,
                        id: 'item-page-container-' + String(self.bill.id),
                        elementId: element.id,
                        pageId: 'item-page-' + String(self.bill.id),
                        type: 'tree',
                        gridOpts: {
                            store: store,
                            escapeHTMLInData: false,
                            typeColumns : billInfo.column_settings,
                            markupSettings: billInfo.markup_settings,
                            elementGridStore: elementGridStore,
                            hierarchyTypes: hierarchyTypes,
                            hierarchyTypesForHead: hierarchyTypesForHead,
                            unitOfMeasurements: uom,
                            addUrl: 'billManager/itemAdd',
                            updateUrl: 'billManager/itemUpdate',
                            rowUpdateUrl: 'billManager/itemRowUpdate',
                            deleteUrl: 'billManager/itemDelete',
                            deleteRateUrl: 'billManager/itemRateDelete',
                            deleteQuantityUrl: 'billManager/itemQuantityDelete',
                            pasteUrl: 'billManager/itemPaste',
                            indentUrl: 'billManager/itemIndent',
                            outdentUrl: 'billManager/itemOutdent',
                            currentBQAddendumId: self.currentBQAddendumId,
                            currentBillLockedStatus: self.currentBillLockedStatus,
                            currentBillVersion: self.currentBillVersion,
                            currentBillType: self.currentBillType,
                            currentGridType: 'item',
                            editableCellDblClick: function(e) {
                                var colField = e.cell.field,
                                    rowIndex = e.rowIndex,
                                    item = this.getItem(rowIndex),
                                    billGridStore = this.store;

                                if (item && (item.project_revision_deleted_at !== undefined && item.project_revision_deleted_at[0])){
                                    return false;
                                }

                                if(colField == "rate-value" && parseInt(String(item.id)) > 0){
                                    if(parseInt(String(item.type)) == buildspace.widget.grid.constants.HIERARCHY_TYPE_WORK_ITEM ||
                                        parseInt(String(item.type)) == buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_HTML_EDITOR ||
                                        parseInt(String(item.type)) == buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_PROVISIONAL ||
                                        parseInt(String(item.type)) == buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_RATE_ONLY ||
                                        parseInt(String(item.type)) == buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_NOT_LISTED ||
                                        (parseInt(String(item.type)) == buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_LUMP_SUM ) ||
                                        (parseInt(String(item.type)) == buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_LUMP_SUM_EXCLUDE && parseInt(String(self.rootProject.status_id)) == buildspace.constants.STATUS_PRETENDER )
                                        ){
                                        self.createBuildUpRateContainer(item, billGridStore);
                                    }else if(parseInt(String(item.type)) == buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_PC_RATE){
                                        var pcRateDialog = new PrimeCostRateDialog({
                                            itemObj: item,
                                            billGridStore: billGridStore,
                                            elementGridStore: elementGridStore,
                                            currentBillLockedStatus: self.currentBillLockedStatus,
                                            currentBillVersion: self.currentBillVersion,
                                            currentItemVersion: parseInt(String(item.version))
                                        });
                                        pcRateDialog.show();
                                    }else if(parseInt(String(item.type)) == buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_LUMP_SUM_PERCENT){
                                        var lumpSumPercentDialog = new LumpSumPercentDialog({
                                            itemObj: item,
                                            billGridStore: billGridStore,
                                            elementGridStore: elementGridStore,
                                            currentBillLockedStatus: self.currentBillLockedStatus,
                                            currentBillVersion: self.currentBillVersion,
                                            currentItemVersion: parseInt(String(item.version))
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

                                    if(type && parseInt(String(item.id)) > 0 && item[billColumnSettingId+'-include'][0]=='true'){
                                        if(parseInt(String(item.type)) != buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER &&
                                            parseInt(String(item.type)) != buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER_N &&
                                            parseInt(String(item.type)) != buildspace.widget.grid.constants.HIERARCHY_TYPE_NOID &&
                                            parseInt(String(item.type)) != buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_LUMP_SUM &&
                                            parseInt(String(item.type)) != buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_LUMP_SUM_EXCLUDE &&
                                            parseInt(String(item.type)) != buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_RATE_ONLY){
                                            if(parseInt(String(item.uom_id)) > 0){
                                                var dimensionColumnQuery = dojo.xhrPost({
                                                    url: "billBuildUpQuantity/getDimensionColumnStructure",
                                                    content:{uom_id: parseInt(String(item.uom_id))},
                                                    handleAs: "json"
                                                });
                                                var pb = buildspace.dialog.indeterminateProgressBar({
                                                    title:nls.pleaseWait+'...'
                                                });
                                                pb.show().then(function(){
                                                    dimensionColumnQuery.then(function(dimensionColumns){
                                                        self.createBuildUpQuantityContainer(item, dimensionColumns, billColumnSettingId, type, billGridStore, elementGridStore, isOriginalColumn);
                                                        pb.hide();
                                                    });
                                                });
                                            }else{
                                                buildspace.dialog.alert(nls.buildUpQtyAlert, nls.pleaseSetUOM, 60, 300);
                                            }
                                        }
                                    }

                                    if(colField == 'description' && parseInt(String(item.id)) > 0 && (parseInt(String(item.type)) == buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_HTML_EDITOR || parseInt(String(item.type)) == buildspace.widget.grid.constants.HIERARCHY_TYPE_NOID)){
                                        var editor = new ItemHtmlEditorDialog({
                                            itemObj: item,
                                            billId: String(self.bill.id),
                                            billGridStore: billGridStore,
                                            currentBillLockedStatus: self.currentBillLockedStatus,
                                            currentBillVersion: self.currentBillVersion,
                                            currentItemVersion: parseInt(String(item.version))
                                        });
                                        editor.show();
                                    }
                                }
                            }
                        }
                    });
                }catch(e){console.debug(e)}
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
                    id: "accordian_"+String(self.bill.id)+"_"+item.id+"-container",
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
                                        addUrl:'billBuildUpRate/buildUpRateItemAdd',
                                        updateUrl:'billBuildUpRate/buildUpRateItemUpdate',
                                        deleteUrl:'billBuildUpRate/buildUpRateItemDelete',
                                        pasteUrl:'billBuildUpRate/buildUpRateItemPaste',
                                        store: store,
                                        buildUpSummaryWidget: buildUpSummaryWidget,
                                        structure: [
                                            {name: 'No.', field: 'id', width:'30px', styles:'text-align:center;', formatter: formatter.rowCountCellFormatter },
                                            {name: nls.description, field: 'description', width:'auto', editable:true, cellType:'buildspace.widget.grid.cells.Textarea', formatter: formatter.linkedCellFormatter },
                                            {name: nls.number, field: 'number-value', width:'100px', styles:'text-align:right;', editable:true, cellType:'buildspace.widget.grid.cells.FormulaTextBox', formatter: formatter.formulaCurrencyCellFormatter},
                                            {name: nls.constant, field: 'constant-value', width:'100px', styles:'text-align:right;', editable:true, cellType:'buildspace.widget.grid.cells.FormulaTextBox', formatter: formatter.formulaCurrencyCellFormatter},
                                            {name: nls.qty, field: 'quantity-value', width:'70px', styles:'text-align:right;', editable:true, cellType:'buildspace.widget.grid.cells.FormulaTextBox', formatter: formatter.formulaNumberCellFormatter},
                                            {name: nls.unit, field: 'uom_id', width:'70px', styles:'text-align:center;', editable:true, cellType: 'dojox.grid.cells.Select', options: uom.options, values: uom.values, formatter: formatter.linkedUnitIdCellFormatter},
                                            {name: nls.rate, field: 'rate-value', width:'120px', styles:'text-align:right;', editable:true, cellType:'buildspace.widget.grid.cells.FormulaTextBox', formatter: formatter.formulaCurrencyCellFormatter},
                                            {name: nls.total, field: 'total', width:'120px', styles:'text-align:right;', formatter: formatter.linkedCurrencyCellFormatter},
                                            {name: nls.wastage+" (%)", field: 'wastage-value', width:'70px', styles:'text-align:right;', editable:true, cellType:'buildspace.widget.grid.cells.FormulaTextBox', formatter: formatter.formulaCurrencyCellFormatter},
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
                            }catch(e){console.log(e)}
                        });
                        baseContainer.addChild(buildUpSummaryWidget);
                    }

                    baseContainer.addChild(aContainer);
                    var container = dijit.byId('billGrid' + String(self.bill.id) + '-stackContainer');
                    if(container){
                        var controllerPane = dijit.byId('billGrid'+String(self.bill.id)+'-controllerPane'),
                            resourceCatBtn = new dijit.form.Button({
                                id: 'add_resource_category_'+String(self.bill.id)+'-btn',
                                label: nls.addResourceCategory,
                                style: "float:right;color:#333333!important;",
                                iconClass: "icon-16-container icon-16-add",
                                baseClass: 'buildUpRateImportResourceCategory',
                                onClick: function(e){
                                    var addResourceDiag = AddResourceCategoryDialog({
                                        billId: String(self.bill.id),
                                        billItem: item,
                                        currencyAbbr: currencySetting,
                                        billGridStore: billGridStore,
                                        baseContainer: baseContainer
                                    });
                                    addResourceDiag.show();
                                }
                            });

                        controllerPane.addChild(resourceCatBtn);

                        var node = document.createElement("div");
                        var child = new dojox.layout.ContentPane({
                                title: buildspace.truncateString(item.description, 60)+' ('+nls.buildUpRate+')',
                                style: "padding:0px;border:0px;",
                                id: 'buildUpRatePage-'+item.id,
                                content: baseContainer,
                                executeScripts: true },
                            node );
                        container.addChild(child);
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

            var disableEditingMode;
            if ((isOriginalColumn) && (item.version[0] != this.currentBillVersion || this.currentBillLockedStatus)) {
                disableEditingMode = true;
            }else{
                disableEditingMode = false;
            }

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
                    editable: true,
                    cellType: 'buildspace.widget.grid.cells.Textarea'
                },{
                    name: nls.factor,
                    field: 'factor-value',
                    width:'100px',
                    styles:'text-align:right;',
                    editable:true,
                    cellType:'buildspace.widget.grid.cells.FormulaTextBox',
                    formatter: formatter.formulaNumberCellFormatter
                }];

                dojo.forEach(dimensionColumns, function(dimensionColumn){
                    structure.push({
                        name: dimensionColumn.title,
                        field: dimensionColumn.field_name,
                        width:'100px',
                        styles:'text-align:right;',
                        editable:true,
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
                    editable: true,
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
                        stackContainerId: 'billGrid' + String(self.bill.id) + '-stackContainer',
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
                        addUrl: 'billBuildUpQuantity/buildUpQuantityItemAdd',
                        updateUrl: 'billBuildUpQuantity/buildUpQuantityItemUpdate',
                        deleteUrl: 'billBuildUpQuantity/buildUpQuantityItemDelete',
                        pasteUrl: 'billBuildUpQuantity/buildUpQuantityItemPaste',
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
                var container = dijit.byId('billGrid' + String(self.bill.id) + '-stackContainer');
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
            dijit.byId('billGrid'+String(this.bill.id)+'-controllerPane').destroyRecursive();
            dijit.byId('billGrid'+String(this.bill.id)+'-stackContainer').destroyRecursive();

            this.createElementGrid();
        }
    });

    return declare('buildspace.apps.Tendering.BillManager', dijit.layout.TabContainer, {
        region: "center",
        rootProject: null,
        nested: true,
        style: "padding:0;border:none;margin:0;width:100%;height:100%;",
        bill: null,
        billLayoutSettingId: null,
        projectBreakdownGrid: null,
        postCreate: function() {
            this.inherited(arguments);

            var billElementContainer = new BillElementContainer({
                    id: 'bill_element_container_'+this.rootProject.id+'-bill-'+String(this.bill.id),
                    title: nls.elementTradeList,
                    rootProject: this.rootProject,
                    bill: this.bill,
                    bqVersion: 0,
                    projectBreakdownGrid: this.projectBreakdownGrid
                });

            this.addChild(billElementContainer);

            this.addChild(new BillPropertiesContainer({
                title: nls.billProperties,
                bill: this.bill,
                projectBreakdownGrid: this.projectBreakdownGrid,
                billElementObj: billElementContainer
            }));

            if ( parseInt(String(this.rootProject.status_id)) != buildspace.apps.Tendering.ProjectStructureConstants.STATUS_IMPORT ) {
                this.addChild(new BillPrintingSettingContainer({
                    title: nls.standardPhrases,
                    bill: this.bill
                }));
            }
        }
    });
});