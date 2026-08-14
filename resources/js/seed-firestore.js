import { db } from './firebase.js';
import { collection, doc, setDoc, serverTimestamp } from 'firebase/firestore';

async function seedFirestoreData() {
  console.log('--- 🚀 SEEDING SAMPLE DATA INTO CLOUD FIRESTORE ---');

  try {
    // 1. Create a sample User profile document
    console.log('Creating sample user profile in "users" collection...');
    await setDoc(doc(db, 'users', 'sample_user_001'), {
      uid: 'sample_user_001',
      displayName: 'Kesararam Digital',
      email: 'kesararamwithdigital@gmail.com',
      role: 'Admin',
      createdAt: new Date().toISOString()
    });
    console.log('  ✅ Created document: /users/sample_user_001');

    // 2. Create sample Store location document
    console.log('Creating sample store in "stores" collection...');
    await setDoc(doc(db, 'stores', 'store_pp_01'), {
      storeId: 'store_pp_01',
      storeName: 'Phnom Penh Main Branch',
      city: 'Phnom Penh',
      phone: '+855 23 123 456',
      isActive: true,
      createdAt: new Date().toISOString()
    });
    console.log('  ✅ Created document: /stores/store_pp_01');

    // 3. Create sample Category document
    console.log('Creating sample category in "categories" collection...');
    await setDoc(doc(db, 'categories', 'cat_apparel_01'), {
      categoryId: 'cat_apparel_01',
      categoryName: 'Apparel & Clothing',
      description: 'Men and Women Store Stock',
      createdAt: new Date().toISOString()
    });
    console.log('  ✅ Created document: /categories/cat_apparel_01');

    console.log('\n🎉 ALL SAMPLE DATA SEEDED SUCCESSFULLY!');
    console.log('👉 Refresh your Cloud Firestore browser page to see the collections!');
  } catch (error) {
    console.error('❌ Error seeding Firestore:', error.message);
  }
}

seedFirestoreData();
