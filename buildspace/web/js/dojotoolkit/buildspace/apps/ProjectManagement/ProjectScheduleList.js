define('buildspace/apps/ProjectManagement/ProjectScheduleList',[
    'dojo/_base/declare',
    'dojo/_base/lang',
    "dojo/dom-style",
    'dojox/grid/EnhancedGrid',
    'dijit/PopupMenuItem',
    "dijit/MenuSeparator",
    'dojo/_base/event',
    'dojo/keys',
    'buildspace/widget/grid/cells/Formatter',
    'dijit/form/DropDownButton',
    "dijit/DropDownMenu",
    "dijit/MenuItem",
    "./ExportProjectScheduleDialog",
    "./ProjectScheduleForm",
    'dojo/i18n!buildspace/nls/ProjectManagement'
], function(declare, lang, domStyle, EnhancedGrid, PopupMenuItem, MenuSeparator, evt, keys, GridFormatter, DropDownButton, DropDownMenu, MenuItem, ExportProjectScheduleDialog, ProjectScheduleForm, nls){

    var Grid = declare('buildspace.apps.ProjectManagement.ProjectScheduleListGrid', EnhancedGrid, {
        project: null,
        workArea: null,
        style: "border:none;",
        region: 'center',
        keepSelection: true,
        rowSelector: '0px',
        currencySetting: buildspace.currencyAbbreviation,
        canSort: function(inSortInfo){
            return false;
        },
        postCreate: function(){
            var self = this;
            this.inherited(arguments);

            this.on('RowClick', function(e) {
                var item = self.getItem(e.rowIndex);
                if(item && item.id > 0) {
                    self.disableToolbarButtons(false);
                }else{
                    self.disableToolbarButtons(true);
                }
            });
        },
        dodblclick: function(e){
            this.onRowDblClick(e);
        },
        onRowDblClick: function(e){
            this.inherited(arguments);
            var item = this.getItem(e.rowIndex);
            if(item.id[0] > 0){
                var self = this;
                var def = dojo.xhrGet({
                    url: "projectManagement/getNonWorkingDays",
                    content: {
                        id: item.id[0]
                    },
                    handleAs: "json"
                });

                def.then(function(resp){
                    buildspace.app.launch({
                        __children: [],
                        icon: "project_sub_package",
                        id: self.project.id+'-'+item.id[0]+'-project_management-Gantt',
                        is_app: true,
                        level: 0,
                        sysname: "Gantt",
                        title: 'Gantt'
                    },{
                        project: self.project,
                        projectSchedule: item,
                        nonWorkingDays: resp
                    });
                });
            }
        },
        deleteRow: function(item){
            var self = this,
                pb = buildspace.dialog.indeterminateProgressBar({
                    title:nls.deleting+'. '+nls.pleaseWait+'...'
                });

            var onYes = function(){
                pb.show().then(function(){
                    dojo.xhrPost({
                        url: 'projectManagement/projectScheduleDelete',
                        content: {id: item.id, _csrf_token: item._csrf_token},
                        handleAs: 'json',
                        load: function(resp) {
                            if(resp.success){
                                var tac = self.workArea.getChildren();
                                for(var i in tac){
                                    if(typeof tac[i].pane_info != "object") continue;
                                    if(tac[i].pane_info.id == item.id+'-project_schedule_tab'){
                                        self.workArea.removeChild(tac[i]);
                                        break;
                                    }
                                }
                                self.reload();
                            }
                            pb.hide();
                            self.selection.clear();
                            self.disableToolbarButtons(true);
                        },
                        error: function(error) {
                            pb.hide();
                            self.selection.clear();
                            self.disableToolbarButtons(true);
                        }
                    });
                });
            };

            buildspace.dialog.confirm(nls.confirmation,nls.deleteProjectScheduleAndAllData,90,310, onYes);
        },
        disableToolbarButtons: function(isDisable) {
            var deleteRowBtn = dijit.byId('ProjectManagement'+this.project.id+'-ScheduleListDeleteRow-button');
            var editRowBtn = dijit.byId('ProjectManagement'+this.project.id+'-ScheduleListEditRow-button');
            var exportRowBtn = dijit.byId('ProjectManagement'+this.project.id+'-ExportRow-button');

            if(deleteRowBtn)
                deleteRowBtn._setDisabledAttr(isDisable);

            if(editRowBtn)
                editRowBtn._setDisabledAttr(isDisable);

            if(exportRowBtn)
                exportRowBtn._setDisabledAttr(isDisable);
        },
        reload: function(){
            this.store.close();
            this._refresh();
        }
    });

    var Formatter = {
        typeCellFormatter: function(cellValue, rowIdx){
            return buildspace.getProjectScheduleTypeText(parseInt(cellValue));
        }
    };

    var ProjectScheduleFormDialog = declare('buildspace.apps.ProjectManagement.ProjectScheduleFormDialog', dijit.Dialog, {
        style:"padding:0;margin:0;",
        title: null,
        project: null,
        projectSchedule: null,
        subPackage: null,
        buildRendering: function(){
            var form = this.createForm();
            form.startup();
            this.content = form;

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
        createForm: function(){
            var borderContainer = new dijit.layout.BorderContainer({
                style:"padding:0;margin:0;width:740px;height:325px;",
                gutters: false
            });
            var form;

            if(this.projectSchedule){
                form = new ProjectScheduleForm({
                    projectScheduleId: this.projectSchedule.id,
                    project: this.project,
                    subPackage: this.projectSchedule.sub_package_id > 0 ? {id: this.projectSchedule.sub_package_id, name: this.projectSchedule.sub_package_name} : null,
                    subPackageName: this.projectSchedule.sub_package_id > 0 ? this.projectSchedule.sub_package_name : null,
                    dialogWidget: this
                });
            }else{
                form = new ProjectScheduleForm({
                    project: this.project,
                    subPackage: this.subPackage,
                    subPackageName: this.subPackage ? this.subPackage.title : null,
                    dialogWidget: this
                });
            }

            var toolbar = new dijit.Toolbar({
                region: "top",
                style: "outline:none!important;padding:2px;overflow:hidden;"
            });

            toolbar.addChild(new dijit.form.Button({
                id: 'ProjectManagement'+this.project.id+'-ImportRow-button',
                label: nls.importFromEPMFile,
                iconClass: "icon-16-container icon-16-import",
                style:"outline:none!important;",
                onClick: dojo.hitch(form, 'importProjectSchedule')
            }));
            toolbar.addChild(new dijit.ToolbarSeparator());
            toolbar.addChild(
                new dijit.form.Button({
                    label: nls.save,
                    iconClass: "icon-16-container icon-16-save",
                    style:"outline:none!important;",
                    onClick: dojo.hitch(form, 'save')
                })
            );
            toolbar.addChild(new dijit.ToolbarSeparator());
            toolbar.addChild(
                new dijit.form.Button({
                    label: nls.close,
                    iconClass: "icon-16-container icon-16-close",
                    style:"outline:none!important;",
                    onClick: dojo.hitch(this, 'hide')
                })
            );

            borderContainer.addChild(toolbar);
            borderContainer.addChild(form);

            return borderContainer;
        }
    });

    var SubPackageListDialog = declare('buildspace.apps.ProjectManagement.SubPackageListDialog', dijit.Dialog, {
        style:"padding:0;margin:0;",
        title: null,
        project: null,
        buildRendering: function(){
            var gridContainer = this.createGrid();
            gridContainer.startup();
            this.content = gridContainer;

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
        createGrid: function(){
            var borderContainer = new dijit.layout.BorderContainer({
                style:"padding:0;margin:0;width:680px;height:350px;",
                gutters: false
            });

            var self = this,
                formatter = new GridFormatter();

            var grid = new EnhancedGrid({
                style: "border:none;",
                region: "center",
                structure: [
                    {name: 'No.', field: 'count', width:'30px', styles:'text-align:center;', formatter: formatter.rowCountCellFormatter },
                    {name: nls.title, field: 'title', width:'auto'}
                ],
                store: new dojo.data.ItemFileWriteStore({
                    clearOnClose:true,
                    url:"postContractSubPackage/getSubPackageList/projectId/"+this.project.id
                }),
                canSort: function(inSortInfo){
                    return false;
                },
                onRowDblClick: function(e) {
                    var item = this.getItem(e.rowIndex);
                    if(item.id[0] > 0 && item.title[0] !== null && item.title[0] !== '') {
                        var dialog = new ProjectScheduleFormDialog({
                            title: nls.createScheduleForSubPackage,
                            project: self.project,
                            subPackage: item
                        });

                        self.hide();
                        dialog.show();
                    }
                }
            });

            var toolbar = new dijit.Toolbar({
                region: "top",
                style: "outline:none!important;border-bottom:none;padding:2px;overflow:hidden;"
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
            borderContainer.addChild(grid);

            return borderContainer;
        }
    });

    return declare('buildspace.apps.ProjectManagement.ProjectScheduleList', dijit.layout.BorderContainer, {
        region: "center",
        style:"padding:0;margin:0;border:none;width:100%;height:100%;",
        gutters: false,
        project: null,
        workArea: null,
        grid: null,
        postCreate: function(){
            this.inherited(arguments);
            var self = this;
            var formatter = new GridFormatter();
            var grid = this.grid = Grid({
                    id: "projectManagement-projectScheduleListGrid",
                    project: this.project,
                    workArea: this.workArea,
                    structure: [
                        {name: 'No.', field: 'count', width:'30px', styles:'text-align:center;', formatter: formatter.rowCountCellFormatter },
                        {name: nls.title, field: 'title', width:'auto'},
                        {name: nls.type, field: 'type', width:'120px', styles:'text-align: center;', formatter: Formatter.typeCellFormatter}
                    ],
                    store: new dojo.data.ItemFileWriteStore({
                        clearOnClose:true,
                        url:"projectManagement/getProjectScheduleList/id/"+this.project.id
                    })
                }),
                toolbar = new dijit.Toolbar({region:"top", style:"outline:none!important;border:none;padding:2px;width:100%;"});

            var createScheduleDropDownMenu = new DropDownMenu({ style: "display: none;"});

            createScheduleDropDownMenu.addChild(new MenuItem({
                label: nls.createScheduleForMainProject,
                onClick: function(e){
                    var dialog = new ProjectScheduleFormDialog({
                        title: nls.createScheduleForMainProject,
                        project: self.project
                    });

                    dialog.show();
                }
            }));

            createScheduleDropDownMenu.addChild(new MenuItem({
                label: nls.createScheduleForSubPackage,
                onClick: function(e){
                    var dialog = new SubPackageListDialog({
                        title: nls.createScheduleForSubPackage+' ('+nls.subPackageList+')',
                        project: self.project
                    });

                    dialog.show();
                }
            }));

            toolbar.addChild(new DropDownButton({
                id: this.project.id+'ImportDropDownRow-button',
                label: nls.createProjectSchedule,
                iconClass: "icon-16-container icon-16-add",
                dropDown: createScheduleDropDownMenu
            }));

            toolbar.addChild(new dijit.ToolbarSeparator());
            toolbar.addChild(
                new dijit.form.Button({
                    id: 'ProjectManagement'+this.project.id+'-ScheduleListEditRow-button',
                    label: nls.edit,
                    iconClass: "icon-16-container icon-16-edit",
                    disabled: true,
                    style:"outline:none!important;",
                    onClick: function(){
                        if(grid.selection.selectedIndex > -1){
                            var item = grid.getItem(grid.selection.selectedIndex);
                            if(item.id[0] > 0){
                                var dialog = new ProjectScheduleFormDialog({
                                    title: buildspace.truncateString(item.title[0], 35),
                                    project: self.project,
                                    projectSchedule: item
                                });

                                dialog.show();
                            }
                        }
                    }
                })
            );

            toolbar.addChild(new dijit.ToolbarSeparator());
            toolbar.addChild(
                new dijit.form.Button({
                    id: 'ProjectManagement'+this.project.id+'-ScheduleListDeleteRow-button',
                    label: nls.delete,
                    iconClass: "icon-16-container icon-16-delete",
                    disabled: true,
                    style:"outline:none!important;",
                    onClick: function(){
                        if(grid.selection.selectedIndex > -1){
                            var item = grid.getItem(grid.selection.selectedIndex);
                            if(item.id[0] > 0){
                                grid.deleteRow(item);
                            }
                        }
                    }
                })
            );

            toolbar.addChild(new dijit.ToolbarSeparator());
            toolbar.addChild(new dijit.form.Button({
                id: 'ProjectManagement'+this.project.id+'-ExportRow-button',
                label: nls.export,
                iconClass: "icon-16-container icon-16-export",
                disabled: true,
                style:"outline:none!important;",
                onClick: function(){
                    if(grid.selection.selectedIndex > -1){
                        var item = grid.getItem(grid.selection.selectedIndex);
                        if(item.id[0] > 0){
                            var dialog = new ExportProjectScheduleDialog({
                                projectSchedule: item,
                                exportUrl: 'projectManagement/projectScheduleExport'
                            });

                            dialog.show();
                        }
                    }
                }
            }));

            this.addChild(toolbar);
            this.addChild(grid);
        }
    });
});