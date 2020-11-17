/**
 * Check password and confirmation match 
 */
$('[id^=confirmpassword],[id^=password]').bind("keyup change", function (event) {
    var confirm_password = $(event.target).closest('form').find('[id^=confirmpassword]').val();
    var password = $(event.target).closest('form').find('[id^=password]').val();
    if (password !== confirm_password) {

        $(event.target).closest('form').find('[id^=password]').css("border-color", "red");
        $(event.target).closest('form').find('[id^=confirmpassword]').css("border-color", "red");
        $(event.target).closest('form').find(':submit').attr("disabled", "disabled");
    } else {
        $(event.target).closest('form').find('[id^=password]').css("border-color", "#28a745");
        $(event.target).closest('form').find('[id^=confirmpassword]').css("border-color", "#28a745");
        $(event.target).closest('form').find(':submit').removeAttr("disabled");
    }
});