# 💳 PayTrack Admin Panel   
Live :[https://linkable.in/paytrack/](https://linkable.in/paytrack/) 

The **PayTrack Admin Panel** is a modern, responsive web application for managing **payment transaction records** with **full CRUD functionality**.  
It acts as the backend management interface for the **PayTrack Android App**, allowing administrators to add, edit, delete, and view transactions in real time.

---

## 🚀 Features

### 📄 Transaction Management
- View all transactions with detailed information:
  - Transaction ID
  - Amount
  - Payment method
  - Date & time
  - Status (Completed / Pending / Failed)
- Add new payment records with form validation
- Edit existing transactions
- Delete transactions with confirmation prompts
- Color-coded **status badges** for quick visual reference

### 🔍 Search & Filter
- Real-time search across:
  - Serial number
  - Payment method
  - Description
- Filter by payment status
- Combine search & filters for precise results

### 📶 Backend-Driven Data
- Server-side pagination for large datasets
- RESTful API delivering JSON responses
- Automatic status updates reflected in the Android app

### 🎨 Modern UI/UX
- Fully responsive layout (desktop, tablet, mobile)
- Gradient-based design with smooth animations
- Intuitive forms and modal dialogs
- Iconography with **Font Awesome**

---

## 🛠 Technical Stack

| Layer         | Technology |
|---------------|------------|
| **Language**  | PHP 7.4+ |
| **Database**  | MySQL 5.7+ |
| **Frontend**  | HTML5, CSS3, Vanilla JavaScript |
| **UI Icons**  | Font Awesome |
| **API**       | RESTful endpoints with JSON |
| **Security**  | Prepared statements for SQL injection prevention |
| **Pagination**| Server-side pagination with metadata |

---

## 📂 Project Structure
`````
paytrack-admin/
├── index.html # Main admin interface
├── styles.css # Modern styling
├── script.js # Frontend interactivity
├── fetch_transactions.php # Fetch paginated data
├── add_transaction.php # Add new transaction
├── update_transaction.php # Update transaction
├── delete_transaction.php # Delete transaction
├── db.php # Database connection
├── transaction_records.sql # Database schema & sample data
└── README.md # Documentation
`````


---

## 💡 Use Cases
Perfect for:
- Businesses needing to **manage payment transactions** in real-time
- Admins wanting a **centralized web dashboard**
- Teams managing **multiple payment sources**
- Supporting **offline-first mobile apps** with backend data control

---

## 📸 Screenshots

<p align="center">
  <img src="https://github.com/sunadrg/paytrack-backend/blob/main/screenshots/admin-dashboard.png" width="45%" />
  <img src="https://github.com/sunadrg/paytrack-backend/blob/main/screenshots/add-transaction.png" width="45%" />
  <img src="https://github.com/sunadrg/paytrack-backend/blob/main/screenshots/edit-transaction.png" width="45%" />
</p>

---

## 🔗 Live Demo & Repository
- 🌐 **Live Admin Panel:** [https://linkable.in/paytrack/](https://linkable.in/paytrack/)  
- 📦 **Source Code Repository:** [GitHub - PayTrack Admin Panel](https://github.com/sunadrg/paytrack-backend)  