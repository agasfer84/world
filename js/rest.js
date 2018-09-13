"use strict";

function get(url, action, id, params) {

    return new Promise(function(success, error) {

        var xmlhttp = new XMLHttpRequest();

        xmlhttp.onreadystatechange = function () {
            if (xmlhttp.readyState == XMLHttpRequest.DONE) {
                if (xmlhttp.status == 200) {

                    success(JSON.parse(xmlhttp.response));

                } else {
                    error(new Error("Request failed: " + request.statusText));
                }

            }
        };

        xmlhttp.open("GET", url + "?action=" + action + "&id=" + id + "&params=" + params, true);
        xmlhttp.send();

    });
}