/** @jsx React.DOM */
var StandingsTable = React.createClass({
	loadStandings: function() {
		$.ajax({
			url: 'api/standings', dataType: 'json',
			success: function(data) {
				if (data.teams) {
					this.setState({rows: data.teams});
				} else {
					console.log(data.error);
				}
				setTimeout(this.loadStandings, 15000);
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
			return <TeamRow key={teamRow.id} index={index} data={teamRow} />;
		});
		return (
			<table id="standings">
			<tr>
				<th colSpan="2">Teams</th>
				<th>G</th>
				<th>W</th>
				<th>L</th>
				<th>GS</th>
				<th>GC</th>
				<th>Diff</th>
			</tr>
			{teamRows}
			</table>
		);
	}
});
var TeamRow = React.createClass({
	render: function() {
		return (
			<tr className="teamRow" title="Click to view team information">
				<td>{this.props.index+1}.</td>
				<td>{this.props.data.name}</td>
				<td>{this.props.data.games_won + this.props.data.games_lost}</td>
				<td>{this.props.data.games_won}</td>
				<td>{this.props.data.games_lost}</td>
				<td>{this.props.data.goals_scored}</td>
				<td>{this.props.data.goals_conceded}</td>
				<td>{this.props.data.goals_scored - this.props.data.goals_conceded}</td>
			</tr>
		);
	}
});
React.renderComponent(
	<StandingsTable />,
	document.getElementById('standingsComponent')
);