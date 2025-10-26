<?php $logAction = isset($logAction) ? $logAction : null ?>
<div class="row">
    <section class="col col-xs-12 col-md-12 col-lg-12">
        @if ( $log->isEmpty() )
            <p class="required">There are no log entries.</p>
        @else
            <ol style="padding: 0 0 0 20px;" reversed>
                @foreach ( $log as $logEntry )
                    <?php $elaboration = ($logEntry instanceof \PCK\Base\LoggableInterface) ? $logEntry->elaboration() : null; ?>
                    <?php $timestamp = isset($project) ? $project->getProjectTimeZoneTime($logEntry->created_at) : $logEntry->created_at; ?>
                    <?php $actionBy = !is_null($logEntry->actionBy) ? $logEntry->actionBy : $logEntry->user; ?>
                    <li>{{ \PCK\Helpers\Log::present($actionBy, $timestamp, $logAction, $elaboration) }}</li>
                @endforeach
            </ol>
        @endif
    </section>
</div>