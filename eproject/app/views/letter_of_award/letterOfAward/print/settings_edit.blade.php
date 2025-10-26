@extends('layout.main')

@section('css')
    <style>
        .jarviswidget fieldset {
            margin-bottom: 20px;
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
        <li>{{ trans('letterOfAward.printSettings') }}</li>
	</ol>

	@if(!$isTemplate)
		@include('projects.partials.project_status')
	@endif
@endsection

@section('content')
    <div class="jarviswidget" id="wid-id-6" data-widget-editbutton="false" data-widget-custombutton="false">
        <header>
            <span class="widget-icon"> <i class="fa fa-edit"></i> </span>
            <h2>{{ trans('letterOfAward.editPrintSettings') }}</h2>
        </header>
        <div>
            <div class="jarviswidget-editbox"></div>
            <div class="widget-body no-padding">
                <form action="{{{ $saveRoute }}}" method="POST" class="smart-form">
                    <input type="hidden" name="_token" id="_token" value="{{{ csrf_token() }}}">
                    <fieldset>
                        <div class="col col-3">
                            <label class="label">{{ trans('letterOfAward.headerFontSize') }}</label>
                            <label class="input">
                                <input type="number" name="headerFontSize" value="{{{ $printSettings->header_font_size }}}" min="8">
                            </label>
                        </div>
                        <div class="col col-3">
                            <label class="label">{{ trans('letterOfAward.clauseFontSize') }}</label>
                            <label class="input">
                                <input type="number" name="clauseFontSize" value="{{{ $printSettings->clause_font_size }}}"  min="8">
                            </label>
                        </div>
                        <div class="col col-3">
                            <label class="label">{{ trans('letterOfAward.headerSpacing') }}</label>
                            <label class="input">
                                <input type="number" name="headerSpacing" value="{{{ $printSettings->header_spacing }}}"  min="0">
                            </label>
                        </div>
                    </fieldset>
                    <fieldset>
                        <div class="col col-3">
                            <label class="label">{{ trans('letterOfAward.topMargin') }}</label>
                            <label class="input">
                                <input type="number" name="marginTop" value="{{{ $printSettings->margin_top }}}" min="0">
                            </label>
                        </div>
                        <div class="col col-3">
                            <label class="label">{{ trans('letterOfAward.bottomMargin') }}</label>
                            <label class="input">
                                <input type="number" name="marginBottom" value="{{{ $printSettings->margin_bottom }}}"  min="0">
                            </label>
                        </div>
                        <div class="col col-3">
                            <label class="label">{{ trans('letterOfAward.leftMargin') }}</label>
                            <label class="input">
                                <input type="number" name="marginLeft" value="{{{ $printSettings->margin_left }}}" min="0">
                            </label>
                        </div>
                        <div class="col col-3">
                            <label class="label">{{ trans('letterOfAward.rightMargin') }}</label>
                            <label class="input">
                                <input type="number" name="marginRight" value="{{{ $printSettings->margin_right }}}"  min="0">
                            </label>
                        </div>
                    </fieldset>
                    <footer>
                        <button type="submit" name="submit" class="btn btn-primary pull-left"><i class="fa fa-save"></i> {{ trans('forms.save') }}</button>
                        <a href="{{{ $indexRoute }}}" class="btn btn-default pull-left">{{ trans('forms.back') }}</a>
                    </footer>
                </form>
            </div>
        </div>
    </div>
@endsection