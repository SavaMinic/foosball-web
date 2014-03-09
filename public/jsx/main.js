/** @jsx React.DOM */
var Foosball = (function() {
	function changeURL(url) {
		if (location.pathname == url) return;
		if(window.history) {
			window.history.pushState({}, '', url);
		} else {
			location.href = url;
		}
	}
	var tthis = {
		'showHome' : function() {
			$('#content').load('/content/home', function() {
				changeURL('/home');
				React.renderComponent(
					<StandingsTable />,
					$('#standingsComponent')[0]
				);
				React.renderComponent(
					<LiveMatches />,
					$('#liveMatchesComponent')[0]
				);
			});
		},
		'showTeam' : function(id) {
			$('#content').load('/content/team/' + id, function() {
				changeURL('/team/' + id);
				
			});
		},
		'loadContent' : function() {
			var path = location.pathname, matches;
			// match the show team url
			matches = path.match('/team/(\\d*)');
			if (matches && matches.length > 0) {
				tthis.showTeam(matches[1]);
				return;
			}
			// default match is home page
			tthis.showHome();
		}
	};
	window.onpopstate = function() {
		tthis.loadContent();
	}
	return tthis;
})();
$(function() {
	Foosball.loadContent();
	$('#content').on('click', '.teamRow', function() {
		Foosball.showTeam( $(this).data('id') );
	});
	$('#showHome').click(function() {
		Foosball.showHome();
		return false;
	});
});