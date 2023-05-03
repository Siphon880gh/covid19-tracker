
const regression = require('regression');
//https://tom-alexander.github.io/regression-js/
//https://github.com/Tom-Alexander/regression-js

function getFormula_Linear(yPoints) {
    const xyPoints = yPoints.map((y, i)=>[i, y]); // => [0, Y0], [1, Y1], [2, Y2]...
    return regression.linear(xyPoints).string;
}

function getFormula_Polynomial(yPoints) {
    const xyPoints = yPoints.map((y, i)=>[i, y]); // => [0, Y0], [1, Y1], [2, Y2]...
    return regression.polynomial(xyPoints).string;
}

// Validation
if(process.argv.length<3) {
    console.warn('Error: You need to provide csv of curve points "int, int, int...." Surround that argument in double quotation marks so can be parsed as one argument.');
    return false;
}
// Sanitize
let points_str = process.argv[2];
points_str = points_str.replace(/[^0-9,]/g, "");
let yPoints = points_str.split(",").map(str=>parseInt(str));


console.log("Best fit line: " + getFormula_Linear(yPoints).replace("y = ", "") + 
            "<br/>Best fit curve: " + getFormula_Polynomial(yPoints).replace("y = ", "")
            );


// console.log( JSON.stringify( 
//     {
//         "linear":getFormula_Linear(yPoints),
//         "polynomial":getFormula_Polynomial(yPoints),
//         "debug": {
//             "Y points": yPoints,
//             "XY points for linear and polynomial": yPoints.map((y, i)=>[i, y])
//         }
//     }));