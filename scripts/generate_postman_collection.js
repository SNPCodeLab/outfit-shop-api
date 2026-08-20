const fs = require('fs');
const path = require('path');

const PROD_URL = 'https://api.kesararamwithdigital.tech/api/v1';

const authHeader = (tokenVar = 'token') => [
  { key: 'Authorization', value: `Bearer {{${tokenVar}}}`, type: 'text' },
  { key: 'Accept', value: 'application/json', type: 'text' }
];

const jsonHeaders = (tokenVar = null) => {
  const headers = [
    { key: 'Content-Type', value: 'application/json', type: 'text' },
    { key: 'Accept', value: 'application/json', type: 'text' }
  ];
  if (tokenVar) {
    headers.unshift({ key: 'Authorization', value: `Bearer {{${tokenVar}}}`, type: 'text' });
  }
  return headers;
};

const makeUrl = (urlPath, queryParams = []) => {
  const pathParts = urlPath.replace(/^\//, '').split('/');
  return {
    raw: `{{base_url}}/${pathParts.join('/')}`,
    host: ['{{base_url}}'],
    path: pathParts
  };
};

const loginTestScript = [
  "if (pm.response.code === 200) {",
  "    var json = pm.response.json();",
  "    var token = json.token || (json.data && json.data.token);",
  "    if (token) {",
  "        pm.collectionVariables.set('token', token);",
  "    }",
  "}"
];

const collectionItems = [
  {
    name: '01. System & Authentication',
    item: [
      { name: 'Health Check', request: { method: 'GET', header: [{ key: 'Accept', value: 'application/json' }], url: makeUrl('health') } },
      { name: 'System Status', request: { method: 'GET', header: [{ key: 'Accept', value: 'application/json' }], url: makeUrl('status') } },
      {
        name: 'Login',
        event: [{ listen: 'test', script: { type: 'text/javascript', exec: loginTestScript } }],
        request: { method: 'POST', header: jsonHeaders(), body: { mode: 'raw', raw: JSON.stringify({ username: 'admin', password: 'password' }) }, url: makeUrl('auth/login') }
      }
    ]
  }
];

const masterCollection = {
  info: {
    name: "OutfitShop-Backend-API — Master Collection",
    schema: "https://schema.getpostman.com/json/collection/v2.1.0/collection.json"
  },
  variable: [
    { key: 'base_url', value: PROD_URL, type: 'string' },
    { key: 'token', value: '', type: 'string' }
  ],
  item: collectionItems
};

const postmanDir = path.join(__dirname, '..', 'postman');
if (!fs.existsSync(postmanDir)) fs.mkdirSync(postmanDir);

fs.writeFileSync(
  path.join(postmanDir, 'OutfitShop_Master_Collection.json'),
  JSON.stringify(masterCollection, null, 2)
);

console.log('✅ Generated Master Postman Collection: postman/OutfitShop_Master_Collection.json');
