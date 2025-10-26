@extends('layout.main')

@section('breadcrumb')

    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{{ trans('defects.defect-category') }}}</li>
    </ol>

@endsection

@section('content')

<div class="row">
    <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
        <h1 class="page-title txt-color-blueDark">
            <i class="glyphicon glyphicon-wrench"></i>&nbsp;&nbsp;{{{ trans('defects.defect-category') }}}
        </h1>
    </div>

    <div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">
    	<a href="{{ route('defect-categories.create')}}">
	        <button id="createDefect" class="btn btn-primary btn-md pull-right header-btn">
	            <i class="fa fa-plus"></i> {{{ trans('defects.create-defect-category') }}}
	        </button>
        </a>
    </div>
</div>

<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <div class="jarviswidget ">
            <header>
                <h2> {{{ trans('defects.defect-category-listing') }}} </h2>
            </header>
            <div>
                <div class="widget-body no-padding">
                    <div class="table-responsive">
                        <table class="table table-hover" id="dt_basic">
                            <thead>
                                <tr>
                                    <th style="width:40px;">&nbsp;</th>
                                    <th class="hasinput">
                                        <input type="text" class="form-control" placeholder="Filter Category" />
                                    </th>
                                    <th>&nbsp;</th>
                                </tr>
                                <tr>
                                    <th style="width:40px;" class="text-middle">{{{ trans('defects.no') }}}</th>
                                    <th class="text-middle">{{{ trans('defects.category-name') }}}</th>
                                    <th class="text-center text-middle">{{{ trans('defects.defects') }}}</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                    $count = 0;
                                ?>
                                @foreach ($categories as $category)
                                <?php
                                    $countDefect = PCK\Defects\Defect::where("defect_category_id",$category->id)->count();
                                ?>
                                    <tr>
                                        <td class="text-center text-middle">
                                            {{{++$count}}}
                                        </td>
                                        <td class="text-middle">
                                           <a href="{{ route('defect-categories.edit', $category->id)}}">
                                           	    {{{$category->name}}}
                                           </a>
                                            <a href="{{{ route('defect-categories.delete', array($category->id)) }}}"
                                               class="pull-right btn btn-xs btn-danger"
                                               data-method="delete"
                                               data-csrf_token="{{{ csrf_token() }}}">
                                               <i class="fa fa-trash"></i>
                                            </a>
                                        </td>
                                        <td class="text-center text-middle">
                                           <a href="{{ route('defect-categories.defects', $category->id)}}">
                                           	{{{ trans('defects.view-defects') }}}&nbsp;&nbsp;
                                            <span class="badge bg-color-redLight inbox-badge">
                                                {{{$countDefect}}}
                                            </span>
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
<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <a href="{{ route('projects.defect-trade-mapping.index')}}">
            <button id="mapping" class="btn btn-primary btn-md header-btn">
                {{{trans('defects.mapping')}}}
            </button>
        </a>
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