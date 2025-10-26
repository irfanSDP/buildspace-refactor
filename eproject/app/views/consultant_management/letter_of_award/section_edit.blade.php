@extends('layout.main')

@section('css')
<style>
    .smart-form *, .smart-form :after, .smart-form :before { box-sizing: border-box;-moz-box-sizing: border-box;}
</style>
@endsection


@section('breadcrumb')
<ol class="breadcrumb">
    <li>{{ link_to_route('consultant.management.contracts.index', trans('navigation/mainnav.home')) }}</li>
    <li>{{ link_to_route('consultant.management.loa.index', $vendorCategoryRfp->vendorCategory->name.'::'.trans('general.letterOfAppointment'), [$vendorCategoryRfp->id]) }}</li>
    @if(isset($loa))
        @if($type=='letterhead')
        <li>{{{ trans('forms.edit') }}} {{{trans('letterOfAward.letterHead')}}}</li>
        @elseif($type=='signatory')
        <li>{{{ trans('forms.edit') }}} {{{trans('letterOfAward.signatory')}}}</li>
        @elseif($type=='clause')
        <li>{{{ trans('forms.edit') }}} {{{trans('letterOfAward.clauses')}}}</li>
        @endif
    @endif
</ol>
@endsection

@section('content')
<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <h1 class="page-title txt-color-blueDark">
            <i class="fa fa-file-code"></i> {{{ trans('general.letterOfAppointment') }}}
            @if($type == 'letterhead')
            :: {{{ trans('letterOfAward.letterHead') }}}
            @elseif($type == 'signatory')
            :: {{{ trans('letterOfAward.signatory') }}}
            @elseif($type == 'clause')
            :: {{{ trans('letterOfAward.clauses') }}}
            @endif
        </h1>
    </div>
</div>

<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <div class="jarviswidget ">
            <header>
                <h2>
                    {{{ trans('forms.edit') }}} {{{ $loa->short_title }}}
                    @if($type == 'letterhead')
                    :: {{{ trans('letterOfAward.letterHead') }}}
                    @elseif($type == 'signatory')
                    :: {{{ trans('letterOfAward.signatory') }}}
                    @elseif($type == 'clause')
                    :: {{{ trans('letterOfAward.clauses') }}}
                    @endif
                </h2>
            </header>
            <div>
                <div class="widget-body">
                    {{ Form::open(['route' => [$store, $vendorCategoryRfp->id], 'class' => 'smart-form']) }}
                    @if($type=='letterhead')
                    @include('consultant_management.letter_of_award.partials.forms.letterhead')
                    @elseif($type=='signatory')
                    @include('consultant_management.letter_of_award.partials.forms.signatory')
                    @elseif($type=='clause')
                    @include('consultant_management.letter_of_award.partials.forms.clause', ['isTemplate'=>false])
                    @endif
                    <footer>
                        {{ Form::hidden('id', $loa->id) }}
                        {{ link_to_route('consultant.management.loa.index', trans('forms.back'), [$vendorCategoryRfp->id], ['class' => 'btn btn-default']) }}
                        {{ Form::button('<i class="fa fa-save"></i> '.trans('forms.save'), ['type' => 'submit', 'class' => 'btn btn-primary', 'onclick'=>"return 0;"] )  }}
                    </footer>
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('js')
<script src="{{ asset('js/summernote/summernote.min.js') }}"></script>
@if($type=='clause')
<script src="{{ asset('js/plugin/nestable-master/jquery.nestable.js') }}"></script>
<script src="{{ asset('js/app/app.functions.js') }}"></script>
@endif
<script type="text/javascript">
$(document).ready(function () {
    @if($type == 'letterhead' or $type == 'signatory')
    $('#{{$type}}-txt').summernote({
        focus: true,
        disableResizeEditor: true,
        placeholder: "Content",
        toolbar: [
            ['style', ['bold', 'italic', 'underline', 'clear']],
            ['insert', ['link', 'picture', 'table', 'hr']],
            ['color', ['color']],
            ['para', ['style', 'ol', 'ul', 'paragraph', 'height']],
            ['font', ['strikethrough', 'superscript', 'subscript']],
            ['codeview', ['codeview']],
            ['help', ['help']],
            ['view', ['fullscreen']]
        ]
    });
    $('.note-statusbar').hide();//remove resize bar
    @elseif($type=='clause')

    app_progressBar.toggle();
    renderClauses();
    app_progressBar.maxOut();
    app_progressBar.toggle();
    app_progressBar.reset();

    $('.dd').nestable({
        expandBtnHTML: '',
        maxDepth:3,
        includeContent: 'true',
        callback: function(l,e){
            // l is the main container
            // e is the element that was moved
        }
    });

    $(document).on('click', '.clause-summernote', function(e) {
        var container = $(this);

        $('.clause-summernote:hidden').each(function( index ) {
            $(this).summernote('destroy');
            $(this).show();
        });

        container.summernote({
            focus: true,
            disableResizeEditor: true,
            placeholder: "Enter Clause",
            toolbar: [
                ['style', ['bold', 'italic', 'underline', 'clear']],
                ['insert', ['link', 'picture', 'table', 'hr']],
                ['color', ['color']],
                ['para', ['style', 'ol', 'ul', 'paragraph', 'height']],
                ['font', ['strikethrough', 'superscript', 'subscript']],
                ['codeview', ['codeview']],
                ['help', ['help']],
                ['view', ['fullscreen']]
            ],
            disableDragAndDrop: true,
            callbacks: {
                onChange: function(e) {
                    setTimeout(function(){
                        container.html(container.summernote('code'));
                    },200);
                },
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
            }
        });
        $('.note-statusbar').hide();//remove resize bar
    });

    $(document).on('click', '[button_action]', function() {
        var template = $('[data-category=templates] [data-type=clause] > li').clone();
        template.attr('data-display-numbering', true);
        template.find('[name=display_numbering]').prop('checked', true);
        
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
        var clauseList = $(e.target).closest('[data-id]');
        var checkedState = $(e.target).prop('checked');
        clauseList.attr('data-display-numbering', checkedState);
    });

    $("form").submit(function(e){
        app_progressBar.toggle();
        var form = $(this);

        var inputDivs = $('#activeClauseList [item_type=content]');
        inputDivs.each(function(index){
            $(this).append('<div class="dd-content" style="display:none;">' + $(this).html() + '</div>');
        });

        $.ajax({ 
            url   : form.attr('action'),
            type  : form.attr('method'),
            data: {
                _token: $("input[name='_token']").val(),
                id: $("input[name='id']").val(),
                clauses: $('#activeClauseList').nestable('serialise'),
                inactiveClauses: $('#inactiveClauseList').nestable('serialise'),
            },
            success: function(response){
                app_progressBar.maxOut();
                window.location.replace(response.url);
            }
        });
        
        return false;
    });
    @endif
});

@if($type=='clause')
function renderClauses() {
    $({{ $clauses }}).each(function (index, el) {
        buildClauseItems(el);
    });
}
        
function buildClauseItems(el) {
    var template = $('[data-category=templates] [data-type=clause] > li').clone();
    var isChild = el.parentId ? true : false;
    var hasChildren = el.children ? true : false;
    
    template.find('.clause-summernote').empty();
    template.find('.clause-summernote').append(el.content);
    template.attr('data-id', el.id);
    template.attr('data-display-numbering', el.displayNumbering);
    template.find('[name=display_numbering]').prop('checked', el.displayNumbering);
    template.find('.nested').attr('data-nested-parent-id', el.id);

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
@endif
</script>
@endsection
