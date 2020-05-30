var baseFusionAuthURL = 'https://idp.surfwijzer.nl';
var grantType = 'urn:ietf:params:oauth:grant-type:device_code';
var clientId = '82252ce6-ad4a-4a7f-8ff3-f7074f1a58dc';
var tokenEndpoint;
var expiresIn;
var intervalSeconds = 5;
var deviceCode;
var pollId;
var deviceAuthEndpoint;
var accessToken;

$("#connectBtn").click(function() {
	connectDevice();
});

// retrieve the device_authorization_endpoint and token_endpoint
$(document).ready(function() {

	fetch(baseFusionAuthURL + '/.well-known/openid-configuration',{
		mode: 'no-cors'
	})
		.then(response => response.json())
		.then(function(data) {
			deviceAuthEndpoint = data.device_authorization_endpoint;
			tokenEndpoint = data.token_endpoint;
		});


	$.ajax({
		type: 'GET',
		url: baseFusionAuthURL + '/.well-known/openid-configuration',
		datatype: 'json',
		success: function(data) {
			deviceAuthEndpoint = data.device_authorization_endpoint;
			tokenEndpoint = data.token_endpoint;
		}
	});
});

// call the device_authorization_endpoint, display the verification_uri and user_code, then start polling /token endpoint
function connectDevice() {
	$.ajax({
		type: 'POST',
		url: deviceAuthEndpoint,
		contentType: "application/x-www-form-urlencoded; charset=UTF-8",
		data: {
			'client_id': clientId,
			'scope': 'offline_access',
			'metaData.device.name': 'Demo TV app',
			'metaData.device.type': 'TV'
		},
		datatype: 'json',
		success: function(data) {
			expiresIn = data.expires_in;
			intervalSeconds = data.interval;
			deviceCode = data.device_code;

			// make user_code a little more readable
			let userCode = data.user_code;
			let ucLen = userCode.length / 2;
			userCode = userCode.substring(0,ucLen) + "-" + userCode.substring(ucLen);

			// Remove the schema to make it simpler on screen
			$("#device-url").text(data.verification_uri.replace("http://", "").replace("https://", ""));

			$("#user-code").text(userCode);
			$("#connect-device").hide();
			$("#sign-in").show();

			// generate a qr code from the verification_uri_complete
			$("#qrcode").empty();
			new QRCode(document.getElementById("qrcode"), {
			    text: data.verification_uri_complete,
			    width: 150,
			    height:150
			});
			$("#qrlink").attr("href", data.verification_uri_complete);

			pollForToken();
		},
		error: function(data) {
			$("#error-msg").show();
		}
	});
}

// poll the token endpoint, displaying success or error messages, and adjusting polling interval as appropriate
function pollForToken() {
	pollId = setInterval(function() {
		$.ajax({
			type: 'POST',
			url: tokenEndpoint,
			contentType: "application/x-www-form-urlencoded; charset=UTF-8",
			data: {'device_code': deviceCode, 'grant_type': grantType, 'client_id': clientId},
			datatype: 'json',
			success: function(data) {
		  		clearInterval(pollId);
		  		// use the access_token
		  		accessToken = data.access_token;
		  		$("#sign-in").hide();
		  		$("#success-msg").show();
		  		$("#fa-tut").attr("src", "https://www.youtube.com/embed/_ro3jH5Xkgo?autoplay=1");
			},
			error: function(data) {
				let err = $.parseJSON(data.responseText);
				if (err.error == 'slow_down') {
					clearInterval(pollId);
					// spec says to add 5 seconds to all subsequent calls if this happens
					intervalSeconds += 5;
					pollForToken();
				} else if (err.error == 'authorization_pending') {
					// keep polling
				} else {
					// an invalid request occurred, nothing to do but to stop and let user try again
					clearInterval(pollId);
					$("#sign-in").hide();
					$("#connect-device").show();
					$("#error-msg").show();
				}
			}
		});
	}, intervalSeconds * 1000);
}
