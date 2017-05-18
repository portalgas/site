/*
 * http://d3pie.org/
 * http://d3js.org/
 */
function  myGrafics() {

/* grafic chart */
var pie = new d3pie("pieChart", {
/*	
	"header": {
		"title": {
			"text": "",
			"fontSize": 22,
			"font": "verdana"
		},
		"subtitle": {
			"text": "",
			"color": "#999999",
			"fontSize": 10,
			"font": "verdana"
		},
		"titleSubtitlePadding": 12
	},
	"footer": {
		"text": "",
		"color": "#999999",
		"fontSize": 11,
		"font": "open sans",
		"location": "bottom-center"
	},
*/
	"size": {
		"canvasHeight": 400,
		"canvasWidth": 590,
		"pieOuterRadius": "88%"
	},
	"data": {
		"content": data
	},
	"smallSegmentGrouping": {
		"enabled": true,
		"value": 200.00,
		"valueType": "value"
	},	
	"labels": {
		"outer": {
			"pieDistance": 32
		},
		"inner": {
			"format": "value"
		},
		"mainLabel": {
			"font": "verdana"
		},
		"percentage": {
			"color": "#e1e1e1",
			"font": "verdana",
			"decimalPlaces": 0
		},
		"value": {
			"color": "#e1e1e1",
			"font": "verdana"
		},
		"lines": {
			"enabled": true,
			"color": "#cccccc"
		},
		"truncation": {
			"enabled": true
		}
	},
	"effects": {
		"pullOutSegmentOnClick": {
			"effect": "linear",
			"speed": 400,
			"size": 8
		}
	},
	"tooltips": {
		"enabled": true,
		"type": "placeholder",
		"string": "{label}: {value} euro"
	},
	"effects": {
		"pullOutSegmentOnClick": {
			"effect": "linear",
			"speed": 400,
			"size": 8
		}
	},	
	"misc": {
		"gradient": {
			"enabled": true,
			"percentage": 100
		}
	},	
	"callbacks": {}
});

/* grafic bars */
var margin = {top: 20, right: 20, bottom: 30, left: 40},
	width = 500 - margin.left - margin.right,
	height = 500 - margin.top - margin.bottom;

var x = d3.scale.ordinal()
	.rangeRoundBands([0, width], .1);

var y = d3.scale.linear()
	.range([height, 0]);

var xAxis = d3.svg.axis()
	.scale(x)
	.orient("bottom");

var yAxis = d3.svg.axis()
	.scale(y)
	.orient("left")
	.ticks(10, "€");

var tip = d3.tip()
  .attr('class', 'd3-tip')
  .offset([-10, 0])
  .html(function(d) {
    return "<span style='color:#999'>" + d.label + ": " + d.value + " euro</span>";
})
  
var svg = d3.select("#pieBars").append("svg")
	.attr("width", width + margin.left + margin.right)
	.attr("height", height + margin.top + margin.bottom)
  .append("g")
	.attr("transform", "translate(" + margin.left + "," + margin.top + ")");

  x.domain(data.map(function(d) { return d.label; }));
  y.domain([0, d3.max(data, function(d) { return d.value; })]);
		
  svg.append("g")
	  .attr("class", "x axis")
	  .attr("transform", "translate(0," + height + ")")
	  .call(xAxis);

  svg.append("g")
	  .attr("class", "y axis")
	  .call(yAxis)
	.append("text")
	  .attr("transform", "rotate(-90)")
	  .attr("y", 6)
	  .attr("dy", ".71em")
	  .style("text-anchor", "end")
	  .text("value");

  svg.selectAll(".bar")
	  .data(data)
	.enter().append("rect")
	  .attr("class", "bar")
	  .attr("x", function(d) { return x(d.label); })
	  .attr("width", x.rangeBand())
	  .attr("y", function(d) { return y(d.value); })
	  .attr("height", function(d) { return height - y(d.value); })
	  .on('mouseover', tip.show)
      .on('mouseout', tip.hide)
	  ;
	
	svg.call(tip);
}