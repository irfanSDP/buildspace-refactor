define('buildspace/apps/Tendering/Builder',[
    '../../../dojo/_base/declare',
    "dijit/form/Form",
    "dijit/_WidgetBase",
    "dijit/_OnDijitClickMixin",
    "dijit/_TemplatedMixin",
    "dijit/_WidgetsInTemplateMixin",
    'dojo/_base/lang',
    "dojo/_base/array",
    'dojo/_base/event',
    'dojo/keys',
    "dojo/dom-style",
    'dojo/on',
    'dijit/form/DropDownButton',
    "dijit/DropDownMenu",
    "dijit/MenuItem",
    './WorkArea',
    './tenderImportDialog',
    './checkPublishRequirementDialog',
    'dojo/i18n!buildspace/nls/Tendering'
], function(declare, Form, _WidgetBase, _OnDijitClickMixin, _TemplatedMixin, _WidgetsInTemplateMixin, lang, array, evt, keys, domStyle, on, DropDownButton, DropDownMenu, MenuItem, WorkArea, TenderImportDialog, CheckPublishRequirementDialog, nls){

    var ExportProjectForm = declare('buildspace.apps.ProjectBuilder.ExportTenderingProjectForm', [Form,
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
            '<td class="label" style="width:80px;"><label style="display: inline;"></span>'+nls.downloadAs+' :</label></td>' +
            '<td>' +
            '<input type="text" name="filename" style="padding:2px;width:220px;" data-dojo-type="dijit/form/ValidationTextBox" data-dojo-props="trim:true, propercase:true, required: true">' +
            '<input type="hidden" name="id" value="">' +
            '<input type="hidden" name="_csrf_token" value="">' +
            '</td>' +
            '</tr>' +
            '</table>' +
            '</form>',
        project: null,
        region: 'center',
        exportRate: false,
        dialogWidget: null,
        exportUrl: null,
        style: "outline:none;padding:0;margin:0;border:none;",
        baseClass: "buildspace-form",
        startup: function(){
            this.inherited(arguments);
            var filename = this.project.title[0].toString();

            if (filename.length > 60) {
                filename = filename.substring(0, 60);
            }

            filename = this.exportRate ? 'Rates-'+filename : filename;

            this.setFormValues({
                filename: filename,
                id: this.project.id,
                _csrf_token: this.project._csrf_token
            });
        },
        submit: function(){
            if(this.validate() && this.exportUrl){
                var values = dojo.formToObject(this.id);
                var filename = values.filename.replace(/ /g, '_');

                buildspace.windowOpen('POST', this.exportUrl, {
                    filename: filename,
                    id: values.id,
                    _csrf_token: values._csrf_token
                });

                if(this.dialogWidget){
                    this.dialogWidget.hide();
                }
            }
        }
    });

    var ExportProjectDialog = declare('buildspace.apps.Tendering.ExportTenderingProjectDialog', dijit.Dialog, {
        style:"padding:0px;margin:0;",
        title: nls.exportProject,
        project: null,
        exportRate: false,
        exportUrl: null,
        buildRendering: function(){
            var content = this.createContent();
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
        createContent: function(){
            var borderContainer = new dijit.layout.BorderContainer({
                    style:"padding:0;width:400px;height:80px;",
                    gutters: false
                }),
                toolbar = new dijit.Toolbar({
                    region: "top",
                    style: "outline:none!important;padding:2px;overflow:hidden;"
                }),
                form = ExportProjectForm({
                    project: this.project,
                    exportUrl: this.exportUrl,
                    exportRate: this.exportRate,
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
                    label: nls.download,
                    iconClass: "icon-16-container icon-16-import",
                    style:"outline:none!important;",
                    onClick: dojo.hitch(form, 'submit')
                })
            );

            borderContainer.addChild(toolbar);
            borderContainer.addChild(form);

            return borderContainer;
        }
    });

    return declare('buildspace.apps.Tendering.Builder', dijit.layout.BorderContainer, {
        style: "padding:0px;width:100%;height:100%;",
        gutters: false,
        project: null,
        workarea: null,
        postCreate: function(){
            this.inherited(arguments);

            var self = this,
                analysisStatusXhr = dojo.xhrGet({
                    url: "projectAnalyzer/getAnalysisStatus/",
                    content: { pid: this.project.id },
                    handleAs: "json"
                });

            analysisStatusXhr.then(function(analysisStatus){
                var toolbar = new dijit.Toolbar({
                        id: self.project.id+'TenderingBuilder-toolbar',
                        region: "top",
                        style:"padding:2px;width:100%;"
                    }),
                    workarea = self.workarea = WorkArea({
                        rootProject: self.project
                    });

                switch(parseInt(String(self.project.tender_type_id))){
                    case buildspace.constants.TENDER_TYPE_PARTICIPATED:

                        if(parseInt(String(self.project.status_id)) == buildspace.constants.STATUS_IMPORT) {
                            toolbar.addChild(
                                new dijit.form.Button({
                                    label: nls.importAddendum,
                                    id: self.project.id+'ImportAddendumRow-button',
                                    iconClass: "icon-16-container icon-16-container icon-16-import",
                                    disabled: parseInt(String(self.project.status_id)) != buildspace.constants.STATUS_IMPORT,
                                    onClick: function(e){
                                        var tenderImportDialog = new TenderImportDialog({
                                            uploadUrl: "tendering/uploadAddendum",
                                            importUrl: "tendering/importAddendum",
                                            importType: "addendum",
                                            rootProject: self.project,
                                            title: nls.importAddendum
                                        });

                                        self.workarea.removeBillTab();

                                        tenderImportDialog.show();
                                    }

                                })
                            );
                        } else {
                            toolbar.addChild(
                                new dijit.form.Button({
                                    label: nls.importUpdate,
                                    id: self.project.id+'ImportUpdateRow-button',
                                    iconClass: "icon-16-container icon-16-container icon-16-import",
                                    disabled: true,
                                    onClick: function(e){
                                        console.log('update');
                                    }

                                })
                            );
                        }

                        toolbar.addChild(new dijit.ToolbarSeparator());
                        toolbar.addChild(
                            new dijit.form.Button({
                                label: nls.exportRates,
                                id: 'exportRates-button',
                                iconClass: "icon-16-container icon-16-export",
                                onClick: function(){
                                    var dialog = ExportProjectDialog({
                                        title: nls.exportRates,
                                        project: self.project,
                                        exportRate: true,
                                        exportUrl: 'tenderingExportFile/exportRatesByProject'
                                    });

                                    dialog.show();
                                }
                            })
                        );

                        toolbar.addChild(new dijit.ToolbarSeparator());

                        break;
                    case buildspace.constants.TENDER_TYPE_TENDERED:

                        toolbar.addChild(
                            new dijit.form.Button({
                                label: nls.exportProject,
                                iconClass: "icon-16-container icon-16-export",
                                onClick: function(){
                                    var dialog = ExportProjectDialog({
                                        project: self.project,
                                        exportUrl: 'tenderingExportFile/exportByProject'
                                    });

                                    dialog.show();
                                }
                            })
                        );

                        toolbar.addChild(new dijit.ToolbarSeparator());

                        break;
                    default:
                        break;
                }

                var sortOptions = ['resourceAnalysis', 'scheduleOfRateAnalysis'],
                    menu = new DropDownMenu({ style: "display: none;"});

                dojo.forEach(sortOptions, function(opt){
                    var disabled = true;
                    if(opt == 'resourceAnalysis' && analysisStatus.enable_resource_analysis == true){
                        disabled = false;
                    }else if(opt == 'scheduleOfRateAnalysis' && analysisStatus.enable_schedule_of_rate_analysis == true){
                        disabled = false;
                    }

                    var menuItem = new MenuItem({
                        id: opt+"-"+self.project.id+"-menuItem",
                        label: nls[opt],
                        disabled: disabled,
                        onClick: function(){
                            buildspace.app.launch({
                                __children: [],
                                icon: "project_analyzer",
                                id: self.project.id+'-project_analyzer',
                                is_app: true,
                                level: 0,
                                sysname: "ProjectAnalyzer",
                                title: nls.projectAnalyzer
                            },{
                                type: buildspace.constants.STATUS_TENDERING,
                                opt: opt,
                                project: self.project
                            });
                        }
                    });
                    menu.addChild(menuItem);
                });

                toolbar.addChild(
                    new DropDownButton({
                        label: nls.projectAnalyzer,
                        iconClass: "icon-16-container icon-16-project_analyzer",
                        style:"outline:none!important;",
                        dropDown: menu
                    })
                );

                toolbar.addChild(new dijit.ToolbarSeparator());
                toolbar.addChild(
                    new dijit.form.Button({
                        id: self.project.id+'ViewSubPackageRow-button',
                        label: nls.subPackages,
                        iconClass: "icon-16-container icon-16-file",
                        onClick: function(e){
                            buildspace.app.launch({
                                __children: [],
                                icon: "project_sub_package",
                                id: self.project.id+'-project_sub_package',
                                is_app: true,
                                level: 0,
                                sysname: "SubPackage",
                                title: nls.subPackages
                            },{
                                type: buildspace.constants.STATUS_TENDERING,
                                project: self.project
                            });
                        }
                    })
                );

                toolbar.addChild(new dijit.ToolbarSeparator());
                toolbar.addChild(
                    new dijit.form.Button({
                        id: "soq-"+self.project.id+"-mainButton",
                        label: nls.scheduleOfQuantities,
                        iconClass: "icon-16-container icon-16-pyramid",
                        disabled: analysisStatus.enable_schedule_of_qty != true,
                        onClick: function(e) {
                            buildspace.app.launch({
                                __children: [],
                                icon: "project_schedule_of_quantity",
                                id: self.project.id+'-schedule_of_quantity',
                                is_app: true,
                                level: 0,
                                sysname: "ScheduleOfQuantity",
                                title: nls.scheduleOfQuantities
                            },{
                                type: buildspace.constants.STATUS_TENDERING,
                                project: self.project,
                                canEdit: false
                            });
                        }
                    })
                );

                var showPostContractButton = (!self.project.tendering_module_locked[0] && self.project.can_publish_to_post_contract[0]) || ((self.project.status_id[0] == buildspace.constants.STATUS_IMPORT_SUB_PACKAGE || self.project.status_id[0] == buildspace.constants.STATUS_IMPORT));
                if(self.project.is_admin[0] && showPostContractButton){
                    toolbar.addChild(new dijit.ToolbarSeparator({
                        id: self.project.id+'PublishToPostContractBtn-toolbar_separator'
                    }));
                    toolbar.addChild(
                        new dijit.form.Button({
                            id: self.project.id+'PublishToPostContractRow-button',
                            label: nls.pushToPostContract,
                            iconClass: "icon-16-container icon-16-indent",
                            onClick: function(e){
                                self.workarea.removeBillTab();

                                self.workarea.removeTabByType(9999999); //remove subpackage tab

                                self.publishToPostContract();
                            }
                        })
                    );
                }

                workarea.startup();

                self.addChild(toolbar);
                self.addChild(workarea);
            });
        },
        publishToPostContract: function(){
            var self = this,
                project = this.project,
                pb = buildspace.dialog.indeterminateProgressBar({
                    title: nls.processing+'. '+nls.pleaseWait+'...'
                });

            pb.show().then(function(){
                dojo.xhrGet({
                    url: "tendering/checkPublishRequirement",
                    content: {
                        id: project.id
                    },
                    handleAs: 'json',
                    load: function(resp) {

                        pb.hide();
                        var dialog;
                        if(resp.can_publish){
                            dialog = new CheckPublishRequirementDialog({
                                rootProject: project,
                                builderObj: self,
                                data: resp
                            });

                            dialog.show();
                        }else{
                            var content = '<div style="background:#cc1313;padding:5px;min-width:64px;color:#fff;">'+nls.cannotPublishDifferentAwardedContractorTxt+'</div>';
                            content += '<div class="buildspace-form"><fieldset>'+
                            '<legend>'+nls.companyInformation+'</legend>'+
                            '<div style="height:2px;">&nbsp;</div>'+
                            '<table class="table-form">'+
                            '<tr>'+
                            '<td class="label" style="width:10%;">'+
                            '<label style="display: inline;">'+nls.name+' :</label>'+
                            '</td>'+
                            '<td style="width:90%;">'+resp.company_name+'</td>'+
                            '</tr>'+
                            '<tr>'+
                            '<td class="label" style="width:10%;">'+
                            '<label style="display: inline;">'+nls.address+' :</label>'+
                            '</td>'+
                            '<td style="width:90%;"><label style="display: inline;white-space: pre-wrap;">'+resp.company_address+'</label></td>'+
                            '</tr>'+
                            '</table>'+
                            '</fieldset></div>';

                            dialog = buildspace.dialog.alert(nls.error, content, 280, 640);
                        }
                    },
                    error: function(error) {
                        pb.hide();
                    }
                });
            });
        },
        disableButtonsAfterPublish: function () {

            var publishButton = dijit.byId(this.project.id+'PublishToPostContractRow-button');

            var importAddendumBtn = dijit.byId(this.project.id+'ImportAddendumRow-button');

            if(publishButton)
                publishButton._setDisabledAttr(true);

            if(importAddendumBtn)
                importAddendumBtn._setDisabledAttr(true);

            this.reloadRevision();
        },
        reloadRevision: function(){
            var revisionContainer = dijit.byId(this.project.id[0]+'-ProjectRevision');
            dojo.empty(revisionContainer.projectRevisionSettingsForm.tableContainer);
            revisionContainer.projectRevisionSettingsForm.masterGenerateProjectRevisionTableRow();
        },
        disableToolbarButtons: function(isDisable){
            //Button Disabled here
        }
    });
});
