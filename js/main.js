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


// var body = JSON.stringify({
//     name: "Россия"
// });
// post(url, action, body).then(promiseRequest);

getUserCountryInfo();