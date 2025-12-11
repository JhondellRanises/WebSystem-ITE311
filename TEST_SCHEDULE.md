# Schedule Management System - Test Instructions

## ✅ Database Table Created Successfully!

The `schedules` table has been created with all required fields and foreign keys.

### Table Structure Verified:
- ✅ id (Primary Key, Auto Increment)
- ✅ course_id (Foreign Key to courses)
- ✅ instructor_id (Foreign Key to users)
- ✅ day_of_week (VARCHAR)
- ✅ start_time (TIME)
- ✅ end_time (TIME)
- ✅ room_number (VARCHAR, Optional)
- ✅ building (VARCHAR, Optional)
- ✅ duration_minutes (INT, Optional)
- ✅ capacity (INT, Optional)
- ✅ notes (TEXT, Optional)
- ✅ is_active (BOOLEAN, Default: 1)
- ✅ created_at (DATETIME)
- ✅ updated_at (DATETIME)

---

## 🧪 Test Creating a Schedule

### Step 1: Login as Admin
1. Go to: `http://localhost/ITE311-RANISES/admin/dashboard`
2. Login with admin credentials

### Step 2: Navigate to Manage Schedule
1. Click "Manage Schedule" in the navigation menu
2. Or go directly to: `http://localhost/ITE311-RANISES/admin/manage-schedules`

### Step 3: Create a Schedule
1. Click "Add Schedule" button
2. Fill in the form:
   - **Course**: Select any course from the dropdown
   - **Instructor**: Select any teacher from the dropdown
   - **Day of Week**: Select a day (e.g., Monday)
   - **Start Time**: Enter a time (e.g., 08:00)
   - **End Time**: Enter a time (e.g., 09:00)
   - **Room Number**: Enter a room (e.g., 101) - Optional
   - **Building**: Enter a building (e.g., Science Building) - Optional
   - **Room Capacity**: Enter capacity (e.g., 50) - Optional
   - **Notes**: Add any notes - Optional
   - **Status**: Check "Active" checkbox
3. Click "Save Schedule"

### Step 4: Verify
1. Schedule should appear in the list
2. You should see a success message
3. Schedule details should be displayed in the table

---

## ✨ What You Can Do Now

### As Admin
- ✅ Create schedules
- ✅ Edit schedules
- ✅ Delete schedules
- ✅ View enrolled students
- ✅ Search and filter schedules

### As Teacher
- ✅ View assigned schedules
- ✅ See schedule details
- ✅ View location and timing

### As Student
- ✅ View enrolled course schedules
- ✅ See instructor information
- ✅ View location and timing

---

## 🔧 If You Still Get Errors

### Error: "Table doesn't exist"
- The table was just created, try refreshing the page
- Clear your browser cache (Ctrl+Shift+Delete)
- Restart your browser

### Error: "Cannot display error view"
- This usually means the database table is missing
- The table has now been created, so this should be fixed
- Try the test again

### Error: "Field validation failed"
- Make sure you fill in all required fields (marked with *)
- Course and Instructor are required
- Day of week must be a valid day

---

## 📊 Database Status

**Table Name**: `schedules`  
**Status**: ✅ Created and Ready  
**Records**: 0 (Empty, ready for new schedules)  
**Foreign Keys**: ✅ Configured  
**Charset**: utf8mb4  

---

## 🎯 Next Steps

1. ✅ Refresh your browser
2. ✅ Go to `/admin/manage-schedules`
3. ✅ Click "Add Schedule"
4. ✅ Fill in the form
5. ✅ Click "Save Schedule"
6. ✅ Verify it appears in the list

---

**Everything is now ready!** Try creating a schedule now. 🚀
