define('buildspace/apps/Approval/ClaimCertificate/Builder',[
    '../../../../dojo/_base/declare',
    'buildspace/apps/Approval/BaseBuilder',
    'buildspace/apps/PostContract',
    './ViewArea'
], function(declare, BaseBuilder, PostContract, ViewArea){

    return declare('buildspace.apps.Approval.ClaimCertificate.Builder', BaseBuilder, {
        url: 'approval/approveClaim',
        initViewArea: function(){
            return this.viewArea = new ViewArea({
                project: this.project,
                claimCertificate: this.object
            });
        }
    });

});