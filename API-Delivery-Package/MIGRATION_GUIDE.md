# 🔄 Frontend Migration & Integration Guide

Step-by-step instructions for frontend engineers integrating with the CSMS RESTful API.

---

## 1. Environment Configuration

In your frontend root `.env.local` or `.env`:
```env
NEXT_PUBLIC_API_BASE_URL=https://api.kesararamwithdigital.tech/api/v1
NEXT_PUBLIC_DEFAULT_LOCALE=en
NEXT_PUBLIC_CURRENCY_PRIMARY=USD
NEXT_PUBLIC_CURRENCY_SECONDARY=KHR
```

---

## 2. Setting Up Axios Interceptors

```typescript
import axios from 'axios';
import { v4 as uuidv4 } from 'uuid';

export const apiClient = axios.create({
  baseURL: process.env.NEXT_PUBLIC_API_BASE_URL,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
});

// Request Interceptor: Attach Token & Trace ID
apiClient.interceptors.request.use((config) => {
  const token = localStorage.getItem('csms_access_token');
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  
  // Distributed Tracing
  config.headers['X-Request-Id'] = uuidv4();
  
  // Language Localization
  const locale = localStorage.getItem('csms_locale') || 'en';
  config.headers['Accept-Language'] = locale;
  
  return config;
});

// Response Interceptor: Handle 401 Expiry & 429 Throttle
apiClient.interceptors.response.use(
  (response) => response,
  async (error) => {
    if (error.response?.status === 401) {
      // Attempt token rotation or redirect to login
      localStorage.removeItem('csms_access_token');
      window.location.href = '/login';
    }
    return Promise.reject(error);
  }
);
```
