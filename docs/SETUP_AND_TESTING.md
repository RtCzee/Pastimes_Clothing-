# Pastimes Role-Based System — Setup & Testing Guide

## 📋 What's New (Latest Update)

This release introduces a complete **role-based user system** with distinct features for **sellers** and **buyers**:

- ✅ User registration with Seller/Buyer choice
- ✅ Role-based navbar navigation (sellers see "My Listings")
- ✅ Seller dashboard for managing listings (seller-only items)
- ✅ Buyer dashboard for browsing all items
- ✅ Admin dashboard with user management (approve/decline/delete)
- ✅ Prepared statement queries (security hardened)
- ✅ Seller items appear in Shop for all customers

---

## 🔧 Database Schema Changes

If you're **upgrading** from a previous version, run this SQL in phpMyAdmin:

```sql
-- Add role column to tblUser
ALTER TABLE tblUser ADD COLUMN role ENUM('buyer','seller') DEFAULT 'buyer';

-- Add sellerID to tblClothes
ALTER TABLE tblClothes ADD COLUMN sellerID INT;
ALTER TABLE tblClothes ADD FOREIGN KEY (sellerID) REFERENCES tblUser(userID);

-- Update existing admin user
UPDATE tblAdmin SET status='active', role='seller' WHERE username='admin';
```

**Or** simply re-seed from scratch (deletes all data):

1. Delete `data/createTable.php` and let it recreate the database with new schema
2. Or manually run `data/createTable.php` via browser:  
   `http://localhost/Pastimes/data/createTable.php`

---

## 📝 User Roles & Features

### **Buyer Role**
- Browse all items listed by sellers via **Shop** or **Buyer Dashboard** (`/buyerDashboard.php`)
- View seller names alongside items
- Add items to cart
- Standard navigation (no "My Listings")

### **Seller Role**
- Add/Edit/Delete their own items on **Seller Dashboard** (`/sellerDashboard.php`)
- Only see their own listed items (filtered by `sellerID`)
- "My Listings" link appears in navbar when logged in as seller
- Upload images, set prices, write descriptions
- Items automatically appear in Shop for all buyers

### **Admin Role**
- **Admin Dashboard** (`/adminDashboard.php`) - manage all users
- Approve pending registrations (set status → `active`)
- Decline registrations (set status → `declined`)
- Delete users (from any status)
- View user roles (Buyer/Seller)

---

## 🚀 Quick Start (Local Testing)

### Prerequisites
- WAMP64 running (Apache + MySQL)
- PHP 7.4+
- MySQL with database `pastimes` (or your configured name)

### Step 1: Verify Database Schema

1. Visit `http://localhost/Pastimes/data/createTable.php` in browser
2. You should see: **"All tables created and user data loaded successfully!"**
3. If you see errors about `role` or `sellerID`, your schema is missing the new columns (run ALTER statements above)

### Step 2: Register a New Account

1. Go to `http://localhost/Pastimes/register.php`
2. Fill in:
   - **Username**: `testseller`
   - **Full Name**: `Test Seller`
   - **Email**: `testseller@example.com`
   - **Password**: `Test123`
   - **Account Type**: Select **"Seller"** ← Important!
3. Click **Register**
4. You should see: **"Account created. Waiting for admin approval."**

### Step 3: Admin Approval

1. Go to `http://localhost/Pastimes/adminLogin.php`
2. Login with:
   - **Username**: `admin`
   - **Password**: `admin123`
3. On the Admin Dashboard, you should see your new **testseller** account in "Pending Users"
4. Click **"Approve"** → testseller moves to "Active Users"
5. Click **"Logout Admin"** to exit

### Step 4: Login as Seller

1. Go to `http://localhost/Pastimes/login.php`
2. Login with testseller credentials
3. You should land on a success page showing your details
4. **Notice the navbar**: "My Listings" link is now visible (only for sellers)
5. Click "My Listings" or go to `http://localhost/Pastimes/sellerDashboard.php`

### Step 5: Test Seller Dashboard

On the Seller Dashboard:

1. Click **"Add Item"** button
2. Fill in:
   - **Item Name**: `Vintage Denim Jacket`
   - **Price (R)**: `150.00`
   - **Item Photo**: Upload a JPG/PNG (optional)
   - **Description**: `Blue vintage jacket, excellent condition`
3. Click **"Add to Store"**
4. Item appears in your grid with **Edit** and **Remove** buttons
5. **Item now shows in Shop** for all visitors!

### Step 6: Register a Buyer

1. Go to `http://localhost/Pastimes/register.php` again
2. Register a new account:
   - **Username**: `testbuyer`
   - **Full Name**: `Test Buyer`
   - **Email**: `testbuyer@example.com`
   - **Password**: `Test123`
   - **Account Type**: Select **"Buyer"** ← Different!
3. Approve in Admin Dashboard (same as Step 3)

### Step 7: Login as Buyer & View Seller Items

1. Go to `http://localhost/Pastimes/login.php`
2. Login as testbuyer
3. **Notice the navbar**: "My Listings" link is **NOT** there (buyers don't see it)
4. Visit **Shop** or go to `http://localhost/Pastimes/buyerDashboard.php`
5. You see all items listed by all sellers (including testseller's "Vintage Denim Jacket")
6. Each item shows the **seller name**

---

## 🧪 Test Matrix

| Feature | Seller | Buyer | Admin |
|---------|--------|-------|-------|
| Register with role choice | ✅ | ✅ | N/A |
| See "My Listings" in navbar | ✅ | ❌ | ✅ (custom) |
| Add/edit/delete own items | ✅ | ❌ | N/A |
| Browse all items in Shop | ✅ | ✅ | ✅ (dashboard) |
| Seller items appear in Shop | ✅ | ✅ | ✅ |
| Approve/decline users | ❌ | ❌ | ✅ |
| Delete users | ❌ | ❌ | ✅ |
| Only see own listings | ✅ | N/A | ✅ (user list) |

---

## 🔐 Security Notes

✅ **Implemented:**
- Prepared statements for all database queries (prevents SQL injection)
- Prepared statements for insert/update/delete operations
- Role-based access control (seller dashboard checks `$_SESSION['role']`)
- Seller can only delete/edit their own items (checked via `sellerID`)
- Password hashing (bcrypt via `password_hash()`)
- Dual-mode password verification (bcrypt + MD5 legacy fallback)
- Seller items only shown if seller is `active` status

---

## 📁 Key Files

```
Pastimes/
├── register.php               ← Role selection form (Buyer/Seller)
├── login.php                  ← Sets $_SESSION['role']
├── sellerDashboard.php        ← Seller item management
├── buyerDashboard.php         ← Buyer browse view
├── adminDashboard.php         ← Admin user management
├── includes/bootstrap.php     ← Merges DB items into Shop
├── data/
│   ├── createTable.php        ← Schema with role & sellerID
│   └── DBConn.php             ← Database connection
├── assets/
│   ├── css/styles.css         ← All styling
│   └── images/clothes/        ← Seller uploaded items
└── docs/
    └── SETUP_AND_TESTING.md   ← This file
```

---

## 🐛 Troubleshooting

### **"User not found" on login after registration**
- Verify your username/email matches exactly
- Check that database schema includes `role` column (run ALTER or createTable)

### **Navbar doesn't show "My Listings"**
- Ensure `$_SESSION['role']` is set after login (check login.php sets it)
- Verify `register.php` inserts role correctly

### **Seller items not appearing in Shop**
- Check seller account has `status='active'` in admin dashboard
- Verify items have `sellerID` and valid image path
- Seller items only show if seller is approved

### **Admin can't approve users**
- Verify you're logged in as admin (check `$_SESSION['adminID']`)
- Confirm users are in "Pending" section (not already approved)
- Try delete and re-register

### **Image upload failing on seller dashboard**
- Ensure `assets/images/clothes/` directory exists (auto-created)
- Check file is JPG/PNG/WEBP and under 3MB
- Verify write permissions on `assets/images/clothes/`

---

## 📊 Sample Test Data

| Username | Role | Status | Password |
|----------|------|--------|----------|
| admin | Admin | active | admin123 |
| testseller | Seller | active | Test123 |
| testbuyer | Buyer | active | Test123 |

---

## ✨ Next Steps (Future Enhancements)

- [ ] Add order/purchase system
- [ ] Implement seller ratings/reviews
- [ ] Cart checkout functionality
- [ ] Wishlist feature
- [ ] Messaging between buyers/sellers
- [ ] Admin user edit (change email/name)
- [ ] Password reset flow
- [ ] Email verification

---

## 📞 Support

If issues arise, check:

1. **Database**: phpMyAdmin → check `tblUser` has `role` column and `tblClothes` has `sellerID`
2. **Session variables**: Add debugging at page top:  
   ```php
   echo '<pre>'; var_dump($_SESSION); echo '</pre>';
   ```
3. **File permissions**: Ensure `assets/images/clothes/` is writable
4. **PHP errors**: Enable in `php.ini` or add at page top:  
   ```php
   ini_set('display_errors', 1); error_reporting(E_ALL);
   ```

---

**Last Updated**: May 4, 2026  
**Version**: 2.0 (Role-Based System with Shop Integration)
