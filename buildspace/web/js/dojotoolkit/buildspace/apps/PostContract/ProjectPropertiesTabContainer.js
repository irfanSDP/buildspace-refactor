define('buildspace/apps/PostContract/ProjectPropertiesTabContainer',[
'dojo/_base/declare',
'./ProjectProperties',
'buildspace/apps/Approval/LetterOfAward/MainInformation',
'dojo/i18n!buildspace/nls/PostContract'], function(declare, ProjectProperties, LetterOfAwardMainInformation, nls){

	return declare('buildspace.apps.PostContract.ProjectPropertiesTabContainer', dijit.layout.TabContainer, {
		region: "center",
        style:"padding:0px;margin:0px;border:0px;width:100%;height:100%;",
        rootProject: null,
        data:null,
        postCreate: function(){
        	this.inherited(arguments);

         	var self = this;

         	self.addChild(new ProjectProperties({
                title: nls.projectProperties,
                rootProject: self.rootProject,
                id: 'projectProperties-' + self.rootProject.id,
                data: self.data
            }));

         	dojo.xhrGet({
                url: "postContract/getPublishedType",
                content: { pid: self.rootProject.id },
                handleAs: "json"
            }).then(function(data){
                if(data.success){
                    self.addChild(new LetterOfAwardMainInformation({
                        title: nls.contractInformation,
                        rootProject: self.rootProject,
                        id: 'letterOfAward-' + self.rootProject.id
                    }));
                }
            });
        }
    });
});
