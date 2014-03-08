/** @jsx React.DOM */
var LiveMatches = React.createClass({displayName: 'LiveMatches',
	loadMatches: function() {
		$.ajax({
			url: 'api/live', dataType: 'json',
			success: function(data) {
				if (data.matches) {
					this.setState({rows: data.matches});
				} else {
					console.log(data.error);
				}
				setTimeout(this.loadMatches, 5000);
			}.bind(this),
		});
	},
	getInitialState: function() {
		return {rows: []};
	},
	componentWillMount: function() {
		this.loadMatches();
	},
	render: function() {
		var matcheRows = this.state.rows.map(function (matchRow, index) {
			return MatchRow( {key:matchRow.id, index:index, data:matchRow} );
		});
		return (
			React.DOM.table( {id:"matches"}, 
			React.DOM.tr(null, 
				React.DOM.th(null),
				React.DOM.th(null, "Home"),
				React.DOM.th(null),
				React.DOM.th(null, "Away"),
				React.DOM.th(null, "Last update")
			),
			matcheRows
			)
		);
	}
});
var MatchRow = React.createClass({displayName: 'MatchRow',
	render: function() {
		return (
			React.DOM.tr( {className:"matchRow", title:"Click to view match information"}, 
				React.DOM.td(null, this.props.index+1,"."),
				React.DOM.td(null, this.props.data.home_name),
				React.DOM.td( {className:"goalCell"}, this.props.data.home_score,":",this.props.data.away_score),
				React.DOM.td(null, this.props.data.away_name),
				React.DOM.td(null, this.props.data.updated_at)
			)
		);
	}
});
React.renderComponent(
	LiveMatches(null ),
	document.getElementById('liveMatchesComponent')
);