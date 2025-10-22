import axios from 'axios';

/**
 * Utility function to get a specific cookie by name.
 * @param {string} name - The name of the cookie to retrieve.
 * @returns {string|null} The cookie value or null if not found.
 */
function getCookie(name) {
    const value = `; ${document.cookie}`;
    const parts = value.split(`; ${name}=`);
    if (parts.length === 2) {
        return parts.pop().split(';').shift();
    }
    return null;
}

/**
 * Create an Axios instance with default configurations.
 */
const apiClient = axios.create({
    baseURL: process.env.API_URL || 'http://localhost/api/',
    headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json',
    },
    withCredentials: true, // Send cookies with each request
    withXSRFToken: true,
});

/**
 * Flag to track if the CSRF token has been fetched already.
 */
let csrfFetched = false;

/**
 * Fetch CSRF Token (only call this once).
 */
export const fetchCsrfToken = async () => {
    if (!csrfFetched) {
        try {
            await apiClient.get('/../sanctum/csrf-cookie'); // Fetch CSRF token from backend
            csrfFetched = true;  // Mark that the CSRF token has been fetched
        } catch (error) {
            console.error('Error fetching CSRF token', error);
        }
    }
};

let csrfToken = getCookie('XSRF-TOKEN');

if(!csrfToken){
    fetchCsrfToken();
}


/**
 * Request interceptor to attach CSRF token and Authorization header.
 */
apiClient.interceptors.request.use(
    (config) => {
        // Get CSRF Token from cookies

        // Attach CSRF Token to the request
        if (csrfToken) {
            config.headers['X-XSRF-TOKEN'] = decodeURIComponent(csrfToken);
        }

        // Handle Bearer Token
        const token = localStorage.getItem('token');
        if (token) {
            config.headers['Authorization'] = `Bearer ${token}`;
        }

        return config;
    },
    (error) => {
        return Promise.reject(error);
    }
);

// Export functions for making API calls.
export const get = (endpoint, params = {}) => {
    return apiClient.get(endpoint, { params });
};

export const post = (endpoint, data = {}) => {
    return apiClient.post(endpoint, data);
};

export const put = (endpoint, data = {}) => {
    return apiClient.put(endpoint, data);
};

export const del = (endpoint) => {
    return apiClient.delete(endpoint);
};

export const find = (needle, haystack, searchOnKey) => {
    return Object.keys(haystack).find(key => haystack[key][searchOnKey] === needle) || null
};

export const getCookieValue = (name) => {
    let cookie = getCookie(name);
    if(cookie){
        return decodeURIComponent(cookie);
    }
    return null;
};

export default apiClient;
