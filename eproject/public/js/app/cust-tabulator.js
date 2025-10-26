CustomTabulator = {
    /*tableProperties: {},*/
    onEnter_focus:function(cell)
    {
        return;//further test this later
        console.log('onEnter_focus');
        var row = cell.getRow();

        if(row.getNextRow())
        {
            cell.nav().down();
            // console.log('get next row');
        }
        else
        {
            // cell.edit();
            // console.log('no edit');
        }
    },
    //cellEdited should be specific to each table.
    /*cellEdited: function(cell){
        console.log('cellEdited');
        var cellData = cell.getData();
        var table = cell.getTable();
        var tableProperties = CustomTabulator.tableProperties[table.element.id];
        if(cellData.hasOwnProperty('id'))
        {
            var input = {
                _token: _csrf_token
            };
            input['field'] = cell.getField();
            input['value'] = cell.getValue();
            $.post(cellData['route:update'], input)
            .done(function(data){
                cell.getRow().update(data);
                CustomTabulator.onEnter_focus(cell);
                // console.log('no update');
                // console.log('cellFocused');
            })
            .fail(function(data){
                console.error('failed');
            });
        }
        else if(cell.getValue() !== '' && (cell.getValue() !== cell.getOldValue()) && cell.getValue())
        {
            if(tableProperties.storeFields.includes(cell.getField())){
                var input = {
                    _token: _csrf_token
                };
                input[cell.getField()] = cell.getValue();
                $.post(tableProperties.storeUrl, input)
                .done(function(data){
                    cell.getRow().update(data);
                    cell.getTable().addRow({});
                    CustomTabulator.onEnter_focus(cell);
                    cell.getRow().reformat();
                })
                .fail(function(data){
                    console.error('failed');
                });
            }
        }
    }*/
}
Tabulator.prototype.extendModule("format", "formatters", {
    // bold:function(cell, formatterParams){
    //     return "<strong>" + cell.getValue() + "</strong>"; //make the contents of the cell bold
    // },
    // uppercase:function(cell, formatterParams){
    //     return cell.getValue().toUpperCase(); //make the contents of the cell uppercase
    // },
    tickCross: function tickCross(cell, formatterParams, onRendered) {
        var cellData = cell.getData();
        if(!cellData.hasOwnProperty('id') || (!Number.isInteger(cellData['id']))) return '';
        var value = cell.getValue(),
            element = cell.getElement(),
            empty = formatterParams.allowEmpty,
            truthy = formatterParams.allowTruthy,
            tick = typeof formatterParams.tickElement !== "undefined" ? formatterParams.tickElement : '<svg enable-background="new 0 0 24 24" height="14" width="14" viewBox="0 0 24 24" xml:space="preserve" ><path fill="#2DC214" clip-rule="evenodd" d="M21.652,3.211c-0.293-0.295-0.77-0.295-1.061,0L9.41,14.34  c-0.293,0.297-0.771,0.297-1.062,0L3.449,9.351C3.304,9.203,3.114,9.13,2.923,9.129C2.73,9.128,2.534,9.201,2.387,9.351  l-2.165,1.946C0.078,11.445,0,11.63,0,11.823c0,0.194,0.078,0.397,0.223,0.544l4.94,5.184c0.292,0.296,0.771,0.776,1.062,1.07  l2.124,2.141c0.292,0.293,0.769,0.293,1.062,0l14.366-14.34c0.293-0.294,0.293-0.777,0-1.071L21.652,3.211z" fill-rule="evenodd"/></svg>',
            cross = typeof formatterParams.crossElement !== "undefined" ? formatterParams.crossElement : '<svg enable-background="new 0 0 24 24" height="14" width="14"  viewBox="0 0 24 24" xml:space="preserve" ><path fill="#CE1515" d="M22.245,4.015c0.313,0.313,0.313,0.826,0,1.139l-6.276,6.27c-0.313,0.312-0.313,0.826,0,1.14l6.273,6.272  c0.313,0.313,0.313,0.826,0,1.14l-2.285,2.277c-0.314,0.312-0.828,0.312-1.142,0l-6.271-6.271c-0.313-0.313-0.828-0.313-1.141,0  l-6.276,6.267c-0.313,0.313-0.828,0.313-1.141,0l-2.282-2.28c-0.313-0.313-0.313-0.826,0-1.14l6.278-6.269  c0.313-0.312,0.313-0.826,0-1.14L1.709,5.147c-0.314-0.313-0.314-0.827,0-1.14l2.284-2.278C4.308,1.417,4.821,1.417,5.135,1.73  L11.405,8c0.314,0.314,0.828,0.314,1.141,0.001l6.276-6.267c0.312-0.312,0.826-0.312,1.141,0L22.245,4.015z"/></svg>';

        if (truthy && value || value === true || value === "true" || value === "True" || value === 1 || value === "1") {
            element.setAttribute("aria-checked", true);
            return tick || "";
        } else {
            if (empty && (value === "null" || value === "" || value === null || typeof value === "undefined")) {
                element.setAttribute("aria-checked", "mixed");
                return "";
            } else {
                element.setAttribute("aria-checked", false);
                return cross || "";
            }
        }
    },
    // checkbox: function(cell, formatterParams){
    //     return '<input type="checkbox" data-field="'+cell.getField()+'" '+checked+'/>';
    // }
    // checkbox1:function(cell, formatterParams){
    //     var cellData = cell.getData();
    //     if(cellData.hasOwnProperty('id')){
    //         var checked = cellData['can_request_inspection'] ? 'checked' : '';
    //         return '<input type="checkbox" data-field="'+cell.getField()+'" '+checked+'/>';
    //         var paramsString = '';
    //         for (const [key, value] of Object.entries(formatterParams)) {
    //             paramsString += ' '+key+'="'+value+'"';
    //         }
    //         return '<input type="checkbox" '+paramsString+' '+checked+'/>';
    //         // return '<div class="smart-form"><label class="checkbox"><input type="checkbox" '+checked+'><i></i></label></div>';

    //     }
    // },
    // checkbox2:function(cell, formatterParams){
    //     var cellData = cell.getData();
    //     if(cellData.hasOwnProperty('id')){
    //         var checked = cellData['can_request_inspection'] ? 'checked' : '';
    //         return '<input type="checkbox" data-route="'+cellData['route:update']+'" data-field="'+cell.getField()+'" '+checked+'/>';

    //     }
    // },
});
// $(document).on('change', '.tabulator input[type=checkbox][data-field]', function(){
//     // console.log('on click!');
//     // console.log($(this).data());
//     // console.log($(this).data('route'));
//     // console.log($(this).data('field'));
//     // console.log('checked', this.checked);
//     // return;
//     var input = {
//         _token: _csrf_token
//     };
//     input['field'] = $(this).data('field');
//     input['value'] = this.checked;
//     // $.post($(this).data('route'), input)
//     // .done(function(data){
//     //     cell.getRow().update(data);
//     // })
//     // .fail(function(data){
//     //     console.error('failed');
//     // });
// });
Tabulator.prototype.extendModule("edit", "editors", {
    input:function(cell, onRendered, success, cancel, editorParams){

        //create and style input
        var cellValue = cell.getValue(),
        input = document.createElement("input");

        input.setAttribute("type", editorParams.search ? "search" : "text");

        input.style.padding = "4px";
        input.style.width = "100%";
        input.style.boxSizing = "border-box";

        if(editorParams.elementAttributes && typeof editorParams.elementAttributes == "object"){
            for (let key in editorParams.elementAttributes){
                if(key.charAt(0) == "+"){
                    key = key.slice(1);
                    input.setAttribute(key, input.getAttribute(key) + editorParams.elementAttributes["+" + key]);
                }else{
                    input.setAttribute(key, editorParams.elementAttributes[key]);
                }
            }
        }

        input.value = typeof cellValue !== "undefined" ? cellValue : "";

        onRendered(function(){
            input.focus({preventScroll: true});
            input.style.height = "100%";
        });

        function onChange(e){
            // console.log('onChange');
            if(((cellValue === null || typeof cellValue === "undefined") && input.value !== "") || input.value !== cellValue){
                if(success(input.value)){
                    cellValue = input.value; //persist value if successfully validated incase editor is used as header filter
                }
            }else{
                cancel();
                // blur also trigger onChange
                // CustomTabulator.onEnter_focus(cell);
            }
        }

        //submit new value on blur or change
        input.addEventListener("change", onChange);
        input.addEventListener("blur", onChange);

        //submit new value on enter
        input.addEventListener("keydown", function(e){
            switch(e.keyCode){
                // case 9:
                case 13:
                // case 40:
                onChange(e);
                // CustomTabulator.onEnter_focus(cell);
                break;

                case 27:
                cancel();
                break;

                case 35:
                case 36:
                e.stopPropagation();
                break;
            }
        });

        if(editorParams.mask){
            this.table.modules.edit.maskInput(input, editorParams);
        }

        return input;
    },

    //input element with type of number
    number:function(cell, onRendered, success, cancel, editorParams){

        var cellValue = cell.getValue(),
        vertNav = editorParams.verticalNavigation || "editor",
        input = document.createElement("input");

        input.setAttribute("type", "number");

        if(typeof editorParams.max != "undefined"){
            input.setAttribute("max", editorParams.max);
        }

        if(typeof editorParams.min != "undefined"){
            input.setAttribute("min", editorParams.min);
        }

        if(typeof editorParams.step != "undefined"){
            input.setAttribute("step", editorParams.step);
        }

        //create and style input
        input.style.padding = "4px";
        input.style.width = "100%";
        input.style.boxSizing = "border-box";

        if(editorParams.elementAttributes && typeof editorParams.elementAttributes == "object"){
            for (let key in editorParams.elementAttributes){
                if(key.charAt(0) == "+"){
                    key = key.slice(1);
                    input.setAttribute(key, input.getAttribute(key) + editorParams.elementAttributes["+" + key]);
                }else{
                    input.setAttribute(key, editorParams.elementAttributes[key]);
                }
            }
        }

        input.value = cellValue;

        var blurFunc = function(e){
            onChange();
        };

        onRendered(function () {
            //submit new value on blur
            input.removeEventListener("blur", blurFunc);

            input.focus({preventScroll: true});
            input.style.height = "100%";

            //submit new value on blur
            input.addEventListener("blur", blurFunc);
        });

        function onChange(){
            var value = input.value;

            if(!isNaN(value) && value !==""){
                value = Number(value);
            }

            if(value !== cellValue){
                if(success(value)){
                    cellValue = value; //persist value if successfully validated incase editor is used as header filter
                }
            }else{
                cancel();
            }
        }

        //submit new value on enter
        input.addEventListener("keydown", function(e){
            switch(e.keyCode){
                case 13:
                // case 9:
                onChange();
                CustomTabulator.onEnter_focus(cell);
                break;

                case 27:
                cancel();
                break;

                case 38: //up arrow
                case 40: //down arrow
                if(vertNav == "editor"){
                    e.stopImmediatePropagation();
                    e.stopPropagation();
                }
                break;

                case 35:
                case 36:
                e.stopPropagation();
                break;
            }
        });

        if(editorParams.mask){
            this.table.modules.edit.maskInput(input, editorParams);
        }

        return input;
    },

});