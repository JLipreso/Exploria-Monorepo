<?php

namespace App\Http\Controllers\Portal;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;

/**
 * AuthPortalController
 * 
 * Manages authentication for Administrator, Operator, and Staff portals
 * All users must have confirmed=1 and their respective role flag set to true
 * 
 * @package App\Http\Controllers\Portal
 */
class AuthPortalController extends Controller
{
    /**
     * General login method for all portal types
     * Validates user exists, account is confirmed, and has appropriate role
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        try {
            // Validate request
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
                'firebase_uid' => 'required|string',
                'portal_type' => 'required|in:admin,staff,operator',
                'device_info' => 'nullable|array',
                'ip_address' => 'nullable|string',
                'user_agent' => 'nullable|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $email = $request->email;
            $firebaseUid = $request->firebase_uid;
            $portalType = $request->portal_type;

            // Check if user exists
            $user = DB::table('users')
                ->where('email', $email)
                ->where('firebase_uid', $firebaseUid)
                ->first();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found or Firebase UID mismatch'
                ], 404);
            }

            // Check if account is confirmed
            if ($user->confirmed != 1) {
                return response()->json([
                    'success' => false,
                    'message' => 'Account not confirmed. Please complete your profile first.',
                    'requires_profile_completion' => true,
                    'user_refid' => $user->user_refid
                ], 403);
            }

            // Check role-based access
            $roleCheck = $this->checkRoleAccess($user, $portalType);
            if (!$roleCheck['allowed']) {
                return response()->json([
                    'success' => false,
                    'message' => $roleCheck['message']
                ], 403);
            }

            // Update last login
            DB::table('users')
                ->where('user_refid', $user->user_refid)
                ->update([
                    'last_login_at' => now(),
                    'updated_at' => now()
                ]);

            // Log authentication
            $this->logAuthentication($user, $request, 'login', $portalType);

            return response()->json([
                'success' => true,
                'message' => 'Login successful',
                'data' => [
                    'user_refid' => $user->user_refid,
                    'email' => $user->email,
                    'display_name' => $user->display_name,
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'profile_photo_url' => $user->profile_photo_url,
                    'portal_type' => $portalType,
                    'is_admin' => (bool) $user->is_admin,
                    'is_staff' => (bool) $user->is_staff,
                    'is_operator' => (bool) $user->is_operator
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Login failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Admin-specific login
     * Requires: confirmed=1 AND is_admin=1
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function adminLogin(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
                'firebase_uid' => 'required|string',
                'device_info' => 'nullable|array',
                'ip_address' => 'nullable|string',
                'user_agent' => 'nullable|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Check user exists and is confirmed
            $user = DB::table('users')
                ->where('email', $request->email)
                ->where('firebase_uid', $request->firebase_uid)
                ->where('confirmed', 1)
                ->where('is_admin', 1)
                ->first();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Access denied. Administrator account not found or not authorized.'
                ], 403);
            }

            // Update last login
            DB::table('users')
                ->where('user_refid', $user->user_refid)
                ->update([
                    'last_login_at' => now(),
                    'updated_at' => now()
                ]);

            // Log authentication
            $this->logAuthentication($user, $request, 'login', 'admin');

            return response()->json([
                'success' => true,
                'message' => 'Administrator login successful',
                'data' => [
                    'user_refid' => $user->user_refid,
                    'email' => $user->email,
                    'display_name' => $user->display_name,
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'profile_photo_url' => $user->profile_photo_url,
                    'portal_type' => 'admin',
                    'is_admin' => true
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Admin login failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Staff-specific login
     * Requires: confirmed=1 AND is_staff=1
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function staffLogin(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
                'firebase_uid' => 'required|string',
                'device_info' => 'nullable|array',
                'ip_address' => 'nullable|string',
                'user_agent' => 'nullable|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Check user exists and is confirmed
            $user = DB::table('users')
                ->where('email', $request->email)
                ->where('firebase_uid', $request->firebase_uid)
                ->where('confirmed', 1)
                ->where('is_staff', 1)
                ->first();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Access denied. Staff account not found or not authorized.'
                ], 403);
            }

            // Update last login
            DB::table('users')
                ->where('user_refid', $user->user_refid)
                ->update([
                    'last_login_at' => now(),
                    'updated_at' => now()
                ]);

            // Log authentication
            $this->logAuthentication($user, $request, 'login', 'staff');

            return response()->json([
                'success' => true,
                'message' => 'Staff login successful',
                'data' => [
                    'user_refid' => $user->user_refid,
                    'email' => $user->email,
                    'display_name' => $user->display_name,
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'profile_photo_url' => $user->profile_photo_url,
                    'portal_type' => 'staff',
                    'is_staff' => true
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Staff login failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Operator-specific login
     * Requires: confirmed=1 AND is_operator=1
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function operatorLogin(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
                'firebase_uid' => 'required|string',
                'device_info' => 'nullable|array',
                'ip_address' => 'nullable|string',
                'user_agent' => 'nullable|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Check user exists and is confirmed
            $user = DB::table('users')
                ->where('email', $request->email)
                ->where('firebase_uid', $request->firebase_uid)
                ->where('confirmed', 1)
                ->where('is_operator', 1)
                ->first();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Access denied. Operator account not found or not authorized.'
                ], 403);
            }

            // Update last login
            DB::table('users')
                ->where('user_refid', $user->user_refid)
                ->update([
                    'last_login_at' => now(),
                    'updated_at' => now()
                ]);

            // Log authentication
            $this->logAuthentication($user, $request, 'login', 'operator');

            return response()->json([
                'success' => true,
                'message' => 'Operator login successful',
                'data' => [
                    'user_refid' => $user->user_refid,
                    'email' => $user->email,
                    'display_name' => $user->display_name,
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'profile_photo_url' => $user->profile_photo_url,
                    'portal_type' => 'operator',
                    'is_operator' => true
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Operator login failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check if user has access to specific portal type
     * 
     * @param object $user
     * @param string $portalType
     * @return array
     */
    private function checkRoleAccess($user, $portalType)
    {
        switch ($portalType) {
            case 'admin':
                if ($user->is_admin != 1) {
                    return [
                        'allowed' => false,
                        'message' => 'Access denied. Administrator privileges required.'
                    ];
                }
                break;
            
            case 'staff':
                if ($user->is_staff != 1) {
                    return [
                        'allowed' => false,
                        'message' => 'Access denied. Staff privileges required.'
                    ];
                }
                break;
            
            case 'operator':
                if ($user->is_operator != 1) {
                    return [
                        'allowed' => false,
                        'message' => 'Access denied. Operator privileges required.'
                    ];
                }
                break;
            
            default:
                return [
                    'allowed' => false,
                    'message' => 'Invalid portal type specified.'
                ];
        }

        return ['allowed' => true, 'message' => ''];
    }

    /**
     * Log authentication event to auth_history table
     * 
     * @param object $user
     * @param Request $request
     * @param string $eventType
     * @param string $portalType
     * @return void
     */
    private function logAuthentication($user, Request $request, $eventType, $portalType)
    {
        try {
            // Generate auth_refid
            $authRefid = $this->generateAuthRefid();

            // Get device information
            $deviceInfo = $request->device_info ?? [];
            
            DB::table('auth_history')->insert([
                'auth_refid' => $authRefid,
                'user_refid' => $user->user_refid,
                'firebase_uid' => $user->firebase_uid,
                'event_type' => $eventType,
                'auth_method' => $user->auth_method ?? 'firebase',
                'portal_type' => $portalType,
                'ip_address' => $request->ip_address ?? $request->ip(),
                'user_agent' => $request->user_agent ?? $request->header('User-Agent'),
                'device_type' => $deviceInfo['device_type'] ?? null,
                'device_name' => $deviceInfo['device_name'] ?? null,
                'os_name' => $deviceInfo['os_name'] ?? null,
                'os_version' => $deviceInfo['os_version'] ?? null,
                'browser_name' => $deviceInfo['browser_name'] ?? null,
                'browser_version' => $deviceInfo['browser_version'] ?? null,
                'app_version' => $deviceInfo['app_version'] ?? null,
                'success' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        } catch (\Exception $e) {
            // Log error but don't fail the login
            \Log::error('Failed to log authentication: ' . $e->getMessage());
        }
    }

    /**
     * Generate unique auth_refid
     * Format: AUT-DDMMYYYYHHMMSS-XXX
     * 
     * @return string
     */
    private function generateAuthRefid()
    {
        $date = now()->format('dmYHis');
        $random = strtoupper(substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 3));
        return "AUT-{$date}-{$random}";
    }

    /**
     * Logout user
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'user_refid' => 'required|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = DB::table('users')
                ->where('user_refid', $request->user_refid)
                ->first();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found'
                ], 404);
            }

            // Log logout event
            $this->logAuthentication($user, $request, 'logout', $request->portal_type ?? 'unknown');

            return response()->json([
                'success' => true,
                'message' => 'Logout successful'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Logout failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Verify user session and portal access
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function verifySession(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'user_refid' => 'required|string',
                'portal_type' => 'required|in:admin,staff,operator'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = DB::table('users')
                ->where('user_refid', $request->user_refid)
                ->first();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Session invalid: User not found'
                ], 401);
            }

            // Check if account is still confirmed
            if ($user->confirmed != 1) {
                return response()->json([
                    'success' => false,
                    'message' => 'Session invalid: Account not confirmed'
                ], 401);
            }

            // Check role-based access
            $roleCheck = $this->checkRoleAccess($user, $request->portal_type);
            if (!$roleCheck['allowed']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Session invalid: ' . $roleCheck['message']
                ], 401);
            }

            return response()->json([
                'success' => true,
                'message' => 'Session valid',
                'data' => [
                    'user_refid' => $user->user_refid,
                    'email' => $user->email,
                    'display_name' => $user->display_name,
                    'portal_type' => $request->portal_type
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Session verification failed: ' . $e->getMessage()
            ], 500);
        }
    }
}