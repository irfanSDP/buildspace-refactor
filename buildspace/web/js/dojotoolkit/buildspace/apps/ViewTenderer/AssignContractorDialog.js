define('buildspace/apps/ViewTenderer/AssignContractorDialog',[
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
    "dijit/form/DropDownButton",
    "dijit/DropDownMenu",
    "dijit/MenuItem",
    "dojo/text!./templates/assignContractorForm.html",
    "./importRatesDialog",
    './PrintBillDialog',
    './TendererLogDialog',
    "dojo/currency",
    "buildspace/widget/grid/cells/Formatter",
    'buildspace/widget/grid/cells/CheckBox',
    "dojox/grid/enhanced/plugins/IndirectSelection",
    'dojo/i18n!buildspace/nls/ViewTenderer'
], function(declare, Form, _WidgetBase, _OnDijitClickMixin, _TemplatedMixin, _WidgetsInTemplateMixin, FilteringSelect, aspect, lang, connect, when, html, dom, keys, domStyle, DropDownButton, DropDownMenu, MenuItem, template, ImportRatesDialog, PrintBillDialog, TendererLogDialog, Currency, GridFormatter, CheckBox, IndirectSelection, nls){

    var ExportRatesForm = declare('buildspace.apps.ViewTenderer.ExportRatesForm', [Form,
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
        '<input type="hidden" name="cid" value="">' +
        '<input type="hidden" name="wnli" value="">' +
        '<input type="hidden" name="_csrf_token" value="">' +
        '</td>' +
        '</tr>' +
        '</table>' +
        '</form>',
        project: null,
        company: null,
        withNotListedItem: false,
        region: 'center',
        dialogWidget: null,
        exportUrl: null,
        style: "outline:none;padding:0;margin:0;border:none;",
        baseClass: "buildspace-form",
        startup: function(){
            this.inherited(arguments);
            var projectTitle = String(this.project.title);
            var companyName  = String(this.company.name);

            if (projectTitle.length > 60) {
                projectTitle = projectTitle.substring(0, 60);
            }

            if (companyName.length > 60) {
                companyName = companyName.substring(0, 60);
            }

            var filename = companyName+'-'+projectTitle;

            this.setFormValues({
                filename: filename,
                pid: this.project.id,
                cid: this.company.id,
                wnli: this.withNotListedItem,
                _csrf_token: this.project._csrf_token
            });
        },
        submit: function(){
            if(this.validate() && this.exportUrl){
                var values = dojo.formToObject(this.id);
                var filename = values.filename.replace(/ /g, '_');

                buildspace.windowOpen('POST', this.exportUrl, {
                    filename: filename,
                    pid: values.pid,
                    cid: values.cid,
                    wnli: values.wnli,
                    _csrf_token: values._csrf_token
                });

                if(this.dialogWidget){
                    this.dialogWidget.hide();
                }
            }
        }
    });

    var ExportRatesDialog = declare('buildspace.apps.ViewTenderer.ExportRatesDialog', dijit.Dialog, {
        style:"padding:0px;margin:0;",
        title: nls.exportContractorRates,
        project: null,
        company: null,
        withNotListedItem: false,
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
                form = ExportRatesForm({
                    project: this.project,
                    company: this.company,
                    withNotListedItem: this.withNotListedItem,
                    exportUrl: this.exportUrl,
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

    var ContractorGrid = declare('buildspace.apps.ViewTenderer.ContractorGrid', dojox.grid.EnhancedGrid, {
        project: null,
        tenderAlternative: null,
        disableEditing: false,
        style: "border-top:none;",
        region: 'center',
        canSort: function(inSortInfo){
            return false;
        },
        onStyleRow: function(e) {
            this.inherited(arguments);

            if(e.node.children[0]){
                if(e.node.children[0].children[0].rows.length >= 2){
                    var elemToHide = e.node.children[0].children[0].rows[1],
                        childElement = e.node.children[0].children[0].rows[0].children;

                    elemToHide.parentNode.removeChild(elemToHide);

                    dojo.forEach(childElement, function(child, i){
                        var rowSpan = dojo.attr(child, 'rowSpan');

                        if(!rowSpan || rowSpan < 2)
                            dojo.attr(child, 'rowSpan', 2);
                    });
                }
            }
        },
        postCreate: function(){
            var self = this;
            self.inherited(arguments);

            this.on('RowClick', function(e){
                var item = self.getItem(e.rowIndex);
                if(item && parseInt(String(item.id)) > 0){
                    self.disableToolbarButtons(false);
                }else{
                    self.disableToolbarButtons(true);
                }
            });

            this.on('CellClick', function(e) {
                var colField = e.cell.field,
                    item     = self.getItem(e.rowIndex);

                if(colField == 'awarded' && item && parseInt(String(item.id)) > 0 && !self.disableEditing){
                    self.awardCompany(item);
                }
            });

            this.companyIds = [];

            this.store.fetch({
                onComplete: function (items) {
                    dojo.forEach(items, function (item, index) {

                        if(item.awarded[0]){
                            var diff = parseFloat(String(item.adjusted_total)) - parseFloat(String(item.total));

                            if(diff > 0){
                                diff = '<span style="color:blue">' + Currency.format(diff) + '</span>';
                            }else if(diff < 0){
                                diff = '<span style="color:red">' + Currency.format(diff) + '</span>';
                            }else{
                                diff = Currency.format(diff);
                            }

                            self.structure.cells[1][0].name = nls.diff+': ' + diff;
                            self.set('structure', self.structure);
                        }

                        if(item && parseInt(String(item.id)) > 0 && item.show[0]){
                            self.companyIds.push(parseInt(String(item.id)));
                        }
                    });
                }
            });
        },
        doStartEdit: function(inCell, inRowIndex){
            var self = this,
                item = this.getItem(inRowIndex),
                store = this.store;

            if(this.companyIds.length == 5 && item.show[0]){
                store.setValue(item, 'show', false);
                store.save();
                return;
            }

            this.inherited(arguments);

            if(item){
                this.pushItemIdIntoGridArray(item, item.show[0]);
                this.updateCompanySelection();
            }
        },
        dodblclick: function(e){
            this.onRowDblClick(e);
        },
        deleteCompany: function(item){
            var self = this, title = nls.removeContractorFromTender+' '+buildspace.truncateString(this.project.title, 25),
                msg = nls.areYouSureToRemove+' '+buildspace.truncateString(item.name, 30)+'?',
                pb = buildspace.dialog.indeterminateProgressBar({
                    title:nls.deleting+'. '+nls.pleaseWait+'...'
                });
            
            var tenderAlternativeId = (this.tenderAlternative) ? parseInt(String(this.tenderAlternative.id)) : -1;

            var xhrArgs = {
                url: 'viewTenderer/tenderCompanyDelete',
                content: {
                    pid: parseInt(String(self.project.id)),
                    cid: parseInt(String(item.id)),
                    tid: tenderAlternativeId,
                    _csrf_token: item._csrf_token
                },
                handleAs: 'json',
                load: function(data) {
                    if(data.success){
                        var store = self.store;
                        var reconstructBillContainer = (item.show[0]) ? true : false;

                        self.pushItemIdIntoGridArray(item, false);

                        if(item.awarded[0]){
                            self.structure.cells[1][0].name = nls.diff+': ' + Currency.format(0);
                            self.set('structure', self.structure);
                        }

                        store.deleteItem(item);
                        store.save();
                        store.close();
                        self.sort();

                        self.disableToolbarButtons(true);

                        var selectContractor = dijit.byId('assignContractorSelect');
                        selectContractor.store.close();

                        if(reconstructBillContainer){
                            var projectBreakdown = dijit.byId('main-project_breakdown');
                            projectBreakdown.content.reconstructBillContainer();
                        }
                    }
                    pb.hide();
                },
                error: function(error) {
                    pb.hide();
                }
            };

            new buildspace.dialog.confirm(title, msg, 80, 400, function() {
                pb.show().then(function(){
                    dojo.xhrPost(xhrArgs);
                });
            }, function() {
                //nothing
            });
        },
        awardCompany: function(item){
            var self = this,
                pb = buildspace.dialog.indeterminateProgressBar({
                    title:nls.pleaseWait+'...'
                });
            var tenderAlternativeId = (this.tenderAlternative) ? parseInt(String(this.tenderAlternative.id)) : -1;

            pb.show().then(function(){
                dojo.xhrPost({
                    url: 'viewTenderer/awardCompany',
                    content: {
                        pid: parseInt(String(self.project.id)),
                        cid: parseInt(String(item.id)),
                        tid: tenderAlternativeId,
                        _csrf_token: String(item._csrf_token)
                    },
                    handleAs: 'json',
                    load: function(data) {
                        if(data.success){
                            var store = self.store;
                            store.save();
                            store.close();
                            self.sort();
    
                            //reconstruct projcet breakdown
                            var projectBreakdown = dijit.byId('main-project_breakdown');
                            projectBreakdown.content.reconstructBillContainer();
    
                            self.setDiff();
                        }
                        pb.hide();
                    },
                    error: function(error) {
                        pb.hide();
                    }
                });
            });
        },
        setDiff: function(item){
            var self = this;

            this.store.fetch({
                onComplete: function (items) {
                    dojo.forEach(items, function (item, index) {

                        if(item.awarded[0]){
                            var diff = parseFloat(String(item.adjusted_total)) - parseFloat(String(item.total));

                            if(diff > 0){
                                diff = '<span style="color:blue">' + Currency.format(diff) + '</span>';
                            }else if(diff < 0){
                                diff = '<span style="color:red">' + Currency.format(diff) + '</span>';
                            }else{
                                diff = Currency.format(diff);
                            }

                            self.structure.cells[1][0].name = nls.diff+': ' + diff;
                            self.set('structure', self.structure);
                        }
                    });
                }
            });
        },
        disableToolbarButtons: function(isDisable, buttonsToEnable){
            var deleteRowBtn = dijit.byId('TendererSetting-'+this.project.id+'-DeleteRow-button'),
                importRateRowBtn = dijit.byId('TendererSetting-'+this.project.id+'-ImportRateRow-button'),
                exportRateRowBtn = dijit.byId('TendererSetting-'+this.project.id+'-ExportRateRow-button'),
                refreshRateRowBtn = dijit.byId('TendererSetting-'+this.project.id+'-RefreshRateRow-button'),
                printRateRowBtn = dijit.byId('TendererSetting-'+this.project.id+'-PrintRateRow-button'),
                logRateRowBtn = dijit.byId('TendererSetting-'+this.project.id+'-LogRateRow-button');


            if(this.disableEditing){
                if(deleteRowBtn)
                    deleteRowBtn._setDisabledAttr(true);
                if(importRateRowBtn)
                    importRateRowBtn._setDisabledAttr(true);
                if(refreshRateRowBtn)
                    printRateRowBtn._setDisabledAttr(true);
            }else{
                if(deleteRowBtn)
                    deleteRowBtn._setDisabledAttr(isDisable);
                if(importRateRowBtn)
                    importRateRowBtn._setDisabledAttr(isDisable);
                if(printRateRowBtn)
                    printRateRowBtn._setDisabledAttr(isDisable);
            }

            if(exportRateRowBtn)
                exportRateRowBtn._setDisabledAttr(isDisable);

            if(printRateRowBtn)
                printRateRowBtn._setDisabledAttr(isDisable);

            if(logRateRowBtn)
                logRateRowBtn._setDisabledAttr(isDisable);

            if(refreshRateRowBtn)
                refreshRateRowBtn._setDisabledAttr(isDisable);

            if(isDisable && buttonsToEnable instanceof Array ){
                var _this = this;
                dojo.forEach(buttonsToEnable, function(label){
                    var btn = dijit.byId('TendererSetting-'+_this.project.id+'-'+label+'Row-button');
                    if(btn)
                        btn._setDisabledAttr(false);
                })
            }
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
            var idx = dojo.indexOf(grid.companyIds, parseInt(String(item.id)));
            if(select){
                if(idx == -1){
                    grid.companyIds.push(parseInt(String(item.id)));
                }
            }else{
                if(idx != -1){
                    grid.companyIds.splice(idx, 1);
                }
            }
        },
        toggleAllSelection:function(checked){
            var grid = this, selection = grid.selection;
            if(checked){
                selection.selectRange(0, grid.rowCount-1);
                grid.companyIds = [];
                grid.store.fetch({
                    onComplete: function (items) {
                        dojo.forEach(items, function (item, index) {
                            if(item && parseInt(String(item.id)) > 0){
                                grid.companyIds.push(parseInt(String(item.id)));
                            }
                        });
                    }
                });
            }else{
                selection.deselectAll();
                grid.companyIds = [];
            }

            this.updateCompanySelection();
        },
        updateCompanySelection: function(){
            var self = this,
                companyIds = self.companyIds;

            dojo.xhrPost({
                url: 'viewTenderer/updateCompanySelection',
                content: { projectId: self.project.id, companyIds: [companyIds] },
                handleAs: 'json',
                load: function(data) {
                    if(data.success){
                        self.store.save();
                        self.store.close();

                        self._refresh();

                        var projectBreakdown = dijit.byId('main-project_breakdown');
                        projectBreakdown.content.reconstructBillContainer();
                    }
                },
                error: function(error) {

                }
            });
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
                    url: 'viewTenderer/sortUpdate',
                    content: { pid: self.project.id, opt: sortBy, _csrf_token: self.project._csrf_token },
                    handleAs: 'json',
                    load: function(data) {
                        if(data.success){
                            var store = self.store;
                            store.save();//just in case the store still dirty
                            store.close();
                            self.sort();
    
                            //reconstruct projcet breakdown
                            var projectBreakdown = dijit.byId('main-project_breakdown');
                            projectBreakdown.content.reconstructBillContainer();
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

    var ContractorGridContainer = declare('buildspace.apps.ViewTenderer.ContractorGridContainer', dijit.layout.BorderContainer, {
        style: "padding:0px;border:0px;width:100%;height:100%;",
        gutters: false,
        region: 'center',
        project: null,
        tenderAlternative: null,
        disableEditing: false,
        grid: null,
        gridOpts: {},
        postCreate: function(){
            var self = this;
            self.inherited(arguments);
            lang.mixin(self.gridOpts, {project: self.project, tenderAlternative: self.tenderAlternative, region:"center", disableEditing: self.disableEditing});
            var grid = this.grid = new ContractorGrid(self.gridOpts);

            var toolbar = new dijit.Toolbar({region:"top", style:"padding:2px;border:0px;width:100%;"});
            toolbar.addChild(
                new dijit.form.Button({
                    id: 'TendererSetting-'+self.project.id+'-DeleteRow-button',
                    label: nls.remove,
                    iconClass: "icon-16-container icon-16-delete",
                    disabled: (grid.selection.selectedIndex > -1 && !self.disableEditing) ? false : true,
                    onClick: function(e){
                        if(grid.selection.selectedIndex > -1){
                            var item = grid.getItem(grid.selection.selectedIndex);
                            if(item && parseInt(String(item.id)) > 0){
                                grid.deleteCompany(item, self.tenderAlternative);
                            }
                        }
                    }
                })
            );
            toolbar.addChild(new dijit.ToolbarSeparator());
            toolbar.addChild(
                new dijit.form.Button({
                    id: 'TendererSetting-'+self.project.id+'-ImportRateRow-button',
                    label: nls.importContractorRates,
                    iconClass: "icon-16-container icon-16-import",
                    disabled: grid.selection.selectedIndex > -1 ? false : true,
                    onClick: function(e){
                        if(grid.selection.selectedIndex > -1){
                            var item = grid.getItem(grid.selection.selectedIndex);
                            if(item && parseInt(String(item.id)) > 0){
                                var importRatesDialog = new ImportRatesDialog({
                                    title: nls.importContractorRates,
                                    project: self.project,
                                    company: item
                                });

                                importRatesDialog.show();
                            }
                        }
                    }
                })
            );

            var exportRatesDropDown = new DropDownMenu({ style: "display: none;"});

            exportRatesDropDown.addChild(new MenuItem({
                label: nls.withNotListedItem,
                onClick: function(e){
                    self.exportRates(true);
                }
            }));

            exportRatesDropDown.addChild(new MenuItem({
                label: nls.withoutNotListedItem,
                onClick: function(e){
                    self.exportRates(false);
                }
            }));

            var importDropDownBtn = new DropDownButton({
                id: 'TendererSetting-'+self.project.id+'-ExportRateRow-button',
                label: nls.exportContractorRates,
                iconClass: "icon-16-container icon-16-import",
                dropDown: exportRatesDropDown,
                disabled: grid.selection.selectedIndex > -1 ? false : true
            });

            toolbar.addChild(new dijit.ToolbarSeparator());
            toolbar.addChild(importDropDownBtn);

            toolbar.addChild(new dijit.ToolbarSeparator());
            toolbar.addChild(
                new dijit.form.Button({
                    id: 'TendererSetting-'+self.project.id+'-LogRateRow-button',
                    label: 'Log',
                    iconClass: "icon-16-container icon-16-diary",
                    disabled: grid.selection.selectedIndex > -1 ? false : true,
                    onClick: function(e){
                        if(grid.selection.selectedIndex > -1){
                            var item = grid.getItem(grid.selection.selectedIndex);
                            if(item && parseInt(String(item.id)) > 0){
                                var pb = buildspace.dialog.indeterminateProgressBar({
                                    title:nls.pleaseWait+'...'
                                });

                                pb.show().then(function(){
                                    var projectLogsQuery = dojo.xhrGet({
                                        url: "viewTenderer/getTendererLogCount",
                                        handleAs: "json",
                                        content: {
                                            pid: self.project.id,
                                            cid: item.id
                                        }
                                    });

                                    projectLogsQuery.then(function(ret) {
                                        var d = new TendererLogDialog({
                                            project: self.project,
                                            company: item,
                                            logData: ret
                                        });

                                        d.show();
                                        pb.hide();
                                    });
                                });
                            }
                        }
                    }
                })
            );

            toolbar.addChild(new dijit.ToolbarSeparator());
            toolbar.addChild(
                new dijit.form.Button({
                    id: 'TendererSetting-'+self.project.id+'-RefreshRateRow-button',
                    label: nls.refresh,
                    iconClass: "icon-16-container icon-16-reload",
                    disabled: grid.selection.selectedIndex > -1 ? false : true,
                    onClick: function(e){
                        if(grid.selection.selectedIndex > -1){
                            var item = grid.getItem(grid.selection.selectedIndex);

                            var pb = buildspace.dialog.indeterminateProgressBar({
                                title: nls.pleaseWait+'...'
                            });

                            pb.show().then(function(){
                                dojo.xhrPost({
                                    url: 'viewTenderer/refreshContractorRates',
                                    content: {
                                        pid: self.project.id,
                                        cid: item.id
                                    },
                                    handleAs: 'json',
                                    load: function(data) {
                                        pb.hide();
    
                                        if(data.success){
                                            grid.store.save();
                                            grid.store.close();
                                            grid._refresh();
    
                                            if(item.show[0] == true){
                                                var projectBreakdown = dijit.byId('main-project_breakdown');
                                                projectBreakdown.content.reconstructBillContainer();
                                            }
                                        }
                                    },
                                    error: function(error) {
                                        pb.hide();
                                    }
                                });
                            });
                        }
                    }
                })
            );

            toolbar.addChild(new dijit.ToolbarSeparator());
            toolbar.addChild(
                new dijit.form.Button({
                    id: 'TendererSetting-'+self.project.id+'-PrintRateRow-button',
                    label: nls.printContractorRates,
                    iconClass: "icon-16-container icon-16-print",
                    disabled: grid.selection.selectedIndex > -1 ? false : true,
                    onClick: dojo.hitch(this, 'printRates')
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
                new DropDownButton({
                    label: nls.sort,
                    name: "sortBy",
                    dropDown: menu
                })
            );

            self.addChild(toolbar);
            self.addChild(new dijit.layout.ContentPane({style: 'width:100%', content:grid, region:'center'}));
        },
        exportRates: function (withNotListedItem){
            var grid = this.grid;

            if(grid.selection.selectedIndex > -1){
                var item = grid.getItem(grid.selection.selectedIndex);
                var title = withNotListedItem ? nls.withNotListedItem : nls.withoutNotListedItem;

                var dialog = ExportRatesDialog({
                    title: nls.exportContractorRates+' ('+title+')',
                    project: this.project,
                    company: item,
                    withNotListedItem: withNotListedItem,
                    exportUrl: 'viewTenderer/exportContractorRates'
                });

                dialog.show();
            }
        },
        printRates: function (){
            var grid = this.grid;

            if(grid.selection.selectedIndex > -1){
                var item = grid.getItem(grid.selection.selectedIndex);

                var dialog = new PrintBillDialog({
                    projectId: parseInt(String(this.project.id)),
                    tenderAlternative: this.tenderAlternative,
                    title: nls.printBQ + ' :: ' + item.name,
                    contractor: item,
                    _csrf_token: String(item._csrf_token)
                });

                dialog.show();
            }
        }
    });

    var AssignContractorForm = declare('buildspace.apps.ViewTenderer.AssignContractorForm', [Form,
        _WidgetBase,
        _TemplatedMixin,
        _WidgetsInTemplateMixin,
        dojox.form.manager._Mixin,
        dojox.form.manager._NodeMixin,
        dojox.form.manager._ValueMixin,
        dojox.form.manager._DisplayMixin], {
        templateString: template,
        disableEditing: false,
        project: null,
        region: 'top',
        style: "outline:none;",
        baseClass: "buildspace-form",
        nls: nls,
        contractorGrid: null,
        formValues: [],
        startup: function(){
            this.inherited(arguments);
            this.setFormValues(this.formValues);
        },
        postCreate: function(){
            this.inherited(arguments);

            this.selectStore = new dojo.data.ItemFileReadStore({
                url:"viewTenderer/getContractorList/id/"+this.project.id,
                clearOnClose: true
            });

            this.selectContractor = new FilteringSelect({
                id: 'assignContractorSelect',
                name: "tender_company[company_id]",
                store: this.selectStore,
                style: "width:520px;padding:2px;",
                required: true,
                disabled: this.disableEditing,
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

            var xhrArgs = {
                url: 'viewTenderer/tenderCompanyAdd',
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

                        self.contractorGrid.store.close();
                        self.contractorGrid._refresh();
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
            };

            if(this.validate()){
                pb.show().then(function(){
                    dojo.xhrPost(xhrArgs);
                });
            }
        }
    });

    return declare('buildspace.apps.ViewTenderer.AssignContractorDialog', dijit.Dialog, {
        style:"padding:0px;margin:0px;",
        title: nls.assignContractors,
        disableEditing: false,
        project: null,
        tenderAlternative: null,
        formValues: [],
        buildRendering: function(){
            var content = this.createContent();
            content.startup();
            this.content = content;
            this.title = nls.assignContractors+' :: '+buildspace.truncateString(this.project.title, 45);
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
            var self = this;
            var borderContainer = new dijit.layout.BorderContainer({
                style:"padding:0px;width:850px;height:350px;",
                gutters: false
            }),formatter = new GridFormatter();

            var toolbar = new dijit.Toolbar({
                region: "top",
                style: "outline:none!important;padding:2px;overflow:hidden;border-right:0px;border-left:0px;border-top:0px;"
            });

            var tenderAlternativeId = (this.tenderAlternative) ? parseInt(String(this.tenderAlternative.id)) : -1;
            var content = new dijit.layout.BorderContainer({
                    style:"padding-bottom:5px;width:100%;height:100%;border:0px;",
                    gutters: false,
                    region: 'center'
                }),
                store = new dojo.data.ItemFileWriteStore({
                    url:"viewTenderer/getContractors/id/"+parseInt(String(this.project.id))+"/tid/"+tenderAlternativeId,
                    clearOnClose: true
                }),
                contractorGrid = new ContractorGridContainer({
                    project: this.project,
                    tenderAlternative: this.tenderAlternative,
                    disableEditing: this.disableEditing,
                    gridOpts: {
                        id: 'viewTenderer-contractorGrid_'+this.project.id,
                        store: store,
                        structure: {
                            cells: [
                                [{
                                    name: nls.show,
                                    field: 'show',
                                    width:'50px',
                                    styles:'text-align:center;',
                                    type: dojox.grid.cells.Bool,
                                    editable: true,
                                    alwaysEditing:true,
                                    rowSpan: 2
                                }, {
                                    name: 'No.',
                                    field: 'id',
                                    width:'30px',
                                    styles:'text-align:center;',
                                    formatter: formatter.rowCountCellFormatter,
                                    rowSpan: 2
                                },{
                                    name: nls.name,
                                    field: 'name',
                                    width:'auto',
                                    rowSpan: 2
                                },{
                                    name: nls.originalTotal,
                                    field: 'total', width:'120px',
                                    headerClasses: "typeHeader1",
                                    styles:'text-align:right;',
                                    formatter: formatter.unEditableCurrencyCellFormatter
                                },{
                                    name: nls.adjustedTotal,
                                    field: 'adjusted_total',
                                    headerClasses: "typeHeader1",
                                    width:'120px', styles:'text-align:right;',
                                    formatter: formatter.unEditableCurrencyCellFormatter
                                },{
                                    name: nls.action,
                                    field: 'awarded',
                                    width:'80px',
                                    styles:'text-align:center;',
                                    formatter: formatter.selectedCellFormatter,
                                    rowSpan: 2
                                }],
                                [{
                                    colSpan: 2,
                                    headerClasses: "staticHeader typeHeader1",
                                    headerId: 1,
                                    hidden: false,
                                    name: nls.diff + ": " + Currency.format(0),
                                    styles: "text-align:center;"
                                }]
                            ]
                        }
                    }
                }),
                form = new AssignContractorForm({
                    project: this.project,
                    formValues: this.formValues,
                    contractorGrid: contractorGrid.grid,
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
            content.addChild(contractorGrid);

            borderContainer.addChild(toolbar);
            borderContainer.addChild(content);

            return borderContainer;
        }
    });
});