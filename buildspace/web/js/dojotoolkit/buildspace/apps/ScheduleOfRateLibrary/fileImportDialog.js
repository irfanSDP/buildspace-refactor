define('buildspace/apps/ScheduleOfRateLibrary/fileImportDialog',[
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
    "dijit/form/FilteringSelect",
    'dijit/Tooltip',
    'dojox/grid/enhanced/plugins/Pagination',
    'dojox/grid/enhanced/plugins/Filter',
    'dojox/form/uploader/plugins/Flash'
], function(declare, lang, connect, when, html, dom, keys, domStyle, GridFormatter, IndirectSelection, _WidgetBase, _OnDijitClickMixin, _TemplatedMixin, _WidgetsInTemplateMixin, template, previewFormTemplate, nls, ToggleButton, RadioButton, FileUploader, Select, FilteringSelect, Tooltip){

    var FileImportGrid = declare('buildspace.apps.ScheduleOfRateLibrary.FileImportGrid', dojox.grid.EnhancedGrid, {
        type: null,
        selectedItem: null,
        scheduleOfRateId: 0,
        dialogWidget: null,
        _csrf_token: null,
        scheduleOfRateGrid: null,
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
                      /*page step to be displayed*/
              maxPageStep: 4,
                      /*position of the pagination bar*/
              position: "bottom"
            },
            filter: {
                // Show the closeFilterbarButton at the filter bar
                closeFilterbarButton: false,
                // Set the maximum rule count to 5
                ruleCount: 5,
                // Set the name of the items
                itemsName: "files"
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
            var importBtn = dijit.byId('ImportItemGridPreview-'+this.scheduleOfRateId+'_Import-button');

            if(importBtn)
                importBtn._setDisabledAttr(isDisable);
        },
        import: function(withRate){
            var self = this,
                scheduleOfRateGrid = self.scheduleOfRateGrid,
                itemIds = self.itemIds;

            self.dialogWidget.hide();

            if( itemIds.length > 0){
                var pb = buildspace.dialog.indeterminateProgressBar({
                        title:nls.savingData+'. '+nls.pleaseWait+'...'
                    }),
                    rowsToMove = [];
                    pb.show();

                var xhrArgs = {
                    url: 'scheduleOfRate/saveImportedExcel',
                    content: {
                        schedule_of_rate_id: self.scheduleOfRateId,
                        ids: [itemIds],
                        with_rate: withRate,
                        _csrf_token: self._csrf_token,
                        filename: self.gridData.filename,
                        uploadPath: self.gridData.uploadPath
                    },
                    handleAs: 'json',
                    load: function(resp)
                    {
                        var store = self.scheduleOfRateGrid.store;

                        if(resp.success){
                            store.fetchItemByIdentity({ 'identity' : buildspace.constants.GRID_LAST_ROW,  onItem : function(lastItem){
                                dojo.forEach(resp.items,function(data){
                                    if(data.id > 0){
                                        data._csrf_token = lastItem._csrf_token;

                                        var item = store.newItem(data);
                                        store.save();

                                        var itemIdx = self.scheduleOfRateGrid.getItemIndex(item);
                                        var rowIndex = self.scheduleOfRateGrid.getItemIndex(lastItem);
                                        self.scheduleOfRateGrid.rearranger.moveRows([itemIdx], rowIndex);
                                    }
                                });
                            }});
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

    var FileImportGridContainer = declare('buildspace.apps.ScheduleOfRateLibrary.FileImportGridContainer', dijit.layout.BorderContainer, {
        stackContainerTitle: '',
        style: "padding:0px;width:100%;height:100%;",
        gutters: false,
        scheduleOfRateId: 0,
        scheduleOfRateGrid: null,
        withRate: false,
        gridOpts: {},
        postCreate: function(){
            var self = this;
            self.inherited(arguments);
            lang.mixin(self.gridOpts, { type: self.type, scheduleOfRateId: self.scheduleOfRateId, region:"center" });
            var grid = this.grid = new FileImportGrid(self.gridOpts);
            if(self.type != 'tree'){
                var toolbar = new dijit.Toolbar({region: "top", style:"padding:2px;border-bottom:none;width:100%;"});
                toolbar.addChild(
                    new dijit.form.Button({
                        id: 'ImportItemGridPreview-'+self.scheduleOfRateId+'_Import-button',
                        label: nls.import,
                        iconClass: "icon-16-container icon-16-import",
                        onClick: function(){
                            grid.import(self.withRate);
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

            var container = dijit.byId('ScheduleOfRateLibrary-item_import_list_'+this.scheduleOfRateId+'_'+this.scheduleOfRateTradeId+'-stackContainer');
            if(container){
                var node = document.createElement("div");
                var child = new dojox.layout.ContentPane( {title: buildspace.truncateString(self.stackContainerTitle, 60), id: self.pageId, executeScripts: true },node );
                container.addChild(child);
                child.set('content', self);
                container.selectChild(self.pageId);
            }
        }
    });

    var FileImportGridDialog = declare('buildspace.apps.ScheduleOfRateLibrary.FileImportGridDialog', dijit.Dialog, {
        style:"padding:0px;margin:0px;",
        title: nls.importFile,
        selectedItem: null,
        scheduleOfRateId: 0,
        elementId: 0,
        scheduleOfRateGrid: null,
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
                url: 'scheduleOfRate/deleteTempFile',
                content: values,
                handleAs: 'json'
            };

            dojo.xhrPost(xhrArgs);
        },
        createContent: function()
        {
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

            var TradeData = {
                'identifier' : "id",
                'items' : self.gridData.Trades
            };

            var formatter = new GridFormatter(),
                store = dojo.data.ItemFileWriteStore({
                    data: TradeData
                });

            var content = FileImportGridContainer({
                    stackContainerTitle: nls.elements,
                    pageId: 'import_list-page_library-'+this.scheduleOfRateId+'_'+this.elementId,
                    scheduleOfRateId: this.scheduleOfRateId,
                    scheduleOfRateTradeId: this.elementId,
                    gridOpts: {
                        store: store,
                        gridData: this.gridData,
                        scheduleOfRateGrid: self.scheduleOfRateGrid,
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
                'items' : self.gridData.TradesToItem[element.id]
            };

            var store = new dojo.data.ItemFileWriteStore({
                    data: ItemData
                });

            var grid = FileImportGridContainer({
                stackContainerTitle: element.description,
                pageId: 'import_list-page_item-'+this.scheduleOfRateId+'_'+this.elementId,
                scheduleOfRateId: self.scheduleOfRateId,
                scheduleOfRateTradeId: self.elementId,
                type: 'tree',
                gridOpts: {
                    store: store,
                    dialogWidget: self,
                    gridData: this.gridData,
                    selectedItem: self.selectedItem,
                    scheduleOfRateGrid: self.scheduleOfRateGrid,
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
                        {name: nls.rate, field: 'rate-value', width:'80px', styles:'text-align:right;', formatter: formatter.formulaCurrencyCellFormatter}
                    ];
            }

            return structure;
        },
        makeGridContainer: function(content, title){
            var id = this.scheduleOfRateId+'_'+this.elementId;
            var stackContainer = dijit.byId('ScheduleOfRateLibrary-item_import_list_'+id+'-stackContainer');
            if(stackContainer){
                dijit.byId('ScheduleOfRateLibrary-item_import_list_'+id+'-stackContainer').destroyRecursive();
            }

            stackContainer = new dijit.layout.StackContainer({
                style:'width:100%;height:100%;border:0px;',
                region: "center",
                id: 'ScheduleOfRateLibrary-item_import_list_'+id+'-stackContainer'
            });

            var stackPane = new dijit.layout.ContentPane({
                title: title,
                content: content
            });

            stackContainer.addChild(stackPane);

            var controller = new dijit.layout.StackController({
                region: "top",
                containerId: 'ScheduleOfRateLibrary-item_import_list_'+id+'-stackContainer'
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

            dojo.subscribe('ScheduleOfRateLibrary-item_import_list_'+id+'-stackContainer-selectChild',"",function(page){
                var widget = dijit.byId('ScheduleOfRateLibrary-item_import_list_'+id+'-stackContainer');
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

    var FilePreviewGrid = declare('buildspace.apps.ScheduleOfRateLibrary.FilePreviewGrid', dojox.grid.EnhancedGrid, {
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

    var FilePreviewGridContainer = declare('buildspace.apps.ScheduleOfRateLibrary.FilePreviewGridContainer', dijit.layout.BorderContainer, {
        stackContainerTitle: '',
        style: "padding:0px;width:100%;height:100%;",
        gutters: false,
        scheduleOfRateId: 0,
        gridOpts: {},
        postCreate: function(){
            var self = this;
            self.inherited(arguments);
            lang.mixin(self.gridOpts, { type: self.type, scheduleOfRateId: self.scheduleOfRateId, region:"center" });
            var grid = this.grid = new FilePreviewGrid(self.gridOpts);
            self.addChild(new dijit.layout.ContentPane({style: 'width:100%', content:grid, region:'center'}));

            var container = dijit.byId('ScheduleOfRateLibrary-item_import_list_'+this.scheduleOfRateId+'-stackContainer');
            if(container){
                var node = document.createElement("div");
                var child = new dojox.layout.ContentPane( {title: buildspace.truncateString(self.stackContainerTitle, 60), id: self.pageId, executeScripts: true },node );
                container.addChild(child);
                child.set('content', self);
                container.selectChild(self.pageId);
            }
        }
    });

    var PreviewForm = declare("buildspace.apps.ScheduleOfRateLibrary.PreviewForm", [_WidgetBase, _OnDijitClickMixin, _TemplatedMixin, _WidgetsInTemplateMixin], {
        templateString: previewFormTemplate,
        baseClass: "buildspace-form",
        region: 'center',
        dialogObj: null,
        scheduleOfRateId: -1,
        nls: nls,
        uploadUrl: null,
        colData: null,
        fileName: null,
        extension: null,
        style: "padding:5px;overflow:auto;",
        constructor:function(args){
            this.inherited(arguments);
        },
        postCreate: function()
        {
            this.inherited(arguments);
            var self = this;

            var options = self.options = [];

            dojo.forEach(this.colData, function(col, i)
            {
                options.push({
                    label: col.name,
                    value: col.name
                });
            });

            var colDataArray = {
                label : "label",
                identifier : "value",
                items : options
            };

            var columnSelectStore = new dojo.data.ItemFileReadStore({
                data: colDataArray
            });

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
                values = dojo.formToObject(self.fileImportPreviewForm.id);

            var xhrArgs = {
                    url: self.uploadUrl,
                    content: values,
                    handleAs: 'json',
                    load: function(resp)
                    {
                        if(resp.success){
                            var importGridDialog = self.importGridDialog = new FileImportGridDialog({
                                scheduleOfRateId: self.scheduleOfRateId,
                                gridData: resp,
                                excelType: resp.excelType,
                                scheduleOfRateGrid: self.scheduleOfRateGrid
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

    var FilePreviewGridDialog = declare('buildspace.apps.ScheduleOfRateLibrary.FilePreviewGridDialog', dijit.Dialog, {
        style:"padding:0px;margin:0px;",
        title: nls.importFile,
        selectedItem: null,
        scheduleOfRateId: 0,
        scheduleOfRateGrid: null,
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
                url: 'scheduleOfRate/deleteTempFile',
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
                    pageId: 'import_list-page_library-'+this.scheduleOfRateId,
                    scheduleOfRateId: this.scheduleOfRateId,
                    uploadUrl: "scheduleOfRate/importExcel",
                    gridOpts: {
                        store: store,
                        gridData: this.gridData,
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
                style:"padding:0px;width:400px;height:170px;",
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
                scheduleOfRateId: self.scheduleOfRateId,
                uploadUrl: "scheduleOfRate/importExcel",
                colData: self.colData,
                fileName: self.fileName,
                scheduleOfRateGrid: self.scheduleOfRateGrid,
                extension: self.extension
            });

            toolbar.addChild(new dijit.ToolbarSeparator());
            toolbar.addChild(
                new dijit.form.Button({
                    id: 'ImportItemGrid-'+self.scheduleOfRateId+'_Import-button',
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
            var id = this.scheduleOfRateId;
            var stackContainer = dijit.byId('ScheduleOfRateLibrary-item_import_list_'+id+'-stackContainer');
            if(stackContainer){
                dijit.byId('ScheduleOfRateLibrary-item_import_list_'+id+'-stackContainer').destroyRecursive();
            }

            stackContainer = new dijit.layout.StackContainer({
                style:'width:100%;height:100%;border:0px;',
                region: "center",
                id: 'ScheduleOfRateLibrary-item_import_list_'+id+'-stackContainer'
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

    var Form = declare("buildspace.apps.ScheduleOfRateLibrary.FileImportForm", [_WidgetBase, _OnDijitClickMixin, _TemplatedMixin, _WidgetsInTemplateMixin], {
        templateString: template,
        baseClass: "buildspace-form",
        region: 'center',
        dialogObj: null,
        scheduleOfRateId: -1,
        scheduleOfRateGrid: null,
        nls: nls,
        uploadUrl: "scheduleOfRate/importBuildspaceExcel",
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
            if(data.preview)
            {
                var previewGridDialog = self.previewGridDialog = new FilePreviewGridDialog({
                    scheduleOfRateId: self.scheduleOfRateId,
                    previewData: data.previewData,
                    colData: data.colData,
                    fileName: data.fileName,
                    extension: data.extension,
                    scheduleOfRateGrid: self.scheduleOfRateGrid
                });

                previewGridDialog.show();
            }
            else
            {
                var importGridDialog = self.importGridDialog = new FileImportGridDialog({
                    scheduleOfRateId: self.scheduleOfRateId,
                    gridData: data,
                    excelType: data.excelType,
                    scheduleOfRateGrid: self.scheduleOfRateGrid
                });

                importGridDialog.show();
            }
        },
        close: function(){

        },
        onCancel: function(){

        }
    });

    var Dialog = declare('buildspace.apps.ScheduleOfRateLibrary.FileImportDialog', dijit.Dialog, {
        style:"padding:0px;margin:0px;",
        title: nls.importFile,
        selectedItem: null,
        scheduleOfRateId: 0,
        scheduleOfRateGrid: null,
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
                scheduleOfRateId: self.scheduleOfRateId,
                uploadUrl: self.uploadUrl,
                importType: self.importType,
                scheduleOfRateGrid: self.scheduleOfRateGrid
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
            var id = this.scheduleOfRateId;
            var stackContainer = dijit.byId('ScheduleOfRateLibrary-file_import_'+id+'-stackContainer');
            if(stackContainer){
                dijit.byId('ScheduleOfRateLibrary-file_import_'+id+'-stackContainer').destroyRecursive();
            }

            stackContainer = new dijit.layout.StackContainer({
                style:'width:100%;height:100%;border:0px;',
                region: "center",
                id: 'ScheduleOfRateLibrary-file_import_'+id+'-stackContainer'
            });

            var stackPane = new dijit.layout.ContentPane({
                title: title,
                content: content
            });

            stackContainer.addChild(stackPane);

            var controller = new dijit.layout.StackController({
                region: "top",
                containerId: 'ScheduleOfRateLibrary-file_import_'+id+'-stackContainer'
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

    return Dialog;
});