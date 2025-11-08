# Queue System Guide for PostNummer Processing

## Overview
The PostNummer processing system now uses Laravel's queue system to run the `ratsit_hitta.mjs` script in the background. This allows you to queue multiple post codes and they will be processed one at a time without blocking the UI.

## Starting the Queue Worker

To process queued jobs, you need to run a queue worker. Open a terminal and run:

```bash
php artisan queue:work --queue=postnummer
```

Or to process all queues:

```bash
php artisan queue:work
```

### Queue Worker Options

```bash
# Process jobs with detailed output
php artisan queue:work --queue=postnummer --verbose

# Process jobs and restart after processing each job (useful during development)
php artisan queue:work --queue=postnummer --max-jobs=1

# Process jobs with a timeout
php artisan queue:work --queue=postnummer --timeout=7200

# Run queue worker as a daemon (recommended for production)
php artisan queue:work --queue=postnummer --daemon
```

## Using the System

### From the Admin Panel (http://localhost:8000/post-nummers)

1. Click the **Update** button next to any post nummer
2. Confirm in the modal dialog
3. The job is queued immediately
4. You can queue multiple jobs - they will process in order
5. Watch the **Status** column update:
   - **Pending** (gray) - Not yet processed
   - **Running** (yellow) - Currently being processed
   - **Complete** (green) - Processing finished successfully

### From the Command Line

```bash
# Queue a job for a specific post nummer
php artisan ratsit:hitta 10000

# Run synchronously (blocking) - useful for testing
php artisan ratsit:hitta 10000 --sync
```

### Queue Multiple Post Codes at Once

You can create a bash script to queue multiple post codes:

```bash
#!/bin/bash
# queue_postcodes.sh

for postcode in 10000 10001 10002 10003 10004
do
  php artisan ratsit:hitta $postcode
  echo "Queued: $postcode"
done
```

Then run:
```bash
chmod +x queue_postcodes.sh
./queue_postcodes.sh
```

## Monitoring Jobs

### View Queue Status

```bash
# Check how many jobs are in the queue
php artisan queue:monitor postnummer

# View failed jobs
php artisan queue:failed

# Retry a failed job
php artisan queue:retry {job-id}

# Retry all failed jobs
php artisan queue:retry all

# Clear failed jobs
php artisan queue:flush
```

### View Logs

Jobs log their progress to Laravel's log file:

```bash
# View live logs
tail -f storage/logs/laravel.log

# Search for specific post nummer
grep "10000" storage/logs/laravel.log
```

## Production Deployment

### Using Supervisor (Recommended)

Create a supervisor configuration file `/etc/supervisor/conf.d/laravel-worker.conf`:

```ini
[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /home/baba/WORK/ekoll.se/filament/artisan queue:work --queue=postnummer --sleep=3 --tries=1 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=baba
numprocs=1
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

### Using Systemd

Create a systemd service file `/etc/systemd/system/laravel-queue.service`:

```ini
[Unit]
Description=Laravel Queue Worker
After=network.target

[Service]
User=baba
Group=www-data
Restart=always
ExecStart=/usr/bin/php /home/baba/WORK/ekoll.se/filament/artisan queue:work --queue=postnummer --sleep=3 --tries=1 --max-time=3600

[Install]
WantedBy=multi-user.target
```

Then:
```bash
sudo systemctl daemon-reload
sudo systemctl enable laravel-queue
sudo systemctl start laravel-queue
sudo systemctl status laravel-queue
```

## Troubleshooting

### Jobs Not Processing

1. **Check if queue worker is running:**
   ```bash
   ps aux | grep "queue:work"
   ```

2. **Check queue table:**
   ```bash
   php artisan tinker
   >>> DB::table('jobs')->count()
   ```

3. **Check failed jobs:**
   ```bash
   php artisan queue:failed
   ```

### Job Failures

If a job fails:
1. Check `storage/logs/laravel.log` for error details
2. The PostNummer status will be reset to 'pending' and `is_active` to false
3. You can retry from the UI or command line

### Restart Queue Worker After Code Changes

The queue worker caches your code. After making changes:

```bash
# If using Supervisor
sudo supervisorctl restart laravel-worker:*

# If using Systemd
sudo systemctl restart laravel-queue

# Or manually restart
php artisan queue:restart
```

## Performance Tuning

### Multiple Workers

To process multiple post codes simultaneously, increase the number of workers:

```bash
# Run 3 workers (processes 3 jobs at once)
php artisan queue:work --queue=postnummer &
php artisan queue:work --queue=postnummer &
php artisan queue:work --queue=postnummer &
```

Or with Supervisor, set `numprocs=3` in the configuration.

### Horizon (Advanced)

For better queue monitoring, consider installing Laravel Horizon:

```bash
composer require laravel/horizon
php artisan horizon:install
php artisan migrate
php artisan horizon
```

Then visit: http://localhost:8000/horizon

## Database Queue vs Redis

The system currently uses the database queue driver. For better performance with many jobs, consider switching to Redis:

1. **Install Redis:**
   ```bash
   sudo apt install redis-server
   composer require predis/predis
   ```

2. **Update `.env`:**
   ```env
   QUEUE_CONNECTION=redis
   ```

3. **Restart queue worker**
