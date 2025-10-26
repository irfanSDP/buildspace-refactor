@extends('unauthenticated_forms.base')

@section('content')
    <div class="fill" style="padding: 20px;">
        <div class="col col-lg-12 col-md-12 col-sm-12 col-xs-12 text-center">
            <h3 id="statusConfirmationIsSuccessful"></h3>
            <hr/>
        </div>
        <div class="col col-lg-12 col-md-12 col-sm-12 col-xs-12">
            @if($user = Confide::user())
                <p class="text-center" id="currentlyLoggedInAs"></p>
                <p class="text-center"><span class="color-bootstrap-success">{{{ $user->name }}}</span></p>
                <hr/>
            @endif
            @include('layout.partials.flash_message')
            @if($success)
                <div>
                    <h6 class="text-center text-middle">
                        <span id="statusConfirmationIsSuccessful"></span>
                    </h6>
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
                </div>
            @endif
        </div>
    </div>
    <hr/>
    <div class="second-block text-center text-bottom">
        <?php echo link_to('//forum.buildspace.my', 'Forum & Tutorials', array( 'id' => 'link-forum', 'target' => '_blank' ))?>
        ::
        <?php echo link_to('//eepurl.com/LhJNn', 'Subscribe to our newsletter', array( 'id' => 'link-newsletter', 'target' => '_blank' ))?>
    </div>
@endsection