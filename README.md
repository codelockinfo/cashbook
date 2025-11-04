# Cash Book Dashboard

A modern, single-page web application for managing cash-in and cash-out entries with a beautiful and intuitive user interface.

## Features

- **Cash In/Out Entry Forms**: Easy-to-use forms for adding cash transactions
- **Date & Time Tracking**: Record exact datetime for each transaction
- **User/Group Management**: Assign transactions to specific users or groups
- **Message/Notes**: Add descriptions and notes to each entry
- **Real-time Statistics**: View total balance, cash in, cash out, and entry counts
- **Advanced Filtering**: Filter by date range, user/group, and transaction type
- **Search Functionality**: Search transactions by user name or message (minimum 3 characters)
- **Sorting Options**: Sort by date or amount in ascending/descending order
- **Responsive Design**: Works perfectly on desktop, tablet, and mobile devices
- **Beautiful UI**: Modern gradient design with smooth animations

## Technologies Used

- **Frontend**: HTML5, CSS3, JavaScript (Vanilla)
- **Backend**: PHP 7.4+
- **Database**: MySQL 5.7+
- **Icons**: Font Awesome 6.4.0
- **Server**: Apache (WAMP/XAMPP)

## Installation

### Prerequisites

- WAMP, XAMPP, or any Apache/MySQL server
- PHP 7.4 or higher
- MySQL 5.7 or higher

### Setup Instructions

1. **Place files in your web server directory**
   ```
   Copy all files to: C:\wamp\www\Cashbook\
   ```

2. **Create the database**
   - Open phpMyAdmin: `http://localhost/phpmyadmin`
   - Click on "Import" tab
   - Select the `database.sql` file
   - Click "Go" to execute
   
   OR use MySQL command line:
   ```bash
   mysql -u root -p < database.sql
   ```

3. **Configure database connection**
   - Open `config.php`
   - Update the database credentials if needed:
     ```php
     define('DB_HOST', 'localhost');
     define('DB_USER', 'root');
     define('DB_PASS', '');
     define('DB_NAME', 'cashbook');
     ```

4. **Access the application**
   - Open your browser
   - Navigate to: `http://localhost/Cashbook/`

## Database Structure

### Users Table
- `id`: Primary key
- `name`: User/Group name
- `created_at`: Timestamp

### Entries Table
- `id`: Primary key
- `user_id`: Foreign key to users table
- `type`: 'in' or 'out'
- `amount`: Decimal(15,2)
- `datetime`: Transaction datetime
- `message`: Optional text description
- `created_at`: Timestamp

## Usage

### Adding Cash In Entry
1. Fill in the date & time (defaults to current)
2. Enter the amount
3. Select user/group
4. Add a message/description (optional)
5. Click "Add Cash In" button

### Adding Cash Out Entry
1. Fill in the date & time (defaults to current)
2. Enter the amount
3. Select user/group
4. Add a message/description (optional)
5. Click "Add Cash Out" button

### Filtering Transactions
- Use the search bar to find transactions by user name or message
- Filter by date range using the date pickers
- Filter by specific user/group
- Filter by transaction type (Cash In/Out)
- Sort results by date or amount
- Click "Clear Filters" to reset all filters

### Viewing Statistics
The dashboard displays:
- **Total Balance**: Current balance (Cash In - Cash Out)
- **Total Cash In**: Sum of all cash in entries
- **Total Cash Out**: Sum of all cash out entries
- **Total Entries**: Count of all transactions

## Customization

### Colors
Edit `style.css` to change the color scheme:
```css
:root {
    --primary-color: #667eea;
    --secondary-color: #764ba2;
    --success-color: #10b981;
    --danger-color: #ef4444;
    /* ... more colors */
}
```

### Adding More Users
1. Go to phpMyAdmin
2. Open `cashbook` database
3. Insert into `users` table:
   ```sql
   INSERT INTO users (name) VALUES ('New User Name');
   ```

## Security Features

- Input validation on both client and server side
- XSS prevention with HTML escaping
- SQL injection prevention using prepared statements
- Type checking for all inputs
- CORS headers configured

## Browser Compatibility

- Chrome (recommended)
- Firefox
- Safari
- Edge
- Opera

## Responsive Breakpoints

- Desktop: 1400px+
- Tablet: 768px - 1399px
- Mobile: < 768px

## API Endpoints

### GET /api.php?action=getUsers
Returns list of all users/groups

### POST /api.php?action=addEntry
Adds a new cash entry
Parameters: type, user_id, amount, datetime, message

### GET /api.php?action=getEntries
Returns filtered and sorted entries
Parameters: search, date_from, date_to, user_id, type, sort

## Troubleshooting

### Database Connection Error
- Check if MySQL service is running
- Verify database credentials in `config.php`
- Ensure `cashbook` database exists

### Transactions Not Loading
- Check browser console for errors
- Verify API endpoints are accessible
- Check PHP error logs

### Styles Not Applied
- Clear browser cache
- Check if `style.css` is loading
- Verify Font Awesome CDN is accessible

## Future Enhancements

- PDF export functionality
- Excel export
- User authentication
- Multi-currency support
- Transaction categories
- Dashboard charts and graphs
- Email notifications
- Backup and restore

## License

This project is open source and available for personal and commercial use.

## Support

For issues or questions, please check the code comments or create an issue in the repository.

## Credits

- Font Awesome for icons
- Modern CSS gradient techniques
- Responsive design best practices

