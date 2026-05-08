# RSYNC - Room Application System Implementation

## Overview
This implementation adds a complete room application and notification system to the RSYNC boarding house management platform. Tenants can now apply for rooms, and landlords can review and verify/reject applications.

## Database Setup

### Required Table: `applications`
Run `setup_db.php` to create the applications table:
```
php setup_db.php
```

The table structure includes:
- `application_id` - Unique application identifier
- `user_id` - Tenant applying for the room
- `room_id` - Room being applied for
- `status` - Application status (Pending, Verified, Rejected)
- `applied_at` - Timestamp when application was submitted

## Files Modified & Created

### 1. **oop.php** - Added Application Management Methods
```php
// Create a room application
apply_for_room($user_id, $room_id)

// Get tenant's applications
get_tenant_applications($user_id)

// Get all applications for a room (for landlord)
get_room_applications($room_id)

// Verify an application
verify_application($application_id, $user_id, $room_id)

// Reject an application
reject_application($application_id, $room_id)

// Get application count by status
get_application_count_by_status($room_id, $status = null)
```

### 2. **tenant_dashboard.php** (NEW FILE)
A dedicated dashboard for tenants showing:
- **Booked Rooms Section**: Display all active room bookings with:
  - Room name, price, duration
  - Occupancy information
  - Active status badge

- **Applications Section**: Display all applications with:
  - Room details and cover image
  - Application status (Pending, Verified, Rejected)
  - Applied date and time
  - Status-specific messages:
    - "⏳ Waiting for landlord approval" (Pending)
    - "✓ Your application was verified! Proceed to booking." (Verified)
    - "✕ Your application was rejected. Try another room." (Rejected)

**Design Features:**
- Matches landlord dashboard design tokens
- Amber accent color (#e8a020) for brand consistency
- Responsive layout with mobile support
- Clean typography using Syne and DM Sans fonts
- Subtle shadows and spacing

### 3. **landlord_dashboard.php** - Enhanced with Applicant Management
**New Features:**
- Added "Applicants" button to room actions
- New modal displaying:
  - List of all applicants for a room
  - Applicant name, email, and application date
  - Application status badge
  - Verify/Reject action buttons (shown only for Pending applications)

**New JavaScript Functions:**
```js
openApplicants(roomId) - Opens the applicants modal
renderApplicants(apps) - Renders the applicants list
verifyApplicant(appId, userId) - Verifies an application
rejectApplicant(appId) - Rejects an application
```

**New POST Handlers:**
- `get_room_applicants` - Fetches applications for a room
- `verify_application` - Verifies an application and marks it as Verified
- `reject_application` - Rejects an application

### 4. **index.php** - Added Application Flow
**Changes:**
- Added "Apply" button alongside "View" button on room cards
- New `applyForRoom(roomId, roomName)` JavaScript function that:
  - Checks if user is logged in (redirects to login if needed)
  - Checks if user is a tenant
  - Submits application via AJAX
  - Redirects to tenant dashboard on success

**New POST Handler:**
- `apply_for_room` - Handles application submission
  - Checks authentication and role
  - Calls OOP method to create application
  - Returns JSON response with status and message

### 5. **setup_db.php** (NEW FILE)
Database initialization script that:
- Creates the `applications` table if it doesn't exist
- Sets up proper indexes and foreign keys
- Includes error handling for existing tables

## User Flow

### Tenant Application Process
1. **Browse Rooms** (index.php)
   - View room cards with descriptions and images
   - Click "Apply" button on desired room

2. **Authentication Check**
   - If not logged in: Redirected to login page
   - If logged in: Application submitted

3. **Application Submitted**
   - Application status set to "Pending"
   - Tenant redirected to Dashboard
   - Confirmation message displayed

4. **View Application Status** (tenant_dashboard.php)
   - Booked Rooms: Shows all active room bookings
   - Applications: Shows all submitted applications with current status
   - Status updates in real-time after landlord action

### Landlord Application Review Process
1. **Room Management** (landlord_dashboard.php)
   - Click "Applicants" button on any room
   - Modal opens showing all applicants

2. **Review Applications**
   - View applicant details (name, email)
   - See application date
   - View current application status

3. **Verify/Reject**
   - For Pending applications: Click "Verify" or "Reject"
   - Confirmation dialog appears
   - Application status updated in real-time

## Design System Compliance

### Color Palette
- **Primary**: #0a0a0f (Ink)
- **Secondary**: #3d3d4a (Ink 2)
- **Muted**: #8e8ea0 (Ink 3)
- **Background**: #f4f3ef (Soft cream)
- **Accent**: #e8a020 (Amber)
- **Success**: #059669 (Green)
- **Error**: #dc2626 (Red)

### Typography
- **Display**: Syne (800 weight) - Headings
- **Body**: DM Sans (400-500 weight) - Content

### Component Styles
- Border Radius: 14px (var(--r))
- Shadow: 0 2px 12px rgba(0,0,0,.06) (var(--sh))
- Transitions: 150ms (var(--transition-base))

## API Endpoints

### POST Endpoints (JSON Response)

#### Apply for Room
```
POST /index.php
Body: apply_for_room=1&room_id=ROOM_ID

Response:
{
  "status": "success|error",
  "message": "..."
}
```

#### Get Room Applicants
```
POST /landlord_dashboard.php
Body: get_room_applicants=1&room_id=ROOM_ID

Response: Array of applicants
[
  {
    "application_id": int,
    "user_id": int,
    "status": "Pending|Verified|Rejected",
    "applied_at": timestamp,
    "name": string,
    "email": string
  }
]
```

#### Verify Application
```
POST /landlord_dashboard.php
Body: verify_application=1&application_id=APP_ID&user_id=USER_ID&room_id=ROOM_ID

Response: {"status": "success|error"}
```

#### Reject Application
```
POST /landlord_dashboard.php
Body: reject_application=1&application_id=APP_ID&room_id=ROOM_ID

Response: {"status": "success|error"}
```

## Error Handling

### Validation Checks
- User authentication required
- Role-based access (tenants can only apply, landlords can only verify)
- Duplicate application prevention (only one pending/verified per user per room)
- Room ownership verification (only landlord can verify applications)

### User Feedback
- Toast notifications for success/error messages
- Confirmation dialogs for critical actions
- Real-time status updates in modals
- Clear error messages with actionable next steps

## Security Measures
- Session-based authentication
- SQL injection prevention via PDO prepared statements
- CSRF-safe POST handlers with proper content-type headers
- Role-based authorization checks
- Foreign key constraints for data integrity

## Future Enhancements
1. Email notifications when application status changes
2. Bulk applicant actions (approve multiple at once)
3. Application timeline/history view
4. Applicant scoring/ranking system
5. Integration with payment system for verified applications
6. Automated application expiry (after certain days)

## Testing Checklist
- [ ] Run `setup_db.php` to create applications table
- [ ] Test tenant login and dashboard access
- [ ] Test applying for room as logged-in tenant
- [ ] Test apply flow for non-logged-in user
- [ ] Test landlord viewing applications
- [ ] Test verifying applications
- [ ] Test rejecting applications
- [ ] Test duplicate application prevention
- [ ] Test role-based access control
- [ ] Verify responsive design on mobile
- [ ] Test error messages and toast notifications
