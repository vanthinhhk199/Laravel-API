<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PasswordReset;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\URL;



class AuthController extends Controller
{
    public function getAllUser(){
        $users = User::all();

        return response()->json([
            'success' => true,
            'data' => $users,
        ]);
    }
    public function getUserInfo($user_id)
    {
        $user = User::find($user_id);

        if ($user) {
            return response()->json([
                'success' => true,
                'user' => $user
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }
    }

    public function updateUserInfo(Request $request, $user_id)
    {
        $user = User::find($user_id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'string|between:2,100',
            'phone' => 'string',
            'gender' => 'string',
            'birthday' => 'string',
            'avatar' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'messages' => $validator->errors()
            ], 400);
        }

        $user->name = $request->name ? $request->name : $user->name;
        $user->phone = $request->phone ? $request->phone : $user->phone;
        $user->gender = $request->gender ? $request->gender : $user->gender;
        $user->birthday = $request->birthday ? $request->birthday : $user->birthday;



        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'User information updated successfully',
            'user' => $user
        ]);
    }

    public function uploadAvatar(Request $request, $user_id)
    {
        $user = User::find($user_id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 400);
        }

        if ($request->hasFile('avatar')) {
            $file = $request->file('avatar');
            $ext = $file->getClientOriginalExtension();
            $filename = rand() . '.' . $ext;
            $file->move('assets/uploads/avatar/', $filename);
            $user->avatar = $filename;
            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'Avatar uploaded successfully',
                'user' => $user
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'No avatar file found in the request'
        ], 400);
    }


    public function login(Request $request)
    {
        $messages = [
            'email.email' => "Error email",
            'email.required' => "Vui lòng nhập email",

        ];

        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'password' => 'required|string|min:6',

        ], $messages);

        if ($validator->fails()) {
            return response()->json([
                'messages' => $validator->errors()
            ], 404);

        };

        if (!$token = auth()->attempt($validator->validated()))
        {
            return response()->json(['success'=>false, 'masages'=>'Username & Password is incorrect'], 401);
        }
        return $this->respondWithToken($token);
    }

    protected function respondWithToken($token)
    {
        $user = auth()->user();

        return response()->json([
            'success' => true,
            'access_token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => auth()->factory()->getTTL() * 60,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'created_at' => $user->created_at
            ]
        ]);
    }

    public function register(Request $request)
    {
        $messages = [
            'email.email' => "Error email",
            'email.required' => "Vui lòng nhập email",
            'password.required' => "Vui lòng nhập password",
            'phone.required' => "Vui lòng nhập phone",
            'gender.required' => "Vui lòng nhập gender",
            'birthday.required' => "Vui lòng nhập birthday",


        ];

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|between:2,100',
            'email' => 'required|string|email|max:100|unique:users',
            'password' => 'required|string|min:6',
            'phone' => 'required|string',
            'gender' => 'required|string',
            'birthday' => 'required|string',


        ], $messages);

        if ($validator->fails()) {
            return response()->json([
                'messages' => $validator->errors()
            ], 404);

        };

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'gender' => $request->gender,
            'birthday' => $request->birthday,


        ]);

        // Gửi email xác nhận đăng ký
        $this->sendVerifyMail($user->email);

        return response()->json(
            [
                'message' => 'Đăng ký thành công',
                'email' => $user,
            ],200   );

    }

    public function sendVerifyMail($email)
    {
        $user = User::where('email', $email)->first();
        if ($user) {
            $random = Str::random(40);
            $domain = URL::to('/');
            $url = $domain.'/verify-mail/'.$random;

            $data['url'] = $url;
            $data['email'] = $email;
            $data['title'] = "Email Verification";
            $data['body'] = "Please click here to below to verify your mail.";

            Mail::send('verifyMail', ['data' => $data], function ($message) use ($data) {
                $message->to($data['email'])->subject($data['title']);
            });

            $user->remember_token = $random;
            $user->save();

            return response()->json(['success' => true, 'messages' => 'Mail sent successfully.']);
        } else {
            return response()->json(['success' => false, 'msg' => 'Người dùng chưa được xác thực']);
        }
    }

    public function logout()
    {
        try {
            auth()->logout();
            return response()->json(['success'=>true,'messages'=>'Người dùng đã đăng xuất!']);

        } catch (\Throwable $e)
        {
            return response()->json(['success'=>true,'messages'=>$e->getMessage()]);

        }
    }

    public function verificationMail($token)
    {
        $user = User::where('remember_token',$token)->get();
        if (count($user) > 0) {
            $datetime = Carbon::now()->format('Y-m-d H:i:s');
            $user = User::find($user[0]['id']);
            $user->remember_token = '';
            $user->is_verifiled = 1;
            $user->email_verified_at = $datetime;
            $user->save();
            return '<h1>Email verified successfully.</h1>';
        }else {
            return view('404');
        }
    }

    //refresh token api method
    public function refreshToken()
    {
        if (auth()->user()) {
            return $this->respondWithToken(auth()->refresh());
        }else{
            return response()->json(['success'=>false,'messages'=>'user is not Authenticated']);
        }
    }

    public function forgetPassword($email)
    {
        try {
            $user = User::where('email', $email)->get();
            if (count($user) > 0) {

                $token = Str::random(40);
                $domain = URL::to('/');
                $url = $domain.'/reset-password?token='.$token;

                $data['url'] = $url;
                $data['email'] = $email;
                $data['title'] = "Password Reset";
                $data['body'] = "Please click on below link to reset your password.";

                Mail::send('forgetPasswordMail', ['data' => $data],function ($messages) use ($data)
                {
                    $messages->to($data['email'])->subject($data['title']);
                });

                $datetime = Carbon::now()->format('Y-m-d H:i:s');
                PasswordReset::updateOrCreate(
                    ['email' => $email],
                    [
                        'email' => $email,
                        'token' => $token,
                        'created_at' => $datetime,

                    ]
                );

                return response()->json(['success'=>true, 'messages'=>'Please check your mail to reset your password.']);


            }else {
                return response()->json(['success' => false, 'messages' => 'User not found!']);
            }
        } catch (\Throwable $e) {
            return response()->json(['success'=>false,'messages'=>$e->getMessage()]);
        }


    }

    public function resetPasswordLoad(Request $request)
    {
        $resetData = PasswordReset::where('token', $request->token)->get();
        if (isset($request->token) && count($resetData) > 0)
        {
            $user = User::where('email', $resetData[0]['email'])->get();
            return view('resetPassword', compact('user'));

        }else {
            return view('404');
        }
    }

    // password reset functionality
    public function resetPassword(Request $request)
    {
        $request->validate([
            'password' => 'required|string|min:6',
        ]);
        $user = User::find($request->id);
        $user->password = Hash::make($request->password);
        $user->save();

        PasswordReset::where('email',$user->email)->delete();

        return "<h1>Your password has been reset successfully.</h1>";
    }

    public function updateRole(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $user->role = $request->input('role');
        $user->save();

        return response()->json(['message' => 'User role updated successfully']);
    }

    public function deleteUser($id){
        try {
        $result = User::where('id', $id)->delete();
        if($result){
            return response()->json([
                'success'=>true,
                'message'=>"User Delete Successfufly",
            ]);
        }else{
            return response()->json([
                'success'=>false,
                'message'=>"Some Problem",
            ]);
        }
         } catch (Exception $e) {
            return response()->json([
                'success'=>false,
                'error'=>$e->getMessage(),
            ]);
        }

    }
}
?>
