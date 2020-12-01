function onSubmitSecure(e) {
    var formdata = $("form").serialize();

    $.post(window.location, formdata)
        .done(function (jsondata) {
            //get the Redirection from response and do the redirect
            //Workaround for ios safari devices
            
            location.assign(jsondata.redirectTo);
            //If mobile device, then force a page reload as it seems the location.asign is not wokring properly on iOS Safari
            const is_mobile = 'ontouchend' in document;
            if (is_mobile) {
                document.location.reload();
            }
            
        });

}