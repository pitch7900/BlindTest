<?php

declare(strict_types=1);

namespace App\Authentication;

use App\Database\User;
use Carbon\Carbon;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Psr\Log\LoggerInterface;

/**
 * @author: Pierre Christensen
 * Manage authentication
 */
class Auth
{    
    /**
     * logger
     *
     * @var mixed
     */
    private $logger;

    /**
     * __construct
     *
     * @return void
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
        if (isset($_SESSION['authentified'])) {
            $this->logger->debug('Auth::__contruct SuperGlobal Variable Session authentified is set to ' . $_SESSION['authentified']);
        } else {
            $this->setAuthentified(false);
        }
    }

    /**
     * unvalidate : Logout user. Remove $_SESSION variable for authentication
     *
     * @return void
     */
    private function unvalidate()
    {
        $this->setAuthentified(false);
        unset($_SESSION["userid"]);
    }
    
    /**
     * getSessionId : Get PHP Session ID
     *
     * @return string
     */
    public function getSessionId(): string
    {
        return session_id();
    }
    
    /**
     * setUserID : Set database user id for current session
     *
     * @param  mixed $userid
     * @return void
     */
    private function setUserID($userid)
    {
        $_SESSION["userid"] = $userid;
    }
    
    /**
     * getUserId : Return database user id
     *
     * @return int
     */
    public function getUserId(): int
    {
        return $_SESSION["userid"];
    }
    
    /**
     * getUserEmail : Return user email addresse
     *
     * @return string
     */
    public function getUserEmail(): string
    {
        $user = User::find($this->getUserId());
        return  $user->email;
    }
    
    /**
     * getUserNickName : return user nickname
     *
     * @return string
     */
    public function getUserNickName(): string
    {
        return  User::find($this->getUserId())->nickname;
    }
    
    /**
     * checkPassword : Check password for a given email address
     *
     * @param  mixed $email
     * @param  mixed $password
     * @return bool
     */
    public function checkPassword(string $email, string $password): bool
    {
        $email = strtolower($email);
        $this->logger->debug("Auth::checkPasswod() should check password for " . $email);
        $user = User::where([
            ['email', '=', $email]
        ])->first();

        if (!$user) {
            $this->logger->debug("Auth::checkPasswod() User not found : ");
            $this->unvalidate();
            return false;
        }
        $this->logger->debug("Auth::checkPasswod() User found : " . $user->id);
        if (password_verify($password, $user->password)) {
            $this->setUserID($user->id);
            $this->setAuthentified(true);
            $this->logger->debug("Auth::checkPasswod() User " . $user->email . " authentification OK");
            return true;
        }
        $this->setAuthentified(false);
        return false;
    }
    
    /**
     * getAuthentified : return if user is authentified or not 
     *
     * @return void
     */
    public function getAuthentified()
    {
        return $_SESSION['authentified'];
    }
    
    /**
     * setAuthentified : Change the authentification satus
     *
     * @param  mixed $authentified
     * @return void
     */
    private function setAuthentified(bool $authentified)
    {
        $_SESSION['authentified'] = $authentified;
    }
    
    /**
     * signout user and remove all $_SESSION entries
     *
     * @return void
     */
    public function signout()
    {
        $this->unvalidate();
    }
    
    
    /**
     * sendValidationEmail : Send a validation email with link to validate email
     *
     * @param  mixed $email
     * @param  mixed $v4uuid
     * @return void
     */
    public function sendValidationEmail(string $email,string $v4uuid){
        $mail = new PHPMailer(true);
            try {
                //Server settings
                // $mail->SMTPDebug = SMTP::DEBUG_SERVER;                      // Enable verbose debug output
                $mail->isSMTP();                                            // Send using SMTP

                $mail->Host = $_ENV['SMTP_SERVER'];                    // Set the SMTP server to send through

                if (strcmp($_ENV['SMTP_USEAUTH'], "true") == 0) {
                    $this->logger->debug("Auth::sendValidationEmail() Use SMTP Auth for email");
                    $mail->SMTPAuth = true;                                   // Enable SMTP authentication
                    $mail->Username =  $_ENV['SMTP_USERNAME'];                     // SMTP username
                    $mail->Password = $_ENV['SMTP_PASSSWORD'];                               // SMTP password
                }
                if (strcmp($_ENV['SMTP_USESSL'], "true") == 0) {
                    $this->logger->debug("Auth::sendValidationEmail() Use SSL for email");
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
                
                $this->logger->debug("Auth::sendValidationEmail() Mail sent to $email");
            } catch (Exception $e) {
                $this->logger->error("Auth::sendValidationEmail() Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
                die("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
            }
    }

    /**
     * sendValidationEmail : Send a validation email with link to validate email
     *
     * @param  mixed $email
     * @param  mixed $v4uuid
     * @return void
     */
    public function sendValidatorEmail(string $email,string $v4uuid){
        $mail = new PHPMailer(true);
            try {
                //Server settings
                // $mail->SMTPDebug = SMTP::DEBUG_SERVER;                      // Enable verbose debug output
                $mail->isSMTP();                                            // Send using SMTP

                $mail->Host = $_ENV['SMTP_SERVER'];                    // Set the SMTP server to send through

                if (strcmp($_ENV['SMTP_USEAUTH'], "true") == 0) {
                    $this->logger->debug("Auth::sendValidatorEmail() Use SMTP Auth for email");
                    $mail->SMTPAuth = true;                                   // Enable SMTP authentication
                    $mail->Username =  $_ENV['SMTP_USERNAME'];                     // SMTP username
                    $mail->Password = $_ENV['SMTP_PASSSWORD'];                               // SMTP password
                }
                if (strcmp($_ENV['SMTP_USESSL'], "true") == 0) {
                    $this->logger->debug("Auth::sendValidatorEmail() Use SSL for email");
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
                
                $this->logger->debug("Auth::sendValidatorEmail() Mail sent to ".$_ENV['REGISTRATION_ADMIN_EMAIL']);
            } catch (Exception $e) {
                $this->logger->error("Auth::sendValidatorEmail() Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
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
    public function addUser(string $email, string $encryptedpassword): bool
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
                'nickname' => $email,
                'emailchecklink' => $v4uuid_user,
                'emailchecklinktimeout' => $timestamp, //curent time + 15 minutes
                'emailchecked' => false,
                'resetpasswordlink' => null,
                'resetpasswordlinktimeout' => null,
                'approvaleuuid' => $v4uuid_validator,
                'adminapproved' => false

            ]);
            if (strcmp($_ENV['REGISTRATION_REQUIRE_APPROVAL'],"true")==0){
                $this->sendValidatorEmail($email,$v4uuid_validator);
            }else {
                $this->sendValidationEmail($email,$v4uuid_user);
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
    public function validateEmail(string $uuid): bool
    {
        $user = User::where([
            ['emailchecklink', 'like', $uuid]
        ])->first();

        if (!is_null($user)) {
            $time = $user->emailchecklinktimeout;
            $this->logger->debug("Auth::validateEmail() UUID found for user : " . $user->email . " and link timeout is : " . $time);
            $validationtime = Carbon::createFromFormat(Carbon::DEFAULT_TO_STRING_FORMAT, $time);
            $currenttime =  Carbon::createFromTimestamp(time());
            $this->logger->debug("Auth::validateEmail() compare $validationtime >=  $currenttime");

            if ($validationtime->gte($currenttime)) {
                //Mail is checked, we can trust this user
                $this->logger->debug("Auth::validateEmail() TimeStamp for validation is OK");
                $user->emailchecked = true;
                $user->save();
                $this->setUserID($user->id);
                $this->setAuthentified(true);
                return true;
            } else {
                $this->logger->debug("Auth::validateEmail() TimeStamp for validation is not OK");
                $user->emailchecked = false;
                $user->save();
                $this->unvalidate();
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
    public function setNickname(string $nickname){
        $user = User::find($this->getUserId());
        $user->nickname=$nickname;
        $user->save();
    }

    /**
     * resetPassword : set a new password for the uuid passed
     *
     * @param  mixed $uuid
     * @param  mixed $encryptedpassword
     * @return void
     */
    public function resetPassword(string $uuid, string $encryptedpassword):void
    {
        if ($this->checkUUIDpasswordreset($uuid)){
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
    public function changePassword(string $newencryptedpasssword){
        User::find($this->getUserId())->password=$newencryptedpasssword;
    }

    /**
     * checkUUIDpasswordreest : check if UUID is valid
     *
     * @param  mixed $uuid
     * @return bool
     */
    public function checkUUIDpasswordreset(string $uuid): bool
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
    public function sendResetPasswordLink(string $email): bool
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
                    $this->logger->debug("Auth::resetPassword() Use SMTP Auth for email");
                    $mail->SMTPAuth = true;                                   // Enable SMTP authentication
                    $mail->Username =  $_ENV['SMTP_USERNAME'];                     // SMTP username
                    $mail->Password = $_ENV['SMTP_PASSSWORD'];                               // SMTP password
                }
                if (strcmp($_ENV['SMTP_USESSL'], "true") == 0) {
                    $this->logger->debug("Auth::resetPassword() Use SSL for email");
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
                
                $this->logger->debug("Auth::resetPassword() Mail sent to $email");
            } catch (Exception $e) {
                $this->logger->error("Auth::resetPassword() Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
                die("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
            }
        }
        return true;
    }
}
