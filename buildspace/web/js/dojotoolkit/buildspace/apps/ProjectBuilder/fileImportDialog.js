define('buildspace/apps/ProjectBuilder/fileImportDialog',[
    'dojo/_base/declare',
    'dojo/_base/lang',
    "dojo/_base/connect",
    "dojo/dom-construct",
    "dojo/query",
    "dojo/when",
    "dojo/html",
    "dojo/dom",
    'dojo/keys',
    "dojo/dom-style",
    "buildspace/widget/grid/cells/Formatter",
    "dojox/grid/enhanced/plugins/IndirectSelection",
    "dijit/_WidgetBase",
    "dijit/_OnDijitClickMixin",
    "dijit/_TemplatedMixin",
    "dijit/_WidgetsInTemplateMixin",
    "dojo/text!./templates/fileImportForm.html",
    "dojo/text!./templates/previewForm.html",
    'dojo/i18n!buildspace/nls/FileImport',
    'dijit/form/ToggleButton',
    'dijit/form/RadioButton',
    'dojox/form/Uploader',
    "dijit/form/Select",
    "dijit/form/FilteringSelect",
    'dijit/Tooltip'
], function(declare, lang, connect, domConstruct, query, when, html, dom, keys, domStyle, GridFormatter, IndirectSelection, _WidgetBase, _OnDijitClickMixin, _TemplatedMixin, _WidgetsInTemplateMixin, template, previewFormTemplate, nls, ToggleButton, RadioButton, FileUploader, Select, FilteringSelect, Tooltip){

    var FileImportGrid = declare('buildspace.apps.ProjectBuilder.FileImportGrid', dojox.grid.EnhancedGrid, {
        type: null,
        selectedItem: null,
        bill: null,
        dialogWidget: null,
        _csrf_token: null,
        billGrid: null,
        importType: buildspace.constants.FILE_IMPORT_TYPE_BUILDSPACE,
        escapeHTMLInData: false,
        gridData: null,
        style: "border-top:none;",
        constructor: function(args){
            this.itemIds = [];
            this.connects = [];
            if(args.type != 'tree'){
                this.plugins = {indirectSelection: {headerSelector:true, width:"20px", styles:"text-align:center;"}}
            }
            this.inherited(arguments);
        },
        canSort: function(inSortInfo){
            return false;
        },
        postCreate: function(){
            var self = this;
            self.inherited(arguments);
            this.on('RowClick', function(e){
                var item = self.getItem(e.rowIndex);

                if(self.type != 'tree'){
                    if(item && item.id >= 0){
                        self.disableToolbarButtons(false);
                    }else{
                        self.disableToolbarButtons(true);
                    }
                }
            });

            if(this.type != 'tree'){
                this._connects.push(connect.connect(this, 'onCellClick', function(e){
                    self.selectTree(e);
                }));
                this._connects.push(connect.connect(this.rowSelectCell, 'toggleAllSelection', function(newValue){
                    self.toggleAllSelection(newValue);
                }));
            }
        },
        dodblclick: function(e){
            this.onRowDblClick(e);
        },
        disableToolbarButtons: function(isDisable){
            var importBtn = dijit.byId('ImportItemGridPreview-'+this.bill.id[0]+'_Import-button');

            if(importBtn)
                importBtn._setDisabledAttr(isDisable);
        },
        importFile: function(withRate, withQuantity, withBillRef, asNewBill){
            var self = this,
                itemIds = self.itemIds;

            if( itemIds.length > 0){
                var pb = buildspace.dialog.indeterminateProgressBar({
                    title:nls.savingData+'. '+nls.pleaseWait+'...'
                });

                pb.show();

                var action = this.importType == buildspace.constants.FILE_IMPORT_TYPE_BUILDSPACE ? 'saveImportedBuildspaceExcel' : 'saveImportedExcel';
                var uploadPath = this.importType == buildspace.constants.FILE_IMPORT_TYPE_BUILDSPACE ? '' : this.gridData.uploadPath;
                var filename = this.importType == buildspace.constants.FILE_IMPORT_TYPE_BUILDSPACE ? this.gridData.filename+'.'+this.gridData.extension : this.gridData.filename;

                dojo.xhrPost({
                    url: 'billManagerImportFile/'+action,
                    content: {
                        bill_id: this.bill.id[0],
                        ids: [itemIds],
                        with_rate: withRate,
                        with_quantity: withQuantity,
                        with_billRef: withBillRef,
                        as_new: asNewBill,
                        _csrf_token: this._csrf_token,
                        filename: filename,
                        uploadPath: uploadPath
                    },
                    handleAs: 'json',
                    load: function(data) {
                        self.dialogWidget.hide();
                        self.itemIds = [];
                        self.reloadProjectBreakdown();
                        pb.hide();

                    },
                    error: function(error) {
                        self.itemIds = [];
                        self.dialogWidget.hide();
                        pb.hide();
                    }
                });
            }else{
                buildspace.dialog.alert(nls.alert, nls.pleaseSelectBillToImport+'.', 80, 345);
            }
        },
        reloadProjectBreakdown: function(){
            var projectBreakdown = dijit.byId('main-project_breakdown');
            projectBreakdown.grid.reload();
        },
        onCellMouseOver: function(e){
            var cell = e.cell;
            var fieldName = cell.field;
            var item = this.getItem(e.rowIndex);

            if(item && item.hasOwnProperty(fieldName+'-msg') && item[fieldName+'-msg'] != undefined && item[fieldName+'-msg'][0]){
                var msg = "This is cell " + e.rowIndex + ", " + e.cellIndex;
                Tooltip.show(item[fieldName+'-msg'][0], e.cellNode);
            }
        },
        onCellMouseOut: function(e){
            dijit.hideTooltip(e.cellNode);
        },
        selectTree: function(e){
            var rowIndex = e.rowIndex,
                newValue = this.selection.selected[rowIndex],
                item = this.getItem(rowIndex);
            if(item){
                this.pushItemIdIntoGridArray(item, newValue);
            }
        },
        pushItemIdIntoGridArray: function(item, select){
            var grid = this;
            var idx = dojo.indexOf(grid.itemIds, item.id[0]);
            if(select){
                if(idx == -1){
                    grid.itemIds.push(item.id[0]);
                }
            }else{
                if(idx != -1){
                    grid.itemIds.splice(idx, 1);
                }
            }
        },
        toggleAllSelection:function(checked){
            var grid = this, selection = grid.selection;
            if(checked){
                selection.selectRange(0, grid.rowCount-1);
                grid.itemIds = [];
                grid.store.fetch({
                    onComplete: function (items) {
                        dojo.forEach(items, function (item, index) {
                            if(item.id >= 0){
                                grid.itemIds.push(item.id[0]);
                            }
                        });
                    }
                });
            }else{
                selection.deselectAll();
                grid.itemIds = [];
            }
        },
        onStyleRow: function(e) {
            this.inherited(arguments);

            if(e.node.children[0]){
                if(e.node.children[0].children[0].rows.length >= 2){
                    var elemToHide = e.node.children[0].children[0].rows[1],
                        childElement = e.node.children[0].children[0].rows[0].children;

                    elemToHide.parentNode.removeChild(elemToHide);

                    dojo.forEach(childElement, function(child, i){
                        var rowSpan = dojo.attr(child, 'rowSpan');

                        if(!rowSpan || rowSpan < 2)
                            dojo.attr(child, 'rowSpan', 2);
                    });
                }
            }
        },
        destroy: function(){
            this.inherited(arguments);
            dojo.forEach(this._connects, connect.disconnect);
            delete this._connects;
        }
    });

    var FileImportGridContainer = declare('buildspace.apps.ProjectBuilder.FileImportGridContainer', dijit.layout.BorderContainer, {
        stackContainerTitle: '',
        style: "padding:0px;width:100%;height:100%;",
        gutters: false,
        bill: null,
        billElementId: null,
        withRate: false,
        withQuantity: false,
        withBillRef: false,
        importType: buildspace.constants.FILE_IMPORT_TYPE_BUILDSPACE,
        gridOpts: {},
        postCreate: function(){
            var self = this;
            this.inherited(arguments);

            lang.mixin(this.gridOpts, {
                type: this.type,
                importType: this.importType,
                bill: this.bill,
                region:"center"
            });

            var grid = this.grid = new FileImportGrid(this.gridOpts);

            if(this.type != 'tree'){

                var toolbar = new dijit.Toolbar({region: "top", style:"padding:2px;border-bottom:none;width:100%;"});

                if(this.importType == buildspace.constants.FILE_IMPORT_TYPE_BUILDSPACE){
                    toolbar.addChild(
                        new dijit.form.Button({
                            id: 'ImportItemGridPreview-'+this.bill.id[0]+'_ImportIntoExisting-button',
                            label: nls.importIntoExistingBill,
                            disabled: (this.bill.hasOwnProperty('type') && this.bill.type[0] == buildspace.apps.ProjectBuilder.ProjectStructureConstants.TYPE_BILL) ? false : true,
                            iconClass: "icon-16-container icon-16-paste",
                            onClick: function(){
                                grid.importFile(self.withRate, self.withQuantity, self.withBillRef, false);
                            }
                        })
                    );

                    toolbar.addChild(new dijit.ToolbarSeparator());

                    toolbar.addChild(
                        new dijit.form.Button({
                            id: 'ImportItemGridPreview-'+this.bill.id[0]+'_ImportAsNew-button',
                            label: nls.importAsNewBill,
                            iconClass: "icon-16-container icon-16-clipboard",
                            onClick: function(){
                                grid.importFile(self.withRate, self.withQuantity, self.withBillRef, true);
                            }
                        })
                    );
                }else{
                    toolbar.addChild(
                        new dijit.form.Button({
                            id: 'ImportItemGridPreview-'+this.bill.id[0]+'_Import-button',
                            label: nls.import,
                            iconClass: "icon-16-container icon-16-import",
                            onClick: function(){
                                grid.importFile(self.withRate, self.withQuantity, self.withBillRef, false);
                            }
                        })
                    );
                }

                toolbar.addChild(new dijit.ToolbarSeparator());

                toolbar.addChild(
                    new ToggleButton({
                        name: "withRate",
                        label: nls.rate,
                        iconClass: "dijitCheckBoxIcon",
                        value: true,
                        checked: false,
                        onChange: function(newVal){
                            self.withRate = newVal;
                        }
                    })
                );

                toolbar.addChild(new dijit.ToolbarSeparator());
                toolbar.addChild(
                    new ToggleButton({
                        name: "withQuantity",
                        label: nls.quantity,
                        iconClass: "dijitCheckBoxIcon",
                        value: true,
                        checked: false,
                        onChange: function(newVal){
                            self.withQuantity = newVal;
                        }
                    })
                );

                toolbar.addChild(new dijit.ToolbarSeparator());
                toolbar.addChild(
                    new ToggleButton({
                        name: "withBillRef",
                        label: nls.billRef,
                        iconClass: "dijitCheckBoxIcon",
                        value: true,
                        checked: false,
                        onChange: function(newVal){
                            self.withBillRef = newVal;
                        }
                    })
                );

                this.addChild(toolbar);
            }

            this.addChild(new dijit.layout.ContentPane({style: 'width:100%', content:grid, region:'center'}));

            var id = this.billElementId ? this.bill.id[0]+'_'+this.billElementId : this.bill.id[0];
            var container = dijit.byId('ProjectBuilder-item_import_list_'+id+'-stackContainer');
            if(container && this.type == "tree"){
                var node = document.createElement("div");
                container.addChild(new dojox.layout.ContentPane( {
                    title: buildspace.truncateString(this.stackContainerTitle, 60),
                    id: this.pageId,
                    content: this,
                    executeScripts: true
                },node ));
                container.selectChild(this.pageId);
            }
        }
    });

    var FileImportGridDialog = declare('buildspace.apps.ProjectBuilder.FileImportGridDialog', dijit.Dialog, {
        style:"padding:0px;margin:0px;",
        title: nls.importFile,
        selectedItem: null,
        bill: null,
        billGrid: null,
        gridData: null,
        importType: buildspace.constants.FILE_IMPORT_TYPE_BUILDSPACE,
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
        removeUploadedFile: function(){
            dojo.xhrPost({
                url: 'billManagerImportFile/deleteTempFile',
                content: {
                    filename: this.gridData.filename,
                    extension: this.gridData.extension
                },
                handleAs: 'json'
            });
        },
        createContent: function(){
            var self = this;
            var borderContainer = new dijit.layout.BorderContainer({
                style:"padding:0px;width:1020px;height:500px;",
                gutters: false
            });

            var toolbar = new dijit.Toolbar({
                region: "top",
                style: "outline:none!important;padding:2px;overflow:hidden;"
            });

            toolbar.addChild(
                new dijit.form.Button({
                    label: nls.close,
                    iconClass: "icon-16-container icon-16-close",
                    style:"outline:none!important;",
                    onClick: function(){
                        self.removeUploadedFile();
                        self.hide();
                    }
                })
            );

            var formatter = new GridFormatter(),
                store = dojo.data.ItemFileReadStore({
                    data: {
                        'identifier' : "id",
                        'items' : this.gridData.elements
                    }
                });

            var content = FileImportGridContainer({
                stackContainerTitle: nls.elements,
                pageId: 'import_list-page_library-'+this.bill.id[0],
                bill: this.bill,
                importType: this.importType,
                gridOpts: {
                    store: store,
                    gridData: this.gridData,
                    dialogWidget: this,
                    structure: [
                        {name: 'No.', field: 'id', width:'30px', styles:'text-align:center;', formatter: formatter.rowCountCellFormatter },
                        {name: nls.description, field: 'description', width:'auto' },
                        {name: nls.itemCount, field: 'count', width:'80px', styles:'text-align:center;', formatter: formatter.infoFieldFormatter},
                        {name: nls.error, field: 'error', width:'60px', styles:'text-align:center;', formatter: formatter.infoFieldFormatter}
                    ],
                    onRowDblClick: function(e){
                        var _this = this, _item = _this.getItem(e.rowIndex);
                        if(_item.id >= 1 && _item.description[0] !== null){
                            self.createItemGrid(_item);
                        }
                    }
                }
            });

            borderContainer.addChild(toolbar);
            borderContainer.addChild(this.makeGridContainer(content, nls.elements));

            return borderContainer;
        },
        createItemGrid: function(element){
            var store = new dojo.data.ItemFileReadStore({
                data: {
                    'identifier' : "id",
                    'items' : this.gridData.items[element.id]
                }
            });

            FileImportGridContainer({
                stackContainerTitle: element.description[0],
                pageId: 'import_list-page_item-'+this.bill.id[0]+'_'+element.id,
                bill: this.bill,
                billElementId: element.id[0],
                importType: this.importType,
                type: 'tree',
                gridOpts: {
                    store: store,
                    dialogWidget: this,
                    gridData: this.gridData,
                    selectedItem: this.selectedItem,
                    billGrid: this.billGrid,
                    _csrf_token: element._csrf_token,
                    structure: this.getStructure()
                }
            });
        },
        getStructure: function(){
            var importType = this.importType;
            var columnName = null;
            var rowSpan;
            var formatter = GridFormatter();
            var descriptionWidth;

            if (!this.gridData.hasOwnProperty('columns')) {
                descriptionWidth = 'auto';
                rowSpan = 1;
            }else{
                descriptionWidth = this.gridData.columns.length > 1 ? '500px' : 'auto';
                rowSpan = 2;
            }

            var cells = [
                [
                    {name: 'No.', field: 'id', width:'30px', noresize: true, styles:'text-align:center;', formatter: formatter.rowCountCellFormatter, rowSpan: rowSpan },
                    {name: nls.description, field: 'description', width:descriptionWidth, noresize: true, formatter: formatter.treeCellFormatter, rowSpan: rowSpan },
                    {name: nls.type, field: 'type', width:'70px', noresize: true, styles:'text-align:center;', formatter: formatter.typeCellFormatter, rowSpan: rowSpan },
                    {name: nls.unit, field: 'uom_id', width:'70px', noresize: true, styles:'text-align:center;', formatter: formatter.unitIdCellFormatter, rowSpan: rowSpan},
                    {name: nls.rate, field: 'rate-value', width:'75px', styles: "text-align:right;", noresize: true, formatter: formatter.formulaCurrencyCellFormatter, rowSpan: rowSpan}
                ],[]
            ];

            if (!this.gridData.hasOwnProperty('columns')) {

                cells[0].push({
                    name: nls.qty,
                    field: 'quantity_per_unit-value',
                    width:'80px',
                    noresize: true,
                    styles:'text-align:right;',
                    formatter: formatter.currencyCellFormatter
                });

            }else{

                cells[1].push({
                    name: nls.qty,
                    headerClasses: "staticHeader typeHeader1",
                    styles:'text-align:center;',
                    colSpan: Object.keys(this.gridData.columns).length
                });

                var columns = this.gridData.columns;
                Object.keys(columns).forEach(function(idx) {
                    var column = columns[idx];

                    if(importType == buildspace.constants.FILE_IMPORT_TYPE_BUILDSPACE){
                        columnName = column.name + "<br>"+nls.totalUnit+":" + column.qty;
                    }else{
                        columnName = column.name;
                    }

                    cells[0].push({
                        name: columnName,
                        width:'120px',
                        noresize: true,
                        field: 'quantity_per_unit-final_value-'+idx,
                        styles:'text-align:center;',
                        formatter: formatter.numberCellFormatter,
                        headerClasses: "typeHeader1"
                    });
                });
            }

            return [{
                noscroll: false,
                cells: cells
            }];
        },
        makeGridContainer: function(content, title){
            var id = this.bill.id[0];
            var stackContainer = dijit.byId('ProjectBuilder-item_import_list_'+id+'-stackContainer');
            if(stackContainer){
                dijit.byId('ProjectBuilder-item_import_list_'+id+'-stackContainer').destroyRecursive(true);
            }

            stackContainer = new dijit.layout.StackContainer({
                style:'width:100%;height:100%;border:0px;',
                region: "center",
                id: 'ProjectBuilder-item_import_list_'+id+'-stackContainer'
            });

            var stackPane = new dijit.layout.ContentPane({
                title: title,
                content: content
            });

            stackContainer.addChild(stackPane);

            var controller = new dijit.layout.StackController({
                region: "top",
                containerId: 'ProjectBuilder-item_import_list_'+id+'-stackContainer'
            });

            var controllerPane = new dijit.layout.ContentPane({
                style: "padding:0px;overflow:hidden;",
                class: 'breadCrumbTrail',
                region: 'top',
                content: controller
            });

            var borderContainer = new dijit.layout.BorderContainer({
                style:"padding:0px;width:100%;height:100%;border:0px;",
                gutters: false,
                region: 'center'
            });

            borderContainer.addChild(stackContainer);
            borderContainer.addChild(controllerPane);

            dojo.subscribe('ProjectBuilder-item_import_list_'+id+'-stackContainer-selectChild',"",function(page){
                var widget = dijit.byId('ProjectBuilder-item_import_list_'+id+'-stackContainer');
                if(widget){
                    var children = widget.getChildren();
                    var index = dojo.indexOf(children, dijit.byId(page.id));
                    index = index+1;
                    while( children.length > index ){
                        widget.removeChild(children[ index ]);
                        children[index].destroyDescendants();
                        children[index].destroyRecursive();
                        index = index + 1;
                    }
                }
            });

            return borderContainer;
        }
    });

    var FilePreviewGrid = declare('buildspace.apps.ProjectBuilder.FilePreviewGrid', dojox.grid.EnhancedGrid, {
        type: null,
        selectedItem: null,
        dialogWidget: null,
        _csrf_token: null,
        escapeHTMLInData: false,
        style: "border-top:none;",
        uploadUrl: null,
        constructor: function(args){
            this.itemIds = [];
            this.connects = [];
            this.inherited(arguments);
        },
        canSort: function(inSortInfo){
            return false;
        },
        postCreate: function(){
            this.inherited(arguments);
        },
        destroy: function(){
            this.inherited(arguments);
            dojo.forEach(this._connects, connect.disconnect);
            delete this._connects;
        }
    });

    var FilePreviewGridContainer = declare('buildspace.apps.ProjectBuilder.FilePreviewGridContainer', dijit.layout.BorderContainer, {
        stackContainerTitle: '',
        style: "padding:0px;width:100%;height:100%;",
        gutters: false,
        bill: null,
        gridOpts: {},
        postCreate: function(){
            this.inherited(arguments);

            lang.mixin(this.gridOpts, {
                type: this.type,
                bill: this.bill,
                region:"center"
            });

            var grid = this.grid = new FilePreviewGrid(this.gridOpts);
            this.addChild(new dijit.layout.ContentPane({style: 'width:100%', content:grid, region:'center'}));

            var container = dijit.byId('ProjectBuilder-item_import_list_'+this.bill.id[0]+'-stackContainer');
            if(container){
                var node = document.createElement("div");
                var child = new dojox.layout.ContentPane( {title: buildspace.truncateString(this.stackContainerTitle, 60), id: this.pageId, executeScripts: true },node );
                container.addChild(child);
                child.set('content', this);
                container.selectChild(this.pageId);
            }
        }
    });

    var PreviewForm = declare("buildspace.apps.ProjectBuilder.PreviewForm", [_WidgetBase, _OnDijitClickMixin, _TemplatedMixin, _WidgetsInTemplateMixin], {
        templateString: previewFormTemplate,
        baseClass: "buildspace-form",
        region: 'center',
        dialogObj: null,
        bill: null,
        nls: nls,
        uploadUrl: null,
        colData: null,
        fileName: null,
        extension: null,
        importType: null,
        style: "padding:5px;overflow:auto;",
        billColumnSettings: [],
        constructor:function(args){
            this.inherited(arguments);
        },
        postCreate: function() {
            this.inherited(arguments);
            var self = this;

            var options = self.options = [];

            dojo.forEach(this.colData, function(col, i){
                options.push({
                    label: col.name,
                    value: col.name
                });
            });

            var columnSelectStore = new dojo.data.ItemFileReadStore({
                data: {
                    label : "label",
                    identifier : "value",
                    items : options
                }
            });

            self.itemSelect = new FilteringSelect({
                name: "colItem",
                store: columnSelectStore,
                required: false,
                style: "padding:2px;width: 70px;",
                searchAttr: "label"
            }).placeAt(self.itemSelectDivNode);

            self.descriptionSelectFrom = new FilteringSelect({
                name: "colDescriptionFrom",
                store: columnSelectStore,
                style: "padding:2px;width: 70px;",
                searchAttr: "label"
            }).placeAt(self.descriptionFromSelectDivNode);

            self.descriptionSelectTo = new FilteringSelect({
                name: "colDescriptionTo",
                store: columnSelectStore,
                style: "padding:2px;width: 70px;",
                searchAttr: "label"
            }).placeAt(self.descriptionToSelectDivNode);

            self.unitSelect = new FilteringSelect({
                name: "colUnit",
                store: columnSelectStore,
                required: false,
                style: "padding:2px;width: 70px;",
                searchAttr: "label"
            }).placeAt(self.unitSelectDivNode);

            self.rateSelect = new FilteringSelect({
                name: "colRate",
                store: columnSelectStore,
                required: false,
                style: "padding:2px;width: 70px;",
                searchAttr: "label"
            }).placeAt(self.rateSelectDivNode);

            self.amountSelect = new FilteringSelect({
                name: "colAmount",
                store: columnSelectStore,
                required: false,
                style: "padding:2px;width: 70px;",
                searchAttr: "label"
            }).placeAt(self.amountSelectDivNode);

            self.createDynamicQtyColumnsByColumnSetting(columnSelectStore);
        },
        startup: function(){
            this.inherited(arguments);
        },
        close: function(){

        },
        importFile: function(){
            //Add Import Function Here
            var pb = buildspace.dialog.indeterminateProgressBar({
                title:nls.savingData+'...'
            });

            var self = this,
                values = dojo.formToObject(this.fileImportPreviewForm.id);

            if(this.fileImportPreviewForm.validate()){
                this.dialogObj.hide();

                pb.show();

                dojo.xhrPost({
                    url: this.uploadUrl,
                    content: values,
                    handleAs: 'json',
                    load: function(resp){
                        if(resp.success){
                            var  importGridDialog = self.importGridDialog = new FileImportGridDialog({
                                bill: self.bill,
                                gridData: resp,
                                importType: self.importType,
                                excelType: resp.excelType
                            });

                            pb.hide();
                            importGridDialog.show();
                        }
                    },
                    error: function(error) {
                        pb.hide();
                    }
                });
            }
        },
        onCancel: function(){
        },
        createDynamicQtyColumnsByColumnSetting: function(columnSelectStore) {
            var self = this;

            Object.keys(this.billColumnSettings).forEach(function(columnId) {
                var columnSetting = self.billColumnSettings[columnId];

                var node = domConstruct.toDom('<tr><td class="label"><label style="display: inline;">' + columnSetting.name + ' ' + nls.quantity + ':</label><td colspan="3"><div id="quantitySelectDivNode-'+ columnId +'"></div></td><td colspan="4">&nbsp;</td></tr>');

                node = domConstruct.place(node, self.dynamicQuantitySelectDivNode);

                var inputNode = query(node);

                var quantitySelect = new FilteringSelect({
                    name: 'colQty[' + columnId + ']',
                    store: columnSelectStore,
                    required: false,
                    style: "padding:2px;width: 70px;",
                    searchAttr: "label"
                }).placeAt(inputNode[0]['cells'][1]);
            });
        }
    });

    var FilePreviewGridDialog = declare('buildspace.apps.ProjectBuilder.FilePreviewGridDialog', dijit.Dialog, {
        style:"padding:0;margin:0;",
        title: nls.importFile,
        selectedItem: null,
        bill: null,
        billGrid: null,
        previewData: null,
        displayForm: true,
        fileName: null,
        extension: null,
        colData: null,
        importType: null,
        billColumnSettings: [],
        buildRendering: function(){
            var content = null;

            if(this.displayForm){
                content = this.createForm();
            }else{
                content = this.createContent();
            }

            content.startup();
            this.content = content;
            this.inherited(arguments);
        },
        postCreate: function(){
            domStyle.set(this.containerNode, {
                padding:"0",
                margin:"0"
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
        removeUploadedFile: function(){
            dojo.xhrPost({
                url: 'billManagerImportFile/deleteTempFile',
                content: {
                    filename: this.fileName,
                    extension: this.extension
                },
                handleAs: 'json'
            });
        },
        createContent: function(){
            var self = this;
            var borderContainer = new dijit.layout.BorderContainer({
                style:"padding:0px;width:950px;height:500px;",
                gutters: false
            });

            var toolbar = new dijit.Toolbar({
                region: "top",
                style: "outline:none!important;padding:2px;overflow:hidden;"
            });

            toolbar.addChild(
                new dijit.form.Button({
                    label: nls.close,
                    iconClass: "icon-16-container icon-16-close",
                    style:"outline:none!important;",
                    onClick: function(){
                        self.removeUploadedFile();
                        self.hide();
                    }
                })
            );

            var formatter = new GridFormatter(),
                store = dojo.data.ItemFileWriteStore({
                    data: {
                        'identifier' : "id",
                        'items' : this.previewData
                    }
                }),
                content = FilePreviewGridContainer({
                    pageId: 'import_list-page_library-'+this.bill.id[0],
                    bill: this.bill,
                    uploadUrl: "billManagerImportFile/importExcel",
                    gridOpts: {
                        importType: this.importType,
                        store: store,
                        gridData: this.gridData,
                        dialogWidget: this,
                        structure: this.generateStructure()
                    }
                });

            var gridContainer = this.makeGridContainer(content,"Preview");
            borderContainer.addChild(toolbar);
            borderContainer.addChild(gridContainer);

            return borderContainer;
        },
        createForm: function(){
            var self = this;
            var borderContainer = new dijit.layout.BorderContainer({
                style:"padding:0px;width:400px;height:340px;",
                gutters: false
            });

            var toolbar = new dijit.Toolbar({
                region: "top",
                style: "outline:none!important;padding:2px;overflow:hidden;"
            });

            toolbar.addChild(
                new dijit.form.Button({
                    label: nls.close,
                    iconClass: "icon-16-container icon-16-close",
                    style:"outline:none!important;",
                    onClick: function(){
                        self.removeUploadedFile();
                        self.hide();
                    }
                })
            );

            var content = new PreviewForm({
                dialogObj: this,
                bill: this.bill,
                uploadUrl: "billManagerImportFile/importExcel",
                colData: this.colData,
                fileName: this.fileName,
                extension: this.extension,
                importType: this.importType,
                billColumnSettings: this.billColumnSettings
            });

            toolbar.addChild(new dijit.ToolbarSeparator());
            toolbar.addChild(
                new dijit.form.Button({
                    id: 'ImportItemGrid-'+self.bill.id[0]+'_Import-button',
                    label: nls.import,
                    iconClass: "icon-16-container icon-16-import",
                    onClick: function(){
                        content.importFile();
                    }
                })
            );

            var gridContainer = this.makeGridContainer(content,"Preview");
            borderContainer.addChild(toolbar);
            borderContainer.addChild(gridContainer);

            return borderContainer;
        },
        generateStructure: function(){
            var formatter = GridFormatter();

            var structure = [
                {name: 'No.', field: 'id', width:'30px', styles:'text-align:center;', formatter: formatter.rowCountCellFormatter }
            ];

            dojo.forEach(this.colData, function(col, i){
                structure.push({
                    name: col.name,
                    field: col.slug,
                    width: "auto"
                });
            });

            return structure;
        },
        makeGridContainer: function(content, title){
            var id = this.bill.id[0];
            var stackContainer = dijit.byId('ProjectBuilder-item_import_list_'+id+'-stackContainer');
            if(stackContainer){
                dijit.byId('ProjectBuilder-item_import_list_'+id+'-stackContainer').destroyRecursive();
            }

            stackContainer = new dijit.layout.StackContainer({
                style:'width:100%;height:100%;border:0px;',
                region: "center",
                id: 'ProjectBuilder-item_import_list_'+id+'-stackContainer'
            });

            var stackPane = new dijit.layout.ContentPane({
                title: title,
                content: content
            });

            stackContainer.addChild(stackPane);

            var borderContainer = new dijit.layout.BorderContainer({
                style:"padding:0px;width:100%;height:100%;border:0px;",
                gutters: false,
                region: 'center'
            });

            borderContainer.addChild(stackContainer);

            return borderContainer;
        }
    });

    var Form = declare("buildspace.apps.ProjectBuilder.FileImportForm", [_WidgetBase, _OnDijitClickMixin, _TemplatedMixin, _WidgetsInTemplateMixin], {
        templateString: template,
        baseClass: "buildspace-form",
        region: 'center',
        dialogObj: null,
        bill: null,
        nls: nls,
        importType: buildspace.constants.FILE_IMPORT_TYPE_BUILDSPACE,
        uploadUrl: "billManagerImportFile/importBuildspaceExcel",
        style: "padding:5px;overflow:auto;",
        postCreate: function(){
            this.inherited(arguments);
            //attach Complete Event
            var fileUploadField = this.fileUploaderNode;
            fileUploadField.on('Complete', dojo.hitch(this, "uploadComplete"));
            fileUploadField.on('Begin', dojo.hitch(this, "uploadBegin"));
        },
        startup: function(){
            this.inherited(arguments);
        },
        doImportFile: function(){
            //This is where we do Manual Upload if not using fileUploader 'uploadOnSelect' feature
        },
        uploadBegin: function(data){
            var pb = this.pb = buildspace.dialog.indeterminateProgressBar({
                title:nls.uploadingData+'. '+nls.pleaseWait+'...'
            });

            pb.show();
            this.dialogObj.hide();
        },
        uploadComplete:function(data){
            //return Parsed Excel data
            this.pb.hide();
            //Show Grid Dialog for Selection
            if(data.hasOwnProperty('success') && !data.success){
                buildspace.dialog.alert(nls.error,data.errorMsg,80,350);
            }else{
                if(data.preview){
                    var  previewGridDialog = this.previewGridDialog = new FilePreviewGridDialog({
                        bill: this.bill,
                        importType: this.importType,
                        previewData: data.previewData,
                        colData: data.colData,
                        fileName: data.fileName,
                        extension: data.extension,
                        billColumnSettings: data.columns
                    });

                    previewGridDialog.show();
                }else{
                    var importGridDialog = this.importGridDialog = new FileImportGridDialog({
                        bill: this.bill,
                        gridData: data,
                        importType: this.importType
                    });

                    importGridDialog.show();
                }
            }
        }
    });

    return declare('buildspace.apps.ProjectBuilder.FileImportDialog', dijit.Dialog, {
        style:"padding:0;margin:0;",
        title: nls.importFile,
        selectedItem: null,
        bill: null,
        billGrid: null,
        uploadUrl: null,
        importType: null,
        buildRendering: function(){
            var form = this.createForm();
            form.startup();
            this.content = form;
            this.inherited(arguments);
        },
        postCreate: function(){
            domStyle.set(this.containerNode, {
                padding:"0",
                margin:"0"
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
        createForm: function(){
            var borderContainer = new dijit.layout.BorderContainer({
                style:"padding:0;width:450px;height:80px;",
                gutters: false
            });

            var toolbar = new dijit.Toolbar({
                region: "top",
                style: "outline:none!important;padding:2px;overflow:hidden;"
            });

            toolbar.addChild(
                new dijit.form.Button({
                    label: nls.close,
                    iconClass: "icon-16-container icon-16-close",
                    style:"outline:none!important;",
                    onClick: dojo.hitch(this, 'hide')
                })
            );

            borderContainer.addChild(toolbar);
            borderContainer.addChild(new Form({
                dialogObj: this,
                bill: this.bill,
                uploadUrl: this.uploadUrl,
                importType: this.importType
            }));

            return borderContainer;
        }
    });
});