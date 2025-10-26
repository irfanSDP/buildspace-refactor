@extends('layout.main')

@section('css')
    <link href="{{ asset('js/summernote-master/dist/summernote.css') }}" rel="stylesheet">
@endsection

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{ trans('tenderDocumentFolders.tenderDocumentFolders') }}</li>
        <li>{{ link_to_route('tender_documents.template.directory', trans('general.templates')) }}</li>
        <li>{{ link_to_route('tender_documents.template.index', trans('general.template').' '.$root->serial_number, array($root->id)) }}</li>
        <li>{{ link_to_route('structured_documents.template.edit', $folder->name, array($folder->id, $document->id)) }}</li>
        <li>{{ trans('structuredDocuments.clauses') }}</li>
    </ol>
@endsection

@section('content')
    <article class="col-sm-12">

        <div class="row">
            <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                <h1 class="page-title">
                    <i class="fa fa-lg fa-fw fa-list-ol"></i>
                    {{ trans('structuredDocuments.clauses') }}
                </h1>
            </div>
        </div>
        <!-- Widget ID (each widget will need unique ID)-->
        <div class="jarviswidget well" id="wid-id-0">

            <!-- widget div-->
            <div>
                <!-- widget content -->
                <div class="widget-body">

                    <div class="row" style="margin: 0 10px">
                        <button type="button" class="btn btn-default rounded col-sm-12 col-md-12 col-lg-12" data-action="add_clause" data-level="sibling">
                            <span class="label bg-color-purple">
                                &nbsp;<i class="fa fa-plus"></i>
                            </span>
                            &nbsp;
                            {{ trans('formOfTender.addNewClause') }}
                        </button>
                    </div>
                    <div class="row bg-color-magenta rounded-less" style="margin: 10px">
                        <div class="row" style="margin: 10px">
                            <div class="dd scrollable" id="activeClauseList" style="min-width:100%;">
                                <ol class="dd-list root-list">
                                    @foreach($document->clauses as $clause)
                                        @include('structured_documents.clause', array('clause' => $clause, 'isTemplate' => true))
                                    @endforeach
                                </ol>
                            </div>
                        </div>
                    </div>
                    <footer class="row" style="margin: 0 10px;text-align:right;">
                        <a href="{{ route('structured_documents.template.edit', array($folder->id, $document->id)) }}" class="btn btn-default">{{ trans('forms.back') }}</a>
                        <button type="button" data-action="save" class="btn btn-primary"><i class="fa fa-save"></i> {{ trans('forms.save') }}</button>
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
                @include('structured_documents.clause', array('clause' => null, 'isTemplate' => true))
            </div>
        </div>
    </article>

@endsection

@section('js')
    <script src="{{ asset('js/plugin/nestable-master/jquery.nestable.js') }}"></script>
    <script src="{{ asset('js/summernote-master/dist/summernote.min.js') }}"></script>
    <script>

        function applySummernote(item){
            $(item).summernote({
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

        applyAll();

        function applyAll(){
            $('[data-toggle=tooltip]').tooltip();
            updateClauseNumbering();
        }

        function handleDeleteClause(listItem) {
            $(listItem).detach();
        }

        $('.dd').nestable({
            expandBtnHTML: '',
            maxDepth:2,
            includeContent: 'true',
            callback: function(l,e){
                // l is the main container
                // e is the element that was moved
                updateClauseNumbering();
            }
        });

        $(document).on('click', '[data-action=add_clause][data-level=sibling]', function(){
            var template = $('[data-category=templates] [data-type=clause] > li').clone();
            $('#activeClauseList > ol').prepend($(template));
            applySummernote($(template).find('.summernote'));
            applyAll();
        });

        $(document).on('click', '[data-action=add_clause][data-level=child]', function(){
            var template = $('[data-category=templates] [data-type=clause] > li').clone();
            $(this).closest('li.dd-item').after($(template));
            applySummernote($(template).find('.summernote'));
            applyAll();
        });

        $(document).on('click', '[data-action=delete_clause]', function(){
            deleteClause($(this).closest('li.dd-item'));
            applyAll();
        });

        function deleteClause(listItem){
            $(listItem).find('li.dd-item').each(function(){
                handleDeleteClause($(this));
            });
            handleDeleteClause(listItem);
        }

        $('[data-action=save]').on('click', function(){
            app_progressBar.toggle();

            // Add content to serialise.
            var inputDivs = $('[data-type=content]');
            inputDivs.each(function(index){
                $(this).append('<div class="dd-content">' + $(this).code() + '</div>');
            });

            $.ajax({
                url: '{{ route('structured_documents.template.clauses.update', array($folder->id, $document->id)) }}',
                method: 'POST',
                data: {
                    _token: '{{{ csrf_token() }}}',
                    clauses: $('#activeClauseList').nestable('serialise')
                },
                success: function(data){
                    app_progressBar.maxOut();
                    window.location.reload();
                }
            });

        });

        $('.note-editor .note-toolbar').hide();

        $(document).on('focus', '.note-editor .note-editable', function() {
            var toolbar = $(this).parent().children('.note-toolbar');

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

        $('#activeClauseList [data-category=editable-content]').on('click', function(){
            toggleEditableContentMode(this);
        });

        function toggleEditableContentMode(item)
        {
            var displayElement = $(item).find('[data-category=display]');
            displayElement.prop('hidden', true);

            var summernoteElement = $(item).find('[data-category=editor]');
            applySummernote(summernoteElement);
            summernoteElement.removeAttr('hidden');
            summernoteElement.parent().find('.note-editable').trigger('focus');
        }
    </script>
@endsection