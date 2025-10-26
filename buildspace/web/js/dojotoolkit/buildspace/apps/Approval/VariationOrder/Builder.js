define('buildspace/apps/Approval/VariationOrder/Builder',[
    '../../../../dojo/_base/declare',
    'buildspace/apps/Approval/BaseBuilder',
    'buildspace/apps/PostContract',
    'buildspace/apps/PostContract/VariationOrder'
], function(declare, BaseBuilder, PostContract, VariationOrder){

    return declare('buildspace.apps.Approval.VariationOrder.Builder', BaseBuilder, {
        url: 'approval/approveClaim',
        initViewArea: function(){
            return new VariationOrder({
                rootProject: this.project,
                variationOrder: this.object,
                locked: true
            });
        }
    });

});