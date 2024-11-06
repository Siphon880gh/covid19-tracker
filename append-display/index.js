/** These are for adding notes underneath the chart titles especially concerning when a data collection has stopped or has downgraded frequency */

let execAppendDisplay = () => {

    // Add notes to display at the charts here
    var at = "Los Angeles Covid Hospitalizations";
    var width = Math.floor($(`[data-area='${at}']`).closest(".area").width())
    $(`[data-area='${at}']`).append($(`<br/><div style='display:inline-block; overflow-wrap: break-word; font-size:1rem;font-weight: 400;  width:${width}px'><b>Downgraded:</b> No more daily reporting. Is now weekly. LA County Public Health switched to collecting hospitalization data from hospitals every Thursday. This chart and table has transitioned from daily to weekly on 03/16/23.</div>`));
    // Add notes to display at the charts here

    var at = "Los Angeles";
    var width = Math.floor($(`[data-area='${at}']`).closest(".area").width())
    $(`[data-area='${at}']`).append($(`<br/><div style='display:inline-block; overflow-wrap: break-word; font-size:1rem;font-weight: 400;  width:${width}px'><b>Downgraded:</b> No more daily reporting. Is now weekly. LA County's official Covid-19 public page has transitioned to weekly reporting.</div>`));

    /* Massive scale of data affected */
    var width = Math.floor($("[data-area]").eq(1).closest(".area").width())
    var massive = "[data-area='California'], [data-area='New York'], [data-area='US'], [data-area='Japan'], [data-area='Italy'], [data-area='United Kingdom']";
    $(massive).each((i,el)=>{
        $(el).append($(`<br/><div style='display:inline-block; overflow-wrap: break-word; font-size:1rem;font-weight: 400;  width:${width}px'><b>Appears Paused:</b> John Hopkins University stopped collecting Covid case data for states and countries because of the waning pandemic. Have not adjusted to new data sources. May do so if there is public interest. Graphs and charts appear paused and will update as long as John Hopkins occasionally update their data.</div>`)); 
    });    
}

/** Do not touch. This waits for when the chart js is done rendering charts */
let pollerLoadedCharts = setInterval(()=>{
    if($("[data-area]").length) {
        // console.log("*POLLING*")
        clearInterval(pollerLoadedCharts);
        setTimeout(execAppendDisplay, 100);
    }

}, 200)
