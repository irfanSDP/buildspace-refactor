@extends('layout.main')

@section('breadcrumb')

    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{ link_to_route('defect-categories', 'Defect Category', array()) }}</li>
        <li>{{{trans('defects.mapping')}}}</li>
    </ol>

@endsection

@section('content')


<style>
    div.scroll {
        width: 1000px;
        height: 1000px;
        overflow: scroll;
    }
</style>

{{ Form::open(array('route'=>'projects.defect-trade-mapping.store')) }}
    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <h1 class="page-title">
                <i class="fa fa-link"></i>&nbsp;&nbsp;{{{trans('defects.mapping')}}}
            </h1>
        </div>
    </div>

    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <div class="jarviswidget well">
                <div class="widget-body">
                    <div class="table-responsive">
                        <div class="scroll">
                        <table class="table table-hover">
                            <thead>
                            <tr>
                                <th style="width: 25%;" class="text-right">{{{trans('defects.trade')}}}</th>
                                <th style="width: auto;" class="text-center">{{{trans('defects.category')}}}</th>
                            </tr>
                            </thead>
                            <tbody>
                                @foreach($trades as $trade)
                                    <tr>
                                        <td class="text-middle text-right">
                                            <tag>{{{$trade->name}}}</tag>
                                        </td>
                                        <td class="text-middle text-right">
                                            <select name="category[{{{$trade->id}}}][]" class="select2 fill-horizontal" multiple>
                                                @foreach($categories as $category)
                                                    @if(\PCK\DefectCategoryTradeMapping\DefectCategoryPreDefinedLocationCode::recordExists($trade->id, $category->id))
                                                        <option value="{{{ $category->id }}}" selected>
                                                            {{{ $category->name }}}
                                                        </option>
                                                    @else
                                                        <option value="{{{ $category->id }}}">
                                                            {{{ $category->name }}}
                                                        </option>
                                                    @endif
                                                @endforeach
                                            </select>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        </div>
                        <footer class="pull-right">
                            <a href="{{ route('projects.index') }}" class="btn btn-default">
                                {{{ trans('forms.back') }}}
                            </a>
                            {{ Form::submit(trans('forms.save'), array('class' => 'btn btn-primary')) }}
                        </footer>
                    </div>
                </div>
            </div>
        </div>
    </div>

{{ Form::close() }}

@endsection

@section('js')

    <script src="{{ asset('js/app/app.restfulDelete.js') }}"></script>
    
@endsection