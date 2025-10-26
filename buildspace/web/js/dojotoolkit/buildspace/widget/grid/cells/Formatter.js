define('buildspace/widget/grid/cells/Formatter',[
    'dojo/_base/declare',
    'dojo/currency',
    'dojo/number',
    'dijit/form/Button',
    'dojo/_base/lang','dojo/i18n!buildspace/nls/Common'], function(declare, currency, number, Button, lang, nls ){
    var Formatter = declare("buildspace.widget.grid.cells.Formatter", null, {
        rowCountCellFormatter: function(cellValue, rowIdx, cell){
            var item = this.grid.getItem(rowIdx);
            if (item.type != undefined && parseInt(String(item.type)) < 1){
                cell.customClasses.push('invalidTypeItemCell');
            }

            if (item.has_note != undefined && item.has_note == 'true'){
                cell.customClasses.push('hasNoteTypeItemCell');
            }

            if (item.version != undefined  && parseInt(String(item.version)) > 0){
                if (this.grid.currentBillVersion != undefined && this.grid.currentBillVersion == item.version){
                    cell.customClasses.push('hasCurrentAddendumTypeItemCell');
                }else{
                    cell.customClasses.push('hasAddendumTypeItemCell');
                }
            }

            return parseInt(rowIdx)+1;
        },
        disabledCellFormatter: function(cellValue, rowIdx){
            var item = this.grid.getItem(rowIdx);

            if(item.disable == "Yes"){
                return '<span style="color:#808080">'+cellValue+'</span>';
            }else{
                return cellValue;
            }
        },

        analyzerDescriptionCellFormatter: function(cellValue, rowIdx){
            var item = this.grid.getItem(rowIdx);
            if(item && parseInt(String(item.id)) > 0){
                return cellValue;
            }else{
                return '<span style="color:#FF0000">'+cellValue+'</span>';
            }
        },
        allCellFormatter: function(cellValue, rowIdx){
            var item = this.grid.getItem(rowIdx);
            if(item && parseInt(String(item.id)) > 0){
                return '<a href="#" onclick="return false;">'+nls.all+'</a>';
            }else{
                return '&nbsp;';
            }
        },
        removeCellFormatter: function(cellValue, rowIdx){
            var item = this.grid.getItem(rowIdx);
            if(item && parseInt(String(item.id)) > 0){
                return '<a href="#" onclick="return false;">'+nls.remove+'</a>';
            }else{
                return '&nbsp;';
            }
        },
        deleteCellFormatter: function(cellValue, rowIdx){
            var item = this.grid.getItem(rowIdx);
            if(parseInt(String(item.id)) > 0){
                return '<a href="#" onclick="return false;">'+nls.delete+'</a>';
            }else{
                return '&nbsp;';
            }
        },
        moveUpCellFormatter: function(cellValue, rowIdx){
            var item = this.grid.getItem(rowIdx);
            if(item && parseInt(String(item.id)) > 0){
                return '<a href="#" onclick="return false;">'+nls.up+'</a>';
            }else{
                return '&nbsp;';
            }
        },
        moveDownCellFormatter: function(cellValue, rowIdx){
            var item = this.grid.getItem(rowIdx);
            if(item && parseInt(String(item.id)) > 0){
                return '<a href="#" onclick="return false;">'+nls.down+'</a>';
            }else{
                return '&nbsp;';
            }
        },
        selectedCellFormatter: function(cellValue, rowIdx){
            var item = this.grid.getItem(rowIdx),
                title = (item && parseInt(String(item.id)) > 0 && cellValue) ? nls.selected : nls.select;

            return (item && parseInt(String(item.id)) > 0) ? '<a href="#" onclick="return false;">'+title+'</a>' : '&nbsp;';
        },
        awardedCellFormatter: function(cellValue, rowIdx){
            var item = this.grid.getItem(rowIdx);
            return (item && parseInt(String(item.id)) > 0 && cellValue) ? '<span class="dijitReset dijitInline dijitIcon icon-16-container icon-16-checkmark2"></span>' : '&nbsp;';
        },
        includedCellFormatter: function(cellValue, rowIdx){
            var item = this.grid.getItem(rowIdx),
                title = (item && parseInt(String(item.id)) > 0 && cellValue) ? nls.included : nls.include;

            var color = cellValue ? 'green' : 'red';

            return (item && parseInt(String(item.id)) > 0) ? '<a href="#" onclick="return false;" style="color: '+color+';">'+title+'</a>' : '&nbsp;';
        },
        drillInCellFormatter: function(cellValue, rowIdx){
            var item = this.grid.getItem(rowIdx);
            if(item && parseInt(String(item.id)) > 0){
                return '<a href="#" onclick="return false;">'+nls.drillIn+'</a>';
            }else{
                return '&nbsp;';
            }
        },
        importRateButtonCellFormatter: function(cellValue, rowIdx, cell){
            var item = this.grid.getItem(rowIdx),
                rate = number.parse(item['rate-final_value']);
            if(item && parseInt(String(item.id)) > 0 && rate != 0 && !isNaN(rate) && rate != null && parseInt(String(item.type)) != buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER && parseInt(String(item.type)) != buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER_N && item.type != buildspace.widget.grid.constants.HIERARCHY_TYPE_NOID && (item.recalculate_resources_library_status != undefined && !item.recalculate_resources_library_status[0])){
                return '<a href="#" onclick="return false;">'+nls.import+'</a>';
            }else{
                cell.customClasses.push('disable-cell');
                return "&nbsp;";
            }
        },
        setAdminButtonCellFormatter: function(cellValue, rowIdx, cell){
            var item = this.grid.getItem(rowIdx);
            var isAdmin = (item && parseInt(String(item.id)) > 0 && cellValue) ? nls.yesCapital : nls.noCapital;

            return (item && parseInt(String(item.id)) > 0) ? '<a href="#" onclick="return false;">'+isAdmin+'</a>' : '&nbsp;';
        },
        recalculateBillCellFormatter: function(cellValue, rowIdx){
            var item = this.grid.getItem(rowIdx);
            if( item && (parseInt(String(item.id)) > 0) && (parseInt(String(item.level)) > 0) &&
                (parseInt(String(item.type)) == buildspace.constants.TYPE_BILL) &&
                (buildspace.isRecalculateBillStatus(item[ 'bill_status' ][ 0 ]))){
                return '<a href="#" onclick="return false;">'+nls.recalculate+'</a>';
            }else{
                return "&nbsp;";
            }
        },
        recalculateSorCellFormatter: function(cellValue, rowIdx) {
            var item = this.grid.getItem(rowIdx);

            if (item && parseInt(String(item.id)) > 0 && (item.recalculate_resources_library_status[0])) {
                return '<a href="#" onclick="return false;">'+nls.recalculate+'</a>';
            } else {
                return "&nbsp;";
            }
        },
        treeCellFormatter: function(cellValue, rowIdx, cell){
            var item = this.grid.getItem(rowIdx),
                level = parseInt(String(item.level))*16,
                textColor = 'black';
            cellValue = cellValue == null ? '&nbsp': cellValue;
            if(parseInt(String(item.type)) == buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER || parseInt(String(item.type)) == buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER_N){
                cellValue =  '<b>'+cellValue+'</b>';
            }else if(parseInt(String(item.type)) == buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_PC_RATE){
                var table = '<table class="prime_cost_rate-table">' +
                    '<tr><td class="label">&nbsp;</td><td colspan="3">'+nls.ratePerNo+'</td></tr>' +
                    '<tr><td class="label">'+nls.supplyRate+'</td><td style="width:10px;text-align:center;">'+buildspace.currencyAbbreviation+'</td><td style="width:50px;text-align:right;">'+currency.format(item.pc_supply_rate)+'</td><td></td></tr>' +
                    '<tr><td class="label">'+nls.wastage+' ('+number.format(item.pc_wastage_percentage, {places: 2})+'%)</td><td style="width:10px;text-align:center;">'+buildspace.currencyAbbreviation+'</td><td style="width:50px;text-align:right;">'+currency.format(item.pc_wastage_amount)+'</td><td></td></tr>' +
                    '<tr><td class="label">'+nls.labourForInstallation+'</td><td style="width:10px;text-align:center;">'+buildspace.currencyAbbreviation+'</td><td style="width:50px;text-align:right;">'+currency.format(item.pc_labour_for_installation)+'</td><td></td></tr>' +
                    '<tr><td class="label">'+nls.otherCost+'</td><td style="width:10px;text-align:center;">'+buildspace.currencyAbbreviation+'</td><td style="width:50px;text-align:right;">'+currency.format(item.pc_other_cost)+'</td><td></td></tr>' +
                    '<tr><td class="label">'+nls.profit+' ('+number.format(item.pc_profit_percentage, {places: 2})+'%)</td><td style="width:10px;text-align:center;">'+buildspace.currencyAbbreviation+'</td><td style="width:50px;text-align:right;">'+currency.format(item.pc_profit_amount)+'</td><td></td></tr>' +
                    '<tr><td class="label" style="text-align:right;font-weight:bold;">'+nls.total+'&nbsp;&nbsp;</td><td style="width:10px;text-align:center;">'+buildspace.currencyAbbreviation+'</td><td style="width:50px;border-top: 1px solid #1D1D1D;border-bottom: 1px solid #1D1D1D;font-weight:bold;text-align:right;">'+currency.format(item.pc_total)+'</td><td>&nbsp;</td></tr>' +
                    '</table>';
                cellValue = cellValue+'<br /><br />'+table;
            }

            if (item.has_note != undefined && item.has_note == 'true'){
                cell.customClasses.push('hasNoteTypeItemCell');
            }

            if (item.version != undefined  && parseInt(String(item.version)) > 0){
                if (this.grid.currentBillVersion != undefined && this.grid.currentBillVersion == item.version){
                    cell.customClasses.push('hasCurrentAddendumTypeItemCell');
                }else{
                    cell.customClasses.push('hasAddendumTypeItemCell');
                }
            }

            if (item.type != undefined && parseInt(String(item.type)) < 1) {
                cell.customClasses.push('invalidTypeItemCell');
            } else {
                if (item.project_revision_deleted_at != undefined && String(item.project_revision_deleted_at).toLowerCase() != 'false' && String(item.project_revision_deleted_at).length > 0) {
                    cellValue = '<div class="treeNode" style="color:' + textColor + '; padding-left:'+level+'px;"><div class="treeContent"><strike>'+cellValue+'&nbsp;</strike></div></div>';
                } else {
                    cellValue = '<div class="treeNode" style="color:' + textColor + '; padding-left:'+level+'px;"><div class="treeContent">'+cellValue+'&nbsp;</div></div>';
                }
            }

            return cellValue;
        },
        addendumInfoCellFormatter: function(cellValue, rowIdx, cell){
            var item = this.grid.getItem(rowIdx);
            var addendumInfo = "";
            if(item && item.addendum_version != undefined && item.is_add_latest_rev != undefined && parseInt(item.addendum_version)){
                var bgColor = (parseInt(item.is_add_latest_rev)) ? "bg-orange" : "bg-blue-sky";
                addendumInfo = '<span class="badge '+bgColor+'">'+item.addendum_version+'</span>&nbsp;';
            }
            return addendumInfo;
        },
        tenderAlternativeInfoCellFormatter: function(cellValue, rowIdx, cell){
            var item = this.grid.getItem(rowIdx);
            var tenderAlternativeInfo = "";
            if(item && item.tender_alternative_count != undefined && parseInt(item.tender_alternative_count)){
                tenderAlternativeInfo = '<span class="badge bg-blue-sky">'+item.tender_alternative_count+'</span>&nbsp;';
            }
            return tenderAlternativeInfo;
        },
        printPreviewTreeCellFormatter: function(cellValue, rowIdx, cell){
            var item = this.grid.getItem(rowIdx),
                level = item.level*16,
                textColor = 'black';
            cellValue = cellValue == null ? '&nbsp': cellValue;

            if(item.type && item.type[0] === 'tradeItem') {
                cellValue = '<span style="color: blue; font-weight: bold;">' + cellValue + '</span>';
            } else if(item.type && item.type < 1){
                cell.customClasses.push('invalidTypeItemCell');
            } else {
                if(item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER || item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER_N){
                    cellValue =  '<b>'+cellValue+'</b>';
                } else if(item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_PC_RATE){
                    var table = '<table class="prime_cost_rate-table">' +
                        '<tr><td class="label">&nbsp;</td><td colspan="3">'+nls.ratePerNo+'</td></tr>' +
                        '<tr><td class="label">'+nls.supplyRate+'</td><td style="width:10px;text-align:center;">'+buildspace.currencyAbbreviation+'</td><td style="width:50px;text-align:right;">'+currency.format(item.pc_supply_rate)+'</td><td></td></tr>' +
                        '<tr><td class="label">'+nls.wastage+' ('+number.format(item.pc_wastage_percentage, {places: 2})+'%)</td><td style="width:10px;text-align:center;">'+buildspace.currencyAbbreviation+'</td><td style="width:50px;text-align:right;">'+currency.format(item.pc_wastage_amount)+'</td><td></td></tr>' +
                        '<tr><td class="label">'+nls.labourForInstallation+'</td><td style="width:10px;text-align:center;">'+buildspace.currencyAbbreviation+'</td><td style="width:50px;text-align:right;">'+currency.format(item.pc_labour_for_installation)+'</td><td></td></tr>' +
                        '<tr><td class="label">'+nls.otherCost+'</td><td style="width:10px;text-align:center;">'+buildspace.currencyAbbreviation+'</td><td style="width:50px;text-align:right;">'+currency.format(item.pc_other_cost)+'</td><td></td></tr>' +
                        '<tr><td class="label">'+nls.profit+' ('+number.format(item.pc_profit_percentage, {places: 2})+'%)</td><td style="width:10px;text-align:center;">'+buildspace.currencyAbbreviation+'</td><td style="width:50px;text-align:right;">'+currency.format(item.pc_profit_amount)+'</td><td></td></tr>' +
                        '<tr><td class="label" style="text-align:right;font-weight:bold;">'+nls.total+'&nbsp;&nbsp;</td><td style="width:10px;text-align:center;">'+buildspace.currencyAbbreviation+'</td><td style="width:50px;border-top: 1px solid #1D1D1D;border-bottom: 1px solid #1D1D1D;font-weight:bold;text-align:right;">'+currency.format(item.pc_total)+'</td><td>&nbsp;</td></tr>' +
                        '</table>';
                    cellValue = cellValue+'<br /><br />'+table;
                }

                cellValue = '<div class="treeNode" style="color:' + textColor + '; padding-left:'+level+'px;"><div class="treeContent">'+cellValue+'&nbsp;</div></div>';
            }

            if (item['deleted'] && item['deleted'][0] == true)
            {
                cellValue = "<span style='text-decoration: line-through'>" + cellValue + "</span>";
            }

            return cellValue;
        },
        claimTreeCellFormatter: function(cellValue, rowIdx, cell){
            var item = this.grid.getItem(rowIdx),
                level = item.level*16,
                textColor = 'black';
            cellValue = cellValue == null ? '&nbsp': cellValue;

            if(item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER || item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER_N){
                cellValue =  '<b>'+cellValue+'</b>';
            }

            if (item.has_note != undefined && item.has_note == 'true'){
                cell.customClasses.push('hasNoteTypeItemCell');
            }

            cellValue = '<div class="treeNode" style="color:' + textColor + '; padding-left:'+level+'px;"><div class="treeContent">'+cellValue+'&nbsp;</div></div>';

            return cellValue;
        },
        postContractPrintPreviewTreeCellFormatter: function(cellValue, rowIdx, cell){
            var item = this.grid.getItem(rowIdx),
                level = item.level*16,
                textColor = 'black';
            cellValue = cellValue == null ? '&nbsp': cellValue;

            if(item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER || item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER_N){
                cellValue =  '<b>'+cellValue+'</b>';
            }

            if (item.type != undefined && item.type < 1){
                cell.customClasses.push('invalidTypeItemCell');
            } else {
                cellValue = '<div class="treeNode" style="color:' + textColor + '; padding-left:'+level+'px;"><div class="treeContent">'+cellValue+'&nbsp;</div></div>';
            }

            return cellValue;
        },
        typeListTreeCellFormatter: function(cellValue, rowIdx, cell){
            var item = this.grid.getItem(rowIdx),
                level = item.level*16,
                textColor = 'black',
                fontWeight = 'normal';

            cellValue = cellValue == null ? '&nbsp': cellValue;
            fontWeight = (item.level > 0) ? 'normal' : 'bold';

            cellValue = '<div class="treeNode" style="font-weight: ' + fontWeight + ';color:' + textColor + '; padding-left:'+level+'px;"><div class="treeContent">'+cellValue+'&nbsp;</div></div>';

            return cellValue;
        },
        typeListLevelFormatter: function (cellValue, rowIdx, cell) {
            var item = this.grid.getItem(rowIdx);

            if (item.level[0] === 0)
            {
                cell.customClasses.push('disable-cell');
            }

            return cellValue;
        },
        typeCellFormatter: function(cellValue, rowIdx, cell){
            var item = this.grid.getItem(rowIdx);
            switch (cellValue) {
                case buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER:
                    var headerNumber = isNaN(parseInt(item.level)) ? 1 : parseInt(item.level) + 1;
                    cellValue = buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER_TEXT+'&nbsp;'+headerNumber;
                    break;
                case buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER_N:
                    var headerNumber = isNaN(parseInt(item.level)) ? 1 : parseInt(item.level) + 1;
                    cellValue = buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER_N_TEXT+'&nbsp;'+headerNumber;
                    break;
                case buildspace.widget.grid.constants.HIERARCHY_TYPE_WORK_ITEM:
                    cellValue = buildspace.widget.grid.constants.HIERARCHY_TYPE_WORK_ITEM_TEXT;
                    break;
                case buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_HTML_EDITOR:
                    cellValue = buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_HTML_EDITOR_TEXT;
                    break;
                case buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_PROVISIONAL:
                    cellValue = buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_PROVISIONAL_TEXT;
                    break;
                case buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_RATE_ONLY:
                    cellValue = buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_RATE_ONLY_TEXT;
                    break;
                case buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_NOT_LISTED:
                    cellValue = buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_NOT_LISTED_TEXT;
                    break;
                case buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_PC_RATE:
                    cellValue = buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_PC_RATE_TEXT;
                    break;
                case buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_LUMP_SUM:
                    cellValue = buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_LUMP_SUM_TEXT;
                    break;
                case buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_LUMP_SUM_EXCLUDE:
                    cellValue = buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_LUMP_SUM_EXCLUDE_TEXT;
                    break;
                case buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_LUMP_SUM_PERCENT:
                    cellValue = buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_LUMP_SUM_PERCENT_TEXT;
                    break;
                default:
                    cellValue = buildspace.widget.grid.constants.HIERARCHY_TYPE_NOID_TEXT;
                    break;
            }

            if (item.has_note != undefined && item.has_note == 'true'){
                cell.customClasses.push('hasNoteTypeItemCell');
            }

            if (item.version != undefined  && item.version > 0){
                if (this.grid.currentBillVersion != undefined && this.grid.currentBillVersion == item.version){
                    cell.customClasses.push('hasCurrentAddendumTypeItemCell');
                }else{
                    cell.customClasses.push('hasAddendumTypeItemCell');
                }
            }

            return cellValue;
        },
        formulaCurrencyCellFormatter: function(cellValue, rowIdx, cell){
            var item = this.grid.getItem(rowIdx),
                value = number.parse(cellValue),
                formattedValue,
                fieldConstantName = this.field.replace("-value", ""),
                finalValue = item[fieldConstantName+'-final_value'][0],
                val = '&nbsp;';

            if (item.type != undefined && item.type < 1){
                cell.customClasses.push('invalidTypeItemCell');
            }

            if(isNaN(finalValue) || finalValue == 0 || finalValue == null){
                formattedValue = "&nbsp;";
            }else{
                formattedValue = currency.format(finalValue);
            }

            if(item[fieldConstantName+'-has_error'] != undefined && item[fieldConstantName+'-has_error'][0]){
                cell.customClasses.push('cell-error');
                val = '<span style="color:red;"><b>'+formattedValue+'</b></span>';
            }

            if (item.has_note != undefined && item.has_note == 'true'){
                cell.customClasses.push('hasNoteTypeItemCell');
            }

            if (item.version != undefined  && item.version > 0){
                if (this.grid.currentBillVersion != undefined && this.grid.currentBillVersion == item.version){
                    cell.customClasses.push('hasCurrentAddendumTypeItemCell');
                }else{
                    cell.customClasses.push('hasAddendumTypeItemCell');
                }
            }

            if(item[fieldConstantName+'-linked'] != undefined && item[fieldConstantName+'-linked'][0]){
                if(item[fieldConstantName+'-has_build_up'] != undefined && item[fieldConstantName+'-has_build_up'][0]){
                    val = '<span style="color:#42b449;"><strong>'+formattedValue+'</strong></span>';
                } else {
                    val = '<span style="color:#42b449;">'+formattedValue+'</span>';
                }
            }else if(item[fieldConstantName+'-has_build_up'] != undefined && item[fieldConstantName+'-has_build_up'][0]){
                val = '<span style="color:#0000FF;"><b>'+formattedValue+'</b></span>';
            }else if(item[fieldConstantName+'-has_formula'][0]){
                val = '<span style="color:#F78181;">'+formattedValue+'</span>';
            }else{
                val = finalValue >= 0 ? formattedValue : '<span style="color:#FF0000">'+formattedValue+'</span>';
            }

            if ( item.type && item.type[0] === 'tradeItem' ) {
                cell.customClasses.push('disable-cell');
                val = '&nbsp;';
            } else if(item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER || item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER_N || item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_NOID){
                cell.customClasses.push('disable-cell');
                val = '&nbsp;';
            }

            return val;
        },
        formulaPrelimCurrencyCellFormatter: function(cellValue, rowIdx, cell){
            var item = this.grid.getItem(rowIdx),
                value = number.parse(cellValue),
                val = '&nbsp;';
                fieldName = cell.field.split('-');

            if(isNaN(value) || value == 0 || value == null){
                val = "&nbsp;";
            }else{
                val = currency.format(value);
            }

            if(item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER || item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER_N || item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_NOID){
                cell.customClasses.push('disable-cell');
                val = '&nbsp;';
            } else if ( fieldName && (
                fieldName == buildspace.apps.PostContract.ProjectStructureConstants.PRELIM_COLUMN_RECURRING ||
                fieldName == buildspace.apps.PostContract.ProjectStructureConstants.PRELIM_COLUMN_TIMEBASED ||
                fieldName == buildspace.apps.PostContract.ProjectStructureConstants.PRELIM_COLUMN_WORKBASED
                ) ) {
                cell.customClasses.push('recurring-cell');
            }

            return val;
        },
        prelimPercentFormatter: function(cellValue, rowIdx, cell){
            var item = this.grid.getItem(rowIdx), fieldName = cell.fieldName;

            if ( item && item.grand_total && item.grand_total[0] == 0 ) {
                cellValue = "&nbsp;";
            }
            else if(isNaN(cellValue) || cellValue == 0){
                cellValue = "&nbsp;";
            } else {
                var formattedValue = number.format(cellValue, {places:2})+"%";

                if ( cell.fieldName == buildspace.apps.PostContract.ProjectStructureConstants.PRELIM_COLUMN_TIMEBASED || cell.fieldName == buildspace.apps.PostContract.ProjectStructureConstants.PRELIM_COLUMN_WORKBASED ) {
                    cellValue = cellValue > 0 ? '<span style="color:blue;">'+formattedValue+'</span>' : '<span style="color:#FF0000">'+formattedValue+'</span>';
                } else {
                    cellValue = cellValue > 0 ? '<span>'+formattedValue+'</span>' : '<span style="color:#FF0000">'+formattedValue+'</span>';
                }
            }

            if(item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER || item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER_N || item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_NOID){
                cell.customClasses.push('disable-cell');
                val = '&nbsp;';
            } else if ( fieldName && (
                fieldName == buildspace.apps.PostContract.ProjectStructureConstants.PRELIM_COLUMN_RECURRING ||
                fieldName == buildspace.apps.PostContract.ProjectStructureConstants.PRELIM_COLUMN_TIMEBASED ||
                fieldName == buildspace.apps.PostContract.ProjectStructureConstants.PRELIM_COLUMN_WORKBASED
                ) ) {
                cell.customClasses.push('recurring-cell');
            } else if ( fieldName && (
                fieldName == buildspace.apps.PostContract.ProjectStructureConstants.PRELIM_COLUMN_PREVIOUSCLAIM ||
                fieldName == buildspace.apps.PostContract.ProjectStructureConstants.PRELIM_COLUMN_CURRENTCLAIM ||
                fieldName == buildspace.apps.PostContract.ProjectStructureConstants.PRELIM_COLUMN_UPTODATECLAIM
                ) ) {
                cell.customClasses.push('disable-cell');
            }

            return cellValue;
        },
        prelimAmountFormatter: function(cellValue, rowIdx, cell){
            var item = this.grid.getItem(rowIdx),
                value = number.parse(cellValue),
                val = '&nbsp;',
                fieldName = cell.fieldName,
                formatterValue;

            if(isNaN(value) || value == 0 || value == null){
                formatterValue = "&nbsp;";
            }else{
                formatterValue = currency.format(value);

                if ( cell.fieldName == buildspace.apps.PostContract.ProjectStructureConstants.PRELIM_COLUMN_TIMEBASED || cell.fieldName == buildspace.apps.PostContract.ProjectStructureConstants.PRELIM_COLUMN_WORKBASED ) {
                    formatterValue = value > 0 ? '<span style="color:blue;">'+formatterValue+'</span>' : '<span style="color:#FF0000">'+formatterValue+'</span>';
                } else {
                    formatterValue = value > 0 ? '<span>'+formatterValue+'</span>' : '<span style="color:#FF0000">'+formatterValue+'</span>';
                }
            }

            if(item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER || item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER_N || item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_NOID){
                cell.customClasses.push('disable-cell');
                val = '&nbsp;';
            } else if ( fieldName && (
                fieldName == buildspace.apps.PostContract.ProjectStructureConstants.PRELIM_COLUMN_RECURRING ||
                fieldName == buildspace.apps.PostContract.ProjectStructureConstants.PRELIM_COLUMN_TIMEBASED ||
                fieldName == buildspace.apps.PostContract.ProjectStructureConstants.PRELIM_COLUMN_WORKBASED
                ) ) {
                cell.customClasses.push('recurring-cell');
            } else if ( fieldName && (
                fieldName == buildspace.apps.PostContract.ProjectStructureConstants.PRELIM_COLUMN_PREVIOUSCLAIM ||
                fieldName == buildspace.apps.PostContract.ProjectStructureConstants.PRELIM_COLUMN_CURRENTCLAIM ||
                fieldName == buildspace.apps.PostContract.ProjectStructureConstants.PRELIM_COLUMN_UPTODATECLAIM
                ) ) {
                cell.customClasses.push('disable-cell');
            }

            return formatterValue;
        },
        formulaNumberCellFormatter: function(cellValue, rowIdx, cell){
            var item = this.grid.getItem(rowIdx),
                value = number.parse(cellValue),
                fieldConstantName = this.field.replace("-value", ""),
                finalValue = item[fieldConstantName+'-final_value'][0],
                val = '&nbsp;';

            if(isNaN(finalValue) || finalValue == 0 || finalValue == null){
                var formattedValue = "&nbsp;";
            }else{
                var formattedValue = number.format(finalValue, {places: 2});
            }

            if(item[fieldConstantName+'-has_build_up'] != undefined && item[fieldConstantName+'-has_build_up'][0]){
                val = '<span style="color:#0000FF;"><b>'+formattedValue+'</b></span>';
            }else if(item[fieldConstantName+'-linked'] != undefined && item[fieldConstantName+'-linked'][0]){
                val = '<span style="color:#42b449;">'+formattedValue+'</span>';
            }else if(item[fieldConstantName+'-has_formula'][0]){
                val = '<span style="color:#F78181;">'+formattedValue+'</span>';
            }else{
                val = finalValue >= 0 ? formattedValue : '<span style="color:#FF0000">'+formattedValue+'</span>';
            }

            if(item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER || item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER_N || item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_NOID){
                cell.customClasses.push('disable-cell');
                val = '&nbsp;';
            }

            return val;
        },
        recalculateCellFormatter: function(cellValue, rowIdx, cell){
            var item = this.grid.getItem(rowIdx),
                status = item.bill_status;

            if(cellValue){
                return '<a href="#" onclick="return false;">'+nls.recalculate+'</a>';
            }else{

                if(item.id == 'item' && (status != buildspace.constants.BILL_STATUS_RESOURCE_ANALYSIS_RECALCULATE_ITEM || status != buildspace.constants.BILL_STATUS_SCHEDULE_OF_RATE_ANALYSIS_RECALCULATE_ITEM || status != buildspace.constants.BILL_STATUS_SCHEDULE_OF_QUANTITY_RECALCULATE_ITEM)){
                    return '&nbsp;';
                }

                if(item.id == 'element' && (status == buildspace.constants.BILL_STATUS_RESOURCE_ANALYSIS_RECALCULATE_BILL || status == buildspace.constants.BILL_STATUS_SCHEDULE_OF_RATE_ANALYSIS_RECALCULATE_BILL || status == buildspace.constants.BILL_STATUS_SCHEDULE_OF_QUANTITY_RECALCULATE_BILL)){
                    return '&nbsp;';
                }

                if(item.id == 'bill' && status == buildspace.constants.BILL_STATUS_OPEN){
                    return '&nbsp;';
                }

                return '<span style="color:#ee9f05">'+nls.pending+'</span>';
            }
        },
        currencyCellFormatter: function(cellValue, rowIdx, cell){
            var item = this.grid.getItem(rowIdx),
                value = number.parse(cellValue);

            if(isNaN(value) || value == 0 || value == null){
                cellValue = "&nbsp;";
            }else{
                cellValue = currency.format(value);
                cellValue = value >= 0 ? cellValue : '<span style="color:#FF0000">'+cellValue+'</span>';
            }

            if (item && item.hasOwnProperty('type') && item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER || item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER_N || item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_NOID){
                cell.customClasses.push('disable-cell');
                cellValue = '&nbsp;';
            }

            if (item.type != undefined && item.type < 1){
                cell.customClasses.push('invalidTypeItemCell');
            }

            return cellValue;
        },
        companyRateCurrencyCellFormatter: function(cellValue, rowIdx, cell){
            var item = this.grid.getItem(rowIdx),
                value = number.parse(cellValue);

            if (item.type != undefined && item.type < 1){
                cell.customClasses.push('invalidTypeItemCell');
            }

            if(item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER || item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER_N || item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_NOID){
                cell.customClasses.push('disable-cell');
                cellValue = '&nbsp;';
            }

            if(isNaN(value) || value == 0 || value == null){
                cellValue = "&nbsp;";
            }else{
                var formattedValue = number.format(value, {places:2})
                cellValue = value >= 0 ? formattedValue : '<span style="color:#FF0000">'+formattedValue+'</span>';
            }
            return cellValue;
        },
        numberCellFormatter: function(cellValue, rowIdx){
            var value = number.parse(cellValue);
            if(isNaN(value) || value == 0 || value == null){
                cellValue = "&nbsp;";
            }else{
                var formattedValue = number.format(value, {places:2})
                cellValue = value >= 0 ? formattedValue : '<span style="color:#FF0000">'+formattedValue+'</span>';
            }
            return cellValue;
        },
        unitIdCellFormatter: function(cellValue, rowIdx, cell){
            var item = this.grid.getItem(rowIdx);
            var val = item.uom_symbol;
            var fieldName = cell.field;
            if (item.type != undefined && item.type < 1){
                cell.customClasses.push('invalidTypeItemCell');
            }
            if (item.has_note != undefined && item.has_note == 'true'){
                cell.customClasses.push('hasNoteTypeItemCell');
            }
            if (item.version != undefined  && item.version > 0){
                if (this.grid.currentBillVersion != undefined && this.grid.currentBillVersion == item.version){
                    cell.customClasses.push('hasCurrentAddendumTypeItemCell');
                }else{
                    cell.customClasses.push('hasAddendumTypeItemCell');
                }
            }
            if(item[fieldName+'-has_error'] != undefined && item[fieldName+'-has_error'][0]){
                cell.customClasses.push('cell-error');
            }

            if ( item.type && item.type[0] === 'tradeItem' ) {
                cell.customClasses.push('disable-cell');
                val = '&nbsp;';
            }
            else if (item.type != undefined && item.type < 1){
                cell.customClasses.push('invalidTypeItemCell');
                val = '&nbsp;';
            }
            else if(item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER || item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER_N){
                cell.customClasses.push('disable-cell');
                val = '&nbsp;';
            }
            return val;
        },
        rfqUnitCellFormatter: function(cellValue, rowIdx, cell){
            var item      = this.grid.getItem(rowIdx);
            var val       = item.uom;
            var fieldName = cell.field;

            if(item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER || item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER_N){
                cell.customClasses.push('disable-cell');
                val = '&nbsp;';
            }

            if (item.type != undefined && item.type < 1){
                cell.customClasses.push('invalidTypeItemCell');
            }

            return val;
        },
        printPreviewSignCellFormatter: function(cellValue, rowIdx, cell){
            var item = this.grid.getItem(rowIdx);

            if (item.type != undefined && item.type < 1){
                cell.customClasses.push('invalidTypeItemCell');
                val = '&nbsp;';
            }
            else if(item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER || item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER_N){
                cell.customClasses.push('disable-cell');
                val = '&nbsp;';
            }

            return item.sign_symbol;
        },
        signCellFormatter: function(cellValue, rowIdx){
            var item = this.grid.getItem(rowIdx);
            return item.sign_symbol;
        },
        yesNoCellFormatter: function(cellValue, rowIdx, cell){
            var item = this.grid.getItem(rowIdx);
            var val = cellValue == 'true' ? nls.yesCapital : nls.noCapital;
            if(item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER || item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER_N || item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_NOID || item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_RATE_ONLY){
                cell.customClasses.push('disable-cell');
                val = '&nbsp;';
            }

            if (item.has_note != undefined && item.has_note == 'true'){
                cell.customClasses.push('hasNoteTypeItemCell');
            }
            if (item.version != undefined  && item.version > 0){
                if (this.grid.currentBillVersion != undefined && this.grid.currentBillVersion == item.version){
                    cell.customClasses.push('hasCurrentAddendumTypeItemCell');
                }else{
                    cell.customClasses.push('hasAddendumTypeItemCell');
                }
            }
            return val;
        },
        linkedCellFormatter: function(cellValue, rowIdx){
            var item = this.grid.getItem(rowIdx);

            if(item.linked != undefined && item.linked[0]){
                return '<span style="color:#42b449;">'+cellValue+'</span>';
            }else{
                return cellValue;
            }
        },
        unitCellFormatter: function(cellValue, rowIdx, cell){
            var item = this.grid.getItem(rowIdx);
            return item.uom_symbol;
        },
        unEditableUnitCellFormatter: function(cellValue, rowIdx, cell){
            var item = this.grid.getItem(rowIdx);
            cell.customClasses.push('disable-cell');
            return item.uom_symbol;
        },
        linkedUnitIdCellFormatter: function(cellValue, rowIdx, cell){
            var item = this.grid.getItem(rowIdx);
            var val = item.uom_symbol;
            if(item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER || item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER_N || item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_NOID){
                cell.customClasses.push('disable-cell');
                val = '&nbsp;';
            }

            if(item.linked != undefined && item.linked[0]){
                return '<span style="color:#42b449;">'+val+'</span>';
            }else{
                return val;
            }
        },
        linkedNumberCellFormatter: function(cellValue, rowIdx, cell){
            var item = this.grid.getItem(rowIdx),
                value = number.parse(cellValue),
                val = '&nbsp;';

            if(isNaN(value) || value == 0 || value == null){
                var formattedValue = "&nbsp;";
            }else{
                var formattedValue = number.format(value,{places:2});
            }

            if(item.linked != undefined && item.linked[0]){
                val = '<span style="color:#42b449;">'+formattedValue+'</span>';
            }else if(cellValue < 0){
                val = '<span style="color:#FF0000">'+formattedValue+'</span>';
            }else{
                val = formattedValue;
            }

            if(item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER || item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER_N || item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_NOID){
                cell.customClasses.push('disable-cell');
                val = '&nbsp;';
            }
            return val;
        },
        linkedCurrencyCellFormatter: function(cellValue, rowIdx, cell){
            var item = this.grid.getItem(rowIdx),
                value = number.parse(cellValue),
                val = '&nbsp;';

            if(isNaN(value) || value == 0 || value == null){
                var formattedValue = "&nbsp;";
            }else{
                var formattedValue = currency.format(value);
            }

            if(item.linked != undefined && item.linked[0]){
                val = '<span style="color:#42b449;">'+formattedValue+'</span>';
            }else if(cellValue < 0){
                val = '<span style="color:#FF0000">'+formattedValue+'</span>';
            }else{
                val = formattedValue;
            }

            if(item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER || item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER_N || item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_NOID){
                cell.customClasses.push('disable-cell');
                val = '&nbsp;';
            }
            return val;
        },
        linkedPercentageCellFormatter: function(cellValue, rowIdx, cell){
            var item = this.grid.getItem(rowIdx),
                value = number.parse(cellValue),
                val = '&nbsp;';

            if(isNaN(value) || value == 0 || value == null){
                var formattedValue = "&nbsp;";
            }else{
                var formattedValue = number.format(value,{places:2})+"%";
            }

            if(item.linked != undefined && item.linked[0]){
                val = '<span style="color:#42b449;">'+formattedValue+'</span>';
            }else if(cellValue < 0){
                val = '<span style="color:#FF0000">'+formattedValue+'</span>';
            }else{
                val = formattedValue;
            }

            if(item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER || item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER_N || item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_NOID){
                cell.customClasses.push('disable-cell');
                val = '&nbsp;';
            }
            return val;
        },
        formulaPercentageCellFormatter: function(cellValue, rowIdx, cell){
            var item = this.grid.getItem(rowIdx),
                value = number.parse(cellValue),
                fieldConstantName = this.field.replace("-value", ""),
                finalValue = item[fieldConstantName+'-final_value'][0],
                val = '&nbsp;';

            if(isNaN(finalValue) || finalValue == 0 || finalValue == null){
                var formattedValue = "&nbsp;";
            }else{
                var formattedValue = number.format(finalValue, {places: 2})+"%";
            }

            if(item[fieldConstantName+'-has_build_up'] != undefined && item[fieldConstantName+'-has_build_up'][0]){
                val = '<span style="color:#0000FF;"><b>'+formattedValue+'</b></span>';
            }else if(item[fieldConstantName+'-linked'] != undefined && item[fieldConstantName+'-linked'][0]){
                val = '<span style="color:#42b449;">'+formattedValue+'</span>';
            }else if(item[fieldConstantName+'-has_formula'][0]){
                val = '<span style="color:#F78181;">'+formattedValue+'</span>';
            }else{
                val = finalValue >= 0 ? formattedValue : '<span style="color:#FF0000">'+formattedValue+'</span>';
            }

            if (item.type != undefined && item.type < 1){
                cell.customClasses.push('invalidTypeItemCell');
            }

            if(item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER || item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER_N || item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_NOID){
                cell.customClasses.push('disable-cell');
                val = '&nbsp;';
            }

            return val;
        },
        unEditableCurrencyCellFormatter: function(cellValue, rowIdx, cell){
            var item = this.grid.getItem(rowIdx),
                value = number.parse(cellValue);
            if(isNaN(value) || value == 0 || value == null){
                cellValue = "&nbsp;";
            }else{
                var formattedValue = currency.format(value);
                cellValue = value >= 0 ? formattedValue : '<span style="color:#FF0000">'+formattedValue+'</span>';
            }

            if (item.type != undefined && item.type < 1){
                cell.customClasses.push('invalidTypeItemCell');
            }else{
                cell.customClasses.push('disable-cell');
            }

            if (item['deleted'] && item['deleted'][0] == true)
            {
                cellValue = "<span style='text-decoration: line-through'>" + cellValue + "</span>";
            }

            return cellValue;
        },
        printPreviewTendererCurrencyCellFormatter: function(cellValue, rowIdx, cell){
            var item = this.grid.getItem(rowIdx),
                value = number.parse(cellValue),
                tendererId = this.field.replace("-grand_total", "");

            if(isNaN(value) || value == 0 || value == null){
                cellValue = "&nbsp;";
            }else{
                var formattedValue = currency.format(value);
                cellValue = value >= 0 ? formattedValue : '<span style="color:#FF0000">'+formattedValue+'</span>';
            }

            if (item.type != undefined && item.type < 1){
                cell.customClasses.push('invalidTypeItemCell');
            } else {
                if ( item[tendererId+'-lowest_cost'] && item[tendererId+'-lowest_cost'][0] ) {
                    cell.customClasses.push('lowest-cost-cell');
                } else if ( item[tendererId+'-highest_cost'] && item[tendererId+'-highest_cost'][0] ) {
                    cellValue = "<span style=\"color:white;\">" + cellValue + "</span>";

                    cell.customClasses.push('highest-cost-cell');
                } else {
                    cell.customClasses.push('disable-cell');
                }
            }

            if (item['deleted'] && item['deleted'][0] == true)
            {
                cellValue = "<span style='text-decoration: line-through'>" + cellValue + "</span>";
            }

            return cellValue;
        },
        printPreviewTendererRateCellFormatter: function(cellValue, rowIdx, cell){
            var item = this.grid.getItem(rowIdx),
                value = number.parse(cellValue)
                tendererId = this.field.replace("-rate-value", "");

            if(isNaN(value) || value == 0 || value == null){
                cellValue = "&nbsp;";
            }else{
                var formattedValue = currency.format(value);
                cellValue = value >= 0 ? formattedValue : '<span style="color:#FF0000">'+formattedValue+'</span>';
            }

            if (item.type != undefined && item.type < 1){
                cell.customClasses.push('invalidTypeItemCell');
            } else {
                if ( item[tendererId+'-lowest_cost'] && item[tendererId+'-lowest_cost'][0] ) {
                    cell.customClasses.push('lowest-cost-cell');
                } else if ( item[tendererId+'-highest_cost'] && item[tendererId+'-highest_cost'][0] ) {
                    cellValue = "<span style=\"color:white;\">" + cellValue + "</span>";

                    cell.customClasses.push('highest-cost-cell');
                } else {
                    cell.customClasses.push('disable-cell');
                }
            }

            if (item['deleted'] && item['deleted'][0] == true)
            {
                cellValue = "<span style='text-decoration: line-through'>" + cellValue + "</span>";
            }

            return cellValue;
        },
        unEditableNumberCellFormatter: function(cellValue, rowIdx, cell){
            var item = this.grid.getItem(rowIdx),
                value = number.parse(cellValue);
            if(isNaN(value) || value == 0 || value == null){
                cellValue = "&nbsp;";
            }else{
                var formattedValue = number.format(value, {places:2});
                cellValue = value >= 0 ? formattedValue : '<span style="color:#FF0000">'+formattedValue+'</span>';
            }

            if (item.type != undefined && item.type < 1){
                cell.customClasses.push('invalidTypeItemCell');
            }else{
                cell.customClasses.push('disable-cell');
            }

            return cellValue;
        },
        unEditableNumberAndTextCellFormatter: function(cellValue, rowIdx, cell){
            var item = this.grid.getItem(rowIdx),
                value = number.parse(cellValue);
            if(isNaN(value) || value == 0 || value == null){
                if( (typeof cellValue != "string") || cellValue == 0)
                {
                    cellValue = "&nbsp;";
                }
            }else{
                var formattedValue = number.format(value, {places:2});
                cellValue = value >= 0 ? formattedValue : '<span style="color:#FF0000">'+formattedValue+'</span>';
            }

            if (item.type != undefined && item.type < 1){
                cell.customClasses.push('invalidTypeItemCell');
            }else{
                cell.customClasses.push('disable-cell');
            }

            return cellValue;
        },
        unEditableIntegerCellFormatter: function(cellValue, rowIdx, cell){
            var item = this.grid.getItem(rowIdx),
                value = number.parse(cellValue);
            if(isNaN(value) || value == 0 || value == null){
                cellValue = "&nbsp;";
            }else{
                var formattedValue = parseInt(value);
                cellValue = value >= 0 ? formattedValue : '<span style="color:#FF0000">'+formattedValue+'</span>';
            }

            if (item.type != undefined && item.type < 1){
                cell.customClasses.push('invalidTypeItemCell');
            }else{
                cell.customClasses.push('disable-cell');
            }

            return cellValue;
        },
        unEditablePercentageCellFormatter: function(cellValue, rowIdx, cell){
            var item = this.grid.getItem(rowIdx),
                value = number.parse(cellValue);
            if(isNaN(value) || value == 0 || value == null){
                cellValue = "&nbsp;";
            }else{
                var formattedValue = number.format(value, {places:2})+"%";
                cellValue = value >= 0 ? '<span style="color:blue;">'+formattedValue+'</span>' : '<span style="color:#FF0000">'+formattedValue+'</span>';
            }

            if (item.type != undefined && item.type < 1){
                cell.customClasses.push('invalidTypeItemCell');
            } else {
                cell.customClasses.push('disable-cell');
            }

            return cellValue;
        },
        editablePercentageCellFormatter: function(cellValue, rowIdx, cell){
            var value = number.parse(cellValue),
                item = this.grid.getItem(rowIdx);

            if(isNaN(value) || value == 0 || value == null){
                cellValue = "&nbsp;";
            }else{
                var formattedValue = number.format(value, {places:2})+"%";
                cellValue = value >= 0 ? '<span style="color:blue;">'+formattedValue+'</span>' : '<span style="color:#FF0000">'+formattedValue+'</span>';
            }

            if (item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER || item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER_N || item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_NOID || item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_RATE_ONLY) {
                cell.customClasses.push('disable-cell');
            }

            return cellValue;
        },
        elementClaimEditablePercentageCellFormatter: function(cellValue, rowIdx, cell){
            var value = number.parse(cellValue),
                item = this.grid.getItem(rowIdx);

            if(isNaN(value) || value == 0 || value == null){
                cellValue = "&nbsp;";
            }else {
                var formattedValue = number.format(value, {places: 2}) + "%";
                cellValue = value >= 0 ? '<span style="color:blue;">' + formattedValue + '</span>' : '<span style="color:#FF0000">' + formattedValue + '</span>';
            }

            if(item.total_per_unit === undefined) {
                cell.customClasses.push('disable-cell');
            } else if ( item.total_per_unit[0] == '0.00000' ) {
                cell.customClasses.push('disable-cell');
            }

            return cellValue;
        },
        unEditableCellFormatter: function(cellValue, rowIdx, cell){
            var item = this.grid.getItem(rowIdx);

            if(cellValue == undefined) cellValue = "";

            if (item.type != undefined && item.type < 1){
                cell.customClasses.push('invalidTypeItemCell');
            }else{
                cell.customClasses.push('disable-cell');
            }

            return cellValue;
        },
        billQuantityCellFormatter: function(cellValue, rowIdx, cell){
            var item = this.grid.getItem(rowIdx),
                value = number.parse(cellValue),
                fieldConstantName = this.field.replace("-value", ""),
                finalValue = item[fieldConstantName+'-final_value'][0],
                val = '&nbsp;';

            if(isNaN(finalValue) || finalValue == 0 || finalValue == null){
                var formattedValue = "&nbsp;";
            }else{
                var formattedValue = number.format(finalValue, {places: 2});
            }

            if (item.has_note != undefined && item.has_note == 'true'){
                cell.customClasses.push('hasNoteTypeItemCell');
            }

            if (item.version != undefined  && item.version > 0){
                if (this.grid.currentBillVersion != undefined && this.grid.currentBillVersion == item.version){
                    cell.customClasses.push('hasCurrentAddendumTypeItemCell');
                }else{
                    cell.customClasses.push('hasAddendumTypeItemCell');
                }
            }

            if(item[fieldConstantName+'-has_build_up'] != undefined && item[fieldConstantName+'-has_build_up'][0]){
                val = '<span style="color:#0000FF;"><b>'+formattedValue+'</b></span>';
            }else if(item[fieldConstantName+'-linked'] != undefined && item[fieldConstantName+'-linked'][0]){
                val = '<span style="color:#42b449;">'+formattedValue+'</span>';
            }else if(item[fieldConstantName+'-has_formula'] != undefined && item[fieldConstantName+'-has_formula'][0]){
                val = '<span style="color:#F78181;">'+formattedValue+'</span>';
            }else{
                val = finalValue >= 0 ? formattedValue : '<span style="color:#FF0000">'+formattedValue+'</span>';
            }

            var str = this.field.split('-');

            if (item.type != undefined && item.type < 1) {
                cell.customClasses.push('invalidTypeItemCell');
                val = "&nbsp;";
            } else if((item[str[0]+'-include'] != undefined && item[str[0]+'-include'][0]=="false") || item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER || item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER_N || item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_NOID || item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_RATE_ONLY) {
                cell.customClasses.push('disable-cell');
                val = item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_RATE_ONLY ? nls.rateOnly : '&nbsp;';
            }

            return val;
        },
        postContractRemeasurementQuantityCellFormatter: function(cellValue, rowIdx, cell){
            var item = this.grid.getItem(rowIdx),
                value = number.parse(cellValue),
                fieldConstantName = this.field.replace("-qty_per_unit", ""),
                finalValue = cellValue,
                val = '&nbsp;';

            if(isNaN(finalValue) || finalValue == 0 || finalValue == null){
                var formattedValue = "&nbsp;";
            }else{
                var formattedValue = number.format(finalValue, {places: 2});
            }

            if (item.has_note != undefined && item.has_note == 'true'){
                cell.customClasses.push('hasNoteTypeItemCell');
            }

            if (item.version != undefined  && item.version > 0){
                if (this.grid.currentBillVersion != undefined && this.grid.currentBillVersion == item.version){
                    cell.customClasses.push('hasCurrentAddendumTypeItemCell');
                }else{
                    cell.customClasses.push('hasAddendumTypeItemCell');
                }
            }

            if(item[fieldConstantName+'-has_build_up'] != undefined && item[fieldConstantName+'-has_build_up'][0]) {
                val = '<span style="color:#0000FF;"><b>'+formattedValue+'</b></span>';
            } else {
                val = finalValue >= 0 ? formattedValue : '<span style="color:#FF0000">'+formattedValue+'</span>';
            }

            if (item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER || item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER_N || item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_NOID || item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_RATE_ONLY){
                cell.customClasses.push('disable-cell');
                val = item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_RATE_ONLY ? nls.rateOnly : '&nbsp;';
            } else if ( fieldConstantName == 'omission' ) {
                cell.customClasses.push('disable-cell');
            } else if ( item && item.id[0] > 0 && ! item.include[0] ) {
                cell.customClasses.push('disable-cell');
            }

            return val;
        },
        postContractPreliminariesQuantityCellFormatter: function(cellValue, rowIdx, cell){
            var item = this.grid.getItem(rowIdx),
                value = number.parse(cellValue),
                fieldConstantName = this.field.replace("-qty_per_unit", ""),
                finalValue = cellValue,
                val = '&nbsp;';

            if(isNaN(finalValue) || finalValue == 0 || finalValue == null){
                var formattedValue = "&nbsp;";
            }else{
                var formattedValue = number.format(finalValue, {places: 2});
            }

            if (item.has_note != undefined && item.has_note == 'true'){
                cell.customClasses.push('hasNoteTypeItemCell');
            }

            if (item.version != undefined  && item.version > 0){
                if (this.grid.currentBillVersion != undefined && this.grid.currentBillVersion == item.version){
                    cell.customClasses.push('hasCurrentAddendumTypeItemCell');
                }else{
                    cell.customClasses.push('hasAddendumTypeItemCell');
                }
            }

            if(item[fieldConstantName+'-has_build_up'] != undefined && item[fieldConstantName+'-has_build_up'][0]) {
                val = '<span style="color:#0000FF;"><b>'+formattedValue+'</b></span>';
            } else {
                val = finalValue >= 0 ? formattedValue : '<span style="color:#FF0000">'+formattedValue+'</span>';
            }

            cell.customClasses.push('disable-cell');

            if (item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER || item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER_N || item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_NOID || item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_RATE_ONLY) {
                val = item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_RATE_ONLY ? nls.rateOnly : '&nbsp;';
            }

            return val;
        },
        claimAmountCellFormatter : function (cellValue, rowIdx, cell)
        {
            var item = this.grid.getItem(rowIdx),
                value = number.parse(cellValue);

            if(isNaN(value) || value == 0 || value == null){
                cellValue = "&nbsp;";
            }else{
                cellValue = currency.format(value);
                cellValue = value >= 0 ? cellValue : '<span style="color:#FF0000">'+cellValue+'</span>';
            }

            var totalPerUnit = number.parse(item['total_per_unit']);

            if((item['include'] && item['include'][0] == 0) || totalPerUnit <= 0 || item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER || item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER_N || item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_NOID || item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_RATE_ONLY){
                cell.customClasses.push('disable-cell');
            }

            return cellValue;
        },
        elementClaimAmountCellFormatter : function (cellValue, rowIdx, cell)
        {
            var item = this.grid.getItem(rowIdx),
                value = number.parse(cellValue);

            if(isNaN(value) || value == 0 || value == null){
                cellValue = "&nbsp;";
            }else{
                cellValue = currency.format(value);
                cellValue = value >= 0 ? cellValue : '<span style="color:#FF0000">'+cellValue+'</span>';
            }

            if(item.total_per_unit === undefined){
                cell.customClasses.push('disable-cell');
            } else if ( item.total_per_unit[0] == '0.00000' ) {
                cell.customClasses.push('disable-cell');
            }

            return cellValue;
        },
        claimRateLSCellFormatter : function (cellValue, rowIdx, cell)
        {
            var item = this.grid.getItem(rowIdx),
                value = number.parse(cellValue);

            if(isNaN(value) || value == 0 || value == null){
                cellValue = "&nbsp;";
            }else{
                cellValue = currency.format(value);
                cellValue = value >= 0 ? cellValue : '<span style="color:#FF0000">'+cellValue+'</span>';
            }

            if(item.type != buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_LUMP_SUM_PERCENT && item.type != buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_LUMP_SUM_EXCLUDE ||
                (number.parse(item.up_to_date_percentage) || number.parse(item.current_percentage) || number.parse(item.prev_percentage))
            ){
                cell.customClasses.push('disable-cell');
            }

            return cellValue;
        },
        editableClaimPercentageCellFormatter: function(cellValue, rowIdx, cell){
            var item = this.grid.getItem(rowIdx),
                value = number.parse(cellValue);

            if(isNaN(value) || value == 0 || value == null){
                cellValue = "&nbsp;";
            }else{
                var formattedValue = number.format(value, {places:2})+"%";
                cellValue = value >= 0 ? '<span style="color:blue;">'+formattedValue+'</span>' : '<span style="color:#FF0000">'+formattedValue+'</span>';
            }

            var totalPerUnit = number.parse(item['total_per_unit']);

            if((item['include'] && item['include'][0] == 0) || totalPerUnit <= 0 || item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER || item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER_N || item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_NOID || item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_RATE_ONLY){
                cell.customClasses.push('disable-cell');
            }

            return cellValue;
        },
        claimQtyPerUnitCellFormatter: function(cellValue, rowIdx, cell){
            var item = this.grid.getItem(rowIdx),
                value = number.parse(cellValue);

            if(isNaN(value) || value == 0 || value == null){
                var formattedValue = "&nbsp;";
            }else{
                var formattedValue = number.format(value, {places:2});
            }

            if(item[this.field+'-has_build_up'] != undefined && item[this.field+'-has_build_up'][0]){
                cellValue = '<span style="color:#0000FF;"><b>'+formattedValue+'</b></span>';
            }else{
                cellValue = value >= 0 ? formattedValue : '<span style="color:#FF0000">'+formattedValue+'</span>';
            }

            if (item.type != undefined && item.type < 1){
                cell.customClasses.push('invalidTypeItemCell');
            }else{
                cell.customClasses.push('disable-cell');
            }

            return cellValue;
        },
        rfqQuantityCellFormatter: function(cellValue, rowIdx, cell){
            var item = this.grid.getItem(rowIdx),
                value = number.parse(cellValue);

            if(isNaN(value) || value == 0 || value == null){
                var formattedValue = "&nbsp;";
            }else{
                var formattedValue = number.format(value, {places: 2});
            }

            if(item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER || item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER_N){
                cell.customClasses.push('disable-cell');
                formattedValue = '&nbsp;';
            }

            if (item.type != undefined && item.type < 1){
                cell.customClasses.push('invalidTypeItemCell');
            }

            return formattedValue;
        },
        rfqRemarkCellFormatter: function(cellValue, rowIdx, cell){
            var item = this.grid.getItem(rowIdx),
                val = cellValue;

            if(item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER || item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER_N){
                cell.customClasses.push('disable-cell');
                val = '&nbsp;';
            }

            if (cellValue) {
                val = "<div style=\"color:blue;\">"+cellValue+"</div>"
            }

            return val;
        },
        rfqSupplierRemarkCellFormatter: function(cellValue, rowIdx, cell){
            var item = this.grid.getItem(rowIdx),
                val = cellValue;

            if(item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER || item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER_N){
                cell.customClasses.push('disable-cell');
                val = '&nbsp;';
            }

            if (item.type != undefined && item.type < 1){
                cell.customClasses.push('invalidTypeItemCell');
            }

            return val;
        },
        rateAfterMarkupCellFormatter: function(cellValue, rowIdx, cell){
            var item = this.grid.getItem(rowIdx),
                rateAfterMarkup = number.parse(cellValue);
                rateAfterMarkup = isNaN(rateAfterMarkup) ? 0 : rateAfterMarkup;
            var formattedValue = rateAfterMarkup != 0 ? currency.format(rateAfterMarkup) : '&nbsp;';

            if (item.type != undefined && item.type < 1) {
                cell.customClasses.push('invalidTypeItemCell');
            } else {
                cell.customClasses.push('disable-cell');
            }

            return rateAfterMarkup >= 0 ? formattedValue : '<span style="color:#FF0000">'+formattedValue+'</span>';
        },
        reportRateAfterMarkupCellFormatter: function(cellValue, rowIdx, cell){
            var item = this.grid.getItem(rowIdx),
                rateAfterMarkup = number.parse(cellValue);
            rateAfterMarkup = isNaN(rateAfterMarkup) ? 0 : rateAfterMarkup;
            var formattedValue = rateAfterMarkup != 0 ? currency.format(rateAfterMarkup) : '&nbsp;';

            if (item.type != undefined && item.type < 1) {
                cell.customClasses.push('invalidTypeItemCell');
            } else if(item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER || item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER_N || item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_NOID){
                cell.customClasses.push('disable-cell');
                val = '&nbsp;';
            }

            return rateAfterMarkup >= 0 ? '<span style="color:#42b449"><strong>'+formattedValue+'</strong></span>' : '<span style="color:#FF0000">'+formattedValue+'</span>';
        },
        itemTotalMarkupPercentageCellFormatter: function(cellValue, rowIdx, cell){
            cell.customClasses.push('disable-cell');
            var item = this.grid.getItem(rowIdx),
                grandTotal = number.parse(cellValue),
                grandTotalAfterMarkup = number.parse(item.grand_total_after_markup[0]);
            grandTotal = isNaN(grandTotal) ? 0 : grandTotal;
            grandTotalAfterMarkup = isNaN(grandTotalAfterMarkup) ? 0 : grandTotalAfterMarkup;
            var markupPercent = grandTotal != 0 ? (grandTotalAfterMarkup - grandTotal) / grandTotal * 100 : 0;
            var formattedValue = markupPercent != 0 ? number.format(markupPercent, {places:2})+'%' : '&nbsp;';
            return markupPercent >= 0 ? formattedValue : '<span style="color:#FF0000">'+formattedValue+'</span>';
        },
        itemTotalMarkupAmountCellFormatter: function(cellValue, rowIdx, cell){
            cell.customClasses.push('disable-cell');
            var item = this.grid.getItem(rowIdx),
                grandTotal = number.parse(cellValue),
                grandTotalAfterMarkup = number.parse(item.grand_total_after_markup[0]);
            grandTotal = isNaN(grandTotal) ? 0 : grandTotal;
            grandTotalAfterMarkup = isNaN(grandTotalAfterMarkup) ? 0 : grandTotalAfterMarkup;
            var markupAmount = grandTotalAfterMarkup - grandTotal;
            var formattedValue = markupAmount != 0 ? currency.format(markupAmount) : '&nbsp;';
            return markupAmount >= 0 ? formattedValue : '<span style="color:#FF0000">'+formattedValue+'</span>';
        },
        itemOverallTotalAfterMarkupCellFormatter: function(cellValue, rowIdx, cell){
            cell.customClasses.push('disable-cell');
            var total = number.parse(cellValue);
            var formattedValue = (!isNaN(total) && total != 0) ? currency.format(total) : '&nbsp;';
            return total >= 0 ? '<span style="color:blue;">'+formattedValue+'</span>' : '<span style="color:#FF0000">'+formattedValue+'</span>';
        },
        elementOverallTotalAfterMarkupCellFormatter: function(cellValue, rowIdx, cell){
            cell.customClasses.push('disable-cell');
            var item = this.grid.getItem(rowIdx),
                grandTotal = number.parse(cellValue),
                grandTotal = isNaN(grandTotal) ? 0 : grandTotal;
            var formattedValue = grandTotal != 0 ? currency.format(grandTotal) : '&nbsp;';
            return grandTotal >= 0 ? '<span style="color:blue;">'+formattedValue+'</span>' : '<span style="color:#FF0000">'+formattedValue+'</span>';
        },
        elementTotalMarkupPercentageCellFormatter: function(cellValue, rowIdx, cell){
            cell.customClasses.push('disable-cell');
            var item = this.grid.getItem(rowIdx),
                originalGrandTotal = number.parse(cellValue),
                grandTotal = number.parse(item.overall_total_after_markup[0]);

            originalGrandTotal = isNaN(originalGrandTotal) ? 0 : originalGrandTotal;
            grandTotal = isNaN(grandTotal) ? 0 : grandTotal;

            var value = originalGrandTotal != 0 ? (grandTotal - originalGrandTotal) / originalGrandTotal * 100 : 0;
            var formattedValue = value != 0 ? number.format(value, {places:2})+'%' : '&nbsp;';
            return value >= 0 ? formattedValue : '<span style="color:#FF0000">'+formattedValue+'</span>';
        },
        elementTotalMarkupAmountCellFormatter: function(cellValue, rowIdx, cell){
            cell.customClasses.push('disable-cell');
            var item = this.grid.getItem(rowIdx),
                originalGrandTotal = number.parse(cellValue),
                grandTotal = number.parse(item.overall_total_after_markup[0]);

            originalGrandTotal = isNaN(originalGrandTotal) ? 0 : originalGrandTotal;
            grandTotal = isNaN(grandTotal) ? 0 : grandTotal;

            var value = grandTotal - originalGrandTotal;
            var formattedValue = value != 0 ? currency.format(value) : '&nbsp;';
            return value >= 0 ? formattedValue : '<span style="color:#FF0000">'+formattedValue+'</span>';
        },
        itemTotalPerUnitCellFormatter: function(cellValue, rowIdx, cell){
            var item = this.grid.getItem(rowIdx);
            var totalPerUnit = number.parse(cellValue);
            var formattedValue = (!isNaN(totalPerUnit) && totalPerUnit != 0) ? currency.format(totalPerUnit) : '&nbsp;';

            if(isNaN(totalPerUnit) || totalPerUnit == 0 || totalPerUnit == null)
            {
                value = "&nbsp;";
            }
            else if(totalPerUnit < 0)
            {
                value = '<span style="color:#FF0000">'+formattedValue+'</span>';
            }
            else
            {
                value = totalPerUnit >= 0 ? '<span style="color:blue;">'+formattedValue+'</span>' : '<span style="color:#FF0000">'+formattedValue+'</span>';
            }

            if (item.type && item.type != undefined && item.type < 1) {
                cell.customClasses.push('invalidTypeItemCell');
            } else {
                cell.customClasses.push('disable-cell');
            }

            return value;
        },
        itemTotalCellFormatter: function(cellValue, rowIdx, cell){
            cell.customClasses.push('disable-cell');
            var total = number.parse(cellValue);
            var formattedValue = (!isNaN(total) && total != 0) ? currency.format(total) : '&nbsp;';
            return total >= 0 ? '<span style="color:blue;">'+formattedValue+'</span>' : '<span style="color:#FF0000">'+formattedValue+'</span>';
        },
        elementTypeJobPercentageCellFormatter: function(cellValue, rowIdx, cell){
            cell.customClasses.push('disable-cell');
            var field = cell.field.split('-');
            var item = this.grid.getItem(rowIdx),
                total = number.parse(cellValue),
                totalSum = number.parse(item[field[0]+'-element_sum_total']);

            if(!isNaN(total) && !isNaN(totalSum)){
                total = totalSum != 0 ? total / totalSum * 100 : 0;
            }
            if(isNaN(total) || total == 0){
                cellValue = "&nbsp;";
            }else{
                var formattedValue = number.format(total, {places:2})+"%";
                cellValue = total >= 0 ? '<span style="color:blue;">'+formattedValue+'</span>' : '<span style="color:#FF0000">'+formattedValue+'</span>';
            }
            return cellValue;
        },
        elementTotalPerUnitCellFormatter: function(cellValue, rowIdx, cell){
            cell.customClasses.push('disable-cell');
            var value = number.parse(cellValue);
            value = isNaN(value) ? 0 : value;

            if(isNaN(value) || value == 0 || value == null){
                value = "&nbsp;";
            }else{
                var formattedValue = currency.format(value);
                value = value >= 0 ? '<span style="color:blue;">'+formattedValue+'</span>' : '<span style="color:#FF0000">'+formattedValue+'</span>';
            }
            return value;
        },
        elementTotalCellFormatter: function(cellValue, rowIdx, cell){
            cell.customClasses.push('disable-cell');
            var value = number.parse(cellValue);
            value = isNaN(value) ? 0 : value;

            if(isNaN(value) || value == 0 || value == null){
                value = "&nbsp;";
            }else{
                var formattedValue = currency.format(value);
                value = value >= 0 ? '<span style="color:blue;">'+formattedValue+'</span>' : '<span style="color:#FF0000">'+formattedValue+'</span>';
            }
            return value;
        },
        elementJobPercentageCellFormatter: function(cellValue, rowIdx, cell){
            cell.customClasses.push('disable-cell');
            var item = this.grid.getItem(rowIdx),
                total = number.parse(cellValue),
                totalSum = number.parse(item.element_sum_total);

            if(!isNaN(total) && !isNaN(totalSum)){
                total = totalSum != 0 ? total / totalSum * 100 : 0;
            }

            if(isNaN(total) || total == 0){
                cellValue = "&nbsp;";
            }else{
                var formattedValue = number.format(total, {places:2})+"%";
                cellValue = total >= 0 ? '<span style="color:blue;">'+formattedValue+'</span>' : '<span style="color:#FF0000">'+formattedValue+'</span>';
            }
            return cellValue;
        },
        projectBreakdownJobPercentageCellFormatter: function(cellValue, rowIdx, cell){
            cell.customClasses.push('disable-cell');
            var item = this.grid.getItem(rowIdx),
                total = number.parse(cellValue),
                totalSum = number.parse(item.bill_sum_total);

            if(!isNaN(total) && !isNaN(totalSum)){
                total = totalSum != 0 ? total / totalSum * 100 : 0;
            }

            if(isNaN(total) || total == 0){
                cellValue = "&nbsp;";
            }else{
                var formattedValue = number.format(total, {places:2})+"%";
                cellValue = total >= 0 ? '<span style="color:blue;">'+formattedValue+'</span>' : '<span style="color:#FF0000">'+formattedValue+'</span>';
            }
            return cellValue;
        },
        billItemAnalysisTotalCellFormatter: function(cellValue, rowIdx, cell){
            cell.customClasses.push('disable-cell');
            var item = this.grid.getItem(rowIdx),
                rate = item.hasOwnProperty('rate-final_value') ? number.parse(item['rate-final_value'][0]) : 0,
                qty = item.hasOwnProperty('quantity') ? number.parse(item.quantity[0]) : 0;
            rate = isNaN(rate) ? 0 : rate;
            qty = isNaN(qty) ? 0 : qty;
            var total = rate * qty;
            var formattedValue = total != 0 ? currency.format(total) : '&nbsp;';
            return total >= 0 ? formattedValue : '<span style="color:#FF0000">'+formattedValue+'</span>';
        },
        analyzerRateCellFormatter: function(cellValue, rowIdx, cell){
            var item = this.grid.getItem(rowIdx),
                value = number.parse(cellValue),
                fieldConstantName = this.field.replace("-value", ""),
                finalValue = item[fieldConstantName+'-final_value'][0],
                val = '&nbsp;';

            var formattedValue;
            if(item['multi-rate'][0]){
                val = '<span style="color:#ee9f05;">MULTI&nbsp;&nbsp;&nbsp;</span>';
            }else{
                if(isNaN(finalValue) || finalValue == 0 || finalValue == null){
                    formattedValue = "&nbsp;";
                }else{
                    formattedValue = currency.format(finalValue);
                }

                if(item[fieldConstantName+'-linked'] != undefined && item[fieldConstantName+'-linked'][0]){
                    val = '<span style="color:#42b449;">'+formattedValue+'</span>';
                }else if(item[fieldConstantName+'-has_formula'][0]){
                    val = '<span style="color:#F78181;">'+formattedValue+'</span>';
                }else{
                    val = finalValue >= 0 ? formattedValue : '<span style="color:#FF0000">'+formattedValue+'</span>';
                }

                if(item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER || item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER_N || item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_NOID){
                    cell.customClasses.push('disable-cell');
                    val = '&nbsp;';
                }
            }

            return val;
        },
        unEditableAnalyzerRateCellFormatter: function(cellValue, rowIdx, cell){
            var item = this.grid.getItem(rowIdx),
                value = number.parse(cellValue),
                fieldConstantName = this.field.replace("-value", ""),
                finalValue = item[fieldConstantName+'-final_value'][0],
                val = '&nbsp;';

            var formattedValue;
            if(item['multi-rate'] && item['multi-rate'][0]){
                val = '<span style="color:#ee9f05;">MULTI&nbsp;&nbsp;&nbsp;</span>';
            }else{
                if(isNaN(finalValue) || finalValue == 0 || finalValue == null){
                    formattedValue = "&nbsp;";
                }else{
                    formattedValue = currency.format(finalValue);
                }

                if(item[fieldConstantName+'-linked'] && item[fieldConstantName+'-linked'] != undefined && item[fieldConstantName+'-linked'][0]){
                    val = '<span style="color:#42b449;">'+formattedValue+'</span>';
                }else if(item[fieldConstantName+'-has_formula'] && item[fieldConstantName+'-has_formula'][0]){
                    val = '<span style="color:#F78181;">'+formattedValue+'</span>';
                }else{
                    val = finalValue >= 0 ? formattedValue : '<span style="color:#FF0000">'+formattedValue+'</span>';
                }

                if(item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER || item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER_N || item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_NOID){
                    val = '&nbsp;';
                }
            }

            if (item.type && item.type != undefined && item.type < 1) {
                cell.customClasses.push('invalidTypeItemCell');
            } else {
                cell.customClasses.push('disable-cell');
            }

            return val;
        },
        analyzerItemMarkupCellFormatter: function(cellValue, rowIdx, cell){
            var item = this.grid.getItem(rowIdx),
                value = number.parse(cellValue),
                fieldConstantName = this.field.replace("-value", ""),
                finalValue = item[fieldConstantName+'-final_value'][0],
                val = '&nbsp;';

            var formattedValue;
            if(item['multi-item_markup'] && item['multi-item_markup'][0]){
                val = '<span style="color:#ee9f05;">MULTI&nbsp;&nbsp;&nbsp;</span>';
            }else{
                if(isNaN(finalValue) || finalValue == 0 || finalValue == null){
                    formattedValue = "&nbsp;";
                }else{
                    formattedValue = number.format(finalValue, {places: 2});
                }

                if(item[fieldConstantName+'-linked'] && item[fieldConstantName+'-linked'] != undefined && item[fieldConstantName+'-linked'][0]){
                    val = '<span style="color:#42b449;">'+formattedValue+'</span>';
                }else if(item[fieldConstantName+'-has_formula'] && item[fieldConstantName+'-has_formula'][0]){
                    val = '<span style="color:#F78181;">'+formattedValue+'</span>';
                }else{
                    val = finalValue >= 0 ? formattedValue : '<span style="color:#FF0000">'+formattedValue+'</span>';
                }

                if(item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER || item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER_N || item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_NOID){
                    cell.customClasses.push('disable-cell');
                    val = '&nbsp;';
                }
            }

            if ( item.type && item.type[0] === 'tradeItem' ) {
                cell.customClasses.push('disable-cell');
                val = '&nbsp;';
            } else if (item.type && item.type != undefined && item.type < 1) {
                cell.customClasses.push('invalidTypeItemCell');
            }

            return val;
        },
        analyzerWastageCellFormatter: function(cellValue, rowIdx, cell){
            var item = this.grid.getItem(rowIdx),
                value = number.parse(cellValue),
                fieldConstantName = this.field.replace("-value", ""),
                finalValue = item[fieldConstantName+'-final_value'][0],
                val = '&nbsp;';

            if(item['multi-wastage'][0]){
                val = '<span style="color:#ee9f05;">MULTI&nbsp;&nbsp;&nbsp;</span>';

                if(item.type < 1) {
                    cell.customClasses.push('invalidTypeItemCell');
                }
            }else{
                if(isNaN(finalValue) || finalValue == 0 || finalValue == null){
                    var formattedValue = "&nbsp;";
                }else{
                    var formattedValue = number.format(finalValue, {places: 2});
                }

                if(item[fieldConstantName+'-linked'] != undefined && item[fieldConstantName+'-linked'][0]){
                    val = '<span style="color:#42b449;">'+formattedValue+'</span>';
                }else if(item[fieldConstantName+'-has_formula'][0]){
                    val = '<span style="color:#F78181;">'+formattedValue+'</span>';
                }else{
                    val = finalValue >= 0 ? formattedValue : '<span style="color:#FF0000">'+formattedValue+'</span>';
                }

                if(item.type < 1) {
                    cell.customClasses.push('invalidTypeItemCell');
                } else if(item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER || item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER_N || item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_NOID){
                    cell.customClasses.push('disable-cell');
                    val = '&nbsp;';
                }
            }

            return val;
        },
        analysisTreeCellFormatter: function(cellValue, rowIdx, cell){
            var item = this.grid.getItem(rowIdx),
                level = item.level*16;

            cellValue = cellValue == null ? '&nbsp': cellValue;

            if(item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER || item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER_N){
                cellValue =  '<b>'+cellValue+'</b>';
            }

            if(item.type < 1){
                cell.customClasses.push('invalidTypeItemCell');
            }else{
                cellValue = '<div class="treeNode" style="padding-left:'+level+'px;"><div class="treeContent">'+cellValue+'&nbsp;</div></div>';
            }


            return cellValue;
        },
        menuCellFormatter: function(cellValue, rowIdx, cell){
            var item = this.grid.getItem(rowIdx),
                level = item.level*16,
                color = 'black';

            cellValue = cellValue == null ? '&nbsp': cellValue;

            if ( item.is_app[0] ) {
                color = 'blue';
            }

            cellValue = '<div class="treeNode" style="color:'+color+'; padding-left:'+level+'px;"><div class="treeContent">'+cellValue+'&nbsp;</div></div>';

            return cellValue;
        },
        billRefCellFormatter: function(cellValue, rowIdx, cell){
            var item = this.grid.getItem(rowIdx);

            if (item.version != undefined  && item.version > 0){
                if (this.grid.currentBillVersion != undefined && this.grid.currentBillVersion == item.version){
                    cell.customClasses.push('hasCurrentAddendumTypeItemCell');
                }else{
                    cell.customClasses.push('hasAddendumTypeItemCell');
                }
            } else if(item && item.type && item.type < 1) {
                cell.customClasses.push('invalidTypeItemCell');
            }

            return cellValue;
        },
        boolIconCellFormatter: function (cellValue, rowIdx, cell)
        {
            if(cellValue == true)
            {
                cellValue = '<span class="dijitReset dijitInline dijitIcon icon-16-container icon-16-checkmark2"></span>';
            }
            else
            {
                cellValue = '<span class="dijitReset dijitInline dijitIcon icon-16-container icon-16-cross"></span>';
            }

            return cellValue;
        },
        downloadCellFormatter: function (cellValue, rowIdx, cell)
        {
            var item = this.grid.getItem(rowIdx);

            return item.id >= 0 ? '<a href="'+item.file_path+'" download>'+item.name+'</a>' : null;
        },
        attachmentsCellFormatter: function (cellValue, rowIdx, cell){
            var item = this.grid.getItem(rowIdx);

            var label = item.id >= 0 ? nls.attachments : '';

            if(item.attachments > 0) label = item.attachments;

            return '<span style="color:blue;">'+label+'</span>';
        }
    });

    buildspace.widget.grid.constants = {
        HIERARCHY_TYPE_HEADER: buildspace.constants.HIERARCHY_TYPE_HEADER,
        HIERARCHY_TYPE_WORK_ITEM: buildspace.constants.HIERARCHY_TYPE_WORK_ITEM,
        HIERARCHY_TYPE_ITEM_HTML_EDITOR: buildspace.constants.HIERARCHY_TYPE_ITEM_HTML_EDITOR,
        HIERARCHY_TYPE_ITEM_PROVISIONAL: buildspace.constants.HIERARCHY_TYPE_ITEM_PROVISIONAL,
        HIERARCHY_TYPE_ITEM_RATE_ONLY: buildspace.constants.HIERARCHY_TYPE_ITEM_RATE_ONLY,
        HIERARCHY_TYPE_ITEM_NOT_LISTED: buildspace.constants.HIERARCHY_TYPE_ITEM_NOT_LISTED,
        HIERARCHY_TYPE_ITEM_PC_RATE: buildspace.constants.HIERARCHY_TYPE_ITEM_PC_RATE,
        HIERARCHY_TYPE_ITEM_LUMP_SUM: buildspace.constants.HIERARCHY_TYPE_ITEM_LUMP_SUM,
        HIERARCHY_TYPE_ITEM_LUMP_SUM_PERCENT: buildspace.constants.HIERARCHY_TYPE_ITEM_LUMP_SUM_PERCENT,
        HIERARCHY_TYPE_ITEM_LUMP_SUM_EXCLUDE: buildspace.constants.HIERARCHY_TYPE_ITEM_LUMP_SUM_EXCLUDE,
        HIERARCHY_TYPE_HEADER_N: buildspace.constants.HIERARCHY_TYPE_HEADER_N,
        HIERARCHY_TYPE_NOID: buildspace.constants.HIERARCHY_TYPE_NOID,
        HIERARCHY_TYPE_HEADER_TEXT: buildspace.constants.HIERARCHY_TYPE_HEADER_TEXT,
        HIERARCHY_TYPE_WORK_ITEM_TEXT: buildspace.constants.HIERARCHY_TYPE_WORK_ITEM_TEXT,
        HIERARCHY_TYPE_ITEM_HTML_EDITOR_TEXT: buildspace.constants.HIERARCHY_TYPE_ITEM_HTML_EDITOR_TEXT,
        HIERARCHY_TYPE_ITEM_PROVISIONAL_TEXT: buildspace.constants.HIERARCHY_TYPE_ITEM_PROVISIONAL_TEXT,
        HIERARCHY_TYPE_ITEM_RATE_ONLY_TEXT: buildspace.constants.HIERARCHY_TYPE_ITEM_RATE_ONLY_TEXT,
        HIERARCHY_TYPE_ITEM_NOT_LISTED_TEXT: buildspace.constants.HIERARCHY_TYPE_ITEM_NOT_LISTED_TEXT,
        HIERARCHY_TYPE_ITEM_PC_RATE_TEXT: buildspace.constants.HIERARCHY_TYPE_ITEM_PC_RATE_TEXT,
        HIERARCHY_TYPE_ITEM_LUMP_SUM_TEXT: buildspace.constants.HIERARCHY_TYPE_ITEM_LUMP_SUM_TEXT,
        HIERARCHY_TYPE_ITEM_LUMP_SUM_PERCENT_TEXT: buildspace.constants.HIERARCHY_TYPE_ITEM_LUMP_SUM_PERCENT_TEXT,
        HIERARCHY_TYPE_ITEM_LUMP_SUM_EXCLUDE_TEXT: buildspace.constants.HIERARCHY_TYPE_ITEM_LUMP_SUM_EXCLUDE_TEXT,
        HIERARCHY_TYPE_HEADER_N_TEXT: buildspace.constants.HIERARCHY_TYPE_HEADER_N_TEXT,
        HIERARCHY_TYPE_NOID_TEXT: buildspace.constants.HIERARCHY_TYPE_NOID_TEXT
    }

    return Formatter;
});