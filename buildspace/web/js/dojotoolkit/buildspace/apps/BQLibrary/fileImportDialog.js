define('buildspace/apps/BQLibrary/fileImportDialog',[
    'dojo/_base/declare',
    'dojo/_base/lang',
    "dojo/_base/connect",
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
    'dijit/Tooltip',
    'dojox/grid/enhanced/plugins/Pagination',
    'dojox/form/uploader/plugins/Flash'
], function(declare, lang, connect, when, html, dom, keys, domStyle, GridFormatter, IndirectSelection, _WidgetBase, _OnDijitClickMixin, _TemplatedMixin, _WidgetsInTemplateMixin, template, previewFormTemplate, nls, ToggleButton, RadioButton, FileUploader, Select, Tooltip){

    var FileImportGrid = declare('buildspace.apps.BQLibrary.FileImportGrid', dojox.grid.EnhancedGrid, {
        type: null,
        selectedItem: null,
        libraryId: 0,
        dialogWidget: null,
        _csrf_token: null,
        libraryGrid: null,
        escapeHTMLInData: false,
        gridData: null,
        style: "border-top:none;",
        plugins: {
            pagination: {
              pageSizes: ["20", "40", "80", "All"],
              description: true,
              sizeSwitch: true,
              pageStepper: true,
              gotoButton: true,
              defaultPageSize: 20,
              maxPageStep: 4,
              position: "bottom"
            }
        },
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
            var importBtn = dijit.byId('ImportItemGrid-'+this.libraryId+'_Import-button');

            if(importBtn)
                importBtn._setDisabledAttr(isDisable);
        },
        import: function(withRate, withQuantity, withBillRef){
            var self = this,
                libraryGrid = self.libraryGrid,
                itemIds = self.itemIds;

            self.dialogWidget.hide();

            if( itemIds.length > 0){
                var pb = buildspace.dialog.indeterminateProgressBar({
                        title:nls.savingData+'. '+nls.pleaseWait+'...'
                    }),
                    rowsToMove = [];
                    pb.show();

                var xhrArgs = {
                    url: 'bqLibrary/saveImportedExcel',
                    content: {
                        library_id: self.libraryId,
                        ids: [itemIds],
                        with_rate: withRate,
                        with_quantity: withQuantity,
                        with_billRef: withBillRef,
                        _csrf_token: self._csrf_token,
                        filename: self.gridData.filename,
                        extension: self.gridData.extension,
                        uploadPath: self.gridData.uploadPath
                    },
                    handleAs: 'json',
                    load: function(resp) {
                        if(resp.success){
                            var store = self.libraryGrid.store;

                            // refresh the grid
                            self.libraryGrid.store.close();

                            self.libraryGrid.setStore(store);
                        }

                        self.itemIds = [];
                        pb.hide();

                    },
                    error: function(error) {
                        self.itemIds = [];
                        pb.hide();
                    }
                };
                dojo.xhrPost(xhrArgs);
            }
        },
        onCellMouseOver: function(e){
            var cell = e.cell;
            var fieldName = cell.field;
            var item = this.getItem(e.rowIndex);

            if(item[fieldName+'-msg'] != undefined && item[fieldName+'-msg'][0]){
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
        destroy: function(){
            this.inherited(arguments);
            dojo.forEach(this._connects, connect.disconnect);
            delete this._connects;
        }
    });

    var FileImportGridContainer = declare('buildspace.apps.BQLibrary.FileImportGridContainer', dijit.layout.BorderContainer, {
        stackContainerTitle: '',
        style: "padding:0px;width:100%;height:100%;",
        gutters: false,
        libraryId: 0,
        withRate: false,
        withQuantity: false,
        withBillRef: false,
        gridOpts: {},
        postCreate: function(){
            var self = this;
            self.inherited(arguments);
            lang.mixin(self.gridOpts, { type: self.type, libraryId: self.libraryId, region:"center" });
            var grid = this.grid = new FileImportGrid(self.gridOpts);
            if(self.type != 'tree'){
                var toolbar = new dijit.Toolbar({region: "top", style:"padding:2px;border-bottom:none;width:100%;"});
                toolbar.addChild(
                    new dijit.form.Button({
                        id: 'ImportItemGrid-'+self.libraryId+'_Import-button',
                        label: nls.import,
                        iconClass: "icon-16-container icon-16-import",
                        onClick: function(){
                            grid.import(self.withRate, self.withQuantity, self.withBillRef);
                        }
                    })
                );
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

                self.addChild(toolbar);
            }
            self.addChild(new dijit.layout.ContentPane({style: 'width:100%', content:grid, region:'center'}));

            var container = dijit.byId('BQLibrary-item_import_list_'+this.libraryId+'_'+this.billElementId+'-stackContainer');
            if(container){
                var node = document.createElement("div");
                var child = new dojox.layout.ContentPane( {title: buildspace.truncateString(self.stackContainerTitle, 60), id: self.pageId, executeScripts: true },node );
                container.addChild(child);
                child.set('content', self);
                container.selectChild(self.pageId);
            }
        }
    });

    var FileImportGridDialog = declare('buildspace.apps.BQLibrary.FileImportGridDialog', dijit.Dialog, {
        style:"padding:0px;margin:0px;",
        title: nls.importFile,
        selectedItem: null,
        libraryId: 0,
        elementId: 0,
        libraryGrid: null,
        gridData: null,
        excelType: buildspace.constants.EXCEL_TYPE_SINGLE,
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
        removeUploadedFile: function(){
            var self = this;

            var values = {
                filename: self.gridData.filename,
                extension: self.gridData.extension
            };

            var xhrArgs = {
                url: 'bqLibrary/deleteTempFile',
                content: values,
                handleAs: 'json'
            };

            dojo.xhrPost(xhrArgs);
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

            var ElementData = {
                'identifier' : "id",
                'items' : self.gridData.Elements
            };

            var formatter = new GridFormatter(),
                store = dojo.data.ItemFileWriteStore({
                    data: ElementData
                }),
                content = FileImportGridContainer({
                    stackContainerTitle: nls.elements,
                    pageId: 'import_list-page_library-'+this.libraryId+'_'+this.elementId,
                    libraryId: this.libraryId,
                    billElementId: this.elementId,
                    gridOpts: {
                        store: store,
                        gridData: this.gridData,
                        libraryGrid: self.libraryGrid,
                        dialogWidget: self,
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
            var gridContainer = this.makeGridContainer(content,nls.elements);
            borderContainer.addChild(toolbar);
            borderContainer.addChild(gridContainer);

            return borderContainer;
        },
        createItemGrid: function(element){
            var self = this;

            var ItemData = {
                'identifier' : "id",
                'items' : self.gridData.ElementsToItem[element.id]
            };

            var store = new dojo.data.ItemFileWriteStore({
                    data: ItemData
                });

            var grid = FileImportGridContainer({
                stackContainerTitle: element.description,
                pageId: 'import_list-page_item-'+this.libraryId+'_'+this.elementId,
                libraryId: self.libraryId,
                billElementId: self.elementId,
                type: 'tree',
                gridOpts: {
                    store: store,
                    dialogWidget: self,
                    gridData: this.gridData,
                    selectedItem: self.selectedItem,
                    libraryGrid: self.libraryGrid,
                    _csrf_token: element._csrf_token,
                    structure: this.getStructureByExcelType()
                }
            });
        },
        getStructureByExcelType: function(){
            var self = this;
            var structure = null;
            var formatter = GridFormatter();

            switch(self.excelType){
            case buildspace.constants.EXCEL_TYPE_MULTIPLE:
                break;
            default:
                structure = [
                        {name: 'No.', field: 'id', width:'30px', styles:'text-align:center;', formatter: formatter.rowCountCellFormatter },
                        {name: nls.description, field: 'description', width:'auto', formatter: formatter.treeCellFormatter },
                        {name: nls.type, field: 'type', width:'70px', styles:'text-align:center;', formatter: formatter.typeCellFormatter },
                        {name: nls.unit, field: 'uom_id', width:'70px', styles:'text-align:center;', formatter: formatter.unitIdCellFormatter},
                        {name: nls.qty, field: 'quantity_per_unit-value', width:'80px', styles:'text-align:right;', formatter: formatter.formulaCurrencyCellFormatter},
                        {name: nls.rate, field: 'rate-value', width:'80px', styles:'text-align:right;', formatter: formatter.formulaCurrencyCellFormatter},
                        {name: nls.amount, field: 'amount', width:'80px', styles:'text-align:right;', formatter: formatter.unEditableNumberCellFormatter}
                    ];
            }

            return structure;
        },
        makeGridContainer: function(content, title){
            var id = this.libraryId+'_'+this.elementId;
            var stackContainer = dijit.byId('BQLibrary-item_import_list_'+id+'-stackContainer');
            if(stackContainer){
                dijit.byId('BQLibrary-item_import_list_'+id+'-stackContainer').destroyRecursive();
            }

            stackContainer = new dijit.layout.StackContainer({
                style:'width:100%;height:100%;border:0px;',
                region: "center",
                id: 'BQLibrary-item_import_list_'+id+'-stackContainer'
            });

            var stackPane = new dijit.layout.ContentPane({
                title: title,
                content: content
            });

            stackContainer.addChild(stackPane);

            var controller = new dijit.layout.StackController({
                region: "top",
                containerId: 'BQLibrary-item_import_list_'+id+'-stackContainer'
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

            dojo.subscribe('BQLibrary-item_import_list_'+id+'-stackContainer-selectChild',"",function(page){
                var widget = dijit.byId('BQLibrary-item_import_list_'+id+'-stackContainer');
                if(widget){
                    var children = widget.getChildren();
                    var index = dojo.indexOf(children, dijit.byId(page.id));
                    index = index+1;
                    while( children.length > index ){
                        widget.removeChild(children[ index ]);
                        children[ index ].destroyRecursive(true);
                        index = index + 1;
                    }
                }
            });

            return borderContainer;
        }
    });

    var FilePreviewGrid = declare('buildspace.apps.BQLibrary.FilePreviewGrid', dojox.grid.EnhancedGrid, {
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
            var self = this;
            self.inherited(arguments);
        },
        import: function(){

        },
        destroy: function(){
            this.inherited(arguments);
            dojo.forEach(this._connects, connect.disconnect);
            delete this._connects;
        }
    });

    var FilePreviewGridContainer = declare('buildspace.apps.BQLibrary.FilePreviewGridContainer', dijit.layout.BorderContainer, {
        stackContainerTitle: '',
        style: "padding:0px;width:100%;height:100%;",
        gutters: false,
        libraryId: 0,
        gridOpts: {},
        postCreate: function(){
            var self = this;
            self.inherited(arguments);
            lang.mixin(self.gridOpts, { type: self.type, libraryId: self.libraryId, region:"center" });
            var grid = this.grid = new FilePreviewGrid(self.gridOpts);
            self.addChild(new dijit.layout.ContentPane({style: 'width:100%', content:grid, region:'center'}));

            var container = dijit.byId('BQLibrary-item_import_list_'+this.libraryId+'-stackContainer');
            if(container){
                var node = document.createElement("div");
                var child = new dojox.layout.ContentPane( {title: buildspace.truncateString(self.stackContainerTitle, 60), id: self.pageId, executeScripts: true },node );
                container.addChild(child);
                child.set('content', self);
                container.selectChild(self.pageId);
            }
        }
    });

    var PreviewForm = declare("buildspace.apps.BQLibrary.PreviewForm", [_WidgetBase, _OnDijitClickMixin, _TemplatedMixin, _WidgetsInTemplateMixin], {
        templateString: previewFormTemplate,
        baseClass: "buildspace-form",
        region: 'center',
        dialogObj: null,
        libraryId: -1,
        nls: nls,
        uploadUrl: null,
        colData: null,
        fileName: null,
        extension: null,
        style: "padding:5px;overflow:auto;",
        constructor:function(args){
            this.inherited(arguments);
        },
        postCreate: function(){
            this.inherited(arguments);
            var self = this;

            var options = self.options = [];

            dojo.forEach(this.colData, function(col, i){
                options.push({
                    label: col.name,
                    value: col.name
                });
            });

            self.itemSelect = new Select({
                name: "colItem",
                options: options,
                searchAttr: "value"
            }).placeAt(self.itemSelectDivNode);

            self.descriptionSelect = new Select({
                name: "colDescriptionFrom",
                options: options,
                searchAttr: "value"
            }).placeAt(self.descriptionFromSelectDivNode);

            self.descriptionSelect = new Select({
                name: "colDescriptionTo",
                options: options,
                searchAttr: "value"
            }).placeAt(self.descriptionToSelectDivNode);

            self.unitSelect = new Select({
                name: "colUnit",
                options: options,
                searchAttr: "value"
            }).placeAt(self.unitSelectDivNode);

            self.rateSelect = new Select({
                name: "colRate",
                options: options,
                searchAttr: "value"
            }).placeAt(self.rateSelectDivNode);

            self.quantitySelect = new Select({
                name: "colQty",
                options: options,
                searchAttr: "value"
            }).placeAt(self.quantitySelectDivNode);


            self.amountSelect = new Select({
                name: "colAmount",
                options: options,
                searchAttr: "value"
            }).placeAt(self.amountSelectDivNode);

        },
        startup: function(){
            this.inherited(arguments);
        },
        close: function(){

        },
        import: function(){
            //Add Import Function Here
            var pb = buildspace.dialog.indeterminateProgressBar({
                title:nls.savingData+'...'
            });

            var self = this,
                values = dojo.formToObject(self.fileImportPreviewForm.id),
                xhrArgs = {
                    url: self.uploadUrl,
                    content: values,
                    handleAs: 'json',
                    load: function(resp) {
                        if(resp.success){
                            var  importGridDialog = self.importGridDialog = new FileImportGridDialog({
                                libraryId: self.libraryId,
                                gridData: resp,
                                excelType: resp.excelType,
                                libraryGrid: self.libraryGrid
                            });

                            pb.hide();
                            importGridDialog.show();
                        }
                    },
                    error: function(error) {
                        pb.hide();
                        console.log(error);
                    }
                };

            if(this.fileImportPreviewForm.validate()){
                self.dialogObj.hide();
                pb.show();
                dojo.xhrPost(xhrArgs);
            }
        },
        onCancel: function(){

        }
    });

    var FilePreviewGridDialog = declare('buildspace.apps.BQLibrary.FilePreviewGridDialog', dijit.Dialog, {
        style:"padding:0px;margin:0px;",
        title: nls.importFile,
        selectedItem: null,
        libraryId: 0,
        libraryGrid: null,
        previewData: null,
        displayForm: true,
        fileName: null,
        extension: null,
        colData: null,
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
        removeUploadedFile: function(){
            var self = this;

            var values = {
                filename: self.fileName,
                extension: self.extension
            };

            var xhrArgs = {
                url: 'bqLibrary/deleteTempFile',
                content: values,
                handleAs: 'json'
            };

            dojo.xhrPost(xhrArgs);
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

            var PreviewData = {
                'identifier' : "id",
                'items' : self.previewData
            };

            var formatter = new GridFormatter(),
                store = dojo.data.ItemFileWriteStore({
                    data: PreviewData
                }),
                content = FilePreviewGridContainer({
                    pageId: 'import_list-page_library-'+this.libraryId,
                    libraryId: this.libraryId,
                    uploadUrl: "bqLibrary/importExcel",
                    gridOpts: {
                        store: store,
                        gridData: this.gridData,
                        libraryGrid: self.libraryGrid,
                        dialogWidget: self,
                        structure: self.generateStructure()
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
                style:"padding:0px;width:300px;height:200px;",
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
                dialogObj: self,
                libraryId: self.libraryId,
                uploadUrl: "bqLibrary/importExcel",
                colData: self.colData,
                fileName: self.fileName,
                extension: self.extension,
                libraryGrid: self.libraryGrid
            });

            toolbar.addChild(new dijit.ToolbarSeparator());
            toolbar.addChild(
                new dijit.form.Button({
                    id: 'ImportItemGrid-'+self.libraryId+'_Import-button',
                    label: nls.import,
                    iconClass: "icon-16-container icon-16-import",
                    onClick: function(){
                        content.import();
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
            var id = this.libraryId;
            var stackContainer = dijit.byId('BQLibrary-item_import_list_'+id+'-stackContainer');
            if(stackContainer){
                dijit.byId('BQLibrary-item_import_list_'+id+'-stackContainer').destroyRecursive();
            }

            stackContainer = new dijit.layout.StackContainer({
                style:'width:100%;height:100%;border:0px;',
                region: "center",
                id: 'BQLibrary-item_import_list_'+id+'-stackContainer'
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

    var Form = declare("buildspace.apps.BQLibrary.FileImportForm", [_WidgetBase, _OnDijitClickMixin, _TemplatedMixin, _WidgetsInTemplateMixin], {
        templateString: template,
        baseClass: "buildspace-form",
        region: 'center',
        dialogObj: null,
        libraryId: -1,
        libraryGrid: null,
        nls: nls,
        uploadUrl: "bqLibrary/importBuildspaceExcel",
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
            var self = this;
            var pb = self.pb = buildspace.dialog.indeterminateProgressBar({
                title:nls.uploadingData+'. '+nls.pleaseWait+'...'
            });

            pb.show();
            self.dialogObj.hide();
        },
        uploadComplete:function(data){
            //return Parsed Excel data
            var self = this;
            self.pb.hide();
            //Show Grid Dialog for Selection

            if(data.preview){
                var  previewGridDialog = self.previewGridDialog = new FilePreviewGridDialog({
                    libraryId: self.libraryId,
                    previewData: data.previewData,
                    colData: data.colData,
                    fileName: data.fileName,
                    extension: data.extension,
                    libraryGrid: self.libraryGrid
                });

                previewGridDialog.show();
            }else{
                var  importGridDialog = self.importGridDialog = new FileImportGridDialog({
                    libraryId: self.libraryId,
                    gridData: data,
                    excelType: data.excelType,
                    libraryGrid: self.libraryGrid
                });

                importGridDialog.show();
            }
        },
        close: function(){

        },
        onCancel: function(){

        }
    });

    return declare('buildspace.apps.BQLibrary.FileImportDialog', dijit.Dialog, {
        style:"padding:0px;margin:0px;",
        title: nls.importFile,
        selectedItem: null,
        libraryId: -1,
        libraryGrid: null,
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
        createForm: function(){
            var self = this;

            var borderContainer = new dijit.layout.BorderContainer({
                style:"padding:0px;width:400px;height:80px;",
                gutters: false
            });

            var form = new Form({
                dialogObj: self,
                libraryId: self.libraryId,
                uploadUrl: self.uploadUrl,
                importType: self.importType,
                libraryGrid: self.libraryGrid
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
            toolbar.addChild(new dijit.ToolbarSeparator());

            borderContainer.addChild(toolbar);
            borderContainer.addChild(form);

            return borderContainer;
        },
        makeGridContainer: function(content, title){
            var id = this.libraryId;
            var stackContainer = dijit.byId('BQLibrary-file_import_'+id+'-stackContainer');
            if(stackContainer){
                dijit.byId('BQLibrary-file_import_'+id+'-stackContainer').destroyRecursive();
            }

            stackContainer = new dijit.layout.StackContainer({
                style:'width:100%;height:100%;border:0px;',
                region: "center",
                id: 'BQLibrary-file_import_'+id+'-stackContainer'
            });

            var stackPane = new dijit.layout.ContentPane({
                title: title,
                content: content
            });

            stackContainer.addChild(stackPane);

            var controller = new dijit.layout.StackController({
                region: "top",
                containerId: 'BQLibrary-file_import_'+id+'-stackContainer'
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

            return borderContainer;
        }
    });
});