require([
    'buildspace/apps/ProjectSummary/_base'
], function(){
    buildspace.apps.ProjectSummary.ProjectStatus = {
        STATUS_PRETENDER: 1,
        STATUS_TENDERING: 2,
        STATUS_POSTCONTRACT: 4
    }
});