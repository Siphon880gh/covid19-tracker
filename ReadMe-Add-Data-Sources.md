How to Add Another Data Source
--

The idea is that a PHP file or Node Js file will be fired as a cronjob. Then the backend scrapes an online source (webpage, json, etc) for the daily cumulative cases of a city/state/country (if only new cases, then you can change the logic in the scraper to add the previous date's new cases to make a cumulative case number). The cumulative cases are stored in a json near where the scraper file is. Then the app pulls information from these different scraper's jsons to render numbers, graphs, tables, etc.

1. Add a folder into cronjobs/. That folder should have an endpoint.php or endpoint.js (depending which backend logic you want to use for scraping), and a data/daily-cumulative.json. Refer to the other cronjob folders for inspiration.

2. The file endpoint.php must have appropriate logic for scraping cumulative cases from an online source (webpage, json, etc). Most scraping logic has a $leftToken and $rightToken to parse an integer inbetween. Look at the other cronjob folders for inspiration.

3. At the app's index.js, increase the count at window.sourcesAllRetrieved. That's used to determined when the sources are all retrieved for the app.

4. Duplicate an IIFE function like:

```
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
```

It does not matter what you name the function because it'll be called right away, but there are a few things that do matter:
 - Make sure to have a consistent name for the *global data variable* window.laCounty (two places in the function).
 - Make sure that `await fetch` can load a valid URL to the folder you created in cronjobs.
 - Pay attention to the `area` and `title` values. For simplicity purposes, make sure they match names.

\5. The app renders the tables when the sources are all retrieved, by calling renderTable, so add this line:

```
renderTable("Los Angeles", window.laCounty);
```

Pay attention to the first and second parameters. They must match the area name and *global data variable*.


\6. If you want to add visitable links that appear with the table, you can add a .json file into the app's folder /urls/data, making sure the filename matches the area or title name.

\7. Finally, create the cronjob to fire the scraper endpoint.php or endpoint.js. Here's a suggested setting:

Minute:
59	

Hour:
8,10,12,15,20,21,22,23

Day:
*

Month:
*

Weekday:
*

Command:
cd /home/bse7iy70lkjz/public_html/tools/covid19/cronjobs/la-county; php -f endpoint.php # >> cronjobs.log;

Make sure in the command that the path to the endpoint file is correct. I recommend changing to an absolute path starting from /home. If things are not running right, you can tweak the minute and hour so that it runs in the next couple minutes, and you can remove the "#" hash symbol that makes the rest of the command a comment. By adding the >> cronjobs.log, it will log any errors into such a file where endpoint.php is.