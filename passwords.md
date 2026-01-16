# Law Firm Management System - Account Credentials

This file contains the default login credentials for the Law Firm Management System.

## Default Test Accounts

### Administrator Account
- **Username:** `admin`
- **Password:** `admin123`
- **Role:** Administrator
- **Access Level:** Full system access (all modules)

### Advocate Account
- **Username:** `advocate1`
- **Password:** `advocate123`
- **Role:** Advocate
- **Access Level:** View assigned cases, events, and clients

### Receptionist Account
- **Username:** `receptionist1`
- **Password:** `receptionist123`
- **Role:** Receptionist
- **Access Level:** Register clients, schedule appointments, record billing

---

## Important Notes

1. **Security Warning:** These are default test accounts. Change passwords immediately in production environments.

2. **Creating New Users:** Administrators can create new users through the Admin Panel → Users section.

3. **Password Reset:** If you forget your password, contact the system administrator.

4. **Account Management:** Users can change their passwords and update their profile information from the Profile section after logging in.

---

## Setup Instructions

1. **Initial Setup:**
   - Run `database/setup_users.php` to create default users
   - This will create the above accounts with hashed passwords

2. **Login:**
   - Go to `http://localhost/lawfirm/login.php`
   - Select your role from the dropdown
   - Enter username and password
   - Click "Sign In"

3. **First Login:**
   - It's recommended to change your password immediately after first login
   - Go to Profile section to update your information

---

## User Management

Administrators can:
- Create new users (Admin, Advocate, Receptionist)
- Set passwords for new users
- Edit user information
- Delete users (except their own account)

All users can:
- Change their own password
- Update their profile information (name, email, phone)
- Delete their own account

---

**System:** Law Firm Management System  
**Version:** 1.0  
**Database:** lawfirm_db

---

*Note: This file contains sensitive information. Keep it secure and do not commit to public repositories.*
