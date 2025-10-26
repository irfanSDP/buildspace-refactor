@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{ link_to_route('projects.show', \PCK\Helpers\StringOperations::shorten($project->title, 50), array($project->id)) }}</li>
        <li>{{ trans('forum.forum') }}</li>
        <li>{{ link_to_route('forum.threads', trans('forum.threads'), array($project->id)) }}</li>
        <li>{{{ \PCK\Helpers\StringOperations::shorten($thread->title, 50) }}}</li>
    </ol>
@endsection

@section('content')

    <div class="row">
        <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
            <h1 class="page-title txt-color-blueDark">
                <i class="fa fa-thumbtack"></i> {{{ trans('general.editHistory') }}}
            </h1>
        </div>

        <div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">
        </div>
    </div>

    @include('forum.threads.threadHead', array('readOnly' => true))

    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <div class="jarviswidget">
                <div>
                    <div class="widget-body">
                        <div class="padded-top padded-left padded-right">
                            <h3>{{ trans('general.editHistory') }}</h3>
                            <dl>
                                <dt>
                                    {{{ trans('forum.postedByAt', array('name' => $post->getPosterName(), 'date' => \Carbon\Carbon::parse($project->getProjectTimeZoneTime($post->created_at))->format('d M Y g:i A'))) }}}
                                </dt>
                                <dd>{{ nl2br($post->content) }}</dd>
                            </dl>
                            <hr/>
                        </div>
                        @foreach($post->revisions->reverse() as $revision)
                            <div class="padded-top padded-left padded-right">
                                <dl>
                                    <dt>
                                        {{{ trans('forum.postedByAt', array('name' => $revision->getPosterName(), 'date' => \Carbon\Carbon::parse($project->getProjectTimeZoneTime($revision->created_at))->format('d M Y g:i A'))) }}}
                                    </dt>
                                    <dd>{{ nl2br($revision->content) }}</dd>
                                </dl>
                                <hr/>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection