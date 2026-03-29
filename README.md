# AlMadar Bank API

AlMadar Bank API is a robust, secure, and professional banking backend built with **Laravel**. It provides a complete suite of services for modern digital banking, including account management, atomic transfers, administrative controls, and automated financial cycles.

## Key Features

### Secure Authentication
*   **JWT Protected**: Industry-standard JSON Web Token authentication for all secure endpoints.
*   **Role-Based Access**: Specialized permissions for **Admin** and **Client** roles.

### Account Management
*   **Diverse Account Types**: Support for `COURANT`, `EPARGNE`, and `MINEUR` (Minor) accounts.
*   **Guardianship**: specialized logic for minor accounts requiring legal guardians.
*   **Joint Accounts**: Multi-owner support for shared account management.

### Atomic Transfers
*   **Safe Transactions**: Using database transactions to ensure total data integrity.
*   **Business Rules**: Automated checks for insufficient balance, daily limits, and account status (Blocked/Active).

### Administrative Controls
*   **Account Life-cycle**: Admins can block, unblock, and finalize account closures.
*   **Safety Checks**: Closures require consent from all co-holders and a zeroed-out balance.

### Automated Tasks
*   **Monthly Fees**: Automated maintenance fee collection for current accounts.
*   **Interest Calculation**: Dynamic monthly interest application for savings and minor accounts.

## Tech Stack
*   **Framework**: Laravel 11+
*   **Language**: PHP 8.2+
*   **Authentication**: JWT (tymon/jwt-auth)
*   **Database**: MySQL

## Installation & Setup

1.  **Clone and Install**:
    ```bash
    composer install
    ```

2.  **Environment Configuration**:
    ```bash
    cp .env.example .env
    php artisan key:generate
    php artisan jwt:secret
    ```

3.  **Database Migration & Seeding**:
    ```bash
    php artisan migrate:fresh --seed
    ```

## Background Operations
Register these in your server's crontab for automated processing:
```bash
# Charge monthly fees
php artisan fees:charge-monthly

# Apply monthly interest
php artisan interest:apply-monthly
```

## Testing Credentials (from Seeders)
| Role | Email | Password |
| :--- | :--- | :--- |
| **Admin** | admin@almadar.ma | password123 |
| **Guardian** | guardian@example.com | password123 |
| **Minor** | minor@example.com | password123 |
| **Client** | client@example.com | password123 |

---
