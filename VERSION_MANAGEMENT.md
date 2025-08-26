# Version Management Guide

This document outlines the process for updating versions in the RequestQuote module.

## 📋 Files to Update When Changing Version

When releasing a new version, update the version number in these files:

### 1. Main Module File
**File**: `requestquote.php`
- Line ~9: `@version` in the header comment
- Line ~27: `$this->version = 'X.X.X';` in constructor

### 2. Configuration File
**File**: `config.xml`
- Line ~4: `<version><![CDATA[X.X.X]]></version>`

### 3. Documentation Files
**File**: `README.md`
- Version History section
- Add new version with changes

**File**: `CHANGELOG.md`
- Add new version entry at the top
- Follow the existing format

**File**: `install.md`
- Update title with new version number

## 🔢 Version Numbering (Semantic Versioning)

Follow [Semantic Versioning](https://semver.org/) principles:

### MAJOR.MINOR.PATCH (e.g., 2.1.3)

- **MAJOR** (X.0.0): Breaking changes, major rewrites, incompatible API changes
- **MINOR** (0.X.0): New features, enhancements, backward compatible
- **PATCH** (0.0.X): Bug fixes, security patches, minor improvements

### Examples:
- `2.0.0` → `2.0.1`: Bug fixes only
- `2.0.0` → `2.1.0`: New features added
- `2.0.0` → `3.0.0`: Major breaking changes

## 📝 Release Process Checklist

### Before Release:
- [ ] Test all functionality thoroughly
- [ ] Update version numbers in all files
- [ ] Update CHANGELOG.md with all changes
- [ ] Update README.md version history
- [ ] Run verification script (`verify.php`)
- [ ] Test installation on clean PrestaShop
- [ ] Verify admin panel functionality
- [ ] Test AJAX form submissions
- [ ] Check email notifications

### During Release:
- [ ] Create release notes
- [ ] Package module files correctly
- [ ] Test installation package
- [ ] Document any breaking changes
- [ ] Provide migration guide if needed

### After Release:
- [ ] Monitor for issues
- [ ] Update documentation if needed
- [ ] Respond to user feedback
- [ ] Plan next version improvements

## 🏷️ Version History Template

When adding to CHANGELOG.md, use this template:

```markdown
## [X.X.X] - YYYY-MM-DD

### 🚀 Major Updates (for major versions)
- **BREAKING**: Description of breaking changes

### ✅ Fixed
- **Critical**: Critical bug fixes
- **Major**: Important bug fixes
- **Minor**: Small bug fixes

### ✨ Added
- **New**: New features and enhancements

### 🔧 Improved
- **Performance**: Performance improvements
- **Security**: Security enhancements
- **UX**: User experience improvements

### 🔒 Security
- Security-related changes

### 📚 Documentation
- Documentation updates

### 🧪 Testing
- Testing improvements
```

## 🔄 Migration Guidelines

### When to Require Migration:
- Database schema changes
- Template structure changes
- Hook changes
- Configuration changes
- Breaking API changes

### Migration Documentation:
Always provide clear migration steps in CHANGELOG.md for breaking changes.

## 📊 Version Tracking

### Current Version: 2.0.0
### Release Date: 2024-12-19
### Next Planned: 2.0.1 (Bug fixes), 2.1.0 (New features)

## 🛠️ Automated Version Updates (Future)

Consider creating a script to automate version updates:

```bash
#!/bin/bash
# update-version.sh
NEW_VERSION=$1
OLD_VERSION=$(grep -o "version.*[0-9]\+\.[0-9]\+\.[0-9]\+" requestquote.php | grep -o "[0-9]\+\.[0-9]\+\.[0-9]\+")

# Update all files
sed -i "s/$OLD_VERSION/$NEW_VERSION/g" requestquote.php
sed -i "s/$OLD_VERSION/$NEW_VERSION/g" config.xml
# Add other files as needed

echo "Version updated from $OLD_VERSION to $NEW_VERSION"
```

## 📞 Support Versioning

- **Latest Version**: Full support and active development
- **Previous Major**: Security fixes only
- **Older Versions**: No support, recommend upgrade

## 🔍 Version Verification

Use the included `verify.php` script to check version consistency across files.

---

**Remember**: Always test thoroughly before releasing any version update! 