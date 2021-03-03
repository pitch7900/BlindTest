<?php

declare(strict_types=1);

namespace App\Authentication;

use App\Database\User;
use Carbon\Carbon;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;


/**
 * @author: Pierre Christensen
 * Manage authentication
 */
class Auth
{


         /**
     * IsAuthentified
     *
     * @return bool
     */
    public static function IsAuthentified(): bool
    {
        if (isset($_SESSION['userid'])) {
            return true;
        } else {
            return false;
        }
    }
    

    /**
     * setUserID : Set database user id for current session
     *
     * @param  mixed $userid
     * @return void
     */
    private static function setUserID($userid)
    {
        $_SESSION["userid"] = $userid;
    }

    /**
     * getUserId : Return database user id
     *
     * @return int
     */
    public static function getUserId(): int
    {
        return $_SESSION["userid"];
    }

    /**
     * getUserEmail : Return user email addresse
     *
     * @return string
     */
    public static function getUserEmail(): string
    {
        $user = User::find(Auth::getUserId());
        return  $user->email;
    }

    /**
     * getUserNickName : return user nickname
     *
     * @return string
     */
    public static function getUserNickName(): string
    {
        return  User::find(Auth::getUserId())->nickname;
    }

    /**
     * checkPassword : Check password for a given email address
     *
     * @param  mixed $email
     * @param  mixed $password
     * @return bool
     */
    public static function checkPassword(string $email, string $password): bool
    {
        $email = strtolower($email);

        $user = User::where([
            ['email', '=', $email]
        ])->first();

        if (is_null($user)) {
            unset($_SESSION["userid"]);
            return false;
        }
     
        if (password_verify($password, $user->password)) {
            Auth::setUserID($user->id);
        
            return true;
        }
       
        return false;
    }


    public static function setOriginalRequestedPage(string $page)
    {
        if (strcmp('/user/signout',$page)==0) {
            $_SESSION['OriginalRequestedPage'] = '/';
        } else {
            $_SESSION['OriginalRequestedPage'] = $page;
        }
        
    }

    public static function getOriginalRequestedPage(): string
    {
        if (isset($_SESSION['OriginalRequestedPage'])) {
            return $_SESSION['OriginalRequestedPage'];
        } else {
            return "";
        }
    }

 

    /**
     * signout user and remove all $_SESSION entries
     *
     * @return void
     */
    public static function signout()
    {
      
        unset($_SESSION['norobot']);
        unset($_SESSION["userid"]);
    }


    /**
     * sendValidationEmail : Send a validation email with link to validate email
     *
     * @param  mixed $email
     * @param  mixed $v4uuid
     * @return void
     */
    public static function sendValidationEmail(string $email, string $v4uuid)
    {
        $mail = new PHPMailer(true);
        try {
            //Server settings
            // $mail->SMTPDebug = SMTP::DEBUG_SERVER;                      // Enable verbose debug output
            $mail->isSMTP();                                            // Send using SMTP

            $mail->Host = $_ENV['SMTP_SERVER'];                    // Set the SMTP server to send through

            if (strcmp($_ENV['SMTP_USEAUTH'], "true") == 0) {
              
                $mail->SMTPAuth = true;                                   // Enable SMTP authentication
                $mail->Username =  $_ENV['SMTP_USERNAME'];                     // SMTP username
                $mail->Password = $_ENV['SMTP_PASSSWORD'];                               // SMTP password
            }
            if (strcmp($_ENV['SMTP_USESSL'], "true") == 0) {
             
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;         // Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` encouraged
            }
            $mail->Port = $_ENV['SMTP_PORT'];                                    // TCP port to connect to, use 465 for `PHPMailer::ENCRYPTION_SMTPS` above

            //Recipients
            $mail->setFrom($_ENV['SMTP_MAILFROM'], 'Blindtest mailer daemon');
            $mail->addAddress($email);               // Add a recipient
            $mail->addReplyTo($_ENV['SMTP_MAILFROM'], 'Blindtest mailer daemon');


            // Content
            $mail->isHTML(true);                                  // Set email format to HTML
            $mail->Subject = 'Mail validation for Blindtest';
            $mail->Body    = "<p>Hello,</p>" . "\r\n" .
                "<p>Please find below an URL to validate your email." . "</p>\r\n" .
                "<p>This link is valid for 15 minutes." . "</p>\r\n" .
                '<p><a href="' . $_ENV['PUBLIC_HOST'] . '/auth/checkmail/' . $v4uuid . '" >Validate my email address</a></p>';
            $mail->AltBody = "Hello," . "\r\n" .
                "Please find below an URL to validate your email." . "\r\n" .
                "This link is valid for 15 minutes." . "\r\n" .
                $_ENV['PUBLIC_HOST'] . "/auth/checkmail/" . $v4uuid;

            $mail->send();

        } catch (Exception $e) {
           
            die("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
        }
    }

    /**
     * sendAdminsitratorEmail : Send to the admin an information email for a new account creation
     *
     * @param  mixed $email
     * @return void
     */
    public static function sendAdminsitratorEmail(string $email)
    {
        $mail = new PHPMailer(true);
        try {
            //Server settings
            // $mail->SMTPDebug = SMTP::DEBUG_SERVER;                      // Enable verbose debug output
            $mail->isSMTP();                                            // Send using SMTP

            $mail->Host = $_ENV['SMTP_SERVER'];                    // Set the SMTP server to send through

            if (strcmp($_ENV['SMTP_USEAUTH'], "true") == 0) {
               
                $mail->SMTPAuth = true;                                   // Enable SMTP authentication
                $mail->Username =  $_ENV['SMTP_USERNAME'];                     // SMTP username
                $mail->Password = $_ENV['SMTP_PASSSWORD'];                               // SMTP password
            }
            if (strcmp($_ENV['SMTP_USESSL'], "true") == 0) {
               
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;         // Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` encouraged
            }
            $mail->Port = $_ENV['SMTP_PORT'];                                    // TCP port to connect to, use 465 for `PHPMailer::ENCRYPTION_SMTPS` above

            //Recipients
            $mail->setFrom($_ENV['SMTP_MAILFROM'], 'Blindtest mailer daemon');
            $mail->addAddress($_ENV['REGISTRATION_ADMIN_EMAIL']);               // Add a recipient
            $mail->addReplyTo($_ENV['SMTP_MAILFROM'], 'Blindtest mailer daemon');


            // Content
            $mail->isHTML(true);                                  // Set email format to HTML
            $mail->Subject = 'New account creation for Blindtest';
            $mail->Body    = "<p>Hello,</p>" . "\r\n" .
                "<p>A new account has been created for $email." . "</p>\r\n";

            $mail->AltBody = "Hello," . "\r\n" .
                "A new account has been created for $email." . "\r\n";


            $mail->send();

           
        } catch (Exception $e) {
            die("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
        }
    }

    /**
     * CurrentUserID
     *
     * @return int
     */
    public static function CurrentUserID(): int
    {

        if (isset($_SESSION['userid'])) {
            return intval($_SESSION['userid']);
        } else {
                return -1;
            
        }
    }

    /**
     * sendValidationEmail : Send a validation email with link to validate email
     *
     * @param  mixed $email
     * @param  mixed $v4uuid
     * @return void
     */
    public static function sendValidatorEmail(string $email, string $v4uuid)
    {
        $mail = new PHPMailer(true);
        try {
            //Server settings
            // $mail->SMTPDebug = SMTP::DEBUG_SERVER;                      // Enable verbose debug output
            $mail->isSMTP();                                            // Send using SMTP

            $mail->Host = $_ENV['SMTP_SERVER'];                    // Set the SMTP server to send through

            if (strcmp($_ENV['SMTP_USEAUTH'], "true") == 0) {
               
                $mail->SMTPAuth = true;                                   // Enable SMTP authentication
                $mail->Username =  $_ENV['SMTP_USERNAME'];                     // SMTP username
                $mail->Password = $_ENV['SMTP_PASSSWORD'];                               // SMTP password
            }
            if (strcmp($_ENV['SMTP_USESSL'], "true") == 0) {
              
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;         // Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` encouraged
            }
            $mail->Port = $_ENV['SMTP_PORT'];                                    // TCP port to connect to, use 465 for `PHPMailer::ENCRYPTION_SMTPS` above

            //Recipients
            $mail->setFrom($_ENV['SMTP_MAILFROM'], 'Blindtest mailer daemon');
            $mail->addAddress($_ENV['REGISTRATION_ADMIN_EMAIL']);               // Add a recipient
            $mail->addReplyTo($_ENV['SMTP_MAILFROM'], 'Blindtest mailer daemon');


            // Content
            $mail->isHTML(true);                                  // Set email format to HTML
            $mail->Subject = 'Mail validation for Blindtest';
            $mail->Body    = "<p>Hello,</p>" . "\r\n" .
                "<p>Please find below an URL to validate the email for user $email." . "</p>\r\n" .
                '<p><a href="' . $_ENV['PUBLIC_HOST'] . '/auth/validate/' . $v4uuid . '" >Validate this user email address</a></p>';
            $mail->AltBody = "Hello," . "\r\n" .
                "Please find below an URL to validate the email for user $email." . "\r\n" .
                $_ENV['PUBLIC_HOST'] . "/auth/validate/" . $v4uuid;

            $mail->send();

           
        } catch (Exception $e) {
            
            die("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
        }
    }

    /**
     * addUser : Add a user and Send an email link to validate the email address as valid
     *
     * @param  mixed $email
     * @param  mixed $encryptedpassword
     * @return bool
     */
    public static function addUser(string $email, string $encryptedpassword, string $nickname): bool
    {
        $email = strtolower($email);
        $user = User::where([
            ['email', 'like', $email]
        ])->first();

        if (is_null($user)) {
            $v4uuid_user = UUID::v4();
            $v4uuid_validator = UUID::v4();
            //current timestamp +15 minutes
            $timestamp =  Carbon::createFromTimestamp(time() + 15 * 60);

            User::updateOrCreate([
                'email' => $email,
                'password' => $encryptedpassword,
                'nickname' => $nickname,
                'emailchecklink' => $v4uuid_user,
                'emailchecklinktimeout' => $timestamp, //curent time + 15 minutes
                'emailchecked' => false,
                'resetpasswordlink' => null,
                'resetpasswordlinktimeout' => null,
                'approvaleuuid' => $v4uuid_validator,
                'adminapproved' => false

            ]);
            if (strcmp($_ENV['REGISTRATION_REQUIRE_APPROVAL'], "true") == 0) {
                Auth::sendValidatorEmail($email, $v4uuid_validator);
            } else {
                $user = User::where([
                    ['email', '=', $email]
                ])->first();
                $user->adminapproved = true;
                Auth::sendAdminsitratorEmail($email);
                Auth::sendValidationEmail($email, $v4uuid_user);
            }
        }
        return true;
    }

    /**
     * validateEmail : check the email is valid (Answer of the addUser email)
     *
     * @param  mixed $uuid
     * @return bool
     */
    public static function validateEmail(string $uuid): bool
    {
        $user = User::where([
            ['emailchecklink', 'like', $uuid]
        ])->first();

        if (!is_null($user)) {
            $time = $user->emailchecklinktimeout;

            $validationtime = Carbon::createFromFormat(Carbon::DEFAULT_TO_STRING_FORMAT, $time);
            $currenttime =  Carbon::createFromTimestamp(time());
           
            if ($validationtime->gte($currenttime)) {
                //Mail is checked, we can trust this user
               
                $user->emailchecked = true;
                $user->save();
                Auth::setUserID($user->id);
                return true;
            } else {
               
                $user->emailchecked = false;
                $user->save();
                unset($_SESSION["userid"]);
                return false;
            }
        }
        return false;
    }

    /**
     * setNickname : change nickname for current user's session
     *
     * @param  mixed $nickname
     * @return void
     */
    public static function setNickname(string $nickname)
    {
        $user = User::find(Auth::getUserId());
        $user->nickname = $nickname;
        $user->save();
    }


     /**
     * return current user name
     * @return string
     */
    public static function CurrentUserName()
    {
        return User::getUserName(Auth::CurrentUserID());
    }
    
    /**
     * resetPassword : set a new password for the uuid passed
     *
     * @param  mixed $uuid
     * @param  mixed $encryptedpassword
     * @return void
     */
    public static function resetPassword(string $uuid, string $encryptedpassword): void
    {
        if (Auth::checkUUIDpasswordreset($uuid)) {
            $user = User::where([
                ['resetpasswordlink', 'like', $uuid]
            ])->first();
            //reset the password
            $user->password = $encryptedpassword;
            //Set the reset link to now, for avoiding attacks
            $user->resetpasswordlinktimeout = Carbon::createFromTimestamp(time());
            $user->save();
        }
    }

    /**
     * changePassword
     *
     * @param  mixed $newencryptedpasssword
     * @return void
     */
    public static function changePassword(string $newencryptedpasssword)
    {
        User::find(Auth::getUserId())->password = $newencryptedpasssword;
    }

    /**
     * checkUUIDpasswordreest : check if UUID is valid
     *
     * @param  mixed $uuid
     * @return bool
     */
    public static function checkUUIDpasswordreset(string $uuid): bool
    {
        $user = User::where([
            ['resetpasswordlink', 'like', $uuid]
        ])->first();
        if (!is_null($user)) {
            $time = $user->resetpasswordlinktimeout;
            $validationtime = Carbon::createFromFormat(Carbon::DEFAULT_TO_STRING_FORMAT, $time);
            $currenttime =  Carbon::createFromTimestamp(time());
            if ($validationtime->gte($currenttime)) {
                return true;
            }
        }
        return false;
    }

    /**
     * sendResetPasswordLink : Create a UUID for password reset with 15 min validity and send the email with reset link
     *
     * @param  mixed $email
     * @return bool
     */
    public static function sendResetPasswordLink(string $email): bool
    {
        $email = strtolower($email);
        $user = User::where([
            ['email', 'like', $email]
        ])->first();

        if (!is_null($user)) {
            $v4uuid = UUID::v4();

            //current timestamp +15 minutes
            $timestamp = Carbon::createFromTimestamp(time() + 15 * 60);
            $user->resetpasswordlink = $v4uuid;
            $user->resetpasswordlinktimeout = $timestamp;
            $user->save();
            $mail = new PHPMailer(true);
            try {
                //Server settings
                // $mail->SMTPDebug = SMTP::DEBUG_SERVER;                      // Enable verbose debug output
                $mail->isSMTP();                                            // Send using SMTP

                $mail->Host = $_ENV['SMTP_SERVER'];                    // Set the SMTP server to send through

                if (strcmp($_ENV['SMTP_USEAUTH'], "true") == 0) {
                    
                    $mail->SMTPAuth = true;                                   // Enable SMTP authentication
                    $mail->Username =  $_ENV['SMTP_USERNAME'];                     // SMTP username
                    $mail->Password = $_ENV['SMTP_PASSSWORD'];                               // SMTP password
                }
                if (strcmp($_ENV['SMTP_USESSL'], "true") == 0) {
                   
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;         // Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` encouraged
                }
                $mail->Port = $_ENV['SMTP_PORT'];                                    // TCP port to connect to, use 465 for `PHPMailer::ENCRYPTION_SMTPS` above

                //Recipients
                $mail->setFrom($_ENV['SMTP_MAILFROM'], 'Blindtest mailer daemon');
                $mail->addAddress($email);               // Add a recipient
                $mail->addReplyTo($_ENV['SMTP_MAILFROM'], 'Blindtest mailer daemon');


                // Content
                $mail->isHTML(true);                                  // Set email format to HTML
                $mail->Subject = 'Password reset for Blindtest';
                $mail->Body = "<p>Hello,</p>" . "\r\n" .
                    "<p>Please find below an URL to reset your password." . "</p>\r\n" .
                    "<p>This link is valid for 15 minutes." . "</p>\r\n" .
                    '<p><a href="' . $_ENV['PUBLIC_HOST'] . '/auth/resetpassword/' . $v4uuid . '" >Validate my email address</a>' . "</p>\r\n" .
                    "<br>" . "\r\n" .
                    "<p>Note : If you're not at the origin of this password reset, simply ignore this email</p>" . "\r\n";
                $mail->AltBody = "Hello," . "\r\n" .
                    "Please find below an URL to reset your password." . "\r\n" .
                    "This link is valid for 15 minutes." . "\r\n" .
                    $_ENV['PUBLIC_HOST'] . "/auth/resetpassword/" . $v4uuid
                    . "\r\n" . "\r\n" .
                    "Note : If you're not at the origin of this password reset, simply ignore this email" . "\r\n";;

                $mail->send();

            } catch (Exception $e) {
              
                die("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
            }
        }
        return true;
    }
}
