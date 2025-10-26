<div class="padding-10">
	<div class="row">
		<div class="col col-xs-12 col-md-12 col-lg-12">
			<div class="well">
				<div class="smart-form" id="ebidding-info">
					<div class="row">
						<div class="col col-xs-12 col-md-12 col-lg-12">
							<h4>{{ trans('eBidding.selectedTenderOption') }}:</h4>
							<hr class="simple"/>
						</div>
					</div>
					<div class="row">
						<section class="col col-xs-12 col-md-6 col-lg-3">
							@if (! empty($selectedTenderRate['tenderAlternativeTitle']))
								<div><span>{{ nl2br($selectedTenderRate['tenderAlternativeTitle']) }}</span></div>
							@else
								<div><span>{{ nl2br($selectedTenderRate['companyName']) }}</span></div>
							@endif
						</section>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>