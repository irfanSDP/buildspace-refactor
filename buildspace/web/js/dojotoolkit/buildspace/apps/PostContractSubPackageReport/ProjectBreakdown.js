define('buildspace/apps/PostContractSubPackageReport/ProjectBreakdown',[
    'dojo/_base/declare',
    'dojo/number',
    'dojox/grid/EnhancedGrid',
    'buildspace/widget/grid/cells/Formatter',
    'dijit/DropDownMenu',
    'dijit/form/DropDownButton',
    'dijit/MenuItem',
    'dojo/request',
    './PrintProjectClaimReportFormDialog',
    'buildspace/apps/PostContractReport/Builder',
    'dojo/i18n!buildspace/nls/PostContractSubPackage'
], function(declare, number, EnhancedGrid, GridFormatter, DropDownMenu, DropDownButton, MenuItem, request, PrintProjectClaimReportFormDialog, PostContractReport, nls) {

    var Grid = declare('buildspace.apps.PostContractSubPackageReport.ProjectBreakdownGrid', EnhancedGrid, {
        rootProject: null,
        subPackage: null,
        style: "border:none;",
        region: 'center',
        keepSelection: true,
        workArea: null,
        rowSelector: '0px',
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
                    }, {
                        name: nls.description,
                        field: 'title',
                        width:'auto',
                        formatter: Formatter.treeCellFormatter,
                        rowSpan : 2
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
                    }],
                    [{
                        name: nls.upToDateClaim,
                        styles:'text-align:center;',
                        headerClasses: "staticHeader",
                        colSpan : 2
                    }]
                ]
            };

            this.inherited(arguments);
        },
        canSort: function(inSortInfo){
            return false;
        },
        dodblclick: function(e){
            this.onRowDblClick(e);
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

            switch(parseInt(item.type)){
                case buildspace.apps.PostContractSubPackageReport.ProjectStructureConstants.BILL_TYPE_PRELIMINARY:
                    this.workArea.initTab(item, {
                        billId: item.id,
                        billLayoutSettingId: item.billLayoutSettingId,
                        workArea: self.workArea,
                        rootProject: self.rootProject,
                        subPackage: self.subPackage
                    });
                    break;
                case buildspace.apps.PostContractSubPackageReport.ProjectStructureConstants.TYPE_BILL:
                    if(item['bill_status'][0] == buildspace.apps.PostContractSubPackageReport.ProjectStructureConstants.BILL_STATUS_OPEN){
                        // Do Something here
                        // var options = {
                        //     billId: item.id,
                        //     billLayoutSettingId: item.billLayoutSettingId,
                        //     workArea: self.workArea,
                        //     rootProject: self.rootProject
                        // };
                        // this.workArea.initTab(item, options);
                    }
                    break;
                case buildspace.apps.PostContractSubPackageReport.ProjectStructureConstants.TYPE_VARIATION_ORDER:
                    this.workArea.initTab(item, {
                        subPackage: self.subPackage
                    });
                    break;
                case buildspace.apps.PostContractSubPackageReport.ProjectStructureConstants.TYPE_MATERIAL_ON_SITE:
                    this.workArea.initTab(item, {
                        subPackage: self.subPackage
                    });
                default:
                    break;
            }
        },
        reload: function(){
            this.store.close();
            this._refresh();
        }
    });

    var ProjectBreakdown = declare('buildspace.apps.PostContractSubPackageReport.ProjectBreakdown', dijit.layout.BorderContainer, {
        region: "center",
        style:"padding:0px;width:100%;height:100%;",
        gutters: false,
        rootProject: null,
        subPackage: null,
        workArea: null,
        grid: null,
        postCreate: function(){
            this.inherited(arguments);
            var self = this;

            var store = new dojo.data.ItemFileWriteStore({
                clearOnClose:true,
                url:"postContractSubPackage/getProjectBreakdown/id/"+this.subPackage.id
            });

            var grid = this.grid = Grid({
                rootProject: this.rootProject,
                subPackage: this.subPackage,
                store: store,
                workArea: this.workArea
            }),
            toolbar = new dijit.Toolbar({region:"top", style:"outline:none!important;border-bottom:none;padding:2px;width:100%;"});

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
                            id: self.rootProject.id+'-postcontract_subpackage_remeasurement_report',
                            is_app: true,
                            level: 0,
                            sysname: "PostContractSubPackageRemeasurementReport",
                            title: nls.remeasureProvisional
                        },{
                            type: buildspace.constants.STATUS_POSTCONTRACT,
                            opt: opt,
                            project: self.rootProject,
                            subPackage: self.subPackage
                        });
                    }
                });

                menu.addChild(menuItem);
            });

            toolbar.addChild(
                new dijit.form.Button({
                    label: nls.backToProjectPostContractReport,
                    iconClass: "icon-16-container icon-16-directional_left",
                    style:"outline:none!important;",
                    onClick: dojo.hitch(this, 'openBuilderWin', self.rootProject)
                })
            );

            toolbar.addChild(new dijit.ToolbarSeparator());
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
                    id: self.rootProject.id+'PostContractSubPackageReportPrintClaimReport-button',
                    label: nls.printClaimSummary,
                    iconClass: "icon-16-container icon-16-print",
                    onClick: function(e) {
                        self.openPrintingDialog();
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
            this.addChild(new dijit.layout.ContentPane({style: 'width:100%', content:grid, region:'center'}));
        },
        openBuilderWin: function(project){
            this.kill();
            this.project = project;
            this.win = new buildspace.widget.Window({
                title: nls.postContractReport + ' > ' + buildspace.truncateString(project.title, 100) + ' (' + nls.status + ': ' + project.status[0].toUpperCase() + ')',
                onClose: dojo.hitch(this, "kill")
            });

            this.win.addChild(new PostContractReport({
                project: project
            }));

            this.win.show();
            this.win.startup();
        },
        openPrintingDialog: function(self) {
            var pb;

            if (self == null) {
                self = this;
            }

            pb = buildspace.dialog.indeterminateProgressBar({
                title: nls.pleaseWait + "..."
            });

            pb.show();

            return request.get("viewTendererReporting/getPrintingInformation", {
                handleAs: 'json'
            }).then(function(response) {
                var dialog;
                dialog = new PrintProjectClaimReportFormDialog({
                    subPackageId: self.subPackage.id,
                    title: nls.printClaimSummary,
                    projectTitle: self.rootProject.title[0],
                    printURL: 'postContractSubPackageRemeasurementReport/printProjectClaimSummary',
                    exportURL: 'postContractSubPackageRemeasurementReport/exportExcelProjectClaimReport',
                    _csrf_token: response._csrf_token
                });

                pb.hide();

                return dialog.show();
            }, function(error) {
                return pb.hide();
            });
        },
        kill: function(){
            if (typeof(this.win) != "undefined"){
                this.win.close();
            }
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

            if(item.type != undefined && item.type < 1){
                cell.customClasses.push('invalidTypeItemCell');
            }else{
                cellValue = '<div class="treeNode" style="padding-left:'+level+'px;"><div class="treeContent">'+cellValue+'&nbsp;</div></div>';
            }

            return cellValue;
        }
    };

    return ProjectBreakdown;
});