# Email System Documentation

## Overview

The Yore framework includes a robust email system used across multiple applications, including **First Home Healthcare (FHHC)** and **Luxe Card Club (LCC)**. The system uses PHPMailer for SMTP email delivery and includes template management, tracking, and automated notification features.

## Architecture

### Core Components

1. **Mail Module** (`/modules/Mail/Module.php`)
2. **Email Templates** (Database-stored)
3. **Email Tracking** (Database-stored sent emails)
4. **Trait Integration** (YoreTrait and FredTrait implementations)

---

## Mail Module

### Location
`/modules/Mail/Module.php`

### Dependencies
- PHPMailer library (`PHPMailer\PHPMailer\PHPMailer`)
- SMTP server configuration

### Configuration

Each module has its own `settings.json` file with SMTP configuration:

```json
{
  "from_address": "info@example.com",
  "from_name": "Company Name",
  "username": "smtp_username",
  "password": "smtp_password",
  "garfenibbin": "encrypted_password_alternative",
  "cipher": "AES-128-CTR",
  "driver": "smtp",
  "host": "smtp.mailgun.org",
  "port": "587",
  "encryption": "tls"
}
```

**Note**: The system supports both `password` and `garfenibbin` fields for password storage, with `garfenibbin` taking precedence if set.

### Main Method: `send()`

```php
public function send($to, $subject, $body, $addEmbeddedImage = false)
```

#### Parameters:
- `$to` - String or array of recipient email addresses
- `$subject` - Email subject line
- `$body` - HTML email body
- `$addEmbeddedImage` - Optional array for embedded images `[path, cid, name, encoding, mime]`

#### Returns:
- Success: `"Message has been sent"`
- Failure: `"Message could not be sent. Mailer Error: {error details}"`

#### Usage Example:

```php
// Single recipient
$controller->mail->send(
    'user@example.com',
    'Welcome to Our Service',
    '<h1>Welcome!</h1><p>Thank you for signing up.</p>'
);

// Multiple recipients
$controller->mail->send(
    ['user1@example.com', 'user2@example.com'],
    'Team Notification',
    '<p>Important team update...</p>'
);
```

---

## Database Schema

### Email Templates Table

**LCC**: `lcc_emails`  
**FHHC**: `fhhc_emails`

#### Columns:
- `id` - Primary key
- `created_at` - Timestamp
- `updated_at` - Timestamp
- `deleted_at` - Soft delete timestamp
- `slug` - Template identifier (e.g., 'reminder', 'welcome')
- `body` - HTML email template content

### Email Tracking Table

**LCC**: `lcc_emails_sent`  
**FHHC**: `fhhc_emails_sent`

#### Columns:
- `id` - Primary key
- `user_id` - Foreign key to user
- `slug` - Template identifier used
- `body` - Actual email body sent
- `created_at` - When email was sent

#### Purpose:
- Prevents duplicate emails (e.g., only one reminder per user)
- Audit trail of all communications
- Debugging and compliance

---

## Email Scenarios

### 1. Password Reset (Lost Password)

#### Trigger:
User submits lost password form with username/email

#### Implementation:
**LCC**: `/modules/LCC/Traits/YoreTrait.php` (line 280-316)  
**FHHC**: `/modules/FHHC/Traits/YoreTrait.php` (similar)

#### Process:
1. Query database for matching user(s)
2. Generate unique token with `uniqid()`
3. Store token in user record
4. Create password reset link
5. Send email with link
6. Redirect with success message

#### Code Example (LCC):

```php
case 'lostpassword':
    $sql = "SELECT * FROM lcc_users WHERE username = ? OR email = ?";
    $stmt = $this->controller->database->sql($sql, [$_REQUEST['username'], $_REQUEST['email']]);
    $users = $stmt->fetchAll();
    
    foreach ($users as $user) {
        $token = uniqid();
        $this->controller->database->sql("UPDATE lcc_users SET token=? WHERE id=?", [$token, $user['id']]);
        
        $link = "https://app.luxecardclub.com/register/resetpw/$token";
        
        $body = <<<EOB
<p>Username: <b>{$user['username']}</b></p>
<h4>Please click on the link below to reset your password:</h4>
<p><a href="$link"><b>$link</b></a></p>
EOB;
        
        $this->controller->mail->send($user['email'], "Login Info requested", $body);
    }
    
    $controller->back('Email Sent Successfully.');
    break;
```

#### Email Content:
- **Subject**: "Login Info requested" (LCC) or "First Home Healthcare Login Info requested" (FHHC)
- **Body**: Username and password reset link
- **Link Format**: `https://app.{domain}.com/register/resetpw/{unique_token}`

---

### 2. Form Completion Notifications (FHHC Only)

#### Trigger:
Patient/CNA/HHA completes all required forms

#### Implementation:
`/modules/FHHC/Traits/FredTrait.php` (line 408-442)

#### Process:
1. Check if all forms completed (`$formComp == $formCtr`)
2. Verify user status is 0 (not previously notified)
3. Send confirmation email to user
4. Send notification to admin team (`care@firsthhc.com`)
5. Update user status to 1
6. Record in `fhhc_emails_sent` table

#### Code Example:

```php
if ($formComp == $formCtr) {
    $body = "Thank you for completing the forms!";
    
    if (isset($_SESSION['patient']) && $_SESSION['patient']['status'] == 0) {
        $email = $_SESSION['patient']['email'];
        
        // Notify user
        $this->controller->mail->send($email, "First Home Healthcare Forms Completed!", $body);
        
        // Notify admin
        $this->controller->mail->send('care@firsthhc.com', "Patient Forms Completed - $first $last", $body);
        
        // Update status
        $sql = "UPDATE fhhc_patients SET `status` = 1 WHERE user_id=?";
        $this->controller->database->sql($sql, $user_id);
        
        // Track email
        $sql = "INSERT INTO fhhc_emails_sent (user_id, slug, body) VALUES (?,?,?)";
        $this->controller->database->sql($sql, [$user_id, 'client_complete', $body]);
    }
}
```

#### Recipients:
- **User**: Confirmation of completion
- **Admin Team**: Notification for follow-up
- **Developer** (in code): Copy to `mechickaboola@gmail.com` for monitoring

#### Email Types:
- `cna_complete` - CNA forms completed
- `hha_complete` - HHA forms completed  
- `client_complete` - Patient forms completed

---

### 3. Automated Reminder Emails (Cron Job)

#### Trigger:
Scheduled task (typically daily via cron)

#### Implementation:
**LCC**: `/modules/LCC/Traits/YoreTrait.php` (line 401-456)  
**FHHC**: `/modules/FHHC/Traits/YoreTrait.php` (similar, currently disabled)

#### Process:
1. Find users registered >24 hours ago
2. Filter those who haven't completed required forms
3. Exclude users already sent reminders
4. Generate auto-login link with encoded credentials
5. Send reminder email
6. Record in emails_sent table

#### SQL Query Logic:

```sql
SELECT p.first_name, p.last_name, p.email, u.id as user_id, u.password 
FROM lcc_patients p
JOIN lcc_users u ON u.id = p.user_id
LEFT JOIN lcc_emails_sent s ON s.user_id=p.user_id AND s.slug='reminder'
WHERE s.id IS NULL 
  AND p.status = 0 
  AND p.deleted_at IS NULL 
  AND p.created_at < DATE_SUB(NOW(), INTERVAL 1 DAY)
```

#### Key Features:
- **One-time reminder**: `LEFT JOIN` with `s.id IS NULL` ensures no duplicates
- **Auto-login link**: Encodes username and password for seamless access
- **Tracking**: Records each sent reminder with slug='reminder'

#### Code Example:

```php
function yore_cron($controller = false) {
    $sql = "SELECT ... FROM lcc_patients ...";
    $rows = $this->controller->database->sql($sql)->fetchAll();
    
    foreach ($rows as $row) {
        $password = $this->controller->encodeAll($row['password']);
        $username = $this->controller->encodeAll($row['email']);
        $link = "https://app.luxecardclub.com/module/users/auto_login?a=$username&b=$password";
        
        $body = <<<EOB
<h4>Hello {$row['first_name']} {$row['last_name']},</h4>
<p>This is a reminder from Luxe Card Club. Please complete our intake forms...</p>
<p><a href="$link">https://app.luxecardclub.com/login</a></p>
EOB;
        
        $this->controller->mail->send($row['email'], "First Home Healthcare Reminder", $body);
        
        $sql = "INSERT INTO lcc_emails_sent (user_id, slug, body) VALUES (?,?,?)";
        $this->controller->database->sql($sql, [$row['user_id'], 'reminder', $body]);
    }
}
```

#### Current Status:
- **LCC**: Currently disabled with `return;` at function start
- **FHHC**: Currently disabled with `return;` at function start

---

## Email Template Management

### Admin Interface

Both systems provide admin interfaces for managing email templates:

**LCC**: `https://app.luxecardclub.com/emails/index`  
**FHHC**: `https://app.firsthealthhc.com/emails/index`

### Features:
- **View all templates**: DataTable with search/sort
- **Add new template**: Form with slug and body fields
- **Edit template**: Modify existing templates
- **Soft delete**: Move to trash (deleted_at)
- **Restore**: Recover from trash

### CRUD Operations

#### Add Email Template:

```php
case "addemails":
    $fields = "slug,body";
    $sql = "INSERT INTO lcc_emails (slug, body) VALUES (?, ?)";
    $controller->database->sql($sql, [$_REQUEST['slug'], $_REQUEST['body']]);
    $controller->back("Record Added");
    break;
```

#### Edit Email Template:

```php
case "editemails":
    $sql = "UPDATE lcc_emails SET slug=?, body=? WHERE id=?";
    $controller->database->sql($sql, [$_REQUEST['slug'], $_REQUEST['body'], $controller->arg1]);
    $controller->back("Record Updated");
    break;
```

### Template Usage

While the system supports database-stored templates, current implementations primarily use inline HTML for email bodies. Future enhancements could retrieve templates from the database:

```php
// Future implementation example
$sql = "SELECT body FROM lcc_emails WHERE slug = ?";
$stmt = $controller->database->sql($sql, ['welcome']);
$template = $stmt->fetch();
$body = str_replace('{name}', $user['name'], $template['body']);
```

---

## Security Considerations

### 1. Password Handling
- Passwords stored in plain text (consider hashing in future updates)
- Token-based reset prevents password exposure in emails
- Tokens are unique per request (`uniqid()`)

### 2. Email Validation
- Uses `filter_var($email, FILTER_VALIDATE_EMAIL)`
- Checks for duplicate emails before updates
- Prevents unauthorized email changes

### 3. Auto-login Links
- Credentials encoded with `encodeAll()` method
- Links should be time-limited (consider adding expiration)
- HTTPS ensures encrypted transmission

### 4. SQL Injection Prevention
- All queries use prepared statements with placeholders
- Parameters passed separately from SQL

---

## Common Issues & Troubleshooting

### Email Not Sending

1. **Check SMTP settings** in module's `settings.json`
2. **Verify credentials** - test with separate email client
3. **Check firewall** - ensure port 587 (or 465) is open
4. **Review logs** - PHPMailer returns detailed error messages
5. **Test from command line**:
   ```bash
   php web/cli.php
   # Call test email function
   ```

### Duplicate Emails

1. **Check tracking table** - `lcc_emails_sent` / `fhhc_emails_sent`
2. **Verify LEFT JOIN logic** in cron queries
3. **Review status flags** - ensure status updates after sending

### Email Goes to Spam

1. **Configure SPF records** for domain
2. **Set up DKIM** signing
3. **Add DMARC policy**
4. **Use reputable SMTP service** (e.g., Mailgun, SendGrid)
5. **Avoid spam trigger words** in subject/body

---

## Best Practices

### 1. Template Design
- Use responsive HTML templates
- Include plain text alternative
- Test across email clients
- Keep images external (not embedded) when possible

### 2. Email Frequency
- Implement rate limiting for user-triggered emails
- Space out automated reminders
- Provide unsubscribe option (compliance)

### 3. Tracking & Analytics
- Log all sent emails with timestamp
- Track delivery status if SMTP service provides webhook
- Monitor bounce rates
- Review email open/click rates

### 4. Error Handling
```php
try {
    $result = $controller->mail->send($email, $subject, $body);
    // Log success
} catch (\Exception $e) {
    // Log error
    error_log("Email send failed: " . $e->getMessage());
    // Don't expose error to user
}
```

---

## Future Enhancements

### Recommended Improvements

1. **Email Queue System**
   - Background processing for bulk emails
   - Retry logic for failed sends
   - Priority queue

2. **Template Variables**
   - Use database templates with placeholders
   - Variable substitution engine
   - Preview before send

3. **Email Service Integration**
   - SendGrid/Mailgun API integration
   - Delivery tracking
   - Bounce handling

4. **User Preferences**
   - Email notification settings
   - Frequency preferences
   - Unsubscribe management

5. **Security Enhancements**
   - Hash passwords (bcrypt/argon2)
   - Time-limited reset tokens
   - Rate limiting on email sends

6. **Analytics Dashboard**
   - Email delivery metrics
   - Open/click tracking
   - User engagement reports

---

## Code References

### Key Files

| Component | LCC | FHHC |
|-----------|-----|------|
| Mail Module | `/modules/Mail/Module.php` | (shared) |
| Yore Trait | `/modules/LCC/Traits/YoreTrait.php` | `/modules/FHHC/Traits/YoreTrait.php` |
| Fred Trait | `/modules/LCC/Traits/FredTrait.php` | `/modules/FHHC/Traits/FredTrait.php` |
| Email Views | `/pages/_domains/app.luxecardclub.com/emails/` | `/pages/_domains/app.firsthealthhc.com/emails/` |
| Config | `/modules/LCC/settings.json` | `/modules/FHHC/settings.json` |

### Method Signatures

```php
// Mail Module
public function send($to, $subject, $body, $addEmbeddedImage = false)

// Controller access
$this->controller->mail->send($email, $subject, $body);

// Module trait
function yore_cron($controller = false)
function yore_module_post(Controller $controller, $value)
```

---

## Support & Resources

### External Documentation
- [PHPMailer Documentation](https://github.com/PHPMailer/PHPMailer)
- [Mailgun API Docs](https://documentation.mailgun.com/)
- [Email on Acid](https://www.emailonacid.com/) - Email testing
- [Can I Email](https://www.caniemail.com/) - HTML/CSS support reference

### Internal Resources
- `/docs/modules.md` - Module system overview
- `/docs/overview.md` - Framework overview
- `/docs/tenancy.md` - Multi-tenancy setup

---

## Changelog

### Current Version
- PHPMailer-based SMTP sending
- Database template storage
- Email tracking system
- Password reset functionality
- Form completion notifications
- Cron-based reminders (currently disabled)

### Known Issues
- Plain text password storage (security concern)
- Cron reminders disabled in both LCC and FHHC
- No retry mechanism for failed sends
- Limited error reporting to users

---

*Last Updated: October 8, 2025*

