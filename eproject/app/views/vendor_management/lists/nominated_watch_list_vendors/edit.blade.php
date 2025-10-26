@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{{ trans('vendorManagement.vendorManagement') }}}</li>
        <li>{{ link_to_route('vendorManagement.nominatedWatchList', trans('vendorManagement.nomineesForWatchList'), array()) }}</li>
        <li>{{{ $vendor->company->name }}}</li>
    </ol>
@endsection

@section('content')
<div class="row">
    <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
        <h1 class="page-title txt-color-blueDark">
            <i class="fa fa-users"></i> {{{ trans('forms.update') }}}
        </h1>
    </div>
</div>

<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <div class="jarviswidget ">
            <header>
                <h2>{{{ $vendor->company->name }}}</h2>
            </header>
            <div>
                <div class="widget-body">
                    {{ Form::model($vendor->score, array('route' => array('vendorManagement.nominatedWatchList.update', $vendor->id), 'class' => 'smart-form')) }}
                        <div class="row">
                            <section class="col col-xs-12 col-md-6 col-lg-6">
                                <label class="label">{{ trans('vendorManagement.deliberatedScore') }}:</label>
                                <label class="input {{{ $errors->has('deliberated_score') ? 'state-error' : null }}}">
                                    {{ Form::number('deliberated_score', Input::old('deliberated_score')) }}
                                </label>
                                {{ $errors->first('deliberated_score', '<em class="invalid">:message</em>') }}
                            </section>
                        </div>
                        <footer>
                            {{ link_to_route('vendorManagement.nominatedWatchList', trans('forms.back'), array(), array('class' => 'btn btn-default')) }}
                            {{ Form::button('<i class="fa fa-save"></i> '.trans('forms.save'), ['type' => 'submit', 'class' => 'btn btn-primary'] )  }}
                            {{ Form::button('<i class="fa fa-check"></i> '.trans('vendorManagement.pushToActiveVendorList'), ['type' => 'push-to-active-vendor-list', 'name' => 'submit', 'value' => 'to-active-vendor-list', 'class' => 'btn btn-success'] )  }}
                            {{ Form::button('<i class="fa fa-times"></i> '.trans('vendorManagement.pushToWatchList'), ['type' => 'push-to-watch-list', 'name' => 'submit', 'value' => 'push-to-watch-list', 'class' => 'btn btn-danger'] )  }}
                        </footer>
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@section('js')
    <script>
        $('button[type=push-to-active-vendor-list]').hide();
        $('button[type=push-to-watch-list]').hide();

        function showHidePushButtons(){
            $('button[type=push-to-active-vendor-list]').hide();
            $('button[type=push-to-watch-list]').hide();
            if($('input[name=deliberated_score]').val() > {{ $watchListNomineeToActiveVendorListThresholdScore }}){
                $('button[type=push-to-active-vendor-list]').show();
            }
            if($('input[name=deliberated_score]').val() < {{ $watchListNomineeToWatchListThresholdScore }}){
                $('button[type=push-to-watch-list]').show();
            }
        }

        showHidePushButtons();

        $('input[name=deliberated_score]').on('keyup', function(){
            showHidePushButtons();
        });
    </script>
@endsection