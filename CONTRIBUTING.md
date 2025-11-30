# Contributing to Gulio E-Commerce Platform

Thank you for your interest in contributing to Gulio! This document provides guidelines and instructions for contributing.

## Code of Conduct

- Be respectful and inclusive
- Welcome newcomers and help them learn
- Focus on constructive feedback
- Respect different viewpoints and experiences

## How to Contribute

### Reporting Bugs

Before creating a bug report:
1. Check if the bug has already been reported
2. Verify it's not a configuration issue
3. Test with the latest version

When reporting a bug, include:
- **Description**: Clear description of the issue
- **Steps to Reproduce**: Detailed steps to reproduce
- **Expected Behavior**: What should happen
- **Actual Behavior**: What actually happens
- **Environment**: PHP version, MySQL version, OS
- **Error Messages**: Full error messages or logs
- **Screenshots**: If applicable

### Suggesting Features

Feature suggestions should include:
- **Use Case**: Why is this feature needed?
- **Proposed Solution**: How should it work?
- **Alternatives**: Other solutions considered
- **Additional Context**: Any other relevant information

### Pull Requests

1. **Fork the repository**
2. **Create a branch**: `git checkout -b feature/your-feature-name`
3. **Make your changes**
4. **Test thoroughly**
5. **Commit**: Use clear, descriptive commit messages
6. **Push**: `git push origin feature/your-feature-name`
7. **Open Pull Request**: Provide detailed description

### Commit Message Guidelines

Use clear, descriptive commit messages:

```
feat: Add product image upload validation
fix: Resolve cart quantity update issue
docs: Update installation instructions
refactor: Improve database query performance
test: Add unit tests for cart functionality
```

Prefix types:
- `feat`: New feature
- `fix`: Bug fix
- `docs`: Documentation changes
- `style`: Code style changes (formatting)
- `refactor`: Code refactoring
- `test`: Adding or updating tests
- `chore`: Maintenance tasks

## Development Setup

### Prerequisites

- PHP 7.4+
- MySQL 5.7+
- Git
- Composer (optional)

### Setup Steps

1. **Clone your fork**:
```bash
git clone https://github.com/yourusername/gulio-ecommerce.git
cd gulio-ecommerce
```

2. **Configure database**:
   - Create database
   - Update `config/config.php`
   - Run migrations via `setup.php`

3. **Set permissions**:
```bash
chmod 755 logs/
chmod 755 assets/images/products/
```

4. **Test the application**:
   - Verify all features work
   - Check for errors in logs

## Coding Standards

### PHP Code Style

- Follow **PSR-12** coding standards
- Use 4 spaces for indentation (no tabs)
- Use meaningful variable and function names
- Add PHPDoc comments for functions and classes
- Keep functions focused and small

### Example

```php
/**
 * Get product by ID
 *
 * @param int $productId Product ID
 * @return array|null Product data or null if not found
 */
function getProductById($productId) {
    $db = Database::getInstance();
    return $db->fetchOne(
        "SELECT * FROM products WHERE id = :id",
        ['id' => $productId]
    );
}
```

### JavaScript Code Style

- Use ES6+ features
- Use meaningful variable names
- Add comments for complex logic
- Follow consistent indentation

### Database

- Use prepared statements (PDO)
- Add indexes for frequently queried columns
- Use transactions for multi-step operations
- Document schema changes in migrations

## Testing

Before submitting a PR:

1. **Test your changes**:
   - Test all affected features
   - Test edge cases
   - Test error handling

2. **Check for errors**:
   - Review PHP error logs
   - Check browser console
   - Verify database queries

3. **Test compatibility**:
   - Test on different browsers
   - Test on different PHP versions (if possible)

## Documentation

When adding features:

1. **Update README.md** if needed
2. **Add code comments** for complex logic
3. **Update CHANGELOG.md** with your changes
4. **Document API changes** if applicable

## Security

- Never commit sensitive data (passwords, API keys)
- Use prepared statements for all database queries
- Validate and sanitize all user inputs
- Follow security best practices
- Report security issues privately

## Review Process

1. All PRs will be reviewed
2. Feedback will be provided
3. Changes may be requested
4. Once approved, PR will be merged

## Questions?

- Open an issue for questions
- Check existing documentation
- Review code examples in the repository

Thank you for contributing! 🎉

