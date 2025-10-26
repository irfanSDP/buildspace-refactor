@if(\PCK\Verifier\Verifier::isBeingVerified($object))
    <div class="row">
        <section class="col col-xs-12 col-md-12 col-lg-12">
            <label class="label">{{{ trans('verifiers.currentVerifier') }}} :</label>
            {{{ \PCK\Verifier\Verifier::getCurrentVerifier($object)->name }}}
        </section>
    </div>
@else
    @if(($log = \PCK\Verifier\Verifier::getLog($object)) && ($log->count() > 0))
        <div class="row">
            <section class="col col-xs-12 col-md-12 col-lg-12">
                <label class="label">{{{ trans('verifiers.verifierLog') }}} :</label>
                <ol style="padding-left: 20px;">
                    @foreach($log as $logEntry)
                        <li>
                            @if($logEntry->approved)
                                <span class="text-success">
                            {{ trans('verifiers.approved') }}
                        </span>
                            @else
                                <span class="text-danger">
                            {{ trans('verifiers.rejected') }}
                        </span>
                            @endif
                            by
                    <span class="text-info">
                        {{{ $logEntry->verifier->name }}}
                    </span>
                            at
                    <span class="text-success">
                        <?php
                            $logUpdatedAt = $logEntry->updated_at;
                            if(isset($project)) $logUpdatedAt = $project->getProjectTimeZoneTime($logEntry->updated_at);
                        ?>
                        {{{ \Carbon\Carbon::parse($logUpdatedAt)->format(Config::get('dates.created_and_updated_at_formatting')) }}}
                    </span>
                        </li>
                    @endforeach
                </ol>
            </section>
        </div>
    @endif
@endif