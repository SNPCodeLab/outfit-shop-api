# Rule: Postman Collection Management

## Single File, One Source

The ONLY collection file is:

```
postman/OutfitShop_Master_Collection.json
```

Never create, edit, or maintain any other collection JSON.
Every rebuild syncs it to these four copy targets:
- `API-Delivery-Package/postman_collection.json`
- `docs/postman_collection.json`
- `postman_collection.json`
- `public/SS_MIS.postman_collection.json`

Delete any stale collection or environment files found in `postman/` or `docs/`.

## Real IDs Always

Never use placeholder IDs like `1` for products or variants.
Always query the live production API first to get real IDs.

Current real IDs:
- product_id  = 182  (Gucci Oxford Shirt)
- variant_id  = 174  (SKU-GUC-0182)
- barcode     = SKU-GUC-0182
- brand_id    = 3    (Gucci)
- category_id = 14   (Men's Ready-to-Wear)
- employee_id = 1    (admin)
- order_id    = 1

## Every Endpoint

No endpoint from routes/api.php may be skipped or omitted.
When routes are added or changed, update the collection immediately.

## Naming

Folder: `{nn} - {Section Name} [{TIER if applicable}]`
Request: `{METHOD} {Description} ({ID — name if notable})`

## Merge Rule

Never merge or push the collection automatically.
Wait for the user's `pm` or `mp` command before pushing.
