<div align="center">

#  Pastimes

### Sustainable Thrift Fashion — Pre-Loved Style, Re-Loved Pieces

![Demo](assets/Pastimes_Page.gif)

</div>

---

## About

Pastimes is a full-stack e-commerce platform for curated secondhand fashion. Shop sustainable, pre-loved clothing pieces with a focus on quality, style, and circular economy principles. Every item tells a story worth continuing.

## Features

✨ **Smooth Animations**
- Elegant page entrance animations with parallax hero backgrounds
- Scroll-reveal effects for sections, product cards, and content
- 1300ms hero zoom-in, 1100ms section reveals for polished feel
- Respects `prefers-reduced-motion` for accessibility

🛍️ **Product Browsing**
- Browse featured pieces and new arrivals
- Filter by category, size, and condition
- Sort by price, newest, or featured
- Responsive grid layout (2–4 columns)

🔐 **Authentication System**
- User registration with password hashing (bcrypt)
- Dual login (username OR email)
- Account approval workflow (admin-managed)
- Session-based authentication
- Dual password support (bcrypt + legacy MD5 for seed data)

👨‍💼 **Admin Dashboard**
- User management (approve/decline registrations)
- Summary stats for pending and active users
- Clean, spaced card-based layout
- Protected routes with session checks

🛒 **Shopping Cart**
- Add/remove products with quantity control
- LocalStorage persistence
- Real-time cart count badge
- Subtotal, shipping, and total calculations
- Free shipping over R1500

📱 **Responsive Design**
- Mobile-first CSS Grid approach
- Adaptive navigation (hamburger menu on mobile)
- Touch-friendly controls
- Works across all modern browsers

---

## Tech Stack

- **Backend**: PHP 7.4+ with MySQLi (prepared statements)
- **Frontend**: Vanilla JavaScript (ES6), HTML5, CSS3
- **Database**: MySQL
- **Server**: WAMP64 (Apache + PHP)
- **Version Control**: Git + GitHub

---

## Project Structure

```
Pastimes/
├── About.php              # About page
├── Cart.php               # Shopping cart
├── Contact.php            # Contact form
├── Home.php               # Homepage with animations
├── index.php              # Entry point
├── Product.php            # Product detail page
├── Shop.php               # Product listing & filters
├── login.php              # User login (username/email)
├── register.php           # User registration
├── logout.php             # Session logout
├── adminLogin.php         # Admin authentication
├── adminDashboard.php     # Admin user management
├── includes/
│   └── bootstrap.php      # Shared utilities, page renderer, product data
├── data/
│   ├── DBConn.php         # Database connection
│   ├── createTable.php    # Schema creation & seeding
│   └── products.php       # Product data
├── assets/
│   ├── css/
│   │   └── styles.css     # All styling (auth, animations, admin)
│   ├── js/
│   │   └── app.js         # Cart, filters, animations, menu
│   ├── images/            # Product & hero images
│   ├── icons/             # SVG icons
│   └── demo/
│       └── pastimes-demo.gif  # Demo GIF (add here)
└── README.md              # This file
```

---

## Setup & Installation

### Prerequisites
- WAMP64 (or LAMP/LEMP)
- PHP 7.4+
- MySQL 5.7+
- Git

### Steps

1. **Clone the repository**
   ```bash
   cd C:\wamp64\www
   git clone https://github.com/RtCzee/Pastimes_Clothing-.git Pastimes
   cd Pastimes
   ```

2. **Set up the database**
   - Open phpMyAdmin (`http://localhost/phpmyadmin`)
   - Import or run `data/createTable.php` via browser to create tables and seed data
   - Database: `ClothingStore`
   - Connection: `localhost`, root user, no password

3. **Start WAMP**
   - Launch WAMP64 (Apache + MySQL should be green)

4. **Access the site**
   - Open `http://localhost/Pastimes`

---

## Authentication

### User Registration
- Create account with username, full name, email, and password
- Password hashed with bcrypt (`PASSWORD_DEFAULT`)
- Account starts as **pending** — requires admin approval

### User Login
- Enter username **OR** email (either one works)
- Password verified against bcrypt or MD5 (legacy seed data)
- Only **active** accounts can log in
- Session created on success

### Admin Login
- Username: `admin`
- Password: `admin123`
- Access admin dashboard to approve/decline registrations

### Test Users (seed data with MD5 passwords)
All seed passwords are MD5 hashes for testing:
- Username: `johnnD`, Password: from userData.txt
- Username: `sarahM`, Password: from userData.txt
- etc.

---

## Key Features in Detail

### Animations & Performance
- **Hero Entrance**: 1300ms fade + scale from 0.998
- **Scroll Reveals**: 1100ms staggered entrance for sections
- **Parallax Backgrounds**: Dynamic zoom and translate on scroll
- **CSS Transitions**: cubic-bezier easing for smooth feel
- All use `will-change` and `requestAnimationFrame` for performance

### Login & Forms
- Sticky form (values preserved on error)
- Specific error messages:
  - "Please enter a username or email address."
  - "User not found. Please check your username or email."
  - "Incorrect password. Please try again."
  - "Your account is not yet approved by an administrator."
- HTML5 validation (minlength, type checks)
- User data table displayed after successful login

### Admin Dashboard
- Summary stat cards (pending count, active count)
- Sections for pending and active users
- User cards with username, email, and action buttons
- Approve/decline links with color-coded UI
- Responsive on mobile (cards stack, actions wrap)

### Shopping Cart
- `localStorage` persistence
- Add/remove items
- Quantity controls (+/-)
- Price calculations with free shipping threshold
- Real-time cart count badge in header

### Product Filters
- Category, Size, Condition dropdowns
- Real-time filtering and sorting
- Active filter badge in mobile header
- URL sync (bookmarkable filtered views)
- Mobile filter panel toggle

---

## Password Security

### Hashing Strategy
- **New users**: passwords hashed with bcrypt (PASSWORD_DEFAULT)
- **Legacy seed data**: MD5 hashes (for demo compatibility)
- Login checks hash type and verifies accordingly:
  ```php
  if (strpos($user['password'], '$2y$') === 0) {
    password_verify($password, $user['password']); // bcrypt
  } else {
    md5($password) === $user['password'];  // legacy
  }
  ```

### Best Practices
- Prepared statements for all queries (SQL injection prevention)
- Password never logged or echoed
- Session timeout on logout
- CSRF tokens on forms (optional future enhancement)

---

## Database Schema

### tblUser
| Column | Type | Notes |
|--------|------|-------|
| userID | INT (PK) | Auto-increment |
| username | VARCHAR (UNIQUE) | Min 3 chars |
| fullName | VARCHAR | |
| email | VARCHAR (UNIQUE) | |
| password | VARCHAR | bcrypt or MD5 |
| status | ENUM | pending/active/declined |

### tblAdmin
| Column | Type | Notes |
|--------|------|-------|
| adminID | INT (PK) | Auto-increment |
| username | VARCHAR | |
| email | VARCHAR | |
| password | VARCHAR | bcrypt |

### tblClothes
| Column | Type | Notes |
|--------|------|-------|
| itemID | INT (PK) | Auto-increment |
| itemName | VARCHAR | Product name |
| description | TEXT | |
| price | INT | In cents (R) |
| image | VARCHAR | Image path |
| category | VARCHAR | Tops/Bottoms/etc. |
| size | VARCHAR | XS/S/M/L/XL |
| condition | VARCHAR | New/Like New/Good |
| featured | TINYINT | Boolean |
| new | TINYINT | Boolean |

### tblAorder
| Column | Type | Notes |
|--------|------|-------|
| orderID | INT (PK) | Auto-increment |
| userID | INT (FK) | Reference to tblUser |
| itemID | INT (FK) | Reference to tblClothes |
| quantity | INT | |
| orderDate | TIMESTAMP | |

---

## Browser Support

- ✅ Chrome 90+
- ✅ Firefox 88+
- ✅ Safari 14+
- ✅ Edge 90+
- ⚠️ IE 11 (basic functionality only)

---

## Collaborators

- **RtCzee** - Didintle Kutlwano Mokgoro, ST10441052
- **lexi0222** - Alexis Maphosa, ST10440449

---

## License

This project is licensed under the MIT License. See the [LICENSE](LICENSE) file for details.

---

## Author

**RtCzee** — Web Developer  
GitHub: [@RtCzee](https://github.com/RtCzee)

---

## Questions?

See something broken or have an idea? Feel free to open an issue or reach out!

---

**Last Updated**: May 2026






                                         _Didintle Kutlwano Mokgoro
