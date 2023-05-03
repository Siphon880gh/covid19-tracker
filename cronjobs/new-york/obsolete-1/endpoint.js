
const https = require("https");
const gunzip = require("zlib").createGunzip();

let url = "https://ix.cnn.io/dailygraphics/graphics/20200306-us-covid19/data.json";
async function a() {
	https.get(url, res=>{
		let json = "",
			obj = {},
			states = [];

		res.pipe(gunzip);

		gunzip.on('data', function(data){
			json += data.toString();
		}); // on data
	
		gunzip.on('end', function(){
			// Get States
			obj = JSON.parse(json);
			states = obj.data;

			// Find New York
			let NewYork = states.find(state=>state.name==="New York");
			// console.log(NewYork); //name cases deaths deathspercases casesper100kresidents deathsper100kresidents
		
			// endpoint PHP
			let runner = require("child_process");
			let fetchSelfUrl = `${__dirname}/endpoint.php`;
			let args = `next=${NewYork.cases}`;
			runner.exec(`php ${fetchSelfUrl} ${args}`, function(err, phpResponse, stderr) {
				if(err) console.log(err); /* log error */
				console.log( phpResponse );
			});

		}); // onend
	
	}); // https
} // a
a();