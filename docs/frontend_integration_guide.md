# Frontend Integration Guide — SS-MIS RESTful Web API (v1)

This guide provides standard integration patterns for connecting frontend frameworks (**React**, **Next.js**, **Vue**, **Angular**, **Flutter**, **React Native**, or **Vanilla JS**) to the **Clothing Sales Management System (SS-MIS) Backend API**.

---

## 1. API Configuration & Base Setup

### Base URL
- **Local Dev Server**: `http://127.0.0.1:8000/api/v1`
- **Production Server**: `https://your-domain.com/api/v1`

### Default Headers Required
Every HTTP request sent to the API must include the following headers:
```json
{
  "Accept": "application/json",
  "Content-Type": "application/json"
}
```

For authenticated endpoints, append the Sanctum Bearer token:
```json
{
  "Authorization": "Bearer YOUR_ACCESS_TOKEN_HERE"
}
```

---

## 2. Axios / Fetch Client Setup (JavaScript / TypeScript)

### Standard Axios Client (`apiClient.js`)
```javascript
import axios from 'axios';

const apiClient = axios.create({
  baseURL: 'http://127.0.0.1:8000/api/v1',
  headers: {
    'Accept': 'application/json',
    'Content-Type': 'application/json',
  },
});

// Request Interceptor: Inject Sanctum Bearer Token
apiClient.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('ssmis_token');
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
  },
  (error) => Promise.reject(error)
);

// Response Interceptor: Auto-handle 401 Unauthorized
apiClient.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response && error.response.status === 401) {
      localStorage.removeItem('ssmis_token');
      window.location.href = '/login';
    }
    return Promise.reject(error);
  }
);

export default apiClient;
```

---

## 3. Key Frontend Integration Workflows

### A. Employee Authentication Flow (`Login & Logout`)

```javascript
import apiClient from './apiClient';

// 1. Employee Login
export async function loginEmployee(username, password) {
  try {
    const response = await apiClient.post('/auth/login', {
      username,
      password,
    });
    
    // Save Bearer token and employee profile
    const { access_token, employee } = response.data.data;
    localStorage.setItem('ssmis_token', access_token);
    localStorage.setItem('ssmis_user', JSON.stringify(employee));

    return response.data;
  } catch (error) {
    throw error.response?.data?.message || 'Login failed';
  }
}

// 2. Logout Session
export async function logoutEmployee() {
  try {
    await apiClient.post('/auth/logout');
  } finally {
    localStorage.removeItem('ssmis_token');
    localStorage.removeItem('ssmis_user');
    window.location.href = '/login';
  }
}
```

---

### B. POS Checkout Receipt (`POS Module`)

```javascript
// Process POS Checkout Receipt
export async function checkoutPOSReceipt({ customerId, items, paymentMethod, paymentAmount, overallDiscount }) {
  const payload = {
    customer_id: customerId || null,
    items: items.map(item => ({
      variant_id: item.variantId,
      quantity: item.quantity,
      discount: item.discount || 0.0,
    })),
    payment_method: paymentMethod || 'CASH',
    payment_amount: paymentAmount,
    overall_discount: overallDiscount || 0.0,
  };

  const response = await apiClient.post('/sales/checkout', payload);
  return response.data.data; // Returns full receipt object with sale_id, grand_total, and payment details
}
```

---

### C. Barcode Scanning & Product Lookup

```javascript
// Scan Barcode / SKU for instant POS item add
export async function lookupBarcode(scannedBarcode) {
  const response = await apiClient.get(`/variants/barcode/${encodeURIComponent(scannedBarcode)}`);
  return response.data.data; // Returns variant with product_name, sku, sale_price, stock quantity
}
```

---

### D. Inventory & Low Stock Dashboard Widget

```javascript
// Get Low Stock Items for Reorder Alerts
export async function fetchLowStockItems() {
  const response = await apiClient.get('/variants/low-stock');
  return response.data.data; // Returns list of variants where quantity <= reorder_level
}
```

---

## 4. Standard Response & Error Structure

### Successful Response (HTTP 200 / 201)
```json
{
  "success": true,
  "message": "POS Checkout completed successfully",
  "data": {
    "sale_id": 1,
    "total_amount": "50.00",
    "discount": "0.00",
    "grand_total": "50.00",
    "status": "COMPLETED"
  }
}
```

### Error Response (HTTP 400 / 422 / 403 / 401)
```json
{
  "success": false,
  "message": "Insufficient stock quantity for variant TSHIRT-BLK-M. Requested: 10, Available: 2",
  "errors": null
}
```

---

## 5. Security Best Practices for Frontend Developers
1. **Never Store Plaintext Passwords**: Send login credentials over HTTPS only.
2. **Token Storage**: Store Sanctum bearer tokens in `localStorage`, `sessionStorage`, or `HttpOnly` cookies.
3. **Role-Based UI Guards**: Use the `role` field from `GET /api/v1/auth/me` (`ADMIN`, `MANAGER`, `CASHIER`, `STAFF`) to hide/show admin navigation tabs.
4. **CORS Handling**: The API automatically responds with standard CORS headers (`Access-Control-Allow-Origin: *`).
