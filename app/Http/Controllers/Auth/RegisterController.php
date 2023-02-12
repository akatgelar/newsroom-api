<?php

namespace App\Http\Controllers\Auth;

use App\User;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\RegistersUsers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Mail;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
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
        $this->middleware('guest');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'username' => ['required', 'string', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
            'nama' => ['required', 'string', 'max:255'],
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\User
     */
    protected function create(array $data)
    {
        return User::create([
            'email' => $data['email'],
            'username' => $data['username'],
            'password' => Hash::make($data['password']),
            'nama' => $data['nama'],
            'foto' =>  '/uploads/user.png',
            'is_active' => 1,
        ]);
    }

    /**
     * @OA\Post(
     *     path="/register",
     *     tags={"Auth"},
     *     summary="",
     *     description="Register",
     *     operationId="auth_register",
     *     @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(
     *                  @OA\Property(
     *                      property="username",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="email",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="password",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="nama",
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
     *                  "message"="Register Successfull",
     *                  "data"={}
     *              }
     *         )
     *     )
     * )
     */

    public function register(Request $request)
    {
        $userAvailable = $this->checkAccount($request);

        if ($userAvailable)
        {
            event(new Registered($user = $this->create($request->all())));
            $this->guard()->login($user);
            // $this->sendEmailActivation($user);
            return $this->registered($request, $user)?: redirect($this->redirectPath());
        }
        else
        {
            $message = '';
            if  ($userAvailable == false) {
                $message = 'Username / Email already exist in PR Newsroom Account';
            } else {
                $message = 'Username / Email already exist';
            }
            $result['success'] = false;
            $result['message'] = $message;
            $result['data'] = [];
            return response()->json($result, 500);
        }
    }

    protected function registered(Request $request, $user)
    {
        $user->generateToken();

        $result = [];
        $result['success'] = true;
        $result['message'] = 'Register Successfull';
        $result['data'] = $user->toArray();

        ksort($result['data']);
        return response()->json($result, 201);
    }

    public function checkAccount($request)
    {
        try {
            $query1 = User::where([['email','=',$request['email']]]);
            $count1 = $query1->count('id');
            if ($count1 >= 1) {
                return false;
            } else {
                $query2 = User::Where([['username','=',$request['username']]]);
                $count2 = $query2->count('id');
                if ($count2 >= 1) {
                    return false;
                } else {
                    return true;
                }
            }
        }
        catch(Exception $e) {
            return false;
        }
    }


    // public function sendEmailActivation($user)
    // {
    //     $res_user = $user->toArray();

    //     try {
    //         $to = $res_user['email'];
    //         $nama = $res_user['nama'];
    //         $token = Crypt::encryptString($res_user['id']);
    //         $html = '<html>';
    //         $html .= '<body style="background-color: #e9ecef;"><br><br><br><br>';
    //         $html .= '<table border="0" cellpadding="0" cellspacing="0" width="100%">';
    //         $html .= '  <tr><td align="center" bgcolor="#e9ecef">';
    //         $html .= '      <table border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 800px;">';
    //         $html .= '          <tr><td align="left" bgcolor="#ffffff" style="padding: 36px; border-top: 3px solid #d4dadf;">';
    //         $html .= '              <p>';
    //         $html .= '                  Yth. '.$nama.'<br>';
    //         $html .= "                  Aktivasi akun PR Newsroom Anda dengan klik link <b><a target='_blank' href='".url('/')."/register/activation/".$token."'> berikut ini. </a></b>";
    //         $html .= '              </p>';
    //         $html .= '          </td></tr>';
    //         $html .= '      </table>';
    //         $html .= '  </td></tr>';
    //         $html .= '</table>';
    //         $html .= '<br><br><br><br></body>';
    //         $html .= '</html>';
    //         Mail::send([], [], function ($message) use ($to, $html) {
    //             $message->to($to)
    //                 ->subject('Aktivasi Akun PR Newsroom')
    //                 ->from('newroom@oikiran-rakyat.com')
    //                 ->setBody($html, 'text/html');
    //         });
    //     } catch (Exception $ex) {
    //         error_log(print_r($ex));
    //     }

    // }

    // public function activationEmail($token)
    // {
    //     $id = Crypt::decryptString($token);
    //     $data['is_active'] = true;
    //     $query = User::findOrFail($id);
    //     $query->update($data);

    //     return view('activation');
    // }

    public function forgotPassword(Request $request)
    {
        $requests = $request->all();

        if (isset($requests['email']))
        {
            $user = User::where([['email','=',$request['email']]]);
            $user = $user->get()->toArray();

            if ($user)
            {
                $new_password = $this->generateRandomString();
                $data['password'] = Hash::make($new_password);
                $query = User::findOrFail($user[0]['id']);
                $query->update($data);

                try
                {
                    $to = $user[0]['email'];
                    $nama = $user[0]['nama'];
                    $html = '<html>';
                    $html .= '<body style="background-color: #e9ecef;"><br><br><br><br>';
                    $html .= '<table border="0" cellpadding="0" cellspacing="0" width="100%">';
                    $html .= '  <tr><td align="center" bgcolor="#e9ecef">';
                    $html .= '      <table border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 800px;">';
                    $html .= '          <tr><td align="left" bgcolor="#ffffff" style="padding: 36px; border-top: 3px solid #d4dadf;">';
                    $html .= '              <p>';
                    $html .= '                  Yth. '.$nama.'<br>';
                    $html .= "                  Ini password baru Anda, <b>".$new_password."</b><br>";
                    $html .= "                  Silahkan kembali ke halaman login dan masukkan password baru tersebut.";
                    $html .= '              </p>';
                    $html .= '          </td></tr>';
                    $html .= '      </table>';
                    $html .= '  </td></tr>';
                    $html .= '</table>';
                    $html .= '<br><br><br><br></body>';
                    $html .= '</html>';
                    Mail::send([], [], function ($message) use ($to, $html) {
                        $message->to($to)
                            ->subject('Reset Password PR Newsroom')
                            ->from('newsroom@pikiran-rakyat.com')
                            ->setBody($html, 'text/html');
                    });
                    $result['success'] = true;
                    $result['message'] = "Email reset password telah dikirim.";
                    $result['data'] = [];
                    return response()->json($result, 200);
                }
                catch (Exception $ex)
                {
                    $result['success'] = false;
                    $result['message'] = 'Send email failed';
                    $result['data'] = [];
                    return response()->json($result, 500);
                }
            }
            else
            {
                $result['success'] = false;
                $result['message'] = 'Email not found.';
                $result['data'] = [];
                return response()->json($result, 500);
            }
        }
        else
        {
            $result['success'] = false;
            $result['message'] = 'Email not send.';
            $result['data'] = [];
            return response()->json($result, 500);
        }
    }

    function generateRandomString($length = 10)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }
}
