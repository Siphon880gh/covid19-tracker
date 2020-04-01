const fetch = require("node-fetch");
// var express = require('express');
// var php = require("node-php"); 
// var path = require("path"); 

// var app = express();

// app.use("/", php.cgi("./"));
// app.listen(9090);
// console.log("Server listening");


let url = "https://ix.cnn.io/dailygraphics/graphics/20200306-us-covid19/data.json";
async function a() {
	let response = await fetch(url).then(res=>res);
	// debugger;
	let obj = await response.json();
	let states = obj.data;

	let NewYork = states.find(state=>state.name==="New York");
	// console.log(NewYork); //name cases deaths deathspercases casesper100kresidents deathsper100kresidents


	let runner = require("child_process");
	// let fetchSelfUrl = `${__dirname}/endpoint.php?next=${NewYork.cases}`;
	let fetchSelfUrl = `${__dirname}/endpoint.php`;
	let args = `next=${NewYork.cases}`;
	runner.exec(`php ${fetchSelfUrl} ${args}`, function(err, phpResponse, stderr) {
		if(err) console.log(err); /* log error */
		console.log( phpResponse );
	});

	// console.log(fetchSelfUrl);
	// // return false;
	// let response2 = await fetch(fetchSelfUrl).then(res=>res);
	// let obj2 = await response2.text();
	// let $$body = document.getElementsByTagName("body")[0];
	// $$body.innerText = obj2;
	// debugger;
}
a();