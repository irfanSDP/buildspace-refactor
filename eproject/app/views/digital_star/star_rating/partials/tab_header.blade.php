@if(\PCK\SystemModules\SystemModuleConfiguration::isEnabled(\PCK\SystemModules\SystemModuleConfiguration::MODULE_ID_DIGITAL_STAR))
    <li>
        <a href="#digital-star-rating" data-toggle="tab">{{ trans('digitalStar/digitalStar.digitalStarRating') }}</a>
    </li>
@endif