// TODO:
// class DataSourcePicker{ 
//     constructor {
//         this.constants = ["NONE", "DEFAULT", "JOHN_HOPKINS", "LA_COUNTY_DEPT_PUBLIC_HEALTH"];
//         this.interface = {
//             "Los Angeles": this.constants["LA_COUNTY_DEPT_PUBLIC_HEALTH"]
//         }
//         default
//         url
//     }
// }

/* Latest data, so turn off cache */
$.ajaxSetup({
    cache: false
});

/** Overrides 
 * 
 * Expect window.overrides; {area, dates}
*/
window.laCounty = [];
window.johnHopkinsStates = []; // John Hopkins University stopped reporting county levels on 3/10/20 however state levels still available.
window.johnHopkinsCountries= []; // John Hopkins University stopped reporting state levels on 3/24/20 onwards. So we can only refer to US

window.overrides = [];
window.urlLists = [];
window.sourcesRetrieved = 0;
window.sourcesAllRetrieved = 5; // John Hopkins + LA County + overrides + urlLists

// Csv text where first line are headers
const csvToJson = (str, headerList, quotechar = '"', delimiter = ',') => {
    const cutlast = (_, i, a) => i < a.length - 1;
    // const regex = /(?:[\t ]?)+("+)?(.*?)\1(?:[\t ]?)+(?:,|$)/gm; // no variable chars
    const regex = new RegExp(`(?:[\\t ]?)+(${quotechar}+)?(.*?)\\1(?:[\\t ]?)+(?:${delimiter}|$)`, 'gm');
    const lines = str.split('\n');
    let headers = headerList || lines.splice(0, 1)[0].match(regex).filter(cutlast);
    headers = headers.map(header=>(header.length && header[header.length-1]===',')?header.substr(0, header.length-1):header);
  
    const list = [];
  
    for (const line of lines) {
      const val = {};
      for (const [i, m] of [...line.matchAll(regex)].filter(cutlast).entries()) {
        // Attempt to convert to Number if possible, also use null if blank
        val[headers[i]] = (m[2].length > 0) ? Number(m[2]) || m[2] : null;
      }
      list.push(val);
    }
  
    return list;
} // csvToJson

(async function setOverrides() {
    var response = await fetch("override-dates-logic/endpoint.php", dat=>dat.txt());
    var dump = await response.text(); // don't use .json() because can't assure it won't be empty
    var arr = [], arr2 = [];
    if(dump.length) arr = JSON.parse(dump, true);

    Object.keys(arr).forEach(function(filename,index) { // {filename: {dates...}} => conformedObject { area, dates {} }
        var area = "";
        area = filename.replace(".json", "");
        arr2.push({area:area, dates:arr[filename]});
    });
    window.overrides = arr2;
    console.log("overrides [ { area, dates {} }, ... ]", window.overrides);
    window.sourcesRetrieved++;
})(); // setOverrides


(async function setUrls() {
    var response = await fetch("urls-logic/endpoint.php", dat=>dat.txt());
    var dump = await response.text(); // don't use .json() because can't assure it won't be empty
    var arr = [], arr2 = [];
    if(dump.length) arr = JSON.parse(dump, true);

    Object.keys(arr).forEach(function(filename,index) { // {filename: {dates...}} => conformedObject { area, dates {} }
        var area = "";
        area = filename.replace(".json", "");
        arr2.push({area:area, urls:arr[filename]});
    });
    window.urlLists = arr2;
    console.log("urls [ { area, urls [] }, ... ]", window.urlLists);
    window.sourcesRetrieved++;
})(); // setOverrides


(async function setLaCounty() {
    var response = await fetch("cronjobs/la-county/data/daily-cases.json", dat=>dat.txt());
    var dump = await response.text(); // don't use .json() because can't assure it won't be empty
    var arr = [];
    if(dump.length) arr = JSON.parse(dump, true); // {dates...}
    arr = reverseObject(arr);
     
    // {dates...} => conformedObject { area, title, dates {} }
    window.laCounty = [
        {
            area: "Los Angeles",
            title: "Los Angeles",
            dates: arr
        }
    ];
    
    console.log("area, title, dates []]", window.laCounty);
    // debugger;
    window.sourcesRetrieved++;
})(); // setLaCounty

(async function setjohnHopkinsStates() {
    var response = await fetch("https://raw.githubusercontent.com/CSSEGISandData/COVID-19/master/csse_covid_19_data/csse_covid_19_time_series/time_series_19-covid-Confirmed.csv", dat=>dat.txt());
    var dump = await response.text(); // don't use .json() because can't assure it won't be empty
    var arr = [];
    if(dump.length>0) arr = csvToJson(dump);

    // Conform Object
    arr.forEach((areaObject, i) => { // {province/state,country/region,lat,long,dates...} => conformedObject { area, title, dates {} }
        var conformedObject = {
                                area:"",
                                title:"",
                                dates: {}
                              };

        var sp = (typeof areaObject["Province/State"]!=="undefined" && areaObject["Province/State"]!==null)?areaObject["Province/State"]:"", 
            cr = (typeof areaObject["Country/Region"]!=="undefined" && areaObject["Country/Region"]!==null)?areaObject["Country/Region"]:"", 
            lat = (typeof areaObject["Lat"]!=="undefined" && areaObject["Lat"]!==null)?areaObject["Lat"]:"", 
            long = (typeof areaObject["Long"]!=="undefined" && areaObject["Long"]!==null)?areaObject["Long"]:"";

        // Coerce types
        if(typeof sp==="undefined" || sp === null) sp = "";
        if(typeof cr==="undefined" || cr === null) cr = "";
        lat = lat + "";
        long = long + "";

        // Coerce max length for long/lat
        if(lat.toString().length>8) lat = lat.toString().substr(0,8);
        if(long.toString().length>8) long = long.toString().substr(0,8);

        var name = (sp.length?sp+", ":"") + cr;
        var coords = ` <a target="_blank" href="https://www.google.com/maps/@${lat},${long},8z">(${lat}, ${long})</a>`;
        conformedObject.area = name;
        conformedObject.title = name + coords;
        // console.log("Name + coords", conformedObject.title);

        delete areaObject["title"];
        delete areaObject["Province/State"];
        delete areaObject["Country/Region"];
        delete areaObject["Lat"];
        delete areaObject["Long"];
        conformedObject.dates = areaObject;
        // console.log("dates", conformedObject.dates);
        // console.log("obj { area, title, dates {} }", conformedObject);

        arr[i] = conformedObject;

    }); // map dumps
    window.johnHopkinsStates = arr;
    // console.log("johnHopkins [ { area, title, dates {} }, ... ]", window.johnHopkins);
    window.sourcesRetrieved++;
})(); // setjohnHopkinsStates


(async function setjohnHopkinsCountries() {
    var response = await fetch("https://raw.githubusercontent.com/CSSEGISandData/COVID-19/master/csse_covid_19_data/csse_covid_19_time_series/time_series_covid19_confirmed_global.csv", dat=>dat.txt());
    var dump = await response.text(); // don't use .json() because can't assure it won't be empty
    var arr = [];
    if(dump.length>0) arr = csvToJson(dump);

    // Conform Object
    arr.forEach((areaObject, i) => { // {province/state,country/region,lat,long,dates...} => conformedObject { area, title, dates {} }
        var conformedObject = {
                                area:"",
                                title:"",
                                dates: {}
                              };

        var sp = (typeof areaObject["Province/State"]!=="undefined" && areaObject["Province/State"]!==null)?areaObject["Province/State"]:"", 
            cr = (typeof areaObject["Country/Region"]!=="undefined" && areaObject["Country/Region"]!==null)?areaObject["Country/Region"]:"", 
            lat = (typeof areaObject["Lat"]!=="undefined" && areaObject["Lat"]!==null)?areaObject["Lat"]:"", 
            long = (typeof areaObject["Long"]!=="undefined" && areaObject["Long"]!==null)?areaObject["Long"]:"";

        // Coerce types
        if(typeof sp==="undefined" || sp === null) sp = "";
        if(typeof cr==="undefined" || cr === null) cr = "";
        lat = lat + "";
        long = long + "";

        // Coerce max length for long/lat
        if(lat.toString().length>8) lat = lat.toString().substr(0,8);
        if(long.toString().length>8) long = long.toString().substr(0,8);

        var name = (sp.length?sp+", ":"") + cr;
        var coords = ` <a target="_blank" href="https://www.google.com/maps/@${lat},${long},8z">(${lat}, ${long})</a>`;
        conformedObject.area = name;
        conformedObject.title = name + coords;
        // console.log("Name + coords", conformedObject.title);

        delete areaObject["title"];
        delete areaObject["Province/State"];
        delete areaObject["Country/Region"];
        delete areaObject["Lat"];
        delete areaObject["Long"];
        conformedObject.dates = areaObject;
        // console.log("dates", conformedObject.dates);
        // console.log("obj { area, title, dates {} }", conformedObject);

        arr[i] = conformedObject;

    }); // map dumps
    window.johnHopkinsCountries = arr;
    // console.log("johnHopkins [ { area, title, dates {} }, ... ]", window.johnHopkins);
    window.sourcesRetrieved++;
})(); // setjohnHopkinsCountries

function reverseObject(object) {
    var newObject = {};
    var keys = [];

    for (var key in object) {
        keys.push(key);
    }

    for (var i = keys.length - 1; i >= 0; i--) {
      var value = object[keys[i]];
      newObject[keys[i]]= value;
    }       

    return newObject;
} // reverseObject

function renderTable(query, dataSource) {
    let queryFirstEntry = dataSource.find((areaObject, i)=>{
        return areaObject.area.indexOf(query)!==-1;
    });

    // If the area is not found in the data source
    if(typeof queryFirstEntry==="undefined") {
        return;
    }

    let queryFirstOverride = window.overrides.find((anOveride, i)=>{
        return anOveride.area.indexOf(query)!==-1;
    });
    // If need to override
    if(typeof queryFirstOverride!=="undefined") {
        var originalDateCases =  queryFirstEntry.dates;
        var overridingDateCases = reverseObject(queryFirstOverride.dates);

        for (var date in overridingDateCases) { // prop in obj
            originalDateCases[date] = overridingDateCases[date];
            console.log(date);
        }
        queryFirstEntry.dates = originalDateCases;
        console.log("dates recent=>oldest", queryFirstEntry.dates);
    }

    let templateHtml = $("#template-table").html();
    let $template = $(templateHtml);
    let $title = $template.find(".title");
    $title.html(queryFirstEntry.title);
    $title.attr("data-area", queryFirstEntry.area); // You'll need the title without coords for combined graphs' area labels

    let $table = $template.find(".js-table");
    $table.append(`<thead><tr>
                        <th>Date</th>
                        <th>Cases</th>
                        <th>Cumulative</th>
                        <th>Change</th>
                    </tr></thead>`);
    $table.append("<tbody/>");

    window.cumulativeCases = 0;
    window.prevCumulativeCases = 0;
    window.graphData  = []; // of {x:int, y:int}
    let dateCasesKvp = queryFirstEntry.dates; // date-cases key-value pairs {m/d/yy: number cases]}
    let $tbody = $template.find("tbody");

    Object.keys(dateCasesKvp).forEach(function(date, i) {
        let cases = parseInt(dateCasesKvp[date]);
        let unix = moment(date, "MM/DD/YYYY").valueOf()/1000;
        window.cumulativeCases = parseInt(cumulativeCases);
        window.cumulativeCases += cases;

        function getPercentChange() {
            return parseFloat( (cumulativeCases/prevCumulativeCases)*100 ).toFixed(2);
        }

        let TD_percentChange = `<td style="background-color:lightgreen;">+ 0%</td>`; // default
        if(prevCumulativeCases>0 && prevCumulativeCases!==cumulativeCases) {
            let percentChange = getPercentChange();
            if(percentChange>=100) TD_percentChange = `<td style="background-color:pink;">+ ${percentChange}%</td>`;
            else if(percentChange>=75) TD_percentChange = `<td style="background-color:#FED8B1;">+ ${percentChange}%</td>`;
            else if(percentChange>=25) TD_percentChange = `<td style="background-color:lightyellow;">+ ${percentChange}%</td>`;
            else TD_percentChange = `<td style="background-color:lightgreen;">+ ${percentChange}%</td>`;
        }
        window.prevCumulativeCases = cumulativeCases;

        $tbody.prepend(`
            <tr>
                <td data-unix="${unix}">${date}</td>
                <td>${cases}</td>
                <td>${cumulativeCases}</td>
                ${TD_percentChange}
            </tr>
        `);
        window.graphData.push({x:unix, y:cumulativeCases});
    });

    let $formula = $template.find(".formula");
    let y_cumulativeCases = graphData.map(xy=>xy.y);
    $.get("best-fit.php?y_points=" + y_cumulativeCases.join(","))
    .done(res=>{ 
        $formula.html(res);
        // debugger;
     });

     let selfData = [{
        label: 'Covid-19/Coronavirus Cases',
        data: window.graphData
     }];
     insertGraph($template.find(".js-graph"), selfData);

    let queryFirstUrls = window.urlLists.find((anUrlList, i)=>{
        return anUrlList.area.indexOf(query)!==-1;
    });
    // If need render urls
    if(typeof queryFirstUrls!=="undefined") {
        var $links = $template.find(".links");

        queryFirstUrls.urls.forEach(url=>{
            $links.append($(`<i class='fas fa-link clickable' onclick="window.open('${url}');"/>`));
        });
    } // queryFirstUrls

    $("#areas").append($template);
} // renderTable

function insertGraph($parent, datum) { // $DOM to insert, array of data
    var ctx = $parent[0].getContext("2d");
    var scatterChart = new Chart(ctx, {
        type: 'line',
        data: {
            datasets: datum
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
} // insertGraph

function getRandomRgb() {
    var o = Math.round, r = Math.random, s = 255;
    return 'rgba(' + o(r()*s) + ',' + o(r()*s) + ',' + o(r()*s) + ',' + r().toFixed(1) + ')';
}

function combineGraphs() {
    let $combinedGraph = $("#wrapper-combined-graphs");
    let graphs = [];

    $(".area").each((i, area) => { 
        let $cumulativeCases = $(area).find("td:nth-child(3)"); // [$td,...]
        let cumulativeCases = $cumulativeCases.toArray().map((td)=>parseInt($(td).text())); // [int,...]
        let $unixs = $(area).find("td:nth-child(1)"); // [$td,...]
        let unixs = $unixs.toArray().map((td)=>parseInt($(td).attr("data-unix"))); // [int,...]

        window.graphData = [];
        unixs.forEach( (unix, i) => {
            let graphCoordinate = {x:0, y:0};
            graphCoordinate.x = unix;
            graphCoordinate.y = cumulativeCases[i];
            window.graphData.push(graphCoordinate);
        });

        let areaName = $(area).find(".title").attr("data-area"); 
        
        let graph = {
            label: areaName,
            data: window.graphData,
            pointBorderColor: getRandomRgb(),
            borderColor: getRandomRgb()
         }
         graphs.push(graph);
     });

     let selfData = [];

    insertGraph($combinedGraph, graphs);
}

var sourcesRetrieving = setInterval(()=> {
    if(sourcesRetrieved===sourcesAllRetrieved) {
        clearInterval(sourcesRetrieving);
        // renderTable("US", window.johnHopkinsCountries);
        renderTable("Los Angeles", window.laCounty);
        renderTable("California", window.johnHopkinsStates);
        renderTable("Washington", window.johnHopkinsStates);
        renderTable("New York", window.johnHopkinsStates);
        renderTable("United Kingdom, United Kingdom", window.johnHopkinsStates);
        // renderTable("Italy", window.johnHopkins);
        combineGraphs();
    }
}, 100);