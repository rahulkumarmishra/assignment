# Drug Search and Tracker API

API for searching drug information and tracking user medications.

## API Endpoints

### Authentication

**Register User**
- `POST /api/register`
- Payload: `name`, `email`, `password`

**Login User**
- `POST /api/login`
- Payload: `email`, `password`
- Returns: `token` for authenticated requests

### Public Endpoints

**Search Drugs**
- `GET /api/drugs/search?drug_name={name}`
- Returns: Array of drug results with name, rxcui, base names, and dosage forms

### Authenticated Endpoints (require Authorization header with Bearer token)

**Get User Medications**
- `GET /api/user/drugs`
- Returns: List of user's tracked drugs

**Add Drug to User Medications**
- `POST /api/user/drugs`
- Payload: `rxcui`

**Remove Drug from User Medications**
- `DELETE /api/user/drugs/{rxcui}`

## Setup

1. Clone repository
2. Run `composer install`
3. Create `.env` file and configure database
4. Run migrations: `php artisan migrate`
5. Start server: `php artisan serve`

## Testing

Run tests with: `php artisan test`