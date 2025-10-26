@if ( $tender->listOfTendererInformation )
    <hr style="margin: 15px 0; border-color: black;"/>

    <div class="row">
        <section class="col col-xs-12 col-md-12 col-lg-12">
            <div class="table-responsive">
                <h2 style="margin: 0 0 10px;">{{ trans('tenders.selectedContractors') }}</h2>
                @if ($errors->has('min_one_contractor_required_LOT'))
                    {{ $errors->first('min_one_contractor_required_LOT',  '<em class="invalid">:message</em>') }}
                @endif
                @if ($errors->has('min_one_status_yes_required_LOT'))
                    {{ $errors->first('min_one_status_yes_required_LOT',  '<em class="invalid">:message</em>') }}
                @endif
                @if ($errors->has('LOT_selected_contractor_status_pending'))
                    {{ $errors->first('LOT_selected_contractor_status_pending',  '<em class="invalid">:message</em>') }}
                @endif
                <table class="table  table-hover" id="datatable_fixed_column">
                    <thead>
                    <tr>
                        @if (! $disabled )
                            <th class="text-middle text-center squeeze" style="width:104px;">{{ trans('general.remove') }}</th>
                        @endif

                        <th class="text-middle text-center squeeze" style="width:24px;">{{ trans('general.no') }}</th>
                        <th class="text-middle text-left">{{ trans('tenders.contractor') }}</th>
                        <th class="text-middle text-center" style="width:120px;">{{ trans('general.status') }}</th>
                        <th class="text-middle text-center text-nowrap" style="width:92px;">{{ trans('general.status') . ' ' . trans('general.log') }}</th>
                        <th class="text-middle text-center text-nowrap">{{ trans('general.remarks') }}</th>
                        @if($isVendorManagementEnabled)
                        <th class="text-middle text-center squeeze text-nowrap" style="width:120px;">{{ trans('vendorManagement.vendorProfile') }}</th>
                        <th class="text-middle text-center squeeze text-nowrap" style="width:120px;">{{ trans('vendorManagement.duplicateCompanyPersonnels') }}</th>
                        @endif
                    </tr>
                    </thead>
                    <tbody>
                    <input type="hidden" name="listOfTendererInformationId" value="{{{ $tender->listOfTendererInformation->id }}}">
                    @if ( $tender->listOfTendererInformation->selectedContractors->count() > 0 )
                        <?php $counter = 1; ?>
                        @foreach ( $tender->listOfTendererInformation->selectedContractors as $contractor )
                            <?php $strikeThrough = $contractor->pivot->deleted_at ? 'text-decoration: line-through;' : null; ?>
                            <?php $colorRed = $contractor->pivot->deleted_at ? 'color:red;' : null; ?>
                            <?php $addedByGCD = $contractor->pivot->added_by_gcd ? 'color: blue;' : null; ?>

                            <tr>
                                @if (! $disabled )
                                    <td class="text-middle text-center">
                                        @if ( $contractor->pivot->deleted_at )
                                            {{ HTML::decode(link_to_route('projects.tender.reenable_lot_contractor', '<i class="fa fa-power-off"></i> Re-enable', [$tender->project->id, $tender->id, $contractor->id], ['class' => 'btn btn-primary'])) }}
                                        @else
                                            {{ HTML::decode(link_to_route('projects.tender.delete_lot_contractor', '<i class="fa fa-trash-alt"></i> '.trans('forms.delete'), [$tender->project->id, $tender->id, $contractor->id], ['class' => 'btn btn-danger', 'data-method'=>'delete', 'data-csrf_token' => csrf_token()])) }}
                                        @endif
                                    </td>
                                @endif

                                <td class="text-middle text-center" style="{{{ $strikeThrough, $colorRed, $addedByGCD }}}">
                                    <?php echo $counter ++; ?>
                                </td>
                                <td class="text-middle text-left" style="{{{ $strikeThrough, $colorRed, $addedByGCD }}}">
                                    @if ( $contractor->contractor )
                                        <a class="plain" href="{{route('contractors.show', array($contractor->contractor->company_id))}}">{{{ $contractor->name }}}</a>
                                    @else
                                        <a class="plain" href="{{route('companies.show', array($contractor->id))}}">{{{ $contractor->name }}}</a>
                                    @endif
                                </td>
                                <td class="text-middle text-center">
                                    @if($tender->project->open_tender)
                                        {{ Form::select("status[{$contractor->id}]", PCK\TenderRecommendationOfTendererInformation\ContractorCommitmentStatus::getListOfTendererDropDownListing(), $contractor->pivot->status, ['class' => 'select2 fill-horizontal']) }}
                                    @else
                                        @if($disabled)
                                            {{{ PCK\TenderRecommendationOfTendererInformation\ContractorCommitmentStatus::getText($contractor->pivot->status) }}}
                                            {{ Form::hidden("status[{$contractor->id}]", $contractor->pivot->status) }}
                                        @else
                                            {{ Form::select("status[{$contractor->id}]", PCK\TenderRecommendationOfTendererInformation\ContractorCommitmentStatus::getListOfTendererDropDownListing(), $contractor->pivot->status, ['class' => 'select2 fill-horizontal']) }}
                                        @endif
                                    @endif
                                </td>
                                <td class="text-middle text-center">
                                    <?php
                                        $tenderStage = PCK\Tenders\TenderStages::TENDER_STAGE_LIST_OF_TENDERER;
                                        $tabId = \PCK\Forms\TenderListOfTendererInformationForm::TAB_ID;
                                    ?>
                                    <a href="#{{{ $tabId }}}" class="btn btn-primary" id="viewlog-{{{ $contractor->id }}}" tenderStage="{{{  $tenderStage }}}" companyId="{{{ $contractor->id }}}" data-toggle="modal" data-target="#commitmentStatusLogModal">
                                        <i class="fa fa-search"></i> {{ trans('general.view') }}
                                    </a>
                                </td>
                                <td class="text-middle text-left" style="{{{ $colorRed, $addedByGCD }}}">
                                    @if ( $disabled )
                                        {{{ $contractor->pivot->remarks ?? '-' }}}
                                    @else
                                        {{ Form::textarea("remarks[{$contractor->id}]", $contractor->pivot->remarks, array('rows' => 3, 'style' => 'padding: 5px;')) }}
                                    @endif
                                </td>
                                @if($isVendorManagementEnabled)
                                <td class="text-middle text-center">
                                    @if($contractor->vendorProfile)
                                    <button type="button" class="btn btn-primary" data-action="show-vendor-profile" data-id="{{ $contractor->id }}"><i class="fa fa-search"></i> {{{trans("forms.view")}}}</button>
                                    @endif
                                </td>
                                <td class="text-middle text-center">
                                    @if(in_array($contractor->id, $lotDuplicateCompanyPersonnelsCompanyIds))
                                    <button type="button" class="btn btn-warning" data-action="view-duplicate-company-personnels" data-id="{{ $contractor->id }}"><i class="fa fa-user-tie"></i> {{{trans("forms.view")}}}</button>
                                    @endif
                                </td>
                                @endif
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