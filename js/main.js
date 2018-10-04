"use strict";

var url = "Controller.php";
var id = 1;

function getUserCountryInfo() {
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
    var action = "actionMarketPositions";
    var params = JSON.stringify({});
    get(url, action, id, params).then(promiseRequest).then(
        function(data){
            getMarketWorldPositions(data);
        }
    );
}

function getMarketDeals() {
    var action = "actionMarketDeals";
    var params = JSON.stringify({});
    get(url, action, id, params).then(promiseRequest);
}

function getMarketWorldPositions(positions) {
    var action = "actionMarketWorldPositions";
    var body = JSON.stringify({"positions" : positions});
    post(url, action, body).then(promiseRequest);
}

function getWorldProduction() {
    var action = "actionWorldProduction";
    var params = JSON.stringify({});
    get(url, action, id, params).then(promiseRequest);
}

function getMarketPrices() {
    var action = "actionMarketPrices";
    var params = JSON.stringify({});
    get(url, action, id, params).then(promiseRequest);
}


// var body = JSON.stringify({
//     name: "Россия"
// });
// post(url, action, body).then(promiseRequest);



getWorldProduction();
getMarketPositions();
getMarketPrices();
getMarketDeals();
getUserCountryInfo();