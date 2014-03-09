/** @jsx React.DOM */
var LiveMatches = React.createClass({displayName: 'LiveMatches',
	loadMatches: function() {
		$.ajax({
			url: '/api/live', dataType: 'json',
			success: function(data) {
				if (data.matches) {
					this.setState({
						rows: data.matches,
						oldRows: this.state.rows
					});
				} else {
					console.log(data.error);
				}
				setTimeout(this.loadMatches, 5000);
			}.bind(this),
		});
	},
	getInitialState: function() {
		return {rows: [], oldRows: []};
	},
	componentWillMount: function() {
		this.loadMatches();
	},
	getMatchInOldRows: function(id) {
		var rows = this.state.oldRows;
		if (rows.length > 0) {
			for(var i=0, len=rows.length; i<len; i++) {
				if(rows[i].id == id) return rows[i];
			}
		}
		return null;
	},
	render: function() {
		var matcheRows = this.state.rows.map(function (matchRow, index) {
			var oldRow = this.getMatchInOldRows(matchRow.id);
			return MatchRow( {key:matchRow.id, index:index, data:matchRow, oldData:oldRow} );
		}.bind(this));
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
		var rowId = 'match' + this.props.data.id;
		var classes = React.addons.classSet({
			'matchRow': true,
			'updatedRow': this.props.oldData && (
					this.props.oldData.home_score != this.props.data.home_score
				||	this.props.oldData.away_score != this.props.data.away_score
			)
		});
		return (
			React.DOM.tr( {className:classes, id:rowId, title:"Click to view match information"}, 
				React.DOM.td(null, this.props.index+1,"."),
				React.DOM.td(null, this.props.data.home_name),
				React.DOM.td( {className:"goalCell"}, this.props.data.home_score,":",this.props.data.away_score),
				React.DOM.td(null, this.props.data.away_name),
				React.DOM.td(null, this.props.data.updated_at)
			)
		);
	}
});