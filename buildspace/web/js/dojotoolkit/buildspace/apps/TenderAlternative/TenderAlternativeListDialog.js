define('buildspace/apps/TenderAlternative/TenderAlternativeListDialog',[
    'dojo/_base/declare',
    'dojo/_base/lang',
    "dojo/_base/connect",
    'dojo/keys',
    "dojo/dom-style",
    "dojo/on",
    "dijit/form/Form",
    "dojox/form/manager/_Mixin",
    "dojox/form/manager/_NodeMixin",
    "dojox/form/manager/_ValueMixin",
    "dojox/form/manager/_DisplayMixin",
    "dijit/_WidgetBase",
    "dijit/_OnDijitClickMixin",
    "dijit/_TemplatedMixin",
    "dijit/_WidgetsInTemplateMixin",
    "dijit/form/ValidationTextBox",
    'dojox/grid/EnhancedGrid',
    "buildspace/widget/grid/cells/Formatter",
    'buildspace/widget/grid/Filter',
    'buildspace/apps/PageGenerator/GeneratorDialog',
    'buildspace/apps/Tendering/PrintFinalBQDialog',
    'buildspace/apps/RationalizeRate/PrintBillDialog',
    'dojo/i18n!buildspace/nls/TenderAlternative'
], function(declare, lang, connect, keys, domStyle, on, Form, _ManagerMixin, _ManagerNodeMixin, _ManagerValueMixin, _ManagerDisplayMixin, _WidgetBase, _OnDijitClickMixin, _TemplatedMixin, _WidgetsInTemplateMixin, ValidationTextBox, EnhancedGrid, GridFormatter, Filter, GeneratorDialog, PrintFinalBQDialog, RationalizeRatePrintBillDialog, nls){

    var ExportToExcelForm = declare('buildspace.apps.TenderAlternative.ExportToExcelForm', [Form,
        _WidgetBase,
        _TemplatedMixin,
        _WidgetsInTemplateMixin,
        _ManagerMixin,
        _ManagerNodeMixin,
        _ManagerValueMixin,
        _ManagerDisplayMixin], {
        templateString: '<form data-dojo-attach-point="containerNode">' +
            '<table class="table-form">' +
            '<tr>' +
            '<td class="label" style="width:80px;"><label style="display: inline;"></span>'+nls.exportAs+' :</label></td>' +
            '<td>' +
            '<input type="text" name="filename" style="padding:2px;width:220px;" data-dojo-type="dijit/form/ValidationTextBox" data-dojo-props="trim:true, propercase:true, maxlength:45, required: true"> .xlsx' +
            '</td>' +
            '</tr>' +
            '</table>' +
            '</form>',
        project: null,
        region: 'center',
        url: "",
        defaultFilename: "",
        dialogWidget: null,
        style: "outline:none;padding:0px;margin:0px;border:none;",
        baseClass: "buildspace-form",
        startup: function(){
            this.inherited(arguments);
            this.setFormValues({filename: this.defaultFilename});
        },
        submit: function(){
            var values = dojo.formToObject(this.id);
            if(this.validate()){
                var filename = values.filename.replace(/ /g, '_');
                window.open(this.url+'/'+filename, '_self');

                if(this.dialogWidget){
                    this.dialogWidget.hide();
                }
            }
        }
    });

    var ExportExcelDialog = declare('buildspace.apps.TenderAlternative.ExportExcelDialog', dijit.Dialog, {
        style:"padding:0px;margin:0px;",
        title: nls.exportToExcel,
        project: null,
        url: "",
        defaultFilename: "",
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
            form = ExportToExcelForm({
                project: this.project,
                url: this.url,
                defaultFilename: this.defaultFilename,
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

    var Grid = declare('buildspace.apps.TenderAlternative.TenderAlternativeListGrid', EnhancedGrid, {
        style: "border-top:none;",
        containerDialog: null,
        project: null,
        workArea: null,
        type: 'printPdf',
        opt: null,
        region: 'center',
        postCreate: function(){
            this.inherited(arguments);
            var project = this.project;
            var opt = this.opt;
            var self = this;

            this.on("RowClick", function(e){
                var colField = e.cell.field,
                    rowIndex = e.rowIndex,
                     _item = this.getItem(rowIndex);

                if (_item && parseInt(String(_item.id)) > 0) {
                    switch(self.type){
                        case 'viewTenderer':
                            self.openViewTenderer(_item);
                            break;
                        case 'exportExcel':
                            self.openGeneratorDialog("_exportToExcel", _item);
                            break;
                        case 'printPdf':
                            switch(parseInt(String(project.status_id))){
                                case buildspace.constants.STATUS_PRETENDER:
                                    self.openGeneratorDialog("_openProjectSummary", _item);
                                    break;
                                default:
                                    self.openGeneratorDialog("_printProjectSummaryPdf", _item, opt);
                            }
                            break;
                        case 'printFinalBQ':
                            self.openPrintFinalBQ(_item, opt);
                            break;
                        case 'printRationalizeRate':
                            self.openPrintRationalizeRate(_item, opt);
                    }
                }
            }, true);
        },
        canSort: function(inSortInfo){
            return false;
        },
        openViewTenderer: function(tenderAlternative){
            this.containerDialog.hide();

            buildspace.app.launch({
                __children: [],
                icon: "view_tenderer",
                id: String(this.project.id)+'-view_tenderer',
                is_app: true,
                level: 0,
                sysname: "ViewTenderer",
                title: nls.viewTenderers
            },{
                type: buildspace.constants.STATUS_TENDERING,
                project: this.project,
                tenderAlternative: tenderAlternative
            });
        },
        openGeneratorDialog: function(functionName, tenderAlternative, opt){
            var project = this.project;
            var workArea = this.workArea;
            var projectBreakdownGrid;

            var projectBreakdownTab = dijit.byId('main-project_breakdown');
            if(projectBreakdownTab){
                projectBreakdownGrid = projectBreakdownTab.grid;
            }

            var d = new GeneratorDialog({
                project: project,
                onSuccess: lang.hitch(this, functionName, tenderAlternative, opt),
                onClickErrorNode: function(bill, evt){
                    switch (parseInt(String(bill.type))) {
                        case buildspace.constants.TYPE_BILL:
                            if (bill['bill_status'] == buildspace.constants.BILL_STATUS_OPEN) {
                                workArea.initTab(bill, {
                                    billId: bill.id,
                                    billType: bill.bill_type,
                                    billLayoutSettingId: bill.billLayoutSettingId,
                                    projectBreakdownGrid: projectBreakDownGrid,
                                    rootProject: project
                                });
                            }
                            break;
                        case buildspace.constants.TYPE_SUPPLY_OF_MATERIAL_BILL:
                            workArea.initTab(bill, {
                                billId: bill.id,
                                somBillLayoutSettingId: bill.somBillLayoutSettingId,
                                projectBreakdownGrid: projectBreakDownGrid,
                                rootProject: project
                            });
                            break;
                        case buildspace.constants.TYPE_SCHEDULE_OF_RATE_BILL:
                            workArea.initTab(bill, {
                                billId: bill.id,
                                sorBillLayoutSettingId: bill.sorBillLayoutSettingId,
                                projectBreakdownGrid: projectBreakDownGrid,
                                rootProject: project
                            });
                            break;
                        default:
                            break;
                    }
                }
            });

            d.show();
        },
        _openProjectSummary: function(tenderAlternative){
            var self = this,
                pb = buildspace.dialog.indeterminateProgressBar({
                    title:nls.pleaseWait+'...'
                });

            this.containerDialog.hide();
            
            pb.show().then(function(){
                dojo.xhrGet({
                    url: "tenderAlternativeProjectSummary/"+parseInt(String(tenderAlternative.id)),
                    handleAs: "json"
                }).then(function(resp){
                    pb.hide();
                    buildspace.app.launch({
                        __children: [],
                        icon: "project_summary",
                        id: parseInt(String(self.project.id))+'-project_summary',
                        is_app: true,
                        level: 0,
                        sysname: "ProjectSummary",
                        title: nls.projectSummary
                    },{
                        project: self.project,
                        tenderAlternative: tenderAlternative,
                        projectSummaryData: resp
                    });
                });
            });
        },
        _printProjectSummaryPdf: function(tenderAlternative, opt){
            window.open('TenderAlternativeProjectSummaryPdf/'+parseInt(String(tenderAlternative.id))+'/'+String(this.project._csrf_token)+'/'+opt, '_blank');
            return window.focus();
        },
        _exportToExcel: function(tenderAlternative){
            var d = new Date();
            ExportExcelDialog({
                project: this.project,
                url: 'TenderAlternativeProjectSummaryXls/'+parseInt(String(tenderAlternative.id))+'/'+String(this.project._csrf_token),
                defaultFilename: "Project_Summary"+d.getDate()+(d.getMonth() + 1)+ d.getFullYear()
            }).show();
        },
        openPrintFinalBQ: function(tenderAlternative, opt){
            var withPrice = opt.withPrice;
            var t = withPrice ? nls.withPrice : nls.withoutPrice;
            var d = new PrintFinalBQDialog({
                title: nls.printFinalBQ+' ('+t+') '+buildspace.truncateString(tenderAlternative.title, 40),
                project: this.project,
                tenderAlternative: tenderAlternative,
                withPrice: withPrice
            });

            d.show();
        },
        openPrintRationalizeRate: function(tenderAlternative, opt){
            var printRationalizeRate = opt.printRationalizeRate;
            var title = (printRationalizeRate) ? nls.printRationalizeBQ : nls.printOriginalBQ;
            var dialog = new RationalizeRatePrintBillDialog({
                projectId: parseInt(String(this.project.id)),
                statusId: parseInt(String(this.project.status_id)),
                tenderAlternative: tenderAlternative,
                title: title+' ('+buildspace.truncateString(tenderAlternative.title, 40)+')',
                printRationalizedRate: printRationalizeRate,
                _csrf_token: String(this.project._csrf_token)
            });

            dialog.show();
        }
    });

    var GridContainer = declare('buildspace.apps.TenderAlternative.TenderAlternativeListGridContainer', dijit.layout.BorderContainer, {
        style: "padding:0px;width:100%;height:100%;",
        containerDialog: null,
        project: null,
        workArea: null,
        type: 'printPdf',
        opt: null,
        postCreate: function(){
            this.inherited(arguments);

            var formatter = new GridFormatter();
            var Formatter = {
                rowCountCellFormatter: function (cellValue, rowIdx) {
                    return cellValue > 0 ? cellValue : '';
                },
                treeCellFormatter: function (cellValue, rowIdx) {
                    var item = this.grid.getItem(rowIdx);
                    var level = parseInt(String(item.level)) * 16;
                    cellValue = cellValue == null ? '&nbsp' : cellValue;
                    if (parseInt(String(item.type)) < buildspace.constants.TYPE_BILL) {
                        cellValue = '<b>' + cellValue + '</b>';
                    }
                    cellValue = '<div class="treeNode" style="padding-left:' + level + 'px;"><div class="treeContent">' + cellValue + '&nbsp;</div></div>';
                    return cellValue;
                }
            };

            var storeUrl;
            if(this.type == 'printRationalizeRate'){
                var printRationalizeRate = (this.opt.printRationalizeRate) ? 1 : 0;
                storeUrl = "getRationalizeRateTenderAlternatives/" + parseInt(String(this.project.id))+"/"+parseInt(printRationalizeRate);
            }else{
                storeUrl = "getTenderAlternatives/" + parseInt(String(this.project.id)) + "/1";
            }
            
            var grid = this.grid = new Grid({
                containerDialog: this.containerDialog,
                project: this.project,
                workArea: this.workArea,
                type: this.type,
                opt: this.opt,
                structure: [{
                    name: 'No.',
                    field: 'count',
                    width: '30px',
                    styles: 'text-align:center;',
                    formatter: Formatter.rowCountCellFormatter
                },{
                    name: nls.description, field: 'title', width: 'auto', formatter: Formatter.treeCellFormatter
                },{
                    name: nls.selected,
                    field: 'is_awarded',
                    width:'80px',
                    styles:'text-align:center;',
                    formatter: formatter.awardedCellFormatter
                },{
                    name: nls.overallTotal,
                    field: 'overall_total_after_markup',
                    width: '150px',
                    styles: 'text-align: right;',
                    formatter: formatter.unEditableCurrencyCellFormatter
                }],
                store: new dojo.data.ItemFileWriteStore({
                    clearOnClose: true,
                    url: storeUrl
                })
            });

            this.addChild(grid);

            this.addChild(new Filter({
                region: 'top',
                editableGrid: false,
                grid: grid,
                filterFields: [
                    {'title':nls.description}
                ]
            }));
        },
        save: function(){
            this.grid.save();
        }
    });

    return declare('buildspace.apps.TenderAlternative.TenderAlternativeListDialog', dijit.Dialog, {
        style:"padding:0px;margin:0px;",
        project: null,
        workArea: null,
        type: 'printPdf',
        opt: null,
        title: nls.tenderAlternatives,
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
            if(!parseInt(this.project.has_tender_alternative)){
                //we need to set this because there is case where the project is been passed to this dialog is still holding an old value (has_tender_alternative = false)
                this.project.has_tender_alternative = 1;
            }
            var borderContainer = new dijit.layout.BorderContainer({
                style:"padding:0px;width:820px;height:380px;",
                gutters: false
            });

            var gridContainer = new GridContainer({
                region: "center",
                containerDialog: this,
                project: this.project,
                workArea: this.workArea,
                type: this.type,
                opt: this.opt
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
            borderContainer.addChild(gridContainer);

            return borderContainer;
        }
    });
});