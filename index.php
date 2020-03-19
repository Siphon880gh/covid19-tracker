<!DOCTYPE html>
<?php 
// Source
// - Tells you when updated (Files after Feb 1 (UTC): once a day around 23:59 (UTC) AKA 5pm PST
// https://github.com/CSSEGISandData/COVID-19/tree/master/csse_covid_19_data/csse_covid_19_time_series
// https://github.com/CSSEGISandData/COVID-19/blob/master/csse_covid_19_data/csse_covid_19_time_series/time_series_19-covid-Confirmed.csv

// Confirm Los Angeles numbers:
// http://publichealth.lacounty.gov/media/Coronavirus/
// https://twitter.com/lapublichealth

// If tweaking Google Map:
// https://developers.google.com/maps/documentation/maps-static/intro#Zoomlevels

// "Los Angeles inaccurate" ticket:
// https://github.com/CSSEGISandData/COVID-19/issues/495
?>
<html lang="en">
  <head>
   <title>Covid-19 / Coronavirus Growth Tracker</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no">

    <!-- jQuery and Bootstrap  -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css">
    <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/font-awesome/5.12.1/css/all.min.css">
    <script src="https://code.jquery.com/jquery-2.1.4.min.js"></script>

    <!-- Moment JS and Chart JS -->
    <script src="//cdnjs.cloudflare.com/ajax/libs/moment.js/2.24.0/moment.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.3/Chart.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.3/Chart.min.css"></link>

    <style>
    .container-alt {
        padding: 10px;
    }
    #title {
        font-size: 3rem;
    }
    #desc {
        margin-top: 5px;
    }
    #credits {
        margin-top: 20px;
        font-size: 10px;
    }

    /* Mobile responsive: Credit and source inline vs different lines */
    #credits .source {
        display: block;
    }
    @media (min-width: 768px) {
        #credits span:not(:first-child) {
            margin-left: 20px;
            display: inline;
        }
    }
    #credits .authored {
        font-weight: bold;
    }
    #credits .source {
        font-style: italic;
    }
    .area {
        position: relative;
        float: left;
        display: inline-block;
        margin: 10px;
        border: 1px solid black;
        padding: 5px;
    }
    .area .title {
        font-weight: 800;
        font-size: 1.5rem;
        text-align: center;
    }
    .area th, .area td {
        padding-left:10px;
        padding-right:10px;
    }
    .area .js-table {
        margin: 0 auto;
    }
    .fa-link {
        margin-left: .5ch;
        font-size: 1rem;
    }
    .clickable {
        cursor: pointer;
    }
    .clickable:hover {
        color: green;
    }
    .links {
        position: absolute;
        right: 0;
        top: -22.5px;
        margin-top: .25ch;
    }
    </style>

    <script>
    var url = "https://raw.githubusercontent.com/CSSEGISandData/COVID-19/master/csse_covid_19_data/csse_covid_19_time_series/time_series_19-covid-Confirmed.csv";

    function extractLines(text) {
        return text.split("\n");
    }
    function getHeaders(lines) {
        var headers = lines[0]; // first line text
        headers = headers.split(","); // array
        return headers;
    }
    function getBodyCells(lines) {
        lines.shift(1); // remove first line;
        lines = lines.map(line=>{
            return line.split(/,(?=(?:(?:[^"]*"){2})*[^"]*$)/); // array and ignore commas within double-quotes 
        })
        return lines;
    }

    window.overrides = [];
    window.activeOverride = {};
    window.urls = {};

    /**
     * Make an array of objects with the keys from the headers
     * @param {array String} thead Array of keys
     * @param {array String} tbody Array of values
     */
    function makeArrayObjects(thead, tbody) {

        function createTitle(sp, cr, lat, long, newLine) {
            // debugger;
            // debugger;
            // Don't assume the csv is not messed up on some lines
            if(typeof sp==="undefined" || typeof lat==="undefined" || typeof long==="undefined") return;
            // try {
                if(lat.toString().length>8) lat = lat.toString().substr(0,8);
                if(long.toString().length>8) long = long.toString().substr(0,8);
            // } catch {
            //     debugger;
            // }
            var named = sp.length?sp+", ":"";
            named += cr;
            var coords = ` <a target="_blank" href="https://www.google.com/maps/@${lat},${long},8z">(${lat}, ${long})</a>`;
            // if(named.indexOf("Los Angeles")!==-1) {
            //     debugger;
            // }
            return named + coords;
        }
        tbody = tbody.map(function(line) {
            var newLine = {};

            // if(line[0].indexOf("Los Angeles")!==-1) {
            //     debugger;
            // }

            line.forEach( function(val,i) {
                var key = thead[i];
                // if(i>=59) debugger;
                key = key.replace(/"/g, "").replace(/^\s{1,}/g, "").replace(/\s{1,}$/g, ""); // Some CSV entries have double quotation marks to have the comma as part of the value so cleaning it up for DOM text. Some have glitching trailing spaces.
                val = val.replace(/"/g, "").replace(/^\s{1,}/g, "").replace(/\s{1,}$/g, "");
                newLine[key] = val;
            });

            // Title key concatenates named location and geographic coordinates and is first key value pair
            titleVal = createTitle(newLine["Province/State"], newLine["Country/Region"], newLine['Lat'], newLine['Long'], newLine);
            // debugger;
            var newerLine = {title: titleVal ,... newLine};

            var query =  newLine["Province/State"] + newLine["Country/Region"];
            query = query.split(",")[0];
            query = query.substr(0, 10);
            // debugger;
            query = query.replace(new RegExp("[^a-zA-Z0-9 ,]", "g"), "");
            
            // Reset
            window.activeOverride = {};

            Object.keys(window.overrides).forEach(function(keyArea, i) { // {area: {...}}
                // console.log("keyArea Los Angeles: ", keyArea);
                // console.log("query usually not los Angeles: ", query);
                if((new RegExp(query, "i")).test(keyArea)) {
                    activeOverride = jQuery.extend(true, {}, window.overrides[keyArea]);
                }
            });

            if(!jQuery.isEmptyObject(activeOverride)) { // {...} which is {date:cases, date:cases, (urls: [.., ..])}
                Object.keys(activeOverride).forEach(function(keyDate, i) { 
                    // console.log("date format", keyDate);
                    if(typeof newerLine[keyDate]!=="undefined") {
                        // console.log("keydate matches, 2 at end:")
                        newerLine[keyDate] = activeOverride[keyDate]; // assign number cases
                    } else if(keyDate==="urls") {
                        window.urls[query] = activeOverride["urls"];
                    }
                });
            }

            // debugger;
            return newerLine;
        });

        return tbody;
    } // makeArrayObjects

    $.ajaxSetup({
        cache: false
    });

    // Order: Get overrides, then get live datapoints
    $.get("overrides-logic/endpoint.php", (data)=>{
        if(data.length) {
            window.overrides = JSON.parse(data, true);
            // window.overrides.forEach(override => {  
            //     Object.keys(override).forEach(function(key,i) {
            //         return false;
            //     });
            // });
            // debugger;
        }

        $.get(url).done((textData, status, xhr)=>{ // xml header response, nope does not have Last-Modified
            // Prepare lines
            pipe = extractLines(textData);
            var thead = getHeaders(pipe);
            var tbody = getBodyCells(pipe);
            var arrObjs = makeArrayObjects(thead, tbody);
            // debugger;
            
            createSoi("Los Angeles", arrObjs);
            createSoi("California", arrObjs);
            createSoi("Washington", arrObjs);
            createSoi("New York", arrObjs);
            createSoi("Italy", arrObjs);
            createSoi("United Kingdom, United Kingdom", arrObjs);
            createSoi("Orange County", arrObjs);

            // Add URLs if overrides-data provides
            Object.keys(window.urls).forEach(function(keyArea, i) {
                var $matchedTitle = $(`.area .title:contains(${keyArea})`).first();
                if($matchedTitle.length) {
                    var $links = $matchedTitle.closest(".area").find(".links");
                    window.urls[keyArea].forEach(url => {
                        $links.append($(`<i class='fas fa-link clickable' onclick="window.open('${url}');"/>`));
                    });
                }
            });

        }); // get
    });


    // Subject of interest
    function createSoi(place, arrObjs) {
        let queryFirstEntry = arrObjs.find((entry)=>{
            // if(entry.title.indexOf("Los Angeles")!==-1) {
            //     debugger;
            // }

            // Do not assume the live CSV will not glitch on some lines
            if(typeof entry.title==="undefined")
                return false;
            else
                return entry.title.indexOf(place)!==-1;
        });

        let templateHtml = $("#template-table").html();
        let $template = $(templateHtml);
        let $title = $template.find(".title");

        // Do not assume you hardcode the place's name without typos
        if(typeof queryFirstEntry==="undefined") {
            return;
        }
        $title.html(queryFirstEntry.title);

        let $table = $template.find(".js-table");
        $table.append(`<thead><tr>
                            <th>Date</th>
                            <th>Cases</th>
                            <th>Cumulative</th>
                        </tr></thead>`);
        $table.append("<tbody/>");

        let $tbody = $template.find("tbody");

        delete queryFirstEntry["title"];
        delete queryFirstEntry["Province/State"];
        delete queryFirstEntry["Country/Region"];
        delete queryFirstEntry["Lat"];
        delete queryFirstEntry["Long"];

        window.cumulativeCases = 0;
        window.graphData  = []; // of {x:int, y:int}

        
        Object.keys(queryFirstEntry).forEach(function(date, i) {
            let cases = parseInt( queryFirstEntry[date].length?queryFirstEntry[date]:0 );
            let unix = moment(date, "MM/DD/YYYY").valueOf()/1000;
            window.cumulativeCases += cases;
            $tbody.prepend(`
                <tr>
                    <td data-unix="${unix}">${date}</td>
                    <td>${cases}</td>
                    <td>${cumulativeCases}</td>
                </tr>
            `);
            window.graphData.push({x:unix, y:cumulativeCases});
        });

        let $graph = $template.find(".js-graph");
        var ctx = $graph[0].getContext("2d");
        var scatterChart = new Chart(ctx, {
            type: 'line',
            data: {
                datasets: [{
                    label: 'Covid-19/Coronavirus Cases',
                    data: window.graphData
                }]
            },
            options: {
                tooltips: {
                    callbacks: {
                    title(datasets) {
                    var time = new Date(datasets[0].xLabel * 1000);
                        return (time.getMonth() + 1) + '/' + time.getDate()
                    }
                }
                },
                scales: {
                    xAxes: [
                    {
                        type: 'linear',
                        position: 'bottom',
                        ticks: {
                        callback(value) {
                            var time = new Date(value * 1000);
                            return (time.getMonth() + 1) + '/' + time.getDate();
                        }
                        }
                    }
                    ]
                }
            }
        });

        $("body").append($template);
    }
    </script>
</head>
    <body>
        <div class="container-alt">
            <div id="title">Covid-19 Growth <i class="fas fa-first-aid"></i></div>
            <div id="desc">Find out how fast the virus is growing in your area. Contact me to request other areas.</div>
            <div id="credits">
                <span class="authored">Weng Fei Fung</span>
                <span class="source">Data pulled automatically on a daily basis from John Hopkins University. Please contact me if there are errors.</span>
            </div>
            
            <p></p>
            <p></p>

            <template id="template-table">
                <div class="area">
                    <div class="links"></div>
                    <div class="title"></div>
                    <div style="width: 100%; height: 100%">
                        <canvas class="js-graph"></canvas>
                    </div>
                    <table class="js-table table-bordered table-striped">
                    </table>
                </div>
            </template>

    </body>
</html>