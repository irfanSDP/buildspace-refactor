define('buildspace/apps/TenderAlternative/TenderAlternativeContainer', [
    'dojo/_base/declare',
    'dojo/_base/lang',
    'dojo/aspect',
    'dojox/grid/EnhancedGrid',
    'buildspace/widget/grid/cells/Formatter',
    'buildspace/apps/ProjectBuilder/EmptyGrandTotalQtyDialog',
    'buildspace/apps/ProjectBuilder/RecalculateDialog',
    './TenderAlternativeFormDialog',
    './LinkBillDialog',
    'dojo/i18n!buildspace/nls/TenderAlternative'
], function (declare, lang, aspect, EnhancedGrid, GridFormatter, EmptyGrandTotalQtyDialog, RecalculateDialog, TenderAlternativeFormDialog, LinkBillDialog, nls) {

    var Grid = declare('buildspace.apps.TenderAlternative.TenderAlternativeListGrid', EnhancedGrid, {
        project: null,
        tenderAlternative: null,
        projectBreakdownGrid: null,
        workArea: null,
        editable: true,
        type: 'tender_alternative',
        style: "border:none;",
        region: 'center',
        keepSelection: true,
        rowSelector: '0px',
        currencySetting: buildspace.currencyAbbreviation,
        canSort: function () {
            return false;
        },
        postCreate: function () {
            var self = this;
            this.inherited(arguments);

            this.projectBreakdownGrid = this.workArea.projectBreakdownTab.grid;

            this.on("RowClick", function(e){
                
                var buttonsToEnable = [];

                var colField = (e.cell) ? e.cell.field : null,
                    rowIndex = e.rowIndex,
                     _item = this.getItem(rowIndex);

                switch(this.type){
                    case 'tender_alternative':
                        if (this.editable && _item && parseInt(String(_item.id)) > 0 && String(_item.project_revision_deleted_at).length == 0) {
                            buttonsToEnable = ['Delete'];
                        }
                        break;
                    case 'bills':
                        if(this.tenderAlternative){
                            
                            if(colField == 'unlink' && parseInt(String(_item.id)) > 0 && parseInt(String(_item.type)) >= buildspace.constants.TYPE_BILL && parseInt(String(_item.type)) <= buildspace.constants.TYPE_SCHEDULE_OF_RATE_BILL){
                                self.unlinkBill(_item);
                            }

                            if (_item && !isNaN(parseInt(String(_item.id))) && parseInt(String(_item.type)) == buildspace.constants.TYPE_BILL &&
                                ( buildspace.isRecalculateBillStatus(parseInt(String(_item['bill_status']))) )) {
        
                                if(colField == 'recalculate') {
                                    new RecalculateDialog( {
                                        title: nls.recalculate + ' ' + buildspace.truncateString(String(_item.title), 65 ),
                                        rootProject: self.project,
                                        bill: _item,
                                        projectBreakDownGrid: self.projectBreakdownGrid,
                                        tenderAlternativeBillGrid: self
                                    } ).show();
                                }
                            }
                        }
                        break;
                    default:
                        break;
                }

                this.disableToolbarButtons(true, buttonsToEnable);

            }, true);
        },
        deleteRow: function (item) {
            var self = this,
                workArea = this.workArea,
                project = this.project,
                pb = buildspace.dialog.indeterminateProgressBar({
                    title: nls.deleting + '. ' + nls.pleaseWait + '...'
                });
            
            var onYes = function () {
                pb.show().then(function(){
                    dojo.xhrPost({
                        url: 'deleteTenderAlternative',
                        content: {id: item.id, _csrf_token: item._csrf_token},
                        handleAs: 'json',
                        load: function (resp) {
                            if(resp.has_alternative){
                                if (resp.success) {
                                    self.reload();
                                }
                                self.selection.clear();
                                self.disableToolbarButtons(true);
                            }else{
                                var tab = dijit.byId(String(project.id)+'-form_1337');
                                if(tab){
                                    workArea.removeChild(tab);
                                    tab.destroy();

                                    dojo.xhrGet({
                                        url: 'getTenderAlternativeProject/'+String(project.id),
                                        handleAs: 'json',
                                    }).then(function(proj){
                                        var tab = workArea.createProjectBreakdownTab(proj, true);
                                        if(tab.grid){
                                            self.projectBreakdownGrid = tab.grid;
                                            tab.grid.reload();
                                        }
                                    });
                                }
                            }
                            pb.hide();
                        },
                        error: function (error) {
                            pb.hide();
                            self.selection.clear();
                            self.disableToolbarButtons(true);
                        }
                    });
                });
            };

            buildspace.dialog.confirm(nls.confirmation, '<div>' + nls.deleteTenderAlternative + '</div>', 80, 300, onYes);
        },
        disableToolbarButtons: function (isDisable, buttonsToEnable) {
            var deleteBtn = dijit.byId(parseInt(String(this.project.id)) + 'DeleteTenderAlternativeRow-button');

            if (deleteBtn)
                deleteBtn._setDisabledAttr(isDisable);
                
            if (isDisable && buttonsToEnable instanceof Array) {

                var _this = this;
                dojo.forEach(buttonsToEnable, function (label) {
                    var btn = dijit.byId(parseInt(String(_this.project.id)) + label + 'TenderAlternativeRow-button');
                    if (btn)
                        btn._setDisabledAttr(false);
                });
            }
        },
        unlinkBill:  function(bill){
            var self = this;
            var project = this.project;
            var tenderAlternative = this.tenderAlternative;
            var pb = buildspace.dialog.indeterminateProgressBar({
                title:nls.deleting+'. '+nls.pleaseWait+'...'
            });

            var xhrArgs = {
                url: 'unlinkTenderAlternativeBill',
                content: {id: parseInt(String(tenderAlternative.id)), bid: parseInt(String(bill.id)), _csrf_token:String(bill._csrf_token)},
                handleAs: 'json',
                load: function(resp) {
                    if(resp.success){
                        self.reload();
                        pb.hide();
                    }
                },
                error: function(error) {
                    pb.hide();
                }
            };

            pb.show().then(function(){
                dojo.xhrGet({
                    url: "getProjectRevisionInfo/"+String(project.id),
                    handleAs: "json",
                    sync:true,
                    preventCache: true
                }).then(function(rev){
                    pb.hide();
                    if(!rev.locked_status && rev.current_selected_revision && rev.id == parseInt(String(tenderAlternative.project_revision_id))){
                        //reinitiate since destroyed from the above hide()
                        pb = buildspace.dialog.indeterminateProgressBar({
                            title:nls.deleting+'. '+nls.pleaseWait+'...'
                        });
                        var onYes = function(){
                            pb.show().then(function(){
                                dojo.xhrPost(xhrArgs);
                            });
                        };

                        var content = '<div>'+nls.untagBillConfirmation+'</div>';
                        buildspace.dialog.confirm(nls.untagBill,content,68,280, onYes);
                    }else{
                        buildspace.dialog.alert(nls.tenderAlternativeAlert, nls.projectRevisionAlertDesc, 100, 300);
                    }
                });
            });
        },
        reload: function () {
            var self = this;

            if(parseInt(String(this.project.status_id)) == buildspace.constants.STATUS_PRETENDER){
                var validateEmptyGrandTotalQtyXhr = dojo.xhrGet({
                    url: "projectBuilder/validateEmptyGrandTotalQty/",
                    content: { pid: this.project.id },
                    handleAs: "json"
                });
            
                validateEmptyGrandTotalQtyXhr.then(function(r){
                    if(r.has_error){
                        new EmptyGrandTotalQtyDialog({
                            id: 'EmptyGrandTotalQtyDialog-' + self.project.id,
                            project: self.project,
                            data: r
                        }).show();
                    }
                });
            }
            
            var projectBreakdownTab = dijit.byId('main-project_breakdown');
            if(projectBreakdownTab){
                this.projectBreakdownGrid = projectBreakdownTab.grid;
                this.projectBreakdownGrid.reload();
            }

            this.store.close();
            this._refresh();
        }
    });

    var GridContainer =  declare('buildspace.apps.TenderAlternative.GridContainer', dijit.layout.BorderContainer, {
        pageId: 'page-00',
        style: "padding:0px;width:100%;height:100%;border:0px!important;",
        gutters: false,
        stackContainerTitle: '',
        project: null,
        workArea: null,
        projectBreakdownGrid: null,
        tenderAlternative: null,
        editable: true,
        rowSelector: null,
        gridOpts: {},
        type: null,
        projectRevision: null,
        postCreate: function(){
            var project = this.project;
            var projectBreakdownGrid = this.projectBreakdownGrid = this.workArea.projectBreakdownTab.grid;
            var projectRevision = this.projectRevision;

            this.inherited(arguments);
            lang.mixin(this.gridOpts, { project: project, workArea: this.workArea, editable: this.editable, type: this.type, tenderAlternative: this.tenderAlternative, projectBreakdownGrid: this.projectBreakdownGrid, region: "center", borderContainerWidget: this });
            
            var grid = this.grid = new Grid(this.gridOpts);

            var toolbar = new dijit.Toolbar({
                region: "top",
                style: "outline:none!important;border:none;padding:2px;width:100%;"
            });
            
            if(parseInt(String(this.project.tender_type_id)) != buildspace.constants.TENDER_TYPE_PARTICIPATED){
                switch(this.type){
                    case 'tender_alternative':
                        if(parseInt(String(project.status_id)) != buildspace.constants.STATUS_POSTCONTRACT){
                            toolbar.addChild(
                                new dijit.form.Button({
                                    id: parseInt(String(project.id)) + 'AddTenderAlternative-container-button',
                                    label: nls.addTenderAlternative,
                                    iconClass: "icon-16-container icon-16-add",
                                    style: "outline:none!important;",
                                    disabled: !this.editable,
                                    onClick: function () {
                                        var pb = buildspace.dialog.indeterminateProgressBar({
                                            title: nls.pleaseWait+'...'
                                        });
    
                                        pb.show().then(function(){
                                            dojo.xhrGet({
                                                url: "getProjectRevisionInfo/"+String(project.id),
                                                handleAs: "json",
                                                sync:true,
                                                preventCache: true
                                            }).then(function(rev){
                                                pb.hide();
                                                if(!rev.locked_status && rev.current_selected_revision){
                                                    var d = new TenderAlternativeFormDialog({
                                                        title: nls.createNewTenderAlternative,
                                                        tenderAlternativeId: -1,
                                                        project: project,
                                                        projectBreakdownGrid: projectBreakdownGrid
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
                            toolbar.addChild(new dijit.ToolbarSeparator());
    
                            toolbar.addChild(
                                new dijit.form.Button({
                                    id: parseInt(String(project.id)) + 'DeleteTenderAlternativeRow-button',
                                    label: nls.delete,
                                    iconClass: "icon-16-container icon-16-delete",
                                    disabled: true,
                                    style: "outline:none!important;",
                                    onClick: function () {
    
                                        var pb = buildspace.dialog.indeterminateProgressBar({
                                            title: nls.pleaseWait+'...'
                                        });
    
                                        pb.show().then(function(){
                                            dojo.xhrGet({
                                                url: "getProjectRevisionInfo/"+String(project.id),
                                                handleAs: "json",
                                                sync:true,
                                                preventCache: true
                                            }).then(function(rev){
                                                pb.hide();
                                                if(!rev.locked_status && rev.current_selected_revision){
                                                    if (grid.selection.selectedIndex > -1) {
                                                        var item = grid.getItem(grid.selection.selectedIndex);
                                                        if (parseInt(String(item.id)) > 0) {
                                                            grid.deleteRow(item);
                                                        }
                                                    }
                                                }else{
                                                    buildspace.dialog.alert(nls.tenderAlternativeAlert, nls.projectRevisionAlertDesc, 100, 300);
                                                }
                                            });
                                        });
                                    }
                                })
                            );
        
                            toolbar.addChild(new dijit.ToolbarSeparator());
                        }
    
                        break;
                    case 'bills':
                        var tenderAlternative = this.tenderAlternative;
                        if(parseInt(String(project.status_id)) != buildspace.constants.STATUS_POSTCONTRACT &&
                            String(tenderAlternative.project_revision_deleted_at).length == 0 &&
                            parseInt(projectRevision.id) == parseInt(String(tenderAlternative.project_revision_id))){
    
                            toolbar.addChild(
                                new dijit.form.Button({
                                    id: parseInt(String(project.id)) +'-'+ parseInt(String(tenderAlternative.id)) +'EditTenderAlternative-container-button',
                                    label: nls.editTenderAlternative,
                                    iconClass: "icon-16-container icon-16-edit",
                                    style: "outline:none!important;",
                                    disabled: !this.editable,
                                    onClick: function () {
                                        var pb = buildspace.dialog.indeterminateProgressBar({
                                            title: nls.pleaseWait+'...'
                                        });
    
                                        pb.show().then(function(){
                                            dojo.xhrGet({
                                                url: "getProjectRevisionInfo/"+String(project.id),
                                                handleAs: "json",
                                                sync:true,
                                                preventCache: true
                                            }).then(function(rev){
                                                pb.hide();
                                                if(!rev.locked_status && rev.current_selected_revision && rev.id == parseInt(String(tenderAlternative.project_revision_id))){
                                                    var d = new TenderAlternativeFormDialog({
                                                        title: nls.editTenderAlternative,
                                                        tenderAlternativeId: parseInt(String(tenderAlternative.id)),
                                                        project: project,
                                                        projectBreakdownGrid: projectBreakdownGrid
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
            
                            toolbar.addChild(new dijit.ToolbarSeparator());
        
                            toolbar.addChild(
                                new dijit.form.Button({
                                    id: parseInt(String(project.id)) +'-'+ parseInt(String(tenderAlternative.id)) +'LinkBillTenderAlternative-container-button',
                                    label: nls.tagBills,
                                    iconClass: "icon-16-container icon-16-connect",
                                    style: "outline:none!important;",
                                    disabled: !this.editable,
                                    onClick: function () {
                                        var pb = buildspace.dialog.indeterminateProgressBar({
                                            title: nls.pleaseWait+'...'
                                        });
    
                                        pb.show().then(function(){
                                            dojo.xhrGet({
                                                url: "getProjectRevisionInfo/"+String(project.id),
                                                handleAs: "json",
                                                sync:true,
                                                preventCache: true
                                            }).then(function(rev){
                                                pb.hide();
                                                if(!rev.locked_status && rev.current_selected_revision && rev.id == parseInt(String(tenderAlternative.project_revision_id))){
                                                    var d = new LinkBillDialog({
                                                        tenderAlternative: tenderAlternative,
                                                        tenderAlternativeGrid: grid
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
            
                            toolbar.addChild(new dijit.ToolbarSeparator());
                        }
                        
                        break;
                    default:
                }
            }
            
            toolbar.addChild(
                new dijit.form.Button({
                    label: nls.reload,
                    iconClass: "icon-16-container icon-16-reload",
                    onClick: dojo.hitch(grid, "reload")
                })
            );

            this.addChild(toolbar);
            this.addChild(grid);

            var container = dijit.byId('tenderAlternativeGrid'+String(this.project.id)+'-stackContainer');
            if(container){
                container.addChild(new dojox.layout.ContentPane( {
                    title: buildspace.truncateString(this.stackContainerTitle, 60),
                    content: this,
                    id: this.pageId,
                    grid: grid
                }));
                container.selectChild(this.pageId);
            }
        }
    });

    return declare('buildspace.apps.TenderAlternative.TenderAlternativeContainer', dijit.layout.BorderContainer, {
        region: "center",
        style: "padding:0px;width:100%;height:100%;",
        gutters: false,
        project: null,
        projectBreakdownGrid: null,
        workArea: null,
        grid: null,
        editable: true,
        pane_info: {},
        postCreate: function () {
            this.inherited(arguments);
            var project =  this.project;
            this.projectBreakdownGrid = this.workArea.projectBreakdownTab.grid;

            this.createTenderAlternativeGrid();

            dojo.subscribe('tenderAlternativeGrid' + String(project.id) + '-stackContainer-selectChild', "", function(page) {
                var widget = dijit.byId('tenderAlternativeGrid' + String(project.id) + '-stackContainer');
                if(widget) {
                    var children = widget.getChildren(),
                        index = dojo.indexOf(children, page);

                    index = index + 1;

                    if(children.length > index){
                        while(children.length > index) {
                            widget.removeChild(children[index]);
                            children[index].destroyDescendants();
                            children[index].destroyRecursive();

                            index = index + 1;
                        }

                        if(page.grid){
                            var selectedIndex = page.grid.selection.selectedIndex;

                            page.grid.store.save();
                            page.grid.store.close();

                            var handle = aspect.after(page.grid, "_onFetchComplete", function() {
                                handle.remove();
                                if(selectedIndex > -1){
                                    this.scrollToRow(selectedIndex);
                                    this.selection.setSelected(selectedIndex, true);
                                }
                            });

                            page.grid.sort();
                        }
                    }
                }
            });

            this.pane_info = {
                name: nls.tenderAlternative,
                type: 1337,
                id: project.id+'-form_1337'
            }
        },
        createTenderAlternativeGrid: function(){
            var stackContainer = dijit.byId('tenderAlternativeGrid' + String(this.project.id) + '-stackContainer');
            if(stackContainer) {
                dijit.byId('tenderAlternativeGrid' + String(this.project.id) + '-stackContainer').destroyRecursive();
            }
            stackContainer = this.stackContainer = new dijit.layout.StackContainer({
                style: 'border:0px;width:100%;height:100%;',
                region: "center",
                id: 'tenderAlternativeGrid' + String(this.project.id) + '-stackContainer'
            });

            var Formatter = {
                rowCountCellFormatter: function (cellValue, rowIdx) {
                    return cellValue > 0 ? cellValue : '';
                },
                treeCellFormatter: function (cellValue, rowIdx, cell) {
                    var item = this.grid.getItem(rowIdx);
                    var level = parseInt(String(item.level)) * 16;
                    cellValue = cellValue == null ? '&nbsp' : cellValue;
                    if (parseInt(String(item.id)) == -9999) {
                        cellValue = '<b>' + cellValue + '</b>';
                    }

                    if(item && parseInt(String(item.id)) > 0 && item.project_revision_deleted_at != undefined && String(item.project_revision_deleted_at).length > 0 && String(item.project_revision_deleted_at).toLowerCase() != 'false') cell.customClasses.push('addendumDeletedItemCell');

                    cellValue = '<div class="treeNode" style="padding-left:' + level + 'px;"><div class="treeContent">' + cellValue + '&nbsp;</div></div>';
                    return cellValue;
                }
            };
            var formatter = new GridFormatter();
            var currencySetting = (buildspace.billCurrencyAbbreviation) ? buildspace.billCurrencyAbbreviation : buildspace.currencyAbbreviation;

            var me = this;
            var grid = new GridContainer({
                stackContainerTitle: nls.tenderAlternatives,
                project: this.project,
                workArea: this.workArea,
                projectBreakdownGrid: this.projectBreakdownGrid,
                editable: this.editable,
                pageId: 'tender_alternative-page-' + String(this.project.id),
                id: 'tender_alternative-page-container-' + String(this.project.id),
                type: 'tender_alternative',
                gridOpts: {
                    id: String(this.project.id)+"-tenderAlternative-tenderAlternativeListGrid",
                    currencySetting: currencySetting,
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
                        name: nls.originalAmount,
                        field: 'original_total',
                        width: '150px',
                        styles: 'text-align: right;',
                        formatter: formatter.unEditableCurrencyCellFormatter
                    },{
                        name: nls.total + ' ' + nls.markup + ' (%)',
                        field: 'original_total',
                        width: '100px',
                        styles: 'text-align: right;',
                        formatter: formatter.elementTotalMarkupPercentageCellFormatter
                    },{
                        name: nls.total + ' ' + nls.markup + ' (' + currencySetting + ')',
                        field: 'original_total',
                        width: '120px',
                        styles: 'text-align: right;',
                        formatter: formatter.elementTotalMarkupAmountCellFormatter
                    },{
                        name: nls.overallTotal,
                        field: 'overall_total_after_markup',
                        width: '150px',
                        styles: 'text-align: right;',
                        formatter: formatter.unEditableCurrencyCellFormatter
                    }],
                    store: new dojo.data.ItemFileWriteStore({
                        clearOnClose: true,
                        url: "getTenderAlternatives/" + this.project.id + "/0"
                    }),
                    onRowDblClick: function(e) {
                        var self = this,
                            item = self.getItem(e.rowIndex);
                        if(parseInt(String(item.id)) > 0 && String(item.title) !== null && String(item.title) !== '') {

                            var pb = buildspace.dialog.indeterminateProgressBar({
                                title: nls.pleaseWait+'...'
                            });

                            pb.show().then(function(){
                                dojo.xhrGet({
                                    url: "getProjectRevisionInfo/"+String(me.project.id),
                                    handleAs: "json",
                                    sync:true,
                                    preventCache: true
                                }).then(function(rev){
                                    pb.hide();
                                    me.createBillGrid(item, rev, grid);
                                });
                            });

                            
                        }
                    }
                }
            });

            var controller = new dijit.layout.StackController({
                region: "top",
                containerId: 'tenderAlternativeGrid' + String(this.project.id) + '-stackContainer'
            });

            this.addChild(stackContainer);

            this.addChild(new dijit.layout.ContentPane({
                style: "padding:0px;overflow:hidden;",
                baseClass: 'breadCrumbTrail',
                region: 'top',
                id: 'tenderAlternativeGrid'+String(this.project.id)+'-controllerPane',
                content: controller
            }));
        },
        createBillGrid: function(item, projectRevision, tenderAlternativeGrid){
            var Formatter = {
                rowCountCellFormatter: function (cellValue, rowIdx) {
                    return cellValue > 0 ? cellValue : '';
                },
                treeCellFormatter: function (cellValue, rowIdx) {
                    var item = this.grid.getItem(rowIdx);
                    var level = parseInt(String(item.level)) * 16;
                    cellValue = cellValue == null ? '&nbsp' : cellValue;
                    if (item && parseInt(String(item.id)) > 0 && isNaN(parseInt(String(item.bill_type)))) {
                        cellValue = '<b>' + cellValue + '</b>';
                    }
                    cellValue = '<div class="treeNode" style="padding-left:' + level + 'px;"><div class="treeContent">' + cellValue + '&nbsp;</div></div>';
                    
                    return cellValue;
                },
                unlinkBill: function(cellValue, rowIdx){
                    var item = this.grid.getItem(rowIdx);
                    if (item && !isNaN(parseInt(String(item.bill_type))) && parseInt(String(item.id)) > 0) {
                        cellValue = '<a href="#"><span class="dijitReset dijitInline dijitIcon icon-16-container icon-16-disconnect"></span></a>';
                    }else{
                        cellValue = "";
                    }
                    return cellValue;
                }
            };

            var me = this;
            var project = this.project;
            var formatter = new GridFormatter();
            var currencySetting = (buildspace.billCurrencyAbbreviation) ? buildspace.billCurrencyAbbreviation : buildspace.currencyAbbreviation;
            var structure = [{
                name: 'No.',
                field: 'count',
                width: '30px',
                styles: 'text-align:center;',
                formatter: Formatter.rowCountCellFormatter
            }];

            if(parseInt(String(project.tender_type_id)) != buildspace.constants.TENDER_TYPE_PARTICIPATED &&
                parseInt(String(project.status_id)) != buildspace.constants.STATUS_POSTCONTRACT &&
                String(item.project_revision_deleted_at).length == 0 &&
                parseInt(projectRevision.id) == parseInt(String(item.project_revision_id))){
                    structure.push({
                        name: nls.untag,
                        field: 'unlink',
                        width: '48px',
                        styles: 'text-align:center;',
                        formatter: Formatter.unlinkBill
                    });
            }
            
            var otherColumns = [{
                name: nls.description, field: 'title', width: 'auto', formatter: Formatter.treeCellFormatter
            },{
                name: nls.originalAmount,
                field: 'original_total',
                width: '150px',
                styles: 'text-align: right;',
                formatter: formatter.unEditableCurrencyCellFormatter
            },{
                name: nls.total + ' ' + nls.markup + ' (%)',
                field: 'original_total',
                width: '100px',
                styles: 'text-align: right;',
                formatter: formatter.elementTotalMarkupPercentageCellFormatter
            },{
                name: nls.total + ' ' + nls.markup + ' (' + currencySetting + ')',
                field: 'original_total',
                width: '120px',
                styles: 'text-align: right;',
                formatter: formatter.elementTotalMarkupAmountCellFormatter
            },{
                name: nls.overallTotal,
                field: 'overall_total_after_markup',
                width: '150px',
                styles: 'text-align: right;',
                formatter: formatter.unEditableCurrencyCellFormatter
            },{
                name: '% ' + nls.project,
                field: 'overall_total_after_markup',
                width: '80px',
                styles: 'text-align: center;',
                formatter: formatter.projectBreakdownJobPercentageCellFormatter
            },{
                name: nls.recalculate,
                field: 'recalculate',
                width: '80px',
                styles: 'text-align: center;',
                formatter: formatter.recalculateBillCellFormatter
            }];

            dojo.forEach(otherColumns,function(column){
                structure.push(column);
            });

            var grid = new GridContainer({
                stackContainerTitle: String(item.title),
                project: this.project,
                workArea: this.workArea,
                projectBreakdownGrid: this.projectBreakdownGrid,
                tenderAlternative: item,
                editable: this.editable,
                pageId: String(item.id)+'-bill-page-' + String(this.project.id),
                id: String(item.id)+'-bill-page-container-' + String(this.project.id),
                projectRevision: projectRevision,
                type: 'bills',
                gridOpts: {
                    id: String(this.project.id)+"-"+String(item.id)+"-tenderAlternative-billListGrid",
                    currencySetting: currencySetting,
                    structure: structure,
                    store: new dojo.data.ItemFileWriteStore({
                        clearOnClose: true,
                        url: "getTenderAlternativeBills/" + parseInt(String(item.id))
                    }),
                    onRowDblClick: function(e) {
                        var item = this.getItem(e.rowIndex);
                        if(parseInt(String(item.id)) > 0 && String(item.description) !== null && String(item.description) !== '') {
                            me.openBillTab(item, grid);
                        }
                    }
                }
            });
        },
        openBillTab:  function(bill, tenderAlternativeBillGrid){
            var options = null;
            switch (parseInt(String(bill.type))) {
                case buildspace.constants.TYPE_BILL:
                    switch(parseInt(String(this.project.status_id))){
                        case buildspace.constants.STATUS_PRETENDER:
                            options = {
                                billId: parseInt(String(bill.id)),
                                billType: parseInt(String(bill.bill_type)),
                                billLayoutSettingId: parseInt(String(bill.billLayoutSettingId)),
                                projectBreakdownGrid: this.projectBreakdownGrid,
                                tenderAlternativeBillGridId: String(tenderAlternativeBillGrid.id),
                                rootProject: this.project
                            };
                            break;
                        default:
                            options = {
                                bill: bill,
                                billLayoutSettingId: bill.billLayoutSettingId,
                                projectBreakdownGrid: this.projectBreakdownGrid,
                                tenderAlternativeBillGridId: String(tenderAlternativeBillGrid.id),
                                rootProject: this.project
                            };
                            break;
                    }
                    break;
                case buildspace.constants.TYPE_SUPPLY_OF_MATERIAL_BILL:
                    switch(parseInt(String(this.project.status_id))){
                        case buildspace.constants.STATUS_PRETENDER:
                            options = {
                                billId: parseInt(String(bill.id)),
                                somBillLayoutSettingId: parseInt(String(bill.somBillLayoutSettingId)),
                                projectBreakdownGrid: this.projectBreakdownGrid,
                                tenderAlternativeBillGridId: String(tenderAlternativeBillGrid.id),
                                rootProject: this.project
                            };
                            break;
                        default:
                            options = {
                                billId: parseInt(String(bill.id)),
                                projectBreakdownGrid: this.projectBreakdownGrid,
                                tenderAlternativeBillGridId: String(tenderAlternativeBillGrid.id),
                                rootProject: this.project
                            };
                            break;
                    }
                    break;
                case buildspace.constants.TYPE_SCHEDULE_OF_RATE_BILL:
                    switch(parseInt(String(this.project.status_id))){
                        case buildspace.constants.STATUS_PRETENDER:
                            options = {
                                billId: parseInt(String(bill.id)),
                                sorBillLayoutSettingId: parseInt(String(bill.sorBillLayoutSettingId)),
                                projectBreakdownGrid: this.projectBreakdownGrid,
                                tenderAlternativeBillGridId: String(tenderAlternativeBillGrid.id),
                                rootProject: this.project
                            };
                            break;
                        default:
                            options = {
                                billId: parseInt(String(bill.id)),
                                projectBreakdownGrid: this.projectBreakdownGrid,
                                rootProject: this.project
                            };
                            break;
                    }
                    break;
                default:
                    break;
            }

            if(bill && parseInt(String(bill.id)) && options){
                this.workArea.initTab(bill, options);
            }
        }
    });
});