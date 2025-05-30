# 🎓 INTI Student Registration & Login System

一个完整的学生注册和登录系统，包含邮箱验证功能，专为 INTI 大学学生设计。

## 📋 **系统概览**

### 🎯 **主要功能**
- ✅ 学生注册（仅限 INTI 邮箱）
- ✅ 邮箱 OTP 验证
- ✅ 用户登录/登出
- ✅ 个人仪表板
- ✅ PHPMailer 邮件系统
- ✅ 响应式设计

### 🛠️ **技术栈**
- **后端**: PHP 8.x, MySQL 8.x
- **前端**: HTML5, CSS3, JavaScript, Bootstrap 5
- **邮件**: PHPMailer
- **数据库**: MySQL with PDO
- **依赖管理**: Composer

---

## 📁 **项目结构**

```
/var/www/html/
├── 📱 **核心页面**
│   ├── register.php              # 学生注册页面
│   ├── process_register.php      # 注册数据处理
│   ├── login.php                 # 登录页面
│   ├── login_handler.php         # 登录处理
│   ├── general.php               # 用户仪表板
│   ├── otp-verify.php           # OTP 验证页面
│   └── logout.php               # 登出处理
│
├── ⚙️ **配置文件**
│   ├── db.php                   # 数据库连接配置
│   ├── mail_config.php          # 邮件服务器配置
│   ├── function.php             # 通用函数库
│   └── password-validation.php  # 密码验证函数
│
├── 📧 **邮件系统**
│   ├── Mailer.php              # PHPMailer 邮件类
│   ├── composer.json           # Composer 依赖
│   ├── composer.lock           # 依赖锁定文件
│   └── vendor/                 # Composer 包
│
├── 🗄️ **数据库脚本**
│   ├── create_users_table.sql   # 用户表结构
│   ├── create_otp_table.sql     # OTP 表结构
│   └── create_test_user.php     # 测试用户创建
│
├── 🎨 **前端资源**
│   ├── css/
│   │   ├── style.css           # 主样式文件
│   │   ├── login.css           # 登录页面样式
│   │   └── otp-verify.css      # OTP 验证样式
│   ├── js/
│   │   ├── validations.js      # 表单验证
│   │   └── countdown.js        # OTP 倒计时
│   └── images/
│       ├── logo/               # LOGO 图片
│       ├── place/              # 场地图片
│       └── assets/             # 其他资源
│
└── 📋 **其他文件**
    ├── check.php              # Google OAuth (已禁用)
    ├── login copy.php         # 登录页面备份
    └── README.md              # 项目文档
```

---

## 🗄️ **数据库结构**

### 数据库信息
- **数据库名**: `reservation_system`
- **用户名**: `webapp`
- **密码**: `webapp123`

### 表结构

#### 📋 **users 表**
```sql
CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_verified TINYINT(1) DEFAULT 0,
    INDEX idx_email (email),
    INDEX idx_verified (is_verified)
);
```

#### 🔢 **user_otp 表**
```sql
CREATE TABLE user_otp (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    otp_code VARCHAR(6) NOT NULL,
    expires_at DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_expires_at (expires_at)
);
```

---

## 🚀 **安装指南**

### 1. **环境要求**
```bash
- PHP >= 8.0
- MySQL >= 8.0
- Apache/Nginx
- Composer
- 启用的 PHP 扩展: pdo_mysql, curl, openssl
```

### 2. **数据库设置**
```bash
# 创建数据库
sudo mysql -u root
CREATE DATABASE reservation_system;

# 创建数据库用户
CREATE USER 'webapp'@'localhost' IDENTIFIED BY 'webapp123';
GRANT ALL PRIVILEGES ON reservation_system.* TO 'webapp'@'localhost';
FLUSH PRIVILEGES;

# 导入表结构
sudo mysql -u root < create_users_table.sql
sudo mysql -u root < create_otp_table.sql
```

### 3. **安装依赖**
```bash
# 安装 Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# 安装 PHPMailer
composer install
```

### 4. **邮件配置**
编辑 `mail_config.php` 配置 SMTP 设置：
```php
// Gmail 示例
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'your-email@gmail.com');
define('SMTP_PASSWORD', 'your-app-password');
```

### 5. **权限设置**
```bash
sudo chown -R www-data:www-data /var/www/html
sudo chmod -R 755 /var/www/html
sudo mkdir -p /var/www/html/var/log
sudo chmod 777 /var/www/html/var/log
```

---

## 📖 **使用说明**

### 🎯 **注册流程**

#### 1. **学生注册** (`/register.php`)
- 填写用户名、INTI 邮箱、密码
- 系统验证邮箱格式（必须是 `@student.newinti.edu.my`）
- 实时密码强度验证
- 提交后跳转到 OTP 验证

#### 2. **邮箱验证** (`/otp-verify.php`)
- 点击 "Send OTP" 发送验证码
- 输入 6 位数验证码
- 验证成功后账户激活

#### 3. **登录系统** (`/login.php`)
- 使用邮箱和密码登录
- 支持"记住我"功能
- 登录成功后进入仪表板

#### 4. **用户仪表板** (`/general.php`)
- 查看个人信息
- 预订管理（功能待开发）
- 账户设置

### 🔧 **管理功能**

#### 创建测试用户
```bash
php create_test_user.php
```

#### 检查邮件发送
```bash
# 在浏览器中访问
http://your-domain/test_mail.php
```

---

## 🛡️ **安全特性**

### 🔐 **密码安全**
- BCrypt 哈希加密
- 密码强度验证（8字符+数字）
- 防止密码明文存储

### 📧 **邮箱验证**
- OTP 验证码有效期 15 分钟
- 防止重复验证
- 邮箱格式严格验证

### 🚫 **防护措施**
- SQL 注入防护（PDO 预处理）
- XSS 防护（htmlspecialchars）
- CSRF 防护（会话验证）
- 输入数据清理

---

## ⚠️ **已知问题**

### 🔧 **需要修复的问题**

1. **CSS 文件路径**
   - 检查 `css/style.css` 等文件是否存在
   - 确保路径正确

2. **图片资源**
   - 确认 `images/logo/inti_logo.png` 存在
   - 检查其他引用的图片文件

3. **日志目录**
   - 创建 `/var/www/html/var/log/` 目录
   - 设置正确的写入权限

4. **数据库密码**
   - 考虑使用环境变量存储敏感信息
   - 避免硬编码密码

---

## 📊 **系统状态检查**

### ✅ **正常工作的功能**
- [x] 学生注册页面 (HTTP 200)
- [x] 登录页面 (HTTP 200)
- [x] 邮箱验证逻辑
- [x] OTP 发送功能
- [x] 数据库连接
- [x] 用户认证系统
- [x] PHPMailer 集成

### 🔄 **重定向页面**
- [x] 用户仪表板 (HTTP 302 - 正常，需要登录)
- [x] OTP 验证页面 (HTTP 302 - 正常，需要会话)

---

## 🎯 **测试指南**

### 📝 **手动测试流程**

1. **注册测试**
   ```
   访问: http://your-domain/register.php
   邮箱: test@student.newinti.edu.my
   用户名: testuser
   密码: Test123456
   ```

2. **邮箱验证测试**
   ```
   点击 "Send OTP"
   检查邮箱收到验证码
   输入 6 位数字验证
   ```

3. **登录测试**
   ```
   访问: http://your-domain/login.php
   使用注册的账户登录
   验证重定向到仪表板
   ```

### 🔧 **技术测试**
```bash
# 测试数据库连接
php -r "include 'db.php'; echo 'Database connected successfully';"

# 测试页面状态
curl -I http://localhost/register.php
curl -I http://localhost/login.php

# 检查 Composer 依赖
composer install --dry-run
```

---

## 📞 **支持信息**

### 🐛 **问题报告**
如发现问题，请检查：
1. Apache/Nginx 错误日志
2. PHP 错误日志
3. 应用程序日志 (`/var/www/html/var/log/`)

### 🔧 **常见问题**

**Q: 邮件发送失败？**
A: 检查 `mail_config.php` 中的 SMTP 设置，确保使用正确的应用密码。

**Q: 数据库连接失败？**
A: 验证 `db.php` 中的数据库凭据，确保 MySQL 服务运行正常。

**Q: 页面显示 500 错误？**
A: 检查 PHP 错误日志，确保所有依赖文件存在。

---

## 📈 **未来开发计划**

### 🚀 **待开发功能**
- [ ] 房间预订系统
- [ ] 日历集成
- [ ] 通知系统
- [ ] 管理员后台
- [ ] 多语言支持
- [ ] API 接口

### 🔧 **系统优化**
- [ ] 缓存系统
- [ ] 性能监控
- [ ] 自动备份
- [ ] SSL 证书配置

---

**📝 版本**: 0.1.0
**📅 更新时间**: 2025年5月  
**👨‍💻 开发者**: zhiyang