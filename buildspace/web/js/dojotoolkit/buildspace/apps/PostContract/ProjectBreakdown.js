define('buildspace/apps/PostContract/ProjectBreakdown',[
    'dojo/_base/declare',
    'dojo/number',
    'dojox/grid/EnhancedGrid',
    'buildspace/widget/grid/cells/Formatter',
    'dijit/DropDownMenu',
    'dijit/form/DropDownButton',
    'dijit/MenuItem',
    'dojo/i18n!buildspace/nls/PostContract'
], function(declare, number, EnhancedGrid, GridFormatter, DropDownMenu, DropDownButton, MenuItem, nls) {

    var Grid = declare('buildspace.apps.PostContract.ProjectBreakdownGrid', EnhancedGrid, {
        rootProject: null,
        style: "border:none;",
        region: 'center',
        keepSelection: true,
        workArea: null,
        rowSelector: '0px',
        locked: false,
        gridContainer: false,
        claimCertificate: null,
        constructor:function(args){
            var formatter = new GridFormatter();

            this.currencySetting = (buildspace.billCurrencyAbbreviation) ? buildspace.billCurrencyAbbreviation : buildspace.currencyAbbreviation;

            this.structure = {
                noscroll: false,
                cells: [
                    [{
                        name: 'No.',
                        field: 'count',
                        width:'30px',
                        styles:'text-align:center;',
                        formatter: Formatter.rowCountCellFormatter,
                        rowSpan : 2
                    },{
                        name: nls.description,
                        field: 'title',
                        width:'auto',
                        formatter: Formatter.treeCellFormatter,
                        rowSpan : 2
                    },{
                        name: nls.omittedItems,
                        field: 'vo_omitted_items',
                        width:'45px',
                        styles:'text-align: center;',
                        editable: false,
                        noresize: true,
                        showInCtxMenu: true,
                        rowSpan : 2,
                        formatter: formatter.unEditableCellFormatter
                    },{
                        name: nls.overallTotal,
                        field: 'overall_total_after_markup',
                        width:'150px',
                        rowSpan : 2,
                        styles:'text-align:right;',
                        formatter : formatter.unEditableCurrencyCellFormatter
                    },{
                        name: nls.percent,
                        field: 'up_to_date_percentage',
                        width:'80px',
                        formatter: Formatter.unEditablePercentageCellFormatter,
                        styles:'text-align: right;'
                    },{
                        name: nls.amount,
                        field: 'up_to_date_amount',
                        width:'160px',
                        formatter: formatter.unEditableCurrencyCellFormatter,
                        styles:'text-align: right;'
                    },{
                        name: nls.percent,
                        field: 'imported_up_to_date_percentage',
                        width:'80px',
                        formatter: Formatter.unEditablePercentageCellFormatter,
                        styles:'text-align: right;',
                        showInCtxMenu: true,
                        ctxMenuLabel: nls.importedUpToDateClaim,
                        hideColumnGroup: [
                            {field:'imported_up_to_date_amount'},
                            {name: nls.importedUpToDateClaim}
                        ]
                    },{
                        name: nls.amount,
                        field: 'imported_up_to_date_amount',
                        width:'160px',
                        styles:'text-align: right;',
                        formatter: formatter.unEditableCurrencyCellFormatter,
                    }],
                    [{
                        name: nls.upToDateClaim,
                        styles:'text-align:center;',
                        headerClasses: "staticHeader",
                        colSpan : 2
                    },{
                        name: nls.importedUpToDateClaim,
                        styles:'text-align:center;',
                        headerClasses: "staticHeader",
                        colSpan : 2
                    }]
                ]
            };

            buildspace.grid.headerCtxMenu.createMenu(this);
            this.inherited(arguments);
        },
        canSort: function(inSortInfo){
            return false;
        },
        postCreate: function(){
            this.inherited(arguments);
            this.on("RowClick", function(e){
                var colField = e.cell.field,
                    rowIndex = e.rowIndex,
                    _item = this.getItem(rowIndex);

                if(_item && !isNaN(parseInt(String(_item.id))) && _item.type[0] == buildspace.apps.PostContract.ProjectStructureConstants.TYPE_BILL){
                    this.disableToolbarButtons(false);
                }else{
                    this.disableToolbarButtons(true);
                }

            }, true);
        },
        dodblclick: function(e){
            var item = this.getItem(e.rowIndex);
            if(parseInt(String(item.type)) != -1){
                var pb = new dijit.ProgressBar({
                    value: 0,
                    title: "Importing Claims",
                    layoutAlign:"center"
                });
                var box = new dijit.Dialog({
                    content: pb,
                    style: "background:#fff;padding:5px;height:78px;width:280px;",
                    splitter: false
                });
                box.closeButtonNode.style.display = "none";
                box._onKey = function(evt){
                    var key = evt.keyCode;
                    if (key == keys.ESCAPE) {
                        dojo.stopEvent(evt);
                    }
                };
                box.onHide = function() {
                    box.destroyRecursive();
                };

                this.importClaimProgress(e, box, pb);
            }
        },
        importClaimProgress: function(e, box, pb){
            var self = this;
            dojo.xhrPost({
                url: 'claimTransfer/getImportClaimProgress',
                content: {
                    id: parseInt(String(this.rootProject.id))
                },
                handleAs: 'json',
                sync: false,
                load: function(data) {
                    var totalImportedFiles = parseInt(data.total_imported_files);
                    var totalFiles = parseInt(data.total_files);
                    var version = parseInt(data.version);

                    if(data.exists && totalFiles > 0 && totalImportedFiles != totalFiles){
                        if(!box.open){
                            box.show();
                        }

                        box.set({title:"Importing "+totalImportedFiles+"/"+totalFiles+" Files for Claim Revision "+version});

                        var i = totalImportedFiles / totalFiles * 100;
                        pb.set({value: i});

                        setTimeout(function(){self.importClaimProgress(e, box, pb);}, 5000);
                    }else{
                        if(box.open){
                            box.hide();
                        }
                        
                        return self.onRowDblClick(e);
                    }
                },
                error: function(error) {
                    if(box.open){
                        box.hide();
                    }
                }
            });
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
        onRowDblClick: function(e){
            var self = this, item = self.getItem(e.rowIndex);

            if(isNaN(String(item.id))){
                switch(parseInt(String(item.type))){
                    case buildspace.apps.PostContract.ProjectStructureConstants.TYPE_VARIATION_ORDER:
                    case buildspace.apps.PostContract.ProjectStructureConstants.TYPE_REQUEST_FOR_VARIATION_CLAIM:
                        var options = (parseInt(item.type) == buildspace.apps.PostContract.ProjectStructureConstants.TYPE_VARIATION_ORDER) ? {
                            rootProject: this.rootProject,
                            locked: this.locked,
                            claimCertificate: this.claimCertificate
                        } : {
                            project: this.rootProject,
                            locked: this.locked,
                            claimCertificate: this.claimCertificate
                        };

                        this.workArea.initTab(item, options);
                    break;
                    case buildspace.apps.PostContract.ProjectStructureConstants.TYPE_MATERIAL_ON_SITE:
                        this.workArea.initTab(item, {
                            rootProject: this.rootProject
                        });
                    break;
                    case buildspace.apps.PostContract.ProjectStructureConstants.TYPE_ADVANCE_PAYMENT:
                    case buildspace.apps.PostContract.ProjectStructureConstants.TYPE_POST_CONTRACT_CLAIM_MATERIAL_ON_SITE:
                    case buildspace.apps.PostContract.ProjectStructureConstants.TYPE_DEPOSIT:
                    case buildspace.apps.PostContract.ProjectStructureConstants.TYPE_PURCHASE_ON_BEHALF:
                    case buildspace.apps.PostContract.ProjectStructureConstants.TYPE_PERMIT:
                        this.workArea.initTab(item, {
                            rootProject: self.rootProject,
                            type: item.type,
                            withProgressClaim : true,
                            locked: self.locked,
                            claimCertificate: self.claimCertificate
                        });
                        break;
                    case buildspace.apps.PostContract.ProjectStructureConstants.TYPE_KONGSIKONG:
                    case buildspace.apps.PostContract.ProjectStructureConstants.TYPE_WORK_ON_BEHALF:
                    case buildspace.apps.PostContract.ProjectStructureConstants.TYPE_WORK_ON_BEHALF_BACKCHARGE:
                    case buildspace.apps.PostContract.ProjectStructureConstants.TYPE_PENALTY:
                    case buildspace.apps.PostContract.ProjectStructureConstants.TYPE_WATER_DEPOSIT:
                        this.workArea.initTab(item, {
                            rootProject: self.rootProject,
                            type: item.type,
                            withProgressClaim : false,
                            locked: self.locked,
                            claimCertificate: self.claimCertificate
                        });
                        break;
                    case buildspace.apps.PostContract.ProjectStructureConstants.TYPE_DEBIT_CREDIT_NOTE:
                        this.workArea.initTab(item, {
                            rootProject: self.rootProject,
                            claimCertificate: self.claimCertificate,
                        });
                        break;
                    default:
                        break;
                }
            }else{
                switch(parseInt(String(item.type))){
                    // Todo: Refactor. This should be TYPE_BILL, not BILL_TYPE_PRELIMINARY. Verify first.
                    case buildspace.apps.PostContract.ProjectStructureConstants.BILL_TYPE_PRELIMINARY:
                        this.workArea.initTab(item, {
                            billId: item.id,
                            billLayoutSettingId: item.billLayoutSettingId,
                            workArea: self.workArea,
                            rootProject: self.rootProject,
                            locked: self.locked
                        });
                        break;
                    case buildspace.apps.PostContract.ProjectStructureConstants.TYPE_BILL:
                        if(item['bill_status'][0] == buildspace.apps.PostContract.ProjectStructureConstants.BILL_STATUS_OPEN){
                            // Do Something here
                        }
                        break;
                    default:
                        break;
                }
            }
        },
        disableToolbarButtons: function(isDisable, buttonsToEnable){
            if(isDisable && buttonsToEnable instanceof Array ){
                var _this = this;

                dojo.forEach(buttonsToEnable, function(label){
                    var btn = dijit.byId(_this.rootProject.id+label+'Row-button');

                    if(btn)
                        btn._setDisabledAttr(false);
                });
            }
        },
        reload: function(){
            this.claimCertificate = this.gridContainer.claimCertificate;
            this.store.url = this.gridContainer.getProjectBreakdownUrl();
            this.store.close();
            this._refresh();
        }
    });

    var Formatter = {
        rowCountCellFormatter: function(cellValue, rowIdx, cell){
            var item = this.grid.getItem(rowIdx);

            if (item.type != undefined && item.type < 1){
                cell.customClasses.push('invalidTypeItemCell');
            }

            return cellValue > 0 ? cellValue : '';
        },
        unEditablePercentageCellFormatter: function(cellValue, rowIdx, cell){
            var value = number.parse(cellValue);
            var item = this.grid.getItem(rowIdx);

            if (item.type != undefined && item.type < 1){
                cell.customClasses.push('invalidTypeItemCell');
            }else{
                cell.customClasses.push('disable-cell');
            }

            if(isNaN(value) || value == 0 || value == null){
                cellValue = "&nbsp;";
            }else{
                var formattedValue = number.format(value, {places:2})+"%";
                cellValue = value >= 0 ? '<span style="color:blue;">'+formattedValue+'</span>' : '<span style="color:#FF0000">'+formattedValue+'</span>';
            }
            return cellValue;
        },
        treeCellFormatter: function(cellValue, rowIdx, cell){
            var item = this.grid.getItem(rowIdx);
            var level = item.level*16;
            cellValue = cellValue == null ? '&nbsp': cellValue;

            if((item.type < buildspace.apps.PostContract.ProjectStructureConstants.TYPE_BILL) && (!isNaN(item.id))){
                cellValue =  '<b>'+cellValue+'</b>';
            }
            if(item.type != undefined && item.type < 1){
                cell.customClasses.push('invalidTypeItemCell');
            }else{
                cellValue = '<div class="treeNode" style="padding-left:'+level+'px;"><div class="treeContent">'+cellValue+'&nbsp;</div></div>';
            }

            return cellValue;
        }
    };

    return declare('buildspace.apps.PostContract.ProjectBreakdown', dijit.layout.BorderContainer, {
        region: "center",
        style:"padding:0px;width:100%;height:100%;",
        gutters: false,
        rootProject: null,
        workArea: null,
        grid: null,
        locked: false,
        claimCertificate: null,
        getProjectBreakdownUrl: function(){
            var url = "postContract/getProjectBreakdown/id/"+this.rootProject.id;

            if(this.claimCertificate) url += "/claimRevision/"+this.claimCertificate.post_contract_claim_revision_id;

            return url;
        },
        postCreate: function(){
            this.inherited(arguments);
            var self = this;

            var store = new dojo.data.ItemFileWriteStore({
                clearOnClose:true,
                url:self.getProjectBreakdownUrl()
            });

            var grid = this.grid = Grid({
                rootProject: this.rootProject,
                store: store,
                workArea: this.workArea,
                locked: this.locked,
                gridContainer: this,
                claimCertificate: this.claimCertificate,
            });

            if(!this.locked){
                var toolbar = new dijit.Toolbar({region:"top", style:"outline:none!important;border-bottom:none;padding:2px;width:100%;"});

                var sortOptions = ['allItems', 'provisionalItems'],
                    menu = new DropDownMenu({ style: "display: none;"});

                dojo.forEach(sortOptions, function(opt) {
                    var menuItem = new MenuItem({
                        id: opt+"-"+self.rootProject.id+"-menuItem",
                        label: nls[opt],
                        onClick: function(){
                            buildspace.app.launch({
                                __children: [],
                                icon: "project_analyzer",
                                id: self.rootProject.id+'-postcontract_remeasurement',
                                is_app: true,
                                level: 0,
                                sysname: "PostContractRemeasurement",
                                title: nls.remeasureProvisional
                            },{
                                type: buildspace.constants.STATUS_PRETENDER,
                                opt: opt,
                                project: self.rootProject
                            });
                        }
                    });
                    menu.addChild(menuItem);
                });

                toolbar.addChild(
                    new DropDownButton({
                        label: nls.remeasureProvisional,
                        id: self.rootProject.id+'RemeasureProvisionalRow-button',
                        iconClass: "icon-16-container icon-16-ruler_square",
                        dropDown: menu
                    })
                );

                toolbar.addChild(new dijit.ToolbarSeparator());
                toolbar.addChild(
                    new dijit.form.Button({
                        id: "locationManagement-"+self.rootProject.id+"-mainButton",
                        label: nls.locationManagement,
                        iconClass: "icon-16-container icon-16-shopping_basket",
                        onClick: function(e) {
                            buildspace.app.launch({
                                __children: [],
                                icon: "project_location_management",
                                id: self.rootProject.id+'-location_management',
                                is_app: true,
                                level: 0,
                                sysname: "ProjectLocationManagement",
                                title: nls.locationManagement
                            },{
                                type: buildspace.constants.STATUS_POSTCONTRACT,
                                project: self.rootProject,
                                canEdit: true
                            });
                        }
                    })
                );

                toolbar.addChild(new dijit.ToolbarSeparator());
                toolbar.addChild(new dijit.form.Button({
                    id: self.rootProject.id + 'LocationCodeContainerRow-button',
                    label: nls.defineLocations,
                    iconClass: "icon-16-container icon-16-location",
                    onClick: dojo.hitch(this.workArea, "initLocationCodeContainerTab")
                }));

                toolbar.addChild(new dijit.ToolbarSeparator());
                toolbar.addChild(
                    new dijit.form.Button({
                        id: self.rootProject.id+'ViewSubPackageRow-button',
                        label: nls.subPackages,
                        iconClass: "icon-16-container icon-16-file",
                        onClick: function(){
                            buildspace.app.launch({
                                __children: [],
                                icon: "view_subpackage",
                                id: self.rootProject.id+'-view_subpackage',
                                is_app: true,
                                level: 0,
                                sysname: "PostContractSubPackage",
                                title: nls.subPackages
                            },{
                                project: self.rootProject
                            });
                        }
                    })
                );

                toolbar.addChild(new dijit.ToolbarSeparator());
                toolbar.addChild(
                    new dijit.form.Button({
                        id: self.rootProject.id+'-wastageReport-button',
                        label: nls.wastageReport,
                        iconClass: "icon-16-container icon-16-project_analyzer",
                        onClick: function(e){
                            buildspace.app.launch({
                                __children: [],
                                icon: "project_analyzer",
                                id: self.rootProject.id+'-project_analyzer',
                                is_app: true,
                                level: 0,
                                sysname: "ProjectAnalyzer",
                                title: nls.projectAnalyzer
                            },{
                                type: buildspace.constants.STATUS_POSTCONTRACT,
                                opt: 'resourceAnalysis',
                                project: self.rootProject
                            });
                        }
                    })
                );

                toolbar.addChild(new dijit.ToolbarSeparator());
                toolbar.addChild(
                    new dijit.form.Button({
                        id: self.rootProject.id+'finalAccountExport-button',
                        label: nls.finalAccountStatement,
                        iconClass: "icon-16-container icon-16-spreadsheet",
                        onClick: function(e){
                            window.open('exportExcelReport/exportFinalAccountStatement/pid/'+self.rootProject.id, '_self');
                            return window.focus();
                        }
                    })
                );

                toolbar.addChild(new dijit.ToolbarSeparator());

                var budgetReportMenu = new DropDownMenu({ style: "display: none;"});

                budgetReportMenu.addChild(MenuItem({
                    label: nls.budgetReport,
                    onClick: dojo.hitch(self.workArea, "initBudgetReportContainerTab")
                }));

                budgetReportMenu.addChild(MenuItem({
                    label: nls.tagSubProjectItems,
                    onClick: dojo.hitch(self.workArea, "initSubProjectItemLinkContainerTab")
                }));

                toolbar.addChild(
                    new DropDownButton({
                        label: nls.budgetReport,
                        iconClass: "icon-16-container icon-16-abacus",
                        dropDown: budgetReportMenu
                    })
                );

                toolbar.addChild(new dijit.ToolbarSeparator());
                toolbar.addChild(
                    new dijit.form.Button({
                        label: nls.subPackageClaims,
                        iconClass: "icon-16-container icon-16-bill",
                        onClick: dojo.hitch(self.workArea, "initSubPackageClaimsContainerTab")
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
            }

            this.addChild(grid);
        }
    });
});
