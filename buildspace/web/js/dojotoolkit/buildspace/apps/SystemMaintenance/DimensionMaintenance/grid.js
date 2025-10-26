define('buildspace/apps/SystemMaintenance/DimensionMaintenance/grid',[
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
    'dojo/i18n!buildspace/nls/DimensionMaintenance',
    'buildspace/widget/grid/Filter'
], function(declare, lang, array, domAttr, Menu, Selector, Rearrange, evt, keys, focusUtil, html, xhr, PopupMenuItem, Select, Textarea, nls, FilterToolbar ){

    var DimensionMaintenanceGrid = declare('buildspace.apps.SystemMaintenance.DimensionMaintenance.Grid', dojox.grid.EnhancedGrid, {
        type: null,
        style: "border-top:none;",
        selectedItem: null,
        borderContainerWidget: null,
        keepSelection: true,
        rowSelector: '0px',
        dimensionFormContainer: null,
        constructor:function(args){
            this.rearranger = Rearrange(this, {});
        },
        canSort: function(inSortInfo){
            return false;
        },
        postCreate: function(){
            var self = this;
            this.inherited(arguments);
            this.on("RowContextMenu", function(e){
                self.selection.clear();
                var item = self.getItem(e.rowIndex);
                self.selection.setSelected(e.rowIndex, true);
                self.contextMenu(e);
                if(item && !isNaN(parseInt(item.id[0]))){
                    self.disableToolbarButtons(false);
                }else{
                    self.disableToolbarButtons(true, ['add']);
                }
            }, true);

            this.on('RowClick', function(e){
                var item = self.getItem(e.rowIndex);
                if(item && !isNaN(parseInt(item.id[0]))){
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
            var item = this.getItem(e.rowIndex);
            if(item && !isNaN(parseInt(item.id[0]))){
                this.rowCtxMenu.addChild(new dijit.MenuItem({
                    label: nls.addDimension,
                    iconClass:"icon-16-container icon-16-add",
                    onClick: dojo.hitch(this.dimensionFormContainer, "addDimension")
                }));
                this.rowCtxMenu.addChild(new dijit.PopupMenuItem({
                    label: nls.edit,
                    iconClass:"icon-16-container icon-16-edit",
                    onClick: dojo.hitch(this.dimensionFormContainer, "editDimension")
                }));
                this.rowCtxMenu.addChild(new dijit.PopupMenuItem({
                    label: nls.deleteDimension,
                    iconClass:"icon-16-container icon-16-delete",
                    onClick: dojo.hitch(this.dimensionFormContainer, "deleteDimension")
                }));
            }
        },
        disableToolbarButtons: function(isDisable, buttonsToEnable){
            var deleteRowBtn = dijit.byId('deleteDimensionRow-button');
            var editRowBtn = dijit.byId('editDimensionRow-button');
            deleteRowBtn._setDisabledAttr(isDisable);
            editRowBtn._setDisabledAttr(isDisable);
            if(isDisable && buttonsToEnable instanceof Array ){
                dojo.forEach(buttonsToEnable, function(label){
                    var btn = dijit.byId(label+'DimensionRow-button');
                    btn._setDisabledAttr(false);
                })
            }
        }
    });

    return declare('buildspace.apps.SystemMaintenance.DimensionMaintenance.grid', dijit.layout.BorderContainer, {
        pageId: 'page-00',
        style: "padding:0px;width:100%;height:100%;",
        gutters: false,
        title: null,
        rowSelector: null,
        gridOpts: {},
        refreshGrid: function(){
            this.grid.store.close();
            this.grid._refresh();
        },
        postCreate: function(){
            this.inherited(arguments);
            lang.mixin(this.gridOpts, { region:"center", borderContainerWidget: this });
            var grid = this.grid = new DimensionMaintenanceGrid(this.gridOpts);
            var toolbar = new dijit.Toolbar({region: "top", style:"padding:2px;border-bottom:none;width:100%;"});
            toolbar.addChild(
                new dijit.form.Button({
                    id: 'addDimensionRow-button',
                    label: nls.addDimension,
                    iconClass: "icon-16-container icon-16-add",
                    onClick: dojo.hitch(grid.dimensionFormContainer, "addDimension")
                })
            );

            toolbar.addChild(new dijit.ToolbarSeparator());
            toolbar.addChild(
                new dijit.form.Button({
                    id: 'editDimensionRow-button',
                    label: nls.edit,
                    iconClass: "icon-16-container icon-16-edit",
                    disabled: grid.selection.selectedIndex > -1 ? false : true,
                    onClick: dojo.hitch(grid.dimensionFormContainer, "editDimension")
                })
            );

            toolbar.addChild(new dijit.ToolbarSeparator());
            toolbar.addChild(
                new dijit.form.Button({
                    id: 'deleteDimensionRow-button',
                    label: nls.delete,
                    iconClass: "icon-16-container icon-16-delete",
                    disabled: grid.selection.selectedIndex > -1 ? false : true,
                    onClick: dojo.hitch(grid.dimensionFormContainer, "deleteDimension")
                })
            );

            this.addChild(new FilterToolbar({
                grid: grid,
                region:"top",
                filterFields: [ {'name':nls.name}]
            }));

            this.addChild(toolbar);
            this.addChild(new dijit.layout.ContentPane({style: 'width:100%', content:grid, region:'center'}));
        }
    });
});