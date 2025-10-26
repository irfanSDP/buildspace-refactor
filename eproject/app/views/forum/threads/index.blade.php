@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{ link_to_route('projects.show', \PCK\Helpers\StringOperations::shorten($project->title, 50), array($project->id)) }}</li>
        <li>{{ trans('forum.forum') }}</li>
        <li>{{ trans('forum.threads') }}</li>
    </ol>
@endsection

@section('content')

    <div class="row">
        <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
            <h1 class="page-title txt-color-blueDark">
                <i class="fa fa-thumbtack"></i> {{{ trans('forum.threads') }}}
            </h1>
        </div>

        <div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">
            <a href="{{ route('forum.threads.create', array($project->id)) }}" class="btn btn-primary btn-md pull-right header-btn">
                <i class="fa fa-plus"></i> {{{ trans('forum.startThread') }}}
            </a>
        </div>
    </div>

    @if($threads->isEmpty())
        <div class="well text-center">
            {{ trans('forum.noThreads') }}
            <hr/>
            <a href="{{ route('forum.threads.create', array($project->id)) }}" class="btn btn-primary btn-md">
                <i class="fa fa-plus"></i> {{{ trans('forum.startThread') }}}
            </a>
        </div>
    @else
        <div class="well">
            <form class="form-inline" method="GET" id="searchForm">
                <div class="form-group">
                    <label>{{ trans('general.search') }}: </label>
                    <input name="search" class="form-control" type="text" placeholder="{{ trans('tables.filter') }}" autofocus="autofocus" value="{{{ $searchString }}}">
                </div>
                <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i></button>
                <button type="button" class="btn btn-default" data-action="clear-search">{{ trans('general.clearSearch') }}</button>
            </form>
        </div>
        @if(!empty($searchString))
            @if($filteredThreads->isEmpty())
                <div class="well">
                    {{ trans('general.noMatchingResults') }}
                </div>
            @else
                @foreach($filteredThreads as $thread)
                    @include('forum.threads.filteredResultView')
                @endforeach
            @endif
        @else
            @foreach($threads as $thread)
                @include('forum.threads.threadHead')
            @endforeach
            {{ $threads->links() }}
        @endif
    @endif

    @include('templates.log_table_modal', array('modalId' => 'threadUsersModal', 'title' => trans('forum.usersInThisGroup')))

@endsection

@section('js')
    <script>
        $('[data-action=clear-search]').on('click', function(){
            $('#searchForm').find('input[name=search]').val('');
            $('#searchForm').submit();
        });

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