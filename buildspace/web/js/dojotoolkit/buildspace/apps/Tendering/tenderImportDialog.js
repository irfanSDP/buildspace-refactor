define('buildspace/apps/Tendering/tenderImportDialog',[
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
    "dijit/ProgressBar",
    "dijit/_WidgetBase",
    "dijit/_OnDijitClickMixin",
    "dijit/_TemplatedMixin",
    "dijit/_WidgetsInTemplateMixin",
    "dojo/text!./templates/tenderImportForm.html",
    "dojo/text!./templates/projectInfoForm.html",
    'dojo/i18n!buildspace/nls/TenderImport',
    'dojox/form/Uploader'
], function(declare, lang, connect, when, html, dom, keys, domStyle, GridFormatter, IndirectSelection, ProgressBar, _WidgetBase, _OnDijitClickMixin, _TemplatedMixin, _WidgetsInTemplateMixin, template, projectInfoTemplate,nls, FileUploader){

    var Formatter = {
        rowCountCellFormatter: function(cellValue, rowIdx){
            return cellValue > 0 ? cellValue : '';
        },
        treeCellFormatter: function(cellValue, rowIdx){
            var item = this.grid.getItem(rowIdx);
            var level = parseInt(String(item.level))*16;
            cellValue = cellValue == null ? '&nbsp': cellValue;
            if(item && parseInt(String(item.type)) < buildspace.apps.Tendering.ProjectStructureConstants.TYPE_BILL){
                cellValue =  '<b>'+cellValue+'</b>';
            }
            cellValue = '<div class="treeNode" style="padding-left:'+level+'px;"><div class="treeContent">'+cellValue+'&nbsp;</div></div>';
            return cellValue;
        }
    };

    var FileImportGrid = declare('buildspace.apps.Tendering.FileImportGrid', dojox.grid.EnhancedGrid, {
        type: null,
        selectedItem: null,
        dialogWidget: null,
        escapeHTMLInData: false,
        gridData: null,
        style: "border-top:none;",
        constructor: function(args){
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
        }
    });

    var ProjectInfoForm = declare("buildspace.apps.Tendering.ProjectInfoForm", [_WidgetBase, _OnDijitClickMixin, _TemplatedMixin, _WidgetsInTemplateMixin], {
        templateString: projectInfoTemplate,
        baseClass: "buildspace-form",
        region: 'top',
        nls: nls,
        style: "padding:5px;overflow:auto;padding-bottom:10px;",
        projectTitle: null,
        country: null,
        state: null,
        workCategory: null,
        description:null,
        client: null,
        postCreate: function(){
            this.inherited(arguments);
        },
        startup: function(){
            this.inherited(arguments);
        }
    });

    var FileImportGridContainer = declare('buildspace.apps.Tendering.FileImportGridContainer', dijit.layout.BorderContainer, {
        stackContainerTitle: '',
        style: "padding:0px;width:100%;height:100%;",
        gutters: false,
        region: "center",
        gridOpts: {},
        projectInfo: null,
        postCreate: function(){
            this.inherited(arguments);
            lang.mixin(this.gridOpts, { region:"center" });
            var grid = this.grid = new FileImportGrid(this.gridOpts);

            this.addChild(new ProjectInfoForm({
                projectTitle: this.projectInfo.projectTitle,
                workCategory: this.projectInfo.work_category,
                description: this.projectInfo.description,
                country: this.projectInfo.country,
                state: this.projectInfo.state,
                client: this.projectInfo.client
            }));
            this.addChild(new dijit.layout.ContentPane({style: 'width:100%', content:grid, region:'center'}));

            var container = dijit.byId('Tendering-item_import_list_stackContainer');

            if(container){
                var node = document.createElement("div");
                var child = new dojox.layout.ContentPane( {title: buildspace.truncateString(this.stackContainerTitle, 200), id: this.pageId, executeScripts: true },node );
                container.addChild(child);
                child.set('content', this);
                container.selectChild(this.pageId);
            }
        }
    });

    var FileImportGridDialog = declare('buildspace.apps.Tendering.FileImportGridDialog', dijit.Dialog, {
        style:"padding:0px;margin:0px;",
        title: nls.importTenderProject,
        selectedItem: null,
        gridData: null,
        tempFileInfo: null,
        rootProject: null,
        importType: null,
        projectInfo: null,
        importUrl: null,
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
        },
        createContent: function() {
            var self = this;
            var borderContainer = new dijit.layout.BorderContainer({
                style:"padding:0px;width:650px;height:450px;",
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

            toolbar.addChild(new dijit.ToolbarSeparator());
            toolbar.addChild(
                new dijit.form.Button({
                    label: nls.import,
                    iconClass: "icon-16-container icon-16-import",
                    onClick: dojo.hitch(this, "import")
                })
            );

            var content = FileImportGridContainer({
                    stackContainerTitle: this.projectInfo.projectTitle,
                    projectInfo: this.projectInfo,
                    gridOpts: {
                        store: dojo.data.ItemFileWriteStore({
                            data: this.gridData
                        }),
                        dialogWidget: this,
                        structure: [
                            {name: 'No.', field: 'count', width:'30px', styles:'text-align:center;', formatter: Formatter.rowCountCellFormatter },
                            {name: nls.description, field: 'title', width:'auto', formatter: Formatter.treeCellFormatter}
                        ]
                    }
                });

            borderContainer.addChild(toolbar);
            borderContainer.addChild(content);

            return borderContainer;
        },
        import: function(){
            var self = this;

            this.hide();

            var pb = buildspace.dialog.indeterminateProgressBar({
                title:nls.savingData+'. '+nls.pleaseWait+'...'
            });

            pb.show().then(function(){
                dojo.xhrPost({
                    url: self.importUrl,
                    content: {
                        filename: self.tempFileInfo.filename,
                        extension: self.tempFileInfo.extension,
                        uploadPath: self.tempFileInfo.uploadPath,
                        uid: self.projectInfo.unique_id,
                        pid: (self.rootProject) ? parseInt(String(self.rootProject.id)) : -1
                    },
                    handleAs: 'json',
                    load: function(data) {
                        if(data.running){//always true if pass backend validation
                            switch(self.importType){
                                case "tender":
                                    self.getImportProjectLogStatus(pb);
                                    break;
                                default:
                                    self.getImportAddendumLogStatus(parseInt(data.version), pb);
                                    break;
                            }
                        }else{
                            pb.hide();
                            var content = '<div>'+data.errorMsg+'</div>';
                            buildspace.dialog.alert(nls.error,content,98,300);
                        }
                    },
                    error: function(error) {
                        pb.hide();
                    }
                });
            });
        },
        getImportProjectLogStatus: function(pb){
            var self = this;
            dojo.xhrPost({
                url: 'tendering/getImportTenderProjectProgress',
                content: {
                    uid: this.projectInfo.unique_id
                },
                handleAs: 'json',
                sync: false,
                load: function(data) {
                    if(!data.exists){
                        setTimeout(function(){self.getImportProjectLogStatus(pb);}, 2000);
                    }else{
                        pb.hide();
                        var grid = dijit.byId('Tendering-project_listing_grid');
                        if(grid){
                            grid.reload();
                        }
                    }
                },
                error: function(error) {
                    pb.hide();
                }
            });
        },
        getImportAddendumLogStatus: function(version, pb){
            var self = this;
            dojo.xhrPost({
                url: 'tendering/getImportAddendumProjectProgress',
                content: {
                    id: parseInt(String(this.rootProject.id)),
                    version: version
                },
                handleAs: 'json',
                sync: false,
                load: function(data) {
                    if(!data.exists){
                        setTimeout(function(){self.getImportAddendumLogStatus(version, pb);}, 2000);
                    }else{
                        pb.hide();
                        self.importAddendumBillProgressDialog();
                    }
                },
                error: function(error) {
                    pb.hide();
                }
            });
        },
        importAddendumBillProgressDialog: function(){
            if(this.rootProject && !isNaN(parseInt(String(this.rootProject.id))) && parseInt(String(this.rootProject.tender_type_id)) === buildspace.constants.TENDER_TYPE_PARTICIPATED){
                var pb = new ProgressBar({
                    value: 0,
                    title: "Importing Addendum Bills",
                    layoutAlign:"center"
                });

                var box = new dijit.Dialog({
                    content: pb,
                    style: "background:#fff;padding:5px;height:78px;width:280px;",
                    splitter: false
                });
                box.closeButtonNode.style.display = "none";
                box._onKey = function(evt){
                    var key = evt.keyCode;
                    if (key == keys.ESCAPE) {
                        dojo.stopEvent(evt);
                    }
                };
                box.onHide = function() {
                    box.destroyRecursive();
                };

                this.importAddendumBillProgress(box, pb);
            }
        },
        importAddendumBillProgress: function(box, pb){
            var project = this.rootProject,
                self = this;
            dojo.xhrPost({
                url: 'tendering/getImportAddendumBillProgress',
                content: {
                    id: parseInt(String(project.id))
                },
                handleAs: 'json',
                sync: false,
                load: function(data) {
                    var totalImportedBills = parseInt(data.total_imported_bills);
                    var totalBills = parseInt(data.total_bills);

                    if(data.exists && totalBills > 0 && totalImportedBills != totalBills){
                        if(!box.open){
                            box.show();
                        }

                        box.set({title:"Importing "+totalImportedBills+"/"+totalBills+" Addendum Bills"});

                        var i = totalImportedBills / totalBills * 100;
                        pb.set({value: i});

                        setTimeout(function(){self.importAddendumBillProgress(box, pb);}, 5000);
                    }else{
                        if(box.open){
                            box.hide();
                        }
                        
                        self.reloadProjectBreakdown();
                        self.reloadRevision();
                    }
                },
                error: function(error) {
                    if(box.open){
                        box.hide();
                    }
                }
            });
        },
        reloadProjectBreakdown: function(){
            var projectBreakdown = dijit.byId('main-project_breakdown');
            if(projectBreakdown){
                projectBreakdown.grid.reload();
            }
            var projectTenderAlternativeGrid = dijit.byId(String(this.rootProject.id)+"-tenderAlternative-tenderAlternativeListGrid");
            if(projectTenderAlternativeGrid){
                projectTenderAlternativeGrid.reload();
            }
        },
        reloadRevision: function(){
            var revisionContainer = dijit.byId(parseInt(String(this.rootProject.id))+'-ProjectRevision');
            if(revisionContainer){
                dojo.empty(revisionContainer.projectRevisionSettingsForm.tableContainer);
                revisionContainer.projectRevisionSettingsForm.masterGenerateProjectRevisionTableRow();
            }
        },
        reloadProjectListing: function( item ){
            var grid = dijit.byId('Tendering-project_listing_grid');

            if(grid){
                var defaultRowIndex = 0,
                    store = grid.store,
                    saved = store.newItem(item),
                    rowsToMove = [],
                    itemIdx = grid.getItemIndex(saved);

                rowsToMove.push(itemIdx);

                if(rowsToMove.length > 0) {
                    grid.rearranger.moveRows(rowsToMove, defaultRowIndex);
                    grid.selection.setSelected(defaultRowIndex, true);
                    grid.render();
                }
            }
        }
    });


    var Form = declare("buildspace.apps.Tendering.FileImportForm", [_WidgetBase, _OnDijitClickMixin, _TemplatedMixin, _WidgetsInTemplateMixin], {
        templateString: template,
        baseClass: "buildspace-form",
        region: 'center',
        dialogObj: null,
        importType: null,
        rootProject: null,
        nls: nls,
        uploadUrl: "tendering/uploadTenderProject",
        importUrl: "tendering/importTenderProject",
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
        },
        uploadComplete:function(data){
            this.dialogObj.hide();

            if(this.pb)
                this.pb.hide();

            if(data.success){
                var importGridDialog = this.importGridDialog = new FileImportGridDialog({
                    gridData: data.projectBreakdown,
                    projectInfo: data.projectInfo,
                    rootProject: this.rootProject,
                    tempFileInfo: data.tempFileInfo,
                    importType: this.importType,
                    title: this.title,
                    importUrl: this.importUrl
                });

                importGridDialog.show();
            }else{
                var content = '<div>'+data.errorMsg+'</div>';
                buildspace.dialog.alert(nls.error,content,80,320);
            }
        },
        close: function(){

        },
        onCancel: function(){

        }
    });

    return declare('buildspace.apps.Tendering.FileImportDialog', dijit.Dialog, {
        style:"padding:0px;margin:0px;",
        title: nls.importTenderProject,
        rootProject: null,
        uploadUrl: null,
        importUrl: null,
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
                style:"padding:0px;width:400px;height:120px;",
                gutters: false
            });

            var form = new Form({
                dialogObj: this,
                title: this.title,
                importType: this.importType,
                rootProject: this.rootProject,
                uploadUrl: this.uploadUrl,
                importUrl: this.importUrl
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
            borderContainer.addChild(form);

            return borderContainer;
        }
    });
});