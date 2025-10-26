define("buildspace/buildspace",[
    "dojo/dom",
    "dojo/html",
    "dojox/html/entities",
    "dijit/Dialog",
    "dijit/Tooltip",
    "dojo/dom-style",
    "dojo/dom-class",
    "dojo/dom-construct",
    "dojo/dom-geometry",
    "dojo/string",
    "dojo/on",
    "dojo/aspect",
    "dojo/keys",
    "dojo/_base/lang",
    "dojo/_base/fx",
    "dojo/fx/easing",
    "dojo/parser",
    "dojo/number",
    "dojo/_base/window",
    "dojo/window",
    "dojo/data/ItemFileReadStore",
    'dojox/data/AndOrWriteStore',
    'dojo/i18n!buildspace/nls/Common',
    "buildspace/app",
    "buildspace/ui",
    "buildspace/dialog",
    "buildspace/grid",
    "buildspace/widget/Window"
], function(dom, html, entities, Dialog, Tooltip, domStyle, domClass, domConstruct, domGeometry, string, on, aspect, keys, lang, baseFx, easing, parser, number, baseWin, win, ItemFileReadStore, AndOrWriteStore, nls) {

    var startup = buildspace.startup = function(appName, data) {

        var modules;

        if(appName != 'BuildSpace'){
            modules = [
                'buildspace.app',
                'buildspace.dialog'
            ];
        }else{
            modules = [
                'buildspace.app',
                'buildspace.ui',
                'buildspace.dialog'
            ];
        }

        dojo.forEach(modules, function(module){
            callIfExists(module, "init", {
                loadAppList: (appName == 'BuildSpace')
            });
            callIfExists(module, "draw");
        });

        initUI(data, appName);

        var idleSeconds = 1800; //30 min
        var idleTimer;
        var interval;
        var timeOut = false;

        function resetTimer(){
            clearTimeout(idleTimer);

            if(!timeOut){
                idleTimer = setTimeout(createInterval,idleSeconds*1000);
            }
        }

        function cancelTimeout(){
            timeOut = false;

            clearInterval(interval);
            resetTimer();
        }

        function createInterval(){
            timeOut = true;

            var content = '<div>'+nls.idleMsg+', <br>'+nls.continueUsingBuildspace+'<br><br>(<span id="bs_interval_value"></span> '+nls.secLeft+' '+nls.beforeAutoSignOut+')</div>';
            buildspace.dialog.alert('Confirmation',content,80,280, cancelTimeout);

            var seconds_left = 30;

            interval = setInterval(function() {

                dojo.byId("bs_interval_value").innerHTML = --seconds_left;

                if (seconds_left <= 0){
                    userIdle(data, appName);
                    clearInterval(interval);
                }

            }, 1000);
        }

        on(document.body,'mousemove',resetTimer);
        on(document.body,'keydown',resetTimer);
        on(document.body,'click',resetTimer);

        resetTimer();
    },
    initUI = function(data, appName) {
        setTheme('Buildspace');
        preloadImages(data, appName);
    },
    preloadImages = function(data, appName){
        var loader = new PxLoader();
        var images = [{
            directory: 'buildspace/resources/themes/Buildspace/images',
            filename: 'cloth_alike.png'
        },{
            directory: 'buildspace/resources/themes/Buildspace/icons',
            filename: 'buildspace-green.png'
        },{
            directory: 'buildspace/resources/themes/Buildspace/icons/16x16',
            filename: '16-16-icon-sprites.png'
        },{
            directory: 'buildspace/resources/themes/Buildspace/icons/24x24',
            filename: '24-24-icon-sprites.png'
        },{
            directory: 'buildspace/resources/themes/Buildspace/icons/64x64',
            filename: '64-64-icon-sprites.png'
        },{
            directory: 'buildspace/resources/themes/Buildspace/icons/120x120',
            filename: '120-120-icon-sprites.png'
        },{
            directory: 'buildspace/resources/themes/Buildspace/images',
            filename: 'breadcrumb_arrow.gif'
        },{
            directory: 'buildspace/resources/themes/Buildspace/images',
            filename: 'separator.png'
        },{
            directory: 'buildspace/resources/themes/Buildspace/icons',
            filename: 'tooltipConnectorUp.png'
        }];

        dojo.forEach(images, function(i){
            loader.addImage(require.toUrl(i.directory+"/"+ i.filename));
        });

        loader.addCompletionListener(function(e) {
            endLoading(data, appName);

            dijit.Tooltip.defaultPosition=['below','above'];

            if(appName == 'BuildSpace'){
                new Tooltip({
                    connectId: ["buildspace-start_menu-btn"],
                    label: nls.clickHereToBegin
                });
                new Tooltip({
                    connectId: ["eproject-link"],
                    label: nls.goToBuildspaceEProject
                });
            }
        });

        loader.start();
    },
    userIdle = function (data, appName) {
        var url;
        switch(appName) {
            case 'Editor':
                url = 'logout/'+data.pid;
                break;
            case 'MasterCostData':
            case 'CostData':
                url = 'logout/'+data.id;
                break;
            default:
                url = 'logout';
        }
        window.location.href = url;
    },
    startLoading = buildspace.startLoading = function(targetNode) {
        var overlayNode = dom.byId("loadingOverlay");
        if("none" == domStyle.get(overlayNode, "display")) {
            var coords = domGeometry.getMarginBox(targetNode || baseWin.body());
            domGeometry.setMarginBox(overlayNode, coords);
            domStyle.set(dom.byId("loadingOverlay"), {
                display: "block",
                opacity: 1
            });
        }
    },
    endLoading = function(data, appName) {
        baseFx.fadeOut({
            node: dom.byId("loadingOverlay"),
            duration: 1000,
            onEnd: function(node){
                var loaderInterval = buildspace.intervals.siteLoader;
                if(loaderInterval){ clearInterval(loaderInterval); }

                if(data){
                    var isApp = false;
                    var appID, appParams, sysName, appTitle;

                    switch(appName){
                        case 'Editor':
                            isApp = true;
                            appID = 'buildspace-BQ-Editor';
                            sysName = 'Editor';
                            appTitle = 'BQ Editor';
                            appParams = {
                                pid: data.pid
                            };
                            break;
                        case 'MasterCostData':
                            isApp = true;
                            appID = 'buildspace-MasterCostData';
                            sysName = 'MasterCostData';
                            appTitle = 'Master Cost Data';
                            appParams = {
                                id: data.id,
                                data: data
                            };
                            break;
                        case 'CostData':
                            isApp = true;
                            appID = 'buildspace-CostData';
                            sysName = 'CostData';
                            appTitle = 'Project Cost Data';
                            appParams = {
                                id: data.id,
                                data: data
                            };
                            break;
                        case 'BuildSpace':
                            for(var i in data.bs_app_method_args){
                                if(data.bs_app_method_args_store_keys.includes(i)){
                                    var store = new ItemFileReadStore( { data: {
                                        'identifier': 'id',
                                        'items': [data.bs_app_method_args[i]]
                                    } });

                                    store.fetchItemByIdentity({ 'identity' :data.bs_app_method_args[i]['id'],  onItem : function(item){
                                        data.bs_app_method_args[i] = item;
                                    }});
                                }
                            }

                            var app = new buildspace.apps[data.bs_app_name](data.bs_app_name);
                            app[data.bs_app_method].apply(app, Object.values(data.bs_app_method_args));
                            
                            break;
                        default:
                    }

                    if(isApp){
                        buildspace.app.launch({
                            __children: [],
                            id: appID,
                            is_app: true,
                            level: 0,
                            sysname: sysName,
                            title: appTitle
                        },appParams);
                    }
                }

                html.set(dom.byId("preloaderContainer"), "");
                domStyle.set(node, "display", "none");
            }
        }).play();
    },
    callIfExists = function(object, method, config) {
        object = lang.getObject(object);
        if(dojo.isFunction(object[method])) {
            object[method](config);
        }else if(object.prototype && dojo.isFunction(object.prototype[method])){
            object.prototype[method](config);
        }
    },
    truncateString = buildspace.truncateString = function(str, len){
        str = str.toString();
        if (str.length > len) {
            str = str.substring(0, len)+'...';
        }
        return entities.encode(str);
    },
    toCamelCase = buildspace.toCamelCase = function(str){
        return str.replace(/([-_][a-z])/ig, function(r){
            return r.toUpperCase().replace('-', '').replace('_', '');
        });
    },
    //imitate window.open with a post request
    windowOpen = buildspace.windowOpen = function(verb, url, data, target) {
        var form = document.getElementById('bs_window_open_form');

        if(form && form.parentNode){
            form.parentNode.removeChild(form);
        }

        form = document.createElement("form");
        form.setAttribute("id", "bs_window_open_form");
        form.action = url;
        form.method = verb;
        form.target = target || "_self";
        if (data) {
            for (var key in data) {
                var input = document.createElement("textarea");
                input.name = key;
                input.value = typeof data[key] === "object" ? JSON.stringify(data[key]) : data[key];
                form.appendChild(input);
            }
        }
        form.style.display = 'none';
        document.body.appendChild(form);
        form.submit();
    },
    temporaryArraySwap = buildspace.temporaryArraySwap = function (array){
        var left = null;
        var right = null;
        var length = array.length;
        for (left = 0, right = length - 1; left < right; left += 1, right -= 1)
        {
            var temporary = array[left];
            array[left] = array[right];
            array[right] = temporary;
        }
        return array;
    },
    setTheme = function(theme){
        //  summary:
        //      Sets the theme
        //  theme:
        //      the theme to use
        var fileList = ["dijit", "checkedMultiSelect", "theme", "window", "icons", "gantt"];
        dojo.forEach(fileList, function(e){
            var elem = dojo.byId("desktop_theme_"+e);
            if(elem){
                elem.parentNode.removeChild(elem);
                elem = null;
            }
            var element = document.createElement("link");
            element.rel = "stylesheet";
            element.type = "text/css";
            element.media = "screen";
            element.href = require.toUrl("buildspace/resources/themes/"+theme+"/"+e+".css");
            element.id = "desktop_theme_"+e;
            document.getElementsByTagName("head")[0].appendChild(element);
        });
    };

    buildspace.addDojoCss = function(path){
        //  summary:
        //      Adds an additional dojo CSS file (useful for the dojox modules)
        //
        //  path:
        //      the path to the css file (the path to dojo is placed in front)
        //
        //  example:
        //      api.addDojoCss("/dojox/widget/somewidget/foo.css");
        var rootUrl = dojo.baseUrl.substring(0, dojo.baseUrl.indexOf("/dojo/")).trim();
        var element = document.createElement("link");
        element.rel = "stylesheet";
        element.type = "text/css";
        element.media = "screen";
        element.href = rootUrl+'/'+path;
        document.getElementsByTagName("head")[0].appendChild(element);
    };

    buildspace.roundingNumber = function(val, type){
        val = number.parse(val);
        switch(String(type)){
            case buildspace.constants.ROUNDING_TYPE_UPWARD:
                return !isNaN(val) ? Math.ceil(val) : 0;
            case buildspace.constants.ROUNDING_TYPE_DOWNWARD:
                return !isNaN(val) ? Math.floor(val) : 0;
            case buildspace.constants.ROUNDING_TYPE_NEAREST_WHOLE_NUMBER:
                return !isNaN(val) ? Math.round(val) : 0;
            case buildspace.constants.ROUNDING_TYPE_NEAREST_TENTH:
                return !isNaN(val) ? Math.round(val*10)/10 : 0;
            default:
                return !isNaN(val) ? Math.round(val*100)/100 : 0;
        }
    };

    buildspace.getBillTypeText = function(type){
        switch(parseInt(type)){
            case buildspace.constants.BILL_TYPE_PROVISIONAL:
                return buildspace.constants.BILL_TYPE_PROVISIONAL_TEXT;
            case buildspace.constants.BILL_TYPE_PRELIMINARY:
                return buildspace.constants.BILL_TYPE_PRELIMINARY_TEXT;
            case buildspace.constants.BILL_TYPE_STANDARD:
                return buildspace.constants.BILL_TYPE_STANDARD_TEXT;
            case buildspace.constants.BILL_TYPE_PRIMECOST:
                return buildspace.constants.BILL_TYPE_PRIMECOST_TEXT;
            default:
                return "";
        }
    };

    buildspace.getProjectScheduleTypeText = function(type){
        switch(parseInt(type)){
            case buildspace.constants.PROJECT_SCHEDULE_TYPE_PROJECT:
                return buildspace.constants.PROJECT_SCHEDULE_TYPE_PROJECT_TEXT;
            case buildspace.constants.PROJECT_SCHEDULE_TYPE_SUB_PACKAGE:
                return buildspace.constants.PROJECT_SCHEDULE_TYPE_SUB_PACKAGE_TEXT;
            default:
                return "";
        }
    };

    buildspace.constants = {
        HIERARCHY_TYPE_HEADER: '1',
        HIERARCHY_TYPE_WORK_ITEM: '2',
        HIERARCHY_TYPE_NOID: '4',
        HIERARCHY_TYPE_ITEM_HTML_EDITOR: '8',
        HIERARCHY_TYPE_ITEM_PROVISIONAL: '16',
        HIERARCHY_TYPE_ITEM_RATE_ONLY: '32',
        HIERARCHY_TYPE_ITEM_NOT_LISTED: '64',
        HIERARCHY_TYPE_ITEM_PC_RATE: '128',
        HIERARCHY_TYPE_ITEM_LUMP_SUM: '256',
        HIERARCHY_TYPE_ITEM_LUMP_SUM_PERCENT: '512',
        HIERARCHY_TYPE_ITEM_LUMP_SUM_EXCLUDE: '1024',
        HIERARCHY_TYPE_HEADER_N: '2048',
        HIERARCHY_TYPE_HEADER_TEXT: 'HEAD',
        HIERARCHY_TYPE_WORK_ITEM_TEXT: 'ITEM',
        HIERARCHY_TYPE_NOID_TEXT: 'NOID',
        HIERARCHY_TYPE_ITEM_HTML_EDITOR_TEXT: 'ITEM-HE',
        HIERARCHY_TYPE_ITEM_PROVISIONAL_TEXT: 'ITEM-P',
        HIERARCHY_TYPE_ITEM_RATE_ONLY_TEXT: 'ITEM-RO',
        HIERARCHY_TYPE_ITEM_NOT_LISTED_TEXT: 'ITEM-NL',
        HIERARCHY_TYPE_ITEM_PC_RATE_TEXT: 'ITEM-PC',
        HIERARCHY_TYPE_ITEM_LUMP_SUM_TEXT: 'ITEM-LS',
        HIERARCHY_TYPE_ITEM_LUMP_SUM_PERCENT_TEXT: 'ITEM-LS%',
        HIERARCHY_TYPE_ITEM_LUMP_SUM_EXCLUDE_TEXT: 'ITEM-LSX',
        HIERARCHY_TYPE_HEADER_N_TEXT: 'HEAD-N',
        QUANTITY_PER_UNIT_ORIGINAL: '1',
        QUANTITY_PER_UNIT_REMEASUREMENT: '2',
        ROUNDING_TYPE_DISABLED: '1',
        ROUNDING_TYPE_UPWARD: '2',
        ROUNDING_TYPE_DOWNWARD: '4',
        ROUNDING_TYPE_NEAREST_WHOLE_NUMBER: '8',
        ROUNDING_TYPE_NEAREST_TENTH: '16',
        ROUNDING_TYPE_DISABLED_TEXT: nls.roundingTypeDisabled,
        ROUNDING_TYPE_UPWARD_TEXT: nls.roundingTypeUpward,
        ROUNDING_TYPE_DOWNWARD_TEXT: nls.roundingTypeDownward,
        ROUNDING_TYPE_NEAREST_WHOLE_NUMBER_TEXT: nls.roundingTypeNearestWholeNumber,
        ROUNDING_TYPE_NEAREST_TENTH_TEXT: nls.roundingTypeNearestTenth,
        FILE_IMPORT_TYPE_BUILDSPACE: '1',
        FILE_IMPORT_TYPE_BUILDSOFT: '2',
        FILE_IMPORT_TYPE_NORMAL: '8',
        FILE_IMPORT_TYPE_PRICELIST: '16',
        FILE_IMPORT_TYPE_EXCEL: '32',
        FILE_IMPORT_TYPE_BUILDSPACE_SOR: '64',
        EXCEL_TYPE_SINGLE: 1,
        EXCEL_TYPE_MULTIPLE: 2,
        SIGN_POSITIVE: '1',
        SIGN_NEGATIVE: '2',
        SIGN_POSITIVE_TEXT: '+',
        SIGN_NEGATIVE_TEXT: '-',
        STATUS_PRETENDER: 1,
        STATUS_TENDERING: 2,
        STATUS_POSTCONTRACT: 4,
        STATUS_IMPORT: 8,
        STATUS_IMPORT_SUB_PACKAGE: 16,
        STATUS_POSTCONTRACT_SUB_PACKAGE: 32,
        TENDER_TYPE_TENDERED: 1,
        TENDER_TYPE_PARTICIPATED: 2,
        POST_CONTRACT_TYPE_NORMAL: 1,
        POST_CONTRACT_TYPE_NEW: 2,
        NEW_POST_CONTRACT_FORM_TYPE_LETTER_OF_AWARD: 1,
        NEW_POST_CONTRACT_FORM_TYPE_WORK_ORDER: 2,
        NEW_POST_CONTRACT_FORM_TYPE_CONTRACT_INFO: 3,
        WAIVER_OPTION_TYPE_E_TENDER : 1,
        WAIVER_OPTION_TYPE_E_AUCTION : 2,
        E_TENDER_WAIVER_OPTION_SITE_URGENCY: 1,
        E_TENDER_WAIVER_OPTION_INTER_COMPANY: 2,
        E_TENDER_WAIVER_OPTION_OTHERS: 4,
        E_AUCTION_WAIVER_OPTION_SITE_URGENCY: 8,
        E_AUCTION_WAIVER_OPTION_INTER_COMPANY: 16,
        E_AUCTION_WAIVER_OPTION_OTHERS: 32,
        SUB_PACKAGE_WORKS_TYPE_1: 1,
        SUB_PACKAGE_WORKS_TYPE_2: 2,
        ARITHMETIC_OPERATOR_ADDITION: '+',
        ARITHMETIC_OPERATOR_SUBTRACTION: '-',
        ARITHMETIC_OPERATOR_MULTIPLICATION: '*',
        ARITHMETIC_OPERATOR_DIVISION: '/',
        ARITHMETIC_OPERATOR_MODULUS: '%',
        MAX_CLAIM_REVISIONS: 500,
        TYPE_ROOT: 1,
        TYPE_LEVEL: 2,
        TYPE_BILL: 4,
        TYPE_SUPPLY_OF_MATERIAL_BILL: 8,
        TYPE_SCHEDULE_OF_RATE_BILL: 16,
        BILL_TYPE_STANDARD: 1,
        BILL_TYPE_PROVISIONAL: 2,
        BILL_TYPE_PRELIMINARY: 4,
        BILL_TYPE_PRIMECOST: 8,
        BILL_TYPE_STANDARD_TEXT: nls.standard,
        BILL_TYPE_PROVISIONAL_TEXT: nls.standardProvisional,
        BILL_TYPE_PRELIMINARY_TEXT: nls.preliminary,
        BILL_TYPE_PRIMECOST_TEXT: nls.primeCostProvisional,
        BILL_TYPE_STANDARD_DESCRIPTION: nls.standardDescription,
        BILL_TYPE_PROVISIONAL_DESCRIPTION: nls.standardProvisionalDescription,
        BILL_TYPE_PRELIMINARY_DESCRIPTION: nls.preliminaryDescription,
        BILL_TYPE_PRIMECOST_DESCRIPTION: nls.primeCostProvisionalDescription,
        BILL_STATUS_OPEN: 1,
        BILL_STATUS_CLOSED: 2,
        BILL_STATUS_RESOURCE_ANALYSIS_RECALCULATE_ITEM: 4,
        BILL_STATUS_RESOURCE_ANALYSIS_RECALCULATE_ELEMENT: 8,
        BILL_STATUS_RESOURCE_ANALYSIS_RECALCULATE_BILL: 16,
        BILL_STATUS_SCHEDULE_OF_RATE_ANALYSIS_RECALCULATE_ITEM: 32,
        BILL_STATUS_SCHEDULE_OF_RATE_ANALYSIS_RECALCULATE_ELEMENT: 64,
        BILL_STATUS_SCHEDULE_OF_RATE_ANALYSIS_RECALCULATE_BILL: 128,
        BILL_STATUS_SCHEDULE_OF_QUANTITY_RECALCULATE_ITEM: 256,
        BILL_STATUS_SCHEDULE_OF_QUANTITY_RECALCULATE_ELEMENT: 512,
        BILL_STATUS_SCHEDULE_OF_QUANTITY_RECALCULATE_BILL: 1024,
        RFQ_TYPE_PROJECT: 1,
        RFQ_TYPE_RESOURCE: 2,
        RESOURCE_RATE_SORT_AVERAGE: 1,
        RESOURCE_RATE_SORT_LOWEST: 2,
        RESOURCE_RATE_SORT_MEDIAN: 4,
        RESOURCE_RATE_SORT_HIGHEST: 8,
        PROJECT_SCHEDULE_TYPE_PROJECT: 1,
        PROJECT_SCHEDULE_TYPE_SUB_PACKAGE: 2,
        PROJECT_SCHEDULE_TYPE_PROJECT_TEXT: "PROJECT",
        PROJECT_SCHEDULE_TYPE_SUB_PACKAGE_TEXT: "SUB-PACKAGE",
        PROJECT_SCHEDULE_PRODUCTIVITY_TYPE_UNIT_PER_HOUR: 1,
        PROJECT_SCHEDULE_PRODUCTIVITY_TYPE_UNIT_PER_HOUR_TEXT: nls.unitPerHour,
        EVENT_TYPE_PUBLIC: 1,
        EVENT_TYPE_STATE: 2,
        EVENT_TYPE_OTHER: 4,
        PREDEFINED_LOCATION_CODE_TRADE_LEVEL: 0,
        PREDEFINED_LOCATION_CODE_ELEMENT_LEVEL: 1,
        PREDEFINED_LOCATION_CODE_SUB_ELEMENT_LEVEL: 2,
        PREDEFINED_LOCATION_CODE_TYPE_TRADE_TEXT: "TRADE",
        PREDEFINED_LOCATION_CODE_TYPE_ELEMENT_TEXT: "ELEMENT",
        PREDEFINED_LOCATION_CODE_TYPE_SUB_ELEMENT_TEXT: "SUB-ELEMENT",
        LOCATION_SEQUENCE_TYPE_TRADE: 1,
        LOCATION_SEQUENCE_TYPE_LOCATION: 2,
        LOCATION_SEQUENCE_TYPE_COLUMN_TYPE: 3,
        LOCATION_SEQUENCE_TYPE_COLUMN_UNIT: 4,
        USER_PERMISSION_STATUS_PROJECT_BUILDER: 1,
        USER_PERMISSION_STATUS_TENDERING: 2,
        USER_PERMISSION_STATUS_POST_CONTRACT: 4,
        USER_PERMISSION_STATUS_PROJECT_MANAGEMENT: 8,
        PROJECT_SCHEDULE_PRINT_TYPE_PLAN: 2,
        PROJECT_SCHEDULE_PRINT_TYPE_ACTUAL: 4,
        CLAIM_CERTIFICATE_STATUS_IN_PROGRESS: 1,
        CLAIM_CERTIFICATE_STATUS_PENDING_FOR_APPROVAL: 2,
        CLAIM_CERTIFICATE_STATUS_APPROVED: 4,
        CLAIM_CERTIFICATE_STATUS_REJECTED: 128,
        VARIATION_ORDER_TYPE_STANDARD: 1,
        VARIATION_ORDER_TYPE_BUDGETARY: 2,
        VARIATION_ORDER_TYPE_CLAIMABLE: 4,
        VARIATION_ORDER_TYPE_NON_CLAIMABLE: 8,
        VARIATION_ORDER_TYPE_STANDARD_TEXT: "STANDARD",
        VARIATION_ORDER_TYPE_BUDGETARY_TEXT: "BUDGETARY",
        VARIATION_ORDER_TYPE_CLAIMABLE_TEXT: "CLAIMABLE",
        VARIATION_ORDER_TYPE_NON_CLAIMABLE_TEXT: "NON CLAIMABLE",
        CERTIFICATE_INFO_FORMAT_STANDARD: 1,
        CERTIFICATE_INFO_FORMAT_A: 2,
        CERTIFICATE_INFO_FORMAT_B : 3,
        CERTIFICATE_INFO_FORMAT_NSC: 4,
        CERTIFICATE_INFO_FORMAT_STANDARD_TEXT: 'STANDARD',
        CERTIFICATE_INFO_FORMAT_A_TEXT: 'FORMAT A',
        CERTIFICATE_INFO_FORMAT_NSC_TEXT: 'NSC',
        GRID_LAST_ROW: 'LAST_ROW'
    };

    buildspace.getRecalculateBillStatuses = function(){
        return [
            this.constants.BILL_STATUS_RESOURCE_ANALYSIS_RECALCULATE_ITEM,
            this.constants.BILL_STATUS_RESOURCE_ANALYSIS_RECALCULATE_ELEMENT,
            this.constants.BILL_STATUS_RESOURCE_ANALYSIS_RECALCULATE_BILL,
            this.constants.BILL_STATUS_SCHEDULE_OF_RATE_ANALYSIS_RECALCULATE_ITEM,
            this.constants.BILL_STATUS_SCHEDULE_OF_RATE_ANALYSIS_RECALCULATE_ELEMENT,
            this.constants.BILL_STATUS_SCHEDULE_OF_RATE_ANALYSIS_RECALCULATE_BILL,
            this.constants.BILL_STATUS_SCHEDULE_OF_QUANTITY_RECALCULATE_ITEM,
            this.constants.BILL_STATUS_SCHEDULE_OF_QUANTITY_RECALCULATE_ELEMENT,
            this.constants.BILL_STATUS_SCHEDULE_OF_QUANTITY_RECALCULATE_BILL
        ];
    };

    buildspace.isRecalculateBillStatus = function(status){
        return (this.getRecalculateBillStatuses().indexOf( parseInt(String(status)) ) > -1);
    };

    setCookie = function(cname, cvalue, exdays) {
        var expires = '';
        if(exdays){
            var d = new Date();
            d.setTime(d.getTime() + (exdays*24*60*60*1000));
            expires = "expires="+ d.toUTCString();
        }
        document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
    }

    getCookie = function(cname) {
        var name = cname + "=";
        var decodedCookie = decodeURIComponent(document.cookie);
        var ca = decodedCookie.split(';');
        for(var i = 0; i <ca.length; i++) {
            var c = ca[i];
            while (c.charAt(0) == ' ') {
                c = c.substring(1);
            }
            if (c.indexOf(name) == 0) {
                return c.substring(name.length, c.length);
            }
        }
        return "";
    }

    getCookieBoolean = function(cname){
        var trueValues = [
            "true",
            "1"
        ];

        return trueValues.includes(getCookie(cname));
    }
});