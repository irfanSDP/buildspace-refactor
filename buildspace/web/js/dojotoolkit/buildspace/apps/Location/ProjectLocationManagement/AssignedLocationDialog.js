define('buildspace/apps/Location/ProjectLocationManagement/AssignedLocationDialog',[
    'dojo/_base/declare',
    'dojo/_base/lang',
    'dojo/keys',
    "dojo/dom-style",
    "dijit/layout/ContentPane",
    'dojo/i18n!buildspace/nls/Location'
], function(declare, lang, keys, domStyle, ContentPane, nls){

    var LocationAssignmentContainer = declare('buildspace.apps.ProjectLocationManagement.AssignedLocationDialog.LocationAssignmentContainer', dijit.layout.BorderContainer, {
        style: "padding:0;margin:0;width:100%;height:100%;",
        gutters: false,
        locationAssignment: null,
        tabContainer: null,
        postCreate: function(){
            this.inherited(arguments);

            var toolbar = new dijit.Toolbar({
                region: "top",
                style: "outline:none!important;padding:2px;overflow:hidden;"
            });

            toolbar.addChild(
                new dijit.form.Button({
                    label: nls.removeLocation,
                    iconClass: "icon-16-container icon-16-delete",
                    style:"outline:none!important;",
                    onClick: lang.hitch(this, "removeLocation")
                })
            );

            this.addChild(toolbar);

            var predefinedLocationCodes = [],
                projctStructureLocationCodes = [];

            if(this.locationAssignment.hasOwnProperty("predefined_location_code")){
                dojo.forEach(this.locationAssignment["predefined_location_code"], function(item){
                    predefinedLocationCodes.push(String(item.name));
                });
            }

            if(this.locationAssignment.hasOwnProperty("project_structure_location_code")){
                dojo.forEach(this.locationAssignment["project_structure_location_code"], function(item){
                    projctStructureLocationCodes.push(String(item.name));
                });
            }

            var content = new ContentPane({
                content:'<div class="buildspace-form"><fieldset>\n' +
                '<legend>'+nls.predefinedLocationCodes+'</legend>\n' +
                '<div style="height:2px;">&nbsp;</div>\n' +
                '<p>'+predefinedLocationCodes.join(" <span style='color:blue'> > </span>")+'</p>' +
                '</fieldset></div>' +
                '<div class="buildspace-form"><fieldset>\n' +
                '<legend>'+nls.projectStructureLocationCodes+'</legend>\n' +
                '<div style="height:2px;">&nbsp;</div>\n' +
                '<p>'+projctStructureLocationCodes.join(" <span style='color:blue'> > </span>")+'</p>' +
                '</fieldset></div>',
                region: "top",
                style:"height:auto;"
            });

            this.addChild(content);
        },
        removeLocation: function(){

            var _this = this,
                tabContainer = this.tabContainer,
                billItem = this.baseDialog.billItem,
                locationAssignment = this.locationAssignment,
                pb = buildspace.dialog.indeterminateProgressBar({
                title:nls.deleting+'. '+nls.pleaseWait+'...'
            });

            new buildspace.dialog.confirm(nls.removeLocation, nls.removeLocationMsg, 90, 320, function() {
                pb.show().then(function(){
                    dojo.xhrPost({
                        url: "location/removeAssignedLocation",
                        content: { id: locationAssignment.id, _csrf_token: billItem._csrf_token },
                        handleAs: 'json',
                        load: function(data) {
                            pb.hide();
                            if(data.success){
                                tabContainer.removeChild(_this);
                                _this.destroyRecursive();

                                var billItemGrid = dijit.byId('location_assignment-project_item_grid-'+_this.baseDialog.baseApp.project.id);

                                if(billItemGrid){
                                    billItemGrid.reload();
                                }

                                _this.baseDialog.baseApp.resetBQLocationTab();

                                if(tabContainer.getChildren().length == 0){
                                    _this.baseDialog.hide();
                                }
                            }
                        },
                        error: function(error) {
                            pb.hide();
                        }
                    });
                });
            });
        }
    });

    var MainContainer = declare('buildspace.apps.ProjectLocationManagement.AssignedLocationDialog.MainContainer', dijit.layout.BorderContainer, {
        style: "padding:0px;width:100%;height:100%;",
        gutters: false,
        baseDialog: null,
        postCreate: function(){
            this.inherited(arguments);

            var billItem = this.baseDialog.billItem;

            var content = new ContentPane({
                content:'<div class="buildspace-form"><fieldset>\n' +
                '<legend>'+nls.billItem+'</legend>\n' +
                '<div style="height:2px;">&nbsp;</div>\n' +
                '<p>'+String(billItem.description)+'</p>' +
                '</fieldset></div>',
                region: "top",
                style:"height:auto;"
            });

            var tabContainer = new dijit.layout.TabContainer({
                region: "center",
                style:"padding:0px;margin:0px;border:0px;width:100%;height:100%;"
            });

            var locationAssignments = this.baseDialog.locationAssignments;

            var i = 1;
            for (var key in locationAssignments) {
                locationAssignments[key]['id'] = parseInt(key);
                tabContainer.addChild(new LocationAssignmentContainer({
                    title: nls.location+" "+i,
                    region: "center",
                    baseDialog: this.baseDialog,
                    tabContainer: tabContainer,
                    locationAssignment: locationAssignments[key]
                }));

                i++;
            }

            this.addChild(content);
            this.addChild(tabContainer);
        }
    });

    return declare('buildspace.apps.ProjectLocationManagement.AssignedLocationDialog', dijit.Dialog, {
        title: nls.listOfLocationsAssigned,
        style:"padding:0px;margin:0px;",
        billItem: null,
        locationAssignments: null,
        baseApp: null,
        buildRendering: function(){
            var content = this.createContent();
            content.startup();
            this.content = content;

            this.inherited(arguments);
        },
        postCreate: function(){
            domStyle.set(this.containerNode, {
                padding:"0px",
                margin:"0px"
            });
            this.closeButtonNode.style.display = "none";
            this.inherited(arguments);
        },
        _onKey: function(e){
            var key = e.keyCode;
            if (key == keys.ESCAPE) {
                dojo.stopEvent(e);
            }
        },
        onHide: function() {
            this.destroyRecursive();
        },
        createContent: function(){
            var borderContainer = new dijit.layout.BorderContainer({
                    style:"padding:0px;width:780px;height:420px;",
                    gutters: false
                });

            var toolbar = new dijit.Toolbar({
                region: "top",
                style: "outline:none!important;padding:2px;overflow:hidden;"
            });

            toolbar.addChild(
                new dijit.form.Button({
                    label: nls.close,
                    iconClass: "icon-16-container icon-16-close",
                    style:"outline:none!important;",
                    onClick: dojo.hitch(this, 'hide')
                })
            );

            borderContainer.addChild(toolbar);

            borderContainer.addChild(new MainContainer({
                region: 'center',
                baseDialog: this
            }));

            return borderContainer;
        }
    });
});