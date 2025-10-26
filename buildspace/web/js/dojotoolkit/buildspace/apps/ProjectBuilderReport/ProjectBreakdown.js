define('buildspace/apps/ProjectBuilderReport/ProjectBreakdown',[
    'dojo/_base/declare',
    'dojox/grid/EnhancedGrid',
    "dojox/grid/enhanced/plugins/IndirectSelection",
    'buildspace/widget/grid/cells/Formatter',
    'dojo/request',
    'dijit/form/Button',
    'dijit/form/DropDownButton',
    "dijit/DropDownMenu",
    "dijit/MenuItem",
    './PrintPreviewFormDialog',
    'dojo/i18n!buildspace/nls/ProjectBuilder'
], function(declare, EnhancedGrid, IndirectSelection, GridFormatter, request, Button, DropDownButton, DropDownMenu, MenuItem, PrintPreviewFormDialog, nls){

    var Grid = declare('buildspace.apps.ProjectBuilderReport.ProjectBreakdownGrid', EnhancedGrid, {
        rootProject: null,
        explorer: null,
        style: "border:none;",
        region: 'center',
        keepSelection: true,
        rowSelector: '0px',
        constructor:function(args){
            var formatter = new GridFormatter();

            this.currencySetting = (buildspace.billCurrencyAbbreviation) ? buildspace.billCurrencyAbbreviation : buildspace.currencyAbbreviation;

            this.structure = [
                {name: 'No.', field: 'count', width:'30px', styles:'text-align:center;', formatter: Formatter.rowCountCellFormatter },
                {name: nls.description, field: 'title', width:'auto', formatter: Formatter.treeCellFormatter},
                {name: nls.originalAmount, field: 'original_total', width:'150px', styles:'text-align: right;', formatter: formatter.unEditableCurrencyCellFormatter},
                {name: nls.total+' '+nls.markup+' (%)', field: 'original_total', width:'100px', styles:'text-align: right;', formatter: formatter.elementTotalMarkupPercentageCellFormatter},
                {name: nls.total+' '+nls.markup+' ('+this.currencySetting+')', field: 'original_total', width:'120px', styles:'text-align: right;', formatter: formatter.elementTotalMarkupAmountCellFormatter },
                {name: nls.overallTotal, field: 'overall_total_after_markup', width:'150px', styles:'text-align: right;', formatter: formatter.unEditableCurrencyCellFormatter},
                {name: '% '+nls.project, field: 'overall_total_after_markup', width:'80px', styles:'text-align: center;', formatter: formatter.projectBreakdownJobPercentageCellFormatter}
            ];
            this.inherited(arguments);
        },
        canSort: function(inSortInfo){
            return false;
        },
        dodblclick: function(e){
            this.onRowDblClick(e);
        },
        onRowDblClick: function(e){
            var self = this, item = self.getItem(e.rowIndex);
            switch(parseInt(item.type)){
                case buildspace.apps.ProjectBuilderReport.ProjectStructureConstants.TYPE_BILL:
                    if(item['bill_status'][0] == buildspace.apps.ProjectBuilderReport.ProjectStructureConstants.BILL_STATUS_OPEN){
                        var options = {
                            billId: item.id,
                            billLayoutSettingId: item.billLayoutSettingId,
                            explorer: this.explorer,
                            rootProject: self.rootProject
                        };
                        this.explorer.initTab(item, options);
                    }
                    break;
                default:
                    break;
            }
        }
    });

    var ProjectBreakdown = declare('buildspace.apps.ProjectBuilderReport.ProjectBreakdown', dijit.layout.BorderContainer, {
        region: "center",
        style:"padding:0px;width:100%;height:100%;",
        gutters: false,
        rootProject: null,
        explorer: null,
        grid: null,
        analysisStatus: null,
        postCreate: function(){
            this.inherited(arguments);
            var self = this;

            var store = new dojo.data.ItemFileWriteStore({
                clearOnClose:true,
                url:"projectBuilder/getProjectBreakdown/id/"+this.rootProject.id
            });

            var toolbar = new dijit.Toolbar({region:"top", style:"border:none;padding:2px;width:100%;"});

            var grid = this.grid = Grid({
                rootProject: this.rootProject,
                explorer: this.explorer,
                store: store
            });

            var sortOptions = ['resourceAnalysis', 'scheduleOfRateAnalysis'],
                menu = new DropDownMenu({ style: "display: none;"}),
                analysisStatus = this.analysisStatus;

            dojo.forEach(sortOptions, function(opt){
                var disabled = true;

                if(opt == 'resourceAnalysis' && analysisStatus.enable_resource_analysis === true) {
                    disabled = false;
                } else if(opt == 'scheduleOfRateAnalysis' && analysisStatus.enable_schedule_of_rate_analysis === true) {
                    disabled = false;
                }

                var menuItem = new MenuItem({
                    id: opt+"-"+self.rootProject.id+"-menuItem",
                    label: nls[opt],
                    disabled: disabled,
                    onClick: function(){
                        buildspace.app.launch({
                            __children: [],
                            icon: "project_analyzer",
                            id: self.rootProject.id+'-project_analyzer',
                            is_app: true,
                            level: 0,
                            sysname: "ProjectAnalyzerReport",
                            title: nls.projectAnalyzer
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
                    label: nls.projectAnalyzer,
                    iconClass: "icon-16-container icon-16-project_analyzer",
                    style:"outline:none!important;",
                    dropDown: menu
                })
            );

            toolbar.addChild(new dijit.ToolbarSeparator());

            toolbar.addChild(
                new Button({
                    label: nls.printSummary,
                    iconClass: "icon-16-container icon-16-print",
                    style:"outline:none!important;",
                    onClick: function() {
                        self.openSelectedBillsPrintingFormDialog();
                    }
                })
            );

            toolbar.addChild(new dijit.ToolbarSeparator());

            toolbar.addChild(
                new dijit.form.Button({
                    label: nls.subPackages,
                    iconClass: "icon-16-container icon-16-file",
                    onClick: function(e){
                        buildspace.app.launch({
                            __children: [],
                            icon: "project_sub_package",
                            id: self.rootProject.id+'-project_sub_package',
                            is_app: true,
                            level: 0,
                            sysname: "SubPackageReport",
                            title: nls.subPackages
                        },{
                            type: buildspace.constants.STATUS_PRETENDER,
                            project: self.rootProject
                        });
                    }
                })
            );

            this.addChild(toolbar);
            this.addChild(new dijit.layout.ContentPane({style: 'width:100%', content:grid, region:'center'}));
        },
        openSelectedBillsPrintingFormDialog: function() {
            var self = this;

            var pb = buildspace.dialog.indeterminateProgressBar({
                title: nls.pleaseWait+'...'
            });

            pb.show();

            return request.get('viewTendererReporting/getPrintingInformation', {
                handleAs: 'json'
            }).then(function(response) {
                var dialog = new PrintPreviewFormDialog({
                    title: nls.projectEstimateSummary,
                    projectId: self.rootProject.id,
                    printURL: 'projectBuilderReport/printProjectEstimateSummary',
                    exportURL: 'projectBuilderReport/exportExcelProjectEstimateSummary',
                    _csrf_token: response._csrf_token
                });

                dialog.show();
                pb.hide();
            }, function(error) {
                console.log(error);
                pb.hide();
            });
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
            if(item.type < buildspace.apps.ProjectBuilderReport.ProjectStructureConstants.TYPE_BILL){
                cellValue =  '<b>'+cellValue+'</b>';
            }
            cellValue = '<div class="treeNode" style="padding-left:'+level+'px;"><div class="treeContent">'+cellValue+'&nbsp;</div></div>';
            return cellValue;
        }
    };

    return ProjectBreakdown;
});