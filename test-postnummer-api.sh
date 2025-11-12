#!/bin/bash

# Test script for post_nummer API endpoint
# Tests all three formats: with space, URL encoded, without space

API_URL="http://127.0.0.1:8000/api/post-nummer/by-code"

echo "=== Testing Post Nummer API Endpoint ==="
echo

# Reset test record first
echo "1. Resetting test record 624 66..."
sqlite3 database/database.sqlite "UPDATE post_nummer SET count=0, progress=0, status=NULL WHERE post_nummer='624 66';"
echo "   Reset complete"
echo

# Test 1: URL encoded space (%20)
echo "2. Test with URL encoded space (624%2066)..."
RESULT=$(curl -s -X PUT "${API_URL}/624%2066" \
  -H "Content-Type: application/json" \
  -d '{"count": 100, "progress": 25, "status": "url-encoded-test"}')
echo "   Response: $(echo $RESULT | jq -r '.message')"
DB_CHECK=$(sqlite3 database/database.sqlite "SELECT count, progress, status FROM post_nummer WHERE post_nummer='624 66';")
echo "   Database: $DB_CHECK"
echo

# Test 2: Without space (62466)
echo "3. Test without space (62466)..."
RESULT=$(curl -s -X PUT "${API_URL}/62466" \
  -H "Content-Type: application/json" \
  -d '{"count": 200, "progress": 50, "status": "no-space-test"}')
echo "   Response: $(echo $RESULT | jq -r '.message')"
DB_CHECK=$(sqlite3 database/database.sqlite "SELECT count, progress, status FROM post_nummer WHERE post_nummer='624 66';")
echo "   Database: $DB_CHECK"
echo

# Test 3: Literal space in URL (will be auto-encoded by curl)
echo "4. Test with literal space in URL (624 66)..."
RESULT=$(curl -s -X PUT "${API_URL}/624 66" \
  -H "Content-Type: application/json" \
  -d '{"count": 300, "progress": 75, "status": "space-test"}')
echo "   Response: $(echo $RESULT | jq -r '.message')"
DB_CHECK=$(sqlite3 database/database.sqlite "SELECT count, progress, status FROM post_nummer WHERE post_nummer='624 66';")
echo "   Database: $DB_CHECK"
echo

# Test 4: Test with postal code that doesn't exist
echo "5. Test with non-existent postal code (99999)..."
RESULT=$(curl -s -X PUT "${API_URL}/99999" \
  -H "Content-Type: application/json" \
  -d '{"count": 999, "progress": 99, "status": "should-fail"}')
echo "   Response: $(echo $RESULT | jq -r '.message')"
echo

# Final verification
echo "6. Final verification..."
DB_CHECK=$(sqlite3 database/database.sqlite "SELECT post_nummer, count, progress, status FROM post_nummer WHERE post_nummer='624 66';")
echo "   Final state: $DB_CHECK"
echo

echo "=== All Tests Complete ==="
