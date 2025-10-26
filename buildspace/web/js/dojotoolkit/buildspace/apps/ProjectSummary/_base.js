define(["dojo/_base/declare",
    'dojo/currency',
    'dojo/number',
    "dojo/keys",
    "dojo/dom-style",
    "dojo/_base/connect",
    'dijit/form/DropDownButton',
    "dijit/DropDownMenu",
    "dijit/MenuItem",
    'dojo/data/ItemFileWriteStore',
    'dojox/grid/EnhancedGrid',
    "dojox/grid/enhanced/plugins/IndirectSelection",
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
    './TableFooterForm',
    './FooterForm',
    './GeneralSettingForm',
    'buildspace/apps/ProjectBuilder/Builder',
    'dojo/i18n!buildspace/nls/ProjectSummary'], function(declare, currency, number, keys, domStyle, connect, DropDownButton, DropDownMenu, MenuItem, ItemFileWriteStore, EnhancedGrid, IndirectSelection, Form, _ManagerMixin, _ManagerNodeMixin, _ManagerValueMixin, _ManagerDisplayMixin, _WidgetBase, _OnDijitClickMixin, _TemplatedMixin, _WidgetsInTemplateMixin, ValidationTextBox, TableFooterForm, FooterForm, GeneralSettingForm, ProjectBuilder, nls){

    var Formatter = {
        referenceCharCellFormatter: function(cellValue, rowIdx){
            return (cellValue && cellValue.length > 0) ? "<b>"+cellValue+"</b>" : "&nbsp;";
        },
        treeCellFormatter: function(cellValue, rowIdx){
            var item = this.grid.getItem(rowIdx);
            var level = parseInt(parseInt(String(item.level)) - 1)*16;
            cellValue = cellValue == null ? '&nbsp': cellValue;

            var fontWeight = item.is_bold[0] ? "font-weight:bold!important;" : "";
            var fontStyle = item.is_italic[0] ? "font-style:italic!important;" : "";
            var textDecoration = item.is_underline[0] ? "text-decoration:underline!important;" : "";

            cellValue = '<div class="treeNode" style="padding-left:'+level+'px;"><div class="treeContent" style="'+fontWeight+''+fontStyle+''+textDecoration+'">'+cellValue+'&nbsp;</div></div>';
            return cellValue;
        },
        currencyCellFormatter: function(cellValue, rowIdx){
            var value = number.parse(cellValue);
            if(isNaN(value) || value == 0 || value == null){
                cellValue = "&nbsp;";
            }else{
                cellValue = currency.format(value);
                cellValue = value >= 0 ? cellValue : '<span style="color:#FF0000">'+cellValue+'</span>';
            }
            return cellValue;
        }
    };

    var SummaryGrid =  declare('buildspace.apps.ProjectSummary.Grid', EnhancedGrid, {
        project: null,
        style: "border-top:none;",
        constructor: function(args){
            this.itemIds = [];
            this.connects = [];

            if(args.project.status_id == buildspace.apps.ProjectSummary.ProjectStatus.STATUS_PRETENDER){
                this.plugins = {indirectSelection: {headerSelector:true, width:"20px", styles:"text-align: center;"}};
            }

            this.inherited(arguments);
        },
        canSort: function(inSortInfo){
            return false;
        },
        postCreate: function(){
            var self = this;
            this.inherited(arguments);

            if(this.project.status_id == buildspace.apps.ProjectSummary.ProjectStatus.STATUS_PRETENDER){
                this._connects.push(connect.connect(this, 'onCellClick', function(e){
                    self.selectItem(e);
                }));

                this._connects.push(connect.connect(this.rowSelectCell, 'toggleAllSelection', function(newValue){
                    self.toggleAllSelection(newValue);
                }));
            }
        },
        doApplyCellEdit: function(val, rowIdx, inAttrName){
            var self = this,
                item = self.getItem(rowIdx),
                store = self.store,
                pb = buildspace.dialog.indeterminateProgressBar({
                    title:nls.pleaseWait+'...'
                });

            if(val !== item[inAttrName][0]){
                var params = {
                    id: item.id,
                    attr_name: inAttrName,
                    val: val,
                    _csrf_token: item._csrf_token ? item._csrf_token : null
                };

                var cell = self.getCellByField(inAttrName);

                pb.show().then(function(){
                    dojo.xhrPost({
                        url: "projectSummary/billStyleReferenceCharUpdate",
                        content: params,
                        handleAs: 'json',
                        load: function(resp) {
                            if(resp.success){
    
                                for(var property in resp.data){
                                    if(item.hasOwnProperty(property) && property != store._getIdentifierAttribute()){
                                        store.setValue(item, property, resp.data[property]);
                                    }
                                }
    
                                store.save();
    
                                window.setTimeout(function(){
                                    self.focus.setFocusIndex(rowIdx, cell.index);
                                }, 10);
    
                                pb.hide();
                            }
                        },
                        error: function(error) {
                            pb.hide();
                        }
                    });
                });
            }

            this.inherited(arguments);
        },
        doCancelEdit: function(inRowIndex){
            this.inherited(arguments);
            this.views.renormalizeRow(inRowIndex);
            this.scroller.rowHeightChanged(inRowIndex, true);
        },
        updateStyle: function(style){
            var self = this,
                store = this.store,
                pb = buildspace.dialog.indeterminateProgressBar({
                    title:nls.pleaseWait+'...'
                });

            if(this.itemIds.length > 0){
                pb.show().then(function(){
                    var item = self.getItem(0);//we do this just to get csrf token from item

                    dojo.xhrPost({
                        url: "projectSummary/billFontStyleUpdate",
                        content: {pid: self.project.id, style: style, ids: [self.itemIds], _csrf_token: item._csrf_token},
                        handleAs: 'json',
                        load: function(resp) {
                            if(resp.success){

                                dojo.forEach(resp.data, function(node){
                                    store.fetchItemByIdentity({ 'identity' : node.id,  onItem : function(bill){
                                        store.setValue(bill, "is_"+style, node.val);
                                    }});
                                });

                                store.save();

                                self.selection.deselectAll();
                                self.itemIds = [];
                                self.disableToolbarButtons(true);

                                pb.hide();
                            }
                        },
                        error: function(error) {
                            self.selection.deselectAll();
                            self.itemIds = [];
                            self.disableToolbarButtons(true);

                            pb.hide();
                        }
                    });
                });
            }
        },
        selectItem: function(e){
            var rowIndex = e.rowIndex,
                newValue = this.selection.selected[rowIndex],
                item = this.getItem(rowIndex);
            if(item){
                this.pushItemIdIntoGridArray(item, newValue);
            }
        },
        pushItemIdIntoGridArray: function(item, select){
            var grid = this;
            var idx = dojo.indexOf(grid.itemIds, parseInt(String(item.id)));
            if(select && parseInt(String(item.id)) > 0){
                if(idx == -1){
                    grid.itemIds.push(parseInt(String(item.id)));
                }
            }else{
                if(idx != -1){
                    grid.itemIds.splice(idx, 1);
                }
            }

            var isDisable = grid.itemIds.length > 0 ? false : true;
            this.disableToolbarButtons(isDisable);
        },
        toggleAllSelection:function(checked){
            var grid = this, selection = grid.selection;
            if(checked){
                selection.selectRange(0, grid.rowCount-1);
                grid.itemIds = [];
                grid.store.fetch({
                    onComplete: function (items) {
                        dojo.forEach(items, function (item, index) {
                            if(parseInt(String(item.id)) > 0){
                                grid.itemIds.push(parseInt(String(item.id)));
                            }
                        });
                    }
                });
            }else{
                selection.deselectAll();
                grid.itemIds = [];
            }

            var isDisable = grid.itemIds.length > 0 ? false : true;
            this.disableToolbarButtons(isDisable);
        },
        disableToolbarButtons: function(isDisable){
            dijit.byId('ProjectSummary-'+this.project.id+'_StyleBold-button')._setDisabledAttr(isDisable);
            dijit.byId('ProjectSummary-'+this.project.id+'_StyleItalic-button')._setDisabledAttr(isDisable);
            dijit.byId('ProjectSummary-'+this.project.id+'_StyleUnderline-button')._setDisabledAttr(isDisable);
        },
        destroy: function(){
            this.inherited(arguments);
            dojo.forEach(this._connects, connect.disconnect);
            delete this._connects;
        }
    });

    var ExportToExcelForm = declare('buildspace.apps.ProjectSummary.ExportToExcelForm', [Form,
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
        tenderAlternative: null,
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
                var exportExcelUrl;
                if(this.tenderAlternative){
                    exportExcelUrl = 'TenderAlternativeProjectSummaryXls/'+parseInt(String(this.tenderAlternative.id))+'/'+String(this.project._csrf_token)+'/'+filename;
                }else{
                    exportExcelUrl = 'ProjectSummaryXls/'+parseInt(String(this.project.id))+'/'+String(this.project._csrf_token)+'/'+filename;
                }

                window.open(exportExcelUrl, '_self');

                if(this.dialogWidget){
                    this.dialogWidget.hide();
                }
            }
        }
    });

    var ExportExcelDialog = declare('buildspace.apps.ProjectSummary.ExportExcelDialog', dijit.Dialog, {
        style:"padding:0px;margin:0px;",
        title: nls.exportToExcel,
        project: null,
        tenderAlternative: null,
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
                tenderAlternative: this.tenderAlternative,
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

    return declare('buildspace.apps.ProjectSummary', buildspace.apps._App, {
        win: null,
        project: null,
        tenderAlternative: null,
        projectSummaryData: null,
        init: function(args){
            var project = this.project = args.project,
                tenderAlternative = this.tenderAlternative = args.tenderAlternative,
                projectSummaryData = this.projectSummaryData = args.projectSummaryData,
                mainContainer = new dijit.layout.BorderContainer({
                    style:"padding:0;margin:0;width:100%;height:100%;overflow:hidden;",
                    gutters: false,
                    liveSplitters: true
                }),
                viewContainer = new dijit.layout.BorderContainer({
                    title: nls.summaryView,
                    style:"padding:0;margin:0;width:100%;height:100%;overflow:hidden;",
                    gutters: false,
                    liveSplitters: true
                }),
                generalSettingContainer = new dijit.layout.BorderContainer({
                    title: nls.generalSettings,
                    style:"padding:0;margin:0;width:100%;height:100%;overflow:hidden;",
                    gutters: false,
                    liveSplitters: true
                }),
                footerContainer = new dijit.layout.BorderContainer({
                    title: nls.footerText,
                    style:"padding:0;margin:0;width:100%;height:100%;overflow:hidden;",
                    gutters: false,
                    liveSplitters: true
                });

            var title, summaryGridUrl, printPdfUrl;
            if(tenderAlternative){
                title = nls.ProjectBuilder + ' > ' + nls.projectSummary+' ('+buildspace.truncateString(tenderAlternative.title, 60)+') - '+buildspace.truncateString(project.title, 100);
                summaryGridUrl = 'projectSummary/getTenderAlternativeBills/id/'+parseInt(String(tenderAlternative.id));
                printPdfUrl = 'TenderAlternativeProjectSummaryPdf/'+parseInt(String(tenderAlternative.id))+'/'+String(project._csrf_token);
            }else{
                title = nls.ProjectBuilder + ' > ' + nls.projectSummary+' - '+buildspace.truncateString(project.title, 100);
                summaryGridUrl = "projectSummary/getBills/pid/"+parseInt(String(project.id));
                printPdfUrl = 'ProjectSummaryPdf/'+parseInt(String(project.id))+'/'+String(project._csrf_token);
            }
            var win = this.win = new buildspace.widget.Window({
                    title: title,
                    onClose: dojo.hitch(this, "kill")
                }),
                tabArea = new dijit.layout.TabContainer({
                    region: "center",
                    style:"width:100%;height:100%;padding:0;margin:0;outline:none!important;"
                }),
                refCharEditable = project.status_id == buildspace.apps.ProjectSummary.ProjectStatus.STATUS_PRETENDER ? true : false,
                summaryGrid = new SummaryGrid({
                    region: "center",
                    project: project,
                    store: new ItemFileWriteStore({
                        url: summaryGridUrl,
                        clearOnClose: true
                    }),
                    structure: [
                        {name: nls.item, field: 'reference_char', width:'80px', styles:'text-align:center;', formatter: Formatter.referenceCharCellFormatter, editable: refCharEditable, cellType:'buildspace.widget.grid.cells.TextBox' },
                        {name: nls.description, field: 'title', width:'auto', formatter: Formatter.treeCellFormatter },
                        {name: nls.page, field: 'page', width:'150px', styles:'text-align:center;' },
                        {name: nls.amount+" ("+projectSummaryData.currency_code+")", field: 'amount', width:'200px', styles:'text-align:center;', formatter:Formatter.currencyCellFormatter}
                    ]
                }),
                mainToolbar = new dijit.Toolbar({region:"top", style:"outline:none!important;border-top:0px;padding:2px;overflow:hidden;"});

            mainToolbar.addChild(
                new dijit.form.Button({
                    label: nls.backToProjectBuilder,
                    iconClass: "icon-16-container icon-16-directional_left",
                    style:"outline:none!important;",
                    onClick: dojo.hitch(this, 'openBuilderWin', project)
                })
            );
            mainToolbar.addChild(new dijit.ToolbarSeparator());

            var printOptions = ['withPrice', 'withoutPrice'],
                printMenu = new DropDownMenu({ style: "display: none;"});
            
            
            dojo.forEach(printOptions, function(opt){
                printMenu.addChild(new MenuItem({
                    label: nls[opt],
                    onClick: function(){
                        window.open(printPdfUrl+'/'+opt, '_blank');
                        return window.focus();
                    }
                }));
            });

            mainToolbar.addChild(
                new DropDownButton({
                    label: nls.printToPdf,
                    iconClass: "icon-16-container icon-16-print",
                    style:"outline:none!important;",
                    dropDown: printMenu
                })
            );

            mainToolbar.addChild(new dijit.ToolbarSeparator());

            mainToolbar.addChild(
                new dijit.form.Button({
                    label: nls.exportToExcel,
                    iconClass: "icon-16-container icon-16-spreadsheet",
                    style:"outline:none!important;",
                    onClick: function(){
                        ExportExcelDialog({
                            project: project,
                            tenderAlternative: tenderAlternative
                        }).show();
                    }
                })
            );

            if(project.status_id == buildspace.apps.ProjectSummary.ProjectStatus.STATUS_PRETENDER){
                var toolbar = new dijit.Toolbar({region:"top", style:"outline:none!important;border-top:0px;border-bottom:0px;padding:2px;overflow:hidden;"});

                toolbar.addChild(
                    new dijit.form.Button({
                        id: 'ProjectSummary-'+project.id+'_StyleBold-button',
                        label: nls.bold,
                        iconClass: "icon-16-container icon-16-bold",
                        style:"outline:none!important;",
                        disabled: true,
                        onClick: dojo.hitch(summaryGrid, 'updateStyle', "bold")
                    })
                );
                toolbar.addChild(new dijit.ToolbarSeparator());
                toolbar.addChild(
                    new dijit.form.Button({
                        id: 'ProjectSummary-'+project.id+'_StyleItalic-button',
                        label: nls.italic,
                        iconClass: "icon-16-container icon-16-italic",
                        style:"outline:none!important;",
                        disabled: true,
                        onClick: dojo.hitch(summaryGrid, 'updateStyle', "italic")
                    })
                );
                toolbar.addChild(new dijit.ToolbarSeparator());
                toolbar.addChild(
                    new dijit.form.Button({
                        id: 'ProjectSummary-'+project.id+'_StyleUnderline-button',
                        label: nls.underlined,
                        iconClass: "icon-16-container icon-16-underlined",
                        style:"outline:none!important;",
                        disabled: true,
                        onClick: dojo.hitch(summaryGrid, 'updateStyle', "underline")
                    })
                );

                viewContainer.addChild(toolbar);
            }

            viewContainer.addChild(summaryGrid);

            viewContainer.addChild(new TableFooterForm({
                project: project,
                formValues: projectSummaryData.table_form,
                totalCost: projectSummaryData.total_cost
            }));

            footerContainer.addChild(new FooterForm({
                project: project,
                formValues: projectSummaryData.footer_form
            }));

            generalSettingContainer.addChild(
                new GeneralSettingForm({
                    project: project,
                    formValues: projectSummaryData.general_setting_form
                })
            );

            tabArea.addChild(viewContainer);
            tabArea.addChild(footerContainer);
            tabArea.addChild(generalSettingContainer);

            mainContainer.addChild(mainToolbar);
            mainContainer.addChild(tabArea);

            mainContainer.startup();

            win.addChild(mainContainer);
            win.show();
            win.startup();

        },
        openBuilderWin: function(project){
            this.kill();
            this.project = project;
            this.win = new buildspace.widget.Window({
                title: nls.ProjectBuilder + ' > ' + buildspace.truncateString(project.title, 100) + ' (' + nls.status + '::' + project.status[0].toUpperCase() + ')',
                onClose: dojo.hitch(this, "kill")
            });

            this.win.addChild(ProjectBuilder({
                project: project
            }));

            this.win.show();
            this.win.startup();
        },
        kill: function(){
            if (typeof(this.win) != "undefined"){
                this.win.close();
            }
        }
    });
});