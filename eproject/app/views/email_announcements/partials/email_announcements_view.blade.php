<div id="email-announcements-contents" class="inbox-body">
    <div style="float: left; padding: 10px 0 10px 14px; width: 180px;">
        
        <a href="javascript:void(0);" id="compose-announcement-email" class="btn btn-info btn-block" style="margin-bottom: 18px;"><strong><i class="fa fa-pencil-alt"></i> {{ trans('messaging.compose') }}</strong></a>
        
        <ul id="email_inbox_menu" class="inbox-menu-lg">
            <li class="active">
                <a class="email-sent-load" data-conversations-type="{{{ PCK\EmailAnnouncement\EmailAnnouncement::SENT }}}" href="javascript:void(0);">
                    <i class="fa fa-paper-plane"></i> {{ trans('messaging.sent') }}
                    <span id="sentUnreadMessageCounter"></span>
                </a>
            </li>
            <li>
                <a class="email-draft-load" href="javascript:void(0);" data-conversations-type="{{{ PCK\EmailAnnouncement\EmailAnnouncement::DRAFT }}}">
                <i class="fa fa-edit"></i> {{ trans('messaging.draft') }}</a>
            </li>
            <li>
                <a href="javascript:void(0);" style="pointer-events:none;cursor:default;">&nbsp;</a>
            </li>
        </ul>

        <div id="emailAnnouncementsModalBoxContainer"></div>
    </div>

    <div id="email-announcement-filter" class="table-wrap">
        <div class="form-group">
            <div class="col-md-4">
                <div class="icon-addon addon-sm">
                    <input type="text" placeholder="{{ trans('messaging.subject') }}" class="form-control" name="subject">
                    <label class="glyphicon glyphicon-search"></label>
                </div>
            </div>
            <div class="col-md-4">
                <div class="icon-addon addon-sm">
                    <input type="text" placeholder="{{ trans('messaging.message') }}" class="form-control" name="message">
                    <label class="glyphicon glyphicon-search"></label>
                </div>
            </div>
            <div class="col-md-4">
                <div class="icon-addon addon-sm">
                    <input type="text" placeholder="{{ trans('messaging.author') }}" class="form-control" name="author">
                    <label class="glyphicon glyphicon-search"></label>
                </div>
            </div>
        </div>
    </div>
    <div id="email-announcements-list" class="table-wrap custom-scroll animated fast fadeInRight">
        <!-- ajax will fill this area -->
        LOADING...
    </div>
</div>