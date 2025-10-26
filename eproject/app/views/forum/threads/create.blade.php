@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{ link_to_route('projects.show', \PCK\Helpers\StringOperations::shorten($project->title, 50), array($project->id)) }}</li>
        <li>{{ trans('forum.forum') }}</li>
        <li>{{ link_to_route('forum.threads', trans('forum.threads'), array($project->id)) }}</li>
        <li>{{ trans('forum.startThread') }}</li>
    </ol>
@endsection

@section('content')

    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <h1 class="page-title txt-color-blueDark">
                <i class="fa fa-thumbtack"></i> {{{ trans('forum.threads') }}}
            </h1>
        </div>
    </div>

    @include('layout.partials.flash_message_view', array('notificationLevel' => 'warning', 'message' => trans('forum.publicThreadTooltip')))

    <div class="row">
        <div class="col col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <div class="jarviswidget ">
                <header>
                    <h2> {{{ trans('forum.startThread') }}} </h2>
                </header>
                <div>
                    <div class="widget-body">
                        {{ Form::open(array('class' => 'smart-form', 'method' => 'POST', 'route' => array('forum.threads.store', $project->id))) }}
                            @include('forum.threads.formFields')
                            @include('forum.posts.formFields')

                            <footer>
                                {{ link_to_route('forum.threads', trans('forms.back'), array($project->id), array('class' => 'btn btn-default')) }}
                                <button type="submit" class="btn btn-primary"><i class="fa fa-thumbtack"></i> {{ trans('forms.save') }}</button>
                            </footer>
                        {{ Form::close() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection