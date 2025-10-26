define('buildspace/apps/SystemMaintenance/UnitOfMeasurementMaintenance/grid',[
    'dojo/_base/declare',
    'dojo/_base/lang',
    "dojo/_base/array",
    "dojo/dom-attr",
    "dojox/grid/enhanced/plugins/Menu",
    "dojox/grid/enhanced/plugins/Selector",
    "dojox/grid/enhanced/plugins/Rearrange",
    'dojo/_base/event',
    'dojo/keys',
    "dijit/focus",
    'dojo/_base/html',
    'dojo/request/xhr',
    'dijit/PopupMenuItem',
    'buildspace/widget/grid/cells/Select',
    'buildspace/widget/grid/cells/Textarea',
    'dojo/i18n!buildspace/nls/UnitOfMeasurementMaintenance',
	'buildspace/widget/grid/Filter'
], function(declare, lang, array, domAttr, Menu, Selector, Rearrange, evt, keys, focusUtil, html, xhr, PopupMenuItem, Select, Textarea, nls, FilterToolbar ){

    var UnitOfMeasurementMaintenanceGrid = declare('buildspace.apps.SystemMaintenance.UnitOfMeasurementMaintenance.Grid', dojox.grid.EnhancedGrid, {
        type: null,
        style: "border-top:none;",
        selectedItem: null,
        borderContainerWidget: null,
        keepSelection: true,
        rowSelector: '0px',
        unitOfMeasurementFormContainer: null,
        param: null,
        constructor:function(args){
            this.rearranger = Rearrange(this, {});
        },
        canSort: function(inSortInfo){
            return false;
        },
        postCreate: function(){
            var self = this;
            self.inherited(arguments);
            this.on("RowContextMenu", function(e){
                self.selection.clear();
                var item = self.getItem(e.rowIndex);
                self.selection.setSelected(e.rowIndex, true);
                self.contextMenu(e);
                if(item.id > 0){
                    self.disableToolbarButtons(false);
                }else{
                    self.disableToolbarButtons(true, ['add']);
                }
            }, true);

            this.on('RowClick', function(e){
                var item = self.getItem(e.rowIndex);
                if(item && item.id > 0){
                    self.disableToolbarButtons(false);
                }else{
                    self.disableToolbarButtons(true, ['add']);
                }
            });
        },
        dodblclick: function(e){
            this.onRowDblClick(e);
        },
        contextMenu: function(e){
            var rowCtxMenu = this.rowCtxMenu = new dijit.Menu();
            this.contextMenuItems(e);
            var info = {target: e.target, coords: e.keyCode !== keys.F10 && "pageX" in e ? {x: e.pageX, y: e.pageY } : null};
            var item = this.getItem(e.rowIndex);
            if(rowCtxMenu && item && (this.selection.isSelected(e.rowIndex) || e.rowNode && html.hasClass(e.rowNode, 'dojoxGridRowbar'))){
                rowCtxMenu._openMyself(info);
                evt.stop(e);
                return;
            }
        },
        contextMenuItems: function(e){
            var self = this, item = this.getItem(e.rowIndex);
            if(item.id > 0){
                self.rowCtxMenu.addChild(new dijit.MenuItem({
                    label: nls.addUnitOfMeasurement,
                    iconClass:"icon-16-container icon-16-add",
                    onClick: function(){
                        self.unitOfMeasurementFormContainer.addUnitOfMeasurement();
                    }
                }));
                self.rowCtxMenu.addChild(new dijit.PopupMenuItem({
                    label: nls.edit,
                    iconClass:"icon-16-container icon-16-edit",
                    onClick: function(){
                        self.unitOfMeasurementFormContainer.editUnitOfMeasurement(e.rowIndex);
                    }
                }));
                self.rowCtxMenu.addChild(new dijit.PopupMenuItem({
                    label: nls.deleteUnitOfMeasurement,
                    iconClass:"icon-16-container icon-16-delete",
                    onClick: function(){
                        self.unitOfMeasurementFormContainer.deleteUnitOfMeasurement(e.rowIndex);
                    }
                }));
            }
        },
        disableToolbarButtons: function(isDisable, buttonsToEnable){
            var self = this,
                deleteRowBtn = dijit.byId('deleteUnitOfMeasurementRow-button' + this.param),
                editRowBtn = dijit.byId('editUnitOfMeasurementRow-button' + this.param);

            deleteRowBtn._setDisabledAttr(isDisable);
            editRowBtn._setDisabledAttr(isDisable);

            if(isDisable && buttonsToEnable instanceof Array ){
                dojo.forEach(buttonsToEnable, function(label){
                    var btn = dijit.byId(label+'UnitOfMeasurementRow-button' + self.param);
                    btn._setDisabledAttr(false);
                })
            }
        }
    });

    return declare('buildspace.apps.SystemMaintenance.UnitOfMeasurementMaintenance.grid', dijit.layout.BorderContainer, {
        pageId: 'page-00',
        style: "padding:0px;width:100%;height:100%;",
        gutters: false,
        title: null,
        rowSelector: null,
        gridOpts: {},
        param: null,
        refreshGrid: function(){
            this.grid.store.close();
            this.grid._refresh();
        },
        postCreate: function(){
            var self = this;
            self.inherited(arguments);
            lang.mixin(self.gridOpts, { param: self.param, region:"center", borderContainerWidget: self });
            var grid = this.grid = new UnitOfMeasurementMaintenanceGrid(self.gridOpts);
            var toolbar = new dijit.Toolbar({region: "top", style:"padding:2px;border-bottom:none;width:100%;"});
            toolbar.addChild(
                new dijit.form.Button({
                    id: 'addUnitOfMeasurementRow-button' + self.param,
                    label: nls.addUnitOfMeasurement,
                    iconClass: "icon-16-container icon-16-add",
                    onClick: function(){
                        grid.unitOfMeasurementFormContainer.addUnitOfMeasurement();
                    }
                })
            );

            toolbar.addChild(new dijit.ToolbarSeparator());
            toolbar.addChild(
                new dijit.form.Button({
                    id: 'editUnitOfMeasurementRow-button' + self.param,
                    label: nls.edit,
                    iconClass: "icon-16-container icon-16-edit",
                    disabled: grid.selection.selectedIndex > -1 ? false : true,
                    onClick: function(){
                        grid.unitOfMeasurementFormContainer.editUnitOfMeasurement(grid.selection.selectedIndex);
                    }
                })
            );

            toolbar.addChild(new dijit.ToolbarSeparator());
            toolbar.addChild(
                new dijit.form.Button({
                    id: 'deleteUnitOfMeasurementRow-button' + self.param,
                    label: nls.delete,
                    iconClass: "icon-16-container icon-16-delete",
                    disabled: grid.selection.selectedIndex > -1 ? false : true,
                    onClick: function(){
                        if(grid.selection.selectedIndex > -1){
                            grid.unitOfMeasurementFormContainer.deleteUnitOfMeasurement(grid.selection.selectedIndex);
                        }
                    }
                })
            );
			
			self.addChild(new FilterToolbar({
                   grid:self.grid,
                   region:"top",
                   filterFields: [ {'name':'Name'},{'symbol':'Symbol'},{'dimensions':'Dimensions'}]
            })
			);
            self.addChild(toolbar);
            self.addChild(new dijit.layout.ContentPane({style: 'width:100%', content:grid, region:'center'}));
        }
    });
});