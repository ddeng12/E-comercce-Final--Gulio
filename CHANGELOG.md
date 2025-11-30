# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [2.0.0] - 2024-12-XX

### Added
- Complete e-commerce platform with product management
- Shopping cart system with session and database persistence
- Order management system with status tracking
- Payment processing integration (Paystack)
- Invoice generation system
- Coupon and discount code system
- Admin dashboard with full CRUD operations
- Advanced product search and filtering
- User authentication and role-based access control
- Comprehensive security features (CSRF, XSS, SQL injection prevention)
- Database migration system
- Logging and error tracking
- Image upload and management system
- Stock management and inventory tracking
- VAT calculation (inclusive pricing)
- Order history for customers
- Bulk operations for products

### Changed
- Refactored codebase for production readiness
- Improved security with comprehensive input validation
- Enhanced database schema with proper relationships
- Updated authentication system with secure session management
- Improved error handling throughout the application
- Optimized database queries with proper indexing

### Fixed
- Fixed product image path issues on production servers
- Resolved database connection handling for production environments
- Fixed cart persistence issues
- Corrected VAT calculation to be inclusive
- Fixed order creation with proper transaction handling
- Resolved product deletion with foreign key constraints (soft delete)
- Fixed duplicate product creation on form submission (PRG pattern)
- Corrected image display across all pages
- Fixed undefined array key errors in order management

### Security
- Implemented CSRF protection on all forms
- Added comprehensive input sanitization
- Enhanced password hashing with bcrypt
- Improved session security with HttpOnly, Secure, SameSite cookies
- Added SQL injection prevention with prepared statements
- Implemented XSS prevention with output escaping

## [1.0.0] - 2024-XX-XX

### Added
- Initial prototype release
- Basic vendor/service discovery
- User onboarding flow
- City Buddy matching system
- Chatbot integration
- Trust and safety features
- Mobile-first responsive design

---

## Version History

### [2.0.0] - Production Release
Major production-ready update with complete e-commerce functionality.

### [1.0.0] - Initial Release
Prototype version with core city companion features.

---

## Upgrade Notes

### Upgrading to 2.0.0

1. **Database Migration Required**
   - Run `setup.php` to initialize new database schema
   - All existing data will need to be migrated

2. **Configuration Updates**
   - Update `config/config.php` with production database credentials
   - Review security settings in `config/app.php`

3. **File Permissions**
   - Ensure `assets/images/products/` is writable (755 or 777)
   - Ensure `logs/` directory is writable

4. **Breaking Changes**
   - Database schema has changed significantly
   - Session structure updated for security
   - Some API endpoints have changed

---

## Deprecated

- None in current version

## Removed

- Sample data system (replaced with database)
- JSON-based data storage (replaced with MySQL)

---

For detailed migration instructions, see [PRODUCTION.md](PRODUCTION.md).
