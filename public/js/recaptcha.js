
	function onSubmitSecure(e){
        console.log("Send informations");
        var formdata = $("form").serialize();
        console.log(formdata);
        $.post(window.location,formdata, function(data) {
            console.log(data);
            $('body').html(data);
          });
    }


  
