@extends('layout.main')

@section('breadcrumb')
	<ol class="breadcrumb">
		<li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>

		@if ( Confide::user()->isSuperAdmin() )
			<li>
				{{ link_to_route('companies', 'Companies', array()) }}
			</li>
		@endif

		<li>
			{{ link_to_route('companies.users', 'Users', array($company->id)) }}
		</li>
		<li>{{{ trans('users.addUser') }}}</li>
	</ol>
@endsection

@section('content')

	<div class="row">
		<div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
			<h1 class="page-title txt-color-blueDark">
				<i class="fa fa-edit"></i> {{{ trans('users.addUser') }}} ({{{ $company->name }}})
			</h1>
		</div>
	</div>

	@include('users.partials.userForm')

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