define('buildspace/apps/ProjectManagement/ImportProjectScheduleDialog',[
    'dojo/_base/declare',
    'dojo/_base/lang',
    "dojo/_base/connect",
    "dojo/when",
    "dojo/html",
    "dojo/dom",
    "dojo/on",
    'dojo/keys',
    "dojo/dom-style",
    'dijit/form/DropDownButton',
    "dijit/DropDownMenu",
    "dijit/MenuItem",
    "dijit/_WidgetBase",
    "dijit/_OnDijitClickMixin",
    "dijit/_TemplatedMixin",
    "dijit/_WidgetsInTemplateMixin",
    "dojo/text!./templates/projectScheduleUploadForm.html",
    "dojo/text!./templates/projectScheduleImportInfo.html",
    'dojo/i18n!buildspace/nls/ProjectManagement',
    'dojox/form/Uploader'
], function(declare, lang, connect, when, html, dom, on, keys, domStyle, DropDownButton, DropDownMenu, MenuItem, _WidgetBase, _OnDijitClickMixin, _TemplatedMixin, _WidgetsInTemplateMixin, uploadTemplate, projectScheduleImportInfoTemplate, nls, FileUploader){

    var ProjectScheduleImportInfo = declare("buildspace.apps.ProjectManagement.ProjectScheduleImportInfo", [_WidgetBase, _OnDijitClickMixin, _TemplatedMixin, _WidgetsInTemplateMixin], {
        templateString: projectScheduleImportInfoTemplate,
        baseClass: "buildspace-form",
        region: 'center',
        style: "border:none;padding:5px;overflow:auto;",
        containerDialog: null,
        project: null,
        projectScheduleInfo: null,
        subPackage: null,
        importUrl: null,
        filename: null,
        filePath: null,
        subPackageTitle: "",
        nls: nls,
        buildRendering: function(){
            this.projectScheduleInfo.title      = this.projectScheduleInfo.title[0];
            this.projectScheduleInfo.start_date = this.projectScheduleInfo.start_date[0];
            this.projectScheduleInfo.timezone   = this.projectScheduleInfo.timezone[0];

            if(this.subPackage){
                this.subPackageTitle = this.subPackage.title[0];
            }

            this.inherited(arguments);
        },
        startup: function(){
            this.inherited(arguments);

            if(this.subPackage){
                domStyle.set(this.scheduleFormSubPackageName, 'display', '');
            }else{
                domStyle.set(this.scheduleFormSubPackageName, 'display', 'none');
            }

            this.importProjectScheduleForm.setFormValues({
                'exclude_saturdays': this.projectScheduleInfo.exclude_saturdays[0],
                'exclude_sundays': this.projectScheduleInfo.exclude_sundays[0],
                '_csrf_token': this.project._csrf_token[0]
            });
        },
        import: function(useProjectStartDate){
            var formValues = dojo.formToObject(this.importProjectScheduleForm.id);

            var self = this;

            if(this.containerDialog){
                this.containerDialog.hide();
            }

            var pb = buildspace.dialog.indeterminateProgressBar({
                title:nls.savingData+'. '+nls.pleaseWait+'...'
            });

            pb.show().then(function(){
                lang.mixin(formValues, {
                    pid: self.project.id,
                    filename: self.filename,
                    path: self.filePath,
                    psd: useProjectStartDate
                });

                if(self.subPackage){
                    lang.mixin(formValues, {
                        spid: self.subPackage.id
                    });
                }

                dojo.xhrPost({
                    url: self.importUrl,
                    content: formValues,
                    handleAs: 'json',
                    load: function(data) {
                        pb.hide();

                        if(data.success){
                            self.reloadProjectScheduleList();
                        }else{
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
        reloadProjectScheduleList: function(){
            var projectScheduleListGrid = dijit.byId('projectManagement-projectScheduleListGrid');
            if(projectScheduleListGrid){
                projectScheduleListGrid.reload();
            }
        },
        close: function(){

        },
        onCancel: function(){

        }
    });

    var FileImportDialog = declare('buildspace.apps.ProjectManagement.FileImportDialog', dijit.Dialog, {
        title: nls.importProjectSchedule,
        style:"padding:0px;margin:0px;",
        project: null,
        subPackage: null,
        importUrl: null,
        projectScheduleInfo: null,
        filename: null,
        filePath: null,
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
        createContent: function() {
            var borderContainer = new dijit.layout.BorderContainer({
                style:"padding:0px;width:740px;height:325px;",
                gutters: false
            });

            var template = ProjectScheduleImportInfo({
                project: this.project,
                subPackage: this.subPackage,
                projectScheduleInfo: this.projectScheduleInfo,
                importUrl: this.importUrl,
                filename: this.filename,
                filePath: this.filePath,
                containerDialog: this
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

            var importDropDownMenu = new DropDownMenu({ style: "display: none;"});

            importDropDownMenu.addChild(new MenuItem({
                label: nls.useProjectStartDate,
                onClick: dojo.hitch(template, 'import', true)
            }));

            importDropDownMenu.addChild(new MenuItem({
                label: nls.useProjectScheduleStartDate,
                onClick: dojo.hitch(template, 'import', false)
            }));

            toolbar.addChild(new DropDownButton({
                label: nls.import,
                iconClass: "icon-16-container icon-16-import",
                dropDown: importDropDownMenu
            }));

            borderContainer.addChild(template);
            borderContainer.addChild(toolbar);

            return borderContainer;
        }
    });

    var ProjectScheduleUploadForm = declare("buildspace.apps.ProjectManagement.ProjectScheduleUploadForm", [_WidgetBase, _OnDijitClickMixin, _TemplatedMixin, _WidgetsInTemplateMixin], {
        templateString: uploadTemplate,
        title: nls.importProjectSchedule,
        baseClass: "buildspace-form",
        region: 'center',
        dialogObj: null,
        project: null,
        subPackage: null,
        nls: nls,
        uploadUrl: "projectManagement/projectScheduleUpload",
        importUrl: "projectManagement/projectScheduleImport",
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
        uploadBegin: function(data){
            this.pb = buildspace.dialog.indeterminateProgressBar({
                title:nls.uploadingData+'. '+nls.pleaseWait+'...'
            });

            this.pb.show();
        },
        uploadComplete:function(data){
            if(this.pb){
                this.dialogObj.hide();

                this.pb.hide();

                if(data.success){
                    var dialog = self.importGridDialog = new FileImportDialog({
                        project: this.project,
                        subPackage: this.subPackage,
                        importUrl: this.importUrl,
                        projectScheduleInfo: data.projectScheduleInfo,
                        filename: data.filename,
                        filePath: data.filePath
                    });

                    dialog.show();
                }else{
                    var content = '<div>'+data.errorMsg+'</div>';
                    buildspace.dialog.alert(nls.error,content,80,280);
                }
            }
        },
        close: function(){

        },
        onCancel: function(){

        }
    });

    return declare('buildspace.apps.ProjectManagement.ImportProjectScheduleDialog', dijit.Dialog, {
        style:"padding:0px;margin:0px;",
        title: nls.importProjectSchedule,
        project: null,
        subPackage: null,
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
                style:"padding:0px;width:400px;height:80px;",
                gutters: false
            });

            var form = new ProjectScheduleUploadForm({
                dialogObj: this,
                project: this.project,
                subPackage: this.subPackage
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