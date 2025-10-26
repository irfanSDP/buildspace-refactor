@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('home.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{ link_to_route('theme.settings.edit', 'Theme Settings', array()) }}</li>
    </ol>
@endsection

@section('css')
    <style>
        .img-container {
            display: flex;
            justify-content: center;
            align-items: center;
            width: 100%;
            max-width: 400px; /* Fixed maximum width */
            height: auto;
            aspect-ratio: 2 / 1; /* Maintains a 2:1 width-to-height ratio */
            margin-bottom: 15px;
            border: 1px dotted black;
        }
        .img-container img {
            max-width: 100%;
            max-height: 100%; /* Ensure the image does not exceed the container's height */
            object-fit: contain; /* Ensure the image fits within the container */
        }
    </style>
@endsection

@section('content')
    <?php
    $logo1 = array(
        'path' => '',
        'title' => 'logo',
        'width' => '200px',
        'height' => '200px'
    );
    $logo2 = array(
        'path' => '',
        'title' => 'logo',
        'width' => '200px',
        'height' => '200px'
    );
    $logoDir = '/upload/themes/theme-1/logo';

    $loginImg = array(
        'path' => '',
        'title' => 'login image',
        'border' => 'dashed',
        'width' => '200px',
        'height' => '200px'
    );
    $loginImgDir = '/upload/themes/theme-1/login_img';

    $defaultImg = array(
        'logo1' => '/img/company-logo-sample.png',
        'logo2' => '/img/buildspace-login-logo.png',
        'login_img' => '/img/login_img-sample.png'
    );

    if (! empty($themeSettings->logo1)) {
        if (file_exists(public_path($logoDir.'/'.$themeSettings->logo1))) {
            $logo1['path'] = asset($logoDir.'/'.$themeSettings->logo1);
        }
    }
    if (! empty($themeSettings->logo2)) {
        if (file_exists(public_path($logoDir.'/'.$themeSettings->logo2))) {
            $logo2['path'] = asset($logoDir.'/'.$themeSettings->logo2);
        }
    }
    if (! empty($themeSettings->bg_image)) {
        if (file_exists(public_path($loginImgDir.'/'.$themeSettings->bg_image))) {
            $loginImg['path'] = asset($loginImgDir.'/'.$themeSettings->bg_image);
        }
    }

    if (empty($logo1['path'])) {
        if (file_exists(public_path($defaultImg['logo1']))) {
            $logo1['path'] = asset($defaultImg['logo1']);
        }
    }
    if (empty($logo2['path'])) {
        if (file_exists(public_path($defaultImg['logo2']))) {
            $logo2['path'] = asset($defaultImg['logo2']);
        }
    }
    if (empty($loginImg['path'])) {
        if (file_exists(public_path($defaultImg['login_img']))) {
            $loginImg['path'] = asset($defaultImg['login_img']);
        }
    }
    ?>
    <div class="row">
        <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
            <h1 class="page-title txt-color-blueDark">
                <i class="fa fa-cogs"></i> {{ trans('themeSettings.pageTitle') }}
            </h1>
        </div>
    </div>

    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <article class="col-sm-12 col-md-12 col-lg-12" style="padding-top: 10px;">
                <div class="jarviswidget jarviswidget-sortable">
                    <header role="heading">
                        <span class="widget-icon"> <i class="fa fa-edit"></i> </span>
                        <h2>{{ trans('themeSettings.formTitle') }}</h2>
                    </header>

                    <div role="content">
                        <fieldset>
                            {{ Form::open(array('id' => 'themes-form', 'class' => 'smart-form', 'url' => route('theme.settings.update'), 'method' => 'post', 'files' => true)) }}
                                <div class="row">
                                    <section class="col col-xs-12 col-md-6 col-lg-4">
                                        <label class="label">{{{ trans('themeSettings.logo1') }}}:</label>
                                        <div class="img-container">
                                            <img src="{{{ $logo1['path'].'?v='.time() }}}">
                                        </div>
                                        {{ Form::file('logo1', array('accept' => 'image/png')) }}
                                        {{ $errors->first('logo1', '<em class="invalid">:message</em>') }}
                                    </section>
                                    <section class="col col-xs-12 col-md-6 col-lg-4">
                                        <label class="label">{{{ trans('themeSettings.logo2') }}}:</label>
                                        <div class="img-container">
                                            <img src="{{{ $logo2['path'].'?v='.time() }}}">
                                        </div>
                                        {{ Form::file('logo2', array('accept' => 'image/png')) }}
                                        {{ $errors->first('logo2', '<em class="invalid">:message</em>') }}
                                    </section>
                                    <section class="col col-xs-12 col-md-6 col-lg-4">
                                        <label class="label">{{{ trans('themeSettings.loginImg') }}}:</label>
                                        <div class="img-container">
                                            <img src="{{{ $loginImg['path'].'?v='.time() }}}">
                                        </div>
                                        {{ Form::file('login_img', array('accept' => 'image/png')) }}
                                        {{ $errors->first('login_img', '<em class="invalid">:message</em>') }}
                                    </section>
                                </div>

                                {{--<div class="row">
                                    <section class="col col-xs-12 col-md-6 col-lg-6">
                                        <label class="label">{{{ trans('themeSettings.themeColour') }}}:</label>
                                        <button type="button" name="theme_colour1" style="background-color:rgb(48, 106, 243); padding:10px; border-radius:15px; margin:7px;" ></button>
                                        <button type="button" name="theme_colour2" style="background-color:rgb(50, 233, 117); padding:10px; border-radius:15px; margin:7px;" ></button>
                                        <button type="button" name="theme_colour3" style="background-color:rgb(243, 56, 56); padding:10px; border-radius:15px; margin:7px;" ></button>
                                        <button type="button" name="theme_colour4" style="background-color:rgb(251, 50, 211); padding:10px; border-radius:15px; margin:7px;" ></button>
                                    </section>
                                </div>--}}
                                <div class="row">
                                    <section class="col col-xs-3 col-md-3 col-lg-3">
                                        <div class="">
                                            <button type="submit" class="btn btn-primary btn-md header-btn">
                                                <i class="fa fa-save"></i> {{ trans('forms.update') }}
                                            </button>
                                            <button type="button" id="reset-theme-images" class="btn btn-default btn-md header-btn" style="margin-left: 10px;">
                                                <i class="fa fa-undo"></i> {{ trans('forms.reset') }}
                                            </button>
                                        </div>
                                    </section>
                                </div>
                            {{ Form::close() }}
                        </fieldset>
                    </div>

                </div>
            </article>
        </div>
    </div>
@endsection

@section('js')
    <script>
        $(document).ready(function() {
            $('#reset-theme-images').on('click', function() {
                $.ajax({
                    url: '{{ route('theme.settings.reset-images') }}',
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            location.reload();
                        }
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        // error
                    }
                });
            });
        });
    </script>
@endsection