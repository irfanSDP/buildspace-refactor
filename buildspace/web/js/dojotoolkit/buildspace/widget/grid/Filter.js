define([
'dojo/_base/declare',
'dijit/layout/BorderContainer',
'dijit/layout/ContentPane',
'dijit/form/TextBox',
'dijit/form/Button',
'dijit/form/Select',
'dijit/Toolbar',
'dijit/ToolbarSeparator',
'dojo/_base/connect',
'dojo/i18n!buildspace/nls/Filter'],
function(declare, BorderContainer, ContentPane, TextBox, Button, Select, Toolbar, ToolbarSeparator, connect, nls){
    var constants = {
        ON_FETCH_COMPLETE_CASE_NONE                     : 0,
        ON_FETCH_COMPLETE_CASE_SELECT_AND_SCROLL_TO_ROW : 1,
        ON_FETCH_COMPLETE_CASE_REFRESH                  : 2,
        KEY_CODE_TAB                                    : 9,
        KEY_CODE_ENTER                                  : 13,
        KEY_CODE_PAGE_UP                                : 33,
        KEY_CODE_PAGE_DOWN                              : 34
    };

    return declare('buildspace.filterToolbar', Toolbar, {
        // Required
        grid            : null,
        filterFields    : [],
        /* define filterFields as such:
            filterFields = [a, b, ...];
            where each element is either a string <fieldName> or a json <{fieldName : displayedString}>

            eg.
            filterFields = [
            'fieldName1',
            {fieldName2 : displayedString2}
            ];

            */
        //===========

        // Optional settings
        editableGrid                : false,
        autoFocus                   : true,
        initialAutoFocus            : true,
        disableKeyBoardShortcuts    : false,
        //==================

        style                       : 'outline:none!important;padding:2px;overflow:hidden!important;border:none;',
        gutters                     : false,

        filterModeOff               : true,
        fieldSelectionSelectedIndex : null,
        arrayOfColumnIndexes        : [],
        columnIndexToFocus          : null,
        gridRowCount                : null,
        filterReady                 : true,
        onFetchCompleteCase         : constants.ON_FETCH_COMPLETE_CASE_NONE,
        editFromFilter              : false,
        arrayOfMatchingRowIndexes   : [],
        cursor                      : null,
        focusFromFilter             : null,
        editingCell                 : null,
        hasIndirectSelection        : false,

        postCreate: function(){
            if(this.grid.plugins){
                if(this.grid.plugins['indirectSelection']){
                    this.hasIndirectSelection = true;
                }
            }
            var self = this,
                buttonWidth = 22,
                refreshButton = new Button({
                    label: nls.refresh,
                    style: 'float: right',
                    iconClass: 'icon-16-container icon-16-reload',
                    onClick: dojo.hitch(self, 'refreshFilter')
                }),
                filterLabel = this.filterLabel = new ContentPane({
                    style: 'padding-left: 10px; padding-right: 10px; padding-top: 3px; float: right;',
                    content: nls.findBy
                }),
                searchButton = new Button({
                    label: nls.search,
                    style: 'float: right',
                    iconClass: 'icon-16-container icon-16-zoom',
                    onClick: dojo.hitch(self, 'submit')
                }),
                fieldSelection = this.fieldSelection = new Select({
                    style: 'padding:2px!important;width:120px;float:right',
                    options: self.getSelectOptions(),
                    onChange: dojo.hitch(self, 'onFieldSelectionChange')
                }),
                filterTextBox = this.filterTextBox = new TextBox({
                    region: 'right',
                    style: 'width:200px;float:right;padding:3px;',
                    onKeyPress: function(e){
                        self.filterTextBoxOnKeyPress(e);
                    },
                    selectOnClick: true,
                    onChange:function(){
                        self.filterModeOff = true;
                    },
                    intermediateChanges: true
                }),
                nextButton = this.nextButton = new Button({
                    iconClass: 'icon-16-container icon-16-directional_down',
                    style: 'float: right;width: '+buttonWidth+'px',
                    disabled: self.filterModeOff,
                    onClick: dojo.hitch(self, 'nextMatching')
                }),
                previousButton = this.previousButton = new Button({
                    iconClass: 'icon-16-container icon-16-directional_up',
                    style: 'float: right; width: '+buttonWidth+'px',
                    disabled: self.filterModeOff,
                    onClick: dojo.hitch(self, 'previousMatching')
                }),
                numberOfRecordsDisplay = this.numberOfRecordsDisplay = new ContentPane({
                    content: '',
                    style: 'padding-left: 10px; padding-right: 10px; padding-top: 3px; float: left;'
                });
            self.displayNumberOfRecords();

            self.columnIndexToFocus = self.arrayOfColumnIndexes[0];
            self.addChild(numberOfRecordsDisplay);

            self.addChild(refreshButton);
            self.addChild(new ToolbarSeparator({
                style: 'float: right; '
            }));
            self.addChild(searchButton);
            self.addChild(new ToolbarSeparator({
                style: 'float: right; '
            }));
            self.addChild(nextButton);
            self.addChild(previousButton);
            self.addChild(filterTextBox);
            self.addChild(fieldSelection);
            self.addChild(filterLabel);

            self._connects.push(connect.connect(self.grid, '_onFetchComplete', function(){
                self.onFetchComplete();
            }));

            //to prevent autofocus to textbox, when user tries to manipulate grid manually
            self._connects.push(connect.connect(self.grid, 'onCellFocus', function(){
                if(self.focusFromFilter){
                    self.focusFromFilter = false;
                    self.focusTextBox();
                }
            }));

            self._connects.push(connect.connect(self.grid.edit, 'apply', function(){
                self.tryFocus();
            }));

            self._connects.push(connect.connect(self.grid.edit, 'cancel', function(){
                self.tryFocus();
            }));

            //to prevent onApply etc. to fire multiple times
            self._connects.push(connect.connect(self.grid.edit, 'setEditCell', function(){
                self.editingCell = true;
            }));

            self._connects.push(connect.connect(self.grid.store, 'save', function(){
                self.onGridSave();
            }));

        },
        tryFocus: function(){
            if(this.editingCell){
                this.editingCell = false;
                if(this.editFromFilter){
                    this.editFromFilter = false;
                    this.focusTextBox();
                }
            }
        },
        onFetchComplete: function(){
            //for when a new row is added
            if(this.gridRowCount != null){
                if(this.gridRowCount < this.grid.rowCount){
                    this.reset();
                }
            }
            this.gridRowCount = this.grid.rowCount;

            switch(this.onFetchCompleteCase){
                case constants.ON_FETCH_COMPLETE_CASE_SELECT_AND_SCROLL_TO_ROW:
                    this.selectAndScrollToRow();
                    break;
                case constants.ON_FETCH_COMPLETE_CASE_REFRESH:
                    this.reset();
                    break;
                default:
                    break;

            }
            this.onFetchCompleteCase = constants.ON_FETCH_COMPLETE_CASE_NONE;
            if(this.editFromFilter){
                this.editFromFilter = false;
                if(this.initialAutoFocus){
                    this.focusTextBox();
                }
                else{
                    this.initialAutoFocus = true;
                }
            }
        },
        getSelectOptions: function(){
            this.filterFields.push(nls.none);
            var selectOptions = [],
                findByFields;
            var matchFound = false;
            for (var fieldArrayIndex in this.filterFields){
                if(typeof this.filterFields[fieldArrayIndex] === 'string'){
                    var newJsonObject = {};

                    for(structureIndex in this.grid.structure){
                        if(this.grid.structure[structureIndex].field == this.filterFields[fieldArrayIndex]){
                            newJsonObject[this.filterFields[fieldArrayIndex]] = this.grid.structure[structureIndex].name;
                            matchFound = true;
                        }
                    }
                    if(!matchFound){
                        newJsonObject[this.filterFields[fieldArrayIndex]] = this.filterFields[fieldArrayIndex].charAt(0).toUpperCase() + this.filterFields[fieldArrayIndex].replace('_', ' ').replace('-', ' ').slice(1);
                    }
                    matchFound = false;
                    this.filterFields[fieldArrayIndex] = newJsonObject;
                }
                for(var field in this.filterFields[fieldArrayIndex]){
                    findByFields = {};
                    findByFields['label'] = this.filterFields[fieldArrayIndex][field];
                    findByFields['value'] = field;
                    selectOptions.push(findByFields);
                }
            }

            this.setArrayOfColumnIndexes();

            return selectOptions;
        },
        setArrayOfColumnIndexes: function(){
            this.arrayOfColumnIndexes = [];
            for(var index in this.grid.structure){
                this.arrayOfColumnIndexes.push(index);
            }
            if(this.hasIndirectSelection){
                this.arrayOfColumnIndexes.push(this.arrayOfColumnIndexes.length);
            }
        },
        submit: function(){
            if (this.filterReady = true){
                this.filterReady = false;

                if(!this.fieldSelectionSelectedIndex){
                    this.updateFieldSelectionSelectedIndex();
                }

                this.reset();
                this.getMatchingRecords();
                this.startFilter();
            }
        },
        startFilter: function(){
            if(this.filterModeOff){
                if(this.arrayOfMatchingRowIndexes.length > 0){
                    this.cursor = (this.arrayOfMatchingRowIndexes.length)-1;
                    this.nextMatching();
                    this.filterModeOff = false;
                    this.disableButtons(false);
                }
                else{
                    this.displayNumberOfRecords();
                }
            }
            this.filterReady = true;
            this.focusTextBox();
        },
        getMatchingRecords: function(){
            var self = this, grid = this.grid;
            this.arrayOfMatchingRowIndexes = [];
            grid.store.fetch({
                query: this.generateStoreQuery(),
                queryOptions:{ignoreCase: true},
                onComplete: function(matchingRecords){
                    dojo.forEach(matchingRecords, function(item, index){
                        self.arrayOfMatchingRowIndexes.push(item._0);
                    });
                }
            });
        },
        getItemRowIndex: function(item){
            var grid = this.grid;
            var itemIndex = grid.getItemIndex(item);
            if(itemIndex == buildspace.constants.GRID_LAST_ROW){
                grid._fetch(item._0, false);
                itemIndex = grid.getItemIndex(item);
            }
            return itemIndex;
        },
        generateStoreQuery: function(){
            this.filterTextBox.set('value', dojo.trim(this.filterTextBox.get('value')));
            var storeQuery = {};
            storeQuery[this.getSelectedFieldName()] = '*'+this.filterTextBox.get('value').toLowerCase()+'*';
            return storeQuery;
        },
        getSelectedFieldName: function(){
            if(this.fieldSelection.get('value') == nls.none || this.filterTextBox.get('value') == ''){
                return nls.none;
            }
            else{
                return this.fieldSelection.get('value');
            }
        },
        filterTextBoxOnKeyPress: function(e){
            if(this.filterModeOff){
                if(e.keyCode == constants.KEY_CODE_ENTER){
                    this.submit();
                }
            }
            else{
                if(!this.disableKeyBoardShortcuts){
                    switch(e.keyCode){
                        case constants.KEY_CODE_TAB:
                            e.preventDefault();
                            this.onTabKey(e);
                            break;

                        case constants.KEY_CODE_ENTER:
                            this.onEnterKey(e);
                            break;

                        case constants.KEY_CODE_PAGE_UP:
                            this.firstMatching();
                            break;

                        case constants.KEY_CODE_PAGE_DOWN:
                            this.lastMatching();
                            break;

                        default:
                            break;
                    }
                }
            }

        },
        onTabKey: function(e){
            if(this.editableGrid){
                if(e.shiftKey){
                    this.previousColumn();
                }
                else{
                    this.nextColumn();
                }
            }
        },
        onEnterKey: function(e){
            if(e.ctrlKey){
                if(e.shiftKey){
                    this.doubleClickCell(e);
                }
                else{
                    this.nextMatching();
                }
            }
            else if (e.shiftKey){
                this.previousMatching();
            }
            else{
                if(this.hasIndirectSelection){
                    this.toggleSelection();
                }
                else{
                    this.editCell();
                }
            }
        },
        toggleSelection: function(){
            if(this.isRowSelected(this.arrayOfMatchingRowIndexes[this.cursor])){
                this.grid.selection.setSelected(this.arrayOfMatchingRowIndexes[this.cursor], false);
            }
            else{
                this.grid.selection.setSelected(this.arrayOfMatchingRowIndexes[this.cursor], true);
            }
        },
        isRowSelected: function(rowIndex){
            var grid = this.grid;
            for(var index in grid.selection.selected){
                if(index == rowIndex){
                    if(grid.selection.selected[index] == true){
                        return true;
                    }
                }
            }
            return false;
        },
        previousColumn: function(){
            this.columnIndexPointer--;
            if(this.columnIndexPointer < 0){
                this.columnIndexPointer = this.arrayOfColumnIndexes.length-1;
            }
            this.focusNewColumn();
        },
        nextColumn: function(){
            this.columnIndexPointer++;
            if(this.columnIndexPointer >= this.arrayOfColumnIndexes.length){
                this.columnIndexPointer = 0;
            }
            this.focusNewColumn();
        },
        focusNewColumn: function(){
            this.columnIndexToFocus = this.arrayOfColumnIndexes[this.columnIndexPointer];
            this.focusCell();
        },
        editCell: function(){
            if(this.editableGrid){
                this.editFromFilter = true;
                this.grid.edit.setEditCell(this.grid.getCell(this.columnIndexToFocus), this.arrayOfMatchingRowIndexes[this.cursor]);
            }
            else{
                this.focusCell();
            }
        },
        doubleClickCell: function(e){
            e.rowIndex = this.arrayOfMatchingRowIndexes[this.cursor];
            try{
                this.grid.dodblclick(e);
            }
            catch(error){
                // can't doubleclick
            }
        },
        displayNumberOfRecords: function(){
            if(this.arrayOfMatchingRowIndexes.length < 1){
                // no results
                if(this.fieldSelection.value == nls.none || this.filterTextBox.get('value') == ''){
                    this.numberOfRecordsDisplay.set('content', nls.noFilterApplied);
                }
                else{
                    this.numberOfRecordsDisplay.set('content', nls.noMatchingRecords);
                }
            }
            else{
                this.numberOfRecordsDisplay.set('content', (this.cursor + 1).toString()+' '+nls.of+' '+ this.arrayOfMatchingRowIndexes.length.toString() + ' '+nls.matches);
            }
        },
        validMatch: function(){
            return (this.arrayOfMatchingRowIndexes.length>0) && (this.cursor>=0 && this.cursor <= this.arrayOfMatchingRowIndexes.length);
        },
        focusCell: function(){
            this.focusFromFilter = true;
            this.grid.focus.setFocusIndex(this.arrayOfMatchingRowIndexes[this.cursor], this.columnIndexToFocus);
        },
        firstMatching: function(){
            if(this.validMatch()){
                this.onFetchCompleteCase = constants.ON_FETCH_COMPLETE_CASE_SELECT_AND_SCROLL_TO_ROW;
                this.cursor = 0;
                this.selectAndScrollToRow();
            }
        },
        lastMatching: function(){
            if(this.validMatch()){
                this.onFetchCompleteCase = constants.ON_FETCH_COMPLETE_CASE_SELECT_AND_SCROLL_TO_ROW;
                this.cursor = this.arrayOfMatchingRowIndexes.length - 1;
                this.selectAndScrollToRow();
            }
        },
        nextMatching: function(){
            if(this.validMatch()){
                this.onFetchCompleteCase = constants.ON_FETCH_COMPLETE_CASE_SELECT_AND_SCROLL_TO_ROW;
                this.incrementCursor();
                this.selectAndScrollToRow();
            }
        },
        previousMatching: function(){
            if(this.validMatch()){
                this.onFetchCompleteCase = constants.ON_FETCH_COMPLETE_CASE_SELECT_AND_SCROLL_TO_ROW;
                this.decrementCursor();
                this.selectAndScrollToRow();
            }
        },
        selectAndScrollToRow: function(){
            this.deselectAll();
            this.selectRow();
            this.grid.scrollToRow(this.arrayOfMatchingRowIndexes[this.cursor]);
            this.focusCell();
            this.displayNumberOfRecords();
        },
        deselectAll: function(){
            if(!this.hasIndirectSelection){
                this.grid.selection.deselectAll();
            }
        },
        selectRow: function(){
            if(!this.hasIndirectSelection){
                this.grid.selection.setSelected(this.arrayOfMatchingRowIndexes[this.cursor], true);
            }
        },
        focusTextBox: function(){
            if(this.autoFocus){
                this.filterTextBox.focus();
            }
        },
        onGridSave: function(){
            this.grid.focus._focusifyCellNode(false);
            this.arrayOfMatchingRowIndexes = [];
            this.cursor = 0;
            this.filterModeOff = true;
            this.disableButtons(true);

            this.numberOfRecordsDisplay.set('content', nls.noFilterApplied);
        },
        reset: function(){
            this.onGridSave();
            this.deselectAll();
        },
        refreshFilter: function(){
            this.grid.store.save();
            this.filterTextBox.set('value', '');
            this.updateFieldSelectionSelectedIndex();
            this.onFetchCompleteCase = constants.ON_FETCH_COMPLETE_CASE_REFRESH;
            this.grid.selection.deselectAll();
            this.refreshGrid();
        },
        refreshGrid: function(){
            this.grid.store.save();
            this.grid.store.close();
            this.grid.sort();
        },
        onFieldSelectionChange: function(){
            this.updateFieldSelectionSelectedIndex();
            if(this.filterTextBox){
                this.filterTextBox.focus();
            }
            if(this.filterTextBox.value){
                this.submit();
            }
        },
        updateFieldSelectionSelectedIndex: function(){
            //set fieldSelectionSelectedIndex to match with the selected value in the Select widget
            for(var index in this.fieldSelection.options){
                if(this.fieldSelection.options[index].selected == true){
                    this.fieldSelectionSelectedIndex = index;
                    for(var structureIndex in this.grid.structure){
                        if(this.grid.structure[structureIndex].name == this.fieldSelection.options[index]['label']){
                            if(!this.hasIndirectSelection){
                                this.columnIndexToFocus = this.columnIndexPointer = structureIndex;
                            }
                            else{
                                this.columnIndexToFocus = this.columnIndexPointer = Number(structureIndex)+1;
                            }
                            break;
                        }
                    }
                    break;
                }
            }
        },
        incrementCursor: function(){
            this.cursor++;
            if(this.cursor >= this.arrayOfMatchingRowIndexes.length){
                this.cursor = 0;
            }
        },
        decrementCursor: function(){
            this.cursor--;
            if(this.cursor<0){
                this.cursor = this.arrayOfMatchingRowIndexes.length-1;
            }
        },
        disableButtons: function(isDisable){
            this.nextButton._setDisabledAttr(isDisable);
            this.previousButton._setDisabledAttr(isDisable);
        },
        destroy: function(){
            this.inherited(arguments);
            dojo.forEach(this._connects, connect.disconnect);
            delete this._connects;
        }
    });
});