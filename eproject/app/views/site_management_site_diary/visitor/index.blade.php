@extends('layout.main')

@section('breadcrumb')

    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{ link_to_route('projects.show', str_limit($project->title, 50), array($project->id)) }}</li>
        <li>Site Diary</li>
    </ol>

@endsection

@section('content')

<div class="row">
    <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
        <h1 class="page-title txt-color-blueDark">
            <i class="fa fa-users"></i>&nbsp;&nbsp;Visitor
        </h1>
    </div>
        <div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">
            <a href="{{ route('site-management-site-diary-visitor.create',array($project->id,$siteDiaryId))}}">
                <button id="createForm" class="btn btn-primary btn-md pull-right header-btn">
                    <i class="fa fa-plus"></i>&nbsp;Add Visitor
                </button>
            </a>
        </div>
</div>

<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <div class="jarviswidget ">
            <header>
                <h2>Visitors</h2>
            </header>
            <div>
                <div class="widget-body no-padding">
                    <div class="table-responsive">
                        <table class="table " id="dt_basic">
                            <thead>
                                <tr>
                                    <th>&nbsp;</th>
                                    <th class="hasinput">
                                        <input type="text" class="form-control" placeholder="Filter Name" />
                                    </th>
                                    <th class="hasinput">
                                        <input type="text" class="form-control" placeholder="Filter Company" />
                                    </th>
                                    <th class="hasinput">
                                        <input type="text" class="form-control" placeholder="Filter Time In" />
                                    </th>
                                    <th class="hasinput">
                                        <input type="text" class="form-control" placeholder="Filter Time Out" />
                                    </th>
                                </tr>
                                <tr>
                                    <th>Number</th>
                                    <th>Name</th>
                                    <th>Company</th>
                                    <th>Time In</th>
                                    <th>Time Out</th>
                                    <th>Action</th>
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
                                        <td>
                                            <a href="{{{ route('site-management-site-diary-visitor.edit', 
                                                            array($project->id, $siteDiaryId, $record->id)) }}}">
                                                <i class="fa fa-edit"></i>
                                            </a>
                                            &nbsp;
                                            <a href="{{{ route('site-management-site-diary-visitor.delete', 
                                                    array($project->id,$siteDiaryId, $record->id)) }}}" data-method="delete" data-csrf_token="{{ csrf_token() }}">
                                                <i class="fa fa-trash"></i>
                                            </a>
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

@endsection

@section('js')

    <script src="{{ asset('js/app/app.restfulDelete.js') }}"></script>
    <script src="{{ asset('js/datatables_all_plugins.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            var otable = $('#dt_basic').DataTable({
                "sDom": "<'dt-toolbar'<'col-xs-12 col-sm-6 hidden-xs'f><'col-sm-6 col-xs-12 hidden-xs'<'toolbar'>>r>"+
                "t"+
                "<'dt-toolbar-footer'<'col-sm-6 col-xs-12 hidden-xs'i><'col-xs-12 col-sm-6'p>>",
                "autoWidth" : false
            });

            $("#dt_basic thead th input[type=text]").on( 'keyup change', function () {
                otable
                        .column( $(this).parent().index()+':visible' )
                        .search( this.value )
                        .draw();
            } );
        });
    </script>
    
@endsection