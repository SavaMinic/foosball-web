/** @jsx React.DOM */
var LiveMatches = React.createClass({
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
			return <MatchRow key={matchRow.id} index={index} data={matchRow} />;
		});
		return (
			<table id="matches">
			<tr>
				<th></th>
				<th>Home</th>
				<th></th>
				<th>Away</th>
				<th>Last update</th>
			</tr>
			{matcheRows}
			</table>
		);
	}
});
var MatchRow = React.createClass({
	render: function() {
		return (
			<tr className="matchRow" title="Click to view match information">
				<td>{this.props.index+1}.</td>
				<td>{this.props.data.home_name}</td>
				<td className="goalCell">{this.props.data.home_score}:{this.props.data.away_score}</td>
				<td>{this.props.data.away_name}</td>
				<td>{this.props.data.updated_at}</td>
			</tr>
		);
	}
});
React.renderComponent(
	<LiveMatches />,
	document.getElementById('liveMatchesComponent')
);