@extends('layout.main')

@section('breadcrumb')

    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{ trans('openTenderBanners.banners') }}</li>
    </ol>

@endsection

@section('content')
<?php
use Carbon\Carbon;
?>
<div class="row">
    <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
        <h1 class="page-title txt-color-blueDark">
            <i class="fa fa-lg fa-cogs"></i></i>&nbsp;&nbsp;{{ trans('openTenderBanners.banners') }}
        </h1>
    </div>

    <div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">
    	<a href="{{ route('open_tender_banners.create')}}">
	        <button id="createDefect" class="btn btn-primary btn-md pull-right header-btn">
	            <i class="fa fa-plus"></i> {{ trans('openTenderBanners.create_banner') }}
	        </button>
        </a>
    </div>
</div>

<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <div class="jarviswidget ">
            <header>
                <h2> {{ trans('openTenderBanners.banner_list') }} </h2>
            </header>
            <div>
                <div class="widget-body no-padding">
                    <div class="table-responsive">
                        <table class="table table-hover" id="dt_basic">
                            <thead>
                                <tr>
                                    <th class="text-center text-middle">{{ trans('openTenderBanners.no') }}</th>
                                    <th class="text-center text-middle">
                                        {{ trans('openTenderBanners.image') }}
                                    </th>
                                    <th class="text-center text-middle">
                                        {{ trans('openTenderBanners.display_order') }}
                                    </th>
                                    <th class="text-center text-middle">
                                        {{ trans('openTenderBanners.display_date') }}
                                    </th>
                                    <th class="text-center text-middle">{{ trans('openTenderBanners.action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                    $count = 0;
                                ?>
                                @foreach ($openTenderBanners as $Banner)
                                    <?php
                                    $start_time =  Carbon::parse($Banner->start_time);
                                    $end_time =  Carbon::parse($Banner->end_time);

                                    $start_time = $start_time->format('d M');
                                    $end_time = $end_time->format('d M Y g.i A');
                                    ?>
                                    <tr>
                                        <td class="text-center text-middle">
                                            {{++$count}}
                                        </td>
                                        <td class="text-center text-middle">
                                           <a>{{$Banner->image}}</a>
                                        </td>
                                        <td class="text-center text-middle">
                                           <a>{{$Banner->display_order}}</a>
                                        </td>
                                        <td class="text-center text-middle">
                                           <a>{{$start_time}} - {{$end_time}} </a>
                                        </td>
                                        <td class="text-center text-middle" style="white-space: nowrap;">
                                            <a class="btn btn-xs btn-primary" href="{{ route('open_tender_banners.edit', array($Banner->id)) }}" style="margin-right: 5px;"><i class="fa fa-pencil-alt"></i></a>
                                            <a href="{{ route('open_tender_banners.delete', array($Banner->id)) }}" 
                                               class="btn btn-xs btn-danger" data-method="delete"
                                               data-csrf_token="{{ csrf_token() }}"><i class="fa fa-trash"></i>
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