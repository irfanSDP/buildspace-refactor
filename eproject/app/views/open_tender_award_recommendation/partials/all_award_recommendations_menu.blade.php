<div class="dropdown {{{ $classes ?? 'pull-right' }}}">
    <button data-type="action-button-menu" role="button" data-toggle="dropdown" class="btn btn-primary" data-target="#" href="javascript:;"> {{ trans('openTenderAwardRecommendation.allAwardRecommendations') }} <span class="caret"></span> </button>
    <ul data-type="action-button-menu-list" class="dropdown-menu" role="menu">
        @foreach($allAwardRecommendationsForProject as $record)
            <li>
                <?php $disabled = $record['disabled'] ? 'disabled' : null; ?>
                <a href="{{ $record['show_route'] }}" target="_self" class="btn btn-info btn-md btn-success {{ $disabled }}"><i class="fa fa-edit"></i> {{ $record['tender_name'] }}</a>
            </li>
        @endforeach
    </ul>
</div>