# Post Nummer Bulk Update API

## Endpoint
`POST /api/post-nummer/bulk-update`

## Description
Bulk update multiple post_nummer records at once using postal codes (postnummer) as identifiers. Supports up to 100 records per request.

## Features
- ✅ Updates multiple records in a single request
- ✅ Handles all postal code formats (with space, without space, URL encoded)
- ✅ Returns detailed summary with success/failure counts
- ✅ Provides error details for failed updates
- ✅ Validates all fields before processing
- ✅ Maximum 100 records per request

## Request Format

```json
{
  "records": [
    {
      "postnummer": "624 66",
      "count": 100,
      "progress": 25,
      "status": "processing"
    },
    {
      "postnummer": "62467",
      "count": 200,
      "phone": 50,
      "is_complete": true
    }
  ]
}
```

## Available Fields (all optional except postnummer)

- `postnummer` (required) - Postal code identifier
- `post_ort` - City name
- `post_lan` - County name
- `total_count` - Total count
- `count` - Count value
- `phone` - Phone count
- `house` - House count
- `bolag` - Company count
- `foretag` - Business count
- `personer` - Person count
- `platser` - Place count
- `status` - Status text
- `progress` - Progress value (0-100)
- `is_pending` - Boolean
- `is_complete` - Boolean
- `is_active` - Boolean
- `last_processed_page` - Last processed page number
- `processed_count` - Processed count

## Response Format

### Success Response (200 OK)
```json
{
  "message": "Bulk update completed",
  "summary": {
    "total": 3,
    "updated": 2,
    "failed": 1
  },
  "errors": [
    {
      "index": 2,
      "postnummer": "99999",
      "error": "Post nummer not found"
    }
  ]
}
```

## Postal Code Format Handling

The endpoint automatically handles all these formats:
- `"624 66"` - With space
- `"62466"` - Without space
- `"624%2066"` - URL encoded

All three formats will find and update the same record (`624 66` in database).

## Examples

### Example 1: Basic Bulk Update
```bash
curl -X POST http://127.0.0.1:8000/api/post-nummer/bulk-update \
  -H "Content-Type: application/json" \
  -d '{
    "records": [
      {
        "postnummer": "624 66",
        "count": 100,
        "progress": 50,
        "status": "in-progress"
      },
      {
        "postnummer": "62467",
        "count": 200,
        "progress": 75,
        "status": "almost-done"
      }
    ]
  }'
```

### Example 2: Update Multiple Fields
```bash
curl -X POST http://127.0.0.1:8000/api/post-nummer/bulk-update \
  -H "Content-Type: application/json" \
  -d '{
    "records": [
      {
        "postnummer": "749 50",
        "count": 500,
        "phone": 250,
        "house": 150,
        "personer": 500,
        "progress": 100,
        "is_complete": true,
        "status": "completed"
      }
    ]
  }'
```

### Example 3: Mixed Format Update
```bash
curl -X POST http://127.0.0.1:8000/api/post-nummer/bulk-update \
  -H "Content-Type: application/json" \
  -d '{
    "records": [
      {
        "postnummer": "624 66",
        "count": 100
      },
      {
        "postnummer": "62467",
        "count": 200
      },
      {
        "postnummer": "749%2050",
        "count": 300
      }
    ]
  }'
```

### Example 4: JavaScript/Node.js
```javascript
const axios = require('axios');

const response = await axios.post('http://127.0.0.1:8000/api/post-nummer/bulk-update', {
  records: [
    {
      postnummer: '624 66',
      count: 100,
      progress: 50,
      status: 'processing'
    },
    {
      postnummer: '624 67',
      count: 200,
      progress: 75,
      status: 'almost-done'
    }
  ]
});

console.log('Updated:', response.data.summary.updated);
console.log('Failed:', response.data.summary.failed);
console.log('Errors:', response.data.errors);
```

## Validation Rules

- `records` - Required, must be array, min 1 item, max 100 items
- `records.*.postnummer` - Required, must be string
- All other fields are optional and validated by type (string, integer, boolean)

## Error Handling

### Record Not Found
If a postal code doesn't exist, it's reported in the errors array but doesn't stop processing of other records.

```json
{
  "message": "Bulk update completed",
  "summary": {
    "total": 2,
    "updated": 1,
    "failed": 1
  },
  "errors": [
    {
      "index": 1,
      "postnummer": "99999",
      "error": "Post nummer not found"
    }
  ]
}
```

### Validation Errors (422 Unprocessable Entity)
```json
{
  "message": "The records field is required.",
  "errors": {
    "records": [
      "The records field is required."
    ]
  }
}
```

## Performance Notes

- Maximum 100 records per request to prevent timeout
- Each record is processed individually for better error handling
- Database queries are optimized with indexed lookups
- Response includes detailed summary for monitoring

## Related Endpoints

- Single update by postal code: `PUT /api/post-nummer/by-code/{postnummer}`
- Standard CRUD: `GET|POST|PUT|DELETE /api/post-nummer`
