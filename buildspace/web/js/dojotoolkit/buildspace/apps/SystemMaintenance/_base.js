define(["dojo/_base/declare",
    'dojo/i18n!buildspace/nls/SystemMaintenance',
    './UnitOfMeasurementMaintenance/UnitOfMeasurementMaintenanceForm',
    './DimensionMaintenance/DimensionMaintenanceForm',
    './BillAdminSettingMaintenance/BillAdminSettingMaintenanceForm',
    './BusinessTypeMaintenance/BusinessTypeMaintenanceForm',
    './RegionMaintenance/grid',
    './WorkCategoryMaintenance/CategoryMaintenanceForm',
    './ProjectSummaryDefaultSettings/ProjectSummaryDefaultSettingsForm',
    './VoPrintingDefaultSettings/VoPrintingDefaultSettingsForm',
    './GlobalCalendarMaintenance/GlobalCalendarMaintenanceForm',
    './PredefinedLocationCodeMaintenance/PredefinedLocationCodeMaintenanceForm',
    './RetentionSumCodeMaintenance/RetentionSumCodeMaintenanceForm',
    './SubPackageWorkMaintenance/Form',
    './AccountGroup/AccountCode/AccountGroupMaintenanceForm',
    './ClaimCertificateTaxMaintenance/ClaimCertificateTaxMaintenanceForm'
], function(declare, nls, UnitOfMeasurementMaintenance, DimensionMaintenance, BillAdminSettingMaintenance, BusinessTypeMaintenance, RegionMaintenance,CategoryMaintenanceForm, ProjectSummaryDefaultSettingsForm, VoPrintingDefaultSettingsForm, GlobalCalendarMaintenanceForm, PredefinedLocationCodeMaintenanceForm, RetentionSumCodeMaintenanceForm, SubPackageWorkForm, AccountCodeMaintenanceForm, ClaimCertificateTaxMaintenanceForm){
    return declare('buildspace.apps.SystemMaintenance', buildspace.apps._App, {
        win: null,
        init: function(args){
            this.win = new buildspace.widget.Window({
                title: nls.appName,
                height: '300px',
                width: '450px',
                showMaximize: false,
                iconClass: this.iconClass,
                onClose: dojo.hitch(this, "kill")
            });

            var systemMaintenanceMenuStore = this.systemMaintenanceMenuStore = new dojo.data.ItemFileWriteStore({
                url:"default/getSystemMaintenanceMenu"
            });

            var treeModel = new dijit.tree.ForestStoreModel({
                store: systemMaintenanceMenuStore,
                rootId: "root",
                rootLabel: nls.appName,
                childrenAttrs: ["__children"]
            });

            var right = this.tabArea = new dijit.layout.TabContainer({
                region: "center",
                style:"width:100%;height:100%;"
            });

            //TODO: move files using DnD?
            var left = this.left = new dijit.EditableTree({
                model: treeModel,
                splitter: true,
                region: "left",
                openOnClick:true,
                labelAttr: 'name',
                style: "background-color:#ecede9;width:200px;height:100%;",
                getIconClass: dojo.hitch(systemMaintenanceMenuStore, function(item, opened){
                    if(item.root || item.parent){
                        return opened ? 'icon-16-container icon-16-file' : 'icon-16-container icon-16-folder';
                    }else{
                        return 'icon-16-container icon-16-list';
                    }
                }),
                onClick:function(e,node) {

                }
            });

            dojo.connect(left, "onDblClick", this, "onItem");

            var mainContainer = this.mainContainer = new dijit.layout.BorderContainer({
                style:"padding:0px;width:100%;height:100%;",
                gutters: false
            });

            mainContainer.addChild(left);
            mainContainer.addChild(right);

            this.win.addChild(mainContainer);
            this.win.show();
            this.win.startup();
        },
        onItem: function(item){

            var store = this.systemMaintenanceMenuStore;
            if(!store.isItem(item)) return;
            var slug = store.getValue(item, "slug");
            var param = store.getValue(item, "param");
            var name = store.getValue(item, "name");

            slug = (param) ? slug + '-' + param : slug;

            if(slug == 'root') return;

            var tac = this.tabArea.getChildren();

            for(var i in tac){
                if(typeof tac[i].lib_info != "object") continue;
                if(tac[i].lib_info.slug == slug){
                    return this.tabArea.selectChild(tac[i]);
                }
            }

            this.makeTab(item);
        },
        makeTab: function(item){

            if(!item.app[0]){
                return;
            }

            var slug = item.slug[0];
            var name = item.name[0];
            var form = new buildspace.apps[slug][slug+'FormContainer']({
                param: (item.param) ? item.param[0] : null
            });

            var pane = new dijit.layout.ContentPane({
                closable: true,
                style: "padding: 0px; overflow: hidden;border:0px;",
                title: buildspace.truncateString(name, 25),
                content: form
            });

            this.tabArea.addChild(pane);
            this.tabArea.selectChild(pane);

            pane.lib_info = {
                name: name,
                slug: (item.param) ? slug + '-' + item.param[0] : slug
            };
        },
        kill: function() {
            if (typeof(this.win) != "undefined"){
                this.win.close();
            }
        }
    });
});