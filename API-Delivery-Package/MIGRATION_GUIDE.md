# 🔄 OutfitShop API — Frontend Migration & Integration Guide

Step-by-step instructions for frontend engineers integrating with the **OutfitShop Ecommerce Clothing API**.

---

## 🎨 Official Brand Assets

| Asset Type | Resource URL |
| :--- | :--- |
| **Primary Logo** | `https://res.cloudinary.com/od8t271n/image/upload/v1787064621/bleu-SNPCodeLab.png` |
| **Animated Cycle** | `https://res.cloudinary.com/od8t271n/image/upload/v1787062663/default-cycle-SNPCodeLab.gif` |
| **Secondary Logo** | `https://res.cloudinary.com/od8t271n/image/upload/v1787062664/bleu-SNPCodeLab.gif` |
| **Vector Icon** | `https://res.cloudinary.com/od8t271n/image/upload/v1787062662/anime-SNPCodeLab.svg` |
| **Brand Video** | `https://res.cloudinary.com/od8t271n/video/upload/v1787062665/default-cycle-SNPCodeLab.mp4` |

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
  const token = localStorage.getItem('outfitshop_access_token');
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  
  // Distributed Tracing
  config.headers['X-Request-Id'] = uuidv4();
  
  // Language Localization
  const locale = localStorage.getItem('outfitshop_locale') || 'en';
  config.headers['Accept-Language'] = locale;
  
  return config;
});

// Response Interceptor: Handle 401 Expiry & 429 Throttle
apiClient.interceptors.response.use(
  (response) => response,
  async (error) => {
    if (error.response?.status === 401) {
      localStorage.removeItem('outfitshop_access_token');
      window.location.href = '/login';
    }
    return Promise.reject(error);
  }
);
```

---

## 3. Endpoints Migration: Sales to Orders
Frontend applications should migrate calls from `/api/v1/sales/*` to `/api/v1/orders/*`. Both endpoints are actively supported for complete backward compatibility.
