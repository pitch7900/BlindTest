
	function onSubmitSecure(e){
        var formdata = $("form").serialize();

         $.post(window.location,formdata)
            .done(function (jsondata) {
                //get the Redirection from response and do the redirect
                location.href =jsondata.redirectTo;
            });

    }


  
