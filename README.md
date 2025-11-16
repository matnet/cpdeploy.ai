# cpdeploy.ai

cpdeploy.ai is a **self-hosted AI-powered cPanel deployment assistant**. It turns natural-language instructions into real server actions, using a secure split-architecture design.

## 🚀 Introduction

cpdeploy.ai is a PHP application that bridges your ideas and cPanel execution.
Instead of manually creating databases, files, users, or subdomains, you simply **describe what you want**, and the system will plan and execute it securely.

### Example Prompt

“Create a new blog project in `/public_html/myblog`.  
Set up a subdomain `blog.mydomain.com`, create a database `myblog_db` with a user `myblog_user`, and then generate an `index.php` file.”

The **AI Architect** generates a JSON plan.  
The **Secure Executor** runs it on your server.

## 🏛️ Architecture Overview

cpdeploy.ai uses a **3-component split-brain model** for maximum security.

### 1. Frontend (index.php) – “The Terminal”
Handles user input only. Sends the prompt + server ID to backend.

### 2. AI Architect (ai_generator.php) – “The Brain”
• Takes natural language  
• Generates JSON action plan  
• Calls GPT-4o  
• Never touches credentials  

### 3. Secure Executor (cpanel_executor.php) – “The Hands”
• Loads + decrypts credentials  
• Executes via cPanel UAPI/API2  
• Fully isolated from the AI layer  

Credentials and AI never interact directly.

## ✨ Features

### AI-Powered
• Converts natural language into multi-step plans  
• Uses GPT-4o for reasoning  

### cPanel Automation
• Files & folders  
• MySQL DB & users  
• Subdomains  
• Email accounts  
• Git deployments  

### Security
• AES-256-GCM token encryption  
• Prepared statements  
• Credential isolation  
• Multi-server support  

### Self-Hosted
• You own the data  
• Invitation code registration  

## ⚠️ Warning — Highly Experimental

• Wrong prompt may delete files/databases  
• Do NOT use on production without backups  
• Review generated plans manually  
• Protect access strongly  
• Ensure `local_env.php` & `db.php` stay private  

## 💾 Installation

### Clone

```bash
git clone https://github.com/your-username/cpdeploy.ai.git
cd cpdeploy.ai
```

### Copy Config Files

```bash
cp db.example.php db.php
cp local_env.example.php local_env.php
```

### Configure db.php

```php
define('DB_USER', 'your_cpdeploy_db_user');
define('DB_PASS', 'your_cpdeploy_db_password');
define('DB_NAME', 'your_cpdeploy_db_name');
```

### Configure local_env.php

```php
putenv('LAKSANA_SECRET_KEY=YOUR_LONG_SECRET_KEY');
putenv('OPENAI_API_KEY=sk-YOUR_API_KEY');
```

### Database Tables

```sql
CREATE TABLE `laksana_users` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `password_hash` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;

CREATE TABLE `cpanel_accounts` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `user_id` INT NOT NULL,
  `display_name` VARCHAR(100) NOT NULL,
  `host` VARCHAR(255) NOT NULL,
  `cpanel_user` VARCHAR(100) NOT NULL,
  `api_token` TEXT NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;

CREATE TABLE `invitation_codes` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `code` VARCHAR(50) NOT NULL UNIQUE,
  `is_used` TINYINT(1) NOT NULL DEFAULT 0,
  `used_by_user_id` INT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;
```

### Add Invitation Code

```sql
INSERT INTO invitation_codes (`code`) VALUES ('CPD-BETA-2025');
```

### Deploy

Upload files to your server, then visit:

```
https://your-domain.com/cpdeploy/register.php
```

Register and add your first cPanel server.

## 💡 Usage

1. Login  
2. Go to Manage Servers  
3. Add server details  
4. Go to main console  
5. Select server  
6. Type natural-language prompt  
7. Execute  

## 🤝 Contributing

PRs welcome. Open issue for major changes.

## 📜 License

MIT License — see LICENSE file.
