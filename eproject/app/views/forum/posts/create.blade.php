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
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <h1 class="page-title txt-color-blueDark">
                <i class="fa fa-thumbtack"></i> {{{ trans('forum.addAComment') }}}
            </h1>
        </div>
    </div>

    @if($thread->isTypePublic() || $thread->isTypePrivate())
        @include('layout.partials.flash_message_view', array('notificationLevel' => 'warning', 'message' => trans('forum.publicThreadTooltip')))
    @elseif($thread->isTypeSecret())
        @include('layout.partials.flash_message_view', array('notificationLevel' => 'warning', 'message' => trans('forum.secretThreadTooltip')))
    @endif

    @include('forum.threads.threadHead', array('readOnly' => true))

    <div class="row">
        <div class="col col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <div class="jarviswidget">
                <div>
                    <div class="widget-body">
                        @if($parentPost->id !== $thread->post->id)
                            <div class="padded-top">
                                @include('forum.posts.post', array('post' => $parentPost, 'singlePost' => true, 'readOnly' => true))
                            </div>
                        @endif
                        {{ Form::open(array('class' => 'smart-form', 'method' => 'POST', 'route' => array('form.threads.posts.store', $project->id, $parentPost->id))) }}
                            @include('forum.posts.formFields')

                            <footer>
                                {{ link_to_route('forum.threads.show', trans('forms.back'), array($project->id, $thread->id), array('class' => 'btn btn-default')) }}
                                {{ Form::button('<i class="fa fa-arrow-circle-up"></i> '.trans('forum.post'), ['type' => 'submit', 'class' => 'btn btn-success'] )  }}
                            </footer>
                        {{ Form::close() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('templates.log_table_modal', array('modalId' => 'threadUsersModal', 'title' => trans('forum.usersInThisGroup')))

@endsection
@section('js')
    <script>
        var threadUsersModalTable = new Tabulator("#threadUsersModal-table", {
            layout:"fitColumns",
            placeholder: "{{ trans('general.noMatchingResults') }}",
            height: 400,
            tooltips:true,
            resizableColumns:false,
            columns: [
                {title:"{{ trans('general.no') }}", cssClass:"text-center text-middle", width: 20, headerSort:false, formatter:"rownum"},
                {title:"{{ trans('general.name') }}", field: 'name', cssClass:"auto-width text-left"},
            ]
        });

        $(document).on('click', '[data-toggle=modal][data-target="#threadUsersModal"]', function(){
            threadUsersModalTable.setData($(this).data('ajax-url'));
        });
    </script>
@endsection