import config from '../shared-utils/config.json';

// API Base URL - Update this with your actual API URL
const API_BASE_URL = config.server.api || 'http://localhost:8000/api';

// Types and Interfaces
export interface UserData {
  user_refid: string;
  email: string;
  firstname?: string;
  lastname?: string;
  confirmed: boolean;
  is_customer?: boolean;
  is_rider?: boolean;
  is_partner?: boolean;
  is_franchisee?: boolean;
  is_admin?: boolean;
  is_staff?: boolean;
}

export interface CheckEmailResponse {
  success: boolean;
  exists: boolean;
  message: string;
  data?: UserData;
}

export interface RegisterRequest {
  firebase_uid: string;
  email: string;
  phone_number?: string;
  profile_photo_url?: string;
  app_access: 'customer_app' | 'rider_app' | 'partner_app' | 'franchisee_portal' | 'admin_portal';
  gps_init?: [number, number];
  device_info?: {
    device_type?: string;
    device_model?: string;
    os_version?: string;
    app_version?: string;
  };
}

export interface RegisterResponse {
  success: boolean;
  message: string;
  data?: UserData & {
    requires_confirmation: boolean;
  };
}

export interface CompleteProfileRequest {
  user_refid: string;
  firstname: string;
  lastname: string;
  birthday: string; // Format: YYYY-MM-DD
  gender: 'male' | 'female' | 'other';
}

export interface CompleteProfileResponse {
  success: boolean;
  message: string;
  data?: UserData & {
    birthday: string;
    gender: string;
  };
}

export interface LoginRequest {
  firebase_uid: string;
  email: string;
  app_access: 'customer_app' | 'rider_app' | 'partner_app' | 'franchisee_portal' | 'admin_portal';
  gps_init?: [number, number];
  device_info?: {
    device_type?: string;
    device_model?: string;
    os_version?: string;
    app_version?: string;
  };
}

export interface LoginResponse {
  success: boolean;
  message: string;
  data?: UserData;
}

export interface UpdateLocationRequest {
  user_refid: string;
  gps_live: [number, number];
}

export interface LogoutRequest {
  user_refid: string;
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
      },
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
 */
export async function checkEmail(email: string): Promise<CheckEmailResponse> {
  return makeApiRequest<CheckEmailResponse>('/auth/firebase/check-email', 'POST', { email });
}

/**
 * Register a new user with Firebase authentication
 */
export async function registerUser(data: RegisterRequest): Promise<RegisterResponse> {
  return makeApiRequest<RegisterResponse>('/auth/firebase/register', 'POST', data);
}

/**
 * Complete user profile after initial registration
 */
export async function completeProfile(data: CompleteProfileRequest): Promise<CompleteProfileResponse> {
  return makeApiRequest<CompleteProfileResponse>('/auth/firebase/complete-profile', 'POST', data);
}

/**
 * Login existing user
 */
export async function loginUser(data: LoginRequest): Promise<LoginResponse> {
  return makeApiRequest<LoginResponse>('/auth/firebase/login', 'POST', data);
}

/**
 * Update user's GPS location
 */
export async function updateLocation(data: UpdateLocationRequest): Promise<ApiResponse> {
  return makeApiRequest<ApiResponse>('/auth/firebase/update-location', 'POST', data);
}

/**
 * Logout user
 */
export async function logoutUser(data: LogoutRequest): Promise<ApiResponse> {
  return makeApiRequest<ApiResponse>('/auth/firebase/logout', 'POST', data);
}

/**
 * Get current device information
 * This is a helper function to gather device info for the apps
 */
export function getDeviceInfo(): {
  device_type?: string;
  device_model?: string;
  os_version?: string;
  app_version?: string;
} {
  // For web browsers
  if (typeof window !== 'undefined' && window.navigator) {
    const userAgent = window.navigator.userAgent;
    
    let device_type = 'web';
    if (/Android/i.test(userAgent)) {
      device_type = 'android';
    } else if (/iPhone|iPad|iPod/i.test(userAgent)) {
      device_type = 'ios';
    }

    return {
      device_type,
      device_model: window.navigator.platform || 'Unknown',
      os_version: userAgent,
      app_version: config.app.version || '1.0.0',
    };
  }

  // For Capacitor apps, you can enhance this with Capacitor Device API
  return {
    device_type: 'unknown',
    app_version: '1.0.0',
  };
}

/**
 * Get current GPS location
 * Returns a promise with [latitude, longitude]
 */
export function getCurrentLocation(): Promise<[number, number]> {
  return new Promise((resolve, reject) => {
    if ('geolocation' in navigator) {
      navigator.geolocation.getCurrentPosition(
        (position) => {
          resolve([position.coords.latitude, position.coords.longitude]);
        },
        (error) => {
          console.error('Geolocation error:', error);
          reject(error);
        },
        {
          enableHighAccuracy: true,
          timeout: 10000,
          maximumAge: 0,
        }
      );
    } else {
      reject(new Error('Geolocation is not supported'));
    }
  });
}

/**
 * Watch user location and call callback when location changes
 * Returns a watch ID that can be used to clear the watch
 */
export function watchUserLocation(
  callback: (location: [number, number]) => void
): number | null {
  if ('geolocation' in navigator) {
    return navigator.geolocation.watchPosition(
      (position) => {
        callback([position.coords.latitude, position.coords.longitude]);
      },
      (error) => {
        console.error('Geolocation watch error:', error);
      },
      {
        enableHighAccuracy: true,
        timeout: 10000,
        maximumAge: 0,
      }
    );
  }
  return null;
}

/**
 * Clear location watch
 */
export function clearLocationWatch(watchId: number): void {
  if ('geolocation' in navigator) {
    navigator.geolocation.clearWatch(watchId);
  }
}

export default {
  checkEmail,
  registerUser,
  completeProfile,
  loginUser,
  updateLocation,
  logoutUser,
  getDeviceInfo,
  getCurrentLocation,
  watchUserLocation,
  clearLocationWatch,
};