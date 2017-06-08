<?php

namespace App\Http\Controllers;

use App\Models\FacebookManager;
use Illuminate\Http\Request;

/**
 * Authentication controller.
 */
class AuthController extends Controller
{
    /** @var FacebookManager */
    protected $facebook;

    /**
     * Constructor.
     *
     * @param FacebookManager $facebook
     */
    public function __construct(FacebookManager $facebook)
    {
        $this->facebook = $facebook;
    }

    /**
     * Login action.
     *
     * @return \Illuminate\Http\RedirectResponse|\Laravel\Lumen\Http\Redirector
     */
    public function login()
    {
        /** @var \Facebook\Facebook $fbClient */
        $fbClient = $this->facebook->connection($this->facebook->getDefaultConnection());
        $loginUrl = $fbClient->getRedirectLoginHelper()->getLoginUrl(url('login-callback'));

        return redirect()->to($loginUrl);
    }

    /**
     * Login callback action.
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function loginCallback(Request $request)
    {
        /** @var \App\Models\Facebook $fbClient */
        $fbClient = $this->facebook->connection($this->facebook->getDefaultConnection());
        try {
            $fbClient->login($request);
        } catch (\Exception $exception) {
            redirect()->route('homepage')
                ->with('flash_notice', 'Login failed. Please contact with system administrator.')
                ->with('flash_notice_status', 'danger');
        }

        return redirect()->route('profile')
            ->with('flash_notice', 'You are successfully logged in.')
            ->with('flash_notice_status', 'success');
    }

    /**
     * Profile action.
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function profile()
    {
        /** @var \App\Models\Facebook $fbClient */
        $fbClient = $this->facebook->connection($this->facebook->getDefaultConnection());
        if (!$fbClient->isLoggedIn()) {
            return redirect()->route('homepage')
                ->with('flash_notice', 'Please log in.')
                ->with('flash_notice_status', 'warning');
        }

        $user = $fbClient->getUser();

        if (!$user->getId()) {
            return redirect()->route('homepage')
                ->with('flash_notice', 'User not exist.')
                ->with('flash_notice_status', 'danger');
        }

        return view('content.profile', ['facebookUser' => $user]);
    }

    /**
     * Logout action.
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function logout(Request $request)
    {
        /** @var \App\Models\Facebook $fbClient */
        $fbClient = $this->facebook->connection($this->facebook->getDefaultConnection());
        $fbClient->logout();

        return redirect()->route('homepage')
            ->with('flash_notice', 'You are successfully logged out.')
            ->with('flash_notice_status', 'success');
    }

    /**
     * Deauthorize callback.
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function deauthorizeCallback(Request $request)
    {
        /** @var \App\Models\Facebook $fbClient */
        $fbClient = $this->facebook->connection($this->facebook->getDefaultConnection());
        $fbClient->deauthorize($request);

        return redirect()->route('homepage');
    }
}
