const fetch = require("node-fetch");

let url = "https://ix.cnn.io/dailygraphics/graphics/20200306-us-covid19/data.json";
async function a() {
	let response = await fetch(url).then(res=>res);
	// debugger;
	let obj = await response.json();
	let states = obj.data;

	let NewYork = states.find(state=>state.name==="California");
	// console.log(NewYork); //name cases deaths deathspercases casesper100kresidents deathsper100kresidents

	let fetchSelfUrl = `${__dirname}/endpoint.php?next=${NewYork.cases}`;
	console.log(fetchSelfUrl);
	// return false;
	let response2 = await fetch(fetchSelfUrl).then(res=>res);
	let obj2 = await response2.text();
	let $$body = document.getElementsByTagName("body")[0];
	$$body.innerText = obj2;
	// debugger;
}
a();