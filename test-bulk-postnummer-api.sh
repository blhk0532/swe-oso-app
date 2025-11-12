#!/bin/bash

# Test script for bulk post_nummer API endpoint
# Tests bulk updating multiple postal codes at once

API_URL="http://127.0.0.1:8000/api/post-nummer/bulk-update"

echo "=== Testing Bulk Post Nummer API Endpoint ==="
echo

# Reset test records
echo "1. Resetting test records..."
sqlite3 database/database.sqlite "UPDATE post_nummer SET count=0, progress=0, status=NULL WHERE post_nummer IN ('624 66', '624 67', '749 50');"
echo "   Reset complete"
echo

# Show initial state
echo "2. Initial state:"
sqlite3 database/database.sqlite "SELECT post_nummer, count, progress, status FROM post_nummer WHERE post_nummer IN ('624 66', '624 67', '749 50');"
echo

# Test 1: Bulk update with mixed formats (space, no space, URL encoded)
echo "3. Test bulk update with mixed postal code formats..."
RESULT=$(curl -s -X POST "${API_URL}" \
  -H "Content-Type: application/json" \
  -d '{
    "records": [
      {
        "postnummer": "624 66",
        "count": 100,
        "progress": 25,
        "status": "bulk-test-1"
      },
      {
        "postnummer": "62467",
        "count": 200,
        "progress": 50,
        "status": "bulk-test-2"
      },
      {
        "postnummer": "749%2050",
        "count": 300,
        "progress": 75,
        "status": "bulk-test-3"
      }
    ]
  }')

echo "   Response:"
echo "$RESULT" | jq '.'
echo

# Verify database updates
echo "4. Database state after bulk update:"
sqlite3 database/database.sqlite "SELECT post_nummer, count, progress, status FROM post_nummer WHERE post_nummer IN ('624 66', '624 67', '749 50');"
echo

# Test 2: Bulk update with some invalid postal codes
echo "5. Test bulk update with some invalid postal codes..."
RESULT=$(curl -s -X POST "${API_URL}" \
  -H "Content-Type: application/json" \
  -d '{
    "records": [
      {
        "postnummer": "624 66",
        "count": 150,
        "progress": 30,
        "status": "updated-again"
      },
      {
        "postnummer": "99999",
        "count": 999,
        "progress": 99,
        "status": "should-fail"
      },
      {
        "postnummer": "88888",
        "count": 888,
        "progress": 88,
        "status": "should-fail-too"
      }
    ]
  }')

echo "   Response:"
echo "$RESULT" | jq '.'
echo

# Test 3: Bulk update with many fields
echo "6. Test bulk update with multiple fields..."
RESULT=$(curl -s -X POST "${API_URL}" \
  -H "Content-Type: application/json" \
  -d '{
    "records": [
      {
        "postnummer": "62466",
        "count": 500,
        "phone": 250,
        "house": 100,
        "personer": 500,
        "progress": 100,
        "is_complete": true,
        "status": "completed"
      }
    ]
  }')

echo "   Response summary:"
echo "$RESULT" | jq '.message, .summary'
echo

# Final verification
echo "7. Final database state:"
sqlite3 database/database.sqlite "SELECT post_nummer, count, phone, personer, progress, is_complete, status FROM post_nummer WHERE post_nummer IN ('624 66', '624 67', '749 50');"
echo

echo "=== All Bulk Tests Complete ==="
