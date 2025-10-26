define('buildspace/apps/SubPackage/AssignContractorDialog',[
    'dojo/_base/declare',
    "dijit/form/Form",
    "dijit/_WidgetBase",
    "dijit/_OnDijitClickMixin",
    "dijit/_TemplatedMixin",
    "dijit/_WidgetsInTemplateMixin",
    "dijit/form/FilteringSelect",
    'dojo/aspect',
    'dojo/_base/lang',
    "dojo/_base/connect",
    "dojo/when",
    "dojo/html",
    "dojo/dom",
    'dojo/keys',
    "dojo/dom-style",
    "dojo/text!./templates/assignContractorForm.html",
    "./AssignUnitDialog",
    "./ExportSubPackageByContractorDialog",
    "./BillPrintoutSetting/PrintBillDialog",
    "buildspace/widget/grid/cells/Formatter",
    "dojox/grid/enhanced/plugins/IndirectSelection",
    "./importRatesDialog",
    'dijit/form/DropDownButton',
    "dijit/DropDownMenu",
    "dijit/Menu",
    "dijit/MenuItem",
    "dijit/PopupMenuItem",
    'dojo/i18n!buildspace/nls/SubPackages'
], function(declare, Form, _WidgetBase, _OnDijitClickMixin, _TemplatedMixin, _WidgetsInTemplateMixin, FilteringSelect, aspect, lang, connect, when, html, dom, keys, domStyle, template, AssignUnitDialog, ExportSubPackageByContractorDialog, PrintBillDialog, GridFormatter, IndirectSelection, ImportRatesDialog, DropDownButton, DropDownMenu, Menu, MenuItem, PopupMenuItem, nls) {

    var SubContractorGrid = declare('buildspace.apps.SubPackage.SubContractorGrid', dojox.grid.EnhancedGrid, {
        rootProject: null,
        subPackage: null,
        subPackageGrid: null,
        disableEditing: false,
        style: "border-top:none;",
        region: 'center',
        canSort: function(inSortInfo){
            return false;
        },
        postCreate: function(){
            this.inherited(arguments);

            this.on('RowClick', function(e){
                var item = this.getItem(e.rowIndex);
                if(item && item.id > 0){
                    this.disableToolbarButtons(false);
                }else{
                    this.disableToolbarButtons(true);
                }
            });

            this.on('CellClick', function(e) {
                var colField = e.cell.field,
                    item     = this.getItem(e.rowIndex);

                if(colField == 'selected' && item && item.id > 0 && !this.disableEditing){
                    this.saveAsSelectedContractor(item);
                }
            });
        },
        dodblclick: function(e){
            this.onRowDblClick(e);
        },
        deleteCompany: function(item){
            var self = this, title = nls.removeSubContractorFromSubPackage+' '+buildspace.truncateString(this.subPackage.name, 25),
                msg = nls.areYouSureToRemove+' '+buildspace.truncateString(item.name, 30)+'?',
                pb = buildspace.dialog.indeterminateProgressBar({
                    title:nls.deleting+'. '+nls.pleaseWait+'...'
                });

            new buildspace.dialog.confirm(title, msg, 80, 400, function() {
                pb.show().then(function(){
                    dojo.xhrPost({
                        url: 'subPackage/subPackageCompanyDelete',
                        content: { sid: self.subPackage.id, cid: item.id, _csrf_token: item._csrf_token },
                        handleAs: 'json',
                        load: function(data) {
                            if(data.success){
                                self.selection.deselectAll();
                                self.store.deleteItem(item);
                                self.store.save();
                                self.store.close();
                                self.sort();

                                self.subPackageGrid.store.save();//in case it's dirty
                                self.subPackageGrid.store.close();
                                self.subPackageGrid.sort();

                                var selectContractor = dijit.byId('assignSubContractorSelect');
                                selectContractor.store.close();
                            }
                            pb.hide();
                        },
                        error: function(error) {
                            pb.hide();
                        }
                    });
                });

            }, function() {
            });
        },
        assignTypesAndUnits: function() {
            AssignUnitDialog({
                rootProject: this.rootProject,
                subPackage: this.subPackage,
                assignContractorGrid: this,
                subPackageGrid: this.subPackageGrid
            }).show();
        },
        disableToolbarButtons: function(isDisable, buttonsToEnable){
            var deleteRowBtn = dijit.byId('SubPackages_subCons-'+this.subPackage.id+'-DeleteRow-button'),
                importRateRowBtn = dijit.byId('SubPackages_subCons-'+this.subPackage.id+'-ImportRateRow-button'),
                exportImportExcel = dijit.byId('SubPackages_subCons-'+this.subPackage.id+'-ExportExcelRow-button'),
                importBuildspaceRate = dijit.byId('SubPackages_subCons-'+this.subPackage.id+'ImportBuildspaceRatesRow-button'),
                exportBuildspaceRate = dijit.byId('SubPackages_subCons-'+this.subPackage.id+'ExportBuildspaceRatesRow-button'),
                assignUnitBtn = dijit.byId('SubPackages_subCons-'+this.subPackage.id+'-AssignUnit-button'),
                printBQBtn = dijit.byId('SubPackages_subCons-'+this.subPackage.id+'-PrintBQRow-button'),
                exportBuildspaceFileWithSubConRate = dijit.byId('SubPackages_subCons-'+this.subPackage.id+'ExportWithSubConRateRow-button');

            if(this.disableEditing){
                if(deleteRowBtn)
                    deleteRowBtn._setDisabledAttr(true);
                if(importRateRowBtn)
                    importRateRowBtn._setDisabledAttr(true);
                if(exportImportExcel)
                    exportImportExcel._setDisabledAttr(true);
                if(assignUnitBtn)
                    assignUnitBtn._setDisabledAttr(true);
                if(printBQBtn)
                    printBQBtn._setDisabledAttr(true);
                if(exportBuildspaceFileWithSubConRate)
                    exportBuildspaceFileWithSubConRate._setDisabledAttr(true);
                if(importBuildspaceRate)
                    importBuildspaceRate._setDisabledAttr(true);
                if(exportBuildspaceRate)
                    exportBuildspaceRate._setDisabledAttr(true);
            }else{
                if(deleteRowBtn)
                    deleteRowBtn._setDisabledAttr(isDisable);
                if(importRateRowBtn)
                    importRateRowBtn._setDisabledAttr(isDisable);
                if(exportImportExcel)
                    exportImportExcel._setDisabledAttr(isDisable);
                if(printBQBtn)
                    printBQBtn._setDisabledAttr(isDisable);
                if(exportBuildspaceFileWithSubConRate)
                    exportBuildspaceFileWithSubConRate._setDisabledAttr(isDisable);
                if(importBuildspaceRate)
                    importBuildspaceRate._setDisabledAttr(isDisable);
                if(exportBuildspaceRate)
                    exportBuildspaceRate._setDisabledAttr(isDisable);
            }

            if(isDisable && buttonsToEnable instanceof Array ){
                var _this = this;
                dojo.forEach(buttonsToEnable, function(label){
                    var btn = dijit.byId('SubPackages_subCons-'+_this.subPackage.id+'-'+label+'Row-button');
                    if(btn)
                        btn._setDisabledAttr(false);
                });
            }
        },
        sortBy: function(opt){
            var self = this,
                sortBy,
                pb = buildspace.dialog.indeterminateProgressBar({
                    title:nls.sorting+'. '+nls.pleaseWait+'...'
                });

            switch(opt){
                case 'nameDesc':
                    sortBy = 2;
                    break;
                case 'highestToLowest':
                    sortBy = 4;
                    break;
                case 'lowestToHighest':
                    sortBy = 8;
                    break;
                default:
                    sortBy = 1;
            }

            pb.show().then(function(){
                dojo.xhrPost({
                    url: 'subPackage/sortUpdate',
                    content: { sid: self.subPackage.id, opt: sortBy, _csrf_token: self.subPackage._csrf_token },
                    handleAs: 'json',
                    load: function(data) {
                        if(data.success){
                            var store = self.store;
                            store.save();//just in case the store still dirty
                            store.close();
                            self.sort();
                        }
                        pb.hide();
                    },
                    error: function(error) {
                        pb.hide();
                    }
                });
            });
        },
        saveAsSelectedContractor: function (company) {
            var self = this;
            var pb = buildspace.dialog.indeterminateProgressBar({
                title: nls.pleaseWait+'...'
            });

            pb.show().then(function(){
                dojo.xhrPost({
                    url: 'subPackage/setSelectedCompany',
                    content: { sid: self.subPackage.id, cid: company.id, _csrf_token: company._csrf_token },
                    handleAs: 'json',
                    load: function(data) {
                        self.selection.deselectAll();
                        self.store.close();
                        self.sort();

                        self.subPackageGrid.store.save(); //in case it is dirty
                        self.subPackageGrid.store.close();
                        self.subPackageGrid.sort();

                        self.store.fetchItemByIdentity({ 'identity' : company.id,  onItem : function(storeItem){
                            if(storeItem){
                                var idx = self.getItemIndex(storeItem);
                                self.selection.setSelected(idx, true);
                            }
                        }});

                        var pushToPostContractBtn = dijit.byId('SubPackages-'+self.rootProject.id+'-PushToPostContractRow-button');

                        if(pushToPostContractBtn && self.rootProject.status_id[0] == buildspace.constants.STATUS_POSTCONTRACT){
                            pushToPostContractBtn._setDisabledAttr(false);
                        }

                        pb.hide();
                    },
                    error: function(error) {
                        pb.hide();
                    }
                });
            });
        }
    });

    var ExportBillForm = declare('buildspace.apps.ProjectBuilder.ExportBillForm', [Form,
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
            '<input type="hidden" name="pid" value="">' +
            '<input type="hidden" name="sid" value="">' +
            '<input type="hidden" name="cid" value="">' +
            '<input type="hidden" name="with_rate" value="">' +
            '<input type="hidden" name="_csrf_token" value="">' +
            '</td>' +
            '</tr>' +
            '</table>' +
            '</form>',
        project: null,
        subPackage: null,
        contractor: null,
        region: 'center',
        dialogWidget: null,
        exportUrl: null,
        withRate: null,
        style: "outline:none;padding:0;margin:0;border:none;",
        baseClass: "buildspace-form",
        startup: function(){
            this.inherited(arguments);
            var filename = this.contractor ? this.subPackage.name+'_'+this.contractor.name : this.subPackage.name;
            this.setFormValues({
                filename: filename,
                pid: this.project.id,
                sid: this.subPackage.id,
                cid: this.contractor ? this.contractor.id : '',
                with_rate: this.withRate,
                _csrf_token: this.subPackage._csrf_token
            });
        },
        submit: function(){
            if(this.validate() && this.exportUrl){
                var values = dojo.formToObject(this.id);
                var filename = values.filename.replace(/ /g, '_');

                buildspace.windowOpen('POST', this.exportUrl, {
                    filename:filename,
                    pid: values.pid,
                    sid: values.sid,
                    cid: values.cid,
                    with_rate: values.with_rate,
                    _csrf_token: values._csrf_token
                });

                if(this.dialogWidget){
                    this.dialogWidget.hide();
                }
            }
        }
    });

    var ExportBillDialog = declare('buildspace.apps.ProjectBuilder.ExportBillDialog', dijit.Dialog, {
        style:"padding:0px;margin:0;",
        title: nls.downloadZipFile,
        project: null,
        subPackage: null,
        contractor: null,
        exportUrl: null,
        withRate: null,
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
                form = ExportBillForm({
                    project: this.project,
                    subPackage: this.subPackage,
                    contractor: this.contractor,
                    exportUrl: this.exportUrl,
                    withRate: this.withRate,
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

    var SubContractorGridContainer = declare('buildspace.apps.SubPackage.SubContractorGridContainer', dijit.layout.BorderContainer, {
        style: "padding:0px;border:0px;width:100%;height:100%;",
        gutters: false,
        region: 'center',
        subPackage: null,
        rootProject: null,
        subPackageGrid: null,
        disableEditing: false,
        grid: null,
        gridOpts: {},
        postCreate: function(){
            var self = this;
            this.inherited(arguments);

            lang.mixin(this.gridOpts, {
                rootProject: this.rootProject,
                subPackage: this.subPackage,
                disableEditing: this.disableEditing,
                subPackageGrid: this.subPackageGrid,
                region:"center"
            });
            var grid = this.grid = new SubContractorGrid(this.gridOpts);

            var toolbar = new dijit.Toolbar({region:"top", style:"padding:2px;border:0px;width:100%;"});

            toolbar.addChild(
                new dijit.form.Button({
                    id: 'SubPackages_subCons-'+self.subPackage.id+'-DeleteRow-button',
                    label: nls.removeSubContractor,
                    iconClass: "icon-16-container icon-16-delete",
                    disabled: (grid.selection.selectedIndex > -1 && !self.disableEditing) ? false : true,
                    onClick: function(e){
                        if(grid.selection.selectedIndex > -1){
                            var item = grid.getItem(grid.selection.selectedIndex);
                            if(item && item.id > 0){
                                grid.disableToolbarButtons(true);
                                grid.deleteCompany(item);
                            }
                        }
                    }
                })
            );

            toolbar.addChild(new dijit.ToolbarSeparator());

            var importDropDownMenu = new DropDownMenu({ style: "display: none;"});
            var subMenu = new Menu();

            subMenu.addChild(new MenuItem({
                id: 'SubPackages_subCons-'+self.subPackage.id+'ExportWithSubConRateRow-button',
                label: nls.withSubConRates,
                disabled: true,
                onClick: function(e) {
                    var item = grid.getItem(grid.selection.selectedIndex);
                    if(item && item.id > 0){
                        var dialog = ExportBillDialog({
                            project: self.rootProject,
                            subPackage: self.subPackage,
                            contractor: item,
                            withRate: true,
                            exportUrl: 'subPackageExportFile/exportSubPackage'
                        });

                        dialog.show();
                    }
                }
            }));

            subMenu.addChild(new MenuItem({
                id: 'SubPackages_subCons-'+self.subPackage.id+'ExportWithEstimationnRateRow-button',
                label: nls.withEstimationRates,
                onClick: function(e) {
                    var dialog = ExportBillDialog({
                        project: self.rootProject,
                        subPackage: self.subPackage,
                        withRate: true,
                        exportUrl: 'subPackageExportFile/exportSubPackage'
                    });

                    dialog.show();
                }
            }));

            subMenu.addChild(new MenuItem({
                id: 'SubPackages_subCons-'+self.subPackage.id+'ExportWithoutRateRow-button',
                label: nls.withoutRates,
                onClick: function(e) {
                    var dialog = ExportBillDialog({
                        project: self.rootProject,
                        subPackage: self.subPackage,
                        withRate: false,
                        exportUrl: 'subPackageExportFile/exportSubPackage'
                    });

                    dialog.show();
                }
            }));

            importDropDownMenu.addChild(new PopupMenuItem({
                id: 'SubPackages_subCons-'+self.subPackage.id+'ExportBuildspaceFileRow-button',
                label: nls.exportBuildspaceFile,
                popup: subMenu
            }));

            importDropDownMenu.addChild(new MenuItem({
                id: 'SubPackages_subCons-'+self.subPackage.id+'-ExportExcelRow-button',
                label: nls.importExportExcel,
                onClick: function(e){
                    if(grid.selection.selectedIndex > -1){
                        var item = grid.getItem(grid.selection.selectedIndex);
                        if(item && item.id > 0){
                            var exportBillList = dojo.xhrGet({
                                    url: "subPackage/getAllowedExportedBills",
                                    handleAs: "json",
                                    content: {
                                        id: self.subPackage.id,
                                        company_id: item.id
                                    }
                                }),
                                pb = buildspace.dialog.indeterminateProgressBar({
                                    title:nls.pleaseWait+'...'
                                });

                            pb.show().then(function(){
                                when(exportBillList, function(values){
                                    pb.hide();
                                    var dialog = new ExportSubPackageByContractorDialog({
                                        title: nls.importExportSubPackage + ' (' + buildspace.truncateString(item.name, 60) + ')',
                                        data: values,
                                        subPackage: self.subPackage,
                                        company: item,
                                        subPackageGrid: self.subPackageGrid,
                                        subContractorGridContainer: self
                                    });
                                    dialog.show();
                                });
                            });
                        }
                    }
                },
                disabled: (grid.selection.selectedIndex > -1 && !self.disableEditing) ? false : true
            }));

            importDropDownMenu.addChild(new MenuItem({
                id: 'SubPackages_subCons-'+self.subPackage.id+'ImportBuildspaceRatesRow-button',
                label: nls.importTenderRates,
                disabled: (grid.selection.selectedIndex > -1 && !self.disableEditing) ? false : true,
                onClick: function(e){
                    if(grid.selection.selectedIndex > -1){
                        var item = grid.getItem(grid.selection.selectedIndex);
                        if(item && item.id > 0){
                            var importRatesDialog = new ImportRatesDialog({
                                title: nls.importSubContractorRates,
                                contractorGrid: grid,
                                subPackageGrid: self.subPackageGrid,
                                project: self.rootProject,
                                subPackage: self.subPackage,
                                company: item
                            });

                            importRatesDialog.show();
                        }
                    }
                }
            }));

            importDropDownMenu.addChild(new MenuItem({
                id: 'SubPackages_subCons-'+self.subPackage.id+'ExportBuildspaceRatesRow-button',
                label: nls.exportTenderRates,
                disabled: (grid.selection.selectedIndex > -1 && !self.disableEditing) ? false : true,
                onClick: function(e){
                    var item = grid.getItem(grid.selection.selectedIndex);
                    if(item && item.id > 0){
                        var dialog = ExportBillDialog({
                            project: self.rootProject,
                            subPackage: self.subPackage,
                            contractor: item,
                            exportUrl: 'subPackageExportFile/exportContractorRates'
                        });

                        dialog.show();
                    }
                }
            }));

            toolbar.addChild(new DropDownButton({
                id: 'SubPackages_subCons-'+self.subPackage.id+'ImportExportDropDownRow-button',
                label: nls.importExportSubPackage,
                iconClass: "icon-16-container icon-16-export",
                dropDown: importDropDownMenu
            }));

            toolbar.addChild(new dijit.ToolbarSeparator());
            toolbar.addChild(
                new dijit.form.Button({
                    id: 'SubPackages_subCons-'+self.subPackage.id+'-PrintBQRow-button',
                    label: nls.printBQ,
                    iconClass: "icon-16-container icon-16-print",
                    disabled: (grid.selection.selectedIndex > -1 && !self.disableEditing) ? false : true,
                    onClick: function(e) {
                        var item = grid.getItem(grid.selection.selectedIndex);
                        new PrintBillDialog({
                            subPackage: self.subPackage,
                            subContractorId: item.id
                        }).show();
                    }
                })
            );

            var sortOptions = ['nameAsc', 'nameDesc', 'highestToLowest', 'lowestToHighest'];
            var menu = new DropDownMenu({ style: "display: none;"});

            dojo.forEach(sortOptions, function(opt){
                var menuItem = new MenuItem({
                    label: nls[opt],
                    onClick: function(){
                        grid.sortBy(opt);
                    }
                });
                menu.addChild(menuItem);
            });

            toolbar.addChild(new dijit.ToolbarSeparator());

            toolbar.addChild(
                new dijit.form.Button({
                    id: 'SubPackages_subCons-'+self.subPackage.id+'-AssignUnit-button',
                    label: nls.assignTypesAndUnits,
                    iconClass: "icon-16-container icon-16-add",
                    disabled: (!self.disableEditing) ? false : true,
                    onClick: function(e){
                        grid.assignTypesAndUnits();
                    }
                })
            );

            toolbar.addChild(new dijit.ToolbarSeparator());

            toolbar.addChild(
                new DropDownButton({
                    label: nls.sort,
                    name: "sortBy",
                    dropDown: menu
                })
            );

            self.addChild(toolbar);
            self.addChild(new dijit.layout.ContentPane({style: 'width:100%', content:grid, region:'center'}));
        }
    });

    var AssignContractorForm = declare('buildspace.apps.SubPackage.AssignContractorForm', [Form,
        _WidgetBase,
        _TemplatedMixin,
        _WidgetsInTemplateMixin,
        dojox.form.manager._Mixin,
        dojox.form.manager._NodeMixin,
        dojox.form.manager._ValueMixin,
        dojox.form.manager._DisplayMixin], {
        templateString: template,
        subPackage: null,
        region: 'top',
        style: "outline:none;",
        baseClass: "buildspace-form",
        nls: nls,
        subContractorGrid: null,
        formValues: [],
        startup: function(){
            this.inherited(arguments);
            this.setFormValues(this.formValues);
        },
        postCreate: function(){
            this.inherited(arguments);

            this.selectStore = new dojo.data.ItemFileReadStore({
                url:"subPackage/getSubContractorList/id/"+this.subPackage.id,
                clearOnClose: true
            });

            this.selectContractor = new FilteringSelect({
                id: 'assignSubContractorSelect',
                name: "sub_package_company[company_id]",
                store: this.selectStore,
                style: "width:520px;padding:2px;",
                required: true,
                disabled: (this.disableEditing) ? true : false,
                searchAttr: "name"
            }).placeAt(this.contractorSelectDivNode);

            if(this.disableEditing){
                this.saveBtn.set('disabled', true);
            }
        },
        submit: function(){
            var self = this,
                values = dojo.formToObject(this.id),
                pb = buildspace.dialog.indeterminateProgressBar({
                    title:nls.savingData+'. '+nls.pleaseWait+'...'
                });

            if(this.validate()){
                pb.show().then(function(){
                    dojo.xhrPost({
                        url: 'subPackage/subPackageCompanyAdd',
                        content: values,
                        handleAs: 'json',
                        load: function(resp) {
                            dojo.query('[id^="error-"]').forEach(function(node){
                                node.innerHTML = '';
                            });

                            if(resp.success == true){
                                self.setFormValues(self.formValues);
                                self.selectContractor.store.close();
                                self.selectContractor.set('store', self.selectStore);
                                self.selectContractor.set('value', '');

                                self.subContractorGrid.store.close();
                                self.subContractorGrid._refresh();
                            }else{
                                var errors = resp.errors;
                                for(var error in errors){
                                    if(self['error-'+error]){
                                        html.set(self['error-'+error], errors[error]);
                                    }
                                }
                            }
                            pb.hide();
                        },
                        error: function(error) {
                            pb.hide();
                        }
                    });
                });
            }
        }
    });

    return declare('buildspace.apps.SubPackage.AssignContractorDialog', dijit.Dialog, {
        style:"padding:0px;margin:0px;",
        title: nls.assignSubContractors,
        subPackage: null,
        rootProject: null,
        subPackageGrid: null,
        disableEditing: false,
        formValues: [],
        buildRendering: function(){
            var content = this.createContent();
            content.startup();
            this.content = content;

            this.title = nls.assignSubContractors+' :: '+buildspace.truncateString(this.subPackage.name, 45);
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
                style:"padding:0px;width:850px;height:350px;",
                gutters: false
            }),formatter = new GridFormatter();

            var toolbar = new dijit.Toolbar({
                region: "top",
                style: "outline:none!important;padding:2px;overflow:hidden;border-right:0px;border-left:0px;border-top:0px;"
            });

            var content = new dijit.layout.BorderContainer({
                    style:"padding-bottom:5px;width:100%;height:100%;border:0px;",
                    gutters: false,
                    region: 'center'
                }),
                store = new dojo.data.ItemFileWriteStore({
                    url:"subPackage/getSubContractors/id/"+this.subPackage.id,
                    clearOnClose: true
                }),
                subContractorGrid = new SubContractorGridContainer({
                    subPackage: this.subPackage,
                    dialogObj: this,
                    subPackageGrid: this.subPackageGrid,
                    disableEditing: this.disableEditing,
                    rootProject: this.rootProject,
                    gridOpts: {
                        store: store,
                        rootProject: this.rootProject,
                        structure: [
                            {name: 'No.', field: 'id', width:'30px', styles:'text-align:center;', formatter: formatter.rowCountCellFormatter },
                            {name: nls.name, field: 'name', width:'auto' },
                            {name: nls.total, field: 'total', width:'120px', styles:'text-align:right;', formatter: formatter.unEditableCurrencyCellFormatter},
                            {name: nls.action, field: 'selected', width:'80px', styles:'text-align:center;', formatter: formatter.selectedCellFormatter}
                        ]
                    }
                }),
                form = new AssignContractorForm({
                    subPackage: this.subPackage,
                    formValues: this.formValues,
                    subContractorGrid: subContractorGrid.grid,
                    disableEditing: this.disableEditing
                });

            toolbar.addChild(
                new dijit.form.Button({
                    label: nls.close,
                    iconClass: "icon-16-container icon-16-close",
                    style:"outline:none!important;",
                    onClick: dojo.hitch(this, 'hide')
                })
            );

            content.addChild(form);
            content.addChild(subContractorGrid);

            borderContainer.addChild(toolbar);
            borderContainer.addChild(content);

            return borderContainer;
        }
    });
});