$(document).ready(function () {
	const hashHistoryUrlsCookieKey = 'eproject-hashedUrls';
	const hashHistoryHashesCookieKey = 'eproject-urlHashes';
	const urlBackQueryParam = 'isBack';
	const maxHashHistoryCount = 10;

	var currentLocation = location.hostname+location.pathname;

	function pageHashHistoryGetHistory(cookieKey)
	{
		var history = getCookie(cookieKey);

		if(history.length === 0)
		{
			history = [];
		}
		else{
			history = JSON.parse(history);
		}

		return history;
	}

	function pageHashHistoryAddEntry(url, hash)
	{
		var hashHistory = pageHashHistoryGetHistory(hashHistoryHashesCookieKey);
		var urlHistory = pageHashHistoryGetHistory(hashHistoryUrlsCookieKey);

		index = urlHistory.indexOf(url);

		if(index > -1)
		{
			hashHistory.splice(index, 1);
			urlHistory.splice(index, 1);
		}

		hashHistory.unshift(hash);
		urlHistory.unshift(url);

		hashHistory = hashHistory.slice(0, maxHashHistoryCount);
		urlHistory = urlHistory.slice(0, maxHashHistoryCount);

		setCookie(hashHistoryHashesCookieKey, JSON.stringify(hashHistory));
		setCookie(hashHistoryUrlsCookieKey, JSON.stringify(urlHistory));
	}

	var urlParams = new URLSearchParams(location.search);

	var urlHistory = pageHashHistoryGetHistory(hashHistoryUrlsCookieKey);

	if(urlHistory.includes(currentLocation) && urlParams.get(urlBackQueryParam))
	{
		index = urlHistory.indexOf(currentLocation);

		if(index > -1)
		{
			var hashHistory = pageHashHistoryGetHistory(hashHistoryHashesCookieKey);

			location.hash = hashHistory[index];
		}
	}
	if(location.hash)
	{
		pageHashHistoryAddEntry(currentLocation, location.hash);
	}

	addEventListener('hashchange', function() {
	  	pageHashHistoryAddEntry(currentLocation, location.hash);
	});
});