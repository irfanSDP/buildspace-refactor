@if ( $tender->callingTenderInformation )
    <hr style="margin: 15px 0; border-color: black;"/>

    <div class="row">
        <section class="col col-xs-12 col-md-12 col-lg-12">
            <div class="table-responsive">
                <h2 style="margin: 0 0 10px;">{{ trans('tenders.selectedContractors') }}</h2>

                <table class="table  table-hover" id="datatable_fixed_column">
                    <thead>
                    <tr>
                        <th class="text-middle text-center squeeze" style="width:24px;">{{ trans('general.no') }}</th>
                        <th class="text-middle text-left">{{ trans('tenders.contractor') }}</th>
                        <th class="text-middle text-center" style="width:160px;">{{ trans('companies.referenceNumber') }}</th>
                        <th class="text-middle text-center squeeze text-nowrap" style="width:100px;">{{ trans('general.status') }}</th>
                        <th class="text-middle text-center squeeze text-nowrap" style="width:92px;">{{ trans('general.status') . ' ' . trans('general.log') }}</th>
                    </tr>
                    </thead>
                    <tbody>
                    @if ( $tender->callingTenderInformation->selectedContractors->count() > 0 )
                        <?php $counter = 1; ?>
                        @foreach ( $tender->callingTenderInformation->selectedContractors as $contractor )
                            <tr>
                                <td class="text-middle text-center">
                                    <?php echo $counter ++; ?>
                                </td>
                                <td class="text-middle text-left">
                                    @if ( $contractor->contractor )
                                        <a class="plain" href="{{route('contractors.show', array($contractor->contractor->company_id))}}">{{{ $contractor->name }}}</a>
                                    @else
                                        <a class="plain" href="{{route('companies.show', array($contractor->id))}}">{{{ $contractor->name }}}</a>
                                    @endif
                                </td>

                                <td class="text-middle text-center">{{{ $contractor->reference_no }}}</td>

                                <td class="text-middle text-center">
                                    @if ( $disabled )
                                        {{{ PCK\TenderRecommendationOfTendererInformation\ContractorCommitmentStatus::getText($contractor->pivot->status) }}}
                                    @else
                                        {{ Form::select("status[{$contractor->id}]", PCK\TenderRecommendationOfTendererInformation\ContractorCommitmentStatus::getCallingTenderDropDownListing(), $contractor->pivot->status, ['class' => 'select2 fill-horizontal']) }}
                                    @endif
                                </td>
                                <td class="text-middle text-center">
                                    <?php
                                        $tenderStage = PCK\Tenders\TenderStages::TENDER_STAGE_CALLING_TENDER;
                                        $tabId = \PCK\Forms\TenderCallingTenderInformationForm::TAB_ID;
                                    ?>

                                    <a href="#{{{ $tabId }}}" class="btn btn-primary" id="viewlog-{{{ $contractor->id }}}" companyId="{{{ $contractor->id }}}" tenderStage="{{{  $tenderStage }}}" data-toggle="modal" data-target="#commitmentStatusLogModal">
                                        <i class="fa fa-search"></i> {{ trans('general.view') }}
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="8" style="text-align: center; color: red;">No record(s) available..</td>
                        </tr>
                    @endif
                    </tbody>
                </table>
            </div>
        </section>
    </div>
@endif