<?php

namespace Modules\Software\Auth;

use Illuminate\Auth\TokenGuard;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Http\Request;

class Sha256TokenGuard extends TokenGuard
{
    /**
     * Get the token for the current request.
     *
     * @return string|null
     */
    public function getTokenForRequest()
    {
        $token = parent::getTokenForRequest();
        
        if ($token) {
            // Hash the token with SHA256 to match database storage
            return hash('sha256', $token);
        }
        
        return null;
    }
    
    /**
     * Retrieve a user by the given credentials.
     *
     * @param  array  $credentials
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function getUser()
    {
        if (!is_null($this->user)) {
            return $this->user;
        }

        $user = null;

        $token = $this->getTokenForRequest();

        if (!empty($token)) {
            // Token is already hashed by getTokenForRequest()
            $user = $this->provider->retrieveByCredentials([
                $this->storageKey => $token
            ]);
        }

        return $this->user = $user;
    }
}

