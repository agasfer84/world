"use strict";

var url = "Controller.php";

function getUserCountryInfo() {
    var id = 1;
    var action = "actionCountryInfo";
    var params = JSON.stringify({});

    get(url, action, id, params).then(promiseRequest).then(
        function(data){

            for (var key in data) {

                if (!data.hasOwnProperty(key)) continue;

                var element_name = "usercountry_" + key;
                var value = data[key];
                setElementContent (element_name, value);
            }

        }
    );

}

function promiseRequest(data) {
    console.log(data);

    return data;
}

function setElementContent (element_name, value) {
    var element = document.getElementById(element_name);

    if (element) {
        element.innerText = value;
    }
}

function getMarketPositions() {
    var id = 1;
    var action = "actionMarketPositions";
    var params = JSON.stringify({});
    get(url, action, id, params).then(promiseRequest).then(
        function(data){
            getMarketDeals(data);
        }
    );
}

function getMarketDeals(positions) {
    // var positions_obj = arr.reduce(function(acc, cur, i) {
    //     acc[i] = cur;
    //     return acc;
    // }, {});
    var action = "actionMarketDeals";
    var body = JSON.stringify({"positions" : positions});
    post(url, action, body).then(promiseRequest);
}


// var body = JSON.stringify({
//     name: "Россия"
// });
// post(url, action, body).then(promiseRequest);

getUserCountryInfo();
getMarketPositions();