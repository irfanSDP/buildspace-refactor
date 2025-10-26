define('buildspace/apps/Editor/ProjectBreakdown',[
    'dojo/_base/declare',
    "dojo/aspect",
    "dojo/keys",
    "dojo/dom-style",
    'dojo/_base/lang',
    'dojo/_base/connect',
    'dojox/grid/EnhancedGrid',
    "dijit/TooltipDialog",
    "dijit/popup",
    'buildspace/widget/grid/cells/Formatter',
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
    './PrintFinalBQDialog',
    'buildspace/apps/Editor/TenderAlternativeListDialog',
    'dojo/i18n!buildspace/nls/Tendering',
    'dojo/i18n!buildspace/nls/Common'
], function(declare, aspect, keys, domStyle, lang, connect, EnhancedGrid, TooltipDialog, popup, GridFormatter, Form, _WidgetBase, _OnDijitClickMixin, _TemplatedMixin, _WidgetsInTemplateMixin, ValidationTextBox, Menu, DropDownButton, DropDownMenu, MenuItem, PopupMenuItem, PrintFinalBQDialog, TenderAlternativeListDialog, nls, commonNls){

    var Grid = declare('buildspace.apps.Editor.ProjectBreakdownGrid', EnhancedGrid, {
        project: null,
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
            var tooltipDialog = null;

            this._connects.push(connect.connect(this, 'onCellMouseOver', function(e) {
                var colField = e.cell.field,
                    rowIndex = e.rowIndex,
                    item = this.getItem(rowIndex);
                
                // will show tooltip for formula, if available
                if (!item || (!item.hasOwnProperty('addendum_version')) || colField != 'addendum_version' || typeof item['addendum_version'] === 'undefined' || ! parseInt(item.addendum_version)) {
                    return;
                }

                if(tooltipDialog === null) {
                    // Call the asynchronous xhrGet
                    var deferred = dojo.xhrGet({
                        url: "addendumInfoByBill/"+String(item.id),
                        handleAs: "json",
                        sync:true,
                        preventCache: true
                    });
                    
                    // Now add the callbacks
                    deferred.then(function(data){
                        if(data.length){
                            var content = '<table class="buildspace-table"><thead><tr>'
                            + '<th class="gridCell" style="text-align:center;padding:2px;">'+nls.version+'</th>'
                            + '<th class="gridCell" style="text-align:center;padding:2px;">'+nls.revision+'</th>'
                            + '</tr><tbody>';
                            for (var i = 0; i < data.length; i++){
                                content += '<tr><td class="gridCell" style="text-align:center;">'+data[i].version + '</td><td class="gridCell" style="text-align:center;padding-left:4px;padding-right:4px;">'+ data[i].revision +'</td></tr>';
                            }
                            content +='</tbody></table>';
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
            var item = this.getItem(e.rowIndex);
            switch(parseInt(item.type)){
                case buildspace.apps.Editor.ProjectStructureConstants.TYPE_BILL:
                    if(parseInt(String(item['bill_status'])) == buildspace.apps.Editor.ProjectStructureConstants.BILL_STATUS_OPEN){
                        this.workArea.initTab(item, {
                            bill: item,
                            billLayoutSettingId: item.billLayoutSettingId,
                            projectBreakdownGrid: this,
                            project: this.project
                        });
                    }
                    break;
                default:
                    break;
            }
        },
        reload: function(){
            this.store.close();
            this._refresh();
        }
    });

    var Formatter = {
        rowCountCellFormatter: function(cellValue, rowIdx){
            return cellValue > 0 ? cellValue : '';
        },
        treeCellFormatter: function(cellValue, rowIdx){
            var item = this.grid.getItem(rowIdx);
            var level = parseInt(String(item.level))*16;
            cellValue = cellValue == null ? '&nbsp': cellValue;
            if(parseInt(String(item.type)) < buildspace.apps.Editor.ProjectStructureConstants.TYPE_BILL){
                cellValue =  '<b>'+cellValue+'</b>';
            }
            cellValue = '<div class="treeNode" style="padding-left:'+level+'px;"><div class="treeContent">'+cellValue+'&nbsp;</div></div>';
            return cellValue;
        }
    };

    var GridContainer =  declare('buildspace.apps.Editor.TenderAlternative.GridContainer', dijit.layout.BorderContainer, {
        pageId: 'page-00',
        style: "padding:0px;width:100%;height:100%;border:0px!important;",
        gutters: false,
        stackContainerTitle: '',
        project: null,
        workArea: null,
        mainContainer: null,
        tenderAlternative: null,
        gridOpts: {},
        postCreate: function(){
            var project = this.project;
            this.inherited(arguments);
            lang.mixin(this.gridOpts, { project: project, workArea: this.workArea, tenderAlternative: this.tenderAlternative, region: "center", borderContainerWidget: this });

            if(this.tenderAlternative){
                this.grid = new Grid(this.gridOpts);
            }else{
                this.grid = new EnhancedGrid(this.gridOpts);
            }

            var grid = this.grid;

            var toolbar = new dijit.Toolbar({region:"top", style:"outline:none!important;border:none;padding:2px;width:100%;"});

            if(!this.project.disable_tender_rates_submission){
                toolbar.addChild(
                    new dijit.form.Button({
                        label: nls.submitTender,
                        iconClass: "icon-16-container icon-16-export",
                        style:"outline:none!important;",
                        onClick: lang.hitch(this.mainContainer, "submitTender")
                    })
                );
                toolbar.addChild(new dijit.ToolbarSeparator());
            }

           var projectSummaryOptions = ['printWithPrice', 'printWithoutPrice'];

            var menu = new DropDownMenu({
                style: "display: none;"
            });

            var self = this;
            dojo.forEach(projectSummaryOptions, function(opt) {
                var withPrice;

                if ( opt === 'printWithoutPrice' ) {
                    withPrice = 0;
                } else {
                    withPrice = 1;
                }

                return menu.addChild(new MenuItem({
                    label: nls[opt],
                    onClick: dojo.hitch(self, '_printProjectSummary', withPrice)
                }));
            });

            toolbar.addChild(new DropDownButton({
                label: nls.projectSummary,
                iconClass: "icon-16-container icon-16-list",
                style:"outline:none!important;",
                dropDown: menu
            }));

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
                    label: nls.reload,
                    iconClass: "icon-16-container icon-16-reload",
                    style:"outline:none!important;",
                    onClick: function(e){
                        grid.store.close();
                        grid._refresh();
                    }
                })
            );
            var eprojectUrl = this.project.eproject_url;
            toolbar.addChild(new dijit.ToolbarSeparator());
            toolbar.addChild(
                new dijit.form.Button({
                    label: 'eProject',
                    iconClass: 'icon-16-buildspace_eproject',
                    style:"outline:none!important;",
                    onClick: function(){
                        window.open(eprojectUrl, '_self');
                    }
                })
            );
            
            this.addChild(toolbar);
            this.addChild(grid);

            var container = dijit.byId('editorTenderAlternativeGrid'+String(this.project.id)+'-stackContainer');
            if(container){
                var node = document.createElement("div");
                container.addChild(new dojox.layout.ContentPane( {
                    title: buildspace.truncateString(this.stackContainerTitle, 60),
                    content: this,
                    id: this.pageId,
                    grid: grid
                }, node));
                container.selectChild(this.pageId);
            }
        },
        _printProjectSummary: function(withPrice){
            var self = this;
            var pb = buildspace.dialog.indeterminateProgressBar({
                title: nls.pleaseWait + '...'
            });
            var checkTenderAlternativeXhr = dojo.xhrGet({
                url: "getTenderAlternatives/"+String(this.project.id),
                handleAs: "json"
            });

            pb.show().then(function(){
                checkTenderAlternativeXhr.then(function(r){
                    pb.hide();
                    if(r.items.length > 2){
                        //getTenderAlternatives will return project name and last row along with tender alternative records. so we exclude 2 returned items from getTenderAlternatives to check if there is  any tender alternative records
                        var d = new TenderAlternativeListDialog({
                            project: self.project,
                            workArea: self.workArea,
                            type: 'printProjectSummary',
                            opt: {withPrice: withPrice}
                        });
                        d.show();
                    }else{
                        window.open('projectSummary/'+self.project.id+'/'+self.project._csrf_token+'/'+withPrice, '_blank');
                        return window.focus();
                    }
                });
            });

            
        },
        _printFinalBQ: function(withPrice){
            var self = this;
            var pb = buildspace.dialog.indeterminateProgressBar({
                title: nls.pleaseWait + '...'
            });
            var checkTenderAlternativeXhr = dojo.xhrGet({
                url: "getTenderAlternatives/"+String(this.project.id),
                handleAs: "json"
            });

            pb.show().then(function(){
                checkTenderAlternativeXhr.then(function(r){
                    pb.hide();
                    if(r.items.length > 2){
                        //getTenderAlternatives will return project name and last row along with tender alternative records. so we exclude 2 returned items from getTenderAlternatives to check if there is  any tender alternative records
                        var d = new TenderAlternativeListDialog({
                            project: self.project,
                            workArea: self.workArea,
                            type: 'printFinalBQ',
                            opt: {withPrice: withPrice}
                        });
                        d.show();
                    }else{
                        var t = withPrice ? nls.withPrice : nls.withoutPrice;
                        var d = new PrintFinalBQDialog({
                            title: nls.printFinalBQ+' ('+t+')',
                            project: self.project,
                            withPrice: withPrice
                        });

                        d.show();
                    }
                });
            });
        }
    });

    return declare('buildspace.apps.Editor.ProjectBreakdown', dijit.layout.BorderContainer, {
        region: "center",
        style:"padding:0px;width:100%;height:100%;",
        gutters: false,
        project: null,
        workArea: null,
        grid: null,
        postCreate: function(){
            this.inherited(arguments);

            var grid, project = this.project;

            if(parseInt(String(project.has_tender_alternatives))){
                grid = this.grid = this.createTenderAlternativeGrid();

                dojo.subscribe('editorTenderAlternativeGrid' + String(project.id) + '-stackContainer-selectChild', "", function(page) {
                    var widget = dijit.byId('editorTenderAlternativeGrid' + String(project.id) + '-stackContainer');
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

            }else{
                grid = this.grid = this.createBillGrid();

                toolbar = new dijit.Toolbar({region:"top", style:"outline:none!important;border:none;padding:2px;width:100%;background:#f7f7f7;"});

                if(!project.disable_tender_rates_submission){
                    toolbar.addChild(
                        new dijit.form.Button({
                            id: project.id+'SubmitTenderRow-button',
                            label: nls.submitTender,
                            iconClass: "icon-16-container icon-16-export",
                            style:"outline:none!important;",
                            onClick: lang.hitch(this, "submitTender")
                        })
                    );
                    toolbar.addChild(new dijit.ToolbarSeparator());
                }

                var projectSummaryOptions = ['printWithPrice', 'printWithoutPrice'];

                var menu = new DropDownMenu({
                    style: "display: none;"
                });

                var self = this;
                dojo.forEach(projectSummaryOptions, function(opt) {
                    var withPrice;

                    if ( opt === 'printWithoutPrice' ) {
                        withPrice = 0;
                    } else {
                        withPrice = 1;
                    }

                    return menu.addChild(new MenuItem({
                        label: nls[opt],
                        onClick: function() {
                            window.open('projectSummary/'+project.id+'/'+project._csrf_token+'/'+withPrice, '_blank');
                            return window.focus();
                        }
                    }));
                });

                toolbar.addChild(new DropDownButton({
                    label: nls.projectSummary,
                    iconClass: "icon-16-container icon-16-list",
                    style:"outline:none!important;",
                    dropDown: menu
                }));

                var printFinalBillOptions = ['withPrice', 'withoutPrice'];

                var printFinalBillMenu = new DropDownMenu({
                    style: "display: none;"
                });

                dojo.forEach(printFinalBillOptions, function(opt) {
                    var withPrice =  ( opt === 'withPrice' );

                    return printFinalBillMenu.addChild(new MenuItem({
                        label: nls[opt],
                        onClick: function() {
                            var t = withPrice ? nls.withPrice : nls.withoutPrice;
                            var d = new PrintFinalBQDialog({
                                title: nls.printFinalBQ+' ('+t+')',
                                project: project,
                                withPrice: withPrice
                            });

                            d.show();
                        }
                    }));
                });

                toolbar.addChild(new dijit.ToolbarSeparator());
                toolbar.addChild(new DropDownButton({
                    id: project.id+'PrintFinalBQ-button',
                    label: nls.printFinalBQ,
                    iconClass: "icon-16-container icon-16-print",
                    style:"outline:none!important;",
                    dropDown: printFinalBillMenu
                }));

                toolbar.addChild(new dijit.ToolbarSeparator());
                toolbar.addChild(
                    new dijit.form.Button({
                        id: project.id+'ReloadGridRow-button',
                        label: nls.reload,
                        iconClass: "icon-16-container icon-16-reload",
                        style:"outline:none!important;",
                        onClick: function(e){
                            grid.reload();
                        }
                    })
                );
                var eprojectUrl = project.eproject_url;
                toolbar.addChild(new dijit.ToolbarSeparator());
                toolbar.addChild(
                    new dijit.form.Button({
                        id: "eproject-link",
                        label: commonNls.goToBuildspaceEProject,
                        iconClass: 'icon-24-buildspace_eproject',
                        style:"outline:none!important;",
                        onClick: function(){
                            window.open(eprojectUrl, '_blank');
                        }
                    })
                );

                this.addChild(toolbar);
                this.addChild(grid);
            }
        },
        submitTender: function(){
            var pb = buildspace.dialog.indeterminateProgressBar({
                title:nls.pleaseWait+'...'
            }),
            params = {
                pid:this.project.id,
                _csrf_token: this.project._csrf_token
            };
            var msg;

            pb.show().then(function(){
                dojo.xhrPost({
                    url: 'billManager/submitTender',
                    content: params,
                    handleAs: 'json',
                    load: function(resp) {
                        if(resp.success){
                            msg = nls.editorSuccessSubmitTenderMsg;
                        }else{
                            msg = resp.errorMsg;
                        }
                        pb.hide();

                        buildspace.dialog.alert(nls.submitTender, msg, 120, 320);
                    },
                    error: function(error) {
                        pb.hide();
                    }
                });
            });
        },
        createBillGrid: function(){
            var formatter = new GridFormatter(),
                currencySetting = (buildspace.billCurrencyAbbreviation) ? buildspace.billCurrencyAbbreviation : buildspace.currencyAbbreviation;

            var structure = [
                {name: 'No.', field: 'count', width:'30px', styles:'text-align:center;', formatter: Formatter.rowCountCellFormatter },
                {name: nls.description, field: 'title', width:'auto', formatter: Formatter.treeCellFormatter}
            ];

            if(parseInt(String(this.project.has_addendum))){
                structure.push({name: nls.addendum, field: 'addendum_version', styles:'text-align: center;', width:'68px', formatter: formatter.addendumInfoCellFormatter, noresize: true});
            }

            var otherColumns = [
                {name: nls.overallTotal, field: 'overall_total', width:'150px', styles:'text-align: right;', formatter: formatter.unEditableCurrencyCellFormatter},
                {name: '% '+nls.project, field: 'overall_total', width:'80px', styles:'text-align: center;', formatter: formatter.projectBreakdownJobPercentageCellFormatter}
            ];

            dojo.forEach(otherColumns,function(column){
                structure.push(column);
            });

            return new Grid({
                project: this.project,
                workArea: this.workArea,
                currencySetting: currencySetting,
                structure: structure,
                store: new dojo.data.ItemFileWriteStore({
                    clearOnClose:true,
                    url:"projectBreakdown/"+parseInt(String(this.project.id))
                })
            });
        },
        createTenderAlternativeGrid: function(){
            var stackContainer = dijit.byId('editorTenderAlternativeGrid' + String(this.project.id) + '-stackContainer');
            if(stackContainer) {
                dijit.byId('editorTenderAlternativeGrid' + String(this.project.id) + '-stackContainer').destroyRecursive();
            }
            stackContainer = this.stackContainer = new dijit.layout.StackContainer({
                style: 'border:0px;width:100%;height:100%;',
                region: "center",
                id: 'editorTenderAlternativeGrid' + String(this.project.id) + '-stackContainer'
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
                mainContainer: this,
                pageId: 'tender_alternative-page-' + String(this.project.id),
                id: 'tender_alternative-page-container-' + String(this.project.id),
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
                        name: nls.overallTotal,
                        field: 'overall_total',
                        width: '150px',
                        styles: 'text-align: right;',
                        formatter: formatter.unEditableCurrencyCellFormatter
                    }],
                    store: new dojo.data.ItemFileWriteStore({
                        clearOnClose: true,
                        url: "getTenderAlternatives/" + this.project.id
                    }),
                    onRowDblClick: function(e) {
                        var self = this,
                            item = self.getItem(e.rowIndex);
                        if(parseInt(String(item.id)) > 0 && String(item.title) !== null && String(item.title) !== '') {
                            me.createTenderAlternativeBillGrid(item, grid);
                        }
                    }
                }
            });

            var controller = new dijit.layout.StackController({
                region: "top",
                containerId: 'editorTenderAlternativeGrid' + String(this.project.id) + '-stackContainer'
            });

            this.addChild(stackContainer);

            this.addChild(new dijit.layout.ContentPane({
                style: "padding:0px;overflow:hidden;",
                baseClass: 'breadCrumbTrail',
                region: 'top',
                id: 'editorTenderAlternativeGrid'+String(this.project.id)+'-controllerPane',
                content: controller
            }));
        },
        createTenderAlternativeBillGrid: function(item, tenderAlternativeGrid){

            var formatter = new GridFormatter(),
                currencySetting = (buildspace.billCurrencyAbbreviation) ? buildspace.billCurrencyAbbreviation : buildspace.currencyAbbreviation;

            var structure = [
                {name: 'No.', field: 'count', width:'30px', styles:'text-align:center;', formatter: Formatter.rowCountCellFormatter },
                {name: nls.description, field: 'title', width:'auto', formatter: Formatter.treeCellFormatter}
            ];

            if(parseInt(String(this.project.has_addendum))){
                structure.push({name: nls.addendum, field: 'addendum_version', styles:'text-align: center;', width:'68px', formatter: formatter.addendumInfoCellFormatter, noresize: true});
            }

            var otherColumns = [
                {name: nls.overallTotal, field: 'overall_total', width:'150px', styles:'text-align: right;', formatter: formatter.unEditableCurrencyCellFormatter},
                {name: '% '+nls.project, field: 'overall_total', width:'80px', styles:'text-align: center;', formatter: formatter.projectBreakdownJobPercentageCellFormatter}
            ];

            dojo.forEach(otherColumns,function(column){
                structure.push(column);
            });

            return new GridContainer({
                stackContainerTitle: String(item.title),
                project: this.project,
                workArea: this.workArea,
                mainContainer: this,
                tenderAlternative: item,
                pageId: String(item.id)+'-bill-page-' + String(this.project.id),
                id: String(item.id)+'-bill-page-container-' + String(this.project.id),
                gridOpts: {
                    id: String(this.project.id)+"-"+String(item.id)+"-tenderAlternative-billListGrid",
                    currencySetting: currencySetting,
                    structure: structure,
                    store: new dojo.data.ItemFileWriteStore({
                        clearOnClose: true,
                        url: "getTenderAlternativeBills/" + parseInt(String(item.id))
                    })
                }
            });
        }
    });
});
