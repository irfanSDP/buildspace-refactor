define('buildspace/apps/PostContract/Builder',[
    '../../../dojo/_base/declare',
    './WorkArea',
    'dojo/i18n!buildspace/nls/PostContract'
], function(declare, WorkArea, nls){

    return declare('buildspace.apps.PostContract.Builder', dijit.layout.BorderContainer, {
        style: "padding:0px;width:100%;height:100%;",
        gutters: false,
        project: null,
        workArea: null,
        postCreate: function(){
            this.inherited(arguments);

            var self = this,
                pb = buildspace.dialog.indeterminateProgressBar({
                    title:nls.pleaseWait+'...'
                });

            pb.show().then(function(){
                dojo.xhrPost({
                    url: "postContract/getCurrentViewingClaimRevision",
                    content: { id: self.project.id },
                    handleAs: "json"
                }).then(function(data){

                    var claimCertificate = data.revision ? {post_contract_claim_revision_id: data.revision.id} : null;

                    var workArea = self.workArea = new WorkArea({
                        rootProject: self.project,
                        claimCertificate: claimCertificate
                    });

                    self.addChild(workArea);
                    pb.hide();
                });
            });
        }
    });
});