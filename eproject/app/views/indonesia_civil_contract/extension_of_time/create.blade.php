@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{ link_to_route('projects.show', str_limit($project->title, 50), array($project->id)) }}</li>
        <li>{{ trans('extensionOfTime.extensionOfTime') }}</li>
        <li>{{ trans('extensionOfTime.issueNew') }}</li>
    </ol>

    @include('projects.partials.project_status')
@endsection

@section('content')
    <h1>{{ trans('extensionOfTime.issueNew') }}</h1>

    <div class="row">
        <!-- NEW COL START -->
        <article class="col-sm-12 col-md-12 col-lg-6">
            <!-- Widget ID (each widget will need unique ID)-->
            <div class="jarviswidget">
                <header>
                    <span class="widget-icon"> <i class="fa fa-edit"></i> </span>
                    <h2>{{ trans('extensionOfTime.issueNew') }}</h2>
                </header>

                <!-- widget div-->
                <div>
                    <!-- widget content -->
                    <div class="widget-body no-padding">
                        {{ Form::open(array('class' => 'smart-form')) }}
                            @include('indonesia_civil_contract.extension_of_time.partials.eot_form')

                            <footer>
                                @if ( $currentUser->getAssignedCompany($project)->id == $project->getSelectedContractor()->id )
                                    {{ Form::submit(trans('extensionOfTime.issue'), array('class' => 'btn btn-primary', 'name' => 'issue')) }}
                                @endif

                                {{ Form::submit(trans('forms.saveAsDraft'), array('class' => 'btn btn-default', 'name' => 'draft')) }}

                                {{ link_to_route('indonesiaCivilContract.extensionOfTime', trans('forms.cancel'), [$project->id], ['class' => 'btn btn-default']) }}
                            </footer>
                        {{ Form::close() }}
                    </div>
                    <!-- end widget content -->
                </div>
                <!-- end widget div -->
            </div>
            <!-- end widget -->
        </article>
        <!-- END COL -->
    </div>
@endsection