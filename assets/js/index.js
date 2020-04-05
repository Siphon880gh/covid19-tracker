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
window.laCounty = []; // LA County Department of Public Health
window.window.laTimesCalifornia = []; // LA Times reporting California levels, now replaced John Hopkins
window.window.cnnNewYork = []; // CNN reporting state levels, now replaced John Hopkins for US states
window.johnHopkinsStates = []; // John Hopkins University stopped reporting county levels on 3/10/20 however state levels still available.
window.johnHopkinsCountries= []; // John Hopkins University stopped reporting state levels on 3/24/20 onwards. So we can only refer to US

window.overrides = [];
window.urlLists = [];
window.notes = [];
window.sourcesRetrieved = 0;
window.sourcesAllRetrieved = 7; // John Hopkins + LA Public Health for LA County + LA Times for California + CNN for New York + overrides + urlLists + notes

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
    var response = await fetch("override-dates-logic/endpoint.php", {cache: "reload"}, dat=>dat);
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
    var response = await fetch("urls-logic/endpoint.php", {cache: "reload"}, dat=>dat);
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

(async function setNotes() {
    var response = await fetch("notes-logic/endpoint.php", {cache: "reload"}, dat=>dat);
    var dump = await response.text(); // don't use .json() because can't assure it won't be empty
    var arr = [], arr2 = [];
    if(dump.length) arr = JSON.parse(dump, true);

    Object.keys(arr).forEach(function(filename,index) { // {filename: {dates...}} => conformedObject { area, dates {} }
        var area = "";
        area = filename.replace(".json", "");
        arr2.push({area:area, note:arr[filename]});
    });
    window.notes = arr2;
    console.log("notes [ { area, note }, ... ]", window.notes);
    window.sourcesRetrieved++;
})(); // setOverrides

(async function setLaCounty() {
    var response = await fetch("cronjobs/la-county/data/daily-cumulative.json", {cache: "reload"}, dat=>dat);
    var dump = await response.text(); // don't use .json() because can't assure it won't be empty
    var arr = [];
    if(dump.length) arr = JSON.parse(dump, true); // {dates...}
    arr = reverseObject(arr);
    arr = convertCumulativeCasesToBreakdownCases(arr);
     
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


(async function setCnnNewYork() {
    var response = await fetch("cronjobs/new-york/data/daily-cumulative.json", {cache: "reload"}, dat=>dat);
    var dump = await response.text(); // don't use .json() because can't assure it won't be empty
    var arr = [];
    if(dump.length) arr = JSON.parse(dump, true); // {dates...}
    arr = reverseObject(arr);
    arr = convertCumulativeCasesToBreakdownCases(arr);
     
    // {dates...} => conformedObject { area, title, dates {} }
    window.cnnNewYork = [
        {
            area: "New York",
            title: "New York",
            dates: arr
        }
    ];
    
    console.log("area, title, dates []]", window.cnnNewYork);
    // debugger;
    window.sourcesRetrieved++;
})(); // setLaCounty


(async function setLaTimesCalifornia() {
    var response = await fetch("cronjobs/california/data/daily-cumulative.json", {cache: "reload"}, dat=>dat);
    var dump = await response.text(); // don't use .json() because can't assure it won't be empty
    var arr = [];
    if(dump.length) arr = JSON.parse(dump, true); // {dates...}
    arr = reverseObject(arr);
    arr = convertCumulativeCasesToBreakdownCases(arr);
     
    // {dates...} => conformedObject { area, title, dates {} }
    window.laTimesCalifornia = [
        {
            area: "California",
            title: "California",
            dates: arr
        }
    ];
    
    console.log("area, title, dates []]", window.laCounty);
    // debugger;
    window.sourcesRetrieved++;
})(); // setLaTimesCalifornia


// (async function setjohnHopkinsStates() { // reports daily breakdown cases
//     var response = await fetch("https://raw.githubusercontent.com/CSSEGISandData/COVID-19/master/csse_covid_19_data/csse_covid_19_time_series/time_series_19-covid-Confirmed.csv", {cache: "reload"}, dat=>dat);
//     var dump = await response.text(); // don't use .json() because can't assure it won't be empty
//     var arr = [];
//     if(dump.length>0) arr = csvToJson(dump);

//     // Conform Object
//     arr.forEach((areaObject, i) => { // {province/state,country/region,lat,long,dates...} => conformedObject { area, title, dates {} }
//         var conformedObject = {
//                                 area:"",
//                                 title:"",
//                                 dates: {}
//                               };

//         var sp = (typeof areaObject["Province/State"]!=="undefined" && areaObject["Province/State"]!==null)?areaObject["Province/State"]:"", 
//             cr = (typeof areaObject["Country/Region"]!=="undefined" && areaObject["Country/Region"]!==null)?areaObject["Country/Region"]:"", 
//             lat = (typeof areaObject["Lat"]!=="undefined" && areaObject["Lat"]!==null)?areaObject["Lat"]:"", 
//             long = (typeof areaObject["Long"]!=="undefined" && areaObject["Long"]!==null)?areaObject["Long"]:"";

//         // Coerce types
//         if(typeof sp==="undefined" || sp === null) sp = "";
//         if(typeof cr==="undefined" || cr === null) cr = "";
//         lat = lat + "";
//         long = long + "";

//         // Coerce max length for long/lat
//         if(lat.toString().length>8) lat = lat.toString().substr(0,8);
//         if(long.toString().length>8) long = long.toString().substr(0,8);

//         var name = (sp.length?sp+", ":"") + cr;
//         var coords = ` <a target="_blank" href="https://www.google.com/maps/@${lat},${long},8z">(${lat}, ${long})</a>`;
//         conformedObject.area = name;
//         conformedObject.title = name + coords;
//         // console.log("Name + coords", conformedObject.title);

//         delete areaObject["title"];
//         delete areaObject["Province/State"];
//         delete areaObject["Country/Region"];
//         delete areaObject["Lat"];
//         delete areaObject["Long"];
//         if(typeof areaObject[""]!=="undefined") delete areaObject[""]; // glitchy csv
//         if(typeof areaObject[","]!=="undefined") delete areaObject[","]; // glitchy csv
//         conformedObject.dates = areaObject;
//         // console.log("dates", conformedObject.dates);
//         // console.log("obj { area, title, dates {} }", conformedObject);

//         arr[i] = conformedObject;

//     }); // map dumps
//     window.johnHopkinsStates = arr;
//     // console.log("johnHopkins [ { area, title, dates {} }, ... ]", window.johnHopkins);
//     window.sourcesRetrieved++;
// })(); // setjohnHopkinsStates


(async function setjohnHopkinsCountries() { // // reports daily cumulative cases
    var response = await fetch("https://raw.githubusercontent.com/CSSEGISandData/COVID-19/master/csse_covid_19_data/csse_covid_19_time_series/time_series_covid19_confirmed_global.csv", {cache: "reload"}, dat=>dat);
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
        if(typeof areaObject[""]!=="undefined") delete areaObject[""]; // glitchy csv
        if(typeof areaObject[","]!=="undefined") delete areaObject[","]; // glitchy csv
        areaObject = convertCumulativeCasesToBreakdownCases(areaObject);
        conformedObject.dates = areaObject;
        // console.log("dates", conformedObject.dates);
        // console.log("obj { area, title, dates {} }", conformedObject);

        arr[i] = conformedObject;

    }); // map dumps
    window.johnHopkinsCountries = arr;
    // console.log("johnHopkins [ { area, title, dates {} }, ... ]", window.johnHopkins);
    window.sourcesRetrieved++;
})(); // setjohnHopkinsCountries

// Convert cumulative cases to breakdown cases
function convertCumulativeCasesToBreakdownCases(areaObject) { // areaObject without the city/state/lat/long fields, so only the date fields
    var prevCumulativeCases = 0;
    Object.keys(areaObject).forEach(function(date, index) {
        let cumulativeCases = parseInt(areaObject[date]);
        areaObject[date] = cumulativeCases - prevCumulativeCases;
        if(areaObject[date]===0) delete areaObject[date];
        prevCumulativeCases = cumulativeCases;
    });
    return areaObject;
} // convertCumulativeCasesToBreakdownCases


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
// window.queryByCoords = [];

function renderTable(query, dataSource) {
    // query can search area name or coordinates (coordinates have to be exact)
    let queryFirstEntry = dataSource.find((areaObject, i)=>{
        return areaObject.title.indexOf(query)!==-1;
    });

    // dataSource.forEach((areaObject, i)=>{
    //     if(areaObject.title.indexOf(query)!==-1);
    //         window.queryByCoords.push(areaObject.title);
    // });

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
                        <th>New<br/>Cases</th>
                        <th>Cumulative<br/><small>Doubling<br/>Time</small></th>
                        <th>Change</th>
                    </tr></thead>`);
    $table.append("<tbody/>");

    window.cumulativeCases = 0;
    window.prevCumulativeCases = 0;
    window.graphData  = []; // of {x:int, y:int}
    let dateCasesKvp = queryFirstEntry.dates; // date-cases key-value pairs {m/d/yy: number cases]}
    let $tbody = $template.find("tbody");

    let lastDoubled = 0;
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
            if(percentChange>=100) TD_percentChange = `<td class="percent-change" style="background-color:pink;">${percentChange}%</td>`;
            else if(percentChange>=75) TD_percentChange = `<td class="percent-change" style="background-color:#FED8B1;">${percentChange}%</td>`;
            else if(percentChange>=25) TD_percentChange = `<tdclass="percent-change" style="background-color:lightyellow;">${percentChange}%</td>`;
            else TD_percentChange = `<td class="percent-change" style="background-color:lightgreen;">+ ${percentChange}%</td>`;
        }
        window.prevCumulativeCases = cumulativeCases;

        let nowDoubled = "";
        if(window.cumulativeCases>=lastDoubled*2) {
            nowDoubled = `<span class="clickable" style="color:red" onclick="$(this).children().toggleClass('hide');">
                            <span>(2x)</span>
                            <span class="hide">${window.cumulativeCases*2}</span>
                          </span>`;
            lastDoubled = window.cumulativeCases;
        }
        $tbody.prepend(`
            <tr>
                <td data-unix="${unix}">${date}</td>
                <td>${cases}</td>
                <td>${cumulativeCases} ${nowDoubled}</td>
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

    // Insert graph
    let selfData = [{
        label: 'Covid-19/Coronavirus Cases',
        data: window.graphData
    }];
    insertGraph($template.find(".js-graph"), selfData);

    // Insert any links
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

    // Insert any notes
    let queryFirstNote = window.notes.find((aNote, i)=>{
        return aNote.area.indexOf(query)!==-1;
    });
    if(typeof queryFirstNote!=="undefined") {
        let $note = $template.find(".note");
        let note = queryFirstNote.note;
        $note.html(`<i class="fa fa-sticky-note">`).attr({"title":note,"data-placement":"bottom","data-html":"true"}).tooltip();
    } // queryFirstNotes

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

function combineGraphs($canva, arr$areas) {
    let graphs = [];

    arr$areas.forEach(($area, i) => { 
        let $cumulativeCases = $area.find("td:nth-child(3)"); // [$td,...]
        let cumulativeCases = $cumulativeCases.toArray().map((td)=>parseInt($(td).text())); // [int,...]
        let $unixs = $area.find("td:nth-child(1)"); // [$td,...]
        let unixs = $unixs.toArray().map((td)=>parseInt($(td).attr("data-unix"))); // [int,...]

        window.graphData = [];
        unixs.forEach( (unix, i) => {
            let graphCoordinate = {x:0, y:0};
            graphCoordinate.x = unix;
            graphCoordinate.y = cumulativeCases[i];
            window.graphData.push(graphCoordinate);
        });

        let areaName = $area.find(".title").attr("data-area"); 
        let randomColor = getRandomRgb();
        
        let graph = {
            label: areaName,
            data: window.graphData,
            pointBorderColor: randomColor,
            borderColor: randomColor
         }
         graphs.push(graph);
     });

     let selfData = [];

    insertGraph($canva, graphs);
}

var sourcesRetrieving = setInterval(()=> {
    if(sourcesRetrieved===sourcesAllRetrieved) {
        clearInterval(sourcesRetrieving);
        renderTable("Los Angeles", window.laCounty);
        renderTable("California", window.laTimesCalifornia);
        renderTable("New York", window.cnnNewYork);
        renderTable("Japan", window.johnHopkinsCountries);
        renderTable("US", window.johnHopkinsCountries);
        renderTable("Italy", window.johnHopkinsCountries);
        renderTable("55.3781, -3.436", window.johnHopkinsCountries); // United Kingdom

        // Standardize table heights
        // var heights = $(".area").map((i,area)=>$(area).height()).toArray();
        // var maxHeight = Math.max.apply(null, heights);
        // $(".area").height(maxHeight);

        // Compare dropdown on change
        $("#compare-view").on("blur", ev=>{
            let value = ev.target.value;
            if(value.length===0) return false;
            let modal = value; // "#modal-combined-graphs-1"
            $(modal).modal("show");
        });

        // Init graph views
        const arr$areas1 = [
                            $(".area:has(.title[data-area='US'])"), 
                            $(".area:has(.title[data-area='Los Angeles'])"),
                            $(".area:has(.title[data-area='Japan'])"),
                            $(".area:has(.title[data-area='Italy'])"),
                        ]
        combineGraphs($("#modal-combined-graphs-1 canvas"), arr$areas1);
        $("#compare-view").append(`<option value="#modal-combined-graphs-1">Interested Cities</option>`);


        const arr$areas2 = [
            $(".area:has(.title[data-area='Los Angeles'])"),
            $(".area:has(.title[data-area='Japan'])")
        ]
        combineGraphs($("#modal-combined-graphs-2 canvas"), arr$areas2);
        $("#compare-view").append(`<option value="#modal-combined-graphs-2">LA and Japan</option>`);


        const arr$areas3 = [
            $(".area:has(.title[data-area='Los Angeles'])"),
            $(".area:has(.title[data-area='New York'])")
        ]
        combineGraphs($("#modal-combined-graphs-3 canvas"), arr$areas3);
        $("#compare-view").append(`<option value="#modal-combined-graphs-3">LA and NY</option>`);
    }
}, 100);