@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{{ trans('vendorManagement.vendorPerformanceEvaluation') }}}</li>
        <li>{{ link_to_route('vendorPerformanceEvaluation.templateForms', trans('forms.templateForms'), array()) }}</li>
        <li>{{{ $templateForm->weightedNode->name }}}</li>
        <li>{{{ trans('forms.approval') }}}</li>
    </ol>
@endsection

@section('content')
<div class="row">
    <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
        <h1 class="page-title txt-color-blueDark">
            <i class="fa fa-check"></i> {{{ trans('forms.approval') }}}
        </h1>
    </div>
</div>

<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <div class="jarviswidget ">
            <header>
                <h2>{{{ $templateForm->weightedNode->name }}}</h2>
            </header>
            <div>
                <div class="widget-body">
                    <div id="main-table"></div>
                    @if($templateForm->isDraft())
                    {{ Form::open(array('route' => array('vendorPerformanceEvaluation.templateForms.approve', $templateForm->id), 'id' => 'form')) }}
                    <footer class="pull-right">
                        <button type="submit" class="btn btn-primary" name="submit" value="submit">{{ trans('forms.finalize') }}</button>
                        <a href="{{ route('vendorPerformanceEvaluation.templateForms', array($templateForm->id)) }}" class="btn btn-default">{{ trans('general.back') }}</a>
                    </footer>
                    {{ Form::close() }}
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('js')
    <script>
        $(document).ready(function () {
            var mainTable = new Tabulator('#main-table', {
                dataTree: true,
                dataTreeStartExpanded:true,
                height:450,
                placeholder: "{{ trans('general.noRecordsFound') }}",
                data: {{ json_encode($data) }},
                layout:"fitColumns",
                columns:[
                    {title:"{{ trans('general.description') }}", field:"description", minWidth: 300, hozAlign:"left", headerSort:false},
                    {title:"{{ trans('vendorManagement.score') }}", field:"score", width: 150, cssClass:"text-center text-middle", headerSort:false},
                ],
            });
        });
    </script>
@endsection