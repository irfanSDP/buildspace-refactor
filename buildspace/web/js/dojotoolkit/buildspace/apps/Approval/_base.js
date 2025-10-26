define(["dojo/_base/declare", 'buildspace/apps/_App', './LetterOfAward/Builder', './VariationOrder/Builder', './ClaimCertificate/Builder', './PostContractClaim/Builder', 'dojo/i18n!buildspace/nls/Approval'], function(declare, _App, LetterOfAwardBuilder, VariationOrderBuilder, ClaimCertificateBuilder, PostContractClaimBuilder, nls){
    return declare('buildspace.apps.Approval', buildspace.apps._App, {
        win: null,
        init: function(args){
            this.win = new buildspace.widget.Window({
                title: nls.approval,
                onClose: dojo.hitch(this, "kill")
            });

            this.win.addChild(this.projectListing);
            this.win.show();
            this.win.startup();
        },
        projectListingDblClick: function(e){
            var item = this.projectListing.grid.getItem(e.rowIndex);
            if(item.id > 0){
                this.createBuilderWin(item);
            }
        },
        createBuilderWin: function(project, object){
            this.kill();
            this.project = project;
            var title = buildspace.apps.Approval.getTypeText(object.module_identifier);
            this.win = new buildspace.widget.Window({
                title: nls.approval + ': ' + nls.project + ' > ' + buildspace.truncateString(project.title, 100) + ' (' + title + ')',
                onClose: dojo.hitch(this, "kill")
            });

            var builder = null;

            switch(parseInt(object.module_identifier)) {
                case buildspace.apps.Approval.Constants.TYPE_LETTER_OF_AWARD:
                    builder = new LetterOfAwardBuilder({
                        project: project,
                        object: object
                    });
                    break;
                case buildspace.apps.Approval.Constants.TYPE_VARIATION_ORDER:
                    builder = new VariationOrderBuilder({
                        project: project,
                        object: object
                    });
                    break;
                case buildspace.apps.Approval.Constants.TYPE_CLAIM_CERTIFICATE:
                    builder = new ClaimCertificateBuilder({
                        project: project,
                        object: object
                    });
                    break;
                case buildspace.apps.Approval.Constants.TYPE_ADVANCED_PAYMENT:
                case buildspace.apps.Approval.Constants.TYPE_WATER_DEPOSIT:
                case buildspace.apps.Approval.Constants.TYPE_DEPOSIT:
                case buildspace.apps.Approval.Constants.TYPE_OUT_OF_CONTRACT_ITEM:
                case buildspace.apps.Approval.Constants.TYPE_PURCHASE_ON_BEHALF:
                case buildspace.apps.Approval.Constants.TYPE_WORK_ON_BEHALF:
                case buildspace.apps.Approval.Constants.TYPE_WORK_ON_BEHALF_BACK_CHARGE:
                case buildspace.apps.Approval.Constants.TYPE_PENALTY:
                case buildspace.apps.Approval.Constants.TYPE_PERMIT:
                case buildspace.apps.Approval.Constants.TYPE_POST_CONTRACT_CLAIM_MATERIAL_ON_SITE:
                    builder = new PostContractClaimBuilder({
                        project: project,
                        object: object
                    });
                    break;
                default:
                    break;
            }

            if(!builder) return;

            this.win.addChild(builder);
            this.win.show();
            this.win.startup();
        },
        makeTab: function(appName, title, pane){
            this.tabArea.addChild(pane);
            this.tabArea.selectChild(pane);

            pane.mod_info = {
                title: title,
                appName: appName
            };
        },
        kill: function(){
            if (this.win && typeof(this.win) != "undefined"){
                this.win.close();
            }
        }
    });
});