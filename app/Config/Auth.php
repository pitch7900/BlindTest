<?php

declare(strict_types=1);

namespace App\Config;

/**
 * @author: Pierre Christensen
 * Auth : Return if user in current session is authentified
 */
class Auth
{
    
    /**
     * authentified
     *
     * @var bool
     */
    private $authentified;
    

    private $sessionid;

    /**
     * __construct
     *
     * @return void
     */
    public function __construct()
    {
        $this->sessionid = session_id();
        if (isset($_SESSION["authentified"])) {
            $this->authentified = true;
        } else {
            $this->authentified = false;
        }
    }

        
    /**
     * check
     *
     * @return bool
     */
    public function check():bool
    {

        return $this->authentified;
    }

    public function getSessionId():string {
        return $this->sessionid;
    }
}
