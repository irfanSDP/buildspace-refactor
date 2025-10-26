buildspace.grid = {
    headerCtxMenu:{
        createMenu: function(grid, createItems=true){
            // summary:
            //      Creates the context menu. To be called in the grid constructor.
            //
            // createItems=false allows createItems() to be called at a later time, if variables are not available in constructor().
            grid.plugins = {menus: {
                headerMenu: new dijit.Menu()
            }};
            if(createItems) buildspace.grid.headerCtxMenu.createItems(grid);
        },
        createItems: function(grid){
            // summary:
            //      Creates the context menu items.
            //
            // Additional options for the column object:
            // (Optional) column.ctxMenuLabel allows the context menu labels to be changed.
            // (Optional) column.showHideCookieName will save the hide/show status in cookies.
            // Usage:
            // column = {
            //     ctxMenuLabel: <display_name>
            //     hidden:getCookieBoolean('cookie_name'),
            //     showHideCookieName: 'cookie_name'
            // }
            menusObject = grid.plugins.menus;

            if (typeof grid.structure !== 'undefined') {
                var column = grid.structure.cells[0];
                dojo.forEach(column, function(data, index){
                    if(data.showInCtxMenu){
                        var label = data.name;
                        var field = data.field;

                        if(data.ctxMenuLabel)
                        {
                            label = data.ctxMenuLabel;
                        }

                        menusObject.headerMenu.addChild(new dijit.CheckedMenuItem({
                            label: label,
                            checked: (typeof data.hidden === 'undefined' || data.hidden === false) ? true : false,
                            onChange: function(val){
                                var show = false;
                                if (val){
                                    show = true;
                                }

                                buildspace.grid.showHideColumn(grid, show, index, data.hideColumnGroup ? data.hideColumnGroup : []);

                                if(data.showHideCookieName) setCookie(data.showHideCookieName, !show);
                            }
                        }));
                    }
                });
            }
        }
    },
    getCellIndexByAttribute: function(cells, attribute, value){
        var cellIndex;
        dojo.forEach(cells, function(data, index){
            if(data[attribute] && data[attribute] == value) cellIndex = index;
        });
        return cellIndex;
    },
    showHideColumn: function(grid, show, index, hideColumnGroup=[]) {
        // summary:
        //      Hides or shows columns.
        //
        // Additional options for the column object:
        // (Optional) column.hideColumnGroup allows other columns to be hidden in addition to the selected column.
        // To hide columns B and C:
        // column = {
        //    hideColumnGroup: [
        //        {<column_B_attribute>:<column_B_attribute_value>}
        //        {<column_C_attribute>:<column_C_attribute_value>}
        //    ]
        // }
        grid.beginUpdate();

        var indexes = [index];

        var attribute;

        for(var i in hideColumnGroup)
        {
            attribute = Object.keys(hideColumnGroup[i])[0];

            indexes.push(buildspace.grid.getCellIndexByAttribute(grid.layout.cells, attribute, hideColumnGroup[i][attribute]));
        }

        for(var i in indexes){
            grid.layout.setColumnVisibility(indexes[i], show);
        }
        grid.endUpdate();
    }
}