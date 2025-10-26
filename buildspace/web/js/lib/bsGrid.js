define([
    'dojox/data/JsonRestStore',
    'dojo/data/ItemFileReadStore',
    'dojox/data/QueryReadStore',
    'dojox/grid/enhanced/plugins/Menu',
    'dojox/grid/EnhancedGrid',
    'dojox/grid/enhanced/plugins/Selector',
    'dojo/_base/event',
    'dojo/keys',
    'lib/bsApp',
    'dojo/_base/declare',
    'dijit/form/Select',
    'dijit/form/RadioButton',
    'dijit/Toolbar',
    'dojo/aspect'
], function(JsonRestStore, ItemFileReadStore, QueryReadStore, Menu, EnhancedGrid, Selector, event, keys, bsApp, declare, Select, RadioButton, Toolbar, aspect){
    var HIERARCHY_TYPE_HEADER = 1;
    var HIERARCHY_TYPE_WORK_ITEM = 2;
    var HIERARCHY_TYPE_NOID = 4;
    var HIERARCHY_TYPE_HEADER_TEXT = 'HEAD';
    var HIERARCHY_TYPE_WORK_ITEM_TEXT = 'WORK ITEM';
    var HIERARCHY_TYPE_NOID_TEXT = 'NOID';
    var GRID_TYPE_NORMAL = 'normal';
    var GRID_TYPE_TREE = 'tree';
    var GRID_OPTS = {};
    var A = declare('Bs.Grid', null, {
        originalVal: null,
        store: null,
        model: null,
        grid: null,
        dblClickTypeTab: 'tab',
        dblClickTypeDrillIn: 'drill',
        constructor: function(args){
            this.options = args;
            GRID_OPTS = this.options;
            this.store = new JsonRestStore({target:this.options.dataUrl, idAttribute:'identifier'});
            this.grid = new EnhancedGridExt({
                id: this.options.gridId,
                store: this.store,
                structure: this.options.layout,
                rowSelector: '0px',
                rowsPerPage: 30,
                canSort: function(){return false;},
                plugins: {
                    selector: true
                    //menus: this.contextMenu()
                }
            }, this.options.container);
            var self = this;

            self.grid.startup();

            dojo.connect(self.grid, "_onFetchBegin", self.initAdditionalRows);

            this.grid.on('StartEdit', function(cell, rowIdx){
                var item = self.grid.getItem(rowIdx);
                self.originalVal = item[cell.field];
            });

            dojo.connect(this.grid, 'doApplyCellEdit', function(val, rowIdx, inAttrName){
                self.cellContentChange(self.grid, self.options.postUrl, self.originalVal, val, rowIdx, inAttrName);
            });

            var view = this.grid.views.views[0];
            var scroll = view.scrollboxNode;
            dojo.connect(scroll, 'onscroll', function(e) {
                var contentPos = dojo.position(view.contentNode, true);
                var scrollerPos = dojo.position(this, true);

                var item = {};
                dojo.forEach(self.options.layout, function(entry, idx){
                    item[ entry.field ] = null;
                });

                if((parseInt(e.target.scrollTop) + parseInt(scrollerPos.h)) == parseInt(contentPos.h)){
                    self.grid.store.fetch( {
                        onBegin: function(size) {
                            item.id = Math.abs(size+1) * -1;
                            self.grid.store.newItem(item);
                        }
                    });
                }
            });

            this.grid.on('RowDblClick', function(e){
                var item = self.grid.getItem(e.rowIndex);
                if(item.id > 0){
                    switch(self.options.dblClickType) {
                        case self.dblClickTypeTab:
                            bsApp.addTab(self.options.tabIdContainer, bsApp.truncateString(item.description, 20), self.options.gridId+'-'+item.id, self.options.tabGridUrl+'?id='+item.id);
                            break;
                        case self.dblClickTypeDrillIn:
                            var xhrArgs = {
                                url: self.options.drillInUrl,
                                content: {parent_id: item.id},
                                handleAs: 'json',
                                load: function(resp) {
                                    var container = dijit.byId(self.options.gridId+'-stackContainer');
                                    if(container){
                                        var pageId = "page-"+item.id+'_'+self.options.gridId;
                                        var node = document.createElement("div");
                                        var child = new dojox.layout.ContentPane( {title: bsApp.truncateString(item.description, 30), id: pageId, executeScripts: true },node );
                                        container.addChild(child);
                                        child.set('content', resp.html);
                                        container.selectChild(pageId);
                                    }
                                },
                                error: function(error) {
                                    console.log(error);
                                }
                            };
                            dojo.xhrPost(xhrArgs);
                            break;
                        default:
                            break;
                    }
                }
            }, true);

            dojo.subscribe(this.options.gridId+"-stackContainer-selectChild","",function(page){
                var widget = dijit.byId(self.options.gridId+'-stackContainer');
                if(widget){
                    var children = widget.getChildren();
                    var index = dojo.indexOf(children, dijit.byId(page.id));
                    index = index+1;
                    while( children.length > index ){
                        widget.removeChild(children[ index ]);
                        children[ index ].destroyRecursive(true);
                        index = index + 1;
                    }
                }
            });

            dojo.connect(this.grid, 'onCellClick', function (e) {
                var colField = e.cell.field; // field name
                var rowIndex = e.rowIndex; // row index
                var item = self.grid.getItem(rowIndex);
                if(colField == 'type' && item.id > 0){
                    var xhrArgs = {
                        url: self.options.nestedSetOpts.typeEditUrl,
                        preventCache: true,
                        content: {
                            grid_id: self.options.gridId,
                            id: item.id
                        },
                        handleAs: 'json',
                        load: function(data) {
                            self.renderTypeEditDialog(item, data);
                        },
                        error: function(error) {
                            console.log(error);
                            //dojo.byId("mainMessageText").innerHTML = "Error :" + error;
                        }
                    };
                    dojo.xhrPost(xhrArgs);
                }
            });
        },

        initAdditionalRows: function(size, request){
            var item = {};
            dojo.forEach(GRID_OPTS.layout, function(entry, idx){
                if(entry.field != 'id'){
                    item[ entry.field ] = null;
                }
            });
            for(var i = size+1; i <=100; i++){
                item['id'] = Math.abs(i) * -1;
                this.store.newItem(item);
            }
        },

        cellContentChange: function(grid, postUrl, originalVal, val, rowIndex, colField){
            if(val !== originalVal){
                var item = grid.getItem(rowIndex);
                var content = {
                    id: item.id,
                    row: rowIndex+1,
                    fn: colField,
                    fv: val
                };

                var xhrArgs = {
                    url: postUrl,
                    content: content,
                    handleAs: 'json',
                    load: function(resp) {
                        grid.store.setValue(item, 'id', resp.data.id);
                        if(item.level !== undefined){
                            grid.store.setValue(item, 'level', resp.data.level);
                        }
                        if(item.type !== undefined){
                            grid.store.setValue(item, 'type', resp.data.type);
                        }
                        if(item.updated_at !== undefined){
                            grid.store.setValue(item, 'updated_at', resp.data.updated_at);
                        }
                    },
                    error: function(error) {
                        console.log(error);
                    }
                };
                dojo.xhrPost(xhrArgs);
            }
        },

        renderTypeEditDialog: function(item, data){
            var self = this;
            var cp = new dojox.layout.ContentPane();

            cp.set('content', data.content);
            var radioContainer = dojo.create('span');
            dojo.attr(radioContainer, "class", "tundra");
            /*var radioTable = '<table class="bordered" border="0" cellspacing="0" cellpadding="0" style="width:100%; ">';
            radioTable +='<thead><tr><th>Head</th><th>Description</th><th></th></tr>';
            radioTable +='</thead><tbody>';
            for (var i = 0; i < data.headers.length; i++) {
                var marginStart = parseInt(GRID_OPTS.nestedSetOpts.parentLvl)+1;
                var description = '<span style="margin-left:'+(data.headers[i].level-marginStart)*10+'px;">'+bsApp.truncateString(data.headers[i].description, 30)+'</span>';
                var checked = data.headers[i].checked ? 'checked' : '';
                radioTable +='<tr>';
                radioTable +='<td>'+data.headers[i].header_number+'</td>';
                radioTable +='<td>'+description+'</td>';
                radioTable +='<td><input type="radio" data-dojo-type="dijit.form.RadioButton" name="head_count" id="setToHeader'+data.headers[i].id+'" value="'+data.headers[i].id+'" '+checked+'/></td>'
                radioTable +='</tr>';
            }
            radioTable += '</tbody></table>';
            radioContainer.innerHTML = radioTable;*/

            var store = new QueryReadStore({
                url: 'library/getNestedSetHeaders?id='+item.id
            });

            var layout = [[
                {name: 'Head', field: 'header_number', noresize: true,  styles:'text-align:center;', width: '10%'},
                {name: 'Description', field: 'description', noresize: true, width: '82%', formatter: descriptionReadOnlyCellFormatter},
                {name: '&nbsp;', field: 'id', noresize: true,  styles:'text-align:center;', width: '8%', formatter: radioButtonFormatter}
            ]];

            var grid = new dojox.grid.DataGrid({
                store: store,
                structure: layout,
                rowSelector: '0px',
                canSort: function(){return false;},
                height: '200px'},
                document.createElement('div')
            );

            var items = [];
            for(x=0; x<data.selectStoreItems.length; x++ ){
                var i = data.selectStoreItems[x];
                items.push({"label":i.label,"value":i.value})
            }

            var storeData = { "identifier": "value", "label":"label", "items": items }
            var selectStore = new ItemFileReadStore({data: storeData});

            var select = new Select({
                id: self.options.gridId+'typeSelect',
                name: 'hierarchy_type',
                store: selectStore,
                sortByLabel: false,
                onChange: function(value){
                    console.log('here')
                }
            });

            var dialog = new dijit.Dialog({
                id: self.options.gridId+'nestedSetTypeDialog',
                title: 'Type for '+bsApp.truncateString(item.description, 30),
                content: cp.domNode,
                style: 'width:650px',
                onHide: function() {
                    dialog.destroyRecursive();
                }
            });

            dojo.connect(dialog, 'onShow', null, function(e) {

                dojo.place(select.domNode, dojo.byId(self.options.gridId+'-typeSelect'+item.id), 'replace');
                select.startup();
                select.set("value", item.type);

                dojo.place(grid.domNode, dojo.byId(self.options.gridId+'-radioButton'+item.id), 'first');
                //radioContainer.appendChild(grid.domNode);
                grid.startup();

                var toolbar = new Toolbar({}, self.options.gridId+'-toolbar'+item.id);
                dojo.forEach(['Save', 'Cancel'], function(label){
                    var button = new dijit.form.Button({
                        label: label,
                        showLabel: true,
                        iconClass: label.toLowerCase()+'Icon',
                        onClick: function(){
                            if(label.toLowerCase()=='save'){
                                dojo.xhrPost({
                                    url: self.options.nestedSetOpts.updateTypeUrl,
                                    form: self.options.gridId+'-form'+item.id,
                                    timeout: 60000,
                                    load: function(result) {
                                        var scrollTop = self.grid.scrollTop;
                                        /*self.store.clearOnClose = true;
                                        self.store.close();
                                        self.store.fetch({query: {}, onBegin: function(i, r){
                                            self.initAdditionalRows(i,r);

                                        }, start: 0, count: 0});

                                        var handle = aspect.after(self.grid, "_onFetchComplete", function(i, r) {
                                            handle.remove();
                                            this.scrollToRow(this.selection.selectedIndex);
                                        });

                                        self.grid.sort();
                                        /*self.grid.sort();
                                        self.grid.setStore(self.store);
                                        self.grid.setScrollTop(scrollTop);
                                        setTimeout(function(){
                                                self.grid.setScrollTop(scrollTop);
                                            },
                                            100);*/

                                    },
                                    error: function(error, args) {
                                        console.log(error);
                                    }
                                });

                            }

                            dialog.hide();
                        }
                    });
                    toolbar.addChild(button);
                });
            });
            dialog.show();
        }
    });

    var EnhancedGridExt = declare(EnhancedGrid, {
        dodblclick: function(e){
            this.onRowDblClick(e);
        }
    });

    var radioButtonFormatter = function(cellValue, rowIdx){
        var store = this.grid.store;
        var item = store._itemsByIdentity[cellValue];
        var checked = item.checked ? 'checked' : '';
        var cellValue = '<div style="padding:3px;"><input type="radio" data-dojo-type="dijit.form.RadioButton" name="head_count" id="setToHeader'+item.id+'" value="'+item.id+'" '+checked+'/></div>';
        return cellValue;
    }

    var descriptionReadOnlyCellFormatter = function(cellValue, rowIdx){
        var item = this.grid.getItem(rowIdx);
        var marginStart = parseInt(GRID_OPTS.nestedSetOpts.parentLvl)+1;
        cellValue = '<span style="margin-left:'+(item.i.level-marginStart)*20+'px;">'+bsApp.truncateString(cellValue, 60)+'</span>';
        return cellValue;
    }

    var editableCellFormatter = function(cellValue){
        if (cellValue && cellValue.charAt(0) == '=') {
            cellValue = cellValue.substring(1, cellValue.length);
            var eq = Parser.evaluate(cellValue);
            cellValue = '<span formula="'+cellValue+'" style="color:blue">'+eq+'</span>';
        }
        return cellValue;
    }

    return {
        editableCellFormatter: function(cellValue){
            return editableCellFormatter(cellValue);
        },
        rowCountCellFormatter: function(cellValue, rowIdx){
            return parseInt(rowIdx)+1;
        },
        descriptionCellFormatter: function(cellValue, rowIdx){
            var item = this.grid.getItem(rowIdx);
            cellValue = editableCellFormatter(cellValue);
            if(item['id'] > 0){
                var marginStart = parseInt(GRID_OPTS.nestedSetOpts.parentLvl)+1;
                cellValue = '<span style="margin-left:'+(item["level"]-marginStart)*20+'px;">'+cellValue+'</span>';
            }
            return cellValue;
        },
        rowTypeCellFormatter: function(cellValue, rowIdx){
            var item = this.grid.getItem(rowIdx);
            if(item['id'] > 0){
                switch (cellValue) {
                    case HIERARCHY_TYPE_HEADER:
                        cellValue = HIERARCHY_TYPE_HEADER_TEXT+item['header_number'];
                        break;
                    case HIERARCHY_TYPE_WORK_ITEM:
                        cellValue = HIERARCHY_TYPE_WORK_ITEM_TEXT;
                        break;
                    default:
                        cellValue = HIERARCHY_TYPE_NOID_TEXT;
                        break;
                }
                cellValue = '<a href="#" onclick="return false;">'+cellValue+'</a>';
            }
            return cellValue;
        }
    }
});