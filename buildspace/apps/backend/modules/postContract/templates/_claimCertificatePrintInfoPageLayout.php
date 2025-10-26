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
        	<td style="width:132px;text-align:left">Contractor Name:</td>
            <td style="text-align:left"><?php echo $contractorName ?></td>
            <td style="width:126px;text-align:left">Project Code:</td>
            <td style="text-align:left"><?php echo $projectCode ?></td>
        </tr>
        <tr>
        	<td style="width:132px;text-align:left">Address:</td>
            <td style="text-align:left"><?php echo $contractorAddr ?></td>
            <td style="width:126px;text-align:left">Reference:</td>
            <td style="text-align:left"><?php echo $reference ?></td>
        </tr>
        <tr>
        	<td style="width:132px;text-align:left">Tel:</td>
            <td style="text-align:left"><?php echo $contractorTel ?></td>
            <td style="width:126px;text-align:left">Fax:</td>
            <td style="text-align:left"><?php echo $fax ?></td>
        </tr>
        <tr>
        	<td style="width:132px;text-align:left">Date:</td>
            <td style="text-align:left"><?php echo $date ?></td>
        	<td style="width:126px;text-align:left">Payment Due Date:</td>
            <td style="text-align:left"><?php echo $dueDate ?></td>
        </tr>
        <tr>
        	<td style="width:132px;text-align:left">Person In Charge:</td>
            <td style="text-align:left"><?php echo $contractorPIC ?></td>
            <td style="width:126px;text-align:left">Claim No:</td>
            <td style="text-align:left"><?php echo $claimNo ?></td>
        </tr>
        <tr>
        	<td style="width:132px;text-align:left">Sub Contract Works:</td>
            <td style="text-align:left"><?php echo $subPackageTitle ?></td>
            <td style="width:126px;text-align:left">Completion %:</td>
            <td style="text-align:left"><?php echo $completionPercentage ?></td>
        </tr>
        <tr>
		    <td style="width:132px;text-align:left">Prepared By:</td>
            <td style="text-align:left"><?php echo $personInCharge ?></td>
        	<td style="width:126px;text-align:left">Accm Previous:</td>
            <td style="text-align:left"><?php echo $cumulativePreviousAmountCertified ?></td>
        </tr>
        <tr>
        	<td style="text-align:left" colspan="2"></td>
            <td style="width:126px;text-align:left">Lab + Mat:</td>
            <td style="text-align:left"><?php echo $worksfromLA ?></td>
		</tr>
		<tr>
        	<td style="width:132px;text-align:left">Remark:</td>
            <td style="text-align:left" colspan="3"><?php echo $remark ?></td>
        </tr>
        <tr>
        	<td style="width:132px;text-align:left">Project Title:</td>
            <td style="text-align:left" colspan="3"><?php echo $projectTitle ?></td>
        </tr>
	</table>
</fieldset>
<fieldset>
    <table cellpadding="0" cellspacing="5" width="100%">
        <tr>
			<td></td>
			<td></td>
        	<td style="text-align:right">Contract Sum</td>
            <td>&nbsp;</td>
            <td style="text-align:right">%</td>
			<td style="text-align:right">Work Done</td>
			<?php if($claimCertificatePrintSettings['display_tax_column']):?>
            <td style="text-align:right">
				<?php echo $claimCertificatePrintSettings['tax_label']?>&nbsp;<span><?php echo $taxPercentage ?></span>%
            </td>
			<td style="text-align:right">Amount</td>
			<?php endif ?>
        </tr>
        <tr>
            <td colspan="<?php if($claimCertificatePrintSettings['display_tax_column']):?>8 <?php else: ?>6<?php endif?>"></td>
		</tr>
		<!--section A-->
        <tr>
			<td style="text-align:left">
				<label style="display:inline;font-size:13px;font-weight:bold;"><?php echo $claimCertificatePrintSettings['section_a_label']?></label>
            </td>
            <td style="text-align:left">
				<label style="display:inline;font-size:13px;">Bill Total</label>
            </td>
        	<td style="text-align:right"><?php echo $billTotal ?></td>
			<td style="text-align:right" colspan="3"><?php echo $billWorkDone ?></td>
			<?php if($claimCertificatePrintSettings['display_tax_column']):?>
			<td colspan="2"></td>
			<?php endif?>
        </tr>
        <tr>
			<td></td>
        	<td style="text-align:left">Variation Order</td>
        	<td style="text-align:right"><?php echo $voTotal ?></td>
			<td style="text-align:right" colspan="3"><?php echo $voWorkDone ?></td>
			<?php if($claimCertificatePrintSettings['display_tax_column']):?>
			<td colspan="2"></td>
			<?php endif?>
		</tr>
		<?php if($requestForVariationWorkDone != 0):?>
		<tr>
			<td></td>
        	<td style="text-align:left">RFV Claims</td>
			<td style="text-align:right" colspan="4"><?php echo $requestForVariationWorkDone ?></td>
			<?php if($claimCertificatePrintSettings['display_tax_column']):?>
			<td colspan="2"></td>
			<?php endif?>
		</tr>
		<?php endif;?>
		<?php if($retentionSumIncludeMaterialOnSite): ?>
			<tr>
				<td></td>
				<td style="text-align:left">Material On Site</td>
				<td style="text-align:left" colspan="3"></td>
				<td style="text-align:right"><?php echo $materialOnSiteWorkDone ?></td>
				<?php if($claimCertificatePrintSettings['display_tax_column']):?>
				<td colspan="2"></td>
				<?php endif?>
			</tr>
		<?php endif ?>
        <tr>
		    <td></td>
			<td colspan="5" style="border-top:1px solid black"></td>
			<?php if($claimCertificatePrintSettings['display_tax_column']):?>
			<td colspan="2"></td>
			<?php endif ?>
		</tr>
        <tr>
			<td></td>
        	<td style="text-align:left">Total Work Done</td>
        	<td style="text-align:right"><?php echo $contractSum ?></td>
            <td>&nbsp;</td>
            <td style="text-align:right"><?php echo $completionPercentage ?></td>
			<td style="text-align:right"><?php echo $totalWorkDone ?></td>
			<?php if($claimCertificatePrintSettings['display_tax_column']):?>
			<td colspan="2"></td>
			<?php endif?>
        </tr>
		<tr>
			<td></td>
			<td style="text-align:left" colspan="4">Retention Sum</td>
			<td style="text-align:right"><font color="red">[<?php echo $cumulativeTotalRetentionWithoutCurrentClaimRelease ?>]</font></td>
			<?php if($claimCertificatePrintSettings['display_tax_column']):?>
			<td style="text-align:right" colspan="2"></td>
			<?php endif?>
		</tr>
		<tr>
			<td></td>
			<td style="text-align:left" colspan="4">Release Retention</td>
			<td style="text-align:right">
				<?php if($claimCertificatePrintSettings['display_tax_column']):?>
					(<?php echo $retentionTaxPercentage ?>)
				<?php endif?>
				<?php echo $currentReleaseRetentionAmount ?>
			</td>
			<?php if($claimCertificatePrintSettings['display_tax_column']):?>
			<td style="text-align:right" ><?php echo $releaseRetentionAmountAfterGST ?></td>
			<td></td>
			<?php endif?>
		</tr>
		<tr>
		    <td colspan="5"></td>
			<td style="border-top:1px solid black"></td>
			<?php if($claimCertificatePrintSettings['display_tax_column']):?>
			<td colspan="2"></td>
			<?php endif?>
		</tr>
		<tr>
			<td></td>
		    <td style="text-align:left" colspan="4">Total Amount</td>
			<td style="text-align:right"><?php echo $cumulativeAmountCertified ?></td>
			<?php if($claimCertificatePrintSettings['display_tax_column']):?>
		    <td style="text-align:right"><?php echo $cumulativeAmountGSTAmount ?></td>
			<td></td>
			<?php endif?>
		</tr>
		<tr>
			<td></td>
			<td style="text-align:left" colspan="4">Previous Certified</td>
			<td style="text-align:right"><font color="red">[<?php echo $cumulativePreviousAmountCertified ?>]</font></td>
			<?php if($claimCertificatePrintSettings['display_tax_column']):?>
			<td style="text-align:right" colspan="2"></td>
			<?php endif?>
		</tr>
		<tr>
		    <td colspan="5"></td>
		    <td <?php if($claimCertificatePrintSettings['display_tax_column']):?>colspan="3"<?php endif?> style="border-top:1px solid black"></td>
		</tr>
		<tr>
			<td></td>
			<td style="text-align:left" colspan="4">Amount Certified</td>
			<td style="text-align:right"><?php echo $amountCertified ?></td>
			<?php if($claimCertificatePrintSettings['display_tax_column']):?>
		    <td style="text-align:right"><?php echo $amountCertifiedTaxAmount ?></td>
			<td style="text-align:right"><?php echo $amountCertifiedIncludingTax ?></td>
			<?php endif?>
		</tr>
		<tr>
            <td colspan="<?php if($claimCertificatePrintSettings['display_tax_column']):?> 8 <?php else:?> 6 <?php endif?>"></td>
		</tr>
		<tr>
		    <td colspan="<?php if($claimCertificatePrintSettings['display_tax_column']):?> 8 <?php else:?> 6 <?php endif?>" style="border-top:1px solid black"></td>
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
			<td style="text-align:right">ACCM Total</td>
		    <td style="text-align:right" colspan="2">Previous Claim</td>
			<td style="text-align:right">This Claim</td>
			<?php if($claimCertificatePrintSettings['display_tax_column']):?>
			<td colspan="2"></td>
			<?php endif?>
		</tr>
		<tr>
		    <td colspan="<?php if($claimCertificatePrintSettings['display_tax_column']):?> 8 <?php else:?> 6 <?php endif?>"></td>
		</tr>
		<?php if($claimCertificatePrintSettings['include_advance_payment']):?>
		<tr>
			<td></td>
			<td style="text-align:left">Advance Payment</td>
			<td style="text-align:right"><?php echo $advancePaymentOverallTotal ?></td>
		    <td style="text-align:right" colspan="2"><font color="red">[<?php echo $advancePaymentPreviousClaim ?>]</font></td>
			<td style="text-align:right"><?php echo $advancePaymentThisClaim ?></td>
			<?php if($claimCertificatePrintSettings['display_tax_column']):?>
			<td style="text-align:right" colspan="2"><?php echo $advancePaymentThisClaimAfterGST ?></td>
			<?php endif?>
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
			<?php if($claimCertificatePrintSettings['display_tax_column']):?>
			<td style="text-align:right" colspan="2"><?php echo $materialOnSiteThisClaimAfterGST ?></td>
			<?php endif?>
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
			<?php if($claimCertificatePrintSettings['display_tax_column']):?>
			<td style="text-align:right" colspan="2"><?php echo $kskThisClaimAfterGST ?></td>
			<?php endif?>
		</tr>
		<?php endif;?>
		<?php if($claimCertificatePrintSettings['include_work_on_behalf_mc']):?>
		<tr>
			<td></td>
			<td style="text-align:left">WOB ( M/C )</td>
			<td style="text-align:right"><?php echo $wobMCOverallTotal ?></td>
		    <td style="text-align:right" colspan="2"><?php echo $wobMCPreviousClaim ?></td>
			<td style="text-align:right"><?php echo $wobMCThisClaim ?></td>
			<?php if($claimCertificatePrintSettings['display_tax_column']):?>
			<td style="text-align:right" colspan="2"><?php echo $wobMCThisClaimAfterGST ?></td>
			<?php endif?>
		</tr>
		<?php endif;?>
		<tr>
		    <td colspan="<?php if($claimCertificatePrintSettings['display_tax_column']):?> 8 <?php else:?> 6 <?php endif?>"></td>
		</tr>
		<tr>
		    <td style="text-align:right" colspan="5">Sub Total</td>
			<td style="text-align:right"><?php echo $miscThisClaimSubTotal ?></td>
			<?php if($claimCertificatePrintSettings['display_tax_column']):?>
		    <td style="text-align:right"><?php echo $miscThisClaimAfterGSTSubTotal ?></td>
			<td style="text-align:right"><?php echo $miscThisClaimOverallTotal ?></td>
			<?php endif?>
		</tr>
		<tr>
		    <td colspan="<?php if($claimCertificatePrintSettings['display_tax_column']):?> 8 <?php else:?> 6 <?php endif?>"></td>
		</tr>
		<tr>
		    <td colspan="<?php if($claimCertificatePrintSettings['display_tax_column']):?> 8 <?php else:?> 6 <?php endif?>" style="border-top:1px solid black"></td>
		</tr>
		<tr>
			<td colspan="5" style="text-align:right;font-size:12px;">
			<?php echo $claimCertificatePrintSettings['tax_invoice_by_sub_contractor_label']?>
		    </td>
			<td style="text-align:right"><?php echo $taxInvoiceBySubConSubTotal ?></td>
			<?php if($claimCertificatePrintSettings['display_tax_column']):?>
		    <td style="text-align:right"><?php echo $taxInvoiceBySubConAfterGSTSubTotal ?></td>
			<td style="text-align:right"><?php echo $taxInvoiceBySubConOverallTotal ?></td>
			<?php endif?>
		</tr>
		<tr>
		    <td colspan="<?php if($claimCertificatePrintSettings['display_tax_column']):?> 8 <?php else:?> 6 <?php endif?>"></td>
		</tr>
		<tr>
		    <td colspan="<?php if($claimCertificatePrintSettings['display_tax_column']):?> 8 <?php else:?> 6 <?php endif?>" style="border-top:1px solid black"></td>
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
				<?php if($claimCertificatePrintSettings['display_tax_column']):?>
				<td style="text-align:right" colspan="2"><?php echo number_format($debitCreditNoteBreakdownThisClaimAfterGSTTotal, 2, '.', ',') ?></td>
				<?php endif?>
			</tr>
			<?php endforeach;?>
			<?php else:?>
		<tr>
			<td></td>
			<td style="text-align:left">Credit/Debit Note</td>
			<td style="text-align:right"><?php echo $debitCreditNoteOverallTotal ?></td>
		    <td style="text-align:right" colspan="2"><?php echo $debitCreditNotePreviousClaim ?></td>
			<td style="text-align:right"><?php echo $debitCreditNoteThisClaim ?></td>
			<?php if($claimCertificatePrintSettings['display_tax_column']):?>
			<td style="text-align:right" colspan="2"><?php echo $debitCreditNoteThisClaimAfterGST ?></td>
			<?php endif?>
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
			<?php if($claimCertificatePrintSettings['display_tax_column']):?>
			<td style="text-align:right" colspan="2"><?php echo $pobThisClaimAfterGST ?></td>
			<?php endif?>
		</tr>
		<?php endif;?>
		<?php if($claimCertificatePrintSettings['include_work_on_behalf']):?>
		<tr>
			<td></td>
			<td style="text-align:left">WOB</td>
			<td style="text-align:right"><?php echo $wobOverallTotal ?></td>
		    <td style="text-align:right" colspan="2"><?php echo $wobPreviousClaim ?></td>
			<td style="text-align:right"><?php echo $wobThisClaim ?></td>
			<?php if($claimCertificatePrintSettings['display_tax_column']):?>
			<td style="text-align:right" colspan="2"><?php echo $wobThisClaimAfterGST ?></td>
			<?php endif?>
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
		    <td colspan="<?php if($claimCertificatePrintSettings['display_tax_column']):?> 8 <?php else:?> 6 <?php endif?>"></td>
		</tr>
		<tr>
		    <td colspan="<?php if($claimCertificatePrintSettings['display_tax_column']):?> 8 <?php else:?> 6 <?php endif?>" style="border-top:1px solid black"></td>
		</tr>
		<tr>
			<td colspan="5" style="text-align:right;font-size:12px;"><?php echo $claimCertificatePrintSettings['tax_invoice_by_subsidiary_label']?> <span><?php echo $companyName ?></span></td>
			<td style="text-align:right"><?php echo $otherThisClaimSubTotal ?></td>
			<?php if($claimCertificatePrintSettings['display_tax_column']):?>
		    <td style="text-align:right"><?php echo $otherThisClaimAfterGSTSubTotal ?></td>
			<td style="text-align:right"><?php echo $otherThisClaimOverallTotal ?></td>
			<?php endif?>
		</tr>
		<tr>
		    <td colspan="<?php if($claimCertificatePrintSettings['display_tax_column']):?> 8 <?php else:?> 6 <?php endif?>"></td>
		</tr>
		<tr>
		    <td colspan="<?php if($claimCertificatePrintSettings['display_tax_column']):?> 8 <?php else:?> 6 <?php endif?>" style="border-top:1px solid black"></td>
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
		    <td style="text-align:right" <?php if($claimCertificatePrintSettings['display_tax_column']):?> colspan="3" <?php endif?>><?php echo $waterDepositThisClaim ?></td>
		</tr>
		<?php endif;?>
		<?php if($claimCertificatePrintSettings['include_permit']):?>
		<tr>
			<td></td>
			<td style="text-align:left">Permit</td>
			<td style="text-align:right"><?php echo $permitOverallTotal ?></td>
		    <td style="text-align:right" colspan="2"><font color="red">[<?php echo $permitPreviousClaim ?>]</font></td>
			<td style="text-align:right" <?php if($claimCertificatePrintSettings['display_tax_column']):?> colspan="3" <?php endif?>><?php echo $permitThisClaim ?></td>
		</tr>
		<?php endif;?>
		<tr>
		    <td colspan="<?php if($claimCertificatePrintSettings['display_tax_column']):?> 8 <?php else:?> 6 <?php endif?>"></td>
		</tr>
		<tr>
			<td style="text-align:right" colspan="5">Sub Total</td>
			<td style="text-align:right"><?php echo $paymentOnBehalfThisClaimSubTotal ?></td>
			<?php if($claimCertificatePrintSettings['display_tax_column']):?>
		    <td style="text-align:right"><?php echo $paymentOnBehalfThisClaimAfterGSTSubTotal ?></td>
			<td style="text-align:right"><?php echo $paymentOnBehalfThisClaimOverallTotal ?></td>
			<?php endif?>
		</tr>
		<tr>
		    <td colspan="<?php if($claimCertificatePrintSettings['display_tax_column']):?> 8 <?php else:?> 6 <?php endif?>"></td>
		</tr>
		<tr>
		    <td colspan="<?php if($claimCertificatePrintSettings['display_tax_column']):?> 8 <?php else:?> 6 <?php endif?>" style="border-top:1px solid black"></td>
		</tr>
		<?php endif;?>
		<tr>
			<td style="text-align:right;" colspan="5">
			    <label style="display:inline;font-size:13px;font-weight:bold;">Net Payable Amount (<?php echo $currencyCode?>)</label>
		    </td>
			<td style="text-align:right"><?php echo $netPayableAmount ?></td>
			<?php if($claimCertificatePrintSettings['display_tax_column']):?>
		    <td style="text-align:right"><?php echo $netPayableAmountGST ?></td>
			<td style="text-align:right"><?php echo $netPayableAmountOverallTotal ?></td>
			<?php endif?>
		</tr>
		<?php if($claimCertificatePrintSettings['footer_format'] != ClaimCertificatePrintSetting::FOOTER_FORMAT_NONE):?>
		<tr>
		    <td colspan="<?php if($claimCertificatePrintSettings['display_tax_column']):?> 8 <?php else:?> 6 <?php endif?>"></td>
		</tr>
		<tr>
		    <td colspan="<?php if($claimCertificatePrintSettings['display_tax_column']):?> 8 <?php else:?> 6 <?php endif?>" style="border-top:1px solid black"></td>
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