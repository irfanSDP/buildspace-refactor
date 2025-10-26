@extends('layout.main')

@section('css')
    <link href="{{ asset('js/summernote-0.9.0-dist/summernote.min.css')}}" rel="stylesheet">
    <style>
        #letter_of_award_clause_comment_modal {
            color: #000;
        }

        #letter_of_award_clause_comment_modal .modal {
            overflow-y: initial;
        }

        #letter_of_award_clause_comment_modal .modal-body {
            max-height: 400px;
            overflow-y: auto;
        }

        #divCommentBox {
            background: #524F4F;
            position:sticky;
            top:0;
            width:100%;
            z-index:100;
            margin: 0 auto;
        }

        #txtComments {
            resize: none;
        }
    </style>
@endsection

@section('breadcrumb')
	<ol class="breadcrumb">
		<li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), []) }}</li>
		@if ($isTemplate)
            <li>{{ trans('letterOfAward.letterOfAward') }}</li>
            <li>{{ link_to_route('letterOfAward.templates.selection', trans('letterOfAward.listOfTemplates'), []) }}</li>
            <li>{{ link_to_route('letterOfAward.template.index', $templateName . '(' . trans('letterOfAward.template') . ')', [$templateId]) }}</li>
        @else
            <li>{{ link_to_route('projects.show', str_limit($project->title, 50), [$project->id]) }}</li>
            <li>{{ link_to_route('letterOfAward.index', trans('letterOfAward.letterOfAward'), [$project->id]) }}</li>
        @endif
        <li>{{ trans('letterOfAward.clauses') }}</li>
	</ol>

	@if(!$isTemplate)
		@include('projects.partials.project_status')
	@endif
@endsection

@section('content')
    <article class="col-sm-12">

        <div class="row">
            <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                <h1 class="page-title">
                    <i class="fa fa-lg fa-fw fa-list-ol"></i>
                    {{ trans('letterOfAward.clauses') }}
                </h1>
            </div>
        </div>

        <div class="jarviswidget well" id="wid-id-0">
            <div>
                <div class="widget-body">
                    @if ($canUserEditLetterOfAward)
                        <div class="row" style="margin: 0 10px;text-align:right;">
                            <button type="button" button_action="add_clause_top" class="btn btn-danger">
                                <span class="label bg-color-purple">
                                    &nbsp;<i class="fa fa-plus"></i>
                                </span>
                                &nbsp;
                                {{ trans('letterOfAward.addNewClause') }}
                            </button>
                        </div>
                    @endif
                    <div class="row bg-color-magenta rounded-less" style="margin: 10px">
                        <div class="row" style="margin: 10px">
                            <div class="dd scrollable" id="activeClauseList" style="min-width:100%;overflow-y:auto;">
                                <ol class="dd-list root-list">

                                </ol>
                            </div>
                        </div>
                    </div>
                    <footer class="row" style="margin: 0 10px;text-align:right;">
                        <a href="{{{ $indexRoute }}}" class="btn btn-default">{{ trans('forms.back') }}</a>
                        @if ($canUserEditLetterOfAward)
                            <button type="button" id="save_button" class="btn btn-primary"><i class="fa fa-save"></i> {{ trans('forms.save') }}</button>
                        @endif
                    </footer>
                </div>
            </div>
        </div>
        <div data-category="templates" hidden>
            <div data-type="clause">
                <li class="dd-item bg-color-purple rounded-ne rounded-sw">
                    <div class="dd-handle dd3-handle">&nbsp;</div>
                    <div class="dd3-content rounded-ne rounded-sw">
                        <table>
                            <tr>
                                <td class="fill-horizontal">
                                    <div class="clause-summernote" item_type="content"><div style="text-align: justify;"><br></div></div>
                                </td>
                                <td class="padded-left padded-right">
                                    <div class="checkbox" style="margin-left:2px;" title="{{ trans('letterOfAward.displayClauseNumbering') }}" data-toggle="tooltip" >
                                        <label>
                                            <input type="checkbox" class="checkbox style-0" name="display_numbering" {{{ !$canUserEditLetterOfAward ? 'disabled' : null }}}>
                                            <span></span>
                                        </label>
                                    </div>
                                </td>
                                <td style="margin:0">
                                    <div class="d-flex">
                                        @if ($canUserEditLetterOfAward)
                                            <button type="button" class="btn btn-xs btn-success" style="margin-right:2px;" button_action="add_clause_bottom" data-toggle="tooltip" data-placement="left" title="Add a new Clause"><i class="fa fa-plus"></i></button>
                                            <button type="button" class="btn btn-xs btn-danger" style="margin-right:2px;" button_action="delete_clause" data-toggle="tooltip" data-placement="left" title="Delete this Clause"><i class="fa fa-times"></i></button>
                                        @endif
                                        @if (!$isTemplate)
                                            @include('letter_of_award.partials.comments_modal', [
                                                'canAddComments' => $canUserCommentLetterOfAward,
                                            ])
                                            <button type="button" class="btn btn-xs btn-info" button_action="viewComments" title="View Comments"><i class="fa fa-comments"></i></button>
                                        @endif
                                            </div>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <ol class="dd-list root-list nested">
                    </ol>
                </li>
            </div>
        </div>
        <div class="modal fade">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-body">
                        <div class="dd" id="inactiveClauseList" style="height: 100%;">
                            <ol class="dd-list">
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </article>
@endsection

@section('js')
<script src="{{ asset('js/plugin/nestable-master/jquery.nestable.js') }}"></script>
<script src="{{ asset('js/summernote-0.9.0-dist/summernote.min.js')}}"></script>
<script src="{{ asset('js/datatables_all_plugins.min.js') }}"></script>
<script src="{{ asset('js/app/app.functions.js') }}"></script>
<script>
    $(document).ready(function () {
        function renderClauses() {
            $({{ $clauses }}).each(function (index, el) {
                buildClauseItems(el);
            });
        }
        
        function buildClauseItems(el) {
            @if(!$isTemplate)
                var unreadCommentsCountGroupedByClause = {{ $unreadCommentsCountGroupedByClause }};
            @endif
            
            var template = $('[data-category=templates] [data-type=clause] > li').clone();
            var isChild = el.parentId ? true : false;
            var hasChildren = el.children ? true : false;
            
            template.find('.clause-summernote').empty();
            template.find('.clause-summernote').append(el.contents);
            template.attr('data-id', el.id);
            template.attr('data-display-numbering', el.displayNumbering);
            template.find('[name=display_numbering]').prop('checked', el.displayNumbering);
            template.find('.nested').attr('data-nested-parent-id', el.id);
            template.find('[button_action=viewComments]').attr('data-id', el.id);

            @if(!$isTemplate)
                if(unreadCommentsCountGroupedByClause[el.id] !== 0) {
                    var viewCommentsButtonInnerHTML = template.find('[button_action=viewComments]').html();
                    template.find('[button_action=viewComments]').html(viewCommentsButtonInnerHTML + '&nbsp;&nbsp;<span class="badge bg-color-white inbox-badge">' + unreadCommentsCountGroupedByClause[el.id] + '</span>');
                }
            @endif

            if(isChild) {
                var parentNestedClauseList = $('#activeClauseList [data-id=' + el.parentId + '] [data-nested-parent-id=' + el.parentId + ']');
                parentNestedClauseList.append(template);
            } else {
                $('#activeClauseList > ol').append(template);
            }

            if(hasChildren) {
                $.each(el.children, function(index, childEl) {
                    buildClauseItems(childEl);
                });
            }
        }

        app_progressBar.toggle();
        renderClauses();

        @if (!$canUserEditLetterOfAward)
            $(".note-editable").attr("contenteditable","false")
        @endif

        app_progressBar.maxOut();
        app_progressBar.toggle();
        app_progressBar.reset();

        // disabled drag and drop nestable if LA is not editable
        @if ($canUserEditLetterOfAward)
            $(document).on('click', '.clause-summernote', function(e) {
                var container = $(this);
                $('.note-editor').remove();
                $('.clause-summernote:hidden').each(function( index ) {
                    $(this).show();
                });

                container.summernote({
                    placeholder: 'Enter Clause',
                    toolbar: [
                        ['style', ['bold', 'italic', 'underline', 'clear']],
                        ['insert', ['table', 'hr']],
                        ['color', ['color']],
                        ['para', ['style', 'ol', 'ul', 'paragraph', 'height']],
                        ['font', ['strikethrough', 'superscript', 'subscript']],
                        ['codeview', ['codeview']],
                        ['help', ['help']],
                        ['view', ['fullscreen']]
                    ],
                    disableDragAndDrop: true,
                    onKeyup: function(e) {
                        setTimeout(function(){
                            container.html(container.summernote('code'));
                        },200);
                    },
                    onPaste: function(e) {
                        setTimeout(function(){
                            container.html(container.summernote('code'));
                        },200);
                    }
                });
            });

            $('.dd').nestable({
                expandBtnHTML: '',
                maxDepth:3,
                includeContent: 'true',
                callback: function(l,e){
                    // l is the main container
                    // e is the element that was moved
                }
            });
        @endif

        $(document).on('click', '[button_action]', function() {
            var template = $('[data-category=templates] [data-type=clause] > li').clone();
            template.attr('data-display-numbering', true);
            template.find('[name=display_numbering]').prop('checked', true);
            template.find('[button_action=viewComments]').hide();

            var defaultEditTxt = "Click this section to start editing";

            switch( $(this).attr('button_action') ){
                case 'add_clause_top':
                    template.find('.clause-summernote').append(defaultEditTxt);
                    $('#activeClauseList > ol').prepend($(template));
                    break;
                case 'add_clause_bottom':
                    template.find('.clause-summernote').append(defaultEditTxt);
                    $(this).closest('li.dd-item').after($(template));
                    break;
            }
        });

        $(document).on('click', '[button_action=delete_clause]', function() {
            $(this).closest('li.dd-item').detach().appendTo('#inactiveClauseList > ol');
        });

        $(document).on('click', '[name=display_numbering]', function(e) {
            var clauseList = $(this).closest('[data-id]');
            var checkedState = $(this).prop('checked');
            clauseList.attr('data-display-numbering', checkedState);
        });

        $('#save_button').on('click', function() {
            app_progressBar.toggle();
            
            var inputDivs = $('#activeClauseList [item_type=content]');

            inputDivs.each(function(index){
                $(this).append('<div class="dd-content">' + $(this).summernote('code') + '</div>');
            });

            $.ajax({
                url: "{{{ $saveContentsRoute }}}",
                method: 'POST',
                data: {
                    _token: '{{{ csrf_token() }}}',
                    clauses: $('#activeClauseList').nestable('serialise'),
                    inactiveClauses: $('#inactiveClauseList').nestable('serialise'),
                    isTemplate: "{{{ $isTemplate ? 1 : 0 }}}",
                    projectId: "{{{ isset($project) ? $project->id : null }}}"
                },
                success: function(data){
                    if(data.success) {
                        app_progressBar.maxOut();
                        window.location.replace(data.url);
                        app_progressBar.maxOut();
                        app_progressBar.toggle();
                        app_progressBar.reset();
                    }
                }
            });
        });

        $('[button_action=viewComments]').on('click', function(e) {
            e.preventDefault();
            var letterOfAwardClauseId = $(this).data('id');
            $('#letter_of_award_clause_comment_modal').attr('data-id', letterOfAwardClauseId);
            $('#letter_of_award_clause_comment_modal').modal('show');
        });

        @if(!$isTemplate)
            $('#letter_of_award_clause_comment_modal').on('shown.bs.modal', function(e) {
                var letterOfAwardClauseId = this.getAttribute('data-id');
                var url = "{{ route('letterOfAward.clause.comments.get', [$project->id]) }}";

                $('#commentsTable').DataTable({
                    "sDom": "tpi",
                    "autoWidth" : false,
                    scrollCollapse: true,
                    "paging": true,
                    "iDisplayLength":10,
                    "bServerSide" : true,
                    "bDestroy": true,
                    "language": {
                        "infoFiltered": "",
                        "zeroRecords": "{{ trans('letterOfAward.noCommentsPosted') }}"
                    },
                    "sAjaxSource": url,
                    "fnServerParams": function ( aoData ) {
                        aoData.push( 
                            { name: 'letterOfAwardClauseId', value: letterOfAwardClauseId }
                        );
                    },
                    "aoColumnDefs": [
                    {
                        "aTargets": [ 0 ],
                        "orderable": false,
                        "mData": function ( source, type, val ) {
                            return source['comments'];
                        },
                        "sClass": "text-middle text-left"
                    },
                    {
                        "aTargets": [ 1 ],
                        "orderable": false,
                        "mData": function ( source, type, val ) {
                            return source['commentor'];
                        },
                        "sClass": "text-middle text-center squeeze"
                    },
                    {
                        "aTargets": [ 2 ],
                        "orderable": false,
                        "mData": function ( source, type, val ) {
                            return source['date'];
                        },
                        "sClass": "text-middle text-center squeeze"
                    },
                    ]
                });
            });

            $('#letter_of_award_clause_comment_modal').on('hidden.bs.modal', function(e) {
                var clauseId = e.target.getAttribute('data-id');
                var clause = $('.dd-list.root-list').find('[data-id=' + clauseId + ']');
                var viewCommentsButton = clause.find('[button_action=viewComments][data-id=' + clauseId + ']');
                viewCommentsButton.html('<i class="fa fa-comments"></i>');
            });

            $('#btnPostComment').on('click', function(e) {
                e.preventDefault();

                var that = this;
                var url = "{{ route('letterOfAward.clause.comments.save', [$project->id]) }}";
                var letterOfAwardClauseId = $('#letter_of_award_clause_comment_modal').attr('data-id');
                var comments = $('#txtComments').val();

                if(comments === "") {
                    return;
                }

                app_progressBar.toggle();
                $(that).addClass('disabled');

                $.ajax({
                    url: url,
                    method: 'POST',
                    data: {
                        _token: '{{{ csrf_token() }}}',
                        letterOfAwardClauseId: letterOfAwardClauseId,
                        comments: comments,
                    },
                    success: function (data) {
                        if(data.success) {
                            $('#txtComments').val('');
                            $('#commentsTable').DataTable().draw();
                            $(that).removeClass('disabled');
                            app_progressBar.maxOut();
                            app_progressBar.toggle();
                            app_progressBar.reset();
                        }
                    }
                });
            });
        @endif
    });
    
</script>
@endsection