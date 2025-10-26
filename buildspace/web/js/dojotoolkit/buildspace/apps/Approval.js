require([
    'buildspace/apps/Approval/_base'
], function(nls){

    buildspace.apps.Approval.Constants = {
    	TYPE_LETTER_OF_AWARD: 1,
    	TYPE_WATER_DEPOSIT: 2,
    	TYPE_DEPOSIT: 3,
    	TYPE_OUT_OF_CONTRACT_ITEM: 4,
    	TYPE_PURCHASE_ON_BEHALF: 5,
    	TYPE_ADVANCED_PAYMENT: 6,
    	TYPE_WORK_ON_BEHALF: 7,
    	TYPE_WORK_ON_BEHALF_BACK_CHARGE: 8,
    	TYPE_PENALTY: 9,
    	TYPE_PERMIT: 10,
    	TYPE_POST_CONTRACT_CLAIM_MATERIAL_ON_SITE: 11,
    	TYPE_CLAIM_CERTIFICATE: 12,
    	TYPE_VARIATION_ORDER: 32,
    	TYPE_MATERIAL_ON_SITE: 64,
    	TYPE_REQUEST_FOR_VARIATION_CLAIM: 128,
    	TYPE_DEBIT_CREDIT_NOTE: 256,
    	TYPE_LETTER_OF_AWARD_TEXT: 'Letter of Award',
    	TYPE_WATER_DEPOSIT_TEXT: 'Water Deposit',
    	TYPE_DEPOSIT_TEXT: 'Deposit',
    	TYPE_OUT_OF_CONTRACT_ITEM_TEXT: 'OutOfContractItem',
    	TYPE_PURCHASE_ON_BEHALF_TEXT: 'PurchaseOnBehalf',
    	TYPE_ADVANCED_PAYMENT_TEXT: 'Advanced Payment',
    	TYPE_WORK_ON_BEHALF_TEXT: 'Work on Behalf',
    	TYPE_WORK_ON_BEHALF_BACK_CHARGE_TEXT: 'Work on Behalf Backcharge',
    	TYPE_PENALTY_TEXT: 'Penalty',
    	TYPE_PERMIT_TEXT: 'Permit',
    	TYPE_POST_CONTRACT_CLAIM_MATERIAL_ON_SITE_TEXT: 'Claim Material on Site',
    	TYPE_CLAIM_CERTIFICATE_TEXT: 'Claim Certificate',
    	TYPE_VARIATION_ORDER_TEXT: 'Variation Order',
    	TYPE_MATERIAL_ON_SITE_TEXT: 'Material on Site',
    	TYPE_REQUEST_FOR_VARIATION_CLAIM_TEXT: 'Request for Variation',
    	TYPE_DEBIT_CREDIT_NOTE_TEXT: 'Debit/Credit',
    };

    buildspace.apps.Approval.getTypeText = function(type) {
        var typeText;

        switch ( parseInt(type) )
        {
			case buildspace.apps.Approval.Constants.TYPE_LETTER_OF_AWARD:
				typeText = buildspace.apps.Approval.Constants.TYPE_LETTER_OF_AWARD_TEXT;
				break;
			case buildspace.apps.Approval.Constants.TYPE_WATER_DEPOSIT:
				typeText = buildspace.apps.Approval.Constants.TYPE_WATER_DEPOSIT_TEXT;
				break;
			case buildspace.apps.Approval.Constants.TYPE_DEPOSIT:
				typeText = buildspace.apps.Approval.Constants.TYPE_DEPOSIT_TEXT;
				break;
			case buildspace.apps.Approval.Constants.TYPE_OUT_OF_CONTRACT_ITEM:
				typeText = buildspace.apps.Approval.Constants.TYPE_OUT_OF_CONTRACT_ITEM_TEXT;
				break;
			case buildspace.apps.Approval.Constants.TYPE_PURCHASE_ON_BEHALF:
				typeText = buildspace.apps.Approval.Constants.TYPE_PURCHASE_ON_BEHALF_TEXT;
				break;
			case buildspace.apps.Approval.Constants.TYPE_ADVANCED_PAYMENT:
				typeText = buildspace.apps.Approval.Constants.TYPE_ADVANCED_PAYMENT_TEXT;
				break;
			case buildspace.apps.Approval.Constants.TYPE_WORK_ON_BEHALF:
				typeText = buildspace.apps.Approval.Constants.TYPE_WORK_ON_BEHALF_TEXT;
				break;
			case buildspace.apps.Approval.Constants.TYPE_WORK_ON_BEHALF_BACK_CHARGE:
				typeText = buildspace.apps.Approval.Constants.TYPE_WORK_ON_BEHALF_BACK_CHARGE_TEXT;
				break;
			case buildspace.apps.Approval.Constants.TYPE_PENALTY:
				typeText = buildspace.apps.Approval.Constants.TYPE_PENALTY_TEXT;
				break;
			case buildspace.apps.Approval.Constants.TYPE_PERMIT:
				typeText = buildspace.apps.Approval.Constants.TYPE_PERMIT_TEXT;
				break;
			case buildspace.apps.Approval.Constants.TYPE_POST_CONTRACT_CLAIM_MATERIAL_ON_SITE:
				typeText = buildspace.apps.Approval.Constants.TYPE_POST_CONTRACT_CLAIM_MATERIAL_ON_SITE_TEXT;
				break;
			case buildspace.apps.Approval.Constants.TYPE_CLAIM_CERTIFICATE:
				typeText = buildspace.apps.Approval.Constants.TYPE_CLAIM_CERTIFICATE_TEXT;
				break;
			case buildspace.apps.Approval.Constants.TYPE_VARIATION_ORDER:
				typeText = buildspace.apps.Approval.Constants.TYPE_VARIATION_ORDER_TEXT;
				break;
			case buildspace.apps.Approval.Constants.TYPE_MATERIAL_ON_SITE:
				typeText = buildspace.apps.Approval.Constants.TYPE_MATERIAL_ON_SITE_TEXT;
				break;
			case buildspace.apps.Approval.Constants.TYPE_REQUEST_FOR_VARIATION_CLAIM:
				typeText = buildspace.apps.Approval.Constants.TYPE_REQUEST_FOR_VARIATION_CLAIM_TEXT;
				break;
			case buildspace.apps.Approval.Constants.TYPE_DEBIT_CREDIT_NOTE:
				typeText = buildspace.apps.Approval.Constants.TYPE_DEBIT_CREDIT_NOTE_TEXT;
				break;

            default:
                typeText = "";
                break;
        }

        return typeText;
    }
});