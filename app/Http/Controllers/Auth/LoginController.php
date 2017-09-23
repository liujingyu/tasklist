<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Socialite;
use App\User;
use Auth;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    protected $config;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Redirect the user to the GitHub authentication page.
     *
     * @return Response
     */
    public function redirectToProvider()
    {

        return Socialite::with('gitlab')->setConfig($this->config)->redirect();
    }

    /**
     * Obtain the user information from GitHub.
     *
     * @return Response
     */
    public function handleProviderCallback(Request $request)
    {
        $user = Socialite::driver('gitlab')->setConfig($this->config)->user();

        if(!User::where('gitlab_id',$user->id)->first()){
            $userModel = new User;
            $userModel->gitlab_id = $user->id;
            $userModel->email = $user->email;
            $userModel->name = $user->name;
            $userModel->password = '';
            $userModel->save();
        }

        $userInstance = User::where('gitlab_id',$user->id)->firstOrFail();
        Auth::login($userInstance);
        return $this->sendLoginResponse($request);
    }

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {

        $gitlab = config('services.gitlab');
        $additionalProviderConfig = ['instance_uri' => 'http://gitlab.superman2014.com:10080/'];
        $config = new \SocialiteProviders\Manager\Config($gitlab['client_id'], $gitlab['client_secret'], $gitlab['redirect'], $additionalProviderConfig);

        $this->config = $config;

        $this->middleware('guest')->except('logout');
    }
}

