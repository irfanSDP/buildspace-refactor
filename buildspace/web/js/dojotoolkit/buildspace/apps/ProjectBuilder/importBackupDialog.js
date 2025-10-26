define('buildspace/apps/ProjectBuilder/importBackupDialog',[
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
    "dojo/text!./templates/importBackupForm.html",
    "dojo/text!./templates/billInfoForm.html",
    'dojo/i18n!buildspace/nls/BackupImport',
    'dojox/form/Uploader'
], function(declare, lang, connect, when, html, dom, keys, domStyle, GridFormatter, IndirectSelection, _WidgetBase, _OnDijitClickMixin, _TemplatedMixin, _WidgetsInTemplateMixin, template, billInfoTemplate, nls, FileUploader){

    var Formatter = {
        rowCountCellFormatter: function(cellValue, rowIdx){
            return cellValue > 0 ? cellValue : '';
        }
    };
    
    var FileImportGrid = declare('buildspace.apps.ProjectBuilder.FileImportGrid', dojox.grid.EnhancedGrid, {
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

    var billInfoForm = declare("buildspace.apps.ProjectBuilder.billInfoForm", [_WidgetBase, _OnDijitClickMixin, _TemplatedMixin, _WidgetsInTemplateMixin], {
        templateString: billInfoTemplate,
        baseClass: "buildspace-form",
        region: 'top',
        nls: nls,
        style: "padding:5px;overflow:auto;padding-bottom:10px;",
        title: null,
        postCreate: function(){
            this.inherited(arguments);
        },
        startup: function(){
            this.inherited(arguments);
        }
    });

    var FileImportGridContainer = declare('buildspace.apps.ProjectBuilder.FileImportGridContainer', dijit.layout.BorderContainer, {
        stackContainerTitle: '',
        style: "padding:0px;width:100%;height:100%;",
        gutters: false,
        region: "center",
        gridOpts: {},
        billInfo: null,
        postCreate: function(){
            var self = this;
            self.inherited(arguments);
            lang.mixin(self.gridOpts, { region:"center" });
            var grid = this.grid = new FileImportGrid(self.gridOpts);

            self.addChild(new billInfoForm({
                title: self.billInfo.title[0]
            }));
            self.addChild(new dijit.layout.ContentPane({style: 'width:100%', content:grid, region:'center'}));
        }
    });
    
    var FileImportGridDialog = declare('buildspace.apps.ProjectBuilder.FileImportGridDialog', dijit.Dialog, {
        style:"padding:0px;margin:0px;",
        gridData: null,
        projectId: null,
        billInfo: null,
        parentId: null,
        billId: null,
        tempFileInfo: null,
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
            var self = this;
        },
        createContent: function()
        {
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
                    onClick: function(){
                        self.import();
                    }
                })
            );

            var store = dojo.data.ItemFileWriteStore({
                data: self.gridData
            });

            var content = FileImportGridContainer({
                stackContainerTitle: self.billInfo.title,
                billInfo: self.billInfo,
                gridOpts: {
                    store: store,
                    dialogWidget: self,
                    structure: [
                        {name: nls.number, field: 'id', width:'30px', styles:'text-align:center;', formatter: Formatter.rowCountCellFormatter },
                        {name: nls.description, field: 'description', width:'auto' }
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
                        id: self.billId,
                        parent_id: self.parentId,
                        filename: self.tempFileInfo.filename,
                        extension: self.tempFileInfo.extension,
                        uploadPath: self.tempFileInfo.uploadPath
                    },
                    handleAs: 'json',
                    load: function(data) {
                        pb.hide();

                        if(data.success) {
                            self.reloadProjectBreakdown();
                        } else {
                            var content = '<div>'+data.errorMsg+'</div>';
                            buildspace.dialog.alert(nls.error,content,80,280);
                        }
                    },
                    error: function(error) {
                        pb.hide();
                    }
                });
            });
        },
        reloadProjectBreakdown: function(){
            var projectBreakdown = dijit.byId('main-project_breakdown');
            projectBreakdown.grid.reload();
        }
    });
    
    var Form = declare("buildspace.apps.ProjectBuilder.FileImportForm", [_WidgetBase, _OnDijitClickMixin, _TemplatedMixin, _WidgetsInTemplateMixin], {
        templateString: template,
        baseClass: "buildspace-form",
        region: 'center',
        dialogObj: null,
        billId: -1,
        parentId: -1,
        projectId: -1,
        projectBreakdownGrid: null,
        nls: nls,
        uploadUrl: "projectBackup/uploadBill",
        importUrl: "projectBackup/importBill",
        style: "padding:5px;overflow:auto;",
        postCreate: function(){
            this.inherited(arguments);
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
            var self = this;
            self.pb.hide();

            if(data.success){
                var importGridDialog = self.importGridDialog = new FileImportGridDialog({
                    gridData: data.elements,
                    projectId: self.projectId,
                    parentId: self.parentId,
                    billId: self.billId,
                    billInfo: data.billInfo,
                    tempFileInfo: data.tempFileInfo,
                    importUrl: self.importUrl,
                    title: self.title,
                });

                importGridDialog.show();
            }else{
                var content = '<div>'+data.errorMsg+'</div>';
                buildspace.dialog.alert(nls.error,content,80,280);
            }
        },
        close: function(){

        },
        onCancel: function(){

        }
    });

    return declare('buildspace.apps.ProjectBuilder.FileImportDialog', dijit.Dialog, {
        style:"padding:0px;margin:0px;",
        title: nls.importBackup,
        rootProject: null,
        projectBreakdownGrid: null,
        item: null,
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
            var self = this;

            var borderContainer = new dijit.layout.BorderContainer({
                style:"padding:0px;width:400px;height:80px;",
                gutters: false
            });

            var form = new Form({
                dialogObj: self,
                title: self.title,
                projectBreakdownGrid: self.projectBreakdownGrid,
                projectId: self.rootProject.id,
                parentId: self.item.id
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