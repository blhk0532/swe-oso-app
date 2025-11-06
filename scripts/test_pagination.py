#!/usr/bin/env python3
"""Quick test of pagination logic"""

# Simulate the pagination logic
total_pages = None
current_page = 1

# First page - set total_pages
if current_page == 1:
    total_pages = 24
    print(f"Page {current_page}: total_pages set to {total_pages}")

# Check pagination logic for pages 2-5
for test_page in range(2, 6):
    current_page = test_page
    
    print(f"\n--- Testing Page {current_page} ---")
    print(f"total_pages = {total_pages}")
    print(f"current_page = {current_page}")
    
    if total_pages and current_page < total_pages:
        print(f"✓ Should continue: Moving to page {current_page + 1} of {total_pages}")
    elif not total_pages:
        print("✗ total_pages is None - would check for next button")
    else:
        print(f"✓ Reached final page ({current_page} of {total_pages})")
