import { dataConnect, db } from './firebase.js';
import { createStore, listStores, createCategory, listCategories } from './dataconnect/esm/index.esm.js';
import { collection, getDocs } from 'firebase/firestore';

async function checkAndSeedData() {
  console.log('--- 🔍 CHECKING FIREBASE DATA STORES ---');

  // 1. Check & Seed Data Connect (PostgreSQL Database)
  try {
    console.log('\n[1] Checking Firebase SQL Connect (PostgreSQL)...');
    let storesRes = await listStores(dataConnect);
    
    if (storesRes.data.stores.length === 0) {
      console.log('   No stores found. Inserting sample store "Flagship Store Phnom Penh"...');
      await createStore(dataConnect, {
        storeName: "Flagship Store Phnom Penh",
        code: "PP-001",
        phone: "+855 23 123 456",
        email: "phnompenh@ssmis.com",
        address: "Monivong Blvd, Phnom Penh"
      });
      
      await createCategory(dataConnect, {
        categoryName: "Shirts & Tops",
        slug: "shirts-tops",
        description: "Men and Women Clothing Tops"
      });
      
      storesRes = await listStores(dataConnect);
    }
    
    console.log('✅ SQL Connect Stores Count:', storesRes.data.stores.length);
    console.log('   Stores Data:', JSON.stringify(storesRes.data.stores, null, 2));

    const catRes = await listCategories(dataConnect);
    console.log('✅ SQL Connect Categories Count:', catRes.data.categories.length);
    console.log('   Categories Data:', JSON.stringify(catRes.data.categories, null, 2));
  } catch (err) {
    console.error('❌ SQL Connect Error:', err.message);
  }

  // 2. Check Cloud Firestore Database
  try {
    console.log('\n[2] Checking Cloud Firestore Database...');
    const snapshot = await getDocs(collection(db, 'users'));
    console.log('✅ Firestore Users Count:', snapshot.size);
    snapshot.forEach(doc => console.log('   User Doc:', doc.id, doc.data()));
  } catch (err) {
    console.error('❌ Firestore Error:', err.message);
  }

  console.log('\n--- 🚀 DATA INSPECTION COMPLETE ---');
}

checkAndSeedData();
