-- Fix Duplicate Batch Numbers Script
-- This script identifies and fixes existing batch records with problematic batch numbers

-- Step 1: Identify problematic batch numbers (N/A format)
SELECT 
    id,
    batch_number,
    item_id,
    quantity,
    created_at
FROM batches 
WHERE batch_number LIKE 'N/A-%' 
   OR batch_number LIKE 'NA-%-%' 
   OR batch_number = 'N/A-251130'
ORDER BY created_at;

-- Step 2: Check for actual duplicates to understand the scope
SELECT 
    batch_number,
    COUNT(*) as duplicate_count,
    STRING_AGG(id::text, ', ') as batch_ids,
    STRING_AGG(item_id::text, ', ') as item_ids
FROM batches 
WHERE batch_number LIKE 'N/A-%' 
   OR batch_number = 'N/A-251130'
GROUP BY batch_number 
HAVING COUNT(*) > 1;

-- Step 3: Generate new unique batch numbers for problematic records
-- This creates unique batch numbers for all records with N/A-251130 or similar patterns

UPDATE batches 
SET batch_number = 'BATCH-' || 
    LPAD(item_id::text, 6, '0') || '-' || 
    TO_CHAR(created_at, 'YYYYMMDD') || '-' || 
    LPAD(id::text, 4, '0')
WHERE batch_number LIKE 'N/A-%' 
   OR batch_number = 'N/A-251130'
   OR batch_number LIKE 'NA-%-%';

-- Step 4: Verify the updates worked
SELECT 
    id,
    batch_number,
    item_id,
    quantity,
    created_at
FROM batches 
WHERE batch_number LIKE 'BATCH-%'
ORDER BY created_at DESC 
LIMIT 10;

-- Step 5: Show final batch number format
SELECT 
    batch_number,
    COUNT(*) as count,
    MIN(created_at) as first_created,
    MAX(created_at) as last_created
FROM batches 
GROUP BY batch_number 
HAVING COUNT(*) > 1
ORDER BY count DESC;