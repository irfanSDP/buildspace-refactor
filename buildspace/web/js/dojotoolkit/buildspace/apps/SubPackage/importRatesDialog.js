define('buildspace/apps/SubPackage/importRatesDialog',[
    'dojo/_base/declare',
    'dojo/_base/lang',
    "dojo/_base/connect",
    "dojo/when",
    "dojo/html",
    "dojo/dom",
    'dojo/keys',
    "dojo/dom-style",
    "dijit/_WidgetBase",
    "dijit/_OnDijitClickMixin",
    "dijit/_TemplatedMixin",
    "dijit/_WidgetsInTemplateMixin",
    "dojo/text!./templates/importRatesForm.html",
    'dojo/i18n!buildspace/nls/ImportRatesDialog',
    'dojox/form/Uploader'
], function(declare, lang, connect, when, html, dom, keys, domStyle, _WidgetBase, _OnDijitClickMixin, _TemplatedMixin, _WidgetsInTemplateMixin, template,nls, FileUploader){

    var Form = declare("buildspace.apps.SubPackage.FileImportForm", [_WidgetBase, _OnDijitClickMixin, _TemplatedMixin, _WidgetsInTemplateMixin], {
        templateString: template,
        baseClass: "buildspace-form",
        region: 'center',
        dialogObj: null,
        projectId: null,
        contractorGrid: null,
        subPackageGrid: null,
        companyId: null,
        subPackageId: -1,
        nls: nls,
        uploadUrl: null,
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

            if(data.success)
            {
                this.refreshContractorGrid();
                
                this.refreshSubPackageGrid();
            }
            else
            {
                var content = '<div>'+data.errorMsg+'</div>';
                buildspace.dialog.alert(nls.error,content,80,300);
            }
        },
        refreshContractorGrid: function()
        {
        	this.contractorGrid.store.save();
            this.contractorGrid.store.close();
            this.contractorGrid._refresh();
        },
        refreshSubPackageGrid: function()
        {
        	this.subPackageGrid.store.save();
            this.subPackageGrid.store.close();
            this.subPackageGrid._refresh();
        }
    });

    return declare('buildspace.apps.SubPackage.FileImportDialog', dijit.Dialog, {
        style:"padding:0px;margin:0px;",
        title: nls.importContractorRates,
        project: null,
        subPackage: null,
        contractorGrid: null,
        subPackageGrid: null,
        company: null,
        uploadUrl: null,
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
                projectId: self.project.id,
                companyId: self.company.id,
                contractorGrid: self.contractorGrid,
                subPackageGrid: self.subPackageGrid,
                subPackageId: self.subPackage.id,
                uploadUrl: "subPackage/importContractorRates/pid/"+self.project.id+"/cid/"+self.company.id+"/sid/"+self.subPackage.id
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