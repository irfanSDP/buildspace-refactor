@extends('unauthenticated_forms.base')
@include('layout.partials.flash_message')
@section('content')
    <div class="fill" style="padding: 20px;">
        <div class="col col-lg-12 col-md-12 col-sm-12 col-xs-12 text-left pull-right">
            <section class="col col-xs-2 col-md-2 col-lg-2">
                <label class="label" style="color:black; font-size:110%;" id="languageLabel"></label>
                <label class="fill-horizontal">
                    <select id="displayLanguageSelect" class="select2 fill-horizontal" name="form_of_tender_template_id" id="letter_of_award_template_select">
                        @foreach ($languages as $id => $language)
                            <?php $selected = ($id == $userLocale) ? 'selected' : ''; ?>
                            <option value="{{{ $id }}}" {{{ $selected }}}>{{{ $language }}}</option>
                        @endforeach
                    </select>
                </label>
            </section>
        </div>
        <div class="col col-lg-12 col-md-12 col-sm-12 col-xs-12 text-center">
            <h3 id="pleaseConfirmInterestToTender"></h3>
            <hr/>
        </div>
        <div class="col col-lg-12 col-md-12 col-sm-12 col-xs-12" style="height: 100%">
            @if($user)
                <p id="currentlyLoggedInAs" class="text-center"></p>
                <p class="text-center"><span class="color-bootstrap-success">{{{ $user->name }}}</span></p>
                <hr/>
            @endif
            <div>
                @if(isset($project))
                    <table class="fill-horizontal">
                        <tr>
                            <th class="text-right">
                                <span id="project"></span>
                            </th>
                            <td class="text-left">
                                : [{{{ $project->reference }}}] {{{ $project->title }}}
                            </td>
                        </tr>
                        <tr>
                            <th class="text-right">
                                <span id="descriptionOfWork"></span>
                            </th>
                            <td class="text-left">
                                : {{{ $project->workCategory->name }}}
                            </td>
                        </tr>
                    </table>
                    <hr/>
                @endif
                {{ Form::open(array('route' => array($route, $key), 'id' => 'status-form')) }}
                <fieldset>
                    <div class="form-group text-center">
                        @foreach( $listOptions as $itemId => $listItem )
                            <button id="btn_{{{ $itemId }}}" type="submit" class="btn btn-default" data-action="respond" data-option-id="{{{ $itemId }}}" data-form-id="status-form" data-intercept='confirmation' data-intercept-timeout-duration='0' data-confirmation-message="{{ trans('general.areYouSure') }}" data-confirmation-with-remarks='remarks'>
                                {{{ $listItem }}}
                            </button>
                        @endforeach
                    </div>
                </fieldset>
                <input type="hidden" name="option" value="" />
                <input type="hidden" id="selectedLocale" name="selectedLocale" value="{{{ $userLocale }}}">
                {{ Form::close() }}
            </div>
        </div>
    </div>
    <hr/>
    <div class="second-block text-center text-bottom">
        <?php echo link_to('//forum.buildspace.my', 'Forum & Tutorials', array( 'id' => 'link-forum', 'target' => '_blank' ))?>
        ::
        <?php echo link_to('//eepurl.com/LhJNn', 'Subscribe to our newsletter', array( 'id' => 'link-newsletter', 'target' => '_blank' ))?>
    </div>
@endsection