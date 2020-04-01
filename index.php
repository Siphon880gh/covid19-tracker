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
   <title>Covid-19 / Coronavirus Growth Tracker</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no">

    <!-- Bootstrap, Font-Awesome  -->
    <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css">
    <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/font-awesome/5.12.1/css/all.min.css">

    <style><?php include("assets/css/index.css"); ?></style>

</head>
    <body>
        <div class="container-alt">
            <div id="title">Covid-19 Growth <i class="fas fa-first-aid"></i></div>
            <div id="desc">Find out how fast the virus is growing in your area. <a href="mailto:weffung@ucdavis.edu">Contact me</a> to request other areas.</div>
            <div id="credits">
                <span class="authored">Weng Fei Fung</span>
                <span class="source">Data automatically pulled on a daily basis from Los Angeles County Public Health, LA Times, and John Hopkins University<a href="javascript:void()" onclick="$(this).next().toggle();">...</a><span style="display:none;"> There are some differences in how LA County and John Hopkins report numbers so during the early days California numbers are 0 while Los Angeles numbers are 0-4 but that does not highly impact the graph or cumulative cases. John Hopkins University has stopped reporting county level numbers since 3/10/20, hence I added Los Angeles Public Health as a daily source of data to pull from. On 3/24/20, John Hopkins Universty has stopped reporting at a state level, so state information is no longer available on 3/24/20 and onwards, so we have to refer to the US table. Data is updated everyday at 8am, 10am, 12pm, 3pm, 5pm, and 8pm PST. If you know a website or API updating the number of cases for your county / state / province / country / region, you can contact me to have my app pull from their info daily, but please keep in mind that it costs bandwidth on my server.</span></span>
            </div>
            
            <br/>

            <div id="areas"></div>

            <template id="template-table">
                <div class="area">
                    <div class="note"></div>
                    <div class="links"></div>
                    <div class="title" data-area=""></div>
                    <div style="width: 100%; height: 100%">
                        <canvas class="js-graph"></canvas>
                        <div class="formula"></div>
                    </div>
                    <table class="js-table table-bordered table-striped">
                    </table>
                </div>
            </template>

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
                            <p>Compare growth curves of interested areas</p>
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
                            <p>Compare growth curves of interested areas</p>
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
                            <p>Compare growth curves of interested areas</p>
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

            <div id="nav-global" style="position:absolute; top:10px; right:10px;">
                <span id="label-compare">Compare:</span>
                <a data-toggle="modal" href="#modal-combined-graphs-1"><i class="fa fa-chart-line"></i>1</a>&nbsp;
                <a data-toggle="modal" href="#modal-combined-graphs-2"><i class="fa fa-chart-line"></i>2</a>&nbsp;
                <a data-toggle="modal" href="#modal-combined-graphs-3"><i class="fa fa-chart-line"></i>3</a>&nbsp;
            </div>

    <!-- jQuery, Bootstrap, Moment JS, Chart JS, Regression, index.js -->
    <script src="//code.jquery.com/jquery-2.1.4.min.js"></script>
    <script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.4/js/bootstrap.min.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/moment.js/2.24.0/moment.min.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.3/Chart.min.js"></script>
    <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.3/Chart.min.css"></link>
    
    <script><?php include("assets/js/index.js"); ?></script>

    </body>
</html>