import fetch from 'node-fetch';

const BASE_URL = process.env.API_URL || 'http://127.0.0.1:8000/api';

async function testApiEndpoints() {
  console.log('================================================================');
  console.log('🧪 TESTING SS-MIS REST API ENDPOINTS');
  console.log(`🌐 Target Base URL: ${BASE_URL}`);
  console.log('================================================================\n');

  let authToken = '';

  // 1. Test Registration
  console.log('[1/5] Testing POST /api/register...');
  try {
    const testEmail = `admin_${Date.now()}@example.com`;
    const regRes = await fetch(`${BASE_URL}/register`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
      body: JSON.stringify({
        name: 'Test Admin',
        email: testEmail,
        password: 'Password123!',
        password_confirmation: 'Password123!',
        is_admin: 1
      })
    });
    const regData = await regRes.json();
    if (regRes.ok) {
      console.log('   ✅ REGISTER SUCCESS:', regData.message);
      authToken = regData.access_token;
    } else {
      console.log('   ℹ️ REGISTER RESPONSE:', regData.message || JSON.stringify(regData));
    }
  } catch (err) {
    console.log('   ⚠️ Register test error:', err.message);
  }

  // 2. Test Login
  if (!authToken) {
    console.log('\n[2/5] Testing POST /api/login...');
    try {
      const loginRes = await fetch(`${BASE_URL}/login`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
        body: JSON.stringify({
          email: 'admin@example.com',
          password: 'Password123!'
        })
      });
      const loginData = await loginRes.json();
      if (loginRes.ok) {
        console.log('   ✅ LOGIN SUCCESS:', loginData.message);
        authToken = loginData.access_token;
      } else {
        console.log('   ⚠️ LOGIN FAILED:', loginData.message);
      }
    } catch (err) {
      console.log('   ⚠️ Login test error:', err.message);
    }
  }

  // 3. Test Protected GET /api/user
  console.log('\n[3/5] Testing GET /api/user (Protected)...');
  try {
    const userRes = await fetch(`${BASE_URL}/user`, {
      headers: {
        'Accept': 'application/json',
        'Authorization': `Bearer ${authToken}`
      }
    });
    const userData = await userRes.json();
    if (userRes.ok) {
      console.log('   ✅ GET USER SUCCESS:', userData.user.name, `(${userData.user.email})`);
    } else {
      console.log('   ❌ GET USER FAILED:', userData.message);
    }
  } catch (err) {
    console.log('   ⚠️ User test error:', err.message);
  }

  // 4. Test GET /api/dashboard/stats (Admin Only)
  console.log('\n[4/5] Testing GET /api/dashboard/stats (Admin Traffic Stats)...');
  try {
    const statsRes = await fetch(`${BASE_URL}/dashboard/stats`, {
      headers: {
        'Accept': 'application/json',
        'Authorization': `Bearer ${authToken}`
      }
    });
    const statsData = await statsRes.json();
    if (statsRes.ok) {
      console.log('   ✅ DASHBOARD STATS SUCCESS:');
      console.log('      Requests Today:', statsData.data.requests_today);
      console.log('      Error Count:', statsData.data.error_count);
      console.log('      Top Endpoints:', statsData.data.top_endpoints.length);
    } else {
      console.log('   ❌ DASHBOARD STATS FAILED:', statsData.message);
    }
  } catch (err) {
    console.log('   ⚠️ Stats test error:', err.message);
  }

  // 5. Test GET /api/v1/status
  console.log('\n[5/5] Testing GET /api/v1/status...');
  try {
    const statusRes = await fetch(`${BASE_URL}/v1/status`, {
      headers: { 'Accept': 'application/json', 'Authorization': `Bearer ${authToken}` }
    });
    const statusData = await statusRes.json();
    if (statusRes.ok) {
      console.log('   ✅ GET STATUS SUCCESS:', statusData.message || JSON.stringify(statusData));
    } else {
      console.log('   ℹ️ STATUS RESPONSE:', statusData.message || JSON.stringify(statusData));
    }
  } catch (err) {
    console.log('   ⚠️ Status test error:', err.message);
  }

  console.log('\n================================================================');
  console.log('🎉 ALL API ENDPOINTS TESTED!');
  console.log('================================================================');
}

testApiEndpoints();
