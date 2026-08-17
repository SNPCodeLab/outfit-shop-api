# Example Requests & Code Snippets

Copyable cURL and TypeScript/Axios code snippets for primary frontend workflows.

---

## 1. Cashier Login & Token Storage

### cURL
```bash
curl -X POST "https://api.kesararamwithdigital.tech/api/v1/auth/login" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "username": "cashier1",
    "password": "Password123!"
  }'
```

### TypeScript / Axios
```typescript
import axios from 'axios';

const response = await axios.post('https://api.kesararamwithdigital.tech/api/v1/auth/login', {
  username: 'cashier1',
  password: 'Password123!',
});

const { token, employee } = response.data.data;
localStorage.setItem('auth_token', token);
```

---

## 2. Open Cash Register Shift (Morning Float)

### cURL
```bash
curl -X POST "https://api.kesararamwithdigital.tech/api/v1/shifts/open" \
  -H "Authorization: Bearer <TOKEN>" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "opening_float_usd": 100.00,
    "opening_float_khr": 410000.00,
    "notes": "Morning register opening"
  }'
```

---

## 3. Fetch 2D Size $\times$ Color Variant Matrix

### cURL
```bash
curl -X GET "https://api.kesararamwithdigital.tech/api/v1/products/1/matrix" \
  -H "Accept: application/json"
```

### TypeScript / Axios
```typescript
const { data } = await axios.get('https://api.kesararamwithdigital.tech/api/v1/products/1/matrix');
console.log(data.data.matrix); // { "Black": { "S": { ... }, "M": { ... } } }
```

---

## 4. Execute Transactional POS Checkout with Idempotency

### cURL
```bash
curl -X POST "https://api.kesararamwithdigital.tech/api/v1/sales/checkout" \
  -H "Authorization: Bearer <TOKEN>" \
  -H "Content-Type: application/json" \
  -H "X-Idempotency-Key: POS-TXN-20260817-0091" \
  -H "Accept: application/json" \
  -d '{
    "customer_id": 1,
    "items": [
      {
        "variant_id": 1,
        "quantity": 2,
        "discount": 0.00
      }
    ],
    "payment_method": "CASH",
    "payment_amount": 150.00,
    "tax_rate": 10.00
  }'
```

### TypeScript / Axios
```typescript
const salePayload = {
  customer_id: 1,
  items: [{ variant_id: 1, quantity: 2, discount: 0 }],
  payment_method: 'CASH',
  payment_amount: 150.00,
  tax_rate: 10.00,
};

const response = await axios.post(
  'https://api.kesararamwithdigital.tech/api/v1/sales/checkout',
  salePayload,
  {
    headers: {
      Authorization: `Bearer ${token}`,
      'X-Idempotency-Key': 'POS-' + crypto.randomUUID(),
    },
  }
);

console.log('Invoice Generated:', response.data.data.invoice_no);
console.log('Change Due:', response.data.data.payments[0].change_due);
```

---

## 5. Close Register Shift & Compile Z-Report

### cURL
```bash
curl -X POST "https://api.kesararamwithdigital.tech/api/v1/shifts/close" \
  -H "Authorization: Bearer <TOKEN>" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "closing_cash_usd": 221.00,
    "notes": "Evening shift closing"
  }'
```
