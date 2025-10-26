<?php namespace PCK\Tenders;

trait SyncToBuildSpaceQueueChecker {

	/**
	 * Will check redis for pending queue, if available then don't allow global queue-ing
	 * for syncing contractor rates into BuildSpace
	 *
	 * @return bool
	 */
	public function canQueue()
	{
		$redis = \Redis::connection();

		$queues = $redis->lrange('queues:' . Tender::QUEUE_SYNC_TO_BS_TUBE_NAME, 0, - 1);

		if ( !empty( $queues ) )
		{
			return false;
		}

		return true;
	}

}