# 🛠️ Client SDK Generation Guide

Generate type-safe client SDKs in TypeScript, Python, and PHP directly from the live OpenAPI specification (`https://api.kesararamwithdigital.tech/api/v1/openapi.json`).

---

## 1. 🔷 TypeScript / Next.js SDK Generation

Install `openapi-typescript`:
```bash
npx openapi-typescript https://api.kesararamwithdigital.tech/api/v1/openapi.json -o src/types/api.ts
```

### Usage in React / Next.js:
```typescript
import type { paths } from '@/types/api';

type ProductsResponse = paths['/products']['get']['responses']['200']['content']['application/json'];
```

---

## 2. 🐍 Python SDK Generation

Using `openapi-python-client`:
```bash
pip install openapi-python-client
openapi-python-client generate --url https://api.kesararamwithdigital.tech/api/v1/openapi.json --output csms_client
```

### Usage in Python:
```python
from csms_client import Client
from csms_client.api.products import get_products

client = Client(base_url="https://api.kesararamwithdigital.tech/api/v1")
products = get_products.sync(client=client)
print(products)
```

---

## 3. 🐘 PHP Client SDK Generation

Using `jane-php/open-api`:
```bash
composer require jane-php/open-api-runtime
vendor/bin/jane-openapi generate --openapi-file=https://api.kesararamwithdigital.tech/api/v1/openapi.json
```
