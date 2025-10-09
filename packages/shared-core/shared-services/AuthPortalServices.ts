import project from "../shared-utils/project.json";

export interface PortalLoginRequest {
    email: string;
    firebase_uid: string;
    portal_type?: 'admin' | 'staff' | 'operator';
    device_info?: DeviceInfo;
    ip_address?: string;
    user_agent?: string;
  }
  
  export interface DeviceInfo {
    device_type?: string;
    device_name?: string;
    os_name?: string;
    os_version?: string;
    browser_name?: string;
    browser_version?: string;
    app_version?: string;
  }
  
  export interface PortalLoginResponse {
    success: boolean;
    message: string;
    data?: {
      user_refid: string;
      email: string;
      display_name: string;
      first_name: string;
      last_name: string;
      profile_photo_url: string;
      portal_type: 'admin' | 'staff' | 'operator';
      is_admin?: boolean;
      is_staff?: boolean;
      is_operator?: boolean;
    };
    requires_profile_completion?: boolean;
    errors?: any;
  }
  
  export interface LogoutRequest {
    user_refid: string;
    portal_type?: 'admin' | 'staff' | 'operator';
  }
  
  export interface VerifySessionRequest {
    user_refid: string;
    portal_type: 'admin' | 'staff' | 'operator';
  }
  
  // Get API base URL from environment
  const API_BASE_URL = project.server.api;
  
  /**
   * General portal login (auto-detect portal type)
   * 
   * @param loginData - Login credentials and portal type
   * @returns Promise<PortalLoginResponse>
   */
  export async function portalLogin(
    loginData: PortalLoginRequest
  ): Promise<PortalLoginResponse> {
    try {
      const response = await fetch(`${API_BASE_URL}portal/auth/login`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
        body: JSON.stringify(loginData),
      });
  
      const data = await response.json();
      return data;
    } catch (error) {
      console.error('Portal login error:', error);
      return {
        success: false,
        message: error instanceof Error ? error.message : 'Login failed',
      };
    }
  }
  
  /**
   * Administrator login
   * Requires: confirmed=1 AND is_admin=1
   * 
   * @param loginData - Admin login credentials
   * @returns Promise<PortalLoginResponse>
   */
  export async function adminLogin(
    loginData: Omit<PortalLoginRequest, 'portal_type'>
  ): Promise<PortalLoginResponse> {
    try {
      const response = await fetch(`${API_BASE_URL}portal/auth/admin/login`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
        body: JSON.stringify(loginData),
      });
  
      const data = await response.json();
      return data;
    } catch (error) {
      console.error('Admin login error:', error);
      return {
        success: false,
        message: error instanceof Error ? error.message : 'Admin login failed',
      };
    }
  }
  
  /**
   * Staff login
   * Requires: confirmed=1 AND is_staff=1
   * 
   * @param loginData - Staff login credentials
   * @returns Promise<PortalLoginResponse>
   */
  export async function staffLogin(
    loginData: Omit<PortalLoginRequest, 'portal_type'>
  ): Promise<PortalLoginResponse> {
    try {
      const response = await fetch(`${API_BASE_URL}portal/auth/staff/login`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
        body: JSON.stringify(loginData),
      });
  
      const data = await response.json();
      return data;
    } catch (error) {
      console.error('Staff login error:', error);
      return {
        success: false,
        message: error instanceof Error ? error.message : 'Staff login failed',
      };
    }
  }
  
  /**
   * Operator login
   * Requires: confirmed=1 AND is_operator=1
   * 
   * @param loginData - Operator login credentials
   * @returns Promise<PortalLoginResponse>
   */
  export async function operatorLogin(
    loginData: Omit<PortalLoginRequest, 'portal_type'>
  ): Promise<PortalLoginResponse> {
    try {
      const response = await fetch(`${API_BASE_URL}portal/auth/operator/login`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
        body: JSON.stringify(loginData),
      });
  
      const data = await response.json();
      return data;
    } catch (error) {
      console.error('Operator login error:', error);
      return {
        success: false,
        message: error instanceof Error ? error.message : 'Operator login failed',
      };
    }
  }
  
  /**
   * Logout user from portal
   * 
   * @param logoutData - Logout request data
   * @returns Promise<PortalLoginResponse>
   */
  export async function portalLogout(
    logoutData: LogoutRequest
  ): Promise<PortalLoginResponse> {
    try {
      const response = await fetch(`${API_BASE_URL}portal/auth/logout`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
        body: JSON.stringify(logoutData),
      });
  
      const data = await response.json();
      return data;
    } catch (error) {
      console.error('Logout error:', error);
      return {
        success: false,
        message: error instanceof Error ? error.message : 'Logout failed',
      };
    }
  }
  
  /**
   * Verify user session and portal access
   * 
   * @param sessionData - Session verification data
   * @returns Promise<PortalLoginResponse>
   */
  export async function verifyPortalSession(
    sessionData: VerifySessionRequest
  ): Promise<PortalLoginResponse> {
    try {
      const response = await fetch(`${API_BASE_URL}portal/auth/verify-session`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
        body: JSON.stringify(sessionData),
      });
  
      const data = await response.json();
      return data;
    } catch (error) {
      console.error('Session verification error:', error);
      return {
        success: false,
        message: error instanceof Error ? error.message : 'Session verification failed',
      };
    }
  }
  
  /**
   * Helper: Get current device information
   * 
   * @returns DeviceInfo
   */
  export function getDeviceInfo(): DeviceInfo {
    const userAgent = navigator.userAgent;
    
    // Detect device type
    let deviceType = 'desktop';
    if (/mobile/i.test(userAgent)) deviceType = 'mobile';
    else if (/tablet/i.test(userAgent)) deviceType = 'tablet';
    
    // Detect OS
    let osName = 'Unknown';
    let osVersion = '';
    if (userAgent.indexOf('Win') !== -1) {
      osName = 'Windows';
      const match = userAgent.match(/Windows NT (\d+\.\d+)/);
      if (match) osVersion = match[1];
    } else if (userAgent.indexOf('Mac') !== -1) {
      osName = 'MacOS';
      const match = userAgent.match(/Mac OS X (\d+[._]\d+[._]\d+)/);
      if (match) osVersion = match[1].replace(/_/g, '.');
    } else if (userAgent.indexOf('Linux') !== -1) {
      osName = 'Linux';
    } else if (userAgent.indexOf('Android') !== -1) {
      osName = 'Android';
      const match = userAgent.match(/Android (\d+\.\d+)/);
      if (match) osVersion = match[1];
    } else if (userAgent.indexOf('iOS') !== -1 || userAgent.indexOf('iPhone') !== -1) {
      osName = 'iOS';
      const match = userAgent.match(/OS (\d+_\d+)/);
      if (match) osVersion = match[1].replace('_', '.');
    }
    
    // Detect browser
    let browserName = 'Unknown';
    let browserVersion = '';
    if (userAgent.indexOf('Chrome') !== -1 && userAgent.indexOf('Edg') === -1) {
      browserName = 'Chrome';
      const match = userAgent.match(/Chrome\/(\d+\.\d+)/);
      if (match) browserVersion = match[1];
    } else if (userAgent.indexOf('Safari') !== -1 && userAgent.indexOf('Chrome') === -1) {
      browserName = 'Safari';
      const match = userAgent.match(/Version\/(\d+\.\d+)/);
      if (match) browserVersion = match[1];
    } else if (userAgent.indexOf('Firefox') !== -1) {
      browserName = 'Firefox';
      const match = userAgent.match(/Firefox\/(\d+\.\d+)/);
      if (match) browserVersion = match[1];
    } else if (userAgent.indexOf('Edg') !== -1) {
      browserName = 'Edge';
      const match = userAgent.match(/Edg\/(\d+\.\d+)/);
      if (match) browserVersion = match[1];
    }
    
    return {
      device_type: deviceType,
      device_name: navigator.platform,
      os_name: osName,
      os_version: osVersion,
      browser_name: browserName,
      browser_version: browserVersion,
      app_version: project.app.version || '1.0.0',
    };
  }
  
  /**
   * Helper: Get user's IP address (requires external API)
   * 
   * @returns Promise<string>
   */
  export async function getUserIpAddress(): Promise<string> {
    try {
      const response = await fetch('https://api.ipify.org?format=json');
      const data = await response.json();
      return data.ip;
    } catch (error) {
      console.error('Failed to get IP address:', error);
      return '';
    }
  }
  
  /**
   * Helper: Store user session in localStorage
   * 
   * @param userData - User data to store
   * @param portalType - Portal type
   */
  export function storePortalSession(
    userData: PortalLoginResponse['data'],
    portalType: 'admin' | 'staff' | 'operator'
  ): void {
    if (!userData) return;
    
    const sessionData = {
      ...userData,
      portal_type: portalType,
      logged_in_at: new Date().toISOString(),
    };
    
    localStorage.setItem(`${portalType}_session`, JSON.stringify(sessionData));
  }
  
  /**
   * Helper: Get stored portal session
   * 
   * @param portalType - Portal type
   * @returns Stored session data or null
   */
  export function getPortalSession(
    portalType: 'admin' | 'staff' | 'operator'
  ): any | null {
    try {
      const sessionData = localStorage.getItem(`${portalType}_session`);
      return sessionData ? JSON.parse(sessionData) : null;
    } catch (error) {
      console.error('Failed to get portal session:', error);
      return null;
    }
  }
  
  /**
   * Helper: Clear portal session
   * 
   * @param portalType - Portal type
   */
  export function clearPortalSession(
    portalType: 'admin' | 'staff' | 'operator'
  ): void {
    localStorage.removeItem(`${portalType}_session`);
  }
  
  /**
   * Helper: Check if user is logged in to portal
   * 
   * @param portalType - Portal type
   * @returns boolean
   */
  export function isLoggedInToPortal(
    portalType: 'admin' | 'staff' | 'operator'
  ): boolean {
    const session = getPortalSession(portalType);
    return session !== null && session.user_refid !== undefined;
  }