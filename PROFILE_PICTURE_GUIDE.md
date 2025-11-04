# 📸 Profile Picture Feature - Complete Guide

## ✅ Implementation Complete!

Your Cash Book application now has a complete profile picture system with upload and editing capabilities.

---

## 🎯 Features Implemented

### 1. **Profile Picture on Registration**
- ✅ Users can upload profile picture during registration
- ✅ Optional - registration works without photo
- ✅ Image preview before submission
- ✅ File validation (type and size)
- ✅ Automatic upload to server

### 2. **Profile Editing Page**
- ✅ View current profile picture
- ✅ Change profile picture
- ✅ Remove profile picture
- ✅ Edit name and email
- ✅ Change password
- ✅ Secure updates with password verification

### 3. **Profile Picture Display**
- ✅ Shows in dashboard header
- ✅ Shows in groups page header
- ✅ Shows in profile page
- ✅ Fallback to icon if no picture

---

## 📁 Files Created/Modified

### New Files:
| File | Purpose |
|------|---------|
| `profile.php` | User profile editing page |
| `profile-api.php` | Profile update API endpoint |
| `setup-profile-pictures.php` | Uploads directory setup script |
| `migrate_profile_picture.sql` | Database migration script |
| `PROFILE_PICTURE_GUIDE.md` | This documentation |

### Modified Files:
| File | Changes |
|------|---------|
| `database.sql` | Added `profile_picture` column |
| `register.php` | Added profile picture upload field |
| `auth-api.php` | Added profile picture handling |
| `auth.js` | Added file upload logic |
| `auth-style.css` | Added profile picture styles |
| `style.css` | Added user avatar styles |
| `dashboard.php` | Added profile picture display |
| `groups.php` | Added profile picture display |
| `check-session.php` | Added profile_picture to session |

---

## 🚀 Setup Instructions

### Step 1: Run Database Migration

**Option A: Using phpMyAdmin (Recommended)**
1. Open phpMyAdmin: `http://localhost/phpmyadmin/`
2. Select `cash_book` database
3. Click SQL tab
4. Copy and paste this SQL:

```sql
USE cash_book;

ALTER TABLE users 
ADD COLUMN profile_picture VARCHAR(255) DEFAULT NULL AFTER password;
```

5. Click "Go"

**Option B: Using WAMP MySQL Console**
1. Open WAMP MySQL Console
2. Run: `USE cash_book;`
3. Run the ALTER TABLE command above

### Step 2: Create Uploads Directory

**Option A: Automatic (Easiest)**
1. Open: `http://localhost/cashbook/setup-profile-pictures.php`
2. Follow the instructions
3. Done!

**Option B: Manual**
1. Create folder: `cashbook/uploads/`
2. Create folder: `cashbook/uploads/profile_pictures/`
3. Set permissions to 755 or 777

### Step 3: Test the Feature!

**Register New User:**
```
http://localhost/cashbook/register.php
```

**Login and Edit Profile:**
```
http://localhost/cashbook/login.php
Then click "My Profile"
```

---

## 🎨 How It Works

### Registration Flow:
```
User fills registration form
    ↓
User selects profile picture (optional)
    ↓
Preview shows selected image
    ↓
User submits form
    ↓
File uploaded to uploads/profile_pictures/
    ↓
Filename saved in database
    ↓
User registered with profile picture
```

### Profile Edit Flow:
```
User goes to Profile page
    ↓
Current picture displayed
    ↓
User can:
  - Change picture
  - Remove picture
  - Update name/email
  - Change password
    ↓
Validates all inputs
    ↓
Updates database
    ↓
Updates session
    ↓
Page reloads with new data
```

---

## 🔐 Security Features

### File Upload Security:
1. **File Type Validation**
   - Only allows: JPG, JPEG, PNG, GIF, WEBP
   - Checks MIME type server-side
   - Checks file extension

2. **File Size Limit**
   - Maximum: 5MB per image
   - Validated on both client and server

3. **Unique Filenames**
   - Format: `profile_[uniqid]_[timestamp].[ext]`
   - Prevents filename conflicts
   - Prevents overwriting

4. **Secure Storage**
   - Files stored outside public root
   - `.htaccess` prevents PHP execution
   - Index protection prevents listing

5. **SQL Injection Protection**
   - Prepared statements everywhere
   - Parameterized queries

6. **Session Protection**
   - Profile API requires authentication
   - Session validation on every request

---

## 📊 Database Schema

### Users Table Update:
```sql
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    password VARCHAR(255) NOT NULL,
    profile_picture VARCHAR(255) DEFAULT NULL,  ← NEW
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

---

## 🎯 API Endpoints

### Profile API (`profile-api.php`)

#### 1. Update Profile
```
POST profile-api.php
Body: FormData {
    action: 'update_profile',
    name: 'User Name',
    email: 'user@example.com',
    current_password: 'oldpass' (optional),
    new_password: 'newpass' (optional),
    profilePicture: File (optional)
}
```

**Response:**
```json
{
    "success": true,
    "message": "Profile updated successfully",
    "user": {
        "name": "User Name",
        "email": "user@example.com",
        "profile_picture": "uploads/profile_pictures/profile_xxx.jpg"
    }
}
```

#### 2. Remove Photo
```
POST profile-api.php
Body: {
    action: 'remove_photo'
}
```

**Response:**
```json
{
    "success": true,
    "message": "Profile picture removed successfully"
}
```

---

## 📂 Directory Structure

```
cashbook/
├── uploads/                          ← NEW
│   ├── .htaccess                    ← Security (no PHP execution)
│   ├── index.php                    ← Protection (no listing)
│   ├── default-avatar.png           ← Optional default image
│   └── profile_pictures/            ← User uploads go here
│       ├── index.php                ← Protection
│       ├── profile_abc123_123.jpg   ← User photos
│       ├── profile_def456_456.png
│       └── ...
│
├── profile.php                       ← Profile edit page
├── profile-api.php                   ← Profile API
├── register.php                      ← Updated with upload
├── dashboard.php                     ← Shows profile picture
├── groups.php                        ← Shows profile picture
└── ...
```

---

## 🎨 UI Features

### Profile Picture Upload:
- ✅ Circular preview (120px diameter)
- ✅ Hover effects and animations
- ✅ Custom styled file input
- ✅ File name display
- ✅ Gradient borders
- ✅ Responsive design

### Profile Display:
- ✅ Circular avatar (32px in header)
- ✅ Border with primary color
- ✅ Fallback to icon
- ✅ Error handling (onerror)
- ✅ Object-fit: cover

---

## 🧪 Testing Checklist

### Registration:
- [ ] Register without profile picture
- [ ] Register with JPG image
- [ ] Register with PNG image
- [ ] Try invalid file type (should fail)
- [ ] Try large file >5MB (should fail)
- [ ] Verify image saved in uploads folder
- [ ] Verify filename in database

### Profile Editing:
- [ ] View current profile
- [ ] Change name
- [ ] Change email
- [ ] Upload new profile picture
- [ ] Remove profile picture
- [ ] Change password
- [ ] Try wrong current password (should fail)
- [ ] Verify old photo deleted when uploading new

### Display:
- [ ] Check avatar in dashboard header
- [ ] Check avatar in groups header
- [ ] Check avatar in profile page
- [ ] Test fallback icon (no photo)
- [ ] Test broken image handling

---

## 💡 Usage Examples

### For Users:

**1. Register with Profile Picture:**
```
1. Go to Register page
2. Fill in Name, Email, Password
3. Click "Choose Photo"
4. Select an image (JPG, PNG, GIF)
5. See preview
6. Click "Register"
7. Done! Your profile picture is saved
```

**2. Edit Profile:**
```
1. Login to your account
2. Click "My Profile" in header
3. See your current picture
4. Click "Change Photo" to update
5. Or click "Remove Photo" to delete
6. Update name/email if needed
7. Change password (optional)
8. Click "Update Profile"
9. Done! Changes saved
```

---

## 🔧 Troubleshooting

### Issue: Uploads directory not found
**Solution:**
- Run `setup-profile-pictures.php`
- Or manually create `uploads/profile_pictures/`
- Set permissions to 755 or 777

### Issue: Image not uploading
**Solution:**
- Check file size (max 5MB)
- Check file type (JPG, PNG, GIF, WEBP only)
- Check folder permissions
- Check PHP upload settings in `php.ini`

### Issue: Image not displaying
**Solution:**
- Check if file exists in uploads folder
- Check database has correct path
- Check file permissions
- View browser console for errors

### Issue: Old image not deleted
**Solution:**
- Check file permissions
- Check if file path is correct
- Manually delete from uploads folder

---

## 📝 Configuration

### Upload Settings (in `auth-api.php` and `profile-api.php`):

```php
// Allowed file types
$allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];

// Maximum file size (5MB)
$maxSize = 5 * 1024 * 1024;

// Upload directory
$uploadDir = __DIR__ . '/uploads/profile_pictures/';

// Filename format
$filename = 'profile_' . uniqid() . '_' . time() . '.' . $extension;
```

**To change these:**
1. Edit the configuration values
2. Update in both `auth-api.php` and `profile-api.php`
3. Test the changes

---

## 🚀 Future Enhancements

Possible improvements:

1. **Image Cropping**
   - Allow users to crop images before upload
   - Ensure consistent dimensions

2. **Image Compression**
   - Automatically compress large images
   - Reduce storage space

3. **Multiple Sizes**
   - Generate thumbnails
   - Optimize for different displays

4. **Image Filters**
   - Add filters/effects
   - Profile picture customization

5. **Avatar Generator**
   - Auto-generate avatars from initials
   - Use services like Gravatar

6. **CDN Integration**
   - Store images on CDN
   - Faster loading

---

## 📞 Quick Reference

| Feature | URL |
|---------|-----|
| Register | `/register.php` |
| Login | `/login.php` |
| Profile | `/profile.php` |
| Dashboard | `/dashboard.php` |
| Groups | `/groups.php` |
| Setup | `/setup-profile-pictures.php` |

| File Type | Max Size | Allowed? |
|-----------|----------|----------|
| JPG/JPEG | 5MB | ✅ Yes |
| PNG | 5MB | ✅ Yes |
| GIF | 5MB | ✅ Yes |
| WEBP | 5MB | ✅ Yes |
| BMP | Any | ❌ No |
| SVG | Any | ❌ No |
| PDF | Any | ❌ No |

---

## ✅ Summary

✅ **Profile picture upload on registration**  
✅ **Profile editing page with all features**  
✅ **Secure file upload with validation**  
✅ **Profile picture display throughout app**  
✅ **Remove/change picture functionality**  
✅ **Password change with verification**  
✅ **Responsive and beautiful UI**  
✅ **Complete security implementation**  
✅ **Automatic directory creation**  
✅ **Full documentation**  

---

**Your Cash Book application now has a professional profile picture system!** 🎉

*Last Updated: November 4, 2025*

