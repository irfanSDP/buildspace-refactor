@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>
            {{ link_to_route('companies', 'Companies', array()) }}
        </li>
        <li>
            {{ link_to_route('companies.edit', substr($company->name, 0, 30), array($company->id)) }}
        </li>
        <li>{{ trans('companies.contractorDetails') }}</li>
    </ol>
@endsection

@section('content')

    <div class="row">
        <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
            <h1 class="page-title txt-color-blueDark">
                <i class="fa fa-edit"></i> {{ trans('companies.editContractorDetails') }}
            </h1>
        </div>
    </div>

    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <div class="jarviswidget">
                <header>
                    <h2>{{ trans('forms.edit') }}</h2>
                </header>
                <div>
                    <div class="widget-body no-padding">

                        {{ Form::model($contractor, array('id'=> 'company-form', 'class' => 'smart-form', 'method' => 'put')) }}

                            @include('contractors.partials.contractorForm')
                            <footer>
                                {{ link_to_route('companies.edit', trans('forms.back'), array($company_id), array('class' => 'btn btn-default')) }}

                                {{ link_to_route('companies', 'Next time', array(), array('class' => 'btn btn-default')) }}

                                {{ Form::button('<i class="fa fa-fw fa-save"></i> '.trans('forms.save'), ['type' => 'submit', 'class' => 'btn btn-primary'] )  }}
                            </footer>

                        {{ Form::close() }}

                    </div>
                </div>
            </div>
        </div>
    </div>


@endsection

@section('js')
    <script src="{{ asset('js/plugin/jquery-validate/jquery.validate.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            $('#company-form').validate({
                errorPlacement : function(error, element) {
                    error.insertAfter(element.parent());
                }
            });
        });
    </script>
@endsection