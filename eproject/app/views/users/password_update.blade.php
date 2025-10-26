@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{ trans('users.passwordUpdate') }}</li>
    </ol>
@endsection

@section('content')
    <div class="row">
        <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
            <h1 class="page-title txt-color-blueDark">
                <i class="fa fa-user-secret"></i> {{ trans('users.passwordUpdate') }}
            </h1>
        </div>
    </div>

    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <article class="col-sm-12 col-md-12 col-lg-12" style="padding-top: 10px;">
                <div class="jarviswidget jarviswidget-sortable">
                    <header role="heading">
                        <span class="widget-icon"> <i class="fa fa-user-secret"></i> </span>
                        <h2>{{ trans('users.passwordUpdate') }}</h2>
                    </header>

                    <!-- widget div-->
                    <div role="content">
                        <!-- widget content -->
                        <div class="widget-body no-padding">
                            {{ Form::model($user, array('class' => 'smart-form', 'method' => 'PUT')) }}
                            <fieldset>
                                @include('users.partials.passwordUpdateFields')
                            </fieldset>

                            <footer>
                                <button type="submit" class="btn btn-primary"><i class="fa fa-save" aria-hidden="true"></i> {{ trans('forms.save') }}</button>
                            </footer>
                            {{ Form::close() }}
                        </div>
                        <!-- end widget content -->

                    </div>
                    <!-- end widget div -->
                </div>
            </article>
        </div>
    </div>
@endsection

@section('js')
    <script>
        $('input[type=password]').first().focus();
    </script>
@endsection