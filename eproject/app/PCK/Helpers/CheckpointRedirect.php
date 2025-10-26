<?php namespace PCK\Helpers;

class CheckpointRedirect 
{
	CONST CHECKPOINT_SESSION_KEY = 'checkpointUrls';
	CONST QUERY_STRING_BACK_PARAMETER = 'isBack';

	public function reset()
	{
		\Session::put(self::CHECKPOINT_SESSION_KEY, array());
	}

	public function previous()
	{
		$urls = \Session::get(self::CHECKPOINT_SESSION_KEY);

		if( empty($urls) )
		{
			$url = \URL::previous();
		}
		else
		{
			if( $urls[0] === \Request::url() )
			{
				$url = $urls[1] ?? \URL::previous();
			}
			else
			{
				$url = $urls[0];
			}
		}

		$scheme = parse_url($url, PHP_URL_SCHEME);
		$host 	= parse_url($url, PHP_URL_HOST);
		$path 	= parse_url($url, PHP_URL_PATH);
		$query 	= parse_url($url, PHP_URL_QUERY);

		parse_str($query, $params);

		$params[self::QUERY_STRING_BACK_PARAMETER] = 1;

		$query = http_build_query( $params );

        return "{$scheme}://{$host}{$path}?{$query}";
	}

	public function process()
	{
		$urls = \Session::get(self::CHECKPOINT_SESSION_KEY) ?? array();

		$currentUrl = \Request::url();

		if( $keyOfFirstInstance = array_search($currentUrl, $urls) )
		{
		    $urls = array_slice($urls, $keyOfFirstInstance);

		    \Session::put(self::CHECKPOINT_SESSION_KEY, $urls);
		}
		else
		{
			$this->stackPush();
		}
	}

	protected function stackPop()
	{
		$urls = \Session::get(self::CHECKPOINT_SESSION_KEY);

		if( ! empty($urls) ) array_shift($urls);

        \Session::put(self::CHECKPOINT_SESSION_KEY, $urls);
	}

	protected function stackPush()
	{
		$urls = \Session::get(self::CHECKPOINT_SESSION_KEY);

		$url = \Request::url();

		if( empty($urls) )
		{
			$urls[0] = $url;
		}
		elseif( $urls[0] !== $url )
		{
			array_unshift($urls, $url);
		}

        \Session::put(self::CHECKPOINT_SESSION_KEY, $urls);
	}
}