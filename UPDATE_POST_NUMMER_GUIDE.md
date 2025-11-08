# Update Post Nummer Interactive Guide

## Overview
The interactive Post Nummer update system allows you to update all postal codes with data from the Sweden table while monitoring real-time progress from the admin panel.

## Features
- ✅ Real-time progress tracking
- ✅ Background job processing
- ✅ Live statistics (total, processed, updated, skipped)
- ✅ Progress percentage with visual bar
- ✅ Auto-refresh every 2 seconds
- ✅ Multiple tab support - monitor from any browser tab

## How to Use

### Step 1: Start the Queue Worker
The queue worker must be running to process background jobs. Open a terminal and run:

```bash
cd /home/baba/WORK/ekoll.se/filament
php artisan queue:work --queue=default
```

**For development**, you can also use:
```bash
php artisan queue:listen
```

**For production**, use a process manager like Supervisor to keep the queue worker running.

### Step 2: Access the Update Page
1. Log in to the Filament admin panel
2. Navigate to **System → Update Post Nummer from Sweden** (in the sidebar)
3. You'll see the update interface

### Step 3: Start the Update
1. Click the **"Start Update"** button in the top-right corner
2. Confirm the action in the modal dialog
3. The job will be queued and start processing

### Step 4: Monitor Progress
The page will automatically refresh every 2 seconds and display:
- **Status**: Current state (Running, Completed, Failed)
- **Progress Bar**: Visual representation of completion percentage
- **Statistics**:
  - Total: Total records to process
  - Processed: Records processed so far
  - Updated: Records successfully updated
  - Skipped: Records not found in post_nummer table
- **Message**: Current operation status

### Step 5: Multi-Tab Monitoring
You can:
- Open the page in multiple browser tabs
- Keep working in other parts of the admin panel
- The progress updates in real-time across all tabs

### Step 6: After Completion
Once the status shows "Completed", you can:
- Review the final statistics
- Click **"Clear Progress"** to reset the display
- Start a new update if needed

## Technical Details

### Job Processing
- Job: `App\Jobs\UpdatePostNummerFromSweden`
- Queue: `default`
- Timeout: 1 hour
- Progress updates: Every 100 records

### Progress Storage
Progress is stored in Laravel Cache with key: `update_post_nummer_progress`

Structure:
```php
[
    'status' => 'running|completed|failed',
    'total' => 18858,
    'updated' => 15000,
    'skipped' => 3858,
    'processed' => 18858,
    'percentage' => 100.0,
    'message' => 'Status message...'
]
```

### Alternative: Run via Command Line
You can also trigger the update programmatically:

```php
use App\Jobs\UpdatePostNummerFromSweden;

UpdatePostNummerFromSweden::dispatch();
```

Or from tinker:
```bash
php artisan tinker
>>> \App\Jobs\UpdatePostNummerFromSweden::dispatch();
```

## Queue Configuration

### Using Database Queue (Recommended)
Set in `.env`:
```env
QUEUE_CONNECTION=database
```

Then run:
```bash
php artisan queue:work database
```

### Using Sync Queue (Testing Only)
Set in `.env`:
```env
QUEUE_CONNECTION=sync
```

This processes jobs immediately without background processing (no real-time updates).

## Troubleshooting

### Progress Not Updating
1. **Check queue worker is running**:
   ```bash
   php artisan queue:work
   ```

2. **Check cache is working**:
   ```bash
   php artisan cache:clear
   ```

3. **Verify queue connection**:
   ```bash
   php artisan queue:failed  # Check for failed jobs
   ```

### Job Fails
- Check `storage/logs/laravel.log` for errors
- Check failed jobs: `php artisan queue:failed`
- Retry failed job: `php artisan queue:retry {job-id}`

### Clear Stuck Progress
If the progress gets stuck, clear it manually:
```bash
php artisan tinker
>>> Cache::forget('update_post_nummer_progress');
```

Or click **"Clear Progress"** button in the admin panel.

## Performance Tips

1. **Batch Size**: The job updates progress every 100 records for optimal performance
2. **Queue Workers**: Run multiple workers for faster processing:
   ```bash
   php artisan queue:work --queue=default --tries=3 --sleep=3
   ```

3. **Cache Driver**: Use Redis for better performance:
   ```env
   CACHE_DRIVER=redis
   ```

## Production Setup

### Supervisor Configuration
Create `/etc/supervisor/conf.d/laravel-worker.conf`:

```ini
[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /home/baba/WORK/ekoll.se/filament/artisan queue:work database --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=baba
numprocs=2
redirect_stderr=true
stdout_logfile=/home/baba/WORK/ekoll.se/filament/storage/logs/worker.log
stopwaitsecs=3600
```

Then:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start laravel-worker:*
```

## Files Modified/Created

1. **Job**: `app/Jobs/UpdatePostNummerFromSweden.php`
2. **Page**: `app/Filament/Pages/UpdatePostNummer.php`
3. **View**: `resources/views/filament/pages/update-post-nummer.blade.php`

## Benefits Over CLI Seeder

### Old Way (CLI Seeder)
```bash
php artisan db:seed --class=UpdatePostNummerFromSwedenSeeder
```
- ❌ Blocks terminal
- ❌ No progress visibility
- ❌ Can't use admin panel while running
- ❌ No easy way to monitor from different location

### New Way (Interactive Job)
- ✅ Runs in background
- ✅ Real-time progress tracking
- ✅ Multi-tab monitoring
- ✅ Professional UI
- ✅ Error handling with notifications
- ✅ Can continue working in admin panel
