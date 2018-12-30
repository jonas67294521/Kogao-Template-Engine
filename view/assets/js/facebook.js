/**
 * Kogao Software
 * @package modules.facebook.login
 * @view modules/facebook
 */

function fb_login() {
    FB.login(function(e) {
        e.authResponse ? (access_token = e.authResponse.accessToken, user_id = e.authResponse.userID, FB.api("/me?fields=id,name,email,first_name,last_name,birthday,gender", function(e) {
            var user_id = e.id;
            var user_name = e.name;
            //jQuery POST
        })) : console.log("Not Login...")
    }, {
        scope: "public_profile,email"
    })
}
window.fbAsyncInit = function() {
    FB.init({
        appId: facebook_api_id,
        cookie: !0,
        xfbml: !0,
        version: "v2.2"
    }), FB.AppEvents.logPageView()
},
    function(e, n, t) {
        var a, i = e.getElementsByTagName(n)[0];
        e.getElementById(t) || ((a = e.createElement(n)).id = t, a.src = "https://connect.facebook.net/en_US/sdk.js", i.parentNode.insertBefore(a, i))
    }(document, "script", "facebook-jssdk");