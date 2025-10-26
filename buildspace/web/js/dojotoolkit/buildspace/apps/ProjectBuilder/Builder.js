define('buildspace/apps/ProjectBuilder/Builder',[
    'dojo/_base/declare',
    "dojo/dom-style",
    'dojo/keys',
    'dojo/on',
    'dojo/_base/connect',
    'dijit/form/DropDownButton',
    "dijit/DropDownMenu",
    "dijit/MenuItem",
    "dijit/layout/ContentPane",
    './WorkArea',
    'buildspace/apps/AssignUser/assignGroupProjectGrid',
    'buildspace/apps/PageGenerator/GeneratorDialog',
    'buildspace/apps/TenderAlternative/TenderAlternativeListDialog',
    './EmptyGrandTotalQtyDialog',
    './HeadWithoutItemsDialog',
    'dojo/i18n!buildspace/nls/ProjectBuilder'
], function(declare, domStyle, keys, on, connect, DropDownButton, DropDownMenu, MenuItem, ContentPane, WorkArea, AssignGroupProjectGrid, GeneratorDialog, TenderAlternativeListDialog, EmptyGrandTotalQtyDialog, HeadWithoutItemsDialog, nls){

    var PublishToTenderDialog = declare('buildspace.apps.ProjectBuilder.PublishToTenderDialog', dijit.Dialog, {
        style:"padding:0px;margin:0px;",
        title: nls.publishToTender,
        project: null,
        mainDialog: null,
        data: null,
        assignGroupProjectGridSavedState: false,
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
            if (key == dojo.keys.ESCAPE) {
                dojo.stopEvent(e);
            }
        },
        onHide: function() {
            dojo.forEach(this._connects, connect.disconnect);
            this.destroyRecursive();
        },
        createForm: function(){
            var self = this;

            var borderContainer = new dijit.layout.BorderContainer({
                style:"padding:0;width:380px;height:120px;",
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

            toolbar.addChild(new dijit.ToolbarSeparator());

            toolbar.addChild(
                new dijit.form.Button({
                    label: nls.assignUsersForTendering,
                    iconClass: "icon-16-container icon-16-add",
                    style:"outline:none!important;",
                    onClick: dojo.hitch(this, 'assignUserPermission')
                })
            );

            toolbar.addChild(new dijit.ToolbarSeparator());

            toolbar.addChild(
                new dijit.form.Button({
                    label: nls.publish,
                    iconClass: "icon-16-container icon-16-indent",
                    style:"outline:none!important;",
                    onClick: function(){
                        buildspace.dialog.confirm(nls.confirmation,'<div>'+nls.generateBillRef+'</div>',80,300, dojo.hitch(self.mainDialog, "doPublishToTender"));
                    }
                })
            );

            var msgContainer = new ContentPane({
                content: nls.generateBillRef,
                region: "center",
                style:"padding:5px;text-align:center"
            });

            borderContainer.addChild(toolbar);
            borderContainer.addChild(msgContainer);

            return borderContainer;
        },
        assignUserPermission: function() {
            var self = this;
            var assignGroupProjectGrid = new AssignGroupProjectGrid( {
                rootProject: this.project,
                sysName: 'Tendering',
                projectStatus: buildspace.constants.USER_PERMISSION_STATUS_TENDERING
            } );
            assignGroupProjectGrid.show();
            assignGroupProjectGrid.selectGroup();

            this._connects.push(connect.connect(assignGroupProjectGrid, 'save', function(){
                self.assignGroupProjectGridSavedState = true;
            }));
            this._connects.push(connect.connect(assignGroupProjectGrid, 'isAdminUpdate', function(){
                self.assignGroupProjectGridSavedState = true;
            }));
        }
    });

    return declare('buildspace.apps.ProjectBuilder.Builder', dijit.layout.BorderContainer, {
        style: "padding:0;margin:0;width:100%;height:100%;",
        gutters: false,
        project: null,
        workarea: null,
        publishToTenderDialog: null,
        postCreate: function(){
            this.inherited(arguments);

            var self = this,
                analysisStatusXhr = dojo.xhrGet({
                    url: "projectAnalyzer/getAnalysisStatus/",
                    content: { pid: this.project.id },
                    handleAs: "json"
                }),validateEmptyGrandTotalQtyXhr = dojo.xhrGet({
                    url: "projectBuilder/validateEmptyGrandTotalQty/",
                    content: { pid: this.project.id },
                    handleAs: "json"
                });

            validateEmptyGrandTotalQtyXhr.then(function(r){
                if(r.has_error){
                    self.displayEmptyGrandTotalQtyDialog(r);
                }
            });

            analysisStatusXhr.then(function(analysisStatus){
                var toolbar = new dijit.Toolbar({region: "top", style:"padding:2px;width:100%;"}),
                    workarea = self.workarea = WorkArea({
                        rootProject: self.project
                    });

                toolbar.addChild(
                    new dijit.form.Button({
                        label: nls.importRates,
                        iconClass: "icon-16-container icon-16-import",
                        onClick: function(){
                            buildspace.app.launch({
                                __children: [],
                                icon: "project_import_rates",
                                id: self.project.id+'-project_import_rates',
                                is_app: true,
                                level: 0,
                                sysname: "ProjectImportRates",
                                title: nls.importRates
                            },{
                                type: buildspace.constants.STATUS_PRETENDER,
                                project: self.project
                            });
                        }
                    })
                );

                toolbar.addChild(new dijit.ToolbarSeparator());

                var sortOptions = ['resourceAnalysis', 'scheduleOfRateAnalysis'],
                    menu = new DropDownMenu({ style: "display: none;"});

                dojo.forEach(sortOptions, function(opt){
                    var disabled = true;
                    if(opt == 'resourceAnalysis' && analysisStatus.enable_resource_analysis == true){
                        disabled = false;
                    }else if(opt == 'scheduleOfRateAnalysis' && analysisStatus.enable_schedule_of_rate_analysis == true){
                        disabled = false;
                    }

                    menu.addChild(new MenuItem({
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
                                type: buildspace.constants.STATUS_PRETENDER,
                                opt: opt,
                                project: self.project
                            });
                        }
                    }));
                });

                toolbar.addChild(
                    new DropDownButton({
                        id: "analyzer-"+self.project.id+"-dropDownButton",
                        label: nls.projectAnalyzer,
                        iconClass: "icon-16-container icon-16-project_analyzer",
                        style:"outline:none!important;",
                        disabled: (!analysisStatus.enable_resource_analysis && !analysisStatus.enable_schedule_of_rate_analysis),
                        dropDown: menu
                    })
                );

                toolbar.addChild(new dijit.ToolbarSeparator());
                toolbar.addChild(
                    new dijit.form.Button({
                        label: nls.projectSummary,
                        iconClass: "icon-16-container icon-16-list",
                        onClick: function(e){
                            var projectBreakDownGrid = dijit.byId('projectBuilder-projectBreakdownGrid'),
                                pb = buildspace.dialog.indeterminateProgressBar({
                                    title: nls.pleaseWait + '...'
                                });

                            var checkTenderAlternativeXhr = dojo.xhrGet({
                                url: "getTenderAlternatives/"+String(self.project.id)+"/1",
                                handleAs: "json"
                            });

                            pb.show().then(function(){
                                checkTenderAlternativeXhr.then(function(r){
                                    pb.hide();
                                    var d;
                                    if(r.items.length > 2){
                                        self.project.has_tender_alternative = 1;
                                        //getTenderAlternatives will return project name and last row along with tender alternative records. so we exclude 2 returned items from getTenderAlternatives to check if there is  any tender alternative records
                                        d = new TenderAlternativeListDialog({
                                            project: self.project,
                                            workArea: self.workarea,
                                            type: 'printPdf'
                                        });
                                    }else{
                                        self.project.has_tender_alternative = 0;
                                        d = new GeneratorDialog({
                                            project: self.project,
                                            onSuccess: dojo.hitch(self, "_openProjectSummary"),
                                            onClickErrorNode: function(bill, evt){
                                                switch (parseInt(String(bill.type))) {
                                                    case buildspace.apps.ProjectBuilder.ProjectStructureConstants.TYPE_BILL:
                                                        if (parseInt(String(bill['bill_status'])) == buildspace.apps.ProjectBuilder.ProjectStructureConstants.BILL_STATUS_OPEN) {
                                                            self.workarea.initTab(bill, {
                                                                billId: parseInt(String(bill.id)),
                                                                billType: parseInt(String(bill.bill_type)),
                                                                billLayoutSettingId: bill.billLayoutSettingId,
                                                                projectBreakdownGrid: projectBreakDownGrid,
                                                                rootProject: self.project
                                                            });
                                                        }
                                                        break;
                                                    case buildspace.apps.ProjectBuilder.ProjectStructureConstants.TYPE_SUPPLY_OF_MATERIAL_BILL:
                                                        self.workarea.initTab(bill, {
                                                            billId: parseInt(String(bill.id)),
                                                            somBillLayoutSettingId: bill.somBillLayoutSettingId,
                                                            projectBreakdownGrid: projectBreakDownGrid,
                                                            rootProject: self.project
                                                        });
                                                        break;
                                                    case buildspace.apps.ProjectBuilder.ProjectStructureConstants.TYPE_SCHEDULE_OF_RATE_BILL:
                                                        self.workarea.initTab(bill, {
                                                            billId: parseInt(String(bill.id)),
                                                            sorBillLayoutSettingId: bill.sorBillLayoutSettingId,
                                                            projectBreakdownGrid: projectBreakDownGrid,
                                                            rootProject: self.project
                                                        });
                                                        break;
                                                    default:
                                                        break;
                                                }
                                            }
                                        });
                                    }

                                    d.show();
                                });
                            });
                        }
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
                                type: buildspace.constants.STATUS_PRETENDER,
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
                                type: buildspace.constants.STATUS_PRETENDER,
                                project: self.project,
                                canEdit: self.project.status_id == buildspace.constants.STATUS_PRETENDER
                            });
                        }
                    })
                );

                toolbar.addChild(new dijit.ToolbarSeparator());
                toolbar.addChild(
                    new dijit.form.Button({
                        id: "locationManagement-"+self.project.id+"-mainButton",
                        label: nls.locationManagement,
                        iconClass: "icon-16-container icon-16-shopping_basket",
                        onClick: function(e) {
                            buildspace.app.launch({
                                __children: [],
                                icon: "project_location_management",
                                id: self.project.id+'-location_management',
                                is_app: true,
                                level: 0,
                                sysname: "ProjectLocationManagement",
                                title: nls.locationManagement
                            },{
                                type: buildspace.constants.STATUS_PRETENDER,
                                project: self.project,
                                canEdit: true
                            });
                        }
                    })
                );

                if(self.project.is_admin[0]){
                    toolbar.addChild(new dijit.ToolbarSeparator());
                    toolbar.addChild(
                        new dijit.form.Button({
                            id: self.project.id+'PublishToTenderRow-button',
                            label: nls.publishToTender,
                            iconClass: "icon-16-container icon-16-indent",
                            onClick: function(e) {
                                var pb = buildspace.dialog.indeterminateProgressBar({
                                    title:nls.pleaseWait+'...'
                                });

                                pb.show().then(function(){
                                    dojo.xhrGet({
                                        url: "projectAnalyzer/getAnalysisStatus",
                                        content: { pid: self.project.id },
                                        handleAs: "json",
                                        load: function(stat) {
                                            pb.hide();
                                            if(stat.enable_resource_analysis && stat.enable_schedule_of_rate_analysis && stat.enable_schedule_of_qty){
                                                self.publishToTender();
                                            }else{
                                                buildspace.dialog.alert(nls.alert, nls.cannotPushToTenderNoOpenBill, 100, 300);
                                            }
                                        },
                                        error: function(error) {
                                            pb.hide();
                                        }
                                    });
                                });
                            }
                        })
                    );
                }

                workarea.startup();

                self.addChild(toolbar);
                self.addChild(workarea);
            });
        },
        _openProjectSummary: function(){
            var self = this,
                pb = buildspace.dialog.indeterminateProgressBar({
                    title:nls.pleaseWait+'...'
                });

            pb.show().then(function(){
                dojo.xhrGet({
                    url: "projectSummary/projectSummaryData",
                    content: { id: self.project.id },
                    handleAs: "json"
                }).then(function(resp){
                    pb.hide();
                    buildspace.app.launch({
                        __children: [],
                        icon: "project_summary",
                        id: self.project.id+'-project_summary',
                        is_app: true,
                        level: 0,
                        sysname: "ProjectSummary",
                        title: nls.projectSummary
                    },{
                        project: self.project,
                        projectSummaryData: resp
                    });
                });
            });
        },
        publishToTender: function(){
            var self = this,
                validateEmptyGrandTotalQtyXhr = dojo.xhrGet({
                    url: "projectBuilder/validateEmptyGrandTotalQty/",
                    content: { pid: this.project.id },
                    handleAs: "json"
                }),
                validateZeroGrandTotalQtyXhr = dojo.xhrGet({
                    url: "projectBuilder/validateZeroGrandTotalQty/",
                    content: { pid: this.project.id },
                    handleAs: "json"
                }),
                validateHeadsWithoutItemsXhr = dojo.xhrGet({
                    url: "projectBuilder/validateHeadsWithoutItems/",
                    content: { pid: this.project.id },
                    handleAs: "json"
                });

            var onYes = function(){
                validateEmptyGrandTotalQtyXhr.then(function(r){
                    if(r.has_error){
                        self.displayEmptyGrandTotalQtyDialog(r);
                    }else{
                        validateZeroGrandTotalQtyXhr.then(function(rz){
                            if(rz.has_error){
                                self.displayEmptyGrandTotalQtyDialog(rz);
                            }else{
                                var pb = buildspace.dialog.indeterminateProgressBar({
                                    title:nls.pleaseWait+'...'
                                }),
                                validateTenderAlternativeXhr = dojo.xhrGet({
                                    url: "getTenderAlternativeUntagProjectBills/"+self.project.id,
                                    handleAs: "json"
                                });

                                pb.show().then(function(){
                                    validateTenderAlternativeXhr.then(function(ret){
                                        pb.hide();
                                        if(ret.success){
                                            var projectBreakDownGrid = dijit.byId('projectBuilder-projectBreakdownGrid');
                                            new GeneratorDialog({
                                                project: self.project,
                                                onSuccess: dojo.hitch(self, "_openPublishToTenderDialog"),
                                                onClickErrorNode: function(bill, evt){
                                                    switch (parseInt(String(bill.type))) {
                                                        case buildspace.apps.ProjectBuilder.ProjectStructureConstants.TYPE_BILL:
                                                            if (parseInt(String(bill['bill_status'])) == buildspace.apps.ProjectBuilder.ProjectStructureConstants.BILL_STATUS_OPEN) {
                                                                self.workarea.initTab(bill, {
                                                                    billId: parseInt(String(bill.id)),
                                                                    billType: parseInt(String(bill.bill_type)),
                                                                    billLayoutSettingId: bill.billLayoutSettingId,
                                                                    projectBreakdownGrid: projectBreakDownGrid,
                                                                    rootProject: self.project
                                                                });
                                                            }
                                                            break;
                                                        case buildspace.apps.ProjectBuilder.ProjectStructureConstants.TYPE_SUPPLY_OF_MATERIAL_BILL:
                                                            self.workarea.initTab(bill, {
                                                                billId: parseInt(String(bill.id)),
                                                                somBillLayoutSettingId: bill.somBillLayoutSettingId,
                                                                projectBreakdownGrid: projectBreakDownGrid,
                                                                rootProject: self.project
                                                            });
                                                            break;
                                                        case buildspace.apps.ProjectBuilder.ProjectStructureConstants.TYPE_SCHEDULE_OF_RATE_BILL:
                                                            self.workarea.initTab(bill, {
                                                                billId: parseInt(String(bill.id)),
                                                                sorBillLayoutSettingId: bill.sorBillLayoutSettingId,
                                                                projectBreakdownGrid: projectBreakDownGrid,
                                                                rootProject: self.project
                                                            });
                                                            break;
                                                        default:
                                                            break;
                                                    }
                                                }
                                            }).show();
                                        }else{
                                            buildspace.dialog.alert(nls.alert, nls.cannotPushToTenderTenderAlternativeUntagBills, 100, 300);
                                        }
                                    });
                                });
                            }
                        });
                    }
                });
            };

            validateHeadsWithoutItemsXhr.then(function(headWithoutItemsResponse){
                if(headWithoutItemsResponse.has_error){
                    self.displayHeadWithoutItemsDialog(headWithoutItemsResponse, onYes);
                }
                else{
                    onYes();
                }
            });
        },
        _openPublishToTenderDialog: function(){
            var publishToTenderDialog = this.publishToTenderDialog = new PublishToTenderDialog({
                id: 'PublishToTenderDialog-'+this.project.id,
                project: this.project,
                mainDialog: this
            });

            publishToTenderDialog.show();
        },
        displayEmptyGrandTotalQtyDialog: function(data){
            new EmptyGrandTotalQtyDialog({
                id: 'EmptyGrandTotalQtyDialog-'+this.project.id,
                project: this.project,
                data: data,
                mainDialog: this
            }).show();
        },
        displayHeadWithoutItemsDialog: function(data, onYes){
            new HeadWithoutItemsDialog({
                id: 'HeadWithoutItemsDialog-'+this.project.id,
                project: this.project,
                data: data,
                mainDialog: this,
                onYes: onYes
            }).show();
        },
        doPublishToTender: function(){
            var self = this,
                grid = dijit.byId('projectBuilder-projectBreakdownGrid'),
                project = self.project,
                pb = buildspace.dialog.indeterminateProgressBar({
                    title: nls.processing+'. '+nls.pleaseWait+'...'
                });

            var onYes = function(){
                pb.show().then(function(){
                    grid.workArea.removeBillTab();
                    dojo.xhrPost({
                        url: "projectBuilder/publishToTender",
                        content: {
                            id: project.id,
                            usersAssignedManually: self.publishToTenderDialog.assignGroupProjectGridSavedState
                        },
                        handleAs: 'json',
                        load: function(resp) {
                            //update status project
                            self.project.status_id = [resp.status_id];

                            //disable all buttons
                            grid.disableButtonsAfterPublish();

                            var projectProperties = dijit.byId('projectProperties-' + self.project.id);

                            if(projectProperties){
                                projectProperties.projectMainInfoForm.disableForm();
                            }

                            var publishToTenderDialog = dijit.byId('PublishToTenderDialog-'+self.project.id);

                            if(publishToTenderDialog){
                                publishToTenderDialog.hide();
                            }

                            pb.hide();
                        },
                        error: function(error) {
                            pb.hide();
                        }
                    });
                });

            };

            var content = '<div>'+nls.generateBillRefConfirmation+'</div>';
            buildspace.dialog.confirm(nls.confirmation,content,100,300, onYes);
        }
    });
});