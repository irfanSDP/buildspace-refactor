<fieldset style="border-bottom:none;">
	<table width="100%">
		<tr>
			<td class="label" style="width:50%;text-align:left;border-bottom:1pt solid black;">
				<label style="display:inline;font-size:13px;font-weight:bold;"><?php echo $companyName ?></label>
			</td>
			<td class="label" style="width:50%;text-align:right;border-bottom:1pt solid black;">
				<label style="display:inline;font-size:13px;font-weight:bold;"><?php echo $claimCertificatePrintSettings['certificate_title']?></label>
			</td>
		</tr>
	</table>
    <table cellpadding="2" cellspacing="2" width="100%">
		<tr>
			<td style="width:132px;text-align:left">Project Description:</td>
			<td style="text-align:left" colspan="3"><?php echo $projectTitle ?></td>
		</tr>
		<tr>
			<td style="text-align:left" colspan="2"></td>
			<td style="width:126px;text-align:left">Reference:</td>
			<td style="text-align:left"><?php echo $reference ?></td>
		</tr>
		<tr>
			<td style="width:132px;text-align:left">Sub Contract Works:</td>
			<td style="text-align:left"><?php echo $subPackageTitle ?></td>
			<td style="width:126px;text-align:left">Certificate Date:</td>
			<td style="text-align:left"><?php echo date('d/m/Y', strtotime($claimCertificate->qs_received_date)) ?></td>
		</tr>
		<tr>
			<td style="width:132px;text-align:left">Contractor:</td>
			<td style="text-align:left"><?php echo $contractorName ?></td>
			<td style="width:126px;text-align:left">Period Ending:</td>
			<td style="text-align:left"><?php echo date('d/m/Y', strtotime($claimCertificate->budget_due_date)) ?></td>
		</tr>
		<tr>
			<td style="width:132px;text-align:left">Invoice Date:</td>
			<td style="text-align:left"><?php echo ($claimCertificate->Invoice && $claimCertificate->Invoice->invoice_date) ? date('d/m/Y', strtotime($claimCertificate->Invoice->invoice_date)) : ""; ?></td>
			<td style="width:126px;text-align:left">Payment Due Date:</td>
			<td style="text-align:left"><?php echo $dueDate ?></td>
		</tr>
		<tr>
			<td style="width:132px;text-align:left">Invoice No:</td>
			<td style="text-align:left"><?php echo ($claimCertificate->Invoice && $claimCertificate->Invoice->invoice_number) ? $claimCertificate->Invoice->invoice_number : ""; ?></td>
			<td style="width:126px;text-align:left">Claim No:</td>
			<td style="text-align:left"><?php echo $claimNo ?></td>
		</tr>
		<tr>
			<td style="width:132px;text-align:left">Contract Sum:</td>
			<td style="text-align:left"><?php echo $billTotal ?></td>
			<td style="width:132px;text-align:left">URN:</td>
			<td style="text-align:left"><?php echo $urn ?></td>
		</tr>
		<tr>
			<td style="width:132px;text-align:left">Total VO Amount:</td>
			<td style="text-align:left" colspan="3"><?php echo $voTotal ?></td>
		</tr>
		<tr>
			<td style="width:132px;text-align:left">Revised Contract Sum:</td>
			<td style="text-align:left"><?php echo $contractSum ?></td>
			<td style="width:126px;text-align:left">Completion %:</td>
			<td style="text-align:left"><?php echo $completionPercentage ?></td>
		</tr>
		<tr>
			<td style="width:132px;text-align:left">Remark:</td>
			<td style="text-align:left" colspan="3"><?php echo $remark ?></td>
		</tr>
		<tr>
			<td style="width:132px;text-align:left">Prepared By:</td>
			<td style="text-align:left" colspan="3"><?php echo $personInCharge ?></td>
		</tr>
	</table>
</fieldset>
<fieldset>
    <table cellpadding="0" cellspacing="5" width="100%">
        <tr>
			<td></td>
			<td></td>
        	<td style="text-align:right">ACCM Total</td>
		    <td style="text-align:right" colspan="2">Previous Claim</td>
			<td style="text-align:right">This Claim</td>
        </tr>
        <tr>
		    <td colspan="6"></td>
		</tr>
		<!--section A-->
        <tr>
			<td style="text-align:left">
				<label style="display:inline;font-size:13px;font-weight:bold;"><?php echo $claimCertificatePrintSettings['section_a_label']?></label>
            </td>
            <td style="text-align:left">
				<label style="display:inline;font-size:13px;">Bill Total</label>
            </td>
        	<td style="text-align:right"><?php echo $billWorkDone ?></td>
            <td style="text-align:right" colspan="2"><?php echo $previousBillClaimWorkDone ?></td>
			<td style="text-align:right"><?php echo $currentBillClaimWorkDone ?></td>
        </tr>
        <tr>
			<td></td>
        	<td style="text-align:left">Variation Order</td>
        	<td style="text-align:right"><?php echo $voWorkDone ?></td>
            <td style="text-align:right" colspan="2"><?php echo $previousCumulativeVoWorkDone ?></td>
			<td style="text-align:right"><?php echo $currentVoWorkDone ?></td>
		</tr>
		<?php if( $showRequestForVariationWorkDone ):?>
		<tr>
			<td></td>
        	<td style="text-align:left">RFV Claims</td>
        	<td style="text-align:right"><?php echo $requestForVariationWorkDone ?></td>
        	<td style="text-align:right" colspan="2"><?php echo $previousCumulativeRequestForVariationWorkDone ?></td>
			<td style="text-align:right"><?php echo $currentRequestForVariationWorkDone ?></td>
		</tr>
		<?php endif;?>
		<?php if($retentionSumIncludeMaterialOnSite): ?>
			<tr>
				<td></td>
				<td style="text-align:left">Material On Site</td>
				<td style="text-align:right"><?php echo $cumulativeMaterialOnSiteWorkDone ?></td>
				<td style="text-align:right" colspan="2"><?php echo $previousCumulativeMaterialOnSiteWorkDone ?></td>
				<td style="text-align:right"><?php echo $currentMaterialOnSiteWorkDone ?></td>
			</tr>
		<?php endif ?>
        <tr>
			<td></td>
        	<td style="text-align:left"><strong>Total Work Done</strong></td>
        	<td style="text-align:right"><strong><?php echo $totalWorkDone ?></strong></td>
            <td style="text-align:right" colspan="2"><strong><?php echo $previousTotalWorkDone ?></strong></td>
			<td style="text-align:right"><strong><?php echo $currentTotalWorkDone ?></strong></td>
        </tr>
        <tr>
	        <td></td>
			<td colspan="5" style="border-top:1px solid black"></td>
	    </tr>
        <tr>
			<td></td>
        	<td style="text-align:left">Retention Sum</td>
        	<td style="text-align:right"><font color="red">[<?php echo $cumulativeRetentionSum ?>]</font></td>
            <td style="text-align:right" colspan="2"><font color="red">[<?php echo $previousCumulativeRetentionSum ?>]</font></td>
			<td style="text-align:right"><font color="red">[<?php echo $currentRetentionSum ?>]</font></td>
        </tr>
        <tr>
			<td></td>
        	<td style="text-align:left">Release Retention</td>
        	<td style="text-align:right"><?php echo $cumulativeReleasedRetentionAmount ?></td>
            <td style="text-align:right" colspan="2"><?php echo $previousCumulativeReleasedRetentionAmount ?></td>
			<td style="text-align:right">
				<?php echo $currentReleaseRetentionAmount ?>
			</td>
        </tr>
		<tr>
			<td></td>
        	<td style="text-align:left"><strong>Total Retention</strong></td>
        	<td style="text-align:right"><strong><font color="red">[<?php echo $cumulativeTotalRetention ?>]</font></strong></td>
            <td style="text-align:right" colspan="2"><strong><font color="red">[<?php echo $previousCumulativeTotalRetention ?>]</font></strong></td>
			<td style="text-align:right"><strong><font color="red">[<?php echo $currentTotalRetention ?>]</font></strong></td>
        </tr>
        <tr>
	        <td></td>
	        <td colspan="5" style="border-top:1px solid black"></td>
	    </tr>
		<tr>
			<td></td>
        	<td style="text-align:left"><strong>Amount Certified</strong></td>
            <td style="text-align:right"><strong><?php echo $cumulativeAmountCertified ?></strong></td>
            <td style="text-align:right" colspan="2"><strong><?php echo $cumulativePreviousAmountCertified ?></strong></td>
			<td style="text-align:right"><strong><?php echo $amountCertified ?></strong></td>
        </tr>
        <tr>
			<td></td>
        	<td style="text-align:left"><strong><?php echo $amountCertifiedTaxPercentageLabel ?></strong></td>
            <td style="text-align:right">&nbsp;</td>
            <td style="text-align:right" colspan="2">&nbsp;</td>
			<td style="text-align:right"><strong><?php echo $amountCertifiedTax ?></strong></td>
        </tr>
        <tr>
			<td></td>
        	<td style="text-align:left"><strong>Amount Certified + Tax</strong></td>
            <td style="text-align:right"><strong><?php echo $cumulativeAmountCertifiedPlusTax ?></strong></td>
            <td style="text-align:right" colspan="2"><strong><?php echo $cumulativePreviousAmountCertifiedPlusTax ?></strong></td>
			<td style="text-align:right"><strong><?php echo $amountCertifiedPlusTax ?></strong></td>
        </tr>
        <?php if($selectedRfvCategoryName) :?>
		<tr>
	        <td></td>
	        <td colspan="5" style="border-top:1px solid black"></td>
	    </tr>
        <tr>
            <td></td>
        	<td style="text-align:left"><strong><?php echo $selectedRfvCategoryName ?></strong></td>
            <td style="text-align:right"><strong><?php echo $voWorkDoneForSelectedRfvCategory ?></strong></td>
            <td style="text-align:right" colspan="2"><strong><?php echo $previousVoWorkDoneForSelectedRfvCategory ?></strong></td>
			<td style="text-align:right"><strong><?php echo $currentVoWorkDoneForSelectedRfvCategory ?></strong></td>
        </tr>
        <?php endif ?>
		<tr>
		    <td colspan="6" style="border-top:1px solid black"></td>
		</tr>
		<?php if($claimCertificatePrintSettings['display_section_b']):?>
		<!--section B -->
		<tr>
			<td style="text-align:left">
				<label style="display:inline;font-size:13px;font-weight:bold;"><?php echo $claimCertificatePrintSettings['section_b_label']?></label>
            </td>
			<td style="text-align:left">
			    <label style="display:inline;font-size:13px;font-weight:bold;"><?php echo $claimCertificatePrintSettings['section_misc_label']?></label>
		    </td>
			<td colspan="4"></td>
		</tr>
		<tr>
		    <td colspan="6"></td>
		</tr>
		<?php if($claimCertificatePrintSettings['include_advance_payment']):?>
		<tr>
			<td></td>
			<td style="text-align:left">Advance Payment</td>
			<td style="text-align:right"><?php echo $advancePaymentOverallTotalFormatB ?></td>
		    <td style="text-align:right" colspan="2"><?php echo $advancePaymentPreviousClaimFormatB ?></td>
			<td style="text-align:right"><?php echo $advancePaymentThisClaimFormatB ?></td>
		</tr>
        <tr>
			<td></td>
			<td style="text-align:left">Advance Payment Recoupment</td>
			<td style="text-align:right"><font color="red">[<?php echo $advancePaymentRecoupmentOverallTotal ?>]</font></td>
		    <td style="text-align:right" colspan="2"><font color="red">[<?php echo $advancePaymentRecoupmentPreviousClaim ?>]</font></td>
			<td style="text-align:right"><font color="red">[<?php echo $advancePaymentRecoupmentThisClaim ?>]</font></td>
		</tr>
		<?php endif;?>
		<?php if($claimCertificatePrintSettings['include_deposit']):?>
		<tr>
			<td></td>
			<td style="text-align:left">Deposit</td>
			<td style="text-align:right"><?php echo $depositOverallTotal ?></td>
		    <td style="text-align:right" colspan="2"><font color="red">[<?php echo $depositPreviousClaim ?>]</font></td>
		    <td style="text-align:right" <?php if($claimCertificatePrintSettings['display_tax_column']):?> colspan="3" <?php endif?>><?php echo $depositThisClaim ?></td>
		</tr>
		<?php endif;?>
		<?php if(!$retentionSumIncludeMaterialOnSite): ?>
		<?php if($claimCertificatePrintSettings['include_material_on_site']):?>
		<tr>
			<td></td>
			<td style="text-align:left">Material On Site</td>
			<td style="text-align:right"><?php echo $materialOnSiteOverallTotal ?></td>
		    <td style="text-align:right" colspan="2"><font color="red">[<?php echo $materialOnSitePreviousClaim ?>]</font></td>
			<td style="text-align:right"><?php echo $materialOnSiteThisClaim ?></td>
		</tr>
		<?php endif;?>
		<?php endif ?>
		<?php if($claimCertificatePrintSettings['include_ksk']):?>
		<tr>
			<td></td>
			<td style="text-align:left">KSK</td>
			<td style="text-align:right"><?php echo $kskOverallTotal ?></td>
			<td style="text-align:right" colspan="2"><?php echo $kskPreviousClaim ?></td>
			<td style="text-align:right"><?php echo $kskThisClaim ?></td>
		</tr>
		<?php endif;?>
		<?php if($claimCertificatePrintSettings['include_work_on_behalf_mc']):?>
		<tr>
			<td></td>
			<td style="text-align:left">WOB ( M/C )</td>
			<td style="text-align:right"><?php echo $wobMCOverallTotal ?></td>
		    <td style="text-align:right" colspan="2"><?php echo $wobMCPreviousClaim ?></td>
			<td style="text-align:right"><?php echo $wobMCThisClaim ?></td>
		</tr>
		<?php endif;?>
		<tr>
		    <td colspan="6"></td>
		</tr>
		<tr>
		    <td style="text-align:right" colspan="5">Sub Total</td>
			<td style="text-align:right"><?php echo $miscThisClaimSubTotalRecoupment ?></td>
		</tr>
		<tr>
		    <td colspan="6>"></td>
		</tr>
		<tr>
		    <td colspan="6>" style="border-top:1px solid black"></td>
		</tr>
		<tr>
			<td colspan="5" style="text-align:right;font-size:12px;">
			<?php echo $claimCertificatePrintSettings['tax_invoice_by_sub_contractor_label']?>
		    </td>
			<td style="text-align:right"><?php echo $taxInvoiceBySubConSubTotalRecoupment ?></td>
		</tr>
		<tr>
		    <td colspan="6"></td>
		</tr>
		<tr>
		    <td colspan="6" style="border-top:1px solid black"></td>
		</tr>
		<?php endif;?>
		<?php if($claimCertificatePrintSettings['display_section_c']):?>
		<!--section C -->
		<tr>
			<td style="text-align:left">
				<label style="display:inline;font-size:13px;font-weight:bold;"><?php echo $claimCertificatePrintSettings['section_c_label']?></label>
            </td>
			<td style="text-align:left" colspan="<?php if($claimCertificatePrintSettings['display_tax_column']):?> 7 <?php else:?> 5 <?php endif?>">
				<label style="display:inline;font-size:13px;font-weight:bold;"><?php echo $claimCertificatePrintSettings['section_others_label']?></label>
		    </td>
		</tr>
		<?php if($claimCertificatePrintSettings['include_debit_credit_note']):?>
			<?php if($claimCertificatePrintSettings['debit_credit_note_with_breakdown']):?>
			<?php foreach($debitCreditNoteBreakdownOverallTotal as $id => $debitCreditBreakdown):?>
			<?php
			$debitCreditNoteBreakdownPreviousClaimTotal = (array_key_exists($id, $debitCreditNoteBreakdownPreviousClaim)) ? $debitCreditNoteBreakdownPreviousClaim[$id]['total'] : 0;
			$debitCreditNoteBreakdownThisClaimTotal = (array_key_exists($id, $debitCreditNoteBreakdownThisClaim)) ? $debitCreditNoteBreakdownThisClaim[$id]['total'] : 0;
			$debitCreditNoteBreakdownThisClaimAfterGSTTotal = (array_key_exists($id, $debitCreditNoteBreakdownThisClaimAfterGST)) ? $debitCreditNoteBreakdownThisClaimAfterGST[$id]['total'] : 0;
			?>
			<tr>
				<td></td>
				<td style="text-align:left"><?php echo $debitCreditBreakdown['description'] ?></td>
				<td style="text-align:right"><?php echo number_format($debitCreditBreakdown['total'], 2, '.', ',') ?></td>
				<td style="text-align:right" colspan="2"><?php echo  number_format($debitCreditNoteBreakdownPreviousClaimTotal, 2, '.', ',')?></td>
				<td style="text-align:right"><?php echo number_format($debitCreditNoteBreakdownThisClaimTotal, 2, '.', ',') ?></td>
			</tr>
			<?php endforeach;?>
			<?php else:?>
		<tr>
			<td></td>
			<td style="text-align:left">Credit/Debit Note</td>
			<td style="text-align:right"><?php echo $debitCreditNoteOverallTotal ?></td>
		    <td style="text-align:right" colspan="2"><?php echo $debitCreditNotePreviousClaim ?></td>
			<td style="text-align:right"><?php echo $debitCreditNoteThisClaim ?></td>
		</tr>
			<?php endif;?>
		<?php endif;?>
		<?php if($claimCertificatePrintSettings['include_purchase_on_behalf']):?>
		<tr>
			<td></td>
			<td style="text-align:left">POB</td>
			<td style="text-align:right"><?php echo $pobOverallTotal ?></td>
		    <td style="text-align:right" colspan="2"><font color="red">[<?php echo $pobPreviousClaim ?>]</font></td>
			<td style="text-align:right"><?php echo $pobThisClaim ?></td>
		</tr>
		<?php endif;?>
		<?php if($claimCertificatePrintSettings['include_work_on_behalf']):?>
		<tr>
			<td></td>
			<td style="text-align:left">WOB</td>
			<td style="text-align:right"><?php echo $wobOverallTotal ?></td>
		    <td style="text-align:right" colspan="2"><?php echo $wobPreviousClaim ?></td>
			<td style="text-align:right"><?php echo $wobThisClaim ?></td>
		</tr>
		<?php endif;?>
		<?php if($claimCertificatePrintSettings['include_penalty']):?>
		<tr>
			<td></td>
			<td style="text-align:left">Penalty</td>
			<td style="text-align:right"><?php echo $penaltyOverallTotal ?></td>
		    <td style="text-align:right" colspan="2"><?php echo $penaltyPreviousClaim ?></td>
		    <td style="text-align:right" <?php if($claimCertificatePrintSettings['display_tax_column']):?> colspan="3" <?php endif?>><?php echo $penaltyThisClaim ?></td>
		</tr>
		<?php endif;?>
		<tr>
		    <td colspan="6"></td>
		</tr>
		<tr>
		    <td colspan="6" style="border-top:1px solid black"></td>
		</tr>
		<tr>
			<td colspan="5" style="text-align:right;font-size:12px;"><?php echo $claimCertificatePrintSettings['tax_invoice_by_subsidiary_label']?> <span><?php echo $companyName ?></span></td>
			<td style="text-align:right"><?php echo $otherThisClaimSubTotal ?></td>
		</tr>
		<tr>
		    <td colspan="6"></td>
		</tr>
		<tr>
		    <td colspan="6" style="border-top:1px solid black"></td>
		</tr>
		<?php endif;?>
		<?php if($claimCertificatePrintSettings['display_section_d']):?>
		<!-- section D -->
		<tr>
			<td style="text-align:left">
				<label style="display:inline;font-size:13px;font-weight:bold;"><?php echo $claimCertificatePrintSettings['section_d_label']?></label>
            </td>
			<td style="text-align:left" colspan="<?php if($claimCertificatePrintSettings['display_tax_column']):?> 7 <?php else:?> 5 <?php endif?>">
			    <label style="display:inline;font-size:13px;font-weight:bold;"><?php echo $claimCertificatePrintSettings['section_payment_on_behalf_label']?></label>
			</td>
		</tr>
		<?php if($claimCertificatePrintSettings['include_utility']):?>
		<tr>
			<td></td>
			<td style="text-align:left">Utility</td>
			<td style="text-align:right"><?php echo $waterDepositOverallTotal ?></td>
		    <td style="text-align:right" colspan="2"><?php echo $waterDepositPreviousClaim ?></td>
		    <td style="text-align:right"><?php echo $waterDepositThisClaim ?></td>
		</tr>
		<?php endif;?>
		<?php if($claimCertificatePrintSettings['include_permit']):?>
		<tr>
			<td></td>
			<td style="text-align:left">Permit</td>
			<td style="text-align:right"><?php echo $permitOverallTotal ?></td>
		    <td style="text-align:right" colspan="2"><font color="red">[<?php echo $permitPreviousClaim ?>]</font></td>
			<td style="text-align:right"><?php echo $permitThisClaim ?></td>
		</tr>
		<?php endif;?>
		<tr>
		    <td colspan="6"></td>
		</tr>
		<tr>
			<td style="text-align:right" colspan="5">Sub Total</td>
			<td style="text-align:right"><?php echo $paymentOnBehalfThisClaimSubTotal ?></td>
		</tr>
		<tr>
		    <td colspan="6" style="border-top:1px solid black"></td>
		</tr>
		<?php endif;?>
		<tr>
			<td style="text-align:right;" colspan="5">
			<label style="display:inline;font-size:13px;font-weight:bold;">Net Payable Amount (<?php echo $currencyCode?>)</label>
		    </td>
			<td style="text-align:right"><strong><?php echo $netPayableAmountRecoupmentWithTax ?></strong></td>
		</tr>
		<?php if($claimCertificatePrintSettings['footer_format'] != ClaimCertificatePrintSetting::FOOTER_FORMAT_NONE):?>
		<tr>
		    <td colspan="6"></td>
		</tr>
		<tr>
		    <td colspan="6" style="border-top:1px solid black"></td>
		</tr>
		<?php endif ?>
	</table>
	<?php if($claimCertificatePrintSettings['footer_format'] == ClaimCertificatePrintSetting::FOOTER_FORMAT_A):?>
    <table cellpadding="0" cellspacing="12" width="100%">
    	<tr>
            <td width="25%" style="text-align:left"><?php echo $claimCertificatePrintSettings['footer_bank_label'] ?></td>
			<td width="25%" style="text-align:left"><?php echo $claimCertificatePrintSettings['footer_cheque_number_label'] ?></td>
			<td width="25%" style="text-align:left"><?php echo $claimCertificatePrintSettings['footer_cheque_date_label'] ?></td>
			<td width="25%" style="text-align:left"><?php echo $claimCertificatePrintSettings['footer_cheque_amount_label'] ?></td>
        </tr>
        <tr>
		    <td colspan="4" style="height:28px;"></td>
		</tr>
		<tr>
            <td width="25%" style="text-align:left"><?php echo $claimCertificatePrintSettings['footer_bank_signature_label'] ?></td>
			<td width="25%" style="text-align:left"><?php echo $claimCertificatePrintSettings['footer_cheque_number_signature_label'] ?></td>
			<td width="25%" style="text-align:left"><?php echo $claimCertificatePrintSettings['footer_cheque_date_signature_label'] ?></td>
			<td width="25%" style="text-align:left"><?php echo $claimCertificatePrintSettings['footer_cheque_amount_signature_label'] ?></td>
        </tr>
        <tr>
            <td width="25%" style="border-bottom:1px solid #000;text-align:left;height:24px;"></td>
			<td width="25%" style="border-bottom:1px solid #000;text-align:left;height:24px;"></td>
			<td width="25%" style="border-bottom:1px solid #000;text-align:left;height:24px;"></td>
			<td width="25%" style="border-bottom:1px solid #000;text-align:left;height:24px;"></td>
        </tr>
	</table>
	<?php elseif($claimCertificatePrintSettings['footer_format'] == ClaimCertificatePrintSetting::FOOTER_FORMAT_B && $claimCertificate->getContractManagementClaimVerifiers()->count()):?>

	<table cellpadding="2" cellspacing="2" width="100%">
		<thead>
			<tr>
				<th style="text-align:left;" colspan="4">Verifier Log</th>
			</tr>
			<tr>
				<th style="width:10px;text-align:center;">No.</th>
				<th style="width:200px;text-align:center;">Name</th>
				<th style="width:100px;text-align:center;">Status</th>
				<th style="width:100px;text-align:center;">Verified At</th>
				<th style="width:auto;text-align:center;">Remarks</th>
			</tr>
		</thead>
		<tbody>
			<?php $count = 1?>
			<?php foreach($claimCertificate->getContractManagementClaimVerifiers() as $verifier):?>
			<?php $statusText = ($verifier->verified_at) ? ($verifier->approved ? 'Approved' : 'Rejected') : "Pending"; ?>
			<tr>
				<td style="width:10px;text-align:center;"><?php echo $count ?></td>
				<td style="width:200px;text-align:center;"><?php echo $verifier->sfGuardUser->first_name?></td>
				<td style="width:100px;text-align:center;"><?php echo $statusText ?></td>
				<td style="width:100px;text-align:center;"><?php echo ($verifier->verified_at) ? date('d/m/Y', strtotime($verifier->verified_at)) : ""?></td>
				<td style="width:auto;text-align:left;"><?php echo $verifier->remarks?></td>
			</tr>
			<?php $count++?>
			<?php endforeach;?>
		</tbody>
	</table>
	<?php endif?>
</fieldset>