const fetch = require("node-fetch");

let url = "https://ix.cnn.io/dailygraphics/graphics/20200306-us-covid19/data.json";
async function a() {
	let response = await fetch(url).then(res=>res);
	// debugger;
	let obj = await response.json();
	let states = obj.data;

	let NewYork = states.find(state=>state.name==="New York");
	// console.log(NewYork); //name cases deaths deathspercases casesper100kresidents deathsper100kresidents

	let runner = require("child_process");
	let fetchSelfUrl = `${__dirname}/endpoint.php`;
	let args = `next=${NewYork.cases}`;
	runner.exec(`php ${fetchSelfUrl} ${args}`, function(err, phpResponse, stderr) {
		if(err) console.log(err); /* log error */
		console.log( phpResponse );
	});
}
a();