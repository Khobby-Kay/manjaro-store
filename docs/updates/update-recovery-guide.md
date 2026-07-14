# 🔄 Update Recovery Guide - Manjaro E-commerce

## 📋 **Purpose**
This guide provides step-by-step instructions for handling Active E-commerce updates while preserving all customizations.

---

## 🚨 **Before Any Update**

### **Step 1: Create Full Backup**
```powershell
# Run the backup script
cd C:\xampp\htdocs\manjaro\store
.\backup\scripts\backup-customizations.ps1
```

### **Step 2: Document Current State**
- Note current version
- List all customizations
- Test all functionality
- Take screenshots of admin panel

### **Step 3: Prepare Recovery Environment**
- Ensure fresh directory is clean
- Prepare database backup
- Ready restore scripts

---

## 🔄 **Update Process**

### **Phase 1: Pre-Update Preparation**

#### **1.1 Backup Everything**
```bash
# Database backup
mysqldump -u root -p manjaro_store > backup_before_update.sql

# File system backup
xcopy C:\xampp\htdocs\manjaro\store C:\backup\manjaro_store_backup /E /I /H /Y

# Run customizations backup
.\backup\scripts\backup-customizations.ps1
```

#### **1.2 Document Current Features**
- List all working features
- Note admin panel structure
- Document any custom code
- Test all payment gateways

### **Phase 2: Apply Update**

#### **2.1 Download New Version**
- Download latest Active E-commerce
- Extract to temporary folder
- Compare with current version

#### **2.2 Apply Update**
```bash
# Stop services
net stop mysql
net stop apache

# Backup current installation
xcopy C:\xampp\htdocs\manjaro\store C:\backup\manjaro_store_old /E /I /H /Y

# Apply update (replace files)
# Keep customizations folder separate
```

#### **2.3 Update Database**
```bash
# Import new database structure
mysql -u root -p manjaro_store < new_database_structure.sql

# Restore data from backup
mysql -u root -p manjaro_store < backup_before_update.sql
```

### **Phase 3: Restore Customizations**

#### **3.1 Restore Custom Code**
```powershell
# Use the restore script
.\backup\scripts\restore-customizations.ps1 -BackupDate "20250907_072700"
```

#### **3.2 Manual Restoration Steps**

**A. Restore Controllers:**
```bash
# Copy custom controllers
cp backup/customizations/app/Http/Controllers/Admin/AlibabaController.php app/Http/Controllers/Admin/
cp backup/customizations/app/Http/Controllers/GeoCurrencyController.php app/Http/Controllers/
```

**B. Restore Services:**
```bash
# Copy custom services
cp backup/customizations/app/Services/Alibaba*.php app/Services/
cp backup/customizations/app/Services/GeoLocationService.php app/Services/
```

**C. Restore Models:**
```bash
# Copy custom models
cp backup/customizations/app/Models/Alibaba*.php app/Models/
```

**D. Restore Views:**
```bash
# Copy custom views
cp -r backup/customizations/resources/views/backend/alibaba resources/views/backend/
cp -r backup/customizations/resources/views/backend/setup_configurations/geo_currency_settings resources/views/backend/setup_configurations/
```

**E. Restore Routes:**
```bash
# Add custom routes to admin.php
# (Manual process - copy from backup)
```

**F. Restore Menu:**
```bash
# Update admin sidebar
# (Manual process - merge from backup)
```

### **Phase 4: Post-Update Configuration**

#### **4.1 Clear Caches**
```bash
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear
```

#### **4.2 Run Migrations**
```bash
php artisan migrate
php artisan db:seed
```

#### **4.3 Update Permissions**
```bash
# Set file permissions
chmod -R 755 storage/
chmod -R 755 bootstrap/cache/
```

---

## 🧪 **Testing After Update**

### **Critical Tests:**
1. **Database Connection** - Verify MySQL connection
2. **Admin Panel Access** - Test all admin features
3. **Payment Gateways** - Test all payment methods
4. **Alibaba Integration** - Test import functionality
5. **Geo-Currency** - Test location-based currency
6. **Multi-language** - Test language switching
7. **Seller Features** - Test marketplace functionality

### **Test Checklist:**
- [ ] Admin dashboard loads
- [ ] All menu items visible
- [ ] Payment gateways working
- [ ] Alibaba integration accessible
- [ ] Geo-currency settings working
- [ ] Language switching functional
- [ ] Seller registration works
- [ ] Product import works
- [ ] Order processing works

---

## 🚨 **Troubleshooting Common Issues**

### **Issue 1: Database Connection Failed**
```bash
# Check MySQL service
net start mysql

# Test connection
mysql -u root -p

# Check .env file
# Verify database credentials
```

### **Issue 2: Admin Panel Not Loading**
```bash
# Clear all caches
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Check file permissions
chmod -R 755 storage/
chmod -R 755 bootstrap/cache/
```

### **Issue 3: Missing Features in Admin**
```bash
# Check if routes are registered
php artisan route:list | grep alibaba
php artisan route:list | grep geo

# Verify menu configuration
# Check admin_sidenav.blade.php
```

### **Issue 4: Payment Gateway Errors**
```bash
# Check payment gateway files
# Verify API credentials
# Test payment gateway configuration
```

---

## 📋 **Recovery Checklist**

### **Before Update:**
- [ ] Full system backup created
- [ ] Customizations documented
- [ ] Database backup created
- [ ] Test environment ready

### **During Update:**
- [ ] Services stopped
- [ ] Files backed up
- [ ] Update applied
- [ ] Database updated

### **After Update:**
- [ ] Customizations restored
- [ ] Caches cleared
- [ ] Migrations run
- [ ] Permissions set
- [ ] All tests passed

---

## 🔧 **Automated Recovery Script**

### **Quick Recovery Script:**
```powershell
# recovery-quick.ps1
Write-Host "🔄 Quick Recovery Starting..." -ForegroundColor Green

# Get latest backup
$latestBackup = Get-ChildItem "C:\xampp\htdocs\manjaro\store\backup\customizations" | Sort-Object LastWriteTime -Descending | Select-Object -First 1

if ($latestBackup) {
    Write-Host "📁 Using backup: $($latestBackup.Name)" -ForegroundColor Yellow
    .\backup\scripts\restore-customizations.ps1 -BackupDate $latestBackup.Name
} else {
    Write-Host "❌ No backup found!" -ForegroundColor Red
    exit 1
}

# Clear caches
Set-Location "C:\xampp\htdocs\manjaro\store"
& "C:\xampp\php\php.exe" artisan config:clear
& "C:\xampp\php\php.exe" artisan route:clear
& "C:\xampp\php\php.exe" artisan view:clear

Write-Host "✅ Quick recovery completed!" -ForegroundColor Green
```

---

## 📚 **Reference Materials**

### **Backup Locations:**
- Customizations: `store/backup/customizations/`
- Database: `store/backup/database/`
- Scripts: `store/backup/scripts/`

### **Key Files to Monitor:**
- `routes/admin.php` - Admin routes
- `resources/views/backend/inc/admin_sidenav.blade.php` - Admin menu
- `app/Http/Controllers/Admin/` - Admin controllers
- `app/Services/` - Custom services
- `app/Models/` - Custom models

### **Documentation:**
- `docs/development/current-state-assessment.md`
- `docs/development/team-handover.md`
- `docs/modules/` - Module-specific docs

---

## ⚠️ **Important Notes**

1. **Always test in development first**
2. **Keep multiple backups**
3. **Document every change**
4. **Test all functionality after restore**
5. **Have a rollback plan ready**

---

*Last Updated: September 7, 2025*
*Version: 1.0*
*Maintained by: Development Team*

