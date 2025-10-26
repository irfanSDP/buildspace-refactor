@extends('layout.main')

@section('css')
    <link href="{{ asset('js/summernote-0.9.0-dist/summernote.min.css')}}" rel="stylesheet">
@endsection

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        @if($isTemplate)
            <li>{{ trans('formOfTender.formOfTender') }}</li>
            <li>{{ link_to_route('form_of_tender.template.selection', trans('formOfTender.listOfTemplates'), array()) }}</li>
            <li>{{ link_to_route('form_of_tender.template.edit', $templateName . ' (' . trans('formOfTender.template') . ')', array($templateId)) }}</li>
        @else
            <li>
                <a href="{{ route('projects.show', array($project->id)) }}">{{{ str_limit($project->title, 50) }}}</a>
            </li>
            <li>
                <a href="{{ route('projects.tender.index', array($project->id)) }}">{{ trans('formOfTender.tenders') }}</a>
            </li>
            <li>
                <a href="{{ route('projects.tender.show', array($project->id, $tender->id)) }}">{{{ str_limit($tender->current_tender_name, 50) }}}</a>
            </li>
            <li><a href="{{{ $backRoute }}}">{{ trans('formOfTender.formOfTender') }}</a></li>
        @endif
        <li>{{ trans('formOfTender.clauses') }}</li>
    </ol>
@endsection

@section('content')
    <?php
        $disabled = ( isset($editable) && (!$editable) );
        if($isTemplate)
        {
            $routeToTenderAlternativesEdit = route('form_of_tender.tenderAlternatives.template.edit', [$templateId]);
        }
        else
        {
            $routeToTenderAlternativesEdit = route('form_of_tender.tenderAlternatives.edit', array($project->id, $tender->id));
        }
    ?>
    <article class="col-sm-12">

        <div class="row">
            <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                <h1 class="page-title">
                    <i class="fa fa-lg fa-fw fa-list-ol"></i>
                    @if(isset($isTemplate) && $isTemplate)
                        {{ trans('formOfTender.formOfTenderClauses(Template)') }}
                    @else
                        {{ trans('formOfTender.formOfTenderClauses') }}
                    @endif
                </h1>
            </div>
        </div>
        <!-- Widget ID (each widget will need unique ID)-->
        <div class="jarviswidget well" id="wid-id-0">

            <!-- widget div-->
            <div>
                <!-- widget content -->
                <div class="widget-body">
                    <div class="row text-right">
                        <button type="button" id="add_clause_button" class="btn btn-danger mb-4" {{{ $disabled ? 'disabled' : '' }}}>
                            <span class="label bg-color-purple">
                                &nbsp;<i class="fa fa-plus"></i>
                            </span>
                            &nbsp;
                            {{ trans('formOfTender.addNewClause') }}
                        </button>
                        <button type="button" id="add_tender_alternatives_button" class="btn btn-primary mb-4" {{{ $disabled ? 'disabled' : '' }}}>
                            <span class="label label-success">
                                &nbsp;<i class="fa fa-plus"></i>
                            </span>
                            &nbsp;
                            {{ trans('formOfTender.addTenderAlternatives') }}
                        </button>
                    </div>
                    <div class="row bg-color-magenta rounded-less mb-8">
                        <div class="row" style="margin: 10px">
                            <div class="dd scrollable" id="activeClauseList" style="min-width:100%;">
                                <ol class="dd-list root-list">
                                    @foreach($parentClauses as $parentClause)

                                        @if($parentClause instanceof PCK\FormOfTender\Clause)

                                        <li class="dd-item bg-color-purple rounded-ne rounded-sw" data-id="{{{ $parentClause->id }}}" {{{ $parentClause->is_editable ? 'data-is_editable="true"' : '' }}}>

                                            @include('form_of_tender.clauses.partials.nestable_clause_list_item_content', array('clause' => $parentClause, 'disabled' => $disabled))

                                            @if($parentClause->children()->count() > 0)
                                                <ol class="dd-list">
                                                    @foreach($parentClause->children->sortBy('sequence_number') as $subClause)
                                                        <li class="dd-item" data-id="{{{ $subClause->id }}}" {{{ $subClause->is_editable ? 'data-is_editable="true"' : '' }}}>
                                                            @include('form_of_tender.clauses.partials.nestable_clause_list_item_content', array('clause' => $subClause))
                                                        </li>
                                                    @endforeach
                                                </ol>
                                            @endif
                                        </li>

                                        @elseif($parentClause instanceof \PCK\FormOfTender\TenderAlternativesPosition)

                                            <li class="dd-item dd-nochildren" item_type="tender-alternatives-marker"><div class="dd-handle dd3-handle">&nbsp;</div>
                                                <div class="dd3-content rounded-ne rounded-se">
                                                    <table>
                                                        <tr>
                                                            <td class="text-top" data-category="clause-numbering">
                                                                <span class="label" data-type="label" style="margin-right:2px;"></span>
                                                            </td>
                                                            <td class="fill-horizontal">
                                                                <a href="{{{ $routeToTenderAlternativesEdit }}}" style="text-decoration: inherit; color: inherit">
                                                                    <span class="label label-success">{{ trans('formOfTender.tenderAlternatives') }}</span>
                                                                </a>
                                                                <button type="button" class="btn btn-xs btn-danger pull-right rounded" button_action="delete_tender-alternatives-marker" data-toggle="tooltip" data-placement="left" title="Delete this Clause" style="padding-bottom: 0"><i class="fa fa-times"></i></button>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </div>
                                            </li>

                                        @endif

                                    @endforeach
                                </ol>
                            </div>
                        </div>
                    </div>
                    <em class="required" data-category="form-error-message"></em>
                    <footer class="row text-right">
                        <a href="{{{ $backRoute }}}" class="btn btn-default">{{ trans('forms.back') }}</a>
                        <button type="button" id="save_button" class="btn btn-primary" {{{ $disabled ? 'disabled' : '' }}}><i class="fa fa-save"></i> {{ trans('forms.save') }}</button>
                    </footer>
                </div>
                <!-- end widget content -->

            </div>
            <!-- end widget div -->

        </div>
        <!-- end widget -->

        <!-- Template(s) -->
        <div data-category="templates" hidden>
            <div data-type="clause">
                <li class="dd-item bg-color-purple rounded-ne rounded-sw" data-is_editable="true">
                    <div class="dd-handle dd3-handle">&nbsp;</div>
                    <div class="dd3-content rounded-ne rounded-sw">
                        <table>
                            <tr>
                                <td class="text-top" data-category="clause-numbering">
                                    <span class="label" data-type="label">
                                    </span>
                                </td>
                                <td class="fill-horizontal">
                                    <div class="summernote" item_type="content"><div style="text-align: justify;"><br></div>
                                    </div>
                                </td>
                                <td class="padded-left padded-right">
                                    @if($isTemplate)
                                        <div class="checkbox" title="Allow clause to be editable" data-toggle="tooltip">
                                            <label>
                                                <input type="checkbox" class="checkbox style-0" name="is_editable" data-editable="true" checked>
                                                <span></span>
                                            </label>
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex">
                                        <button type="button" class="btn btn-xs btn-primary" style="margin-right:2px;" button_action="add_clause" data-toggle="tooltip" data-placement="left" title="{{{ trans('formOfTender.addNewClause') }}}"><i class="fa fa-plus"></i></button>
                                        <button type="button" class="btn btn-xs btn-danger" button_action="delete_clause" data-toggle="tooltip" data-placement="left" title="{{{ trans('formOfTender.deleteClause') }}}"><i class="fa fa-times"></i></button>
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </div>
                </li>
            </div>
        </div>
    </article>

    <div class="modal fade">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body">
                    <div class="dd" id="inactiveClauseList" style="height: 100%;">
                        <ol class="dd-list">
                        </ol>
                    </div>
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div>
@endsection

@section('js')
    <script src="{{ asset('js/plugin/nestable-master/jquery.nestable.js') }}"></script>
    <script src="{{ asset('js/summernote-0.9.0-dist/summernote.min.js')}}"></script>
    <script>
        $(document).ready(function() {
            function applySummernote(){
                $('#activeClauseList .summernote').summernote({
                    placeholder: 'Empty Clause',
                    toolbar: [
                        ['style', ['bold', 'italic', 'underline', 'clear']],
                        ['insert', ['picture', 'table', 'hr']],
                        ['color', ['color']],
                        ['para', ['style', 'ol', 'ul', 'paragraph', 'height']],
                        ['font', ['strikethrough', 'superscript', 'subscript']],
                        ['codeview', ['codeview']],
                        ['help', ['help']],
                        ['view', ['fullscreen']]
                    ]
                });
            }

            function applyAll(){
                applySummernote();
                $('[data-toggle=tooltip]').tooltip();
                updateClauseNumbering();
            }

            applyAll();

            $('.dd').nestable({
                expandBtnHTML: '',
                maxDepth:2,
                includeContent: 'true',
                callback: function(l,e){
                    // l is the main container
                    // e is the element that was moved
                    secedeTenderAlternativesMarkers();
                    updateClauseNumbering();
                }
            });

            function secedeTenderAlternativesMarkers() {
                var tenderAlternativesMarkers = $('[item_type=tender-alternatives-marker]');
                tenderAlternativesMarkers.each(function(index){
                    secede($(this));
                });
            }

            function secede(item) {
                if( ! $(item).parent().hasClass('root-list') )
                {
                    var parent = $(item).parent();

                    parent.closest('li.dd-item').after( $(item).detach() );
                }
            }

            function handleDeleteClause(listItem) {
                @if($isTemplate)
                    $(listItem).detach().appendTo('#inactiveClauseList > ol');
                @else
                    if(!$(listItem).data('is_editable'))
                    {
                        // Put back into active list.
                        secede(listItem);
                    }
                    else {
                        $(listItem).detach().appendTo('#inactiveClauseList > ol');
                    }
                @endif
            }

            function deleteClause(listItem){
                $(listItem).find('li.dd-item').each(function(){
                    handleDeleteClause($(this));
                });
                handleDeleteClause(listItem);
            }

            $(document).on('click', '[button_action]', function() {
                switch( $(this).attr('button_action') ){
                    case 'delete_clause':
                        deleteClause($(this).closest('li.dd-item'));
                        break;
                    case 'delete_tender-alternatives-marker':
                        $(this).closest('li').detach();
                        break;
                    case 'add_clause':
                        var template = $('[data-category=templates] [data-type=clause] > li').clone();
                        $(this).closest('li.dd-item').after($(template));
                        break;
                    default:
                        break;
                }
                applyAll();
            });

            $('#save_button').on('click', function(){
                app_progressBar.toggle();

                $('[data-category=form-error-message]').html('');

                secedeTenderAlternativesMarkers();

                // Add content to serialise.
                var inputDivs = $('[item_type=content]');
                inputDivs.each(function(index){
                    $(this).append('<div class="dd-content">' + $(this).summernote('code') + '</div>');
                });

                var tenderAlternativesMarkerPositions = [];

                var tenderAlternativesMarkers = $('[item_type=tender-alternatives-marker]');
                tenderAlternativesMarkers.each(function(index){
                    var position = $(this).index();
                    tenderAlternativesMarkerPositions.push(position);
                });

                $.ajax({
                    url: '{{{ $isTemplate ? route('form_of_tender.clauses.template.update', [$templateId]) : route('form_of_tender.clauses.update', array($project->id, $tender->id)) }}}',
                    method: 'POST',
                    data: {
                        _token: '{{{ csrf_token() }}}',
                        clauses: $('#activeClauseList').nestable('serialise'),
                        inactiveClauses: $('#inactiveClauseList').nestable('serialise'),
                        isTemplate: '{{{ $isTemplate ? $isTemplate : 0 }}}',
                        tenderAlternativesMarkerPositions: tenderAlternativesMarkerPositions
                    },
                    success: function(data){
                        app_progressBar.maxOut();
                        window.location.replace(data);
                    },
                    error: function(data)
                    {
                        app_progressBar.maxOut();

                        var errorMsg = data.responseJSON.errors[Object.keys(data.responseJSON.errors)[0]][0];

                        $('[data-category=form-error-message]').html(errorMsg);

                        app_progressBar.hide();
                    }
                });

            });

            $('#add_clause_button').on('click', function(){
                var template = $('[data-category=templates] [data-type=clause] > li').clone();
                $('#activeClauseList > ol').prepend($(template));
                applyAll();
            });

            var tenderAlternativesPositionMarkerCode =
                    '<li class="dd-item dd-nochildren" item_type="tender-alternatives-marker">' +
                    '<div class="dd-handle dd3-handle">&nbsp;</div>' +
                    '<div class="dd3-content bg-grey-f rounded-ne rounded-se">' +
                    '<table>' +
                    '<tr>' +
                    '<td class="text-top" data-category="clause-numbering">'+
                    '<span class="label" data-type="label">'+
                    '</span>'+
                    '</td>' +
                    '<td class="fill-horizontal">' +
                    '<a href="{{{ $routeToTenderAlternativesEdit }}}" style="text-decoration: inherit; color: inherit"><span class="label label-success">Tender Alternatives</span></a>' +
                    '<button type="button" class="btn btn-xs btn-danger pull-right rounded" button_action="delete_tender-alternatives-marker" data-toggle="tooltip" data-placement="left" title="Delete this Clause"><i class="fa fa-times"></i></button>' +
                    '</td>' +
                    '</tr>' +
                    '</table>' +
                    '</div>' +
                    '</li>';

            $('#add_tender_alternatives_button').on('click', function(){
                $('#activeClauseList > ol').prepend(tenderAlternativesPositionMarkerCode);
                applyAll();
            });

            $('.note-editor .note-toolbar').hide();

            $(document).on('focus', '.note-editor .note-editable', function() {
                const toolbar = $(this).parent().parent().children('.note-toolbar');

                if( ! (toolbar.hasClass('active')) ){
                    $('.note-editor .note-toolbar').removeClass('active').slideUp();

                    toolbar.addClass('active').slideDown();
                }
            });

            $(document).on('change', '.dd-item [name=is_editable]', function(){
                if($(this).prop('checked') == true)
                {
                    $(this).closest('li.dd-item').attr('data-is_editable', true);
                }
                else
                {
                    $(this).closest('li.dd-item').removeAttr('data-is_editable');
                }
            });

            function updateClauseNumbering()
            {
                var parentCount = 0;
                $('.root-list>li.dd-item').each(function(){
                    $(this).find('[data-category=clause-numbering]>[data-type=label]').first().empty().append(++parentCount).removeClass('bg-color-magenta').removeClass('bg-color-purple').addClass('bg-color-magenta');


                    var childCount = 0;
                    $(this).find('li.dd-item').each(function(){
                        $(this).find('[data-category=clause-numbering]>[data-type=label]').first().empty().append(++childCount).removeClass('bg-color-magenta').removeClass('bg-color-purple').addClass('bg-color-purple');
                    });
                });
            }
        });
    </script>
@endsection