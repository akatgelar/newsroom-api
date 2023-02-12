<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\UserLevel;
use App\MasterProvinsi;
use App\MasterKota;
use App\MasterKecamatan;
use App\MasterKelurahan;

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

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    /**
     * @OA\Post(
     *     path="/login",
     *     tags={"Auth"},
     *     summary="",
     *     description="Login",
     *     operationId="auth_login",
     *     @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(
     *                  @OA\Property(
     *                      property="email",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="password",
     *                      type="string"
     *                  )
     *              )
     *          )
     *     ),
     *     @OA\Response(
     *         response="default",
     *         description="OK",
     *         @OA\MediaType(
     *              mediaType="application/json",
     *              example={
     *                  "success"=true,
     *                  "message"="Insert Data Successfull",
     *                  "data"={}
     *              }
     *         )
     *     )
     * )
     */

    public function login(Request $request)
    {
        $this->validateLogin($request);

        if ($this->attemptLogin($request)) {
            $user = $this->guard()->user();
            $user->generateToken();

            $temp = $user->toArray();
            if ($temp['is_active'] > 0)
            {
                $result = [];
                $result['success'] = true;
                $result['message'] = 'Login Successfull';
                $result['data'] = $user->toArray();

                $user_level = UserLevel::select('name')->where('id','=',$result['data']['user_level_id'])->get();
                if(count($user_level) > 0){
                    $result['data']['user_level_name'] = $user_level[0]['name'];
                }else {
                    $result['data']['user_level_name'] = null;
                }

                // insert log
                app()->call('App\Http\Controllers\LogController@InsertLog', [$result['data']['api_token'], 'LOGIN', 'Login', $result['data']['id']]);

                ksort($result['data']);
                return response()->json($result);
            }
            else
            {
                $result = [];
                $result['success'] = false;
                $result['message'] = 'Login Failed, Akun belum diaktivasi.';
                $result['data'] = [];

                return response()->json($result);
            }

        } else {
            $result = [];
            $result['success'] = false;
            $result['message'] = 'Login Failed, Email atau password salah.';
            $result['data'] = [];

            return response()->json($result);

            // return $this->sendFailedLoginResponse($request);
        }


    }


    /**
     * @OA\Post(
     *     path="/logout",
     *     tags={"Auth"},
     *     summary="",
     *     description="Logout",
     *     operationId="auth_logout",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response="default",
     *         description="OK",
     *         @OA\MediaType(
     *              mediaType="application/json",
     *              example={
     *                  "success"=true,
     *                  "message"="Insert Data Successfull",
     *                  "data"={}
     *              }
     *         )
     *     )
     * )
     */

    public function logout(Request $request)
    {
        $user = Auth::guard('api')->user();

        if ($user) {
            $user->api_token = null;
            $user->save();
        }

        $result = [];
        $result['success'] = true;
        $result['message'] = 'User logged out.';
        $result['data'] = [];

        return response()->json($result);
    }
}
