<!DOCTYPE html>
<?php 
// Source
// - Tells you when updated (Files after Feb 1 (UTC): once a day around 23:59 (UTC) AKA 5pm PST
// https://github.com/CSSEGISandData/COVID-19/tree/master/csse_covid_19_data/csse_covid_19_time_series
// https://github.com/CSSEGISandData/COVID-19/blob/master/csse_covid_19_data/csse_covid_19_time_series/time_series_19-covid-Confirmed.csv

// Other possible sources:
// https://covidtracking.com/data/
// https://covidtracking.com/api/states/info
// https://www.worldometers.info/coronavirus/country/us/

// If tweaking Google Map:
// https://developers.google.com/maps/documentation/maps-static/intro#Zoomlevels

// "Los Angeles inaccurate" ticket:
// https://github.com/CSSEGISandData/COVID-19/issues/495
// Yeah, starting 3/10/20, they're only doing the US by states. Province/State is smallest subcategory they go, and only for some countries (dependencies of some nations and provinces/states of US, China, Canada, Australia, I believe).
//
// "California inaccurate" ticket
// https://github.com/CSSEGISandData/COVID-19/issues/1505
// Staring 3/24, they're only doing by country

?>
<html lang="en">
  <head>
   <title>Daily Covid-19 Tracking</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no">

    <!-- Meta tags for thumbnails, favico etc -->
    <meta property='og:title' content="Covid19 Tracker" />
    <meta property='og:image' content='https://wengindustry.com/tools/covid19/assets/icons/Icons8-Ios7-Healthcare-Virus--as-site-thumbnail.png' />
    <meta property='og:description' content="Track Covid19 hospitalizations and cases in Los Angeles and select areas." />
    <link rel="icon" href="assets/icons/Icons8-Ios7-Healthcare-Virus.ico">

    <!-- Bootstrap, Font-Awesome  -->
    <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css">
    <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/font-awesome/5.12.1/css/all.min.css">

    <!-- Icons -->

    <!-- Site Assets -->
    <link rel="stylesheet" href="assets/css/index.css">

</head>
    <body>
        <div class="container-alt">
            <div id="title">Daily Covid-19 Tracking <i class="fas fa-first-aid"></i></div>
            <div id="desc" style="margin-top:20px;">Quick snapshots of the Covid crisis in the hospitals and the population. Want to request an area? <a href="mailto:weffung@ucdavis.edu">Contact me</a>.
            <br/>
            <br/>Updated 1/9/22: Covid beds percent shows when hovering mouse cursor over a graph point.
            <br/>Updated 1/6/22: It's clear that Covid is lasting years, so I changed the X axis date format from MM/DD to MM/DD/YY.
            <br/>Updated 7/11/21: Now with Covid beds column.
            <br/>Updated 5/15/21: Now with cumulative/non-cumulative graph views and panning/zooming ability. Hold SHIFT and drag to pan. Use gestures/mouse to zoom. Full screen graphs option available on wide screens.

        </div>
            <div id="credits">
                <span class="authored">By Weng Fei Fung</span>
            </div>
            <div class="self-promo" style="font-size:1.3rem;">
                <span><a target="_blank" href="https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=wff1012%40hotmail.com&currency_code=USD"><i class="fab fa-paypal"></i> Help me buy a cup of coffee - thank you</a>. </br><a target="_blank" href="https://www.linkedin.com/in/weng-fung/"><i class="fab fa-linkedin"></i> Available for hire for web development / software engineering - thank you.</a></span>
            </div>
            
            <div class="ui-graph-mode">
            <?php
                echo "<span>Switch Graph View: </span>";
                $cumulativeButton = '<button class="btn btn-default p-sm-px" onclick="window.location.href=\'?mode=noncumulative\';">Non-cumulative</button>';
                $nonCumulativeButton = '<button class="btn btn-default p-sm-px" onclick="window.location.href=\'?mode=cumulative\';">Cumulative</button>';
                $defaultModeButton = $cumulativeButton;
                if(isset($_GET["mode"])) {
                    if($_GET["mode"]==="noncumulative") {
                        echo $nonCumulativeButton;
                    } else {
                        echo $cumulativeButton;
                    }
                } else {
                    echo $defaultModeButton;
                }
            ?>
            </div>

            <div id="areas">
                <div style="font-size:2.5rem;">
                    <i class="fa fa-spinner fa-pulse"></i>
                    <span>Loading</span>
                </div>
            </div>

            <template id="template-table">
                <div class="area">
                    <div class="area-inner">
                        <div class="note"></div>
                        <div class="links"></div>
                        <div class="title" data-area=""></div>
                        <div class="not-table" style="width: 100%; height: 100%">
                            <canvas class="js-graph"></canvas>
                            <div class="reset-zoom-wrapper">
                                <a class="reset-btn" onclick="$(event.target).data('ctx').resetZoom();" href="javascript:void(0)">Reset Zoom & Pan</a>
                                <span class="full-screen-btn-separator">&nbsp;|&nbsp;</span>
                                <a class="full-screen-btn" onclick="var $this = $(event.target); var $graphWrapper = $this.closest('.not-table'), $graph=$graphWrapper.find('canvas'), $tile = $graphWrapper.closest('.area'); $graphWrapper.css('width', '90vw'); $graph.attr('width', '3000').attr('height', '1000'); $tile.get(0).scrollIntoView(); $this.prev().remove(); $this.remove();" href="javascript:void(0)">Full screen</a>
                            </div>
                            <div class="math">
                                <a onclick="event.preventDefault(); $(this).hide(); $(this).next().removeClass('hide');" href="javascript:void(0)" style="float:right; margin:5px;">Show Prediction Model</a>
                                <div class="inner hide">
                                    <div class="formula"></div>
                                    <div class="population-line"></div>
                                    <div class="population-density-line"></div>
                                </div>
                            </div>
                        </div>
                        <table class="js-table table-bordered table-striped">
                        </table>
                    </div>
                </div>
            </template>

            <!-- Modal -->
            <div id="modal-zoomed-graphs-0" class="modal fade" role="dialog">
                <div class="modal-dialog">

                    <!-- Modal content-->
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal">×</button>
                            <h4 class="modal-title">Zoomed Graph</h4>
                            <small>
                            </p>
                            <p>Shows only the last two weeks so you can more easily eye the graph for peaks or waves.<br/>Contact Weng Fei Fung if you want more graphs.</p>
                            </small>
                        </div>
                        <div class="modal-body">
                            <!-- Insert here -->
                            <canvas></canvas>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                        </div> <!--/ footer -->
                    </div> <!--/ modal-content -->

                </div> <!--/ dialog -->
            </div> <!--/ modal -->


            <!-- Modal -->
            <div id="modal-combined-graphs-1" class="modal fade" role="dialog">
                <div class="modal-dialog">

                    <!-- Modal content-->
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal">×</button>
                            <h4 class="modal-title">Compare Growth Curves</h4>
                            <small>
                            </p>
                            <p>Compare growth curves of interested areas. Contact Weng Fei Fung if you want more comparisons.</p>
                            </small>
                        </div>
                        <div class="modal-body">
                            <!-- Insert here -->
                            <canvas></canvas>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                        </div> <!--/ footer -->
                    </div> <!--/ modal-content -->

                </div> <!--/ dialog -->
            </div> <!--/ modal -->


            <!-- Modal -->
            <div id="modal-combined-graphs-2" class="modal fade" role="dialog">
                <div class="modal-dialog">

                    <!-- Modal content-->
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal">×</button>
                            <h4 class="modal-title">Compare Growth Curves</h4>
                            <small>
                            </p>
                            <p>Compare growth curves of interested areas. Contact Weng Fei Fung if you want more comparisons.</p>
                            </small>
                        </div>
                        <div class="modal-body">
                            <!-- Insert here -->
                            <canvas></canvas>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                        </div> <!--/ footer -->
                    </div> <!--/ modal-content -->

                </div> <!--/ dialog -->
            </div> <!--/ modal -->


            <!-- Modal -->
            <div id="modal-combined-graphs-3" class="modal fade" role="dialog">
                <div class="modal-dialog">

                    <!-- Modal content-->
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal">×</button>
                            <h4 class="modal-title">Compare Growth Curves</h4>
                            <small>
                            </p>
                            <p>Compare growth curves of interested areas. Contact Weng Fei Fung if you want more comparisons.</p>
                            </small>
                        </div>
                        <div class="modal-body">
                            <!-- Insert here -->
                            <canvas></canvas>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                        </div> <!--/ footer -->
                    </div> <!--/ modal-content -->

                </div> <!--/ dialog -->
            </div> <!--/ modal -->

            <div id="nav-global-a" style="position:absolute; top:0; right: 10px; z-index:1; background-color:white;">
                <a href="javascript:void(0)" onclick='$("#nav-global-b").css("display", "block");'><</a>
            </div>

            <div id="nav-global-b" style="position:absolute; top:0; right: 10px; display:none; z-index:2; background-color:white;">
                <label for="more-graphs">More Graphs:</label>
                    <select id="more-graphs">
                        <option value=""></option>
                    </select>
                    <span>&nbsp;</span>
                    <a href="javascript:void(0)" onclick='$("#nav-global-b").css("display", "none");'>></a>
            </div>

    <!-- jQuery, Bootstrap, Moment JS, Chart JS, Regression, index.js -->
    <script src="//code.jquery.com/jquery-2.1.4.min.js"></script>
    <script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.4/js/bootstrap.min.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/moment.js/2.24.0/moment.min.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.3/Chart.min.js"></script>
    <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.3/Chart.min.css"></link>
    <!-- <script src="//npmcdn.com/Chart.Zoom.js@0.3.0/Chart.Zoom.min.js"></script> -->
    <script src="https://cdn.jsdelivr.net/npm/hammerjs@2.0.8"></script>
    <script src="//cdn.jsdelivr.net/npm/chartjs-plugin-zoom@0.7.7/dist/chartjs-plugin-zoom.min.js"></script>
    
    <script><?php include("assets/js/index.js"); ?></script>

    <div class="source" style="margin-top:10px;"><a href="javascript:void()" onclick="$(this).next().toggle();">Data collection notes.</a><span style="display:none;"> Los Angeles reflects all of Los Angeles county including Hollywood, Lincoln Heights, Long Beach, Monterey Park, Pasadena, etc. 7/3/20-7/5/20 do not have reported numbers from LA County because their reporting system was down to improve it. Other Los Angeles notes: Monday/Tuesday numbers tend to spike up as weekend results catch up. Notes on New York: The stats can come in as late as 1AM EST next day. General Notes: Data automatically pulled on a daily basis from Los Angeles County Public Health, LA Times, and John Hopkins University. There are some differences in how LA County and John Hopkins report numbers so during the early days California numbers are 0 while Los Angeles numbers are 0-4 but that does not highly impact the graph or cumulative cases. John Hopkins University has stopped reporting county level numbers since 3/10/20, hence I added Los Angeles Public Health as a daily source of data to pull from. On 3/24/20, John Hopkins Universty has stopped reporting at a state level, so state information is no longer available on 3/24/20 and onwards, so we have to refer to the US table. Data is updated everyday at 8am, 10am, 12pm, 3pm, 5pm, and 8pm PST. If you know a website or API updating the number of cases for your county / state / province / country / region, you can contact me to have my app pull from their info daily, but please keep in mind that it costs bandwidth on my server.</span></div>

    </body>
</html>