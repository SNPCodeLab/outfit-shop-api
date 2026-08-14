# SS-MIS RESTful API Documentation & Integration Guide

Welcome to the **SS-MIS (Store Stock & Point-of-Sale Information System)** API Documentation.

---

## 🔐 Base URLs & Authentication

- **Development Base URL**: `http://localhost:8000/api`
- **Production Base URL**: `https://api.kesararamwithdigital.tech/api` (or `https://ss-mis.vercel.app/api`)
- **Authentication Strategy**: **Laravel Sanctum Bearer Tokens**
- **Rate Limiting**: `60 requests / minute` per user/IP.

### Request Headers
```http
Authorization: Bearer <your_access_token>
Content-Type: application/json
Accept: application/json
```

---

## 📑 API Endpoints Summary

### 1. Authentication Endpoints

#### `POST /api/register`
- **Auth**: Public (Unprotected)
- **Description**: Register a new user account.
- **Request Body**:
```json
{
  "name": "Admin User",
  "email": "admin@example.com",
  "password": "Password123!",
  "password_confirmation": "Password123!",
  "is_admin": true
}
```
- **Response `201 Created`**:
```json
{
  "success": true,
  "message": "User registered successfully.",
  "access_token": "1|sanctum_token_string...",
  "token_type": "Bearer",
  "user": {
    "id": 1,
    "name": "Admin User",
    "email": "admin@example.com",
    "is_admin": true
  }
}
```

---

#### `POST /api/login`
- **Auth**: Public (Unprotected)
- **Description**: Authenticate user or employee credentials and issue a Bearer token.
- **Request Body**:
```json
{
  "email": "admin@example.com",
  "password": "Password123!"
}
```
- **Response `200 OK`**:
```json
{
  "success": true,
  "message": "Login successful.",
  "access_token": "2|sanctum_token_string...",
  "token_type": "Bearer",
  "user": {
    "id": 1,
    "name": "Admin User",
    "email": "admin@example.com",
    "is_admin": true
  }
}
```

---

#### `GET /api/user`
- **Auth**: `auth:sanctum`
- **Description**: Retrieve current authenticated user profile.
- **Response `200 OK`**:
```json
{
  "success": true,
  "user": {
    "id": 1,
    "name": "Admin User",
    "email": "admin@example.com",
    "is_admin": true
  }
}
```

---

#### `POST /api/logout`
- **Auth**: `auth:sanctum`
- **Description**: Revoke the current access token.
- **Response `200 OK`**:
```json
{
  "success": true,
  "message": "Logged out successfully."
}
```

---

### 2. Admin Dashboard & Traffic Analytics

#### `GET /api/dashboard/stats`
- **Auth**: `auth:sanctum` + `admin`
- **Description**: Fetch API request counts, top endpoints, user traffic, and recent logs.
- **Response `200 OK`**:
```json
{
  "success": true,
  "data": {
    "requests_today": 142,
    "requests_last_7_days": [
      { "date": "2026-08-14", "count": 142 }
    ],
    "top_endpoints": [
      { "path": "/api/v1/products", "method": "GET", "count": 45 },
      { "path": "/api/v1/sales/checkout", "method": "POST", "count": 32 }
    ],
    "requests_by_user": [
      { "user_id": "1", "count": 89 }
    ],
    "error_count": 2,
    "recent_requests": [
      {
        "id": 1,
        "user_id": "1",
        "method": "POST",
        "path": "/api/v1/sales/checkout",
        "ip": "127.0.0.1",
        "status": 200,
        "duration_ms": 14.5,
        "response_size": 420,
        "created_at": "2026-08-14T17:21:40.000000Z"
      }
    ]
  }
}
```

---

### 3. Core POS & Inventory Resource Routes (`/api/v1/*`)

| Endpoint | Method | Auth Level | Description |
| :--- | :--- | :--- | :--- |
| `/api/v1/categories` | `GET` | Sanctum | List all product categories |
| `/api/v1/categories` | `POST` | Sanctum (Admin/Manager) | Create category |
| `/api/v1/products` | `GET` | Sanctum | List products & stock |
| `/api/v1/products` | `POST` | Sanctum (Admin/Manager) | Create product |
| `/api/v1/variants` | `GET` | Sanctum | List product variants & SKUs |
| `/api/v1/variants/low-stock` | `GET` | Sanctum | List low stock variants |
| `/api/v1/variants/barcode/{barcode}` | `GET` | Sanctum | Lookup variant by barcode |
| `/api/v1/customers` | `GET` | Sanctum | List customers & loyalty points |
| `/api/v1/customers` | `POST` | Sanctum | Create new customer |
| `/api/v1/sales/checkout` | `POST` | Sanctum | Process POS checkout transaction |
| `/api/v1/sales` | `GET` | Sanctum | List sales history |
| `/api/v1/suppliers` | `GET` | Sanctum (Admin/Manager) | List suppliers |
| `/api/v1/purchases` | `GET` | Sanctum (Admin/Manager) | List purchase orders |
| `/api/v1/stock-movements/adjust` | `POST` | Sanctum (Admin/Manager) | Adjust inventory stock |
| `/api/v1/audit-logs` | `GET` | Sanctum (Admin/Manager) | View audit logs |
| `/api/v1/employees` | `GET` | Sanctum (Admin Only) | List employees |

---

## 💻 Reusable JavaScript Client Code (`apiRequest` Helper)

Copy and paste this production-ready JavaScript helper into your frontend application:

```javascript
// Configuration
const API_BASE_URL = 'https://api.kesararamwithdigital.tech/api';

/**
 * Perform login and store Sanctum Bearer Token in localStorage
 */
async function login(email, password) {
  const response = await fetch(`${API_BASE_URL}/login`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
    },
    body: JSON.stringify({ email, password }),
  });

  const data = await response.json();

  if (response.ok && data.access_token) {
    localStorage.setItem('auth_token', data.access_token);
    console.log('✅ Login Successful! Token stored:', data.access_token);
    return data;
  } else {
    console.error('❌ Login Failed:', data.message);
    throw new Error(data.message || 'Login failed');
  }
}

/**
 * Reusable API request helper that automatically attaches Bearer Token
 */
async function apiRequest(endpoint, options = {}) {
  const token = localStorage.getItem('auth_token');

  const defaultHeaders = {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  };

  if (token) {
    defaultHeaders['Authorization'] = `Bearer ${token}`;
  }

  const config = {
    ...options,
    headers: {
      ...defaultHeaders,
      ...options.headers,
    },
  };

  const response = await fetch(`${API_BASE_URL}${endpoint}`, config);
  const data = await response.json();

  if (response.status === 401) {
    console.warn('⚠️ Token expired or invalid. Redirecting to login...');
    localStorage.removeItem('auth_token');
  }

  if (!response.ok) {
    throw new Error(data.message || `API Error: ${response.status}`);
  }

  return data;
}

/**
 * Example Usage Scenarios
 */
async function demoUsage() {
  try {
    // 1. Login
    await login('admin@example.com', 'Password123!');

    // 2. Fetch User Profile
    const profile = await apiRequest('/user');
    console.log('User Profile:', profile.user);

    // 3. Fetch Admin Traffic Stats (Admin only)
    const stats = await apiRequest('/dashboard/stats');
    console.log('Traffic Stats:', stats.data);

    // 4. Fetch Products Catalog
    const products = await apiRequest('/v1/products');
    console.log('Products:', products.data);

  } catch (error) {
    console.error('Execution Error:', error.message);
  }
}
```
