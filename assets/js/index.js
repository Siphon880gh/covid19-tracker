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
window.johnHopkinsCountries = []; // John Hopkins University stopped reporting state levels on 3/24/20 onwards. So we can only refer to US

window.overrides = [];
window.urlLists = [];
window.notes = [];
window.sourcesRetrieved = 0;
window.sourcesAllRetrieved = 8; // John Hopkins + LA Public Health for LA County + LA Times for California + CNN for New York + overrides + urlLists + notes

// Csv text where first line are headers
const csvToJson = (str, headerList, quotechar = '"', delimiter = ',') => {
        const cutlast = (_, i, a) => i < a.length - 1;
        // const regex = /(?:[\t ]?)+("+)?(.*?)\1(?:[\t ]?)+(?:,|$)/gm; // no variable chars
        const regex = new RegExp(`(?:[\\t ]?)+(${quotechar}+)?(.*?)\\1(?:[\\t ]?)+(?:${delimiter}|$)`, 'gm');
        const lines = str.split('\n');
        let headers = headerList || lines.splice(0, 1)[0].match(regex).filter(cutlast);
        headers = headers.map(header => (header.length && header[header.length - 1] === ',') ? header.substr(0, header.length - 1) : header);

        const list = [];

        for (const line of lines) {
            const val = {};
            for (const [i, m] of[...line.matchAll(regex)].filter(cutlast).entries()) {
                // Attempt to convert to Number if possible, also use null if blank
                val[headers[i]] = (m[2].length > 0) ? Number(m[2]) || m[2] : null;
            }
            list.push(val);
        }

        return list;
    } // csvToJson

(async function setOverrides() {
    var response = await fetch("override-dates-logic/endpoint.php", { cache: "reload" }, dat => dat);
    var dump = await response.text(); // don't use .json() because can't assure it won't be empty
    var arr = [],
        arr2 = [];
    if (dump.length) arr = JSON.parse(dump, true);

    Object.keys(arr).forEach(function(filename, index) { // {filename: {dates...}} => conformedObject { area, dates {} }
        var area = "";
        area = filename.replace(".json", "");
        arr2.push({ area: area, dates: arr[filename] });
    });
    window.overrides = arr2;
    console.log("overrides [ { area, dates {} }, ... ]", window.overrides);
    window.sourcesRetrieved++;
})(); // setOverrides


(async function setUrls() {
    var response = await fetch("urls-logic/endpoint.php", { cache: "reload" }, dat => dat);
    var dump = await response.text(); // don't use .json() because can't assure it won't be empty
    var arr = [],
        arr2 = [];
    if (dump.length) arr = JSON.parse(dump, true);

    Object.keys(arr).forEach(function(filename, index) { // {filename: {dates...}} => conformedObject { area, dates {} }
        var area = "";
        area = filename.replace(".json", "");
        arr2.push({ area: area, urls: arr[filename] });
    });
    window.urlLists = arr2;
    console.log("urls [ { area, urls [] }, ... ]", window.urlLists);
    window.sourcesRetrieved++;
})(); // setOverrides

(async function setNotes() {
    var response = await fetch("notes-logic/endpoint.php", { cache: "reload" }, dat => dat);
    var dump = await response.text(); // don't use .json() because can't assure it won't be empty
    var arr = [],
        arr2 = [];
    if (dump.length) arr = JSON.parse(dump, true);

    Object.keys(arr).forEach(function(filename, index) { // {filename: {dates...}} => conformedObject { area, dates {} }
        var area = "";
        area = filename.replace(".json", "");
        arr2.push({ area: area, note: arr[filename] });
    });
    window.notes = arr2;
    console.log("notes [ { area, note }, ... ]", window.notes);
    window.sourcesRetrieved++;
})(); // setOverrides


(async function setLaCountyHospitalized() {
    var response = await fetch("cronjobs/la-county-hospitals/data/daily-cumulative.json", { cache: "reload" }, dat => dat);
    var dump = await response.text(); // don't use .json() because can't assure it won't be empty
    var arr = [];
    if (dump.length) arr = JSON.parse(dump, true); // {dates...}
    arr = reverseObject(arr);
    arr = convertCumulativeCasesToBreakdownCases(arr);

    // {dates...} => conformedObject { area, title, dates {} }
    window.laCountyHospitals = [{
        area: "Los Angeles Covid Hospitalizations", // must match name passed by renderTable
        title: "Los Angeles Covid Hospitalizations", // Render title top of graph
        dates: arr
    }];

    console.log("area, title, dates []]", window.laCountyHospitals);
    // debugger;
    window.sourcesRetrieved++;
})(); // setLaCountyHospitalized

(async function setLaCounty() {
    var response = await fetch("cronjobs/la-county/data/daily-cumulative.json", { cache: "reload" }, dat => dat);
    var dump = await response.text(); // don't use .json() because can't assure it won't be empty
    var arr = [];
    if (dump.length) arr = JSON.parse(dump, true); // {dates...}
    arr = reverseObject(arr);
    arr = convertCumulativeCasesToBreakdownCases(arr);

    // {dates...} => conformedObject { area, title, dates {} }
    window.laCounty = [{
        area: "Los Angeles", // must match name passed by renderTable
        title: "Los Angeles Population", // Render title top of graph
        dates: arr
    }];

    console.log("area, title, dates []]", window.laCounty);
    // debugger;
    window.sourcesRetrieved++;
})(); // setLaCounty


(async function setCnnNewYork() {
    var response = await fetch("cronjobs/new-york/data/daily-cumulative.json", { cache: "reload" }, dat => dat);
    var dump = await response.text(); // don't use .json() because can't assure it won't be empty
    var arr = [];
    if (dump.length) arr = JSON.parse(dump, true); // {dates...}
    arr = reverseObject(arr);
    arr = convertCumulativeCasesToBreakdownCases(arr);

    // {dates...} => conformedObject { area, title, dates {} }
    window.cnnNewYork = [{
        area: "New York",
        title: "New York",
        dates: arr
    }];

    console.log("area, title, dates []]", window.cnnNewYork);
    // debugger;
    window.sourcesRetrieved++;
})(); // setLaCounty


(async function setLaTimesCalifornia() {
    var response = await fetch("cronjobs/california/data/daily-cumulative.json", { cache: "reload" }, dat => dat);
    var dump = await response.text(); // don't use .json() because can't assure it won't be empty
    var arr = [];
    if (dump.length) arr = JSON.parse(dump, true); // {dates...}
    arr = reverseObject(arr);
    arr = convertCumulativeCasesToBreakdownCases(arr);

    // {dates...} => conformedObject { area, title, dates {} }
    window.laTimesCalifornia = [{
        area: "California",
        title: "California",
        dates: arr
    }];

    console.log("area, title, dates []]", window.laCounty);
    // debugger;
    window.sourcesRetrieved++;
})(); // setLaTimesCalifornia

(async function setjohnHopkinsCountries() { // // reports daily cumulative cases
    var response = await fetch("https://raw.githubusercontent.com/CSSEGISandData/COVID-19/master/csse_covid_19_data/csse_covid_19_time_series/time_series_covid19_confirmed_global.csv", { cache: "reload" }, dat => dat);
    var dump = await response.text(); // don't use .json() because can't assure it won't be empty
    var arr = [];
    if (dump.length > 0) arr = csvToJson(dump);

    // Conform Object
    arr.forEach((areaObject, i) => { // {province/state,country/region,lat,long,dates...} => conformedObject { area, title, dates {} }
        var conformedObject = {
            area: "",
            title: "",
            dates: {}
        };

        var sp = (typeof areaObject["Province/State"] !== "undefined" && areaObject["Province/State"] !== null) ? areaObject["Province/State"] : "",
            cr = (typeof areaObject["Country/Region"] !== "undefined" && areaObject["Country/Region"] !== null) ? areaObject["Country/Region"] : "",
            lat = (typeof areaObject["Lat"] !== "undefined" && areaObject["Lat"] !== null) ? areaObject["Lat"] : "",
            long = (typeof areaObject["Long"] !== "undefined" && areaObject["Long"] !== null) ? areaObject["Long"] : "";

        // Coerce types
        if (typeof sp === "undefined" || sp === null) sp = "";
        if (typeof cr === "undefined" || cr === null) cr = "";
        lat = lat + "";
        long = long + "";

        // Coerce max length for long/lat
        if (lat.toString().length > 8) lat = lat.toString().substr(0, 8);
        if (long.toString().length > 8) long = long.toString().substr(0, 8);

        var name = (sp.length ? sp + ", " : "") + cr;
        var coords = ` <a target="_blank" href="https://www.google.com/maps/@${lat},${long},8z">(${lat}, ${long})</a>`;
        conformedObject.area = name;
        conformedObject.title = name + coords;
        // console.log("Name + coords", conformedObject.title);

        delete areaObject["title"];
        delete areaObject["Province/State"];
        delete areaObject["Country/Region"];
        delete areaObject["Lat"];
        delete areaObject["Long"];
        if (typeof areaObject[""] !== "undefined") delete areaObject[""]; // glitchy csv
        if (typeof areaObject[","] !== "undefined") delete areaObject[","]; // glitchy csv
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
        if (areaObject[date] === 0) delete areaObject[date];
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
        newObject[keys[i]] = value;
    }

    return newObject;
} // reverseObject
// window.queryByCoords = [];

function renderTable(query, dataSource, population, populationDensity) {
    console.log("*renderTable*: ", query, dataSource);

    let isHospitalBeds = query.indexOf("Hospital") >= 0;
    // query can search area name or coordinates (coordinates have to be exact)
    let queryFirstEntry = dataSource.find((areaObject, i) => {
        return areaObject.title.indexOf(query) !== -1;
    });
    // debugger;

    // dataSource.forEach((areaObject, i)=>{
    //     if(areaObject.title.indexOf(query)!==-1);
    //         window.queryByCoords.push(areaObject.title);
    // });

    // If the area is not found in the data source
    if (typeof queryFirstEntry === "undefined") {
        return;
    }

    let queryFirstOverride = window.overrides.find((anOveride, i) => {
        return anOveride.area.indexOf(query) !== -1;
    });
    // If need to override
    if (typeof queryFirstOverride !== "undefined") {
        var originalDateCases = queryFirstEntry.dates;
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
    if (isHospitalBeds) {
        $table.append(`<thead>
                            <tr>
                                <th>Date</th>
                                <th colspan='2'>Beds</th>
                                <th>% Change</th>
                            </tr>
                            <tr>
                                <th></th>
                                <th>Change</th>
                                <th>Total (Real-Time)</th>
                                <th></th>
                            </tr>
                        </thead>`);
    } else {
        $table.append(`<thead>
                            <tr>
                                <th>Date</th>
                                <th>Cases</th>"}
                                <th>Cumulative (Ever)</th>"}
                                <th>% Change</th>
                            </tr>
                        </thead>`);
    }
    $table.append("<tbody/>");

    window.cumulativeCases = 0;
    window.prevCumulativeCases = 0;
    window.graphData = []; // of {x:int, y:int}
    let dateCasesKvp = queryFirstEntry.dates; // date-cases key-value pairs {m/d/yy: number cases]}
    let $tbody = $template.find("tbody");

    let lastDoubled = 0;
    let countingTillDoubled = 1;
    let firstDoubledEver = true;
    Object.keys(dateCasesKvp).forEach(function(date, i) {
        let cases = parseInt(dateCasesKvp[date]);
        let unix = moment(date, "MM/DD/YYYY").valueOf() / 1000;
        window.cumulativeCases = parseInt(cumulativeCases);
        window.cumulativeCases += cases;

        function getPercentChangeFloat() {
            var pc = parseFloat((cumulativeCases / prevCumulativeCases) * 100).toFixed(2);
            if (pc >= 100) {
                pc = `+${pc}`;
            } else if (pc >= 0) {
                // pc = pc;
            } else {
                // pc = `- ${pc}`;
            }
            return pc;
        }

        let TD_percentChange = `<td style="background-color:lightgreen;">+ 0%</td>`; // default
        if (prevCumulativeCases > 0 && prevCumulativeCases !== cumulativeCases) {
            let percentChange = getPercentChangeFloat();
            let bgColor = "";
            if (percentChange >= 100) bgColor = "pink";
            else if (percentChange >= 75) bgColor = "#FED8B1;";
            else if (percentChange >= 25) bgColor = "lightyellow";
            else bgColor = "lightgreen";
            TD_percentChange = `<td class="percent-change" style="background-color:${bgColor};">${percentChange}%</td>`;
        }
        window.prevCumulativeCases = cumulativeCases;

        countingTillDoubled++;
        let nowDoubled = "";
        if (window.cumulativeCases >= lastDoubled * 2) {
            if (firstDoubledEver) {
                firstDoubledEver = false;
                countingTillDoubled = 1;
            } // case first doubled for the table
            nowDoubled = `<br/><span class="clickable" style="color:red" onclick="$(this).children().toggleClass('hidden');">
                            <span class="doubling-time">${countingTillDoubled}</span>
                            <span class="predict-doubling-time hidden">${window.cumulativeCases*2}</span>
                          </span>`;
            lastDoubled = window.cumulativeCases;
            countingTillDoubled = 0;
        }
        var date = moment(date).format("MM/DD/YY");
        let dayOfWeek = moment(date).format("ddd");
        $tbody.prepend(`
            <tr>
                <td data-unix="${unix}">${date} ${dayOfWeek}</td>
                <td>${cases}</td>
                <td>${cumulativeCases} ${nowDoubled}</td>
                ${TD_percentChange}
            </tr>
        `);


        // Prepare graph either cumulatively or non-cumulatively
        var params = new URLSearchParams(window.location.search);
        var defaultMode = "cumulative"; // predefined by developer
        var mode = params.get("mode"); // mixed value vs null
        var routedMode = defaultMode;
        if(mode==="cumulative") {
            routedMode = "cumulative";
        } else if(mode==="noncumulative") {
            routedMode = "noncumulative";
        }
        if(routedMode==="cumulative")
            window.graphData.push({ x: unix, y: cumulativeCases });
        else {
            window.graphData.push({ x: unix, y: cases });
        }
    });

    // Best fit lines
    let $formula = $template.find(".formula");
    let y_cumulativeCases = graphData.map(xy => xy.y);
    $.get("best-fit.php?y_points=" + y_cumulativeCases.join(","))
        .done(res => {
            $formula.html(res);
            // debugger;
        });

    // Population density
    if (typeof population !== "undefined") { // arg provided in renderTable call
        let $population = $template.find(".population-line");
        if (!isHospitalBeds)
            helpIcon = `<i class="fa fa-question clickable" style="font-size:1rem; vertical-align:top;" onclick='alert("Population infected is the cumulative cases over the total population of ${query} (${parseInt(population)}). That tells you how many people have ever been infected in ${query}. But bear in mind this is an underestimation because less than 1% of population is actually tested because we do not have enough testing equipments.");'></i>`;
        else
            helpIcon = `<i class="fa fa-question clickable" style="font-size:1rem; vertical-align:top;" onclick='alert("This is the number of hospital beds (${population}) in the entire ${query} that are taken up with covid patients at the moment.");'></i>`;
        if (!isHospitalBeds)
            populationCalc = `Population infected (test capacity limited) ${helpIcon} ${ ((window.cumulativeCases/population)*100).toFixed(11) }%`;
        else
            populationCalc = `Hospital beds saturated with covid patients: ${helpIcon} ${ ((window.cumulativeCases/population)*100).toFixed(4) }%`;
        $population.html(populationCalc);
    }
    if (typeof populationDensity !== "undefined") { // arg provided in renderTable call
        let $populationDensity = $template.find(".population-density-line");
        let helpIcon = `<i class="fa fa-question clickable" style="font-size:1rem; vertical-align:top;" onclick='alert("Population density infected is the population infected percentage multiplied by ${query} population density (${populationDensity}/mi²) which is the number of people in a square mile. Of course, this is when the crowdedness is redistributed to be spread out over the entire ${query}. Is a good number to compare against other cities.");'></i>`;
        let populationDensityCalc = `Population density infected (test cpty limited) ${helpIcon} ${ ((window.cumulativeCases/population)*populationDensity).toFixed(2) }person/mi²`;
        $populationDensity.html(populationDensityCalc);
    }

    // Insert graph (after rendering table)
    let selfData = [{
        label: 'Covid-19/Coronavirus Cases',
        data: window.graphData
    }];
    insertGraph($template.find(".js-graph"), selfData);

    // Insert any links
    // let queryFirstUrls = window.urlLists.find((anUrlList, i)=>{
    //     return anUrlList.area.indexOf(query)!==-1;
    // });

    let queryFirstUrls = window.urlLists.find((anUrlList, i) => {
        return anUrlList.area === query;
    });

    // If need render urls
    if (typeof queryFirstUrls !== "undefined") {
        var $links = $template.find(".links");

        queryFirstUrls.urls.forEach(url => {
            $links.append($(`<i class='fas fa-link clickable' onclick="window.open('${url}');"/>`));
        });
    } // queryFirstUrls

    // Insert any notes
    let queryFirstNote = window.notes.find((aNote, i) => {
        return aNote.area.indexOf(query) !== -1;
    });
    if (typeof queryFirstNote !== "undefined") {
        let $note = $template.find(".note");
        let note = queryFirstNote.note;
        $note.html(`<i class="fa fa-sticky-note">`).attr({ "title": note, "data-placement": "bottom", "data-html": "true" }).tooltip();
    } // queryFirstNotes

    $("#areas").append($template);
} // renderTable

function insertGraph($parent, datum) { // $DOM to insert, array of data
    console.log("**insertGraph**", datum);
    var ctx = $parent[0].getContext("2d");
    // debugger;
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
                xAxes: [{
                    type: 'linear',
                    position: 'bottom',
                    ticks: {
                        maxTicksLimit: datum[0].data.length - 1,
                        stepSize: 3000000,
                        callback(value) {
                            if (isNaN(value)) return 0;
                            var time = new Date(value * 1000);
                            return (time.getMonth() + 1) + '/' + time.getDate();
                        }
                    }
                }]
            },
            pan: {
                // Boolean to enable panning
                enabled: true,
    
                // Panning directions. Remove the appropriate direction to disable 
                // Eg. 'y' would only allow panning in the y direction
                mode: 'xy',
                
                speed: 1
            },
    
            // Container for zoom options
            zoom: {
                // Boolean to enable zooming
                enabled: true,						
                // Zooming directions. Remove the appropriate direction to disable 
                // Eg. 'y' would only allow zooming in the y direction
                mode: 'xy',
            }
        }
    });

    // Resetable zoom level on click
    var $resetButton = $parent.closest(".not-table").find(".reset-zoom-wrapper").children(1);
    $resetButton.data("ctx", scatterChart);
} // insertGraph

function getRandomRgb() {
    var o = Math.round,
        r = Math.random,
        s = 255;
    return 'rgba(' + o(r() * s) + ',' + o(r() * s) + ',' + o(r() * s) + ',' + r().toFixed(1) + ')';
}


function zoomGraphs($canva, arr$areas) {
    let graphs = [];

    arr$areas.forEach(($area, i) => {
        let $cumulativeCases = $area.find("td:nth-child(3)"); // [$td,...]
        let cumulativeCases = $cumulativeCases.toArray().map((td) => parseInt($(td).text())); // [int,...]
        let $unixs = $area.find("td:nth-child(1)"); // [$td,...]
        let unixs = $unixs.toArray().map((td) => parseInt($(td).attr("data-unix"))); // [int,...]

        // Only show last 14 days so the graph is easier to eye
        cumulativeCases.splice(-14);
        unixs.splice(-14);

        window.graphData = [];
        unixs.forEach((unix, i) => {
            let graphCoordinate = { x: 0, y: 0 };
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
} // zoomGraphs

function combineGraphs($canva, arr$areas) {
    let graphs = [];

    arr$areas.forEach(($area, i) => {
        let $cumulativeCases = $area.find("td:nth-child(3)"); // [$td,...]
        let cumulativeCases = $cumulativeCases.toArray().map((td) => parseInt($(td).text())); // [int,...]
        let $unixs = $area.find("td:nth-child(1)"); // [$td,...]
        let unixs = $unixs.toArray().map((td) => parseInt($(td).attr("data-unix"))); // [int,...]

        window.graphData = [];
        unixs.forEach((unix, i) => {
            let graphCoordinate = { x: 0, y: 0 };
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

var sourcesRetrieving = setInterval(() => {
    if (sourcesRetrieved === sourcesAllRetrieved) {
        clearInterval(sourcesRetrieving);
        renderTable("Los Angeles Covid Hospitalizations", window.laCountyHospitals, 19500);
        renderTable("Los Angeles", window.laCounty, 10.04 * 100000000, 2489); // 8564
        renderTable("California", window.laTimesCalifornia);
        renderTable("New York", window.cnnNewYork);
        renderTable("US", window.johnHopkinsCountries);
        renderTable("Japan", window.johnHopkinsCountries);
        renderTable("Italy", window.johnHopkinsCountries);
        renderTable("55.3781, -3.436", window.johnHopkinsCountries); // United Kingdom

        // Standardize table heights
        // var heights = $(".area").map((i,area)=>$(area).height()).toArray();
        // var maxHeight = Math.max.apply(null, heights);
        // $(".area").height(maxHeight);

        // More graphs' dropdown's onchange
        $("#more-graphs").on("change", ev => {
            let value = ev.target.value;
            if (value.length === 0) return false;
            let modal = value; // "#modal-combined-graphs-1"
            $(modal).modal("show");
            $("#more-graphs").val("");
        });

        // Init graph views
        const arr$areas0 = [
            $(".area:has(.title[data-area='Los Angeles'])")
        ]
        zoomGraphs($("#modal-zoomed-graphs-0 canvas"), arr$areas0);
        $("#more-graphs").append(`<option value="#modal-zoomed-graphs-0">Zoomed Los Angeles</option>`);

        const arr$areas1 = [
            $(".area:has(.title[data-area='US'])"),
            $(".area:has(.title[data-area='Los Angeles'])"),
            $(".area:has(.title[data-area='California'])"),
            $(".area:has(.title[data-area='New York'])"),
            $(".area:has(.title[data-area='Japan'])"),
            $(".area:has(.title[data-area='Italy'])"),
        ]
        combineGraphs($("#modal-combined-graphs-1 canvas"), arr$areas1);
        $("#more-graphs").append(`<option value="#modal-combined-graphs-1">Interested Cities</option>`);


        const arr$areas2 = [
            $(".area:has(.title[data-area='Los Angeles'])"),
            $(".area:has(.title[data-area='Japan'])")
        ]
        combineGraphs($("#modal-combined-graphs-2 canvas"), arr$areas2);
        $("#more-graphs").append(`<option value="#modal-combined-graphs-2">LA and Japan</option>`);


        const arr$areas3 = [
            $(".area:has(.title[data-area='Los Angeles'])"),
            $(".area:has(.title[data-area='New York'])")
        ]
        combineGraphs($("#modal-combined-graphs-3 canvas"), arr$areas3);
        $("#more-graphs").append(`<option value="#modal-combined-graphs-3">LA and NY</option>`);
    }
}, 100);