# ITE311-RANISES - CodeIgniter 4 Project

## Setup Instructions

### Prerequisites
- PHP 8.1 or higher
- Composer
- XAMPP (for database if needed)

### Installation
1. Clone or download this project
2. Run `composer install` to install dependencies
3. Copy `env` to `.env` and configure your settings

### Quick Start

#### Option 1: Using Spark CLI
```bash
# Set environment to development
php spark env development

# Generate encryption key
php spark key:generate

# Start development server
php spark serve --host=127.0.0.1 --port=8080
```

#### Option 2: Using Batch File (Windows)
Double-click `start-server.bat`

#### Option 3: Using PowerShell Script
```powershell
.\start-server.ps1
```

### Access Your Application
Open your browser and go to: http://127.0.0.1:8080

### Available Spark Commands
- `php spark list` - List all available commands
- `php spark routes` - Show all routes
- `php spark make:controller [name]` - Create new controller
- `php spark make:model [name]` - Create new model
- `php spark make:migration [name]` - Create new migration

### Project Structure
- `app/Controllers/` - Controllers
- `app/Models/` - Models
- `app/Views/` - View files
- `app/Config/` - Configuration files
- `writable/` - Logs, cache, and uploads

### Current Features
- Bootstrap 5 integration
- Responsive navigation
- Basic template structure
- Development server ready

## Troubleshooting

### Spark CLI Issues
If you get errors with Spark, make sure:
1. PHP is in your system PATH
2. You're in the project root directory
3. All dependencies are installed

### Server Issues
- Make sure port 8080 is not in use
- Check if PHP is properly installed
- Verify all file permissions are correct

## Development Tips
- Use `php spark serve` for development
- Check logs in `writable/logs/`
- Use `php spark env development` for debugging
- Clear cache with `php spark cache:clear` if needed
