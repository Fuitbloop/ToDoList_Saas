<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AuthController extends Controller
{

    public function oAuthUrl()
    {
        $url = Socialite::driver('google')->stateless()->redirect()->getTargetUrl();
        return response()->json(['url' => $url]);
    }

    public function oAuthCallback(Request $request)
    {
        $user = Socialite::driver('google')->stateless()->user();
        // dd(['OAuth callback received' => $user]);
        $existingUser = User::where('email', $user->getEmail())->first();
        if ($existingUser) {
            $token = $existingUser->createToken('auth_token')->plainTextToken;
            $existingUser->update([
                'avatar' => $user->avatar ?? $user->getAvatar()
            ]);
            return response()->json([
                'message' => 'Login successful',
                'user' => $existingUser,
                'token' => $token,
            ]);
        } else {
            $freePlan = Plan::where('name', 'Free')->first();
            if (!$freePlan) {
                return response()->json(['message' => 'Default plan not found.'], 500);
            }
            // dd($freePlan->id);
            $newUser = User::create([
                'name' => $user->getName(),
                'email' => $user->getEmail(),
                'password' => null,
                'plan_id' => $freePlan->id,
                'avatar' => $user->getAvatar()
            ]);

            $token = $newUser->createToken('auth_token')->plainTextToken;
            return response()->json([
                'message' => 'User created and logged in successfully',
                'user' => $newUser,
                'token' => $token
            ], 201);
        }
    }

    public function register(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            "name"=> "required|string|max:255",
            "email"=> "required|string|email|max:255|unique:users",
            "password"=> "required|string|min:8",
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $freePlan = Plan::where("name", 'free')->first();
        if (!$freePlan) {
            return response()->json(['message' => 'default plan not found.'], 500);
        }

        $user = User::create([
            'name'=> $request->name,
            'email'=> $request->email,
            'password'=> bcrypt($request->password),
            'plan_id' => $freePlan->id,
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;
        return response()->json([
            'message'=> 'User created succesfully',
            'user' => $user,
            'token'=> $token
        ],201);
    }

    public function login(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
             [
                'email'=> 'required|string|email',
                'password'=> 'required|string',
             ]
                );

                if ($validator->fails()) {
                    return response()->json($validator->errors(), 422);
                }

                if (!auth::attempt()->attempt($request->only('email', 'password'))){
                    return response()->json([
                        'message'=> 'invalid login detail'
                    ], 401);
                }

                $user = auth()->user();
                $token = $user->createToken('auth_tooken')->plainTextToken;
                return response()->json([
                    'message'=> 'Login succesfull',
                    'user'=> $user,
                    'token'=> $token
                ]);
    }

    public function me()
    {
        return response()->json(auth()->user());
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Successfully logged out']) ;
    }
}
