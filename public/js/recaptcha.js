
	function onSubmitSecure(e){
        var formdata = $("form").serialize();

         $.post(window.location,formdata)
            .done(function (jsondata) {
                //get the Redirection from response and do the redirect
                //location.href =jsondata.redirectTo;
                //Workaround for ios safari devices
                //https://stackoverflow.com/questions/31223216/why-isnt-window-location-href-not-forwarding-to-page-using-safari
                setTimeout(function(){document.location.href =jsondata.redirectTo;},250);
            });

    }


  
