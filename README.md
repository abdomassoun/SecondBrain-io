# File Storage Service

A robust, enterprise-grade file storage service built with Laravel following Domain-Driven Design (DDD) principles. This service provides secure file upload, download, and management capabilities with JWT authentication, user-based access control, and comprehensive activity logging.

## 📋 Table of Contents

- [Features](#-features)
- [Architecture](#-architecture)
- [Requirements](#-requirements)
- [Installation](#-installation)
- [Configuration](#-configuration)
- [Authentication](#-authentication)
- [Access Control](#-access-control)
- [Postman Collection](#-postman-collection)
- [File Upload Methods](#-file-upload-methods)
- [Testing](#-testing)
- [Database Schema](#-database-schema)
- [Development Flow](#-development-flow)
- [Note](#-note)

## ✨ Features

### Core Features
- ✅ **File Upload/Download/Delete**: Complete file lifecycle management
- ✅ **JWT Authentication**: Secure token-based authentication using `tymon/jwt-auth`
- ✅ **User Registration & Login**: Full authentication flow with password reset
- ✅ **Ownership-based Access Control**: Users can only access their own files
- ✅ **File Metadata Tracking**: ID, name, size, MIME type, upload date, owner
- ✅ **Activity Logging**: Track all file operations (upload, download, delete) with Spatie Activity Log
- ✅ **Input Validation**: Comprehensive request validation for all endpoints
- ✅ **Error Handling**: Meaningful error messages with appropriate HTTP status codes
- ✅ **Unit & Feature Tests**: Extensive test coverage using Pest PHP

### Advanced Features
- 🚀 **Chunked File Upload**: Support for large files with resumable chunk-based uploads
- 🔒 **Idempotency Protection**: Prevents duplicate uploads of the same file
- 📁 **File Type Validation**: Whitelist-based MIME type validation
- 📄 **Pagination**: Efficient handling of large file lists
- 🎯 **File Filtering**: Filter files by owner and MIME type
- 📊 **Formatted File Sizes**: Human-readable file size formatting
- 🔐 **Rate Limiting**: Throttling for auth and API endpoints
- 🐳 **Docker Support**: Complete Docker Compose setup with PostgreSQL
- 📈 **Database Indexing**: Optimized database queries with proper indexing

## 🏗️ Architecture

This project follows **Domain-Driven Design (DDD)** principles with a clean, layered architecture:

```
app/
├── Application/          # Application services, handlers, DTOs
│   ├── Files/
│   │   ├── Commands/     # Command objects
│   │   ├── DTOs/         # Data Transfer Objects
│   │   ├── Handlers/     # Command/Query handlers
│   │   ├── Queries/      # Query objects
│   │   └── Services/     # Application services
│   └── Users/
├── Domain/              # Business logic and entities
│   ├── Files/
│   │   ├── Entities/    # Domain entities
│   │   ├── Repositories/ # Repository interfaces
│   │   └── ValueObjects/ # Value objects (MimeType, FileSize, FilePath)
│   ├── Shared/          # Shared domain logic
│   └── Users/
├── Infrastructure/      # External concerns
│   ├── Persistence/
│   │   └── Eloquent/    # Eloquent models and repositories
│   ├── EventListeners/
│   └── Providers/
└── Presentation/        # API layer
    ├── Http/
    │   ├── Controllers/ # API controllers
    │   ├── Requests/    # Form request validation
    │   └── Resources/   # API resources
    └── CLI/
```

### Key Design Patterns
- **Repository Pattern**: Abstract data access layer
- **CQRS**: Separate command and query responsibilities
- **Value Objects**: Encapsulate domain logic (MimeType, FileSize)
- **DTOs**: Transfer data between layers
- **Service Layer**: Orchestrate business operations

## 📦 Requirements

- PHP 8.2 or higher
- PostgreSQL 15+
- Composer
- Docker & Docker Compose (recommended)
- Node.js & npm (for asset compilation)

## 🚀 Installation

### Option 1: Docker (Recommended)

1. **Clone the repository**
   ```bash
   git clone https://github.com/abdomassoun/SecondBrain-io.git
   ```

2. **Start the services**
   ```bash
   docker-compose up -d
   ```
3. **Enter the application container (could also be `secondbrain-io_application_1`)**
   ```bash
   docker exec -ti secondbrain-io-application-1 bash
   ```
4. **Run the Deploy script**
   ```bash
   sh deploy.sh
   ```
## ⚙️ Configuration

### File Upload Limits

- **Single Upload**: 4MB (configurable in `UploadFileRequest.php`)
- **Chunk Upload**: Unlimited (chunks are merged server-side)
- **Chunk Size**: Configurable (default: 1MB in test script)

### Allowed File Types

Configured in `app/Domain/Files/ValueObjects/MimeType.php`:
- **Images**: JPEG, PNG, GIF, WebP, SVG
- **Documents**: PDF, Word, Excel, PowerPoint, Text, CSV
- **Archives**: ZIP, RAR, 7Z
- **Video**: MP4, MPEG, MOV, AVI
- **Audio**: MP3, WAV, OGG

## 🔐 Authentication

### JWT (JSON Web Tokens)

This service uses JWT for stateless authentication:

1. **Register** or **Login** to receive a JWT token
2. Include the token in the `Authorization` header for all protected endpoints:
   ```
   Authorization: Bearer {your-jwt-token}
   ```
3. Tokens expire after 60 minutes (configurable via `JWT_TTL`)
4. Use the refresh endpoint to get a new token without re-authenticating

### Security Features

- Passwords are hashed using bcrypt
- JWT tokens are signed and verified
- Rate limiting on authentication endpoints
- Protected routes require valid authentication

## 🔒 Access Control

### Ownership-based Authorization

The system implements strict ownership-based access control:

- Users can **only view, download, or delete** files they uploaded
- Attempts to access other users' files return `403 Forbidden`
- File ownership is tracked via `owner_uuid` field
- Authorization checks are performed at the service layer

## 📮 Postman Collection

A complete Postman collection is included for testing all API endpoints:

**Import the collection:**
```bash
backend.postman_collection.json
```

**Environment Variables:**
- Set `{{base_url}}` to `http://localhost:8000/api/v1`

## 📤 File Upload Methods

### 1. Simple Upload (< 4MB)

Best for small files:

```bash
curl -X POST http://localhost:8000/api/v1/files/upload \
  -H "Authorization: Bearer {token}" \
  -F "file=@/path/to/file.pdf"
```

### 2. Chunked Upload (Large Files)

For files of any size with resumable uploads:

```bash
# Use the provided test script
chmod +x test-chunked-upload.sh
./test-chunked-upload.sh /path/to/large-file.mp4
```

**Chunked Upload Process:**
1. File is split into chunks on the client
2. Each chunk is uploaded with `upload-chunk` endpoint
3. Server tracks progress in `file_chunks` table
4. Once all chunks are uploaded, call `complete-upload`
5. Server merges chunks into final file
6. Chunks are cleaned up automatically

**Benefits:**
- Support for unlimited file sizes
- Resumable uploads
- Progress tracking
- Network interruption tolerance

## 🧪 Testing

### Run All Tests

```bash
# Using Docker
docker-compose exec application php artisan test

# Local
php artisan test
```

### Run Specific Test Suites

```bash
# Application tests only
php artisan test --testsuite=Application

# Domain tests only
php artisan test --testsuite=Domain

# Example: test the file module in the Application layer
php artisan test --filter files --testsuite=Application
```

## 🗄️ Database Schema
For detailed information, see the `database-schema.dbml` file.

## 🛠️ Development Flow

To make the workflow faster and more organized, I follow this process:

**🧱 DBML Schema (Structure & Model) → ⚙️ Module Implementation (Controllers / Services / Business Logic) → 📬 AI Postman Update (API Docs & Testing)**
### ✅ Why this approach?

- Clear architecture before coding  
- Faster implementation with structured thinking  
- AI-friendly reference for accurate generation  
- Automatically updated API documentation  
- Better deployment readiness  

This way, I save time, keep the project well-documented, and ensure smooth deployment.

## 📝 Note

I know the task was asking for a normal file upload, but I added the chunk upload part because it is closer to what happens in real-world cases, along with other improvements like idempotency.

Chunked uploads are well-suited for small to medium-sized files. For very large files, a protocol like TUS would be more appropriate. However, I intentionally did not implement TUS here to avoid unnecessary complexity and over-engineering beyond the scope of the exercise.
