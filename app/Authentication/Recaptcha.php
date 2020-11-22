<?php

declare(strict_types=1);

namespace App\Authentication;

use Psr\Log\LoggerInterface;
use \hamburgscleanest\GuzzleAdvancedThrottle as GuzzleAdvancedThrottle;

/**
 * @author: Pierre Christensen
 * @ref: https://developers.google.com/recaptcha/docs/verify
 * @ref: https://cloud.google.com/recaptcha-enterprise/quotas
 * Manage Recaptcha
 */
class Recaptcha
{    


    /**
     * This is the url to call the API
     *
     * @var string
     */
    private $_sApiUrl = "https://www.google.com";

    /**
     * Max queries per $_sApiRequestInterval
     * @var string
     */
    private $_sApiMaxRequest = "60000";

    /**
     * Interval for max queries used in _sApiMaxRequest
     * @var string
     */
    private $_sApiRequestInterval = "60";

    /**
     * @var GuzzleAdvancedThrottle\RequestLimitRuleset
     */
    private $ThrottlerRules;

    /**
     * @var \GuzzleHttp\HandlerStack
     */
    private $ThrottlerStack;


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
        $this->initiateThrotller();

        if (isset($_SESSION['norobot'])) {
            $this->logger->debug('Recaptcha::__contruct SuperGlobal Variable Session [norobot] is already set to ' . $_SESSION['norobot']);
        } else {
            $this->setNoRobot(false);
        }
        $this->logger->debug('Recaptcha::__contruct SuperGlobal Variable Session [norobot] is now set to ' . $_SESSION['norobot']);
    }


    /**
     * initiateThrotller : Initialize Throttler with values set in the class
     *
     * @return void
     */
    private function initiateThrotller()
    {
        $this->ThrottlerRules = new GuzzleAdvancedThrottle\RequestLimitRuleset([
            $this->_sApiUrl => [
                [
                    'max_requests' => $this->_sApiMaxRequest,
                    'request_interval' => $this->_sApiRequestInterval
                ]
            ]
        ]);
    }


    /**
     * sendRequest : This method will be called to send a request
     *
     * @param  mixed $sUrl
     * @return void
     */
    private function sendRequest(string $method, string $sUrl, array $options)
    {
        $this->ThrottlerStack = new \GuzzleHttp\HandlerStack();
        $this->ThrottlerStack->setHandler(new \GuzzleHttp\Handler\CurlHandler());

        $throttle = new GuzzleAdvancedThrottle\Middleware\ThrottleMiddleware($this->ThrottlerRules);

        $this->ThrottlerStack->push($throttle());

        $client = new \GuzzleHttp\Client([
            'base_uri' => $this->_sApiUrl,
            'handler' => $this->ThrottlerStack,
            'verify' => false
        ]);
        $RequestToBeDone = true;
        do {
            try {
                $this->logger->debug("Recaptcha::sendRequest request recieved : " . $sUrl);
                // $response = $client->post($sUrl,$options);
                 $response = $client->request($method,$sUrl,$options);
                // $request = $client->createRequest($method,$sUrl,$options);
                // $response = $client->send($request);

                $output = $response->getBody();
                $RequestToBeDone = false;
            } catch (\Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException $e) {
                $this->logger->debug("Recaptcha::sendRequest Too many requests. Waiting 1 second");
                sleep(1);
            }
        } while ($RequestToBeDone);


        if ($output === false) {
            $this->logger->debug("Recaptcha::sendRequest Error curl : " . curl_error($response), E_USER_WARNING);
            //trigger_error('Erreur curl : ' . curl_error($response), E_USER_WARNING);
        } else {
            //curl_close($response);
            $this->logger->debug("Recaptcha::sendRequest response recieved : " . print_r($output,true));
            return json_decode($output->getContents(),true);
        }
    }

    public function verifyResponseToken(string $token){
        $url = $this->_sApiUrl . '/recaptcha/api/siteverify';
        
        $output = $this->sendRequest('POST',$url,[
            'form_params' => [
                'secret' => $_ENV['GOOGLE_RECAPTCHA_SECRET_KEY'],
                'response' => $token,
                'remoteip' => $_SERVER['REMOTE_ADDR']
            ]
        ]);
        $this->logger->debug("Recaptcha::verifyResponseToken Response from Google Recaptcha : ".print_r($output,true));
        
        $this->setNoRobot($output['success']);
        return  $this->getNoRobot();    
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
     * getNoRobot : return if user is a robot or not 
     * true : Captcha is passed - Not a robot
     * false : Captcha not passed - possibly a robot
     * @return void
     */
    public function getNoRobot()
    {
        return $_SESSION['norobot'];
    }
    
    /**
     * setNoRobot : Change the captcha satus
     *
     * @param  mixed $authentified
     * @return void
     */
    private function setNoRobot(bool $robot)
    {
        $_SESSION['norobot'] = $robot;
    }
    
}
