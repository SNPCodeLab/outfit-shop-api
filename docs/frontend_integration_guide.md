# Full Frontend Integration & Setup Guide — SS-MIS Web API (v1)

This document provides a step-by-step setup guide and code tutorials for connecting any web frontend (**React**, **Next.js**, **Vite**, **Vue**, **Angular**, **HTML/JS**) or mobile app (**Flutter**, **React Native**) to the **Clothing Sales Management System (SS-MIS) API**.

---

## 🛠️ Step 1: Starting the Local Backend API Server

Run the local API server on port 8000:
```bash
cd /Users/Apple16/Desktop/SS_MIS
export PATH="/usr/local/bin:/opt/homebrew/bin:$PATH"
php artisan serve --host=127.0.0.1 --port=8000
```
- **Live Base URL**: `http://127.0.0.1:8000/api/v1`
- **Health Check**: `GET http://127.0.0.1:8000/api/v1/status`

---

## 🗄️ Step 2: Database Initialization & Test Accounts

To reset the database and seed initial accounts:
```bash
php artisan migrate:fresh --seed
```

### Access Tiers & Roles
- **ADMIN**: Full system control & employee account management.
- **MANAGER**: Product CRUD, purchase receiving, stock adjustments, voiding sales.
- **CASHIER**: POS Checkouts, receipt issuing, customer registration.
- **STAFF**: Catalog lookups & barcode scanning.

---

## 💻 Step 3: Frontend API Client Setup

### Install Axios (if using Node/React/Vue)
```bash
npm install axios
```

### Create API Client (`src/services/api.js`)
```javascript
import axios from 'axios';

// 1. Create Axios instance with Base URL
const api = axios.create({
  baseURL: process.env.NEXT_PUBLIC_API_URL || 'http://127.0.0.1:8000/api/v1',
  headers: {
    'Accept': 'application/json',
    'Content-Type': 'application/json',
  },
});

// 2. Request Interceptor: Attach Sanctum Bearer Token
api.interceptors.request.use((config) => {
  const token = localStorage.getItem('token');
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

// 3. Response Interceptor: Handle 401 Unauthorized Automatically
api.interceptors.response.use(
  (response) => response.data,
  (error) => {
    if (error.response && error.response.status === 401) {
      localStorage.removeItem('token');
      localStorage.removeItem('user');
      window.location.href = '/login';
    }
    return Promise.reject(error.response?.data || error.message);
  }
);

export default api;
```

---

## 🎨 Step 4: Component Code Examples

### A. Login Component (`Login.jsx`)
```jsx
import React, { useState } from 'react';
import api from '../services/api';

export default function Login({ onLoginSuccess }) {
  const [username, setUsername] = useState('cashier');
  const [password, setPassword] = useState('Cashier@123456');
  const [error, setError] = useState('');

  const handleSubmit = async (e) => {
    e.preventDefault();
    try {
      // POST /api/v1/auth/login
      const res = await api.post('/auth/login', { username, password });
      
      // Save Sanctum access token & user details
      localStorage.setItem('token', res.data.access_token);
      localStorage.setItem('user', JSON.stringify(res.data.employee));
      
      onLoginSuccess(res.data.employee);
    } catch (err) {
      setError(err.message || 'Login failed');
    }
  };

  return (
    <form onSubmit={handleSubmit}>
      <h2>Employee Login</h2>
      {error && <div style={{ color: 'red' }}>{error}</div>}
      <input type="text" value={username} onChange={e => setUsername(e.target.value)} placeholder="Username" />
      <input type="password" value={password} onChange={e => setPassword(e.target.value)} placeholder="Password" />
      <button type="submit">Sign In</button>
    </form>
  );
}
```

---

### B. POS Point-of-Sale Receipt Checkout Component (`POSCheckout.jsx`)

```jsx
import React, { useState } from 'react';
import api from '../services/api';

export default function POSCheckout() {
  const [barcodeInput, setBarcodeInput] = useState('');
  const [cart, setCart] = useState([]);
  const [paymentMethod, setPaymentMethod] = useState('CASH');
  const [receipt, setReceipt] = useState(null);

  // 1. Scan Barcode / SKU
  const handleBarcodeScan = async (e) => {
    e.preventDefault();
    try {
      // GET /api/v1/variants/barcode/:code
      const res = await api.get(`/variants/barcode/${barcodeInput}`);
      const variant = res.data;
      
      setCart(prevCart => {
        const existing = prevCart.find(i => i.variant_id === variant.variant_id);
        if (existing) {
          return prevCart.map(i => i.variant_id === variant.variant_id ? { ...i, quantity: i.quantity + 1 } : i);
        }
        return [...prevCart, { ...variant, quantity: 1, discount: 0 }];
      });
      setBarcodeInput('');
    } catch (err) {
      alert('Product not found: ' + barcodeInput);
    }
  };

  // 2. Execute Transactional POS Checkout
  const handleCheckout = async () => {
    try {
      const payload = {
        customer_id: 1,
        items: cart.map(item => ({
          variant_id: item.variant_id,
          quantity: item.quantity,
          discount: item.discount,
        })),
        payment_method: paymentMethod,
        payment_amount: cart.reduce((sum, item) => sum + (item.sale_price * item.quantity), 0),
      };

      // POST /api/v1/sales/checkout
      const res = await api.post('/sales/checkout', payload);
      setReceipt(res.data); // Receipt details
      setCart([]);
      alert('POS Sale Completed Successfully!');
    } catch (err) {
      alert('Checkout Failed: ' + err.message);
    }
  };

  return (
    <div>
      <h2>POS Cashier Checkout</h2>
      <form onSubmit={handleBarcodeScan}>
        <input 
          type="text" 
          value={barcodeInput} 
          onChange={e => setBarcodeInput(e.target.value)} 
          placeholder="Scan Barcode (e.g. 8850001001001)" 
        />
        <button type="submit">Add Item</button>
      </form>

      <h3>Cart Items ({cart.length})</h3>
      <ul>
        {cart.map(item => (
          <li key={item.variant_id}>
            {item.product?.product_name} ({item.sku}) - Qty: {item.quantity} x ${item.sale_price}
          </li>
        ))}
      </ul>

      <label>Payment Method:</label>
      <select value={paymentMethod} onChange={e => setPaymentMethod(e.target.value)}>
        <option value="CASH">Cash</option>
        <option value="CARD">Credit/Debit Card</option>
        <option value="QR">QR Code</option>
        <option value="ABA">ABA Pay</option>
      </select>

      <button onClick={handleCheckout} disabled={cart.length === 0}>
        Complete Checkout
      </button>

      {receipt && (
        <div>
          <h4>Receipt #{receipt.sale_id} Issued!</h4>
          <p>Grand Total: ${receipt.grand_total}</p>
        </div>
      )}
    </div>
  );
}
```

---

### C. Inventory Catalog & Low Stock Alert Dashboard (`Inventory.jsx`)

```jsx
import React, { useEffect, useState } from 'react';
import api from '../services/api';

export default function InventoryDashboard() {
  const [products, setProducts] = useState([]);
  const [lowStock, setLowStock] = useState([]);

  useEffect(() => {
    // GET /api/v1/products
    api.get('/products').then(res => setProducts(res.data));
    
    // GET /api/v1/variants/low-stock
    api.get('/variants/low-stock').then(res => setLowStock(res.data));
  }, []);

  return (
    <div>
      <h2>Inventory Catalog & Reorder Alerts</h2>
      
      {lowStock.length > 0 && (
        <div style={{ background: '#ffebee', padding: 10, marginBottom: 20 }}>
          ⚠️ <strong>Low Stock Warning ({lowStock.length} items need reorder):</strong>
          <ul>
            {lowStock.map(item => (
              <li key={item.variant_id}>
                {item.sku} - Current Qty: {item.quantity} (Reorder Level: {item.reorder_level})
              </li>
            ))}
          </ul>
        </div>
      )}

      <h3>All Products Catalog</h3>
      <table>
        <thead>
          <tr>
            <th>Product Name</th>
            <th>Brand</th>
            <th>Category</th>
            <th>Variants Count</th>
          </tr>
        </thead>
        <tbody>
          {products.map(prod => (
            <tr key={prod.product_id}>
              <td>{prod.product_name}</td>
              <td>{prod.brand}</td>
              <td>{prod.category?.category_name}</td>
              <td>{prod.variants?.length || 0}</td>
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  );
}
```

---

## 🔒 Step 5: Handling Permissions & RBAC in Frontend UI

Every employee profile returned from `GET /api/v1/auth/me` includes their assigned `role`:

```javascript
const user = JSON.parse(localStorage.getItem('user'));

if (user.role === 'ADMIN') {
  // Show Employee Account Management, Audit Logs, Catalog CRUD
} else if (user.role === 'MANAGER') {
  // Show Catalog CRUD, Purchases, Stock Adjustments, Void Sales
} else if (user.role === 'CASHIER') {
  // Show POS Checkout, Customer Registration
} else if (user.role === 'STAFF') {
  // Show Product Lookup & Inventory View Only
}
```
