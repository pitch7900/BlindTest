function onSubmitSecure(e) {
   try {
    var formdata = $("form").serialize();
    var WaitingModal = new bootstrap.Modal(document.getElementById('WaitingModal'), {
        keyboard: false
      });
      WaitingModal.show();
      $('#pulselocation').addClass("dot-pulse");
    } catch(e)
    {}
    $.post(window.location, formdata)
        .done(function (jsondata) {
            //get the Redirection from response and do the redirect
            //Workaround for ios safari devices
            
            location.assign(jsondata.redirectTo);
            // console.log(jsondata.redirectTo);
            //If mobile device, then force a page reload as it seems the location.asign is not wokring properly on iOS Safari
            const is_mobile = 'ontouchend' in document;
            if (is_mobile) {
                document.location.reload();
            }
            
        });

}