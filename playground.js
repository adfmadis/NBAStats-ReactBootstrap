console.log("playground.jsx run");

var Navbar = ReactBootstrap.Navbar,
    FormGroup = ReactBootstrap.FormGroup,
    FormControl = ReactBootstrap.FormControl,
    Button = ReactBootstrap.Button,
    DropdownButton = ReactBootstrap.DropdownButton;
    MenuItem = ReactBootstrap.MenuItem;
    SplitButton = ReactBootstrap.SplitButton;
    Dropdown = ReactBootstrap.Dropdown
    ButtonToolbar = ReactBootstrap.ButtonToolbar
    Checkbox = ReactBootstrap.Checkbox;

var isTeamUpdated = false; // used in updatePositions to decide what to execute, changed in updateTeam

function updateTeam(eventKey) {
  isTeamUpdated = true;
  var team = eventKey;
  teamData = []; // reset
  console.log("option selected: " + team);
  for (var playernum = 0; playernum < queryData.length; playernum++) {
    var player = queryData[playernum];
    // a player's team is always first element in player array
    if (team === player[0]) {
      teamData.push(player);
    } else {
      // queryData is sorted in orer by team, so unnecessary to finish looping through players once all added
      if (teamData.length > 0) break;
    } // else
  } // for

  // set up teamPosData which is used for visualization;
  updatePositions(team);

  // add to visualzition header to include team name
  var newtext = "2015-2016 Player Stats"+ ", " + team;
  // remove and re-add header text
  d3.select("#headertext").remove();
  header = svgItem.append("text")
    .attr("id","headertext")
    .attr("dx", 50). attr("dy",48)
    .text(newtext)
    .style("font-size",23)
    .style("font-weight","bold");

} // updateTeam

function updatePositions(team) {
  var positions = [];
  teamPosData = []; // reset
  if (document.getElementById("pg").checked) positions.push("PG");
  if (document.getElementById("sg").checked) positions.push("SG");
  if (document.getElementById("sf").checked) positions.push("SF");
  if (document.getElementById("pf").checked) positions.push("PF");
  if (document.getElementById("c").checked) positions.push("C");

  if (positions.length === 0) {
    positions.push("PG");
    positions.push("SG");
    positions.push("SF");
    positions.push("PF");
    positions.push("C");
  } // if

  // add data to teamPosData based on positions array
  for (var playernum = 0; playernum < teamData.length; playernum++) {
    var player = teamData[playernum];
    var pos = player[2], secpos = player[3];
    // add player to teamPosData if position is selected
    for (var posnum = 0; posnum < positions.length; posnum++) {
      if (pos === positions[posnum] || secpos === positions[posnum]) {
          teamPosData.push(player); break;
      } // if

    } // for
  } // for

  // debugging
  var positiontext = "Positions are: ";
  for (var pos = 0; pos < positions.length; pos++) {
    positiontext += positions[pos];
    positiontext += ", ";
  } // for
  console.log(positiontext);
  console.log("Players are: ");
  console.log(teamPosData);

  // call updateVisual to change visualization-area
  if (isTeamUpdated) updateVisual(header.text());


} // updatePositions

const dropdownInstance = (
    <DropdownButton bsStyle="success" title="Select Team" className="green-button team-dropdown">
      <MenuItem className="dropdown-item" eventKey="Atlanta Hawks" onSelect={updateTeam}>Atlanta Hawks</MenuItem>
      <MenuItem className="dropdown-item" eventKey="Boston Celtics" onSelect={updateTeam}>Boston Celtics</MenuItem>
      <MenuItem className="dropdown-item" eventKey="Brooklyn Nets" onSelect={updateTeam}>Brooklyn Nets</MenuItem>
      <MenuItem className="dropdown-item" eventKey="Charlotte Hornets" onSelect={updateTeam}>Charlotte Hornets</MenuItem>
      <MenuItem className="dropdown-item" eventKey="Chicago Bulls" onSelect={updateTeam}>Chicago Bulls</MenuItem>
      <MenuItem className="dropdown-item" eventKey="Cleveland Cavaliers" onSelect={updateTeam}>Cleveland Cavaliers</MenuItem>
      <MenuItem className="dropdown-item" eventKey="Dallas Mavericks" onSelect={updateTeam}>Dallas Mavericks</MenuItem>
      <MenuItem className="dropdown-item" eventKey="Denver Nuggets" onSelect={updateTeam}>Denver Nuggets</MenuItem>
      <MenuItem className="dropdown-item" eventKey="Detroit Pistons" onSelect={updateTeam}>Detroit Pistons</MenuItem>
      <MenuItem className="dropdown-item" eventKey="Golden State Warriors" onSelect={updateTeam}>Golden State Warriors</MenuItem>
      <MenuItem className="dropdown-item" eventKey="Houston Rockets" onSelect={updateTeam}>Houston Rockets</MenuItem>
      <MenuItem className="dropdown-item" eventKey="Indiana Pacers" onSelect={updateTeam}>Indiana Pacers</MenuItem>
      <MenuItem className="dropdown-item" eventKey="Los Angeles Clippers" onSelect={updateTeam}>L.A. Clippers</MenuItem>
      <MenuItem className="dropdown-item" eventKey="Los Angeles Lakers" onSelect={updateTeam}>L.A. Lakers</MenuItem>
      <MenuItem className="dropdown-item" eventKey="Memphis Grizzlies" onSelect={updateTeam}>Memphis Grizzlies</MenuItem>
      <MenuItem className="dropdown-item" eventKey="Miami Heat" onSelect={updateTeam}>Miami Heat</MenuItem>
      <MenuItem className="dropdown-item" eventKey="Milwaukee Bucks" onSelect={updateTeam}>Milwaukee Bucks</MenuItem>
      <MenuItem className="dropdown-item" eventKey="Minnesota Timberwolves" onSelect={updateTeam}>Minnesota Timberwolves</MenuItem>
      <MenuItem className="dropdown-item" eventKey="New Orleans Pelicans" onSelect={updateTeam}>New Orleans Pelicans</MenuItem>
      <MenuItem className="dropdown-item" eventKey="New York Knicks" onSelect={updateTeam}>New York Knicks</MenuItem>
      <MenuItem className="dropdown-item" eventKey="Oklahoma City Thunder" onSelect={updateTeam}>Oklahoma City Thunder</MenuItem>
      <MenuItem className="dropdown-item" eventKey="Orlando Magic" onSelect={updateTeam}>Orlando Magic</MenuItem>
      <MenuItem className="dropdown-item" eventKey="Philadelphia 76ers" onSelect={updateTeam}>Philadelphia 76ers</MenuItem>
      <MenuItem className="dropdown-item" eventKey="Phoenix Suns" onSelect={updateTeam}>Phoenix Suns</MenuItem>
      <MenuItem className="dropdown-item" eventKey="Portland Trail Blazers" onSelect={updateTeam}>Portland Trail Blazers</MenuItem>
      <MenuItem className="dropdown-item" eventKey="Sacramento Kings" onSelect={updateTeam}>Sacramento Kings</MenuItem>
      <MenuItem className="dropdown-item" eventKey="San Antonio Spurs" onSelect={updateTeam}>San Antonio Spurs</MenuItem>
      <MenuItem className="dropdown-item" eventKey="Toronto Raptors" onSelect={updateTeam}>Toronto Raptors</MenuItem>
      <MenuItem className="dropdown-item" eventKey="Utah Jazz" onSelect={updateTeam}>Utah Jazz</MenuItem>
      <MenuItem className="dropdown-item" eventKey="Washington Wizards" onSelect={updateTeam}>Washington Wizards</MenuItem>
    </DropdownButton>
);

const buttonInstance = (
  <ButtonToolbar>{dropdownInstance}</ButtonToolbar>
);

const filterInstance = (
  <div className="filter-options">
    Position Filter:
    <Checkbox inline className="filter-option" id="pg"> PG</Checkbox>
    <Checkbox inline className="filter-option" id="sg"> SG</Checkbox>
    <Checkbox inline className="filter-option" id="sf"> SF</Checkbox>
    <Checkbox inline className="filter-option" id="pf"> PF</Checkbox>
    <Checkbox inline className="filter-option" id="c"> C</Checkbox>
    <Button inline bsStyle="success" className="green-button filter-button" onClick={updatePositions}>Update</Button>
  </div>
)

var mountNode = document.getElementById('dropdown-container');
var mountNode2 = document.getElementById('filter-container');

ReactDOM.render(buttonInstance, mountNode);
ReactDOM.render(filterInstance, mountNode2);
