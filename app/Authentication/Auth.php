<?php

declare(strict_types=1);

namespace App\Authentication;

use App\Database\User;
use Carbon\Carbon;
// Import PHPMailer classes into the global namespace
// These must be at the top of your script, not inside a function
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
use Psr\Log\LoggerInterface;

/**
 * @author: Pierre Christensen
 * Manage authentication
 */
class Auth
{
    private $logger;

    /**
     * __construct
     *
     * @return void
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
        if (isset($_SESSION['authentified'])){
            $this->logger->debug('Auth::__contruct SuperGlobal Variable Session authentified is set to ' . $_SESSION['authentified']);
        } else {
            $this->setAuthentified(false);
        }
        
    }


   
    private function unvalidate()
    {
        $this->setAuthentified(false);
        unset($_SESSION["userid"]);
    }

    public function getSessionId(): string
    {
        return session_id();
    }

    private function setUserID($userid)
    {
        $_SESSION["userid"] = $userid;
    }

    public function getUserId(): int
    {
        return $_SESSION["userid"];
    }

    public function getUserEmail(): string
    {
        $user = User::find($this->getUserId());
        return  $user->email;
    }

    public function getUserNickName(): string
    {
        return  User::find($this->getUserId())->nickname;
    }

    public function checkPassword(string $email, string $password): bool
    {
        $email = strtolower($email);
        $this->logger->debug("Auth::checkPasswod() should check password for ".$email);
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

    public function getAuthentified()
    {
        return $_SESSION['authentified'];
    }

    private function setAuthentified(bool $authentified)
    {
        $_SESSION['authentified'] = $authentified;
    }

    public function signout()
    {
        $this->unvalidate();
    }

    public function addUser(string $email, string $encryptedpassword): bool
    {
        $email = strtolower($email);
        $user = User::where([
            ['email', '=', $email]
        ])->first();

        if (is_null($user)) {
            $v4uuid = UUID::v4();

            //current timestamp +15 minutes
            $timestamp =  Carbon::createFromTimestamp(time() + 15 * 60);

            User::updateOrCreate([
                'email' => $email,
                'password' => $encryptedpassword,
                'nickname' => $email,
                'emailchecklink' => $v4uuid,
                'emailchecklinktimeout' => $timestamp, //curent time + 15 minutes
                'emailchecked' => false,
                'resetpasswordlink' => null,
                'resetpasswordlinktimeout' => null

            ]);
            $mail = new PHPMailer(true);
            try {
                //Server settings
                // $mail->SMTPDebug = SMTP::DEBUG_SERVER;                      // Enable verbose debug output
                $mail->isSMTP();                                            // Send using SMTP

                $mail->Host       = $_ENV['SMTP_SERVER'];                    // Set the SMTP server to send through

                if (strcmp($_ENV['SMTP_USEAUTH'], "true") == 0) {
                    $this->logger->debug("Auth::addUser() Use SMTP Auth for email");
                    $mail->SMTPAuth   = true;                                   // Enable SMTP authentication
                    $mail->Username   =  $_ENV['SMTP_USERNAME'];                     // SMTP username
                    $mail->Password   = $_ENV['SMTP_PASSSWORD'];                               // SMTP password
                }
                if (strcmp($_ENV['SMTP_USESSL'], "true") == 0) {
                    $this->logger->debug("Auth::addUser() Use SSL for email");
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;         // Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` encouraged
                }
                $mail->Port       = $_ENV['SMTP_PORT'];                                    // TCP port to connect to, use 465 for `PHPMailer::ENCRYPTION_SMTPS` above

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
                //echo 'Message has been sent';
                $this->logger->debug("Auth::addUser() Mail sent to $email");
            } catch (Exception $e) {
                $this->logger->error("Auth::addUser() Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
                die("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
            }
        }
        return true;
    }

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

    public function resetPassword(string $uuid, string $encryptedpassword)
    {
        $user = User::where([
            ['resetpasswordlink', 'like', $uuid]
        ])->first();
        if (!is_null($user)) {
            $time = $user->resetpasswordlinktimeout;
            $validationtime = Carbon::createFromFormat(Carbon::DEFAULT_TO_STRING_FORMAT, $time);
            $currenttime =  Carbon::createFromTimestamp(time());
            if ($validationtime->gte($currenttime)) {
                $this->logger->debug("Auth::resetPassword() Password reseted for user" . $user->email);
                //reset the password
                $user->password = $encryptedpassword;
                //Set the reset link to now, for avoiding attacks
                $user->resetpasswordlinktimeout = Carbon::createFromTimestamp(time());
                $user->save();
            }
        }
    }

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

                $mail->Host       = $_ENV['SMTP_SERVER'];                    // Set the SMTP server to send through

                if (strcmp($_ENV['SMTP_USEAUTH'], "true") == 0) {
                    $this->logger->debug("Auth::resetPassword() Use SMTP Auth for email");
                    $mail->SMTPAuth   = true;                                   // Enable SMTP authentication
                    $mail->Username   =  $_ENV['SMTP_USERNAME'];                     // SMTP username
                    $mail->Password   = $_ENV['SMTP_PASSSWORD'];                               // SMTP password
                }
                if (strcmp($_ENV['SMTP_USESSL'], "true") == 0) {
                    $this->logger->debug("Auth::resetPassword() Use SSL for email");
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;         // Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` encouraged
                }
                $mail->Port       = $_ENV['SMTP_PORT'];                                    // TCP port to connect to, use 465 for `PHPMailer::ENCRYPTION_SMTPS` above

                //Recipients
                $mail->setFrom($_ENV['SMTP_MAILFROM'], 'Blindtest mailer daemon');
                $mail->addAddress($email);               // Add a recipient
                $mail->addReplyTo($_ENV['SMTP_MAILFROM'], 'Blindtest mailer daemon');


                // Content
                $mail->isHTML(true);                                  // Set email format to HTML
                $mail->Subject = 'Password reset for Blindtest';
                $mail->Body    = "<p>Hello,</p>" . "\r\n" .
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
                //echo 'Message has been sent';
                $this->logger->debug("Auth::resetPassword() Mail sent to $email");
            } catch (Exception $e) {
                $this->logger->error("Auth::resetPassword() Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
                die("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
            }
        }
        return true;
    }
}
