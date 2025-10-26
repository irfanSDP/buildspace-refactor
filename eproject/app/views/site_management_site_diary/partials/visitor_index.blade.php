<div class="row">
    <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
        <h1 class="page-title txt-color-blueDark">
            <i class="fa fa-users"></i>&nbsp;&nbsp;Visitor
        </h1>
    </div>
    @if(!$show)
        <div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">
            <a href="{{ route('site-management-site-diary-visitor.create',array($project->id,$siteDiaryId))}}">
                <button id="createForm" class="btn btn-primary btn-md pull-right header-btn">
                    <i class="fa fa-plus"></i>&nbsp;Add Visitor
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
                                    <th>Number</th>
                                    <th>Name</th>
                                    <th>Company</th>
                                    <th>Time In</th>
                                    <th>Time Out</th>
                                    @if(!$show)
                                    <th>Action</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                    $count = 0;
                                ?>
                                @foreach ($visitorForms as $record)
                                    <tr>
                                        <td>
                                            {{{++$count}}}
                                        </td>
                                        <td>
                                           	{{{$record->visitor_name}}}
                                        </td>
                                        <td>
                                           	{{{$record->visitor_company_name}}}
                                        </td>
                                        <td>
                                           	{{{$record->visitor_time_in}}}
                                        </td>
                                        <td>
                                           	{{{$record->visitor_time_out}}}
                                        </td>
                                        @if(!$show)
                                        <td>
                                            <a href="{{{ route('site-management-site-diary-visitor.edit', 
                                                            array($project->id, $siteDiaryId, $record->id)) }}}" class="btn btn-xs btn-success">
                                                <i class="fa fa-edit"></i>
                                            </a>
                                            &nbsp;
                                            <a href="{{{ route('site-management-site-diary-visitor.delete', 
                                                    array($project->id,$siteDiaryId, $record->id)) }}}" class="btn btn-xs btn-danger" data-method="delete" data-csrf_token="{{ csrf_token() }}">
                                                <i class="fa fa-trash"></i>
                                            </a>
                                        </td>
                                        @endif
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
