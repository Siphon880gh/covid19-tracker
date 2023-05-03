/** These are for adding notes underneath the chart titles especially concerning when a data collection has stopped or has downgraded frequency */

let execAppendDisplay = () => {

    // Add notes to display at the charts here
    var at = "Los Angeles Covid Hospitalizations";
    var width = Math.floor($(`[data-area='${at}']`).closest(".area").width())
    $(`[data-area='${at}']`).append($(`<br/><div style='display:inline-block; overflow-wrap: break-word; font-size:1rem;font-weight: 400;  width:${width}px'><b>Downgraded:</b> No more daily reporting. LA County is now collecting hospitalization data from hospitals every Thursday. This chart and table has transitioned from daily to weekly on 03/16/23.</div>`));
}

/** Do not touch. This waits for when the chart js is done rendering charts */
let pollerLoadedCharts = setInterval(()=>{
    if($("[data-area]").length) {
        // console.log("*POLLING*")
        clearInterval(pollerLoadedCharts);
        setTimeout(execAppendDisplay, 100);
    }

}, 200)
