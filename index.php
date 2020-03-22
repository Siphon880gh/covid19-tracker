<!DOCTYPE html>
<?php 
// Source
// - Tells you when updated (Files after Feb 1 (UTC): once a day around 23:59 (UTC) AKA 5pm PST
// https://github.com/CSSEGISandData/COVID-19/tree/master/csse_covid_19_data/csse_covid_19_time_series
// https://github.com/CSSEGISandData/COVID-19/blob/master/csse_covid_19_data/csse_covid_19_time_series/time_series_19-covid-Confirmed.csv

// If tweaking Google Map:
// https://developers.google.com/maps/documentation/maps-static/intro#Zoomlevels

// "Los Angeles inaccurate" ticket:
// https://github.com/CSSEGISandData/COVID-19/issues/495
// Yeah, starting 3/10/20, they're only doing the US by states. Province/State is smallest subcategory they go, and only for some countries (dependencies of some nations and provinces/states of US, China, Canada, Australia, I believe).
?>
<html lang="en">
  <head>
   <title>Covid-19 / Coronavirus Growth Tracker</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no">

    <!-- Bootstrap, Font-Awesome  -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css">
    <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/font-awesome/5.12.1/css/all.min.css">

    <style><?php include("assets/css/index.css"); ?></style>

</head>
    <body>
        <div class="container-alt">
            <div id="title">Covid-19 Growth <i class="fas fa-first-aid"></i></div>
            <div id="desc">Find out how fast the virus is growing in your area. <a href="mailto:weffung@ucdavis.edu">Contact me</a> to request other areas.</div>
            <div id="credits">
                <span class="authored">Weng Fei Fung</span>
                <span class="source">Data pulled on a daily basis from John Hopkins University and Los Angeles Public Health. There are some differences in how LA County and John Hopkins report numbers so during the early days California numbers are 0 while Los Angeles numbers are 0-4 but that does not highly impact the graph or cumulative cases. If you are aware of any website or API that updates the number of cases for their county/state/province/country/region, you can contact me to have my app pull from their info daily as well, but keep in mind that it costs bandwidth on my server. John Hopkins university has stopped reporting county level numbers since 3/10/20, hence I added Los Angeles Public Health as a daily source of data to pull from.</span>
            </div>
            
            <p></p>
            <p></p>

            <div id="areas"></div>

            <template id="template-table">
                <div class="area">
                    <div class="links"></div>
                    <div class="title"></div>
                    <div style="width: 100%; height: 100%">
                        <canvas class="js-graph"></canvas>
                        <div class="formula"></div>
                    </div>
                    <table class="js-table table-bordered table-striped">
                    </table>
                </div>
            </template>

    <!-- jQuery, Moment JS, Chart JS, Regression, index.js -->
    <script src="https://code.jquery.com/jquery-2.1.4.min.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/moment.js/2.24.0/moment.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.3/Chart.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.3/Chart.min.css"></link>
    
    <script><?php include("assets/js/index.js"); ?></script>

    </body>
</html>