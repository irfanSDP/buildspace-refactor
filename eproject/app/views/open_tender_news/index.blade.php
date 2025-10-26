@extends('layout.main')

@section('breadcrumb')

    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{ trans('openTenderNews.news') }}</li>
    </ol>

@endsection

@section('content')
<?php
use Carbon\Carbon;
?>
<div class="row">
    <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
        <h1 class="page-title txt-color-blueDark">
            <i class="fa fa-lg fa-cogs"></i></i>&nbsp;&nbsp;{{ trans('openTenderNews.news') }}
        </h1>
    </div>

    <div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">
    	<a href="{{ route('open_tender_news.create')}}">
	        <button id="createDefect" class="btn btn-primary btn-md pull-right header-btn">
	            <i class="fa fa-plus"></i> {{ trans('openTenderNews.create_news') }}
	        </button>
        </a>
    </div>
</div>

<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <div class="jarviswidget ">
            <header>
                <h2> {{ trans('openTenderNews.news_list') }} </h2>
            </header>
            <div>
                <div class="widget-body no-padding">
                    <div class="table-responsive">
                        <table class="table table-hover" id="dt_basic">
                            <thead>
                                <tr>
                                    <th class="text-center text-middle">{{ trans('openTenderNews.no') }}</th>
                                    <th>
                                        {{ trans('openTenderNews.department') }}
                                        <input type="text" class="form-control" placeholder="Filter Department"/>
                                    </th>
                                    <th>
                                        {{ trans('openTenderNews.description') }}
                                        <input type="text" class="form-control" placeholder="Filter Description"/>
                                    </th>
                                    <th class="text-center text-middle">
                                        {{ trans('openTenderNews.status') }}
                                        <input type="text" class="form-control" placeholder="Filter Status" />
                                    </th>
                                    <th class="text-center text-middle">
                                        {{ trans('openTenderNews.display_date') }}
                                        <input type="text" class="form-control" placeholder="Filter Display Date" />
                                    </th>
                                    <th class="text-center text-middle">{{ trans('openTenderNews.action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                    $count = 0;
                                ?>
                                @foreach ($openTenderNews as $news)
                                    <?php
                                    $start_time =  Carbon::parse($news->start_time);
                                    $end_time =  Carbon::parse($news->end_time);

                                    $start_time = $start_time->format('d M');
                                    $end_time = $end_time->format('d M Y g.i A');
                                    ?>
                                    <tr>
                                        <td class="text-center text-middle">
                                            {{++$count}}
                                        </td>
                                        <td>
                                           <a>{{$news->subsidiary->name}}</a>
                                        </td>
                                        <td class="text-truncate" style="max-width: 250px;">
                                           <a>{{$news->description}}</a>
                                        </td>
                                        <td class="text-center text-middle">
                                            @if($news->status == 1)
                                                <a>{{ trans('openTenderNews.active') }}</a>
                                            @else
                                                <a>{{ trans('openTenderNews.deactive') }}</a>
                                            @endif
                                        </td>
                                        <td class="text-center text-middle">
                                           <a>{{$start_time}} - {{$end_time}} </a>
                                        </td>
                                        <td class="text-center text-middle" style="white-space: nowrap;">
                                            <a class="btn btn-xs btn-primary" href="{{ route('open_tender_news.edit', array($news->id)) }}" style="margin-right: 5px;"><i class="fa fa-pencil-alt"></i></a>
                                            <a href="{{ route('open_tender_news.delete', array($news->id)) }}" 
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