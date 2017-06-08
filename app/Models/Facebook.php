<?php

namespace App\Models;

use Facebook\Facebook as BaseFacebook;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Class Facebook.
 */
class Facebook extends BaseFacebook
{
    /**
     * The HTTP request instance.
     *
     * @var \Illuminate\Http\Request
     */
    protected $request;

    /**
     * Set the request instance.
     *
     * @param  \Illuminate\Http\Request $request
     *
     * @return $this
     */
    public function setRequest(Request $request)
    {
        $this->request = $request;
        return $this;
    }

    /**
     * Authenticate Facebook customer.
     *
     * @param Request $request
     *
     * @throws \Exception
     *
     * @return void
     */
    public function login(Request $request)
    {
        $this->setRequest($request);
        $session = $request->getSession();

        if ($session->has(\App\User::USER_ACCESS_TOKEN_SESSION_NAME)) {
            //throw new \Exception('User has been already logged in.');
        }

        $user = $this->_getUser();
        if (null === $user->getId()) {
            throw new \Exception('Cannot retrieve user data from Facebook.');
        }

        $authUser = $this->findOrCreateUser($user);
        if ($authUser->getAttribute('id')) {
            $session->set(\App\User::USER_ACCESS_TOKEN_SESSION_NAME, $authUser->getAttribute('token'));
        }
    }

    /**
     * Clear customer session.
     *
     * @return void
     */
    public function logout()
    {
        $session = app('session');
        if ($session->has(\App\User::USER_ACCESS_TOKEN_SESSION_NAME)) {
            $session->remove(\App\User::USER_ACCESS_TOKEN_SESSION_NAME);
        }
    }

    /**
     * Deauthorize user.
     *
     * @param Request $request
     *
     * @return void
     */
    public function deauthorize(Request $request)
    {
        $this->setRequest($request);

        $data = $this->parseSignedRequest();

        if ($data === false) {
            throw new HttpException('500', 'There was a problem with the request format.');
        } else {
            $authUser = User::where('user_id', $data['user_id'])->first();
            if (!empty($authUser)) {
                User::where('user_id', $data['user_id'])
                    ->update(['is_active' => false]);
            }
        }
    }

    /**
     * Check if user logged in.
     *
     * @return bool
     */
    public function isLoggedIn()
    {
        return app('session')->has(\App\User::USER_ACCESS_TOKEN_SESSION_NAME);
    }

    /**
     * Retrieves user information form database.
     *
     * @return \App\User
     */
    public function getUser()
    {
        $session = app('session');
        $authUser = User::where('token', $session->get(\App\User::USER_ACCESS_TOKEN_SESSION_NAME))->first();
        if (null === $authUser) {
            return new \App\User();
        }

        return $this->_mapUserToObject(
            array(
                'user_id'     => $authUser->getAttribute('user_id'),
                'first_name'  => $authUser->getAttribute('first_name'),
                'last_name'   => $authUser->getAttribute('last_name'),
                'email'       => $authUser->getAttribute('email'),
                'profile_url' => $authUser->getAttribute('profile_url'),
            )
        );
    }

    /**
     * Convert array user data to user object.
     *
     * @return \App\User
     */
    protected function _mapUserToObject(array $user)
    {
        return (new \App\User())->setRaw($user)->map(
            [
                'userId'     => $user['user_id'],
                'firstName'  => $user['first_name'],
                'lastName'   => $user['last_name'],
                'email'      => $user['email'],
                'profileUrl' => $user['profile_url']
            ]
        );
    }

    /**
     * Retrieve access token.
     *
     * @return bool|\Facebook\Authentication\AccessToken
     */
    protected function _getLongLivedAccessToken()
    {
        $redirectLoginHelper = $this->getRedirectLoginHelper();
        if ($this->request->exists('state')) {
            $redirectLoginHelper->getPersistentDataHandler()->set('state', $this->request->get('state'));
        }

        $accessToken = $redirectLoginHelper->getAccessToken();

        if (null === $accessToken) {
            return false;
        }

        if (!$accessToken->isLongLived()) {
            try {
                $oAuth2Client = $this->getOAuth2Client();
                $accessToken = $oAuth2Client->getLongLivedAccessToken($accessToken);
            } catch (\Facebook\Exceptions\FacebookSDKException $e) {
                return false;
            }
        }

        return $accessToken;
    }

    /**
     * Retrieves facebook user.
     *
     * @return \App\User
     */
    protected function _getUser()
    {
        $accessToken = $this->_getLongLivedAccessToken();

        if (null === $accessToken) {
            return new \App\User();
        }

        try {
            $profileResponse = $this->get(
                '/me?fields=first_name,last_name,email,picture',
                $accessToken
            );
        } catch (\Facebook\Exceptions\FacebookResponseException $e) {
            return new \App\User();
        } catch (\Facebook\Exceptions\FacebookSDKException $e) {
            return new \App\User();
        }

        $userProfile = $profileResponse->getGraphUser();

        $user = $this->_mapUserToObject(
            array(
                'user_id'     => $userProfile->getId(),
                'first_name'  => $userProfile->getFirstName(),
                'last_name'   => $userProfile->getLastName(),
                'email'       => $userProfile->getEmail(),
                'profile_url' => $userProfile->getPicture()->getUrl()
            )
        );

        return $user->setToken($accessToken->getValue());
    }

    /**
     * Return user if exists; create and return if doesn't
     *
     * @param \App\User $facebookUser
     *
     * @return User
     */
    public function findOrCreateUser($facebookUser)
    {
        $authUser = User::where('user_id', $facebookUser->getId())->first();
        if (!empty($authUser)) {
            User::where('user_id', $facebookUser->getId())
                ->update(
                    [
                        'is_active' => true,
                        'token'     => $facebookUser->getToken()
                    ]
                );

            $authUser = User::where('user_id', $facebookUser->getId())->first();
            return $authUser;
        }

        return User::create(
            [
                'user_id'     => $facebookUser->getId(),
                'first_name'  => $facebookUser->getFirstName(),
                'last_name'   => $facebookUser->getLastName(),
                'email'       => $facebookUser->getEmail(),
                'profile_url' => $facebookUser->getProfileUrl(),
                'token'       => $facebookUser->getToken(),
                'is_active'   => true,
            ]
        );
    }

    /**
     * Parse de-authorization signed request.
     *
     * @return bool|array
     */
    private function parseSignedRequest()
    {
        if ($this->request->has('signed_request')) {
            $signedRequest = $this->request->get('signed_request');
            list($encodedSig, $payload) = explode('.', $signedRequest, 2);

            // decode the data
            $sig = $this->_base64UrlDecode($encodedSig);
            $data = json_decode($this->_base64UrlDecode($payload), true);

            if (strtoupper($data['algorithm']) !== 'HMAC-SHA256') {
                return false;
            }

            // Adding the verification of the signed_request below
            $expectedSig = hash_hmac('sha256', $payload, $this->getApp()->getSecret(), $raw = true);
            if ($sig !== $expectedSig) {
                return false;
            }

            return $data;
        }

        return false;
    }

    /**
     * Base64 url decode.
     *
     * @param string $input
     *
     * @return bool|string
     */
    private function _base64UrlDecode($input)
    {
        return base64_decode(strtr($input, '-_', '+/'));
    }
}