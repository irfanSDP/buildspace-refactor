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
                <i class="fa fa-thumbtack"></i> {{{ trans('forum.editComment') }}}
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
                        @if($parentPost && $parentPost->id !== $thread->post->id)
                            <div class="padded-top">
                                @include('forum.posts.post', array('post' => $parentPost, 'singlePost' => true, 'readOnly' => true))
                            </div>
                        @endif
                        {{ Form::model($post, array('class' => 'smart-form', 'method' => 'PUT', 'route' => array('form.threads.posts.update', $project->id, $post->id))) }}
                            @include('forum.posts.formFields')

                            <footer>
                                {{ link_to_route('forum.threads.show', trans('forms.back'), array($project->id, $thread->id), array('class' => 'btn btn-default')) }}
                                {{ Form::button('<i class="fa fa-save"></i> '.trans('forms.save'), ['type' => 'submit', 'class' => 'btn btn-success'] )  }}
                            </footer>
                        {{ Form::close() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection