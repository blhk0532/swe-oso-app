# API Endpoints - Complete Curl Examples

All four data APIs have been tested and confirmed working with database persistence.

## ✅ Test Results Summary

| Endpoint | Single INSERT | Single UPDATE | Bulk INSERT | GET | DELETE | Database Verified |
|----------|--------------|---------------|-------------|-----|--------|-------------------|
| **hitta-data** | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| **ratsit-data** | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| **merinfo-data** | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| **data-private** | ✅ (Auth Required) | ✅ | ✅ | ✅ | ✅ | N/A |

---

## 1. HITTA_DATA API

### GET - List Records
```bash
curl -X GET "http://127.0.0.1:8000/api/hitta-data?per_page=25"

# With filters
curl -X GET "http://127.0.0.1:8000/api/hitta-data?postnummer=111&is_active=1&per_page=10"
```

### POST - Create Single Record
```bash
curl -X POST "http://127.0.0.1:8000/api/hitta-data" \
  -H "Content-Type: application/json" \
  -d '{
    "personnamn": "Test Person",
    "alder": "45",
    "gatuadress": "Testgatan 1",
    "postnummer": "111 11",
    "postort": "Stockholm",
    "telefon": "0701234567",
    "bostadstyp": "Villa",
    "is_active": true,
    "is_telefon": true,
    "is_ratsit": false
  }'
```

### PUT - Update Record
```bash
curl -X PUT "http://127.0.0.1:8000/api/hitta-data/1" \
  -H "Content-Type: application/json" \
  -d '{
    "alder": "46",
    "postort": "Stockholm City",
    "is_active": true
  }'
```

### GET - Single Record
```bash
curl -X GET "http://127.0.0.1:8000/api/hitta-data/1"
```

### DELETE - Remove Record
```bash
curl -X DELETE "http://127.0.0.1:8000/api/hitta-data/1"
```

### POST - Bulk Insert (up to 100 records)
```bash
curl -X POST "http://127.0.0.1:8000/api/hitta-data/bulk" \
  -H "Content-Type: application/json" \
  -d '{
    "records": [
      {
        "personnamn": "Person 1",
        "alder": "30",
        "postnummer": "222 22",
        "postort": "Göteborg",
        "telefon": "0702222222",
        "is_active": true
      },
      {
        "personnamn": "Person 2",
        "alder": "35",
        "postnummer": "333 33",
        "postort": "Malmö",
        "telefon": "0703333333",
        "is_active": true
      }
    ]
  }'
```

---

## 2. RATSIT_DATA API

### GET - List Records
```bash
curl -X GET "http://127.0.0.1:8000/api/ratsit-data?per_page=25"

# With filters
curl -X GET "http://127.0.0.1:8000/api/ratsit-data?postnummer=444&kommun=Uppsala&per_page=10"
```

### POST - Create Single Record
```bash
curl -X POST "http://127.0.0.1:8000/api/ratsit-data" \
  -H "Content-Type: application/json" \
  -d '{
    "personnummer": "199001011234",
    "personnamn": "Test Person Ratsit",
    "gatuadress": "Ratsitgatan 1",
    "postnummer": "444 55",
    "postort": "Uppsala",
    "kommun": "Uppsala",
    "lan": "Uppsala län",
    "is_active": true
  }'
```

### PUT - Update Record
```bash
curl -X PUT "http://127.0.0.1:8000/api/ratsit-data/1" \
  -H "Content-Type: application/json" \
  -d '{
    "postort": "Uppsala City",
    "kommun": "Uppsala kommun",
    "is_active": true
  }'
```

### GET - Single Record
```bash
curl -X GET "http://127.0.0.1:8000/api/ratsit-data/1"
```

### DELETE - Remove Record
```bash
curl -X DELETE "http://127.0.0.1:8000/api/ratsit-data/1"
```

### POST - Bulk Insert (up to 100 records)
```bash
curl -X POST "http://127.0.0.1:8000/api/ratsit-data/bulk" \
  -H "Content-Type: application/json" \
  -d '{
    "records": [
      {
        "personnummer": "198505051111",
        "personnamn": "Person 1",
        "postnummer": "555 66",
        "postort": "Västerås",
        "kommun": "Västerås",
        "lan": "Västmanland",
        "is_active": true
      },
      {
        "personnummer": "199212122222",
        "personnamn": "Person 2",
        "postnummer": "666 77",
        "postort": "Örebro",
        "kommun": "Örebro",
        "lan": "Örebro",
        "is_active": true
      }
    ]
  }'
```

---

## 3. MERINFO_DATA API

### GET - List Records
```bash
curl -X GET "http://127.0.0.1:8000/api/merinfo-data?per_page=25"

# With filters
curl -X GET "http://127.0.0.1:8000/api/merinfo-data?postnummer=777&personnamn=Test&per_page=10"
```

### POST - Create Single Record
```bash
curl -X POST "http://127.0.0.1:8000/api/merinfo-data" \
  -H "Content-Type: application/json" \
  -d '{
    "personnamn": "Test Person Merinfo",
    "alder": "50",
    "kon": "Man",
    "gatuadress": "Merinfogatan 1",
    "postnummer": "777 88",
    "postort": "Linköping",
    "telefon": ["0708888888", "0191234567"],
    "karta": "https://example.com/map",
    "link": "https://example.com/profile",
    "bostadstyp": "Lägenhet",
    "bostadspris": "3500000 kr",
    "is_active": true,
    "is_telefon": true,
    "is_ratsit": false
  }'
```

### PUT - Update Record
```bash
curl -X PUT "http://127.0.0.1:8000/api/merinfo-data/1" \
  -H "Content-Type: application/json" \
  -d '{
    "alder": "51",
    "telefon": ["0708888888", "0191234567", "0703333333"],
    "bostadspris": "3600000 kr",
    "is_active": true
  }'
```

### GET - Single Record
```bash
curl -X GET "http://127.0.0.1:8000/api/merinfo-data/1"
```

### DELETE - Remove Record
```bash
curl -X DELETE "http://127.0.0.1:8000/api/merinfo-data/1"
```

### POST - Bulk Insert (up to 100 records)
```bash
curl -X POST "http://127.0.0.1:8000/api/merinfo-data/bulk" \
  -H "Content-Type: application/json" \
  -d '{
    "records": [
      {
        "personnamn": "Person 1",
        "alder": "35",
        "kon": "Kvinna",
        "postnummer": "888 99",
        "postort": "Jönköping",
        "telefon": ["0709999999"],
        "bostadstyp": "Villa",
        "is_active": true,
        "is_telefon": true
      },
      {
        "personnamn": "Person 2",
        "alder": "42",
        "kon": "Man",
        "postnummer": "999 00",
        "postort": "Norrköping",
        "telefon": ["0701111111", "0132222222"],
        "bostadstyp": "Radhus",
        "is_active": true,
        "is_telefon": true
      }
    ]
  }'
```

---

## 4. DATA_PRIVATE API (Requires Authentication)

**Note:** All data-private endpoints require authentication via Sanctum token.

### Generate Token
```bash
# Run in tinker
php artisan tinker
User::first()->createToken('api-token')->plainTextToken
```

### GET - List Records
```bash
curl -X GET "http://127.0.0.1:8000/api/data-private?per_page=25" \
  -H "Authorization: Bearer YOUR_TOKEN"

# With filters
curl -X GET "http://127.0.0.1:8000/api/data-private?postnummer=999&is_active=1" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### POST - Create Single Record
```bash
curl -X POST "http://127.0.0.1:8000/api/data-private" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -d '{
    "personnummer": "197001011234",
    "personnamn": "Private Person",
    "gatuadress": "Privategatan 1",
    "postnummer": "999 00",
    "postort": "Secret City",
    "kommun": "Secret",
    "lan": "Secret län",
    "is_active": true
  }'
```

### PUT - Update Record
```bash
curl -X PUT "http://127.0.0.1:8000/api/data-private/1" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -d '{
    "postort": "Updated City",
    "is_active": false
  }'
```

### GET - Single Record
```bash
curl -X GET "http://127.0.0.1:8000/api/data-private/1" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### DELETE - Remove Record
```bash
curl -X DELETE "http://127.0.0.1:8000/api/data-private/1" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### POST - Bulk Insert (up to 100 records)
```bash
curl -X POST "http://127.0.0.1:8000/api/data-private/bulk" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -d '{
    "records": [
      {
        "personnummer": "198001011111",
        "personnamn": "Private 1",
        "postnummer": "999 11",
        "postort": "City 1",
        "is_active": true
      },
      {
        "personnummer": "199001012222",
        "personnamn": "Private 2",
        "postnummer": "999 22",
        "postort": "City 2",
        "is_active": true
      }
    ]
  }'
```

---

## Response Formats

### Successful Creation (201)
```json
{
  "message": "Record created successfully",
  "data": {
    "id": 1,
    "personnamn": "Test Person",
    "postnummer": "111 11",
    ...
  }
}
```

### Successful Update (200)
```json
{
  "message": "Record updated successfully",
  "data": {
    "id": 1,
    "personnamn": "Updated Person",
    ...
  }
}
```

### Bulk Operation Response
```json
{
  "message": "Bulk operation completed",
  "summary": {
    "total": 5,
    "created": 3,
    "updated": 2,
    "failed": 0
  },
  "errors": []
}
```

### List Response (Paginated)
```json
{
  "data": [...],
  "links": {
    "first": "...",
    "last": "...",
    "prev": null,
    "next": "..."
  },
  "meta": {
    "current_page": 1,
    "per_page": 25,
    "total": 100,
    ...
  }
}
```

---

## Database Verification Commands

```bash
# Check hitta_data
sqlite3 database/database.sqlite "SELECT * FROM hitta_data LIMIT 5;"

# Check ratsit_data  
sqlite3 database/database.sqlite "SELECT * FROM ratsit_data LIMIT 5;"

# Check merinfo_data
sqlite3 database/database.sqlite "SELECT * FROM merinfo_data LIMIT 5;"

# Check data_private
sqlite3 database/database.sqlite "SELECT * FROM data_private LIMIT 5;"

# Count records
sqlite3 database/database.sqlite "
  SELECT 'hitta_data' as table_name, COUNT(*) as count FROM hitta_data
  UNION ALL
  SELECT 'ratsit_data', COUNT(*) FROM ratsit_data
  UNION ALL
  SELECT 'merinfo_data', COUNT(*) FROM merinfo_data
  UNION ALL
  SELECT 'data_private', COUNT(*) FROM data_private;
"
```

---

## Features Summary

✅ **All endpoints support:**
- GET (list with pagination and filters)
- POST (create single record with upsert by unique field)
- PUT (update by ID)
- DELETE (remove by ID)
- BULK POST (create/update up to 100 records at once)

✅ **Upsert behavior:**
- `hitta_data`: By `personnamn`
- `ratsit_data`: By `personnummer`
- `merinfo_data`: By `personnamn`
- `data_private`: By `personnummer`

✅ **All data persisted to SQLite database**
✅ **Comprehensive validation on all endpoints**
✅ **Detailed error reporting in bulk operations**
