#!/bin/bash

# Comprehensive API Test Script for all four data endpoints
# Tests: hitta_data, ratsit_data, merinfo_data, data_private

API_BASE="http://127.0.0.1:8000/api"
TOKEN="your_token_here"  # Replace with actual token for data-private

echo "========================================="
echo "   API ENDPOINTS TEST SUITE"
echo "========================================="
echo

# Helper function to print section headers
print_header() {
    echo
    echo "========================================="
    echo "  $1"
    echo "========================================="
    echo
}

# Helper function to check database
check_db() {
    local table=$1
    local where=$2
    echo "Database check:"
    sqlite3 database/database.sqlite "SELECT * FROM $table WHERE $where LIMIT 5;"
    echo
}

# ========================================
# 1. HITTA_DATA API TESTS
# ========================================
print_header "1. HITTA_DATA API"

echo "=== POST /api/hitta-data (Single Insert) ==="
RESPONSE=$(curl -s -X POST "${API_BASE}/hitta-data" \
  -H "Content-Type: application/json" \
  -d '{
    "personnamn": "Test Person Hitta",
    "alder": "45",
    "gatuadress": "Testgatan 1",
    "postnummer": "111 11",
    "postort": "Stockholm",
    "telefon": "0701234567",
    "bostadstyp": "Villa",
    "is_active": true,
    "is_telefon": true
  }')
echo "$RESPONSE" | jq '.message, .data.id, .data.personnamn'
echo

echo "=== GET /api/hitta-data (List) ==="
curl -s "${API_BASE}/hitta-data?per_page=3" | jq '.data | length, .[0].personnamn'
echo

echo "=== POST /api/hitta-data/bulk (Bulk Insert) ==="
BULK_RESPONSE=$(curl -s -X POST "${API_BASE}/hitta-data/bulk" \
  -H "Content-Type: application/json" \
  -d '{
    "records": [
      {
        "personnamn": "Bulk Person 1",
        "alder": "30",
        "postnummer": "111 22",
        "postort": "Stockholm",
        "is_active": true
      },
      {
        "personnamn": "Bulk Person 2",
        "alder": "40",
        "postnummer": "222 33",
        "postort": "Göteborg",
        "is_active": true
      }
    ]
  }')
echo "$BULK_RESPONSE" | jq '.message, .summary'
check_db "hitta_data" "personnamn LIKE 'Bulk%' OR personnamn LIKE 'Test%'"

# ========================================
# 2. RATSIT_DATA API TESTS
# ========================================
print_header "2. RATSIT_DATA API"

echo "=== POST /api/ratsit-data (Single Insert) ==="
RESPONSE=$(curl -s -X POST "${API_BASE}/ratsit-data" \
  -H "Content-Type: application/json" \
  -d '{
    "personnummer": "199001011234",
    "personnamn": "Test Person Ratsit",
    "gatuadress": "Ratsitgatan 1",
    "postnummer": "333 44",
    "postort": "Malmö",
    "kommun": "Malmö",
    "lan": "Skåne",
    "is_active": true
  }')
echo "$RESPONSE" | jq '.message, .data.id, .data.personnummer'
echo

echo "=== GET /api/ratsit-data (List) ==="
curl -s "${API_BASE}/ratsit-data?per_page=3" | jq '.data | length, .[0].personnamn'
echo

echo "=== POST /api/ratsit-data/bulk (Bulk Insert) ==="
BULK_RESPONSE=$(curl -s -X POST "${API_BASE}/ratsit-data/bulk" \
  -H "Content-Type: application/json" \
  -d '{
    "records": [
      {
        "personnummer": "198505051111",
        "personnamn": "Bulk Ratsit 1",
        "postnummer": "444 55",
        "postort": "Uppsala",
        "kommun": "Uppsala",
        "is_active": true
      },
      {
        "personnummer": "199212122222",
        "personnamn": "Bulk Ratsit 2",
        "postnummer": "555 66",
        "postort": "Västerås",
        "kommun": "Västerås",
        "is_active": true
      }
    ]
  }')
echo "$BULK_RESPONSE" | jq '.message, .summary'
check_db "ratsit_data" "personnamn LIKE 'Bulk%' OR personnamn LIKE 'Test%'"

# ========================================
# 3. MERINFO_DATA API TESTS
# ========================================
print_header "3. MERINFO_DATA API"

echo "=== POST /api/merinfo-data (Single Insert) ==="
RESPONSE=$(curl -s -X POST "${API_BASE}/merinfo-data" \
  -H "Content-Type: application/json" \
  -d '{
    "personnamn": "Test Person Merinfo",
    "alder": "50",
    "kon": "Man",
    "gatuadress": "Merinfogatan 1",
    "postnummer": "666 77",
    "postort": "Linköping",
    "telefon": ["0708888888", "0191234567"],
    "bostadstyp": "Lägenhet",
    "is_active": true,
    "is_telefon": true
  }')
echo "$RESPONSE" | jq '.message, .data.id, .data.personnamn'
echo

echo "=== GET /api/merinfo-data (List) ==="
curl -s "${API_BASE}/merinfo-data?per_page=3" | jq '.data | length, .[0].personnamn'
echo

echo "=== POST /api/merinfo-data/bulk (Bulk Insert) ==="
BULK_RESPONSE=$(curl -s -X POST "${API_BASE}/merinfo-data/bulk" \
  -H "Content-Type: application/json" \
  -d '{
    "records": [
      {
        "personnamn": "Bulk Merinfo 1",
        "alder": "35",
        "kon": "Kvinna",
        "postnummer": "777 88",
        "postort": "Örebro",
        "telefon": ["0709999999"],
        "is_active": true
      },
      {
        "personnamn": "Bulk Merinfo 2",
        "alder": "42",
        "kon": "Man",
        "postnummer": "888 99",
        "postort": "Jönköping",
        "telefon": ["0701111111"],
        "is_active": true
      }
    ]
  }')
echo "$BULK_RESPONSE" | jq '.message, .summary'
check_db "merinfo_data" "personnamn LIKE 'Bulk%' OR personnamn LIKE 'Test%'"

# ========================================
# 4. DATA_PRIVATE API TESTS (Requires Auth)
# ========================================
print_header "4. DATA_PRIVATE API (Protected)"

# Note: These requests require authentication token
# To test, first generate a token with: php artisan tinker
# User::first()->createToken('test-token')->plainTextToken

echo "NOTE: data-private endpoints require authentication"
echo "To test, generate a token with:"
echo "  php artisan tinker"
echo "  User::first()->createToken('test-token')->plainTextToken"
echo
echo "Then run these commands with the token:"
echo

echo "=== POST /api/data-private (Single Insert) ==="
echo "curl -X POST ${API_BASE}/data-private \\"
echo "  -H 'Content-Type: application/json' \\"
echo "  -H 'Authorization: Bearer YOUR_TOKEN' \\"
echo "  -d '{
    \"personnummer\": \"197001011234\",
    \"personnamn\": \"Private Person\",
    \"gatuadress\": \"Privategatan 1\",
    \"postnummer\": \"999 00\",
    \"postort\": \"Secret City\",
    \"is_active\": true
  }'"
echo

echo "=== POST /api/data-private/bulk (Bulk Insert) ==="
echo "curl -X POST ${API_BASE}/data-private/bulk \\"
echo "  -H 'Content-Type: application/json' \\"
echo "  -H 'Authorization: Bearer YOUR_TOKEN' \\"
echo "  -d '{
    \"records\": [
      {
        \"personnummer\": \"198001011111\",
        \"personnamn\": \"Bulk Private 1\",
        \"postnummer\": \"999 11\",
        \"is_active\": true
      },
      {
        \"personnummer\": \"199001012222\",
        \"personnamn\": \"Bulk Private 2\",
        \"postnummer\": \"999 22\",
        \"is_active\": true
      }
    ]
  }'"
echo

# ========================================
# SUMMARY
# ========================================
print_header "TEST SUMMARY"

echo "Database Record Counts:"
echo "- hitta_data:   $(sqlite3 database/database.sqlite 'SELECT COUNT(*) FROM hitta_data;')"
echo "- ratsit_data:  $(sqlite3 database/database.sqlite 'SELECT COUNT(*) FROM ratsit_data;')"
echo "- merinfo_data: $(sqlite3 database/database.sqlite 'SELECT COUNT(*) FROM merinfo_data;')"
echo "- data_private: $(sqlite3 database/database.sqlite 'SELECT COUNT(*) FROM data_private;')"
echo

echo "========================================="
echo "   ALL TESTS COMPLETED"
echo "========================================="
