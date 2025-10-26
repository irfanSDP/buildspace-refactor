@extends('layout.main')

@section('breadcrumb')

    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{ link_to_route('defect-categories', 'Defect Category', array()) }}</li>
        <li>{{{ trans('defects.defects') }}}</li>
    </ol>

@endsection

@section('content')

<div class="row">
    <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
        <h1 class="page-title txt-color-blueDark">
            <i class="glyphicon glyphicon-wrench"></i>&nbsp;&nbsp;{{{ trans('defects.defects') }}}
        </h1>
    </div>

    <div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">
    	<a href="{{ route('defect-categories.defects.create', $defectCategoryId)}}">
	        <button id="createDefect" class="btn btn-primary btn-md pull-right header-btn">
	            <i class="fa fa-plus"></i> {{{ trans('defects.addDefects') }}}
	        </button>
        </a>
    </div>
</div>

<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <div class="jarviswidget ">
            <header>
                <h2> {{{ trans('defects.defect-listing') }}} </h2>
            </header>
            <div>
                <div class="widget-body no-padding">
                    <div class="table-responsive">
                        <table class="table " id="dt_basic">
                            <thead>
                                <tr>
                                    <th>{{{ trans('defects.defects') }}}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($defects as $defect)
                                    <tr>
                                        <td>
                                           <a href="{{ route('defect-categories.defects.edit', 
                                                       array($defectCategoryId,$defect->id))}}">
                                            	{{{$defect->name}}}
                                           </a>
                                           <a href="{{{ route('defect-categories.defects.delete', 
                                                    array($defectCategoryId,$defect->id)) }}}"
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

@endsection