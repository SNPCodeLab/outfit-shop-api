# Example Requests & Code Snippets

Copyable cURL and TypeScript/Axios code snippets for primary frontend workflows.

---

## 1. Fetch 2D Size $\times$ Color Variant Matrix

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

## 2. Public Storefront Search

### cURL
```bash
curl -X GET "https://api.kesararamwithdigital.tech/api/v1/products?search=Oxford" \
  -H "Accept: application/json"
```
