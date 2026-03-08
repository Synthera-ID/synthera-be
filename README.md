# Synthera Backend API

Backend API untuk sistem membership dan platform pembelajaran menggunakan Laravel.

---

## Base URL

http://127.0.0.1:8000/api

---

## Endpoints 

### Get Courses

## Endpoints

GET /courses  
GET /plans  
GET /memberships  
GET /transactions  
GET /payments

Response

```json
[
  {
    "id": 1,
    "category_id": 1,
    "title": "Laravel Basic",
    "slug": "laravel-basic",
    "description": "Belajar laravel dari dasar",
    "duration": 120
  }
]

Get Plans

Endpoint

GET /plans

Response

[
  {
    "id": 1,
    "name": "Basic",
    "description": "Paket Basic",
    "price": 50000,
    "duration_days": 30
  }
]

Get Memberships

Endpoint

GET /memberships

Response

[
  {
    "id": 1,
    "user_id": 1,
    "plan_id": 1,
    "status": "active"
  }
]

Get Transactions

Endpoint

GET /transactions

Response

[
  {
    "id": 1,
    "invoice_code": "INV001",
    "user_id": 1,
    "plan_id": 1,
    "amount": 50000,
    "status": "paid"
  }
]

Get Payments

Endpoint

GET /payments

Response

[
  {
    "id": 1,
    "transaction_id": 1,
    "user_id": 1,
    "payment_method": "bank_transfer",
    "payment_gateway": "midtrans",
    "amount": 50000,
    "status": "success"
  }
]

Tech Stack
	•	Laravel
	•	REST API
	•	Postman
	•	MySQL / SQLite