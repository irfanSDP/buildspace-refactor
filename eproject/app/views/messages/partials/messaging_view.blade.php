<div id="inbox-content" class="inbox-body">
    <div style="float: left; padding: 10px 0 10px 14px; width: 180px;">

        @if ( $user->isEditor($project) )
            <a href="javascript:void(0);" id="compose-message" class="btn btn-info btn-block" style="margin-bottom: 18px;"><strong><i class="fa fa-pencil-alt"></i> {{ trans('messaging.compose') }}</strong></a>
        @endif

        <ul id="message_inbox_menu" class="inbox-menu-lg"> 
            <li class="active">
                <a class="inbox-load" data-conversations-type="{{{ PCK\Conversations\StatusType::INBOX }}}" href="javascript:void(0);">
                <i class="fa fa-folder-open"></i> {{ trans('messaging.inbox') }}<span id="inboxUnreadMessageCounter"></span>
                </a>
            </li>
            <li>
                <a class="sent-load" data-conversations-type="{{{ PCK\Conversations\StatusType::SENT }}}" href="javascript:void(0);">
                <i class="fa fa-paper-plane"></i> {{ trans('messaging.sent') }}<span id="sentUnreadMessageCounter"></span>
                </a>
            </li>

            @if ( $user->isEditor($project) )
                <li>
                    <a class="draft-load" data-conversations-type="{{{ PCK\Conversations\StatusType::DRAFT }}}" href="javascript:void(0);">
                    <i class="fa fa-edit"></i> {{ trans('messaging.draft') }}
                    </a>
                </li>
            @endif
        </ul>

        <div id="modalBoxContainer"></div>
    </div>

    <div id="messages-filter" class="table-wrap">
        <div class="form-group">
            <div class="col-md-4">
                <div class="icon-addon addon-sm">
                    <input type="text" placeholder="{{ trans('messaging.subject') }}" class="form-control" name="subject">
                    <label class="glyphicon glyphicon-search"></label>
                </div>
            </div>
            <div class="col-md-4">
                <div class="icon-addon addon-sm">
                    <input type="text" placeholder="{{ trans('messaging.author') }}" class="form-control" name="author">
                    <label class="glyphicon glyphicon-search"></label>
                </div>
            </div>
            <div class="col-md-4">
                <div class="icon-addon addon-sm">
                    <input type="text" placeholder="{{ trans('messaging.purposeOfIssue') }}" class="form-control" name="purpose_of_issue">
                    <label class="glyphicon glyphicon-search"></label>
                </div>
            </div>
        </div>
    </div>
    <div id="message-list" class="table-wrap custom-scroll animated fast fadeInRight">
        <!-- ajax will fill this area -->
        LOADING...
    </div>
</div>