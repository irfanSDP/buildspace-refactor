define('buildspace/apps/Tendering/ProjectBreakdown',[
    'dojo/_base/declare',
    "dojo/aspect",
    "dojo/keys",
    "dojo/dom-style",
    'dojo/_base/connect',
    'dojox/grid/EnhancedGrid',
    "dijit/TooltipDialog",
    "dijit/popup",
    'buildspace/widget/grid/cells/Formatter',
    './RecalculateDialog',
    "../ProjectBuilder/exportItemDialog",
    '../ProjectBuilder/exportSupplyOfMaterialItemDialog',
    '../ProjectBuilder/ExportScheduleOfRateBillDialog',
    './LogDialog',
    "buildspace/apps/ProjectBuilder/ExportProjectBackupDialog",
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
    "dijit/Menu",
    'dijit/form/DropDownButton',
    "dijit/DropDownMenu",
    "dijit/MenuItem",
    "dijit/PopupMenuItem",
    'buildspace/apps/TenderAlternative/TenderAlternativeFormDialog',
    'buildspace/apps/TenderAlternative/TenderAlternativeListDialog',
    'buildspace/apps/PageGenerator/GeneratorDialog',
    'buildspace/apps/Tendering/PrintFinalBQDialog',
    './BillForm',
    'dojo/i18n!buildspace/nls/Tendering'
], function(declare, aspect, keys, domStyle, connect, EnhancedGrid, TooltipDialog, popup, GridFormatter, RecalculateDialog, ExportItemDialog, ExportSupplyOfMaterialItemDialog, ExportScheduleOfRateBillDialog, LogDialog, ExportProjectBackupDialog, Form, _ManagerMixin, _ManagerNodeMixin, _ManagerValueMixin, _ManagerDisplayMixin, _WidgetBase, _OnDijitClickMixin, _TemplatedMixin, _WidgetsInTemplateMixin, ValidationTextBox, Menu, DropDownButton, DropDownMenu, MenuItem, PopupMenuItem, TenderAlternativeFormDialog, TenderAlternativeListDialog, GeneratorDialog, PrintFinalBQDialog, BillForm, nls){

    var Grid = declare('buildspace.apps.Tendering.ProjectBreakdownGrid', EnhancedGrid, {
        rootProject: null,
        workArea: null,
        style: "border:none;",
        region: 'center',
        keepSelection: true,
        rowSelector: '0px',
        aspectHandles: [],
        currencySetting: buildspace.currencyAbbreviation,
        canSort: function(inSortInfo){
            return false;
        },
        postCreate: function(){
            this.inherited(arguments);
            var self = this, rootProject = this.rootProject;
            var tooltipDialog = null;
            const defaultButtonsToEnable = ['AddBill', 'AddLevel'];

            this.on("RowClick", function(e){
                if(e.cell){
                    var colField = e.cell.field,
                        rowIndex = e.rowIndex,
                        _item = this.getItem(rowIndex),
                        buttonsToEnable = defaultButtonsToEnable.slice();

                    if(_item && parseInt(String(_item.id)) > 0) {
                        switch (parseInt(String(_item.type))) {
                            case buildspace.apps.Tendering.ProjectStructureConstants.TYPE_BILL:
                                let buttons = ['Backup', 'ExportItemFromBill', ...buttonsToEnable];

                                if (_item.hasOwnProperty('can_delete') && _item.can_delete[0]) {
                                    buttons = [...buttons, 'Delete'];
                                }

                                buttonsToEnable = [...buttons];
                                
                                break;
                            case buildspace.apps.Tendering.ProjectStructureConstants.TYPE_SUPPLY_OF_MATERIAL_BILL:
                                buttonsToEnable = ['ExportItemFromBill', ...buttonsToEnable];
                                break;
                            case buildspace.apps.Tendering.ProjectStructureConstants.TYPE_SCHEDULE_OF_RATE_BILL:
                                buttonsToEnable = ['ExportItemFromBill', ...buttonsToEnable];
                                break;
                            default:
                                break;
                        }
                    }

                    if(_item && parseInt(String(_item.id)) > 0 &&
                        parseInt(String(_item.type)) == buildspace.apps.Tendering.ProjectStructureConstants.TYPE_BILL &&
                        ( buildspace.apps.Tendering.isRecalculateBillStatus(parseInt(String(_item['bill_status']))) )){

                        if(colField == 'recalculate') {
                            var dialog = new RecalculateDialog( {
                                title: nls.recalculate + ' ' + buildspace.truncateString( _item.title, 70 ),
                                rootProject: rootProject,
                                bill: _item,
                                projectBreakDownGrid: self
                            } );
                            dialog.show();
                        }

                        // Remove export buttons.
                        var buttonsToRemove = ['Backup', 'ExportItemFromBill'];
                        buttonsToRemove.forEach( function(element) {
                            var index = buttonsToEnable.indexOf( element );
                            if( index > -1 ) buttonsToEnable.splice( index, 1 );
                        } );
                    }

                    this.disableToolbarButtons(true, buttonsToEnable);
                }
            });

            var aspectHandle = aspect.after(this, '_onFetchComplete', function(){
                // Enable/disable export rate.
                var disabled = self.store._arrayOfAllItems.some(function(item){
                    // Has bills that need to be recalculated.
                    if('bill_status' in item) {
                        return buildspace.apps.Tendering.isRecalculateBillStatus(parseInt(String(item['bill_status'])));
                    }
                    return false;
                });

                dijit.byId('exportRates-button')._setDisabledAttr(disabled);
            });
            this.aspectHandles.push(aspectHandle);

            this._connects.push(connect.connect(this, 'onCellMouseOver', function(e) {
                var colField = e.cell.field,
                    rowIndex = e.rowIndex,
                    item = this.getItem(rowIndex);
                
                // will show tooltip for formula, if available
                if ((!item || (!item.hasOwnProperty('addendum_version')) || colField != 'addendum_version' || typeof item['addendum_version'] === 'undefined' || ! parseInt(String(item.addendum_version)))
                && (!item || (!item.hasOwnProperty('tender_alternative_count')) || colField != 'tender_alternative_count' || typeof item['tender_alternative_count'] === 'undefined' || ! parseInt(String(item.tender_alternative_count)))) {
                    return;
                }

                if(tooltipDialog === null) {
                    // Call the asynchronous xhrGet
                    var url;
                    switch(colField){
                        case 'addendum_version':
                            url = "tendering/getAddendumInfoByBill/id/"+String(item.id);
                            break;
                        case 'tender_alternative_count':
                            url = "getTenderAlternativeInfoByBill/"+String(item.id);
                            break;
                    }

                    var deferred = dojo.xhrGet({
                        url: url,
                        handleAs: "json",
                        sync:true,
                        preventCache: true
                    });
                    
                    // Now add the callbacks
                    deferred.then(function(data){
                        var content;
                        if(data.length){
                            switch(colField){
                                case 'addendum_version':
                                    content = '<table class="buildspace-table"><thead><tr>'
                                    + '<th class="gridCell" style="text-align:center;padding:2px;">'+nls.version+'</th>'
                                    + '<th class="gridCell" style="text-align:center;padding:2px;">'+nls.revision+'</th>'
                                    + '</tr><tbody>';
                                    for (var i = 0; i < data.length; i++){
                                        content += '<tr><td class="gridCell" style="text-align:center;">'+data[i].version + '</td><td class="gridCell" style="text-align:center;padding-left:4px;padding-right:4px;">'+ data[i].revision +'</td></tr>';
                                    }
                                    content +='</tbody></table>';
                                    break;
                                case 'tender_alternative_count':
                                    url = "getTenderAlternativeInfoByBill/"+String(item.id);
                                    content = '<table class="buildspace-table"><thead><tr>'
                                    + '<th class="gridCell" style="text-align:center;padding:2px;">'+nls.title+'</th>'
                                    + '<th class="gridCell" style="text-align:center;padding:2px;">'+nls.revision+'</th>'
                                    + '</tr><tbody>';
                                    for (var i = 0; i < data.length; i++){
                                        content += '<tr><td class="gridCell" style="text-align:center;">'+data[i].title + '</td><td class="gridCell" style="text-align:center;padding-left:4px;padding-right:4px;">'+ data[i].revision +'</td></tr>';
                                    }
                                    content +='</tbody></table>';
                                    break;
                            }
                            
                            tooltipDialog = new TooltipDialog({
                                content: content,
                                onMouseLeave: function() {
                                    popup.close(tooltipDialog);
                                }
                            });
                            popup.open({
                                popup: tooltipDialog,
                                around: e.cellNode
                            });
                        }
                    });
                }
            }));

            this._connects.push(connect.connect(this, 'onCellMouseOut', function() {
                if(tooltipDialog !== null){
                    popup.close(tooltipDialog);
                    tooltipDialog = null;
                }
            }));

            this._connects.push(connect.connect(this, 'onStartEdit', function() {
                if(tooltipDialog !== null){
                    popup.close(tooltipDialog);
                    tooltipDialog = null;
                }
            }));
        },
        dodblclick: function(e){
            this.onRowDblClick(e);
        },
        disableButtonsAfterPublish: function (){
            //do something
        },
        onRowDblClick: function(e){
            var self = this, item = self.getItem(e.rowIndex);
            switch(parseInt(String(item.type))){
                case buildspace.apps.Tendering.ProjectStructureConstants.TYPE_BILL:
                    if(parseInt(String(item['bill_status'])) == buildspace.apps.Tendering.ProjectStructureConstants.BILL_STATUS_OPEN){
                        this.workArea.initTab(item, {
                            bill: item,
                            billLayoutSettingId: item.billLayoutSettingId,
                            projectBreakdownGrid: this,
                            rootProject: this.rootProject
                        });
                    }
                    break;
                case buildspace.apps.Tendering.ProjectStructureConstants.TYPE_SUPPLY_OF_MATERIAL_BILL:
                    this.workArea.initTab(item, {
                        billId: parseInt(String(item.id)),
                        projectBreakdownGrid: this,
                        rootProject: this.rootProject
                    });
                    break;
                case buildspace.apps.Tendering.ProjectStructureConstants.TYPE_SCHEDULE_OF_RATE_BILL:
                    this.workArea.initTab(item, {
                        billId: parseInt(String(item.id)),
                        projectBreakdownGrid: this,
                        rootProject: this.rootProject
                    });
                    break;
                default:
                    break;
            }
        },
        deleteRow: function (item) {
            var self = this,
                pb = buildspace.dialog.indeterminateProgressBar({
                    title: nls.deleting + '. ' + nls.pleaseWait + '...'
                });

            var onYes = function () {
                pb.show().then(function(){
                    dojo.xhrPost({
                        url: 'projectBuilder/projectStructureDelete',
                        content: {id: item.id, _csrf_token: item._csrf_token},
                        handleAs: 'json',
                        load: function (resp) {
                            if (resp.success) {
                                var items = resp.items;
                                for (var i = 0, len = items.length; i < len; ++i) {
                                    self.store.fetchItemByIdentity({
                                        'identity': items[i].id, onItem: function (itm) {
                                            var tac = self.workArea.getChildren();
                                            for (var i in tac) {
                                                if (typeof tac[i].pane_info != "object") continue;
                                                if (tac[i].pane_info.id == parseInt(String(itm.id)) + '-form_' + parseInt(String(itm.type))) {
                                                    self.workArea.removeChild(tac[i]);
                                                    break;
                                                }
                                            }
                                        }
                                    });
                                }
                                items.length = 0;
                                self.reload();
                            }
                            pb.hide();
                            self.selection.clear();
                            self.disableToolbarButtons(true);
                        },
                        error: function (error) {
                            pb.hide();
                            self.selection.clear();
                            self.disableToolbarButtons(true);
                        }
                    });
                });
            };

            var content;

            if (parseInt(String(item.type)) == buildspace.apps.Tendering.ProjectStructureConstants.TYPE_BILL || parseInt(String(item.type)) == buildspace.apps.Tendering.ProjectStructureConstants.TYPE_SUPPLY_OF_MATERIAL_BILL || parseInt(String(item.type)) == buildspace.apps.Tendering.ProjectStructureConstants.TYPE_SCHEDULE_OF_RATE_BILL) {
                content = '<div>' + nls.deleteBillAndAllData + '</div>';
            } else if (parseInt(String(item.type)) == buildspace.apps.Tendering.ProjectStructureConstants.TYPE_LEVEL) {
                content = '<div>' + nls.deleteLevelAndAllData + '</div>';
            }

            buildspace.dialog.confirm(nls.confirmation, content, 80, 300, onYes);
        },
        openBillForm: function (rowIndex) {
            var item = this.getItem(rowIndex);

            if (String(item.id) == buildspace.constants.GRID_LAST_ROW) {
                item = this.getItem(rowIndex - 1);
            }

            var f = BillForm({
                title: nls.addBill,
                projectBreakdownGrid: this,
                parentId: parseInt(String(item.id))
            });

            f.startup();
            f.show();
        },
        disableToolbarButtons: function(isDisable, buttonsToEnable){

            if(isDisable && buttonsToEnable instanceof Array ){
                var _this = this,
                    BackupBtn = dijit.byId(this.rootProject.id+'BackupRow-button'),
                    ExportItemFromBillBtn = dijit.byId(this.rootProject.id + 'ExportItemFromBillRow-button');
                    DeleteBillButton = dijit.byId(this.rootProject.id + 'DeleteRow-button');

                if(BackupBtn)
                    BackupBtn._setDisabledAttr(isDisable);

                if(ExportItemFromBillBtn)
                    ExportItemFromBillBtn._setDisabledAttr(isDisable);

                if (DeleteBillButton)
                    DeleteBillButton._setDisabledAttr(isDisable);

                dojo.forEach(buttonsToEnable, function(label){
                    var btn = dijit.byId(_this.rootProject.id+label+'Row-button');

                    if (btn)
                        btn._setDisabledAttr(false);
                });
            }
        },
        reload: function(){
            this.store.close();
            this._refresh();
        },
        destroy: function(){
            this.inherited(arguments);

            this.aspectHandles.forEach(function(handle){
                handle.remove();
            });
            this.aspectHandles = [];

            dojo.forEach(this._connects, connect.disconnect);
            delete this._connects;
        }
    });

    var Formatter = {
        rowCountCellFormatter: function(cellValue, rowIdx){
            return cellValue > 0 ? cellValue : '';
        },
        treeCellFormatter: function(cellValue, rowIdx){
            var item = this.grid.getItem(rowIdx);
            var level = item.level*16;
            cellValue = cellValue == null ? '&nbsp': cellValue;
            if(item && parseInt(String(item.type)) < buildspace.apps.Tendering.ProjectStructureConstants.TYPE_BILL){
                cellValue =  '<b>'+cellValue+'</b>';
            }

            cellValue = '<div class="treeNode" style="padding-left:'+level+'px;"><div class="treeContent">'+cellValue+'&nbsp;</div></div>';
            return cellValue;
        }
    };

    var ExportToExcelForm = declare('buildspace.apps.Tendering.ProjectSummary.ExportToExcelForm', [Form,
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
                window.open('ProjectSummaryXls/'+this.project.id+'/'+this.project._csrf_token+'/'+filename, '_self');

                if(this.dialogWidget){
                    this.dialogWidget.hide();
                }
            }
        }
    });

    var ExportExcelDialog = declare('buildspace.apps.Tendering.ProjectSummary.ExportExcelDialog', dijit.Dialog, {
        style:"padding:0px;margin:0px;",
        title: nls.exportToExcel,
        project: null,
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

    var ExportEstimateRatesForm = declare('buildspace.apps.ProjectBuilder.ExportEstimateRatesForm', [Form,
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

    var ExportEstimateRatesDialog = declare('buildspace.apps.Tendering.ExportEstimateRatesDialog', dijit.Dialog, {
        style:"padding:0px;margin:0;",
        title: nls.exportEstimateRates,
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
                form = ExportEstimateRatesForm({
                    project: this.project,
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

    return declare('buildspace.apps.Tendering.ProjectBreakdown', dijit.layout.BorderContainer, {
        region: "center",
        style:"padding:0px;width:100%;height:100%;",
        gutters: false,
        rootProject: null,
        workArea: null,
        grid: null,
        postCreate: function(){
            this.inherited(arguments);

            var formatter = new GridFormatter(),
                currencySetting = (buildspace.billCurrencyAbbreviation) ? buildspace.billCurrencyAbbreviation : buildspace.currencyAbbreviation,
                self = this;

            var structure = [
                {name: 'No.', field: 'count', width:'30px', styles:'text-align:center;', formatter: Formatter.rowCountCellFormatter },
                {name: nls.description, field: 'title', width:'auto', formatter: Formatter.treeCellFormatter}
            ];

            if(parseInt(String(this.rootProject.has_tender_alternative))){
                structure.push({name: nls.tenderAlternative, field: 'tender_alternative_count', styles:'text-align: center;', width:'68px', formatter: formatter.tenderAlternativeInfoCellFormatter, noresize: true});
            }

            if(parseInt(String(this.rootProject.has_addendum))){
                structure.push({name: nls.addendum, field: 'addendum_version', styles:'text-align: center;', width:'68px', formatter: formatter.addendumInfoCellFormatter, noresize: true});
            }

            var otherColumns = [
                {name: nls.originalAmount, field: 'original_total', width:'150px', styles:'text-align: right;', formatter: formatter.unEditableCurrencyCellFormatter},
                {name: nls.total+' '+nls.markup+' (%)', field: 'original_total', width:'100px', styles:'text-align: right;', formatter: formatter.elementTotalMarkupPercentageCellFormatter},
                {name: nls.total+' '+nls.markup+' ('+currencySetting+')', field: 'original_total', width:'120px', styles:'text-align: right;', formatter: formatter.elementTotalMarkupAmountCellFormatter },
                {name: nls.overallTotal, field: 'overall_total_after_markup', width:'150px', styles:'text-align: right;', formatter: formatter.unEditableCurrencyCellFormatter},
                {name: '% '+nls.project, field: 'overall_total_after_markup', width:'80px', styles:'text-align: center;', formatter: formatter.projectBreakdownJobPercentageCellFormatter},
                {name: nls.recalculate, field: 'recalculate', width:'80px', styles:'text-align: center;', formatter: formatter.recalculateBillCellFormatter}
            ];

            dojo.forEach(otherColumns,function(column){
                structure.push(column);
            });

            var grid = this.grid = Grid({
                rootProject: this.rootProject,
                workArea: this.workArea,
                currencySetting: currencySetting,
                structure: structure,
                store: new dojo.data.ItemFileWriteStore({
                    clearOnClose:true,
                    url:"tendering/getProjectBreakdown/id/"+this.rootProject.id
                })
            }),
            toolbar = new dijit.Toolbar({region:"top", style:"outline:none!important;border:none;padding:2px;width:100%;"});

            toolbar.addChild(
                new dijit.form.Button({
                    label: nls.importRates,
                    id: self.rootProject.id+'ImportRatesRow-button',
                    iconClass: "icon-16-container icon-16-container icon-16-import",
                    disabled: false,
                    onClick: function(){
                        buildspace.app.launch({
                            __children: [],
                            icon: "project_import_rates",
                            id: self.rootProject.id+'-project_import_rates',
                            is_app: true,
                            level: 0,
                            sysname: "ProjectImportRates",
                            title: nls.importRates
                        },{
                            type: buildspace.constants.STATUS_TENDERING,
                            project: self.rootProject
                        });
                    }
                })
            );

            switch(parseInt(String(self.rootProject.tender_type_id))){
                case buildspace.constants.TENDER_TYPE_TENDERED:
                    if(parseInt(String(self.rootProject.status_id)) == buildspace.constants.STATUS_TENDERING){
                        toolbar.addChild(new dijit.ToolbarSeparator());
                        toolbar.addChild(
                            new dijit.form.Button({
                                id: self.rootProject.id + 'AddTenderAlternative-button',
                                label: nls.addTenderAlternative,
                                iconClass: "icon-16-container icon-16-add",
                                style: "outline:none!important;",
                                onClick: function () {
                                    var pb = buildspace.dialog.indeterminateProgressBar({
                                        title: nls.pleaseWait+'...'
                                    });

                                    pb.show().then(function(){
                                        dojo.xhrGet({
                                            url: "getProjectRevisionInfo/"+String(self.rootProject.id),
                                            handleAs: "json",
                                            sync:true,
                                            preventCache: true
                                        }).then(function(rev){
                                            pb.hide();
                                            if(!rev.locked_status && rev.current_selected_revision){
                                                var d = new TenderAlternativeFormDialog({
                                                    tenderAlternativeId: -1,
                                                    project: self.rootProject,
                                                    projectBreakdownGrid: grid
                                                });
            
                                                d.show();
                                            }else{
                                                buildspace.dialog.alert(nls.tenderAlternativeAlert, nls.projectRevisionAlertDesc, 100, 300);
                                            }
                                        });
                                    });
                                }
                            })
                        );
                    }
                    
                    toolbar.addChild(new dijit.ToolbarSeparator());
                    toolbar.addChild(
                        new dijit.form.Button({
                            id: self.rootProject.id+'ViewTendererRow-button',
                            label: nls.viewTenderers,
                            disabled: !parseInt(String(self.rootProject.show_contractor_rates)),
                            iconClass: "icon-16-container icon-16-messenger",
                            onClick: dojo.hitch(self, "_viewTenderers")
                        })
                    );

                    break;
                case buildspace.constants.TENDER_TYPE_PARTICIPATED:
                    toolbar.addChild(new dijit.ToolbarSeparator());
                    toolbar.addChild(
                        new dijit.form.Button({
                            id: self.rootProject.id+'RationalizeRatesRow-button',
                            label: nls.compareRationalizedRates,
                            iconClass: "icon-16-container icon-16-list",
                            onClick: function(){
                                buildspace.app.launch({
                                    __children: [],
                                    icon: "rationalize_rate",
                                    id: self.rootProject.id+'-rationalize_rate',
                                    is_app: true,
                                    level: 0,
                                    sysname: "RationalizeRate",
                                    title: nls.compareRationalizedRates
                                },{
                                    type: buildspace.constants.STATUS_IMPORT,
                                    project: self.rootProject
                                });
                            }
                        })
                    );
                    break;
                default:
                    break;
            }

            var summaryMenu = new DropDownMenu({ style: "display: none;"}),
                summaryMenuItems = [{
                    id:'printToPdf',
                    sub_menus: [{
                        id: 'withPrice'
                    },{
                        id: 'withoutPrice'
                    }]
                }, {
                    id:'exportToExcel',
                    sub_menus: []
                }],
                menuItem;

            dojo.forEach(summaryMenuItems, function(item){
                if(item.sub_menus.length > 0){
                    var pSubMenu = new Menu();
                    dojo.forEach(item.sub_menus, function(subItem){
                        pSubMenu.addChild(new MenuItem({
                            label: nls[subItem.id],
                            onClick: dojo.hitch(self, "printToPdf", subItem.id)
                        }));
                    });

                    menuItem = new PopupMenuItem({
                        label: nls[item.id],
                        iconClass: "icon-16-container icon-16-print",
                        popup: pSubMenu
                    });
                }else{
                    menuItem = new MenuItem({
                        label: nls[item.id],
                        iconClass: "icon-16-container icon-16-spreadsheet",
                        onClick: dojo.hitch(self, item.id)
                    });
                }

                summaryMenu.addChild(menuItem);
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

            var printFinalBillOptions = ['withPrice', 'withoutPrice'];

            var printFinalBillMenu = new DropDownMenu({
                style: "display: none;"
            });

            dojo.forEach(printFinalBillOptions, function(opt) {
                var withPrice =  ( opt === 'withPrice' );

                return printFinalBillMenu.addChild(new MenuItem({
                    label: nls[opt],
                    onClick: dojo.hitch(self, '_printFinalBQ', withPrice)
                }));
            });

            toolbar.addChild(new dijit.ToolbarSeparator());
            toolbar.addChild(new DropDownButton({
                label: nls.printFinalBQ,
                iconClass: "icon-16-container icon-16-print",
                style:"outline:none!important;",
                dropDown: printFinalBillMenu
            }));
            
            toolbar.addChild(new dijit.ToolbarSeparator());
            toolbar.addChild(
                new dijit.form.Button({
                    id: self.rootProject.id+'ExportItemFromBillRow-button',
                    label: nls.exportBill,
                    iconClass: "icon-16-container icon-16-export",
                    disabled: grid.selection.selectedIndex <= -1,
                    onClick: function (e) {
                        if (grid.selection.selectedIndex > -1) {
                            var item = grid.getItem(grid.selection.selectedIndex);
                            var d = new GeneratorDialog({
                                project: self.rootProject,
                                bill: item,
                                onSuccess: dojo.hitch(self, "_openExportExcelBillDialog", item),
                                onClickErrorNode: function(bill, evt){
                                    switch (parseInt(String(bill.type))) {
                                        case buildspace.apps.ProjectBuilder.ProjectStructureConstants.TYPE_BILL:
                                            if (parseInt(String(bill['bill_status'])) == buildspace.apps.ProjectBuilder.ProjectStructureConstants.BILL_STATUS_OPEN) {
                                                self.workArea.initTab(bill, {
                                                    billId: parseInt(String(bill.id)),
                                                    billType: parseInt(String(bill.bill_type)),
                                                    billLayoutSettingId: bill.billLayoutSettingId,
                                                    projectBreakdownGrid: grid,
                                                    rootProject: self.rootProject
                                                });
                                            }
                                            break;
                                        case buildspace.apps.ProjectBuilder.ProjectStructureConstants.TYPE_SUPPLY_OF_MATERIAL_BILL:
                                            self.workArea.initTab(bill, {
                                                billId: parseInt(String(bill.id)),
                                                somBillLayoutSettingId: bill.somBillLayoutSettingId,
                                                projectBreakdownGrid: grid,
                                                rootProject: self.rootProject
                                            });
                                            break;
                                        case buildspace.apps.ProjectBuilder.ProjectStructureConstants.TYPE_SCHEDULE_OF_RATE_BILL:
                                            self.workArea.initTab(bill, {
                                                billId: parseInt(String(bill.id)),
                                                sorBillLayoutSettingId: bill.sorBillLayoutSettingId,
                                                projectBreakdownGrid: grid,
                                                rootProject: self.rootProject
                                            });
                                            break;
                                        default:
                                            break;
                                    }
                                }
                            });

                            d.show();
                        }
                    }
                })
            );

            toolbar.addChild(new dijit.ToolbarSeparator());
            toolbar.addChild(
                new dijit.form.Button({
                    id: self.rootProject.id+'BackupRow-button',
                    label: nls.exportBackup,
                    iconClass: "icon-16-container icon-16-export",
                    disabled: grid.selection.selectedIndex > -1 ? false : true,
                    onClick: function(e){
                        if(grid.selection.selectedIndex > -1){
                            var _item = grid.getItem(grid.selection.selectedIndex);
                            new ExportProjectBackupDialog({
                                bill: _item,
                                project: self.rootProject,
                                exportUrl: 'projectBackup/index'
                            }).show();
                        }
                    }
                })
            );

            toolbar.addChild(new dijit.ToolbarSeparator());
            toolbar.addChild(
                new dijit.form.Button({
                    label: nls.exportEstimateRates,
                    id: 'exportEstimateRates-button',
                    iconClass: "icon-16-container icon-16-export",
                    onClick: function(){
                        var dialog = ExportEstimateRatesDialog({
                            project: self.rootProject,
                            exportUrl: 'exportEstimateRates/exportEstimateRatesByProject'
                        });

                        dialog.show();
                    }
                })
            );

            const [canAddAddendumBills] = this.rootProject.can_add_addendum_bills;

            const addBillDropDownMenu = new DropDownMenu({ style: "display: none;" });

            addBillDropDownMenu.addChild(new MenuItem({
                label: nls.normalBill,
                onClick: function (e) {
                    if (grid.selection.selectedIndex > -1) {
                        grid.openBillForm(grid.selection.selectedIndex);
                    }
                }
            }));

            /**
             * KIV
             */
            // addBillDropDownMenu.addChild(new MenuItem({
            //     label: nls.supplyOfMaterialBill,
            //     onClick: function (e) {
            //         if (grid.selection.selectedIndex > -1) {
            //             grid.openSupplyOfMaterialForm(grid.selection.selectedIndex);
            //         }
            //     }
            // }));

            /**
             * KIV
             */
            // addBillDropDownMenu.addChild(new MenuItem({
            //     label: nls.scheduleOfRateBill,
            //     onClick: function (e) {
            //         if (grid.selection.selectedIndex > -1) {
            //             grid.openScheduleOfRateBillForm(grid.selection.selectedIndex);
            //         }
            //     }
            // }));

            toolbar.addChild(new DropDownButton({
                id: self.rootProject.id + 'AddBillRow-button',
                label: nls.addBill,
                iconClass: "icon-16-container icon-16-add",
                dropDown: addBillDropDownMenu,
                disabled: true,
                style: Object.entries({
                    outline: 'none!important',
                    ...(!canAddAddendumBills && { display: 'none' }),
                }).map(([key, value]) => `${key}:${value}`).join(';'),
            }));

            toolbar.addChild(
                new dijit.form.Button({
                    id: self.rootProject.id + 'DeleteRow-button',
                    label: nls.delete,
                    iconClass: "icon-16-container icon-16-delete",
                    disabled: true,
                    style: Object.entries({
                        outline: 'none!important',
                        ...(!canAddAddendumBills && { display: 'none' }),
                    }).map(([key, value]) => `${key}:${value}`).join(';'),
                    onClick: function () {
                        if (grid.selection.selectedIndex > -1) {
                            var item = grid.getItem(grid.selection.selectedIndex);
                            if (item && parseInt(String(item.id)) > 0 && (parseInt(String(item.type)) == buildspace.apps.Tendering.ProjectStructureConstants.TYPE_BILL || parseInt(String(item.type)) == buildspace.apps.Tendering.ProjectStructureConstants.TYPE_SUPPLY_OF_MATERIAL_BILL || parseInt(String(item.type)) == buildspace.apps.Tendering.ProjectStructureConstants.TYPE_SCHEDULE_OF_RATE_BILL || parseInt(String(item.type)) == buildspace.apps.Tendering.ProjectStructureConstants.TYPE_LEVEL)) {
                                grid.deleteRow(item);
                            }
                        }
                    }
                })
            );

            toolbar.addChild(new dijit.ToolbarSeparator());
            toolbar.addChild(
                new dijit.form.Button({
                    id: self.rootProject.id+'LogRateRow-button',
                    label: 'Log',
                    iconClass: "icon-16-container icon-16-diary",
                    onClick: function(e){
                        var projectLogsQuery = dojo.xhrGet({
                            url: "tendering/getProjectRateLogCount",
                            handleAs: "json",
                            content: {
                                pid: self.rootProject.id
                            }
                        });

                        projectLogsQuery.then(function(ret) {
                            var d = new LogDialog({
                                project: self.rootProject,
                                logData: ret
                            });

                            d.show();
                        });
                    }
                })
            );

            toolbar.addChild(new dijit.ToolbarSeparator());
            toolbar.addChild(
                new dijit.form.Button({
                    id: self.rootProject.id+'ReloadGridRow-button',
                    label: nls.reload,
                    iconClass: "icon-16-container icon-16-reload",
                    onClick: function(e){
                        grid.reload();
                    }
                })
            );

            this.addChild(toolbar);
            this.addChild(grid);
        },
        _printFinalBQ: function(withPrice){

            var self = this;
            var pb = buildspace.dialog.indeterminateProgressBar({
                title: nls.pleaseWait + '...'
            });
            var checkTenderAlternativeXhr = dojo.xhrGet({
                url: "getTenderAlternatives/"+String(this.rootProject.id)+"/1",
                handleAs: "json"
            });

            pb.show().then(function(){
                checkTenderAlternativeXhr.then(function(r){
                    pb.hide();
                    if(r.items.length > 2){
                        self.rootProject.has_tender_alternative = 1;
                        //getTenderAlternatives will return project name and last row along with tender alternative records. so we exclude 2 returned items from getTenderAlternatives to check if there is  any tender alternative records
                        var d = new TenderAlternativeListDialog({
                            project: self.rootProject,
                            workArea: self.workArea,
                            type: 'printFinalBQ',
                            opt: {withPrice: withPrice}
                        });
                        d.show();
                    }else{
                        var t = withPrice ? nls.withPrice : nls.withoutPrice;
                        var d = new PrintFinalBQDialog({
                            title: nls.printFinalBQ+' ('+t+')',
                            project: self.rootProject,
                            withPrice: withPrice
                        });

                        d.show();
                    }
                });
            });
        },
        _viewTenderers: function(){
            var self = this;
            var pb = buildspace.dialog.indeterminateProgressBar({
                title: nls.pleaseWait + '...'
            });
            var checkTenderAlternativeXhr = dojo.xhrGet({
                url: "getTenderAlternatives/"+String(this.rootProject.id)+"/1",
                handleAs: "json"
            });

            pb.show().then(function(){
                checkTenderAlternativeXhr.then(function(r){
                    pb.hide();
                    if(r.items.length > 2){
                        self.rootProject.has_tender_alternative = 1;
                        //getTenderAlternatives will return project name and last row along with tender alternative records. so we exclude 2 returned items from getTenderAlternatives to check if there is  any tender alternative records
                        var d = new TenderAlternativeListDialog({
                            project: self.rootProject,
                            workArea: self.workArea,
                            type: 'viewTenderer'
                        });
                        d.show();
                    }else{
                        buildspace.app.launch({
                            __children: [],
                            icon: "view_tenderer",
                            id: String(self.rootProject.id)+'-view_tenderer',
                            is_app: true,
                            level: 0,
                            sysname: "ViewTenderer",
                            title: nls.viewTenderers
                        },{
                            type: buildspace.constants.STATUS_TENDERING,
                            project: self.rootProject
                        });
                    }
                });
            });
        },
        _openExportExcelBillDialog: function(bill){
            // will be determine by bill type
            switch(parseInt(String(bill.type))){
                case buildspace.apps.Tendering.ProjectStructureConstants.TYPE_SUPPLY_OF_MATERIAL_BILL:
                    new ExportSupplyOfMaterialItemDialog({
                        bill: bill
                    }).show();
                    break;
                case buildspace.apps.Tendering.ProjectStructureConstants.TYPE_SCHEDULE_OF_RATE_BILL:
                    new ExportScheduleOfRateBillDialog({
                        bill: bill
                    }).show();
                    break;
                default:
                    new ExportItemDialog({
                        bill: bill
                    }).show();
            }
        },
        exportToExcel: function(){
            var self = this;
            var projectBreakDownGrid = this.grid;
            var pb = buildspace.dialog.indeterminateProgressBar({
                title: nls.pleaseWait + '...'
            });
            var checkTenderAlternativeXhr = dojo.xhrGet({
                url: "getTenderAlternatives/"+String(this.rootProject.id)+"/1",
                handleAs: "json"
            });

            pb.show().then(function(){
                checkTenderAlternativeXhr.then(function(r){
                    pb.hide();
                    var d;
                    if(r.items.length > 2){
                        self.rootProject.has_tender_alternative = 1;
                        //getTenderAlternatives will return project name and last row along with tender alternative records. so we exclude 2 returned items from getTenderAlternatives to check if there is  any tender alternative records
                        d = new TenderAlternativeListDialog({
                            project: self.rootProject,
                            workArea: self.workArea,
                            type: 'exportExcel'
                        });
                    }else{
                        self.rootProject.has_tender_alternative = 0;
                        d = new GeneratorDialog({
                            project: self.rootProject,
                            onSuccess: dojo.hitch(self, "_exportToExcel"),
                            onClickErrorNode: function(bill, evt){
                                switch (parseInt(String(bill.type))) {
                                    case buildspace.apps.Tendering.ProjectStructureConstants.TYPE_BILL:
                                        if (parseInt(String(bill['bill_status'])) == buildspace.apps.Tendering.ProjectStructureConstants.BILL_STATUS_OPEN) {
                                            self.workArea.initTab(bill, {
                                                billId: parseInt(String(bill.id)),
                                                billType: parseInt(String(bill.bill_type)),
                                                billLayoutSettingId: bill.billLayoutSettingId,
                                                projectBreakdownGrid: projectBreakDownGrid,
                                                rootProject: self.rootProject
                                            });
                                        }
                                        break;
                                    case buildspace.apps.Tendering.ProjectStructureConstants.TYPE_SUPPLY_OF_MATERIAL_BILL:
                                        self.workArea.initTab(bill, {
                                            billId: parseInt(String(bill.id)),
                                            somBillLayoutSettingId: bill.somBillLayoutSettingId,
                                            projectBreakdownGrid: projectBreakDownGrid,
                                            rootProject: self.rootProject
                                        });
                                        break;
                                    case buildspace.apps.Tendering.ProjectStructureConstants.TYPE_SCHEDULE_OF_RATE_BILL:
                                        self.workArea.initTab(bill, {
                                            billId: parseInt(String(bill.id)),
                                            sorBillLayoutSettingId: bill.sorBillLayoutSettingId,
                                            projectBreakdownGrid: projectBreakDownGrid,
                                            rootProject: self.rootProject
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
        },
        _exportToExcel: function(){
            ExportExcelDialog({
                project: this.rootProject
            }).show();
        },
        printToPdf: function(opt){
            var self = this;
            var projectBreakDownGrid = this.grid;
            var pb = buildspace.dialog.indeterminateProgressBar({
                title: nls.pleaseWait + '...'
            });
            var checkTenderAlternativeXhr = dojo.xhrGet({
                url: "getTenderAlternatives/"+String(this.rootProject.id)+"/1",
                handleAs: "json"
            });

            pb.show().then(function(){
                checkTenderAlternativeXhr.then(function(r){
                    pb.hide();
                    var d;
                    if(r.items.length > 2){
                        self.rootProject.has_tender_alternative = 1;
                        //getTenderAlternatives will return project name and last row along with tender alternative records. so we exclude 2 returned items from getTenderAlternatives to check if there is  any tender alternative records
                        d = new TenderAlternativeListDialog({
                            project: self.rootProject,
                            workArea: self.workArea,
                            type: 'printPdf',
                            opt: opt
                        });
                    }else{
                        self.rootProject.has_tender_alternative = 0;
                        d = new GeneratorDialog({
                            project: self.rootProject,
                            onSuccess: dojo.hitch(self, "_printToPdf", opt),
                            onClickErrorNode: function(bill, evt){
                                switch (parseInt(String(bill.type))) {
                                    case buildspace.apps.Tendering.ProjectStructureConstants.TYPE_BILL:
                                        if (parseInt(String(bill['bill_status'])) == buildspace.apps.Tendering.ProjectStructureConstants.BILL_STATUS_OPEN) {
                                            self.workArea.initTab(bill, {
                                                billId: parseInt(String(bill.id)),
                                                billType: parseInt(String(bill.bill_type)),
                                                billLayoutSettingId: bill.billLayoutSettingId,
                                                projectBreakdownGrid: projectBreakDownGrid,
                                                rootProject: self.rootProject
                                            });
                                        }
                                        break;
                                    case buildspace.apps.Tendering.ProjectStructureConstants.TYPE_SUPPLY_OF_MATERIAL_BILL:
                                        self.workArea.initTab(bill, {
                                            billId: parseInt(String(bill.id)),
                                            somBillLayoutSettingId: bill.somBillLayoutSettingId,
                                            projectBreakdownGrid: projectBreakDownGrid,
                                            rootProject: self.rootProject
                                        });
                                        break;
                                    case buildspace.apps.Tendering.ProjectStructureConstants.TYPE_SCHEDULE_OF_RATE_BILL:
                                        self.workArea.initTab(bill, {
                                            billId: parseInt(String(bill.id)),
                                            sorBillLayoutSettingId: bill.sorBillLayoutSettingId,
                                            projectBreakdownGrid: projectBreakDownGrid,
                                            rootProject: self.rootProject
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
        },
        _printToPdf:function(opt){
            window.open('ProjectSummaryPdf/'+this.rootProject.id+'/'+this.rootProject._csrf_token+'/'+opt, '_blank');
            return window.focus();
        }
    });
});
