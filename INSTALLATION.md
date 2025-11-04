# Cash Book Dashboard - Installation Guide

## Quick Start Guide

Follow these simple steps to get your Cash Book application up and running!

---

## Step 1: Prerequisites

Make sure you have the following installed:
- **WAMP** (Windows), **XAMPP**, **MAMP** (Mac), or **LAMP** (Linux)
- **PHP 7.4** or higher
- **MySQL 5.7** or higher
- **Apache** web server

---

## Step 2: Extract Files

1. Download/Extract all files to your web server directory:
   - For WAMP: `C:\wamp\www\Cashbook\`
   - For XAMPP: `C:\xampp\htdocs\Cashbook\`
   - For MAMP: `/Applications/MAMP/htdocs/Cashbook/`

---

## Step 3: Start Your Server

1. Start your WAMP/XAMPP/MAMP application
2. Make sure both **Apache** and **MySQL** services are running (green lights)
3. Verify by opening: `http://localhost/phpmyadmin`

---

## Step 4: Database Setup (Choose One Method)

### Method A: Automatic Setup (Recommended - Easiest!)

1. Open your browser
2. Navigate to: `http://localhost/Cashbook/setup.php`
3. Click the **"Start Setup"** button
4. Wait for all steps to complete (should show green checkmarks)
5. Click **"Go to Cash Book Dashboard"**
6. Done! 🎉

### Method B: Manual Setup via phpMyAdmin

1. Open phpMyAdmin: `http://localhost/phpmyadmin`
2. Click on **"Import"** tab
3. Click **"Choose File"** and select `database.sql` from the Cashbook folder
4. Click **"Go"** button at the bottom
5. Wait for success message
6. Done! 🎉

### Method C: Manual Setup via MySQL Command Line

1. Open Command Prompt/Terminal
2. Navigate to the Cashbook directory:
   ```bash
   cd C:\wamp\www\Cashbook
   ```
3. Run the SQL file:
   ```bash
   mysql -u root -p < database.sql
   ```
4. Press Enter (leave password blank if you haven't set one)
5. Done! 🎉

---

## Step 5: Configure Database Connection (If Needed)

Only do this if you have custom MySQL settings!

1. Open `config.php` in a text editor
2. Update these values if needed:
   ```php
   define('DB_HOST', 'localhost');    // Usually 'localhost'
   define('DB_USER', 'root');         // Your MySQL username
   define('DB_PASS', '');             // Your MySQL password (empty by default)
   define('DB_NAME', 'cashbook');     // Database name
   ```
3. Save the file

---

## Step 6: Access the Application

1. Open your browser
2. Navigate to: `http://localhost/Cashbook/`
3. You should see the Cash Book Dashboard! 🎊

---

## What's Included Out of the Box?

✅ **10 Sample Users/Groups:**
- Tushar Rathod
- Rajan Zala
- Amit Shah
- Priya Patel
- Neha Desai
- Office Expenses
- Travel Expenses
- Client Payments
- Vendor Payments
- Salary

✅ **10 Sample Transactions** for testing

---

## Features You Can Use Right Away

### 1. Add Cash Entries
- Click on **Cash In** or **Cash Out** cards
- Fill in Date & Time, Amount, User/Group, and Message
- Click the button to add

### 2. View Dashboard
- See Total Balance (updates automatically)
- View statistics (Total Cash In, Cash Out, Entries)
- Browse all transactions

### 3. Search & Filter
- Search by user name or message (min 3 characters)
- Filter by date range
- Filter by user/group
- Filter by type (Cash In/Out)
- Sort by date or amount

### 4. Manage Users
- Click **"Manage Users"** button in the header
- Add new users/groups
- Delete existing users (with confirmation)

---

## Troubleshooting

### Problem: "Database connection failed"

**Solution:**
1. Check if MySQL is running (green in WAMP/XAMPP)
2. Verify credentials in `config.php`
3. Make sure database "cashbook" exists

### Problem: "Cannot connect to localhost"

**Solution:**
1. Check if Apache is running (green in WAMP/XAMPP)
2. Try using `http://127.0.0.1/Cashbook/` instead
3. Check if port 80 is available

### Problem: "Page not found" or "404 error"

**Solution:**
1. Verify files are in the correct directory
2. Check the URL: `http://localhost/Cashbook/` (capital C)
3. Make sure `index.php` exists in the folder

### Problem: Transactions not loading

**Solution:**
1. Open browser console (F12) and check for errors
2. Verify database is set up correctly
3. Check if `api.php` is accessible: `http://localhost/Cashbook/api.php?action=getUsers`

### Problem: Styles look broken

**Solution:**
1. Clear browser cache (Ctrl+F5)
2. Check if `style.css` exists
3. Verify Font Awesome CDN is accessible (check internet connection)

---

## Testing the Installation

### Quick Test Checklist

1. ✅ Open `http://localhost/Cashbook/`
2. ✅ You see the Cash Book Dashboard
3. ✅ User dropdowns show sample users
4. ✅ Sample transactions are visible
5. ✅ Total Balance shows a number
6. ✅ Add a test Cash In entry (should work without errors)
7. ✅ Search for "Tushar" (should show results)
8. ✅ Click "Manage Users" (should open user management page)

If all above work, **congratulations! Your installation is successful!** 🎉

---

## File Structure

```
Cashbook/
├── index.php              # Main dashboard page
├── manage-users.php       # User management page
├── setup.php              # Database setup wizard
├── style.css              # Styling
├── script.js              # Main JavaScript
├── api.php                # Main API endpoints
├── users-api.php          # User management API
├── config.php             # Database configuration
├── database.sql           # Database structure & sample data
├── .htaccess              # Apache configuration
├── README.md              # Project documentation
└── INSTALLATION.md        # This file
```

---

## Default Database Credentials

- **Host:** localhost
- **Username:** root
- **Password:** (empty)
- **Database Name:** cashbook

---

## Next Steps After Installation

1. **Add Your Own Users:**
   - Go to "Manage Users" page
   - Delete sample users you don't need
   - Add your actual users/groups

2. **Clear Sample Data:**
   - Open phpMyAdmin
   - Go to "cashbook" database
   - Click on "entries" table
   - Click "Empty" to remove sample transactions

3. **Start Using:**
   - Add your first real transaction
   - Explore filtering and search features
   - Bookmark the page for easy access

---

## Security Recommendations

1. **Change Default MySQL Password:**
   ```sql
   SET PASSWORD FOR 'root'@'localhost' = PASSWORD('your_new_password');
   ```
   Then update `config.php` with the new password

2. **For Production Use:**
   - Create a dedicated MySQL user (not root)
   - Use a strong password
   - Enable HTTPS
   - Restrict file permissions
   - Regular backups of the database

---

## Getting Help

If you encounter issues:

1. Check this installation guide thoroughly
2. Review the troubleshooting section
3. Check browser console for JavaScript errors (F12)
4. Check PHP error logs in WAMP/XAMPP control panel
5. Verify all files are present and not corrupted

---

## Browser Requirements

- **Recommended:** Google Chrome (latest)
- **Also supports:** Firefox, Safari, Edge, Opera
- **Minimum:** Any modern browser with JavaScript enabled

---

## System Requirements

- **OS:** Windows, macOS, or Linux
- **RAM:** 512 MB minimum (1 GB recommended)
- **Storage:** 50 MB free space
- **PHP:** 7.4 or higher
- **MySQL:** 5.7 or higher
- **Apache:** 2.4 or higher

---

## Frequently Asked Questions

### Can I use this on a production server?
Yes, but make sure to:
- Change MySQL password
- Use HTTPS
- Remove setup.php after installation
- Implement user authentication

### Can I customize the design?
Yes! Edit `style.css` to change colors, fonts, and layout.

### Can I add more features?
Absolutely! The code is well-documented and easy to extend.

### Is it mobile-friendly?
Yes! The design is fully responsive and works great on all devices.

### Can I export data to Excel?
Not yet, but it's on the roadmap. You can export from phpMyAdmin for now.

---

## Support & Updates

For issues, suggestions, or contributions, please refer to the README.md file.

---

**Congratulations on setting up your Cash Book Dashboard! 📚💰**

Enjoy managing your cash entries with ease! 🎉

