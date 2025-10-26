var app_tabulator_utilities = {
    integerIdEditable: function(cell){
        var cellData = cell.getData();

        if(cellData.hasOwnProperty('id') && (!Number.isInteger(cellData['id']))) return false;

        return true;
    },
    variableHtmlFormatter: function(cell, formatterParams, onRendered) {
        function htmlBuilder(formatterParams) {
            var tag        = formatterParams.tag;
            var innerHtml  = formatterParams.innerHtml ? formatterParams.innerHtml : '';
            var attributes = "";
            var checked    = false;
            var key, value;
            var rowData    = cell.getRow().getData();
            var show;
            var opaque;
            var attrObj    = {};

            switch(typeof formatterParams.show){
                case "boolean":
                    show = formatterParams.show;
                    break;
                case "function":
                    show = formatterParams.show(cell);
                    break;
                default:
                    show = true;
            }

            switch(typeof formatterParams.opaque){
                case "boolean":
                    opaque = formatterParams.opaque;
                    break;
                case "function":
                    opaque = formatterParams.opaque(cell);
                    break;
                default:
                    opaque = true;
            }

            if(!show) return '';

            if(!opaque){
                if(formatterParams.attributes){
                    if(typeof attrObj['class'] == 'undefined') attrObj['class'] = [];
                    attrObj['class'].push('invisible');
                }
            }

            for( key in formatterParams.rowAttributes ){
                value = rowData[ formatterParams.rowAttributes[ key ] ];
                
                if( key === 'checked' ){
                    attributes += (value) ? "checked " : "";

                    if(value) attrObj['checked'] = ['checked'];
                    continue;
                }
                if( key === 'disabled' ){
                    attributes += (value) ? "disabled " : "";

                    if(value) attrObj['disabled'] = ['disabled'];
                    continue;
                }

                attributes += key + "='" + value + "' ";

                if(typeof attrObj[key] == 'undefined') attrObj[key] = [];

                attrObj[key].push(value);
            }

            for( key in formatterParams.attributes ){
                value = formatterParams.attributes[ key ];
                attributes += key + "='" + value + "' ";

                if(typeof attrObj[key] == 'undefined') attrObj[key] = [];

                attrObj[key].push(value);
            }

            if( Array.isArray(innerHtml) ){
                var output = "";
                for( var i in innerHtml ){
                    output += htmlBuilder(innerHtml[ i ]);
                }

                innerHtml = output;
            }
            else {
                switch(typeof innerHtml){
                    case 'object':
                        innerHtml = htmlBuilder(innerHtml);
                        break;
                    case 'function':
                        innerHtml = innerHtml(rowData);
                        break;
                    case 'string':
                        break;
                    default:
                        innerHtml = "";
                }
            }

            var attributeValue;
            var attribute;
            var allAttributesArray = [];

            for(var i in attrObj){
                attributeValue = attrObj[i].join(' ');

                attribute = i+'="'+attributeValue+'"';

                allAttributesArray.push(attribute);
            }

            attributes = allAttributesArray.join(' ');

            if( tag ) return '<' + tag + ' ' + attributes + ' >' + innerHtml + '</' + tag + '>';
            return innerHtml;
        }

        return htmlBuilder(formatterParams);
    },
    /**
    Tabulator's default removeFilter function does not seem to be able to remove custom filters.
    */
    removeCustomFilter(tabulatorTable, customFilter){
        var filters = tabulatorTable.getFilters();
        var itemIndex;

        for(i in filters){
            if(filters[i].field == customFilter){
                filters.splice(i, 1);
            }
        }

        tabulatorTable.setFilter(filters);
    },
    lastRowFormatter: function(cell, formatterParams, onRendered) {
        var data = cell.getRow().getData();

        if(data.hasOwnProperty('id') && data.id == 'last-row' && !cell.getRow().getElement().className.includes('last-row')){
            cell.getRow().getElement().className += " last-row ";
        }

        return this.sanitizeHTML(cell.getValue());
    }
};