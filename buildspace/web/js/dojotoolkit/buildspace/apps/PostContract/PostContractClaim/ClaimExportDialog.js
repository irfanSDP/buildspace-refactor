define('buildspace/apps/PostContract/PostContractClaim/ClaimExportDialog',[
    'dojo/_base/declare',
    "dojo/keys",
    "dojo/dom-style",
    "dijit/_WidgetBase",
    "dijit/_TemplatedMixin",
    "dijit/_WidgetsInTemplateMixin",
    "dijit/form/Form",
    'dojo/i18n!buildspace/nls/PostContract'], function(declare, keys, domStyle, _WidgetBase, _TemplatedMixin, _WidgetsInTemplateMixin, Form, nls){

    var ExportClaimsForm = declare('buildspace.apps.PostContract.ClaimCertificate.ExportClaimsForm', [Form,
        _WidgetBase,
        _TemplatedMixin,
        _WidgetsInTemplateMixin,
        dojox.form.manager._Mixin,
        dojox.form.manager._NodeMixin,
        dojox.form.manager._ValueMixin,
        dojox.form.manager._DisplayMixin], {
        templateString: '<form data-dojo-attach-point="containerNode">' +
        '<table class="table-form">' +
        '<tr>' +
        '<td class="label" style="width:80px;"><label style="display: inline;"></span>'+nls.exportAs+' :</label></td>' +
        '<td>' +
        '<input type="text" name="filename" style="padding:2px;width:220px;" data-dojo-type="dijit/form/ValidationTextBox" data-dojo-props="trim:true, propercase:true, maxlength:45, required: true"> .ebqclaim' +
        '</td>' +
        '</tr>' +
        '</table>' +
        '</form>',
        project: null,
        claimCertificateObj: null,
        region: 'center',
        dialogWidget: null,
        style: "outline:none;padding:0px;margin:0px;border:none;",
        baseClass: "buildspace-form",
        startup: function(){
            this.inherited(arguments);
            this.setFormValues({filename: "Claims-"+this.project.title+"_v"+this.claimCertificateObj.version});
        },
        submit: function(){
            var values = dojo.formToObject(this.id);
            if(this.validate()){
                var filename = values.filename.replace(/ /g, '_');
                window.open('claimTransfer/exportClaims/pid/'+this.project.id+'/_csrf_token/'+this.project._csrf_token+'/revision_id/'+this.claimCertificateObj.claim_revision_id+'/filename/'+filename, '_self');

                if(this.dialogWidget){
                    this.dialogWidget.hide();
                }
            }
        }
    });

    return declare('buildspace.apps.PostContract.ClaimCertificate.ClaimsExportDialog', dijit.Dialog, {
        style:"padding:0px;margin:0px;",
        title: nls.exportClaims,
        project: null,
        claimCertificateObj: null,
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
        createContent: function(){
            var borderContainer = new dijit.layout.BorderContainer({
                    style:"padding:0px;width:400px;height:80px;",
                    gutters: false
                }),
                toolbar = new dijit.Toolbar({
                    region: "top",
                    style: "outline:none!important;padding:2px;overflow:hidden;"
                }),
                form = ExportClaimsForm({
                    project: this.project,
                    claimCertificateObj: this.claimCertificateObj,
                    dialogWidget: this
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
            toolbar.addChild(
                new dijit.form.Button({
                    label: nls.export,
                    iconClass: "icon-16-container icon-16-export",
                    style:"outline:none!important;",
                    onClick: dojo.hitch(form, 'submit')
                })
            );

            borderContainer.addChild(toolbar);
            borderContainer.addChild(form);

            return borderContainer;
        }
    });
});
