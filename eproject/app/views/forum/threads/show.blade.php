@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{ link_to_route('projects.show', \PCK\Helpers\StringOperations::shorten($project->title, 50), array($project->id)) }}</li>
        <li>{{ trans('forum.forum') }}</li>
        <li>
            @if(!$thread->isTypeSecret())
                {{ link_to_route('forum.threads', trans('forum.threads'), array($project->id)) }}
            @else
                {{ trans('forum.threads') }}
            @endif
        </li>
        <li>{{{ \PCK\Helpers\StringOperations::shorten($thread->title, 50) }}}</li>
    </ol>
@endsection

@section('content')

    <div class="row">
        <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
            <h1 class="page-title txt-color-blueDark">
                <i class="fa fa-thumbtack"></i> {{{ trans('forum.comments') }}}
            </h1>
        </div>

        <div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">
            @if ( ! $currentUser->hasCompanyProjectRole($thread->project, \PCK\ContractGroups\Types\Role::CONTRACTOR) )
                <div class="btn-group pull-right header-btn">
                    @include('forum.threads.actions_menu')
                </div>
            @endif
        </div>
    </div>

    @include('templates.log_modal', array('modalId' => 'privacySettingsLog', 'title' => trans('forum.privacySettingsLog'), 'log' => $thread->log))
    @include('templates.log_table_modal', array('modalId' => 'threadUsersModal', 'title' => trans('forum.usersInThisGroup')))

    <div class="row">
        <div class="col-sm-12">

            <div class="well" id="post-{{{ $thread->post->id }}}">
                <p class="color-grey-9">
                    {{{ trans('forum.postedByAt', array('name' => $thread->getPosterName(), 'date' => \Carbon\Carbon::parse($project->getProjectTimeZoneTime($thread->created_at))->format('d M Y g:i A'))) }}}
                    @if($thread->post->edited_at)
                        <a href="{{ route('form.threads.posts.edit.history', array($project->id, $thread->post->id)) }}" class="plain" data-toggle="tooltip" title="{{ trans('general.viewEditHistory') }}" data-placement="top">
                            {{{ trans('forum.editedAt', array('date' => \Carbon\Carbon::parse($project->getProjectTimeZoneTime($thread->post->edited_at))->format('d M Y g:i A'))) }}}
                        </a>
                    @endif

                    <span class="pull-right clickable">
                        @if($thread->isTypePublic())
                            <strong class="text-success" data-toggle="modal" data-tooltip data-target="#privacySettingsLog" data-toggle="tooltip" data-placement="left" title="{{{ trans('forum.publicThreadTooltip') }}}"><i class="fas fa-user"></i> {{ trans('forum.publicThread') }}</strong>
                        @elseif($thread->isTypePrivate())
                            <strong class="text-warning" data-toggle="modal" data-tooltip data-target="#privacySettingsLog" data-toggle="tooltip" data-placement="left" title="{{{ trans('forum.privateThreadTooltip') }}}"><i class="fas fa-user-circle"></i> {{ trans('forum.privateThread') }}</strong>
                        @elseif($thread->isTypeSecret())
                            <strong class="text-danger" data-toggle="modal" data-tooltip data-target="#threadUsersModal" data-toggle="tooltip" data-placement="left" title="{{{ trans('forum.secretThreadTooltip') }}}"><i class="fas fa-user-secret"></i> {{ trans('forum.secretThread') }}</strong>
                        @endif
                    </span>
                </p>

                <h1>
                    {{{ $thread->title }}}
                </h1>

                <div class="{{ isset($unreadPosts) ? ($unreadPosts->find($thread->post->id) ? 'forum-unread' : '') : '' }}">
                    <p class="font-15" data-category="content" data-id="post-{{{ $thread->post->id }}}">{{ nl2br($thread->post->getContent()) }}</p>
                    <p>
                        @include('forum.posts.attachments', array('post' => $thread->post))
                    </p>
                </div>

                <br/>

                <a href="{{ route('form.threads.posts.create', array($project->id, $thread->post->id)) }}" class="plain color-grey-7">
                    <strong>
                        <i class="fa fa-comment"></i> {{{ trans('forum.comment') }}}
                    </strong>
                </a>
                @if($thread->post->createdBy->id == $currentUser->id)
                    &nbsp;
                    <a href="{{ route('form.threads.posts.edit', array($project->id, $thread->post->id)) }}" class="plain color-grey-7">
                        <strong>
                            <i class="fa fa-edit"></i> {{{ trans('forms.edit') }}}
                        </strong>
                    </a>
                    &nbsp;
                    @if(!$currentUser->hasCompanyProjectRole($project, \PCK\ContractGroups\Types\Role::CONTRACTOR))
                        <a class="plain color-grey-7 clickable" data-toggle="modal" data-target="#assignUsersModal" data-url="{{ route('form.threads.posts.alert', array($project->id, $thread->post->id)) }}">
                            <strong>
                                <i class="fa fa-share"></i> {{{ trans('general.notify') }}}
                            </strong>
                        </a>
                    @else
                        <a class="plain color-grey-7 clickable" data-action="automated-alert" data-url="{{ route('form.threads.posts.automatedAlert', array($project->id, $thread->post->id)) }}">
                            <strong>
                                <i class="fa fa-share"></i> {{{ trans('general.notify') }}}
                            </strong>
                        </a>
                    @endif
                @endif

                <br/>

                @foreach($thread->post->children as $childPost)
                    <div class="padded-top padded-bottom">
                        @include('forum.posts.post', array('post' => $childPost))
                    </div>
                @endforeach

            </div>

        </div>
    </div>
    @include('form_partials.assign_users_modal', array('title' => trans('forum.sendNotifications'), 'saveButtonLabel' => trans('forms.send'), 'actionLabel' => trans('forms.sendTo'), 'userList' => $participants))
    <footer class="pull-right">
        <a href="{{ Checkpoint::previous() }}" class="btn btn-default">
            {{ trans('general.back') }}
        </a>
    </footer>

@endsection

@section('js')
    <script src="{{ asset('js/datatables_all_plugins.min.js') }}"></script>
    <script src="{{ asset('js/app/app.functions.js') }}"></script>
    <script>
        if(window.location.hash)
        {
            var postId = window.location.hash.replace('#', '');
            $('[data-category=content][data-id='+postId+']').addClass('text-warning');
        }

        var notificationRecipients = [];

        $("#assignUsersModal table thead th input[type=text]").on( 'keyup change', function () {
            table
                .column( $(this).parent().index()+':visible' )
                .search( this.value )
                .draw();
        });

        $('#assignUsersModal').on('change', 'input[type=checkbox][data-type=user-selection]', function(){
            if($(this).prop('checked'))
            {
                arrayFx.push(notificationRecipients, $(this).val());
            }
            else{
                arrayFx.remove(notificationRecipients, $(this).val());
            }
        });

        var notificationUrl;

        $('[data-toggle=modal]').on('click', function(){
            notificationUrl = $(this).data('url');
            notificationRecipients = [];
            checkboxFx.uncheckAll('[data-type=user-selection]');
        });

        var table = $('#assignUsersModal table').DataTable({
            "sDom": "t",
            "bPaginate": false,
            "language": {
                "emptyTable": "{{ trans('users.noUsers') }}"
            }
        });

        $('#assignUsersModal [data-action=submit]').on('click', function(){
            app_progressBar.show();
            app_progressBar.maxOut(1200, function(){
                $.ajax({
                    url: notificationUrl,
                    method: 'POST',
                    data: {
                        userIds: notificationRecipients,
                        _token: '{{{ csrf_token() }}}'
                    },
                    success: function (data) {
                        if (data['success']) {
                            var successMessage = "{{ trans('forum.notificationsSent') }}";
                            $.smallBox({
                                title : "{{ trans('general.success') }}",
                                content : "<i class='fa fa-check'></i> <i>" + successMessage + "</i>",
                                color : "#739E73",
                                sound: true,
                                iconSmall : "fa fa-paper-plane",
                                timeout : 5000
                            });
                        }
                        app_progressBar.hide();
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        // error
                    }
                });
            });
        });

        $('[data-action=automated-alert]').on('click', function(){
            notificationUrl = $(this).data('url');
            app_progressBar.show();
            app_progressBar.maxOut(1200, function(){
                $.ajax({
                    url: notificationUrl,
                    method: 'POST',
                    data: {
                        _token: '{{{ csrf_token() }}}'
                    },
                    success: function (data) {
                        if (data['success']) {
                            var successMessage = "{{ trans('forum.notificationsSent') }}";
                            $.smallBox({
                                title : "{{ trans('general.success') }}",
                                content : "<i class='fa fa-check'></i> <i>" + successMessage + "</i>",
                                color : "#739E73",
                                sound: true,
                                iconSmall : "fa fa-paper-plane",
                                timeout : 5000
                            });
                        }
                        app_progressBar.hide();
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        // error
                    }
                });
            });
        });

        var threadUsersModalTable = new Tabulator("#threadUsersModal-table", {
            ajaxURL: "{{ route('forum.threads.users', array($project->id, $thread->id)) }}",
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
    </script>
@endsection