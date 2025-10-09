<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Exception;

class AuthFirebaseController extends Controller
{
    /**
     * Generate unique user reference ID
     * Format: USR-DDMMYYYYHHMMSS-XXX
     */
    private function generateUserRefId(): string
    {
        $date = now()->format('dmYHis'); // DDMMYYYYHHMMSS
        $random = strtoupper(substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 3));
        return "USR-{$date}-{$random}";
    }

    /**
     * Generate unique authentication reference ID
     * Format: AUT-DDMMYYYYHHMMSS-XXX
     */
    private function generateAuthRefId(): string
    {
        $date = now()->format('dmYHis'); // DDMMYYYYHHMMSS
        $random = strtoupper(substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 3));
        return "AUT-{$date}-{$random}";
    }

    /**
     * Generate unique referral code for user
     */
    private function generateReferralCode(): string
    {
        return strtoupper(substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 8));
    }

    /**
     * Get IP address from request
     */
    private function getIpAddress(Request $request): ?string
    {
        return $request->ip() ?? $request->header('X-Forwarded-For') ?? $request->header('X-Real-IP');
    }

    /**
     * Get user agent from request
     */
    private function getUserAgent(Request $request): ?string
    {
        return $request->header('User-Agent');
    }

    /**
     * Log authentication event to auth_history
     */
    private function logAuthHistory(array $data): void
    {
        try {
            DB::table('auth_history')->insert([
                'auth_refid' => $this->generateAuthRefId(),
                'user_refid' => $data['user_refid'],
                'firebase_uid' => $data['firebase_uid'] ?? null,
                'auth_method' => $data['auth_method'] ?? 'email_password',
                'auth_status' => $data['auth_status'] ?? 'success',
                'auth_type' => $data['auth_type'] ?? 'login',
                'device_type' => $data['device_type'] ?? null,
                'device_model' => $data['device_model'] ?? null,
                'os_version' => $data['os_version'] ?? null,
                'app_version' => $data['app_version'] ?? null,
                'browser' => $data['browser'] ?? null,
                'user_agent' => $data['user_agent'] ?? null,
                'ip_address' => $data['ip_address'] ?? null,
                'gps_location' => $data['gps_location'] ?? null,
                'country' => $data['country'] ?? null,
                'city' => $data['city'] ?? null,
                'timezone' => $data['timezone'] ?? null,
                'is_new_device' => $data['is_new_device'] ?? 0,
                'is_new_location' => $data['is_new_location'] ?? 0,
                'two_factor_used' => $data['two_factor_used'] ?? 0,
                'auth_timestamp' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (Exception $e) {
            // Log error but don't fail the main operation
            \Log::error('Failed to log auth history: ' . $e->getMessage());
        }
    }

    /**
     * Check if email already exists in the system
     * POST /api/auth/firebase/check-email
     */
    public function checkEmail(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }

            $email = $request->input('email');
            $user = DB::table('users')->where('email', $email)->first();

            if ($user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Email already exists in our system',
                    'data' => [
                        'exists' => true,
                        'user_refid' => $user->user_refid,
                        'confirmed' => (bool)$user->confirmed
                    ]
                ], 200);
            }

            return response()->json([
                'success' => true,
                'message' => 'Email is available',
                'data' => [
                    'exists' => false
                ]
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while checking email',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Register new user after Firebase authentication
     * POST /api/auth/firebase/register
     */
    public function register(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'firebase_uid' => 'required|string',
                'email' => 'required|email',
                'display_name' => 'nullable|string|max:200',
                'profile_photo_url' => 'nullable|url',
                'auth_method' => 'nullable|string|in:email_password,google,facebook,apple,phone',
                'device_info' => 'nullable|array',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Check if firebase_uid already exists
            $existingUser = DB::table('users')->where('firebase_uid', $request->firebase_uid)->first();
            if ($existingUser) {
                return response()->json([
                    'success' => false,
                    'message' => 'User already registered with this Firebase account',
                    'data' => [
                        'user_refid' => $existingUser->user_refid,
                        'confirmed' => (bool)$existingUser->confirmed
                    ]
                ], 409);
            }

            // Check if email already exists
            $existingEmail = DB::table('users')->where('email', $request->email)->first();
            if ($existingEmail) {
                return response()->json([
                    'success' => false,
                    'message' => 'Email already registered',
                    'data' => [
                        'user_refid' => $existingEmail->user_refid
                    ]
                ], 409);
            }

            $userRefId = $this->generateUserRefId();
            $referralCode = $this->generateReferralCode();

            // Insert new user
            DB::table('users')->insert([
                'user_refid' => $userRefId,
                'firebase_uid' => $request->firebase_uid,
                'email' => $request->email,
                'email_verified' => $request->email_verified ?? 0,
                'display_name' => $request->display_name,
                'profile_photo_url' => $request->profile_photo_url,
                'confirmed' => 0, // New user, not confirmed yet
                'is_operator' => 0,
                'is_admin' => 0,
                'is_staff' => 0,
                'account_status' => 'active',
                'referral_code' => $referralCode,
                'registration_source' => $request->input('device_info.device_type', 'web'),
                'last_login_ip' => $this->getIpAddress($request),
                'last_login_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Log authentication history
            $deviceInfo = $request->input('device_info', []);
            $this->logAuthHistory([
                'user_refid' => $userRefId,
                'firebase_uid' => $request->firebase_uid,
                'auth_method' => $request->auth_method ?? 'email_password',
                'auth_status' => 'success',
                'auth_type' => 'login',
                'device_type' => $deviceInfo['device_type'] ?? null,
                'device_model' => $deviceInfo['device_model'] ?? null,
                'os_version' => $deviceInfo['os_version'] ?? null,
                'app_version' => $deviceInfo['app_version'] ?? null,
                'user_agent' => $this->getUserAgent($request),
                'ip_address' => $this->getIpAddress($request),
                'is_new_device' => 1,
            ]);

            $user = DB::table('users')->where('user_refid', $userRefId)->first();

            return response()->json([
                'success' => true,
                'message' => 'User registered successfully. Please complete your profile.',
                'data' => [
                    'user_refid' => $user->user_refid,
                    'email' => $user->email,
                    'display_name' => $user->display_name,
                    'confirmed' => (bool)$user->confirmed,
                    'referral_code' => $user->referral_code,
                    'requires_profile_completion' => true
                ]
            ], 201);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred during registration',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Complete user profile with additional information
     * POST /api/auth/firebase/complete-profile
     */
    public function completeProfile(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'user_refid' => 'required|string|exists:users,user_refid',
                'firstname' => 'required|string|max:100',
                'lastname' => 'required|string|max:100',
                'birthday' => 'required|date|before:today',
                'gender' => 'required|in:male,female,other,prefer_not_to_say',
                'mobile_number' => 'nullable|string|max:20',
                'mobile_country_code' => 'nullable|string|max:5',
                'nationality' => 'nullable|string|max:100',
                'home_country' => 'nullable|string|max:100',
                'home_city' => 'nullable|string|max:100',
                'preferred_language' => 'nullable|string|max:10',
                'preferred_currency' => 'nullable|string|max:5',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }

            $userRefId = $request->user_refid;
            $user = DB::table('users')->where('user_refid', $userRefId)->first();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found'
                ], 404);
            }

            if ($user->confirmed) {
                return response()->json([
                    'success' => false,
                    'message' => 'User profile is already confirmed'
                ], 400);
            }

            // Update user profile
            DB::table('users')
                ->where('user_refid', $userRefId)
                ->update([
                    'firstname' => $request->firstname,
                    'lastname' => $request->lastname,
                    'display_name' => $request->firstname . ' ' . $request->lastname,
                    'birthday' => $request->birthday,
                    'gender' => $request->gender,
                    'mobile_number' => $request->mobile_number,
                    'mobile_country_code' => $request->mobile_country_code,
                    'nationality' => $request->nationality,
                    'home_country' => $request->home_country,
                    'home_city' => $request->home_city,
                    'preferred_language' => $request->preferred_language ?? 'en',
                    'preferred_currency' => $request->preferred_currency ?? 'USD',
                    'confirmed' => 1, // Mark as confirmed
                    'updated_at' => now(),
                ]);

            $updatedUser = DB::table('users')->where('user_refid', $userRefId)->first();

            return response()->json([
                'success' => true,
                'message' => 'Profile completed successfully',
                'data' => [
                    'user_refid' => $updatedUser->user_refid,
                    'email' => $updatedUser->email,
                    'firstname' => $updatedUser->firstname,
                    'lastname' => $updatedUser->lastname,
                    'display_name' => $updatedUser->display_name,
                    'birthday' => $updatedUser->birthday,
                    'gender' => $updatedUser->gender,
                    'mobile_number' => $updatedUser->mobile_number,
                    'confirmed' => (bool)$updatedUser->confirmed,
                    'referral_code' => $updatedUser->referral_code,
                ]
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while completing profile',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Login existing user
     * POST /api/auth/firebase/login
     */
    public function login(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'firebase_uid' => 'required|string',
                'email' => 'required|email',
                'auth_method' => 'nullable|string|in:email_password,google,facebook,apple,phone',
                'device_info' => 'nullable|array',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = DB::table('users')
                ->where('firebase_uid', $request->firebase_uid)
                ->where('email', $request->email)
                ->first();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found. Please register first.'
                ], 404);
            }

            // Check if account is active
            if ($user->account_status !== 'active') {
                return response()->json([
                    'success' => false,
                    'message' => "Account is {$user->account_status}. Please contact support."
                ], 403);
            }

            // Update last login information
            DB::table('users')
                ->where('user_refid', $user->user_refid)
                ->update([
                    'last_login_at' => now(),
                    'last_login_ip' => $this->getIpAddress($request),
                    'last_login_device' => $request->input('device_info.device_type', 'web'),
                    'updated_at' => now(),
                ]);

            // Log authentication history
            $deviceInfo = $request->input('device_info', []);
            $this->logAuthHistory([
                'user_refid' => $user->user_refid,
                'firebase_uid' => $request->firebase_uid,
                'auth_method' => $request->auth_method ?? 'email_password',
                'auth_status' => 'success',
                'auth_type' => 'login',
                'device_type' => $deviceInfo['device_type'] ?? null,
                'device_model' => $deviceInfo['device_model'] ?? null,
                'os_version' => $deviceInfo['os_version'] ?? null,
                'app_version' => $deviceInfo['app_version'] ?? null,
                'user_agent' => $this->getUserAgent($request),
                'ip_address' => $this->getIpAddress($request),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Login successful',
                'data' => [
                    'user_refid' => $user->user_refid,
                    'email' => $user->email,
                    'firstname' => $user->firstname,
                    'lastname' => $user->lastname,
                    'display_name' => $user->display_name,
                    'profile_photo_url' => $user->profile_photo_url,
                    'confirmed' => (bool)$user->confirmed,
                    'is_operator' => (bool)$user->is_operator,
                    'is_admin' => (bool)$user->is_admin,
                    'is_staff' => (bool)$user->is_staff,
                    'member_tier' => $user->member_tier,
                    'loyalty_points' => $user->loyalty_points,
                    'preferred_language' => $user->preferred_language,
                    'preferred_currency' => $user->preferred_currency,
                ]
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred during login',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update user's GPS location
     * POST /api/auth/firebase/update-location
     */
    public function updateLocation(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'user_refid' => 'required|string|exists:users,user_refid',
                'gps_live' => 'required|array',
                'gps_live.0' => 'required|numeric|between:-180,180', // longitude
                'gps_live.1' => 'required|numeric|between:-90,90',   // latitude
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }

            $longitude = $request->gps_live[0];
            $latitude = $request->gps_live[1];
            
            // Update GPS location using POINT
            DB::table('users')
                ->where('user_refid', $request->user_refid)
                ->update([
                    'gps_live' => DB::raw("POINT($longitude, $latitude)"),
                    'updated_at' => now(),
                ]);

            return response()->json([
                'success' => true,
                'message' => 'Location updated successfully'
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while updating location',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Logout user
     * POST /api/auth/firebase/logout
     */
    public function logout(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'user_refid' => 'required|string|exists:users,user_refid',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = DB::table('users')->where('user_refid', $request->user_refid)->first();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found'
                ], 404);
            }

            // Log logout in auth_history
            $this->logAuthHistory([
                'user_refid' => $request->user_refid,
                'firebase_uid' => $user->firebase_uid,
                'auth_method' => 'email_password',
                'auth_status' => 'success',
                'auth_type' => 'logout',
                'ip_address' => $this->getIpAddress($request),
                'user_agent' => $this->getUserAgent($request),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Logout successful'
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred during logout',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user profile information
     * GET /api/auth/firebase/profile/{user_refid}
     */
    public function getProfile($userRefId)
    {
        try {
            $user = DB::table('users')->where('user_refid', $userRefId)->first();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Profile retrieved successfully',
                'data' => [
                    'user_refid' => $user->user_refid,
                    'email' => $user->email,
                    'email_verified' => (bool)$user->email_verified,
                    'firstname' => $user->firstname,
                    'lastname' => $user->lastname,
                    'display_name' => $user->display_name,
                    'mobile_number' => $user->mobile_number,
                    'mobile_country_code' => $user->mobile_country_code,
                    'birthday' => $user->birthday,
                    'gender' => $user->gender,
                    'profile_photo_url' => $user->profile_photo_url,
                    'confirmed' => (bool)$user->confirmed,
                    'is_operator' => (bool)$user->is_operator,
                    'is_admin' => (bool)$user->is_admin,
                    'is_staff' => (bool)$user->is_staff,
                    'account_status' => $user->account_status,
                    'preferred_language' => $user->preferred_language,
                    'preferred_currency' => $user->preferred_currency,
                    'home_country' => $user->home_country,
                    'home_city' => $user->home_city,
                    'nationality' => $user->nationality,
                    'member_tier' => $user->member_tier,
                    'loyalty_points' => $user->loyalty_points,
                    'referral_code' => $user->referral_code,
                    'created_at' => $user->created_at,
                    'last_login_at' => $user->last_login_at,
                ]
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while retrieving profile',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}