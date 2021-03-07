function onSubmitSecure(e) {
    var formdata = $("form").serialize();
    var WaitingModal = new bootstrap.Modal(document.getElementById('WaitingModal'), {
        keyboard: false
      });
      WaitingModal.show();
    $.post(window.location, formdata)
        .done(function (jsondata) {
            //get the Redirection from response and do the redirect
            //Workaround for ios safari devices
            
            location.assign('/auth/signinconfirmation.html');
            console.log(jsondata.redirectTo);
            //If mobile device, then force a page reload as it seems the location.asign is not wokring properly on iOS Safari
            const is_mobile = 'ontouchend' in document;
            if (is_mobile) {
                document.location.reload();
            }
            
        });

}