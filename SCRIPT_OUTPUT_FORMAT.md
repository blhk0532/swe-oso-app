# Script Output Format for Progress Tracking

For the `ProcessPostNummer` job to track progress interactively, your Node.js script (`post_ort_update.mjs`) should output progress information in one of these formats:

## Supported Output Formats

### Format 1: Total Count
```
Total: 123
Found 123 results
Total Count: 123
```

### Format 2: Current Count
```
Processing 5
Count: 5
```

### Format 3: Count with Progress (Recommended)
```
Processing 5 of 123
5/123
5 of 123
```

## Example Script Output

```bash
Found 123 results
Processing 1 of 123
Processing 2 of 123
Processing 3 of 123
...
Processing 123 of 123
Completed!
```

## How It Works

1. **Total Count**: Extracted when script outputs "Total: X" or "Found X results"
   - Updates the `total_count` field in the database
   
2. **Current Count**: Extracted from "Processing X" or "Count: X"
   - Updates the `count` field
   - Calculates `progress` percentage automatically
   
3. **Combined Format**: Extracted from "X of Y" or "X/Y"
   - Updates both `count` and `total_count`
   - Calculates `progress` percentage automatically

## Real-time Updates

- The job streams output in real-time (line by line)
- Database is updated as soon as progress information is detected
- `PostNummerStatusUpdated` event is fired to update the UI
- Progress bar in admin panel updates automatically

## Notes

- Use `console.log()` in your Node.js script to output progress
- Each progress update should be on its own line
- The job will parse and update the database in real-time
- Make sure to output total count early in the script execution
