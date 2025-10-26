<div class="form-group {{{ $errors->has('font_size') ? 'has-error' : '' }}}">
    <label for="font_size" class="col-sm-2 col-md-2 col-lg-2 control-label">{{ trans('structuredDocuments.fontSize') }}</label>
    <div class="col-sm-1 col-md-1 col-lg-1">
        {{ Form::text('font_size', Input::old('font_size'), array('class' => 'form-control', 'placeholder' => trans('structuredDocuments.fontSize'), 'autofocus')) }}
    </div>
    {{ $errors->first('font_size', '<em class="required">:message</em>') }}
</div>

<div class="form-group {{{ $errors->has('title') ? 'has-error' : '' }}}">
    <label for="title" class="col-sm-2 col-md-2 col-lg-2 control-label">{{ trans('structuredDocuments.title') }}</label><i class="fa fa-question-circle" data-toggle="tooltip" data-placement="right" title="{{{ trans('structuredDocuments.cantSeeTitleTooltip') }}}"></i>
    <div class="col-sm-4 col-md-4 col-lg-4">
        {{ Form::text('title', Input::old('title'), array('class' => 'form-control', 'placeholder' => trans('structuredDocuments.title'))) }}
    </div>
    {{ $errors->first('title', '<em class="required">:message</em>') }}
</div>

@if(!$document->is_template)
    <div class="form-group {{{ $errors->has('heading') ? 'has-error' : '' }}}">
        <label for="heading" class="col-sm-2 col-md-2 col-lg-2 control-label">{{ trans('structuredDocuments.heading') }}</label>
        <div class="col-sm-8 col-md-8 col-lg-8">
            {{ Form::textarea('heading', Input::old('heading'), array('class' => 'form-control', 'placeholder' => trans('structuredDocuments.heading'), 'rows' => 3)) }}
        </div>
        {{ $errors->first('heading', '<em class="required">:message</em>') }}
    </div>
@endif

<div class="form-group {{{ $errors->has('margin_top') ? 'has-error' : '' }}}">
    <label for="margin_top" class="col-sm-2 col-md-2 col-lg-2 control-label">{{ trans('structuredDocuments.marginTop') }}</label>
    <div class="col-sm-1 col-md-1 col-lg-1">
        {{ Form::text('margin_top', Input::old('margin_top'), array('class' => 'form-control', 'placeholder' => trans('structuredDocuments.marginTop'))) }}
    </div>
    {{ $errors->first('margin_top', '<em class="required">:message</em>') }}
</div>

<div class="form-group {{{ $errors->has('margin_bottom') ? 'has-error' : '' }}}">
    <label for="margin_bottom" class="col-sm-2 col-md-2 col-lg-2 control-label">{{ trans('structuredDocuments.marginBottom') }}</label>
    <div class="col-sm-1 col-md-1 col-lg-1">
        {{ Form::text('margin_bottom', Input::old('margin_bottom'), array('class' => 'form-control', 'placeholder' => trans('structuredDocuments.marginBottom'))) }}
    </div>
    {{ $errors->first('margin_bottom', '<em class="required">:message</em>') }}
</div>

<div class="form-group {{{ $errors->has('margin_right') ? 'has-error' : '' }}}">
    <label for="margin_right" class="col-sm-2 col-md-2 col-lg-2 control-label">{{ trans('structuredDocuments.marginRight') }}</label>
    <div class="col-sm-1 col-md-1 col-lg-1">
        {{ Form::text('margin_right', Input::old('margin_right'), array('class' => 'form-control', 'placeholder' => trans('structuredDocuments.marginRight'))) }}
    </div>
    {{ $errors->first('margin_right', '<em class="required">:message</em>') }}
</div>

<div class="form-group {{{ $errors->has('margin_left') ? 'has-error' : '' }}}">
    <label for="margin_left" class="col-sm-2 col-md-2 col-lg-2 control-label">{{ trans('structuredDocuments.marginLeft') }}</label>
    <div class="col-sm-1 col-md-1 col-lg-1">
        {{ Form::text('margin_left', Input::old('margin_left'), array('class' => 'form-control', 'placeholder' => trans('structuredDocuments.marginLeft'))) }}
    </div>
    {{ $errors->first('margin_left', '<em class="required">:message</em>') }}
</div>

<div class="form-group {{{ $errors->has('footer_text') ? 'has-error' : '' }}}">
    <label for="footer_text" class="col-sm-2 col-md-2 col-lg-2 control-label">{{ trans('structuredDocuments.footerText') }}</label>
    <div class="col-sm-4 col-md-4 col-lg-4">
        {{ Form::text('footer_text', Input::old('footer_text'), array('class' => 'form-control', 'placeholder' => trans('structuredDocuments.footerText'))) }}
    </div>
    {{ $errors->first('footer_text', '<em class="required">:message</em>') }}
</div>