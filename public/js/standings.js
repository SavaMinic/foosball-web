/** @jsx React.DOM */
var StandingsTable = React.createClass({displayName: 'StandingsTable',
	loadStandings: function() {
		$.ajax({
			url: 'api/standings', dataType: 'json',
			success: function(data) {
				if (data.teams) {
					this.setState({rows: data.teams});
				} else {
					console.log(data.error);
				}
				setTimeout(this.loadStandings, 5000);
			}.bind(this),
		});
	},
	getInitialState: function() {
		return {rows: []};
	},
	componentWillMount: function() {
		this.loadStandings();
	},
	render: function() {
		var teamRows = this.state.rows.map(function (teamRow, index) {
			return TeamRow( {key:teamRow.id, index:index, data:teamRow} );
		});
		return (
			React.DOM.table( {id:"standings"}, 
			React.DOM.tr(null, 
				React.DOM.th( {colSpan:"2"}, "Teams"),
				React.DOM.th(null, "G"),
				React.DOM.th(null, "W"),
				React.DOM.th(null, "L"),
				React.DOM.th(null, "GS"),
				React.DOM.th(null, "GC"),
				React.DOM.th(null, "Diff")
			),
			teamRows
			)
		);
	}
});
var TeamRow = React.createClass({displayName: 'TeamRow',
	render: function() {
		return (
			React.DOM.tr( {className:"teamRow", title:"Click to view team information"}, 
				React.DOM.td(null, this.props.index+1,"."),
				React.DOM.td(null, this.props.data.name),
				React.DOM.td(null, this.props.data.games_won + this.props.data.games_lost),
				React.DOM.td(null, this.props.data.games_won),
				React.DOM.td(null, this.props.data.games_lost),
				React.DOM.td(null, this.props.data.goals_scored),
				React.DOM.td(null, this.props.data.goals_conceded),
				React.DOM.td(null, this.props.data.goals_scored - this.props.data.goals_conceded)
			)
		);
	}
});
React.renderComponent(
	StandingsTable(null ),
	document.getElementById('standingsComponent')
);