/**
 * AuthFirebaseServices.ts
 * Service for managing Firebase authentication API requests
 * Location: packages/shared-core/shared-services/AuthFirebaseServices.ts
 */

// API Base URL - Update this based on your environment
const API_BASE_URL = import.meta.env.VITE_API_URL || 'http://localhost:8000';

/**
 * Type Definitions
 */

export interface DeviceInfo {
  device_type?: string;
  device_model?: string;
  os_version?: string;
  app_version?: string;
  browser?: string;
}

export interface CheckEmailRequest {
  email: string;
}

export interface CheckEmailResponse {
  success: boolean;
  message: string;
  data?: {
    exists: boolean;
    user_refid?: string;
    confirmed?: boolean;
  };
}

export interface RegisterRequest {
  firebase_uid: string;
  email: string;
  email_verified?: boolean;
  display_name?: string;
  profile_photo_url?: string;
  auth_method?: 'email_password' | 'google' | 'facebook' | 'apple' | 'phone';
  device_info?: DeviceInfo;
}

export interface RegisterResponse {
  success: boolean;
  message: string;
  data?: {
    user_refid: string;
    email: string;
    display_name?: string;
    confirmed: boolean;
    referral_code: string;
    requires_profile_completion: boolean;
  };
}

export interface CompleteProfileRequest {
  user_refid: string;
  firstname: string;
  lastname: string;
  birthday: string; // Format: YYYY-MM-DD
  gender: 'male' | 'female' | 'other' | 'prefer_not_to_say';
  mobile_number?: string;
  mobile_country_code?: string;
  nationality?: string;
  home_country?: string;
  home_city?: string;
  preferred_language?: string;
  preferred_currency?: string;
}

export interface CompleteProfileResponse {
  success: boolean;
  message: string;
  data?: {
    user_refid: string;
    email: string;
    firstname: string;
    lastname: string;
    display_name: string;
    birthday: string;
    gender: string;
    mobile_number?: string;
    confirmed: boolean;
    referral_code: string;
  };
}

export interface LoginRequest {
  firebase_uid: string;
  email: string;
  auth_method?: 'email_password' | 'google' | 'facebook' | 'apple' | 'phone';
  device_info?: DeviceInfo;
}

export interface UserData {
  user_refid: string;
  email: string;
  firstname?: string;
  lastname?: string;
  display_name?: string;
  profile_photo_url?: string;
  confirmed: boolean;
  is_operator: boolean;
  is_admin: boolean;
  is_staff: boolean;
  member_tier: string;
  loyalty_points: number;
  preferred_language: string;
  preferred_currency: string;
}

export interface LoginResponse {
  success: boolean;
  message: string;
  data?: UserData;
}

export interface UpdateLocationRequest {
  user_refid: string;
  gps_live: [number, number]; // [longitude, latitude]
}

export interface LogoutRequest {
  user_refid: string;
}

export interface GetProfileResponse {
  success: boolean;
  message: string;
  data?: {
    user_refid: string;
    email: string;
    email_verified: boolean;
    firstname?: string;
    lastname?: string;
    display_name?: string;
    mobile_number?: string;
    mobile_country_code?: string;
    birthday?: string;
    gender?: string;
    profile_photo_url?: string;
    confirmed: boolean;
    is_operator: boolean;
    is_admin: boolean;
    is_staff: boolean;
    account_status: string;
    preferred_language: string;
    preferred_currency: string;
    home_country?: string;
    home_city?: string;
    nationality?: string;
    member_tier: string;
    loyalty_points: number;
    referral_code: string;
    created_at: string;
    last_login_at?: string;
  };
}

export interface ApiResponse {
  success: boolean;
  message: string;
  data?: any;
  errors?: any;
}

/**
 * Helper function to make API requests
 */
async function makeApiRequest<T>(
  endpoint: string,
  method: 'GET' | 'POST' | 'PUT' | 'DELETE' = 'POST',
  data?: any
): Promise<T> {
  try {
    const url = `${API_BASE_URL}${endpoint}`;
    
    const options: RequestInit = {
      method,
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        // Add authorization token if available
        // 'Authorization': `Bearer ${getAuthToken()}`,
      },
      credentials: 'include', // Include cookies for session management
    };

    if (data && (method === 'POST' || method === 'PUT')) {
      options.body = JSON.stringify(data);
    }

    const response = await fetch(url, options);
    const result = await response.json();

    if (!response.ok) {
      throw new Error(result.message || 'API request failed');
    }

    return result as T;
  } catch (error) {
    console.error('API Request Error:', error);
    throw error;
  }
}

/**
 * Check if email already exists in the system
 * @param email - Email address to check
 * @returns CheckEmailResponse
 */
export async function checkEmail(email: string): Promise<CheckEmailResponse> {
  return makeApiRequest<CheckEmailResponse>(
    '/api/auth/firebase/check-email', 
    'POST', 
    { email }
  );
}

/**
 * Register a new user with Firebase authentication
 * @param data - Registration data including Firebase UID and user info
 * @returns RegisterResponse
 */
export async function registerUser(data: RegisterRequest): Promise<RegisterResponse> {
  return makeApiRequest<RegisterResponse>(
    '/api/auth/firebase/register', 
    'POST', 
    data
  );
}

/**
 * Complete user profile after initial registration
 * @param data - Profile completion data
 * @returns CompleteProfileResponse
 */
export async function completeProfile(data: CompleteProfileRequest): Promise<CompleteProfileResponse> {
  return makeApiRequest<CompleteProfileResponse>(
    '/api/auth/firebase/complete-profile', 
    'POST', 
    data
  );
}

/**
 * Login existing user
 * @param data - Login credentials including Firebase UID
 * @returns LoginResponse with user data
 */
export async function loginUser(data: LoginRequest): Promise<LoginResponse> {
  return makeApiRequest<LoginResponse>(
    '/api/auth/firebase/login', 
    'POST', 
    data
  );
}

/**
 * Update user's GPS location
 * @param data - User ref ID and GPS coordinates
 * @returns ApiResponse
 */
export async function updateLocation(data: UpdateLocationRequest): Promise<ApiResponse> {
  return makeApiRequest<ApiResponse>(
    '/api/auth/firebase/update-location', 
    'POST', 
    data
  );
}

/**
 * Logout user
 * @param data - User ref ID
 * @returns ApiResponse
 */
export async function logoutUser(data: LogoutRequest): Promise<ApiResponse> {
  return makeApiRequest<ApiResponse>(
    '/api/auth/firebase/logout', 
    'POST', 
    data
  );
}

/**
 * Get user profile information
 * @param userRefId - User reference ID
 * @returns GetProfileResponse with complete user data
 */
export async function getUserProfile(userRefId: string): Promise<GetProfileResponse> {
  return makeApiRequest<GetProfileResponse>(
    `/api/auth/firebase/profile/${userRefId}`, 
    'GET'
  );
}

/**
 * Get current device information
 * This is a helper function to gather device info for the apps
 * @returns DeviceInfo object
 */
export function getDeviceInfo(): DeviceInfo {
  const userAgent = navigator.userAgent;
  let deviceType = 'web';
  let browser = 'unknown';

  // Detect device type
  if (/Mobile|Android|iPhone|iPad|iPod/.test(userAgent)) {
    if (/iPad/.test(userAgent)) {
      deviceType = 'tablet';
    } else {
      deviceType = 'mobile';
    }
  } else if (/Tablet/.test(userAgent)) {
    deviceType = 'tablet';
  }

  // Detect browser
  if (userAgent.indexOf('Chrome') > -1) {
    browser = 'Chrome';
  } else if (userAgent.indexOf('Safari') > -1) {
    browser = 'Safari';
  } else if (userAgent.indexOf('Firefox') > -1) {
    browser = 'Firefox';
  } else if (userAgent.indexOf('Edge') > -1) {
    browser = 'Edge';
  }

  return {
    device_type: deviceType,
    device_model: navigator.platform || 'unknown',
    os_version: getOSVersion(),
    app_version: import.meta.env.VITE_APP_VERSION || '1.0.0',
    browser: browser,
  };
}

/**
 * Helper function to get OS version from user agent
 * @returns OS version string
 */
function getOSVersion(): string {
  const userAgent = navigator.userAgent;
  let os = 'unknown';

  if (userAgent.indexOf('Win') !== -1) {
    os = 'Windows';
  } else if (userAgent.indexOf('Mac') !== -1) {
    os = 'MacOS';
  } else if (userAgent.indexOf('Linux') !== -1) {
    os = 'Linux';
  } else if (userAgent.indexOf('Android') !== -1) {
    const match = userAgent.match(/Android\s([0-9.]*)/);
    os = match ? `Android ${match[1]}` : 'Android';
  } else if (userAgent.indexOf('iPhone') !== -1 || userAgent.indexOf('iPad') !== -1) {
    const match = userAgent.match(/OS\s([0-9_]*)/);
    os = match ? `iOS ${match[1].replace(/_/g, '.')}` : 'iOS';
  }

  return os;
}

/**
 * Helper function to validate email format
 * @param email - Email address to validate
 * @returns boolean
 */
export function isValidEmail(email: string): boolean {
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  return emailRegex.test(email);
}

/**
 * Helper function to format date for API
 * @param date - Date object or string
 * @returns Formatted date string (YYYY-MM-DD)
 */
export function formatDateForAPI(date: Date | string): string {
  const d = new Date(date);
  const year = d.getFullYear();
  const month = String(d.getMonth() + 1).padStart(2, '0');
  const day = String(d.getDate()).padStart(2, '0');
  return `${year}-${month}-${day}`;
}

/**
 * Store user data in local storage
 * @param userData - User data to store
 */
export function storeUserData(userData: UserData): void {
  try {
    localStorage.setItem('exploria_user', JSON.stringify(userData));
  } catch (error) {
    console.error('Failed to store user data:', error);
  }
}

/**
 * Get stored user data from local storage
 * @returns UserData or null
 */
export function getStoredUserData(): UserData | null {
  try {
    const data = localStorage.getItem('exploria_user');
    return data ? JSON.parse(data) : null;
  } catch (error) {
    console.error('Failed to retrieve user data:', error);
    return null;
  }
}

/**
 * Clear stored user data from local storage
 */
export function clearStoredUserData(): void {
  try {
    localStorage.removeItem('exploria_user');
  } catch (error) {
    console.error('Failed to clear user data:', error);
  }
}

/**
 * Check if user is authenticated (has stored data)
 * @returns boolean
 */
export function isUserAuthenticated(): boolean {
  return getStoredUserData() !== null;
}

// Export all functions as default
export default {
  checkEmail,
  registerUser,
  completeProfile,
  loginUser,
  updateLocation,
  logoutUser,
  getUserProfile,
  getDeviceInfo,
  isValidEmail,
  formatDateForAPI,
  storeUserData,
  getStoredUserData,
  clearStoredUserData,
  isUserAuthenticated,
};
