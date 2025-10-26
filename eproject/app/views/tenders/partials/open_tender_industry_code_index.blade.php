<div class="row">
    <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
        <h1 class="page-title txt-color-blueDark">
            <i class="fa fa-book"></i>&nbsp;&nbsp;{{{ trans('openTender.industryCode') }}}
        </h1>
    </div>
    @if($approvalStatus != PCK\Tenders\OpenTenderPageInformation::STATUS_PENDING_FOR_APPROVAL)
        <div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">
            <a href="{{ route('open-tender-industry-code.create',array($project->id,$tenderId))}}">
                <button id="createForm" class="btn btn-primary btn-md pull-right header-btn">
                    <i class="fa fa-plus"></i>&nbsp;{{{ trans('openTender.addOpenTenderIndustryCode') }}}
                </button>
            </a>
        </div>
    @endif
</div>

<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <div class="jarviswidget ">
            <div>
                <div class="widget-body no-padding">
                    <div class="table-responsive">
                        <table class="table " id="dt_basic">
                            <thead>
                                <tr>
                                    <th>{{{ trans('openTender.number') }}}</th>
                                    <th>{{{ trans('openTender.cidbGrade') }}}</th>
                                    <th>{{{ trans('openTender.cidbCode') }}}</th>
                                    <th>{{{ trans('openTender.vendorCategory') }}}</th>
                                    <th>{{{ trans('openTender.vendorWorkCategory') }}}</th>
                                    <th>{{{ trans('openTender.action') }}}</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                    $count = 0;
                                ?>
                                @foreach ($industryCodeRecords as $record)
                                    <tr>
                                        <td>
                                            {{{++$count}}}
                                        </td>
                                        <td>
                                           	{{{$record->cidbGrade->grade}}}
                                        </td>
                                        <td>
                                           	{{{$record->cidbCode->code}}} <br> <p>({{{ $record->cidbCode->description }}})</p>
                                        </td>
                                        <td>
                                           	{{{$record->vendorCategory->name}}}
                                        </td>
                                        <td>
                                           	{{{$record->vendorWorkCategory->name}}}
                                        </td>
                                        <td>
                                        @if($approvalStatus != PCK\Tenders\OpenTenderPageInformation::STATUS_PENDING_FOR_APPROVAL)
                                                <a href="{{{ route('open-tender-industry-code.edit', 
                                                                array($project->id, $tenderId, $record->id)) }}}" class="btn btn-xs btn-success">
                                                    <i class="fa fa-edit"></i>
                                                </a>
                                                &nbsp;
                                                <a href="{{{ route('open-tender-industry-code.delete', 
                                                        array($project->id,$tenderId, $record->id)) }}}" class="btn btn-xs btn-danger" data-method="delete" data-csrf_token="{{ csrf_token() }}">
                                                    <i class="fa fa-trash"></i>
                                                </a>
                                        @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@include('uploads.downloadModal')
