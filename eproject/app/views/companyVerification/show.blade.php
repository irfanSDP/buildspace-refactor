@extends('layout.main')

@section('breadcrumb')
	<ol class="breadcrumb">
		<li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
		<li>
			{{ link_to_route('companies', 'Companies', array()) }}
		</li>
		<li>{{{ $company->name }}}</li>
	</ol>
@endsection

@section('content')

<div class="row">
	<div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
		<h1 class="page-title txt-color-blueDark">
			<i class="fa fa-edit"></i> {{{ trans('companies.companyDetails') }}}
		</h1>
	</div>
</div>

<div class="row">
	<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
		<div class="jarviswidget">
			<header>
				<h2>{{{ trans('companies.companyDetails') }}}</h2>
			</header>
			<div>
				<div class="widget-body no-padding">
                    <fieldset class="padded">
                        @include('companies.partials.companyDetails')
                    </fieldset>
					{{ Form::model($company, array('id'=> 'company-form', 'class' => 'smart-form', 'method' => 'put')) }}
						<footer>
						    {{ link_to_route('companies.verification.index', trans('forms.back'), array(), array('class' => 'btn btn-default')) }}
                            <a href="{{{ route('companies.verification.delete', array($company->id)) }}}" class="btn btn-danger" data-method="delete" data-csrf_token="{{ csrf_token() }}">
                                <i class="fa fa-trash"></i>
                                {{{ trans('companyVerification.delete') }}}
							</a>
							<a href="{{{ route('companies.verify', array($company->id)) }}}" class="btn btn-success"><i class="fa fa-check"></i> {{{ trans('companyVerification.confirm') }}}</a>
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
    <script src="{{ asset('js/app/app.restfulDelete.js') }}"></script>
    <script src="{{ asset('js/app/app.countrySelect.js') }}"></script>
    <script>
        $(document).ready(function() {
            $('#company-form').validate({
                errorPlacement : function(error, element) {
                    error.insertAfter(element.parent());
                }
            });
            $('select').each(function(){
                $(this).prop('disabled', true);
            });

            $("select").select2();
        });
    </script>
@endsection