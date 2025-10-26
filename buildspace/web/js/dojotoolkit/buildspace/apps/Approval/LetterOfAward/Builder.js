define('buildspace/apps/Approval/LetterOfAward/Builder',[
    '../../../../dojo/_base/declare',
    './ViewArea',
    'buildspace/apps/Approval/BaseBuilder'
], function(declare, ViewArea, BaseBuilder){

    return declare('buildspace.apps.Approval.LetterOfAward.Builder', BaseBuilder, {
        url: 'approval/approveLetterOfAward',
        initViewArea: function(){
            var self = this;
            return self.viewArea = ViewArea({
                rootProject: self.project
            });
        }
    });

});