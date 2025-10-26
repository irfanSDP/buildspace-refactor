define('buildspace/apps/Editor/WorkArea',[
    'dojo/_base/declare',
    './ProjectProperties',
    './ProjectBreakdown',
    'dojo/i18n!buildspace/nls/Tendering'], function(declare, ProjectProperties, ProjectBreakdown, nls){

    return declare('buildspace.apps.Editor.WorkArea', dijit.layout.TabContainer, {
        region: "center",
        style:"padding:0px;margin:0px;border:0px;width:100%;height:100%;",
        project: null,
        postCreate: function(){
            this.inherited(arguments);
            
            buildspace.currencyAbbreviation = buildspace.billCurrencyAbbreviation = this.project.currency;

            var projectBreakdown = new ProjectBreakdown({
                id: 'main-project_breakdown',
                title: nls.projectBreakdown,
                project: this.project,
                workArea: this
            });

            this.addChild(projectBreakdown);

            this.addChild(new ProjectProperties({
                title: nls.projectProperties,
                project: this.project,
                workArea: this,
                id: 'projectProperties-' + this.project.id
            }));

            this.selectChild(projectBreakdown);
        },
        createContentPaneTab: function(id, title, content, closable, type){
            var pane = new dijit.layout.ContentPane({
                closable: closable,
                id: id,
                style: "padding:0px;border:0px;margin:0px;overflow:hidden;",
                title: buildspace.truncateString(title, 35),
                content: content
            });

            this.addChild(pane);
            this.selectChild(pane);

            pane.pane_info = {
                name: title,
                type: type,
                id: id
            };
        },
        removeBillTab: function (){
            var tac = this.getChildren();
            for(var i in tac){
                if(typeof tac[i].pane_info != "object") continue;
                if(tac[i].pane_info.type == buildspace.apps.Editor.ProjectStructureConstants.TYPE_BILL){
                    this.removeChild(tac[i]);
                    tac[i].destroy();
                    continue;
                }
            }
        },
        initTab: function(item, options){
            var opened = false;
            var tac = this.getChildren();
            for(var i in tac){
                if(typeof tac[i].pane_info != "object") continue;
                if(tac[i].pane_info.id == item.id+'-form_'+item.type){
                    opened = true;
                    this.selectChild(tac[i]);
                    continue;
                }
            }
            if(!opened){
                this.makeTab(item, options, true);
            }
        },
        removeTabByType: function (type) {
            var tac = this.getChildren();
            for(var i in tac){
                if(typeof tac[i].pane_info != "object") continue;
                if(tac[i].pane_info.type == type){
                    this.removeChild(tac[i]);
                    tac[i].destroy();
                    continue;
                }
            }
        },
        makeTab: function(item, options, closable){
            var widget, id, title;
            switch(parseInt(item.type)){
                case buildspace.apps.Editor.ProjectStructureConstants.TYPE_BILL:
                    var billType = buildspace.apps.Editor.getBillTypeText(item.bill_type);
                    widget = new buildspace.apps.Editor.BillManager(options);
                    id = item.id+'-form_'+item.type;
                    title = buildspace.truncateString(item.title, 35)+' :: '+billType;
                    break;
                default:
                    widget = null;
                    id = item.id+'-form_'+item.type;
                    title = buildspace.truncateString(item.title, 35);
                    break;
            }

            this.createContentPaneTab(id, title, widget, closable, item.type);
        }
    });
});
