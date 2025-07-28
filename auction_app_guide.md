# 🛒 Auction App Development Guide

## 📱 Flutter App (User Side)

### Features Overview

| Feature             | Description                                                  |
| ------------------- | ------------------------------------------------------------ |
| Authentication      | Register/Login using phone + PIN                             |
| Auctions            | View upcoming, live, and completed auctions                  |
| Bidding             | Place bids (each bid deducts 1 token, can't outbid yourself) |
| Wallet              | Top-up system: 1 token = 100 UGX                             |
| Profile             | View and update display name only                            |
| Rules               | View admin-defined auction rules                             |
| Bid History         | View personal bid records per auction                        |
| Winner Notification | View winners of past auctions                                |

### User Flow

1. **Register/Login** → OTP-less, PIN-secured auth
2. **Home Screen**:
   - Tabs for:
     - Upcoming Auctions
     - Live Auctions
     - Completed Auctions
3. **Auction Detail Page**:
   - Item details, live bids, place a bid (if live)
4. **Wallet Page**:
   - Show token balance
   - Top up wallet (manual or integrate MoMo later)
5. **Profile Page**:
   - View/edit name
6. **Auction Rules Page**:
   - Pull from API
7. **Bid History Page**:
   - Past bidding data

---

## 🛠️ CodeIgniter 4 Backend (Admin + API)

### Tech Stack

- **CI4** (raw PHP controller logic, no JWT)
- **MySQL**
- **Simple PIN login**
- **No frontend, only API + admin backend**

### Admin Features

| Feature         | Description                                |
| --------------- | ------------------------------------------ |
| PIN login       | Secure admin area                          |
| Item Management | Add/edit items with image upload           |
| Auctions        | Set start/end time, price, mark completed  |
| Bidders         | View users & their bids                    |
| Transactions    | Manual marking (pending, complete)         |
| Set Rules       | Show rules in Flutter app                  |
| Admin Notes     | Add notes to user profiles or transactions |
| Dashboard       | Statistics (optional)                      |

---

## 🧩 Database Schema

### `users`

```sql
id INT AUTO_INCREMENT PRIMARY KEY
phone VARCHAR(20) UNIQUE
pin VARCHAR(255) -- Hashed PIN
name VARCHAR(100)
tokens INT DEFAULT 0
created_at DATETIME
```

### `auctions`

```sql
id INT AUTO_INCREMENT PRIMARY KEY
item_name VARCHAR(255)
description TEXT
image VARCHAR(255)
start_time DATETIME
end_time DATETIME
starting_price DECIMAL(10,2)
is_completed BOOLEAN DEFAULT 0
winner_id INT NULL -- FK to users.id
created_at DATETIME
```

### `bids`

```sql
id INT AUTO_INCREMENT PRIMARY KEY
auction_id INT
user_id INT
amount DECIMAL(10,2)
tokens_used INT
created_at DATETIME
```

### `transactions`

```sql
id INT AUTO_INCREMENT PRIMARY KEY
user_id INT
amount DECIMAL(10,2)
tokens INT
status ENUM('pending','approved','rejected')
created_at DATETIME
note TEXT
```

### `rules`

```sql
id INT AUTO_INCREMENT PRIMARY KEY
content TEXT
last_updated DATETIME
```

### `admin`

```sql
id INT AUTO_INCREMENT PRIMARY KEY
pin VARCHAR(255) -- hashed
```

---

## 🧪 API Endpoints (Flutter ↔ CI4)

### Auth

| Method | Endpoint        | Description            |
| ------ | --------------- | ---------------------- |
| POST   | `/api/register` | Register new user      |
| POST   | `/api/login`    | Login with phone + PIN |

### Auctions

| Method | Endpoint                  | Description                               |
| ------ | ------------------------- | ----------------------------------------- |
| GET    | `/api/auctions/upcoming`  | List of future auctions                   |
| GET    | `/api/auctions/live`      | Auctions currently open                   |
| GET    | `/api/auctions/completed` | Auctions that ended                       |
| GET    | `/api/auctions/{id}`      | Auction detail                            |
| POST   | `/api/auctions/{id}/bid`  | Place bid (check if highest bidder first) |

### Wallet

| Method | Endpoint            | Description   |
| ------ | ------------------- | ------------- |
| GET    | `/api/wallet`       | Token balance |
| POST   | `/api/wallet/topup` | Manual top-up |

### Profile

| Method | Endpoint           | Description      |
| ------ | ------------------ | ---------------- |
| GET    | `/api/user`        | Get profile      |
| POST   | `/api/user/update` | Update name only |

### Rules

| Method | Endpoint     | Description              |
| ------ | ------------ | ------------------------ |
| GET    | `/api/rules` | Fetch auction rules text |

### Bid History

| Method | Endpoint         | Description                |
| ------ | ---------------- | -------------------------- |
| GET    | `/api/user/bids` | Get user's bidding history |

---

## 🔐 Admin Backend (CI4)

- `/admin/login` → PIN-based form login
- `/admin/logout`
- `/admin/dashboard` → Basic stats
- `/admin/auctions` → List, Add, Edit, Complete auctions
- `/admin/users` → View users
- `/admin/transactions` → View + manually mark as approved
- `/admin/rules` → Edit rules
- `/admin/notes` → Attach notes to users/transactions

---

## 📸 File Upload (Images)

- Use multipart form data
- Store in `/writable/uploads/` or `public/uploads/`
- Save file name in DB

---

## 🔒 Security Considerations

- Use `password_hash()` for PINs
- Simple session-based admin auth
- Input validation and sanitization on all APIs
- Protect `/admin` with middleware

---

## 📌 Token Logic

- 1 token = 100 UGX
- Top-up 1,000 UGX → 10 tokens
- Each bid deducts 1 token
- No refund for lost bids
- Prevent self-outbidding (don't allow if user already has highest bid)

---

## 🚀 Extra Features

| Feature              | Benefit                                         |
| -------------------- | ----------------------------------------------- |
| Bid History          | Users can view past bids                        |
| Countdown Timer      | Real-time auction progress                      |
| Winner Notification  | Push/in-app alert to winners                    |
| Admin Notes          | Manual notes per transaction or user            |
| Rate Limiting        | Prevent spam bidding or rapid fire              |
| Anti Self-Outbidding | Prevent bidding if user already has highest bid |

