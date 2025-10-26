@extends('layout.main')

@section('breadcrumb')

    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{{ trans('weathers.weathers') }}}</li>
    </ol>

@endsection

@section('content')

<div class="row">
    <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
        <h1 class="page-title txt-color-blueDark">
            <i class="fa fa-lg fa-cloud"></i></i>&nbsp;&nbsp;{{{ trans('weathers.weathers') }}}
        </h1>
    </div>

    <div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">
    	<a href="{{ route('weathers.create')}}">
	        <button id="createDefect" class="btn btn-primary btn-md pull-right header-btn">
	            <i class="fa fa-plus"></i> {{{ trans('weathers.create_weather') }}}
	        </button>
        </a>
    </div>
</div>

<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <div class="jarviswidget ">
            <header>
                <h2> {{{ trans('weathers.weather_list') }}} </h2>
            </header>
            <div>
                <div class="widget-body no-padding">
                    <div class="table-responsive">
                        <table class="table table-hover" id="dt_basic">
                            <thead>
                                <tr>
                                    <th style="width:40px;">&nbsp;</th>
                                    <th class="hasinput">
                                        <input type="text" class="form-control" placeholder="Filter Weathers" />
                                    </th>
                                </tr>
                                <tr>
                                    <th class="text-center text-middle">{{{ trans('weathers.no') }}}</th>
                                    <th>{{{ trans('weathers.name') }}}</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                    $count = 0;
                                ?>
                                @foreach ($weathers as $weather)
                                    <tr>
                                        <td class="text-center text-middle">
                                            {{{++$count}}}
                                        </td>
                                        <td>
                                           <a href="{{{ route('weathers.edit', $weather->id)}}}">
                                           	    {{{$weather->name}}}
                                           </a>
                                            <a href="{{{ route('weathers.delete', array($weather->id)) }}}"
                                               class="pull-right btn btn-xs btn-danger"
                                               data-method="delete"
                                               data-csrf_token="{{{ csrf_token() }}}">
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