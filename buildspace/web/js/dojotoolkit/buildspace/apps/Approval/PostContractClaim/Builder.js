define('buildspace/apps/Approval/PostContractClaim/Builder',[
    '../../../../dojo/_base/declare',
    'buildspace/apps/Approval/BaseBuilder',
    'buildspace/apps/PostContract',
    'buildspace/apps/PostContract/PostContractClaim'
], function(declare, BaseBuilder, PostContract, PostContractClaim){

    return declare('buildspace.apps.Approval.PostContractClaim.Builder', BaseBuilder, {
        url: 'approval/approveClaim',
        initViewArea: function(){
            return new PostContractClaim({
                rootProject: this.project,
                claimObject: this.object,
                type: this.object.module_identifier,
                locked: true
            });
        }
    });

});