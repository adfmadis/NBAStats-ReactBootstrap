<!DOCTYPE html>

<?php
  include "php/nbadatabase.php";
?>

<html lang="en">
  <head>
<!--    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1"> -->
    <title>NBA Stats | Player Stats page | R-BS</title>
    <!-- Bootstrap core CSS -->
    <link href="css/bootstrap.css" rel="stylesheet">
    <!-- Custom styles for this template -->
    <link href="css/style.css" rel="stylesheet">

    <!-- jQuery -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
    <!-- react - latest version -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/react/15.3.1/react.min.js"></script><!--<script src="node_modules/react/dist/react.js"></script>-->
    <!-- react-dom -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/react/15.3.1/react-dom.min.js"></script><!--<script src="node_modules/react-dom/dist/react-dom.js"></script>-->
    <!-- react-bootstrap - https://github.com/react-bootstrap/react-bootstrap-bower -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/react-bootstrap/0.30.3/react-bootstrap.min.js"></script><!--<script src="react-bootstrap/react-bootstrap.min.js"></script>-->
    <!-- jsx transformer - needed for development mode -->
    <script src="JSXTransformer.js"></script>
    <!-- include d3 and jquery -->
    <script src="http://d3js.org/d3.v2.js"></script>
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>

  </head>

  <body bgcolor="#E9FAE3">
    <!-- navbar -->
    <nav class="navbar navbar-fixed-top bg-inverse nav-text">
      <a class="navbar-brand" href="#">NBA Stat Tracker ... </a>
      <ul class="nav navbar-nav">
        <li class="nav-item">
          <a class="nav-link" href="index.html">Main<span class="sr-only">(current)</span></a>
        </li>
        <li class="nav-item active">
          <a class="nav-link" href="playerstats.php">Player Stats</a>
        </li>
        <!--<li class="nav-item">
          <a class="nav-link" href="leaguestats.php">League Stats</a>
        </li>-->
      </ul>
    </nav>

    <!-- section containing form where team and positions are selected for visualization, react-bootstrap
  dropdown menu, checkboxes, etc are edded when playground.js script is executed -->
    <section class="dropdown-filter" id="dropdownfilter">
      <form class="form-control form-inline">
        <div class="container">
          <div class="row">

            <div class="col-md-3 col-sm-12" id="dropdown-container"> </div> <!-- dropdown-container -->

            <div class="col-md-9 col-sm-12" id="filter-container"> </div> <!-- filter-container -->

          </div> <!-- row -->
        </div> <!-- container -->
      </form> <!-- form-control -->
    </section>

    <div class="results"></div>

    <script src="playground.js" type="text/jsx"></script>

    <!-- section contains all javascript, all of d3 code done in here -->
    <section class="visualization-area" id="visualization-area">
      <script id="visualization-area">

      // change background color
      document.body.style.background = "#E9FAE3";



      // get data from php/nbadatabase.php file, for maxes of domains and all players in nba
      var queryData = <?php echo json_encode($queryarr); ?>;
      // teamPosData used for visualization, both variables defined in playground.js jsx file
      var teamData = [], teamPosData = [];
      var maxData = <?php echo json_encode($maxarr); ?>;
      // remove last item from queryData array which will always equal 'null' based on php code
      queryData.splice(queryData.length-1,1);
      // change string numbers/floats to numbers/floats in queryData
      for (var playernum = 0; playernum < queryData.length; playernum++) {
        for (var qnum = 4; qnum < queryData[playernum].length; qnum++) {
          queryData[playernum][qnum] = parseFloat(queryData[playernum][qnum]); // change to num/float
        } // for
      } // for

        // visualization code
        // VARIABLES
        var screenwidth = $(window).width() > 900 ? $(window).width() : 900,
            screenheight = $(window).height()-160 > 400 ? $(window).height()-160 : 400;
        var margin = {
          left: 100, right: 100, top: 100, bottom: 100
        };
        var graphwidth = screenwidth - margin.left - margin.right;
        var graphheight = screenheight - margin.top - margin.bottom;

        // column name/index for player data collected from database
        var team = 0, name = 1, position = 2, secondaryPosition = 3, gamesPlayed = 4, points = 5;
        var rebounds = 6, assists = 7, steals = 8, blocks = 9, turnovers = 10, fgp = 11, tpp = 12;
        // arrays for column names, and filter options
        var graphColumns = ["Games Played","Points","Rebounds","Assists","Steals","Blocks","Turnovers","Field Goal %", "3 Point %"];
        var positions = ["PG","SG","SF","PF","C"];
        // create array to show if column is selected (made with loop in case graphColumns array changes)
        var columnIsSelected = [];
        for (var colnum = 0; colnum < graphColumns.length; colnum++) columnIsSelected.push(false);


        // create an svg container
    		var svgItem = d3.select("body").append("svg:svg").attr("id","svgItem")
          .style("background-color","#E9FAE3")
    			.attr("width",screenwidth)
    			.attr("height",screenheight)
          .attr("class","svgItem");

        var graphParent = svgItem.append("g").attr("id","visualContainer");

        // svgItem helper function
        // resizes svgItem to appropriate width whenever window resizes
        function updateWindow(){
          // update all height/width variables
    			screenwidth = $(window).width() > 900 ? $(window).width() : 900;
          screenheight = $(window).height()-160 > 400 ? $(window).height()-160 : 400;
          graphwidth = screenwidth - margin.left - margin.right;
          graphheight = screenheight - margin.top - margin.bottom;

    			svgItem.attr("width", screenwidth);
          svgItem.attr("height", screenheight);

          // only call updateVisual if data exists
          if (teamPosData.length != 0) updateVisual(header.text());

    		} // updateWindow
        window.onresize = updateWindow;

        // create and place label variable in svg container
        var header = svgItem.append("text")
          .attr("dx", 50). attr("dy",48)
          .text("2015-2016 Player Stats")
          .style("font-size",23)
          .style("font-weight","bold");

        // variables used with d3 visualizations
        var columnData, lineData;
        var lineColours = ["white", "green", "orange", "blue", "red", "grey", "brown", "pink"];

        // FUNCTIONS

        // put data collected from database and filtered (filteredQueryData) into proper format for displaying (in columns)
  			var processColumnData = function(data) {

  				var returnData = [];
  				for (var columnnum = 0; columnnum < graphColumns.length; columnnum++) {
  					// push empty array to returnData for each column that will be shown on the graph
  					returnData.push([]);
  				} // for
  				for (var playernum = 0; playernum < data.length; playernum++) {
  					var player = data[playernum];
  					for (var columnnum2 = 0; columnnum2 < returnData.length; columnnum2++) {
  						var column = returnData[columnnum2];
              // add appropriate objects (dataObj) to columnObj array

              var dataObj = {
                column: graphColumns[columnnum2],
                stat: 0,
                name: player[name],
                position: player[position],
                secPosition: player[secondaryPosition],
                textValue: ""
              }

  						if (dataObj.column == "Games Played") dataObj.stat = player[gamesPlayed];
  						else if (dataObj.column == "Points") dataObj.stat = player[points];
  						else if (dataObj.column == "Rebounds") dataObj.stat = player[rebounds];
  						else if (dataObj.column == "Assists") dataObj.stat = player[assists];
  						else if (dataObj.column == "Steals") dataObj.stat = player[steals];
  						else if (dataObj.column == "Blocks") dataObj.stat = player[blocks];
  						else if (dataObj.column == "Turnovers") dataObj.stat = player[turnovers];
  						else if (dataObj.column == "Field Goal %") dataObj.stat = player[fgp];
              else if (dataObj.column == "3 Point %") dataObj.stat = player[tpp];

              // add data object to columnObj array
              column.push(dataObj);

  					} // for
  				} // for

  				return returnData;
  			} // processColumnData

        // this function puts focus of visualization on 1 column



        //var yDomainArr = []; // this array is used for processLineData function
        var processLineData = function(qData) {

          var xyPixData = [];//, xPixData = [];
          for (var playernum = 0; playernum < qData.length; playernum++) {
            var playerArr = [];
            for (var cnum = 0; cnum < graphColumns.length; cnum++) {
              // Y PIX
              var player = qData[playernum];
              var max = parseFloat(maxData[cnum],10), min = 0;
              var pixVal = graphheight/(max - min); // calculate scale of pixels per 1 unit of a stat along yaxis

              var units = max - player[cnum+4];  // Starts at 4 because first 4 column in player always represent player's team, name, position, and secondary position

              var colwidth = graphwidth/graphColumns.length;
              var pixelsfromleft = colwidth*cnum + 35;
              var pixelsfromtop = /*margin.top +*/ pixVal*units; // since lines are being drawn to svgItem instead of chartAndAxis,
                                                             // margin must be accounted for
              // X PIX
              //var pixelsfromleft = margin.left + graphwidth/3 + (cnum)*graphwidth;
              playerArr.push({"x": pixelsfromleft, "y": pixelsfromtop});
            } // for
            xyPixData.push(playerArr);
          } // for
          return xyPixData;
        } // processLineData

        // define container for visualization
        var chartAndAxis = graphParent.append("g")
          .attr("transform","translate(" + (margin.left + "," + margin.top + ")"))
          .attr("id","chartandaxis");

        // creates and adds all svg visuals
  		  var showFullGraph = function() {

          // execute helpers that add visualization to container
          showLineGraph(chartAndAxis);
          showColGraphs(chartAndAxis);

          // format graph container
          $('#chartandaxis text').css("font-size","14px");

  			} // showFullGraph

        /* showLineGraph:
        */
        var showLineGraph = function(chartAndAxis) {

          // set up line and axes for each player

          for (var playernum = 0; playernum < lineData.length; playernum++) {
            // set x and y domains
            var xDomain = [0,9];//;
                yDomain = [0,graphheight];

            // define x and y scales/axes
            var xScale = d3.scale.linear()
              .domain(xDomain)
              .range([0,graphwidth]);

            var yScale = d3.scale.linear() //REMEMBER: y axis range has the bigger number first because the y value of zero is at the top of chart and increases as you go down.
              .domain(yDomain)
              .range([graphheight,0]);

            var xAxis = d3.svg.axis()
              .orient("bottom").scale(xScale)
              .tickValues([0,1,2,3,4,5,6,7,8,9]) // hard coded, must update if graph columns change
              .tickFormat(function(d) { return graphColumns[d]; });

            var yAxis = d3.svg.axis()
              .orient("left").scale(yScale).ticks(0);

            // only draw the axis once
            if (playernum === 0) {
              // add axes to svg item
              chartAndAxis.append("g")
                .call(yAxis)
                .style('fill','none')
                .attr("stroke-width", 5)
                .attr("stroke", "black")
                .selectAll("text")
                .attr("stroke-width", 1)
                .attr("stroke", "black");

              chartAndAxis.append("g")
                .call(xAxis)
                .style('fill','none')
                .attr("stroke-width", 5)
                .attr("stroke", "black")
                .attr("transform", "translate(0," + graphheight + ")")
                .selectAll("text")
                .attr("stroke-width", 1)
                .attr("stroke", "black");
            } // if

            // define line
            var colnum = -1;
            var valueline = d3.svg.line()
              .x(function(d) {
                return d.x;
              })
              .y(function(d) {
                return d.y;
              })
              .interpolate("linear");

            var colour = lineColours[playernum % lineColours.length];

            // add line to svgItem
            chartAndAxis.append("path")
              .attr("d",valueline(lineData[playernum]))
              .attr("stroke", colour)
              .attr("stroke-width", 2)
              .attr("fill", "none")
              .attr("id", "playerline");
              //.on(");
          } // for

        } // showLineGraph

        /* addPlayerText:
        */
        var addPlayerText = function(playerText, player, columnID) {
          var nameValue = player.name, posValue = player.position, secPosValue = player.secPosition;
          // add to playerText
          if (secPosValue === "")
            playerText += (nameValue + ", " + posValue + ": <br>" + player.stat + " " + columnID + "<br>");
          else
            playerText += (nameValue + ", " + posValue + "/" + secPosValue + ": <br>" + player.stat + " " + columnID + "<br>");

          return playerText;
        } // addPlayerText

        /* showColGraphs:
        */
        var showColGraphs = function(chartAndAxis) {

          // 1 graph made for each column, so all columns colwidth add to graphwidth
  				var colwidth = graphwidth/columnData.length;

          // Define the div for the tooltips that show when hovering over a point of data in graph
          var div = d3.select("body").append("div")
            .attr("class", "tooltip")
            .style("opacity", 0);

          var colnum = -1;
          // helper function(s) for inside loop
          var setDataPoints = function(d,i) {
            colnum++; var shift = colnum*colwidth;
            //console.log("colnum = " + colnum + ", colwidth = " + colwidth + ", product = " + shift);

            // add circles to graph
            colArea.selectAll("circle") // colArea defined in loop
              .data(columnData[columnnum]) // columnnum is the loop variable in upcoming for loop
              .enter()
              .append("circle")
              .attr("class", "data")
              .attr("r", 4)
              .attr("cx", shift + 35)
              .attr("cy",function(point) { return yScale(point.stat); })
              .on("mouseover", function(point) {
                // get column ID to use in tooltip
                var columnID, columnNum;
                if (point.column == "Games Played") { columnID = "GP"; columnNum = 0; }
                else if (point.column == "Points") { columnID = "PPG"; columnNum = 1; }
                else if (point.column == "Rebounds") { columnID = "RPG"; columnNum = 2; }
                else if (point.column == "Assists") { columnID = "APG"; columnNum = 3; }
                else if (point.column == "Steals") { columnID = "SPG"; columnNum = 4; }
                else if (point.column == "Blocks") { columnID = "BPG"; columnNum = 5; }
                else if (point.column == "Turnovers") { columnID = "TPG"; columnNum = 6; }
                else if (point.column == "Field Goal %") { columnID = "FG%"; columnNum = 7; }
                else if (point.column == "3 Point %") { columnID = "3P%"; columnNum = 8; }

                // create text for tooltip, list all players with same exact stat value
                // (list points that are hidden under main point)
                var playerText = ""; var playeramount = 0;

                //console.log("columnnum = " + columnnum + ", ... = "); console.log(columnData);
                for (var playernum = 0; playernum < columnData[columnNum].length; playernum++) {
                  var player = columnData[columnNum][playernum];
                  if (player.stat-point.stat < 0.03 && player.stat-point.stat > -0.03) {
                    playerText = addPlayerText(playerText,player,columnID);
                    playeramount++;
                  } // if
                } // for


                div.transition()
                    .duration(200)
                    .style("opacity", .9);
                div.html(playerText)
                    .style("left", (d3.event.pageX + 15) + "px")
                    .style("top", (d3.event.pageY - 40*playeramount) + "px")
                    .style("background-color", "white")
                    .style("border", "2pt solid black")
                    .style("border-radius", "6pt")
                    .style("margin-left", 5);

              })
              .on("mouseout", function(d) {
                div.transition()
                    .duration(500)
                    .style("opacity", 0);
              })
              .attr("fill", "black");
          } // setDataPoints


          // // on colArea click, if selected then unselect column, and
          // // if unselected then unselect all other columns, and select colArea
          // var unselectColumn = function(colArea, num) {
          //   console.log("unselect column" + num);
          // } // unselect
          // var selectColumn = function(colArea, num) {
          //   console.log("select column" + num);
          // } // select

          // add individual colArea elements to this
          //var colAreas = [];


          // loop through each column where each column is it's own graph
          for (var columnnum = 0; columnnum < columnData.length; columnnum++) {
            // variables
    	      var data = columnData[columnnum];

            // set x and y domains
    				var xDomain = [0,1],
    						yDomain = [0,parseFloat(maxData[columnnum])];

            var colArea = chartAndAxis.append("g").attr("id", ("colArea" + columnnum));
              //.attr("transform","translate(" +  + ",0)");

            // define x and y scales/axes
            var xScale = d3.scale.linear()
              .domain(xDomain)
              .range([0,colwidth]);

            var yScale = d3.scale.linear() //REMEMBER: y axis range has the bigger number first because the y value of zero is at the top of chart and increases as you go down.
              .domain(yDomain)  // make axis end in round number
              .range([graphheight,0]);

            var xAxis = d3.svg.axis()
              .orient("bottom").scale(xScale).ticks(0);

            var yAxis = d3.svg.axis()
              .orient("left").scale(yScale)
              .tickValues(yDomain);

//             // add rectangle to colArea that can be clicked for selecting and unselecting columns
//             var rect = colArea.append("rect")
//             .attr("x",colwidth*columnnum)
//             .attr("height", graphheight).attr("width", colwidth)
//             .attr("fill-opacity", "0");
//
//             rect.on("click", function(thisrect) {
//               var colnum = this.attr("x")/colwidth;
//               console.log("column is: " + colnum);
//
// //              console.log("id = " + colArea.attr("id"));
//               var idlength = colArea.attr("id").length;
//               var colnum = parseInt(colArea.attr("id")[idlength-1],10);
//
//               if (columnIsSelected[colnum]) unselectColumn(colArea,colnum);
//               else {
//                 // unselect any selected column and select colArea
//                 for (var cnum = 0; cnum < colAreas.length; cnum++) {
//                   if (columnIsSelected[cnum]) {
//                     unselectColumn(colAreas[cnum],cnum);
//                     // since only 1 column selected at a time we can break now
//                     break;
//                   } // if
//                 } // for
//
//                 // select colArea
//                 selectColumn(colArea,colnum);
//               } // else
//
//               d3.event.stopPropagation();
//             }); // rect on click

            // draw y axis with labels and axis label
            if (columnnum === 0) { // set up left-most yAxis
              colArea.append("g")
                .call(yAxis)
                .style('fill','none')
                .attr("stroke-width", 5)
                .attr("stroke", "black")
                .selectAll("text")
                .attr("dx", 35)
                .attr("dy",function(d) {
                  return (d === 0) ? -6 : 6;
                }) // dy
                .attr("stroke-width", 1)
                .attr("stroke", "black")
            } else { // set up all other yAxes
              colArea.append("g")
                .call(yAxis)
                .style('fill','none')
                .attr("transform", "translate(" + columnnum*colwidth + ",0)")
                .selectAll("text")
                .attr("dx", 35)
                .attr("dy",function(d) {
                  return (d === 0) ? -6 : 6;
                }) // dy
                .attr("stroke-width", 1)
                .attr("stroke", "black")
            } // else

            // draw x axis with labels and axis label and set up all elements of it
            colArea.append("g")
              .call(xAxis)
              .style('fill','none')
              //.attr("stroke-width", 5)
              //.attr("stroke", "blue")
              //.attr("dx",columnnum*colwidth)
              .attr("transform", "translate(" + columnnum*colwidth + "," + graphheight + ")");

            // create column plot
            var columnplot = colArea.selectAll("columnplot")
              .data([data]).enter()
              .append("g");


            // add finished version of colArea to colAreas arraqy
//            colAreas.push(colArea,columnnum);


            // add data points to column plot
            columnplot.attr("class","columnplot").each(setDataPoints);

          } // for */

        } // showColGraphs


        /* updateVisual: updates visualization-area to contain most recent player data as well as most recent
        visualization */
        var updateVisual = function(text) {
          // clear svg and declare necessities for showFullGraph function
          d3.selectAll("svg > *").remove();
      	  var header = svgItem.append("text")
            .attr("id","headertext")
      	    .attr("dx", 50). attr("dy",48)
      	    .text(text)
      	    .style("font-size",23)
            .style("color","red")
      	    .style("font-weight","bold");
          graphParent = svgItem.append("g").attr("id","visualContainer");
          chartAndAxis = graphParent.append("g")
            .attr("transform","translate(" + (margin.left + "," + margin.top + ")"))
            .attr("id","chartandaxis");

          // call processColumnData
          columnData = processColumnData(teamPosData);
          //console.log("columnData = "); console.log(columnData); // debugging
          // call processLineData
          lineData = processLineData(teamPosData);
          //console.log("lineData = "); console.log(lineData); // debugging

          // call showFullGraph
          showFullGraph();

        } // updateVisual

      </script>
    </section>


  </body>

</html>
