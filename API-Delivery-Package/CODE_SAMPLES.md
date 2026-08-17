# 💻 Multi-Language Code Samples

Ready-to-use snippets for authenticating, fetching catalog matrices, and processing POS checkouts.

---

## 1. 🔷 TypeScript / Axios (React / Next.js)

```typescript
import axios from 'axios';

const API_BASE = 'https://api.kesararamwithdigital.tech/api/v1';

// POS Checkout
export async function executeCheckout(token: string, cartItems: any[]) {
  const response = await axios.post(`${API_BASE}/sales/checkout`, {
    items: cartItems.map(item => ({
      variant_id: item.variant_id,
      quantity: item.quantity,
      discount: item.discount || 0
    })),
    payment_method: 'CASH',
    payment_amount: 50.00,
    tax_rate: 10.00
  }, {
    headers: {
      'Authorization': `Bearer ${token}`,
      'X-Idempotency-Key': crypto.randomUUID()
    }
  });

  return response.data;
}
```

---

## 2. 🐍 Python / Requests

```python
import requests
import uuid

API_BASE = "https://api.kesararamwithdigital.tech/api/v1"

def authenticate(username, password):
    res = requests.post(f"{API_BASE}/auth/login", json={
        "username": username,
        "password": password,
        "device_name": "Python Inventory Sync Tool"
    })
    return res.json()["data"]["access_token"]

def get_product_matrix(token, product_id):
    headers = {"Authorization": f"Bearer {token}"}
    res = requests.get(f"{API_BASE}/products/{product_id}/matrix", headers=headers)
    return res.json()["data"]
```

---

## 3. 🐘 PHP / Guzzle

```php
use GuzzleHttp\Client;
use Ramsey\Uuid\Uuid;

$client = new Client(['base_uri' => 'https://api.kesararamwithdigital.tech/api/v1/']);

$response = $client->post('sales/checkout', [
    'headers' => [
        'Authorization'     => 'Bearer ' . $token,
        'X-Idempotency-Key' => Uuid::uuid4()->toString(),
        'Accept'            => 'application/json',
    ],
    'json' => [
        'items' => [
            ['variant_id' => 1, 'quantity' => 2, 'discount' => 0.00]
        ],
        'payment_method' => 'CASH',
        'payment_amount' => 60.00,
    ]
]);

$data = json_decode($response->getBody(), true);
```

---

## 4. 📟 cURL

```bash
curl -X POST "https://api.kesararamwithdigital.tech/api/v1/sales/checkout" \
  -H "Authorization: Bearer YOUR_SANCTUM_TOKEN" \
  -H "Content-Type: application/json" \
  -H "X-Idempotency-Key: 9b1deb4d-3b7d-4bad-9bdd-2b0d7b3dcb6d" \
  -d '{
    "items": [{"variant_id": 1, "quantity": 2, "discount": 0.00}],
    "payment_method": "CASH",
    "payment_amount": 60.00
  }'
```
