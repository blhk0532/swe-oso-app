# Quick Guide: Update Post Nummer from Sweden Table

## Overview
You can now update all post_nummer records with data from the sweden table directly from the Post Nummer admin page!

## How to Use

### Step 1: Start Queue Worker
Open a terminal and run:
```bash
cd /home/baba/WORK/ekoll.se/filament
php artisan queue:work
```

Keep this terminal open while the update runs.

### Step 2: Navigate to Post Nummer Page
1. Log in to the admin panel
2. Go to **Post Nummer** in the sidebar

### Step 3: Start the Update
1. Click the **"Update from Sweden Table"** button at the top of the page (next to "Create")
2. Confirm the action in the modal
3. The job will start running in the background

### Step 4: Monitor Progress
- A **progress widget** will appear at the top of the page showing:
  - Update Status (Running/Completed/Failed)
  - Progress percentage
  - Records updated
  - Records skipped
- The widget auto-refreshes every 2 seconds
- You can continue working while it runs!

## Features

✅ **Integrated into Post Nummer page** - No need for a separate page
✅ **Real-time progress** - See live updates every 2 seconds
✅ **Background processing** - Continue working while it updates
✅ **Visual stats** - Clear progress indicators
✅ **Error handling** - Shows if already running or if job fails

## Queue Worker Commands

### Start Queue Worker
```bash
php artisan queue:work
```

### Start with auto-reload (development)
```bash
php artisan queue:listen
```

### Check queue status
```bash
php artisan queue:failed  # See failed jobs
```

## What Gets Updated

The job updates these fields in the `post_nummer` table:
- `post_ort` (city/location)
- `post_lan` (county)

It matches records by `post_nummer` (postal code) from the `sweden` table.

## Troubleshooting

### Progress not showing?
1. Make sure queue worker is running: `php artisan queue:work`
2. Refresh the page

### Job failed?
Check the logs: `storage/logs/laravel.log`

### Clear stuck progress
Run in terminal:
```bash
php artisan tinker
>>> Cache::forget('update_post_nummer_progress');
```

Or restart the queue worker.
