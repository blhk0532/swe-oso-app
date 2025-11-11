# Swedish Data API Documentation

This API provides access to Swedish person and property data from various sources including Ratsit, Hitta.se, and private data collections. All endpoints are publicly accessible without authentication.

## Base URL
```
http://your-domain.com/api
```

## Authentication
**No authentication required** - All endpoints are publicly accessible.

## Response Format
All responses return JSON with the following structure:
```json
{
  "data": [...],
  "links": {...},
  "meta": {...}
}
```

## Common Parameters

### Pagination
- `per_page` (optional): Number of records per page (default: 25, max: 100)
- `page` (optional): Page number (default: 1)

### Sorting
- `sort_by` (optional): Field to sort by (default: created_at)
- `sort_direction` (optional): Sort direction - 'asc' or 'desc' (default: desc)

---

## 1. HittaData API

**Table**: `hitta_data` - Data scraped from hitta.se

### List HittaData
```http
GET /api/hitta-data
```

#### Query Parameters
- `is_active` (boolean): Filter by active status
- `is_telefon` (boolean): Filter by phone availability
- `is_ratsit` (boolean): Filter by ratsit integration status
- `postnummer` (string): Filter by postal code (partial match)
- `postort` (string): Filter by city (partial match)
- `personnamn` (string): Filter by person name (partial match)
- `telefon` (string): Filter by phone number (partial match)
- `bostadstyp` (string): Filter by property type (partial match)

#### Example Request
```bash
curl "http://localhost:8000/api/hitta-data?is_active=true&postort=Stockholm&per_page=10"
```

#### Example Response
```json
{
  "data": [
    {
      "id": 1,
      "person": {
        "personnamn": "Anna Andersson",
        "alder": "35",
        "kon": "F"
      },
      "address": {
        "gatuadress": "Storgatan 12",
        "postnummer": "111 22",
        "postort": "Stockholm"
      },
      "contact": {
        "telefon": "08-123 45 67",
        "karta": "https://...",
        "link": "https://hitta.se/..."
      },
      "property": {
        "bostadstyp": "Lägenhet",
        "bostadspris": "3 500 000 kr"
      },
      "flags": {
        "is_active": true,
        "is_telefon": true,
        "is_ratsit": false
      },
      "created_at": "2025-01-10T10:00:00.000000Z",
      "updated_at": "2025-01-10T10:00:00.000000Z"
    }
  ],
  "links": {...},
  "meta": {...}
}
```

### Create HittaData
```http
POST /api/hitta-data
```

#### Request Body
```json
{
  "personnamn": "Anna Andersson",
  "alder": "35",
  "kon": "F",
  "gatuadress": "Storgatan 12",
  "postnummer": "111 22",
  "postort": "Stockholm",
  "telefon": "08-123 45 67",
  "karta": "https://maps.example.com/...",
  "link": "https://hitta.se/...",
  "bostadstyp": "Lägenhet",
  "bostadspris": "3 500 000 kr",
  "is_active": true,
  "is_telefon": true,
  "is_ratsit": false
}
```

**Note**: If `personnamn` is provided and already exists, the record will be updated instead of creating a duplicate.

### Get Single HittaData
```http
GET /api/hitta-data/{id}
```

### Update HittaData
```http
PUT /api/hitta-data/{id}
PATCH /api/hitta-data/{id}
```

### Delete HittaData
```http
DELETE /api/hitta-data/{id}
```

---

## 2. RatsitData API

**Table**: `ratsit_data` - Comprehensive person data from Ratsit

### List RatsitData
```http
GET /api/ratsit-data
```

#### Query Parameters
- `is_active` (boolean): Filter by active status
- `postnummer` (string): Filter by postal code (partial match)
- `postort` (string): Filter by city (partial match)
- `kommun` (string): Filter by municipality (partial match)
- `lan` (string): Filter by county (partial match)
- `personnummer` (string): Filter by personal number (partial match)
- `personnamn` (string): Filter by person name (partial match)

#### Example Request
```bash
curl "http://localhost:8000/api/ratsit-data?is_active=true&lan=Stockholm&per_page=5"
```

#### Example Response
```json
{
  "data": [
    {
      "id": 1,
      "address": {
        "gatuadress": "Storgatan 12",
        "postnummer": "111 22",
        "postort": "Stockholm",
        "forsamling": "Stockholm",
        "kommun": "Stockholm",
        "lan": "Stockholms län",
        "longitude": "18.0686",
        "latitud": "59.3293"
      },
      "person": {
        "fodelsedag": "1990-05-15",
        "personnummer": "900515-1234",
        "alder": "35",
        "kon": "F",
        "civilstand": "Gift",
        "fornamn": "Anna",
        "efternamn": "Andersson",
        "personnamn": "Anna Andersson",
        "telefon": ["08-123 45 67", "070-987 65 43"],
        "epost_adress": ["anna.andersson@email.com"],
        "bolagsengagemang": ["ABC Company AB"]
      },
      "property": {
        "agandeform": "Ägare",
        "bostadstyp": "Lägenhet",
        "boarea": "85 kvm",
        "byggar": "2010",
        "fastighet": "Stockholm Södermalm 1:123",
        "personer": ["Anna Andersson", "Erik Andersson"],
        "foretag": ["ABC Company AB"],
        "grannar": ["Erik Johansson", "Maria Nilsson"],
        "fordon": ["ABC 123"],
        "hundar": ["Bella", "Max"]
      },
      "is_active": true,
      "created_at": "2025-01-10T10:00:00.000000Z",
      "updated_at": "2025-01-10T10:00:00.000000Z"
    }
  ],
  "links": {...},
  "meta": {...}
}
```

### Create RatsitData
```http
POST /api/ratsit-data
```

#### Request Body
```json
{
  "gatuadress": "Storgatan 12",
  "postnummer": "111 22",
  "postort": "Stockholm",
  "forsamling": "Stockholm",
  "kommun": "Stockholm",
  "lan": "Stockholms län",
  "fodelsedag": "1990-05-15",
  "personnummer": "900515-1234",
  "alder": "35",
  "kon": "F",
  "civilstand": "Gift",
  "fornamn": "Anna",
  "efternamn": "Andersson",
  "personnamn": "Anna Andersson",
  "telefon": ["08-123 45 67", "070-987 65 43"],
  "epost_adress": ["anna.andersson@email.com"],
  "bolagsengagemang": ["ABC Company AB"],
  "agandeform": "Ägare",
  "bostadstyp": "Lägenhet",
  "boarea": "85 kvm",
  "byggar": "2010",
  "fastighet": "Stockholm Södermalm 1:123",
  "personer": ["Anna Andersson", "Erik Andersson"],
  "foretag": ["ABC Company AB"],
  "grannar": ["Erik Johansson", "Maria Nilsson"],
  "fordon": ["ABC 123"],
  "hundar": ["Bella", "Max"],
  "longitude": "18.0686",
  "latitud": "59.3293",
  "is_active": true
}
```

**Note**: If `personnummer` is provided and already exists, the record will be updated instead of creating a duplicate.

### Get Single RatsitData
```http
GET /api/ratsit-data/{id}
```

### Update RatsitData
```http
PUT /api/ratsit-data/{id}
PATCH /api/ratsit-data/{id}
```

### Delete RatsitData
```http
DELETE /api/ratsit-data/{id}
```

---

## 3. HittaSe API

**Table**: `hitta_se` - Simplified hitta.se data storage

### Create HittaSe Data
```http
POST /api/hitta-se
```

#### Request Body
```json
{
  "personnamn": "Anna Andersson",
  "alder": "35",
  "kon": "F",
  "gatuadress": "Storgatan 12",
  "postnummer": "111 22",
  "postort": "Stockholm",
  "telefon": ["08-123 45 67"],
  "karta": "https://maps.example.com/...",
  "link": "https://hitta.se/...",
  "bostadstyp": "Lägenhet",
  "bostadspris": "3 500 000 kr",
  "is_active": true,
  "is_telefon": true,
  "is_ratsit": false
}
```

**Note**: If `link` is provided and already exists, the record will be updated instead of creating a duplicate.

#### Example Response
```json
{
  "message": "Record created successfully",
  "data": {
    "personnamn": "Anna Andersson",
    "alder": "35",
    "kon": "F",
    "gatuadress": "Storgatan 12",
    "postnummer": "111 22",
    "postort": "Stockholm",
    "telefon": ["08-123 45 67"],
    "karta": "https://maps.example.com/...",
    "link": "https://hitta.se/...",
    "bostadstyp": "Lägenhet",
    "bostadspris": "3 500 000 kr",
    "is_active": true,
    "is_telefon": true,
    "is_ratsit": false,
    "updated_at": "2025-01-10T10:00:00.000000Z",
    "created_at": "2025-01-10T10:00:00.000000Z",
    "id": 1
  }
}
```

---

## 4. DataPrivate API (Requires Authentication)

**Table**: `private_data` - Private/sensitive person data

⚠️ **Note**: This endpoint requires `auth:sanctum` authentication and contains sensitive private data.

### List DataPrivate
```http
GET /api/data-private
```

#### Query Parameters
- `is_active` (boolean): Filter by active status
- `postnummer` (string): Filter by postal code (partial match)
- `postort` (string): Filter by city (partial match)
- `kommun` (string): Filter by municipality (partial match)
- `lan` (string): Filter by county (partial match)
- `personnummer` (string): Filter by personal number (partial match)
- `personnamn` (string): Filter by person name (partial match)

---

## Data Types and Validation

### Common Data Types
- **String fields**: Nullable strings, max length varies by field
- **Boolean fields**: `true`/`false` values
- **Array fields**: JSON arrays of strings (e.g., phone numbers, emails)
- **Date fields**: ISO 8601 format (YYYY-MM-DD)
- **Numeric fields**: String representations of numbers

### Postal Code Format
All postal codes are stored in the format `XXX XX` (e.g., `111 22` instead of `11122`).

### Gender Values
- `M`: Male
- `F`: Female
- `O`: Other

---

## Error Responses

### Validation Errors (422)
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "personnamn": ["The personnamn field is required."],
    "postnummer": ["The postnummer must be a string."]
  }
}
```

### Not Found (404)
```json
{
  "message": "Record not found"
}
```

### Server Error (500)
```json
{
  "message": "Internal server error"
}
```

---

## Rate Limiting
Currently no rate limiting is implemented. Consider implementing appropriate limits for production use.

---

## Next.js Integration Examples

### Fetch HittaData
```javascript
// pages/api/hitta-data.js
export default async function handler(req, res) {
  if (req.method !== 'GET') {
    return res.status(405).json({ message: 'Method not allowed' });
  }

  try {
    const response = await fetch('http://your-laravel-api.com/api/hitta-data?' + new URLSearchParams(req.query));
    const data = await response.json();

    res.status(200).json(data);
  } catch (error) {
    res.status(500).json({ message: 'Failed to fetch data' });
  }
}
```

### Create RatsitData
```javascript
// In your Next.js component
const createRatsitData = async (data) => {
  try {
    const response = await fetch('http://your-laravel-api.com/api/ratsit-data', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify(data),
    });

    if (!response.ok) {
      throw new Error('Failed to create data');
    }

    const result = await response.json();
    return result;
  } catch (error) {
    console.error('Error:', error);
    throw error;
  }
};
```

### Usage with SWR
```javascript
import useSWR from 'swr';

function HittaDataList() {
  const { data, error } = useSWR('/api/hitta-data?is_active=true&per_page=20', fetcher);

  if (error) return <div>Failed to load</div>;
  if (!data) return <div>Loading...</div>;

  return (
    <div>
      {data.data.map((item) => (
        <div key={item.id}>
          <h3>{item.person.personnamn}</h3>
          <p>{item.address.gatuadress}, {item.address.postnummer} {item.address.postort}</p>
        </div>
      ))}
    </div>
  );
}
```

---

## Database Tables Reference

- `hitta_data`: hitta.se scraped data
- `ratsit_data`: Comprehensive Ratsit person data
- `hitta_se`: Simplified hitta.se storage
- `private_data`: Sensitive private data (requires auth)

---

## Notes for AI Agents

1. **No Authentication Required**: All main endpoints (`hitta-data`, `ratsit-data`, `hitta-se`) work without authentication
2. **Upsert Behavior**: POST requests will update existing records if unique identifiers match
3. **Postal Code Format**: Always use `XXX XX` format (space-separated)
4. **Array Fields**: Phone numbers, emails, etc. should be sent as arrays
5. **Pagination**: Use `per_page` and `page` for large datasets
6. **Filtering**: Combine multiple filters for precise queries
7. **Error Handling**: Always check response status and handle validation errors</content>
<parameter name="filePath">/home/baba/WORK/ekoll.se/filament/API_DOCUMENTATION.md