<tr>
	<td colspan="<?php echo $headerCount; ?>">
		<table cellpadding="0" cellspacing="0" class="headerTable" style="width: 100%;">

			<?php if ( !empty( $materialOnSitePrintSetting->site_belonging_address ) or !empty( $materialOnSitePrintSetting->original_finished_date ) ): ?>
				<tr>
					<td style="width: 55%; padding-bottom: 18px;">
						<?php echo Utilities::truncateString($materialOnSitePrintSetting->site_belonging_address, 100); ?>
					</td>
					<td style="width: 45%; padding-bottom: 18px;">
						<?php echo Utilities::truncateString($materialOnSitePrintSetting->original_finished_date, 100); ?>
					</td>
				</tr>
			<?php endif; ?>

			<?php if ( !empty( $materialOnSitePrintSetting->contract_duration ) or !empty( $materialOnSitePrintSetting->contract_original_amount ) ): ?>
				<tr>
					<td style="width: 55%; padding-bottom: 18px;">
						<?php echo Utilities::truncateString($materialOnSitePrintSetting->contract_duration, 100); ?>
					</td>
					<td style="width: 45%; padding-bottom: 18px;">
						<?php echo Utilities::truncateString($materialOnSitePrintSetting->contract_original_amount, 100); ?>
					</td>
				</tr>
			<?php endif; ?>

			<?php if ( !empty( $materialOnSitePrintSetting->payment_revision_no ) or !empty( $materialOnSitePrintSetting->evaluation_date ) ): ?>
				<tr>
					<td style="width: 55%; padding-bottom: 18px;">
						<?php echo Utilities::truncateString($materialOnSitePrintSetting->payment_revision_no, 100); ?>
					</td>
					<td style="width: 45%; padding-bottom: 18px;">
						<?php echo Utilities::truncateString($materialOnSitePrintSetting->evaluation_date, 100); ?>
					</td>
				</tr>
			<?php endif; ?>

		</table>
	</td>
</tr>