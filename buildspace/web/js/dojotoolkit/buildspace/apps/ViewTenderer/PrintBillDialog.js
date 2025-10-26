define('buildspace/apps/ViewTenderer/PrintBillDialog',[
    'dojo/_base/declare',
    'dojo/_base/lang',
    "dojo/_base/connect",
    "dojo/when",
    "dojo/html",
    "dojo/dom",
    'dojo/keys',
    "dojo/dom-style",
    "dojox/form/manager/_Mixin",
    "dojox/form/manager/_NodeMixin",
    "dojox/form/manager/_ValueMixin",
    "dojox/form/manager/_DisplayMixin",
    "dijit/form/Form",
    "dijit/_WidgetBase",
    "dijit/_OnDijitClickMixin",
    "dijit/_TemplatedMixin",
    "dijit/_WidgetsInTemplateMixin",
    "dijit/form/ValidationTextBox",
    "dijit/Menu",
    'dijit/form/DropDownButton',
    "dijit/DropDownMenu",
    "dijit/MenuItem",
    "dijit/PopupMenuItem",
    'dojo/i18n!buildspace/nls/PrintBillDialog'
], function(
    declare,
    lang,
    connect,
    when,
    html,
    dom,
    keys,
    domStyle,
    _ManagerMixin,
    _ManagerNodeMixin,
    _ManagerValueMixin,
    _ManagerDisplayMixin,
    Form,
    _WidgetBase,
    _OnDijitClickMixin,
    _TemplatedMixin,
    _WidgetsInTemplateMixin,
    ValidationTextBox,
    Menu,
    DropDownButton,
    DropDownMenu,
    MenuItem,
    PopupMenuItem,
    nls){

    var PrintBillGrid = declare('buildspace.apps.ViewTenderer.PrintBillGrid', dojox.grid.EnhancedGrid, {
        projectId: 0,
        region: 'center',
        contractor: null,
        style: "border-top:none;",
        _csrf_token: null,
        printRationalizedRate: null,
        currentModuleName: null,
        canSort: function(inSortInfo){
            return false;
        },
        postCreate: function(){
            var self = this,
            contractor = this.contractor;

            this.inherited(arguments);

            this.on('RowClick', function(e){
                var item = self.getItem(e.rowIndex),
                    colField = e.cell.field;

                if(!self.contractor){
                    switch(parseInt(String(item.type))) {
                        case buildspace.constants.TYPE_SUPPLY_OF_MATERIAL_BILL:
                            if( (colField == 'printWithNotListedItem') || colField == 'printWithoutNotListedItem' ) {
                                window.open('supplyOfMaterialBill/printBill/id/' + item.id, '_blank');
                                return window.focus();
                            }
                            break;
                        case buildspace.constants.TYPE_SCHEDULE_OF_RATE_BILL:
                            if( (colField == 'printWithNotListedItem') || colField == 'printWithoutNotListedItem' ) {
                                window.open('scheduleOfRateBill/printBill/id/' + item.id, '_blank');
                                return window.focus();
                            }
                            break;
                        case buildspace.constants.TYPE_BILL:
                            if (colField == 'printWithoutNotListedItem' && parseInt(String(item.id)) > 0 ){
                                window.open('EstimationBQFinalPdf/'+self.projectId+'/'+item.id+'/'+self._csrf_token+'/0', '_blank');
                                return window.focus();
                            } else if(colField == 'printWithoutNotListedItem' && parseInt(String(item.id)) == 0 ){
                                window.open('EstimationBQSummary/'+self.projectId+'/-1/'+self._csrf_token+'/0', '_blank');
                                return window.focus();
                            } else if(colField == 'printWithNotListedItem' && parseInt(String(item.id)) > 0 ){
                                window.open('EstimationBQFinalPdf/'+self.projectId+'/'+item.id+'/'+self._csrf_token+'/1', '_blank');
                                return window.focus();
                            } else if(colField == 'printWithNotListedItem' && parseInt(String(item.id)) == 0 ){
                                window.open('EstimationBQSummary/'+self.projectId+'/-1/'+self._csrf_token+'/1', '_blank');
                                return window.focus();
                            }
                            break;
                        default:
                        // do nothing
                    }
                }else{
                    switch(parseInt(String(item.type))) {
                        case buildspace.constants.TYPE_SUPPLY_OF_MATERIAL_BILL:
                            if( (colField == 'printWithNotListedItem') || colField == 'printWithoutNotListedItem' ) {
                                window.open('supplyOfMaterialBill/printContractorsRate/bid/' + item.id + '/tcid/' + contractor.tender_company_id + '/pid/' + self.projectId, '_blank');
                                return window.focus();
                            }
                            break;
                        case buildspace.constants.TYPE_SCHEDULE_OF_RATE_BILL:
                            if( (colField == 'printWithNotListedItem') || colField == 'printWithoutNotListedItem' ) {
                                window.open('scheduleOfRateBill/printContractorsRate/bid/' + item.id + '/tcid/' + contractor.tender_company_id + '/pid/' + self.projectId, '_blank');
                                return window.focus();
                            }
                            break;
                        case buildspace.constants.TYPE_BILL:
                            if (colField == 'printWithoutNotListedItem' && parseInt(String(item.id)) > 0) {
                                window.open('ContractorBQFinalPdf/' + self.projectId + '/' + item.id + '/' + contractor.tender_company_id + '/' + self._csrf_token + '/0', '_blank');
                                return window.focus();
                            } else if (colField == 'printWithoutNotListedItem' && parseInt(String(item.id)) == 0) {
                                window.open('ContractorBQSummary/' + self.projectId + '/' + contractor.tender_company_id + '/' + self._csrf_token + '/0', '_blank');
                                return window.focus();
                            } else if (colField == 'printWithNotListedItem' && parseInt(String(item.id)) > 0) {
                                window.open('ContractorBQFinalPdf/' + self.projectId + '/' + item.id + '/' + contractor.tender_company_id + '/' + self._csrf_token + '/1', '_blank');
                                return window.focus();
                            } else if (colField == 'printWithNotListedItem' && parseInt(String(item.id)) == 0) {
                                window.open('ContractorBQSummary/' + self.projectId + '/' + contractor.tender_company_id + '/' + self._csrf_token + '/1', '_blank');
                                return window.focus();
                            }
                            break;
                        default:
                        // do nothing
                    }
                }
            });
        },
        dodblclick: function(e){
            this.onRowDblClick(e);
        }
    });

    var Formatter = declare("buildspace.apps.ViewTenderer.PrintBillGrid.CellFormatter", null, {
        rowCountCellFormatter: function(cellValue, rowIdx, cell){
            return parseInt(rowIdx)+1;
        },
        descriptionCellFormatter: function(cellValue, rowIdx){
            var item = this.grid.getItem(rowIdx);
            return (item && parseInt(String(item.id)) == 0) ? nls.summaryPage : cellValue;
        },
        printCellAddendumWithoutNotListedItemFormatter: function(cellValue, rowIdx){
            var item = this.grid.getItem(rowIdx);

            return (item && parseInt(String(item.id)) >= 0) ? '<a href="javascript:void(0);">'+nls.print+'</a>' : null;
        },
        printCellAddendumWithNotListedItemFormatter: function(cellValue, rowIdx){
            var item = this.grid.getItem(rowIdx);

            return (item && parseInt(String(item.id)) >= 0) ? '<a href="javascript:void(0);">'+nls.print+'</a>' : null;
        }
    });

    var ExportToExcelForm = declare('buildspace.apps.ViewTenderer.ProjectSummary.ExportToExcelForm', [Form,
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
        projectId: null,
        tenderAlternative: null,
        contractor: null,
        _csrf_token: null,
        opt: 'with',
        region: 'center',
        dialogWidget: null,
        style: "outline:none;padding:0px;margin:0px;border:none;",
        baseClass: "buildspace-form",
        startup: function(){
            this.inherited(arguments);
            var d = new Date();
            this.setFormValues({filename: "Project_Summary"+d.getDate()+(d.getMonth() + 1)+ d.getFullYear()});
        },
        submit: function(){
            var values = dojo.formToObject(this.id);
            if(this.validate()){
                var filename = values.filename.replace(/ /g, '_');
                var tenderAlternativeId = (this.tenderAlternative) ? parseInt(String(this.tenderAlternative.id)) : -1;

                window.open('ContractorProjectSummaryXls/'+this.contractor.tender_company_id+'/'+this.projectId+'/'+tenderAlternativeId+'/'+this._csrf_token+'/'+this.opt+'/'+filename, '_self');

                if(this.dialogWidget){
                    this.dialogWidget.hide();
                }
            }
        }
    });

    var ExportExcelDialog = declare('buildspace.apps.ViewTenderer.ProjectSummary.ExportExcelDialog', dijit.Dialog, {
        style:"padding:0px;margin:0px;",
        title: nls.exportToExcel,
        projectId: null,
        tenderAlternative: null,
        contractor: null,
        _csrf_token: null,
        opt: 'with',
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
                    projectId: this.projectId,
                    tenderAlternative: this.tenderAlternative,
                    contractor: this.contractor,
                    _csrf_token: this._csrf_token,
                    opt: this.opt,
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

    return declare('buildspace.apps.ViewTenderer.PrintBillGridDialog', dijit.Dialog, {
        style:"padding:0px;margin:0px;",
        title: nls.printBQ,
        contractor: false,
        projectId: null,
        tenderAlternative: null,
        bqCSRFToken: null,
        printRationalizedRate: false,
        _csrf_token: null,
        currentModuleName: 'tendering',
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
            var self = this,
                borderContainer = new dijit.layout.BorderContainer({
                    style:"padding:0px;width:800px;height:350px;",
                    gutters: false
                }),
                toolbar = new dijit.Toolbar({
                    region: "top",
                    style: "outline:none!important;padding:2px;overflow:hidden;border:0px;"
                });

            toolbar.addChild(
                new dijit.form.Button({
                    label: nls.close,
                    iconClass: "icon-16-container icon-16-close",
                    style:"outline:none!important;",
                    onClick: dojo.hitch(this, 'hide')
                })
            );

            if(this.contractor){
                var summaryMenu = new DropDownMenu({ style: "display: none;"}),
                    menuOptions = [{
                        opt:'printToPdf',
                        iconClass: 'icon-16-print',
                        subMenus: [{
                            id: 'with'
                        }, {
                            id: 'without'
                        }]
                    }, {
                        opt: 'exportToExcel',
                        iconClass: 'icon-16-spreadsheet',
                        subMenus: [{
                            id: 'with'
                        }, {
                            id: 'without'
                        }]
                    }];

                dojo.forEach(menuOptions, function(opt){
                    var pSubMenu = new Menu();
                    dojo.forEach(opt.subMenus, function(subItem){
                        pSubMenu.addChild(new MenuItem({
                            label: nls[subItem.id]+" "+nls.notListedItem,
                            onClick: dojo.hitch(self, opt.opt, subItem.id)
                        }));
                    });

                    summaryMenu.addChild(PopupMenuItem({
                        label: nls[opt.opt],
                        iconClass: "icon-16-container "+opt.iconClass,
                        popup: pSubMenu
                    }));
                });

                toolbar.addChild(new dijit.ToolbarSeparator());
                toolbar.addChild(
                    new DropDownButton({
                        label: nls.projectSummary,
                        iconClass: "icon-16-container icon-16-list",
                        style:"outline:none!important;",
                        dropDown: summaryMenu
                    })
                );
            }

            var projectPrintListUrl = "printBQ/getProjectPrintList/id/"+this.projectId+"/no_summary/true";

            if(this.tenderAlternative){
                projectPrintListUrl += "/tid/"+parseInt(String(this.tenderAlternative.id))
            }

            var formatter = new Formatter(),
                store = dojo.data.ItemFileWriteStore({
                    url: projectPrintListUrl
                });

            borderContainer.addChild(toolbar);
            borderContainer.addChild(PrintBillGrid({
                projectId: this.projectId,
                contractor: this.contractor,
                _csrf_token: this._csrf_token,
                store: store,
                structure: [
                    {name: 'No.', field: 'id', width:'30px', styles:'text-align:center;', formatter: formatter.rowCountCellFormatter },
                    {name: nls.description, field: 'title', width:'auto', formatter: formatter.descriptionCellFormatter },
                    {name: nls.numberOfItems, field: 'item_count', width:'120px', styles:'text-align:center;' },
                    {name: nls.w_o + nls.notListedItem , field: 'printWithoutNotListedItem', width:'120px', styles:'text-align:center;', formatter: formatter.printCellAddendumWithoutNotListedItemFormatter },
                    {name: nls.w + nls.notListedItem , field: 'printWithNotListedItem', width:'120px', styles:'text-align:center;', formatter: formatter.printCellAddendumWithNotListedItemFormatter }
                ],
                currentModuleName: this.currentModuleName
            }));

            return borderContainer;
        },
        printToPdf: function(opt){
            var tenderAlternativeId = (this.tenderAlternative) ? parseInt(String(this.tenderAlternative.id)) : -1;

            window.open('ContractorProjectSummaryPdf/'+this.contractor.tender_company_id+'/'+this.projectId+'/'+tenderAlternativeId+'/'+this._csrf_token+'/'+opt, '_blank');
            return window.focus();
        },
        exportToExcel: function(opt){
            ExportExcelDialog({
                projectId: this.projectId,
                tenderAlternative: this.tenderAlternative,
                contractor: this.contractor,
                _csrf_token: this._csrf_token,
                opt: opt
            }).show();
        }
    });
});