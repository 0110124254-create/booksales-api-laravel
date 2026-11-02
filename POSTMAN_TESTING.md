# Postman API Testing

Base URL:

```text
http://127.0.0.1:8000/api
```

Import [`postman/Booksales API.postman_collection.json`](./postman/Booksales%20API.postman_collection.json)
in Postman. Run the public API smoke tests directly. They verify that guests
can list and view Genre and Author records, and that protected requests reject
guests. Before running either admin folder, set the collection variables
`adminEmail` and `adminPassword` to a local administrator's credentials.

- **Admin Genre dan Author CRUD** logs in, checks that unauthenticated and
  invalid create requests are rejected, verifies that show/update/delete
  requests for missing Genre and Author records return `404`, creates
  uniquely named records, reads and updates each one, verifies the updated
  values, deletes the test records, verifies they return `404`, and logs out.
- **Admin book CRUD** creates a uniquely named test book, reads and updates
  it, deletes it, and confirms the deleted book returns `404`.

The Genre and Author tests remove their temporary records when all requests
complete successfully. If you stop the run before the DELETE requests, remove
the uniquely named `Postman Genre ...` and `Postman Author ...` test records
before rerunning.

Send `Accept: application/json` with requests. JSON request bodies use
`Content-Type: application/json`. Successful and failed API responses use a
`success`, `message`, and `data` envelope; validation errors also include
`errors`.

## Transaction access

Customers must log in through `POST /api/login` and use the returned
`data.token` as a Bearer token. API login accepts existing customer and admin
accounts. Customer accounts must be provisioned locally; public registration
is disabled.

| Access | Methods | Routes |
|---|---|---|
| Authenticated customer only | Create | `POST /api/sales` or `POST /api/checkout` |
| Authenticated owning customer only | Read one, update | `GET /api/sales/{reference}`, `PUT/PATCH /api/sales/{reference}` |
| Admin only | Read all, delete | `GET /api/admin/sales`, `DELETE /api/admin/sales/{id}` |
| Admin only | Confirm or cancel payment | `POST /api/admin/sales/{id}/paid`, `POST /api/admin/sales/{id}/cancel` |

Guests receive `401 Unauthorized`; authenticated users with the wrong role
receive `403 Forbidden`. A customer requesting another customer's transaction
receives `404 Not Found`. Only pending transactions can be updated. Deleting a
pending transaction as an admin restores its reserved book stock.

## Genre and Author access

| Access | Methods | Routes |
|---|---|---|
| Public, no login required | GET list and detail | `/api/genres`, `/api/genres/{id}`, `/api/authors`, `/api/authors/{id}` |
| Admin only | POST, PUT/PATCH, DELETE | `/api/genres`, `/api/genres/{id}`, `/api/authors`, `/api/authors/{id}` |

Unauthenticated write requests return `401 Unauthorized`. Authenticated
non-admin write requests return `403 Forbidden`.

## Public resources

| Method | URL | Description |
|---|---|---|
| GET | `http://127.0.0.1:8000/api` | List books (API root) |
| GET | `http://127.0.0.1:8000/api/books` | List all books |
| GET | `http://127.0.0.1:8000/api/books/{id}` | Get a book |
| GET | `http://127.0.0.1:8000/api/genres` | List genres |
| GET | `http://127.0.0.1:8000/api/genres/{id}` | Get a genre |
| GET | `http://127.0.0.1:8000/api/authors` | List authors |
| GET | `http://127.0.0.1:8000/api/authors/{id}` | Get an author |

## Admin authentication and resources

Create an admin token with `POST /api/login`, then configure Postman's
**Bearer Token** authorization with the returned `data.token`.

| Method | URL | Description |
|---|---|---|
| GET | `http://127.0.0.1:8000/api/login` | Login endpoint information |
| POST | `http://127.0.0.1:8000/api/login` | Create a customer or admin Sanctum token |
| POST | `http://127.0.0.1:8000/api/logout` | Revoke the current token |
| GET | `http://127.0.0.1:8000/api/user` | Get the authenticated user |
| POST | `http://127.0.0.1:8000/api/books` | Create a book |
| PUT/PATCH | `http://127.0.0.1:8000/api/books/{id}` | Update a book |
| DELETE | `http://127.0.0.1:8000/api/books/{id}` | Delete a book |
| POST | `http://127.0.0.1:8000/api/genres` | Create a genre |
| PUT/PATCH | `http://127.0.0.1:8000/api/genres/{id}` | Update a genre |
| DELETE | `http://127.0.0.1:8000/api/genres/{id}` | Delete an unused genre |
| POST | `http://127.0.0.1:8000/api/authors` | Create an author |
| PUT/PATCH | `http://127.0.0.1:8000/api/authors/{id}` | Update an author |
| DELETE | `http://127.0.0.1:8000/api/authors/{id}` | Delete an unused author |
| GET | `http://127.0.0.1:8000/api/admin/books` | List books for administration |
| POST | `http://127.0.0.1:8000/api/admin/books` | Create an admin book |
| PUT/PATCH | `http://127.0.0.1:8000/api/admin/books/{id}` | Update an admin book |
| DELETE | `http://127.0.0.1:8000/api/admin/books/{id}` | Delete an admin book |
| GET | `http://127.0.0.1:8000/api/admin/sales` | List orders |
| DELETE | `http://127.0.0.1:8000/api/admin/sales/{id}` | Delete an order |
| POST | `http://127.0.0.1:8000/api/admin/sales/{id}/paid` | Confirm payment |
| POST | `http://127.0.0.1:8000/api/admin/sales/{id}/cancel` | Cancel a pending order |
| GET | `http://127.0.0.1:8000/api/admin/reports` | Get the sales report |

## Example request bodies

### Login

```json
{
  "email": "admin@example.com",
  "password": "your-password"
}
```

### Create or update a book

`author_id` can be supplied to use an existing author; the stored `author`
value is taken from that author. Otherwise, supply `author` directly.
PUT requests use a full resource body. PATCH requests may include only the
fields being changed.

```json
{
  "title": "Buku Contoh",
  "author_id": 1,
  "genre": "Fiksi",
  "description": "Deskripsi buku.",
  "price": 50000,
  "stock": 10,
  "is_active": true
}
```

### Create or update a genre

```json
{
  "name": "Teknologi",
  "description": "Buku tentang teknologi dan ilmu terapan."
}
```

### Create or update an author

```json
{
  "name": "Nama Penulis",
  "nationality": "Indonesia",
  "description": "Biografi singkat penulis."
}
```

### Create or update a transaction

Use an existing customer account to log in and attach its token as
`Authorization: Bearer <token>` for transaction create, show, and update
requests. The compatible `/api/checkout` route also requires customer
authentication. Only pending transactions can be updated.

```json
{
  "customer_name": "Pelanggan Contoh",
  "customer_email": "pelanggan@example.com",
  "customer_phone": "08123456789",
  "payment_method": "bank_transfer",
  "items": [
    {
      "book_id": 1,
      "quantity": 2
    }
  ]
}
```

## Example responses

### List books — `200 OK`

```json
{
  "success": true,
  "message": "Get All Resource",
  "data": [
    {
      "id": 1,
      "title": "Buku Contoh",
      "author_id": 1,
      "author": "Nama Penulis",
      "genre": "Fiksi",
      "description": "Deskripsi buku.",
      "price": 50000,
      "stock": 10,
      "is_active": true
    }
  ]
}
```

### Create resource — `201 Created`

```json
{
  "success": true,
  "message": "Resource created successfully",
  "data": {
    "id": 6,
    "name": "Genre Baru",
    "description": "Deskripsi genre."
  }
}
```

### Not found — `404 Not Found`

```json
{
  "success": false,
  "message": "Resource not found",
  "data": null
}
```

### Validation failure — `422 Unprocessable Content`

```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "title": [
      "The title field is required."
    ]
  }
}
```

The report endpoint accepts optional `start_date` and `end_date` query
parameters in `YYYY-MM-DD` format, for example:

```text
http://127.0.0.1:8000/api/admin/reports?start_date=2026-10-01&end_date=2026-10-31
```
