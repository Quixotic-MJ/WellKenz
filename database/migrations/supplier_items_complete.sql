-- ============================================================================
-- COMPLETE SUPPLIER-ITEMS CONNECTION SCRIPT
-- Connects all items with appropriate suppliers
-- ============================================================================

-- Clear existing incomplete connections but keep working ones
DELETE FROM supplier_items WHERE id NOT IN (
    SELECT MIN(id) FROM supplier_items GROUP BY supplier_id, item_id
);

-- ============================================================================
-- CATEGORY-BASED SUPPLIER MAPPING
-- ============================================================================

-- FLOUR & GRAINS (Category 1) -> Manila Flour Mills (SUP001), Golden Grains (SUP004)
INSERT INTO supplier_items (supplier_id, item_id, supplier_item_code, unit_price, minimum_order_quantity, lead_time_days, is_preferred) VALUES
(1, 1, 'FLR-AP-001', 42.00, 25.000, 2, true),    -- All-Purpose Flour
(1, 2, 'FLR-BR-001', 49.00, 25.000, 2, true),    -- Bread Flour
(1, 3, 'FLR-CK-001', 45.00, 20.000, 2, true),    -- Cake Flour
(4, 4, 'FLR-WW-ORG', 62.00, 15.000, 3, true),    -- Whole Wheat Flour
(4, 5, 'FLR-RY-001', 72.00, 10.000, 3, false),   -- Rye Flour
(1, 6, 'FLR-CS-001', 38.00, 15.000, 2, false),   -- Corn Starch
(1, 7, 'FLR-RC-001', 53.00, 15.000, 3, false),   -- Rice Flour
(1, 8, 'FLR-OA-001', 58.00, 12.000, 3, false),   -- Oat Flour
(1, 9, 'FLR-SE-001', 56.00, 12.000, 3, false),   -- Semolina
(30, 10, 'FLR-BK-001', 82.00, 8.000, 5, false),  -- Buckwheat Flour
(30, 99, 'FLR-BULK-001', 36.00, 500.000, 7, true); -- Bulk Flour Mix

-- DAIRY PRODUCTS (Category 2) -> Fresh Dairy Corp (SUP002), Frozen Goods (SUP020)
INSERT INTO supplier_items (supplier_id, item_id, supplier_item_code, unit_price, minimum_order_quantity, lead_time_days, is_preferred) VALUES
(2, 11, 'DRY-MLK-FRESH', 60.00, 20.000, 1, true),   -- Fresh Milk
(2, 12, 'DRY-BUTTER-UNS', 300.00, 8.000, 1, true),  -- Butter Unsalted
(2, 13, 'DRY-BUTTER-SAL', 290.00, 8.000, 1, false), -- Butter Salted
(2, 14, 'DRY-CREAM-HVY', 175.00, 12.000, 1, true),  -- Heavy Cream
(2, 15, 'DRY-CHEESE-CRM', 275.00, 6.000, 2, true),   -- Cream Cheese
(2, 16, 'DRY-YOGURT-PLN', 92.00, 15.000, 1, false), -- Yogurt Plain
(2, 17, 'DRY-BUTTERMILK', 72.00, 10.000, 1, false), -- Buttermilk
(2, 18, 'DRY-MOZZARELLA', 335.00, 5.000, 2, false), -- Mozzarella Cheese
(2, 100, 'DRY-CHSE-FRSH', 375.00, 4.000, 1, true),  -- Fresh Cream Cheese
(2, 123, 'DRY-CHSE-SOFT', 445.00, 3.000, 1, false), -- Soft Cheese
(2, 127, 'DRY-YOGURT-LIV', 315.00, 6.000, 1, true); -- Live Culture Yogurt

-- SWEETENERS (Category 3) -> Sweet Sugar Co (SUP003), Premium Imports (SUP022)
INSERT INTO supplier_items (supplier_id, item_id, supplier_item_code, unit_price, minimum_order_quantity, lead_time_days, is_preferred) VALUES
(3, 19, 'SWT-SUGAR-WHT', 52.00, 20.000, 2, true),   -- White Sugar
(3, 20, 'SWT-SUGAR-BRN', 62.00, 15.000, 2, true),   -- Brown Sugar
(3, 21, 'SWT-SUGAR-PWD', 67.00, 12.000, 2, false),  -- Powdered Sugar
(3, 22, 'SWT-HONEY-PUR', 245.00, 8.000, 3, true),   -- Honey
(3, 23, 'SWT-SYRUP-MAP', 445.00, 5.000, 4, false),  -- Maple Syrup
(3, 24, 'SWT-SYRUP-CRN', 115.00, 10.000, 2, false), -- Corn Syrup
(22, 101, 'SWT-SYRUP-AGV', 315.00, 6.000, 5, true), -- Agave Syrup
(30, 106, 'SWT-SUGAR-BLK', 46.00, 1000.000, 7, true); -- Bulk Sugar

-- FINISHED PRODUCTS (Category 12) -> Internal production (no external suppliers needed)

-- PACKAGING MATERIALS (Category 10) -> Packaging Pros (SUP009), High Volume (SUP030)
INSERT INTO supplier_items (supplier_id, item_id, supplier_item_code, unit_price, minimum_order_quantity, lead_time_days, is_preferred) VALUES
(9, 102, 'PKG-BREAD-BAG-SM', 0.48, 200.000, 3, true),   -- Bread Bag Small
(9, 103, 'PKG-BREAD-BAG-LG', 0.72, 150.000, 3, true),   -- Bread Bag Large
(9, 104, 'PKG-PASTRY-BOX-SM', 3.45, 100.000, 5, true),  -- Pastry Box Small
(9, 105, 'PKG-PASTRY-BOX-LG', 4.95, 80.000, 5, false),  -- Pastry Box Large
(9, 106, 'PKG-CAKE-BOX-SP', 7.85, 50.000, 7, true),     -- Cake Box
(9, 107, 'PKG-PAPER-BAG-BR', 1.15, 300.000, 3, false),  -- Paper Bag
(30, 114, 'PKG-BULK-MATL', 0.24, 2000.000, 7, true);    -- Bulk Packaging

-- CLEANING SUPPLIES (Category 11) -> Clean Solutions (SUP010)
INSERT INTO supplier_items (supplier_id, item_id, supplier_item_code, unit_price, minimum_order_quantity, lead_time_days, is_preferred) VALUES
(10, 108, 'CLN-SANITIZE-FOOD', 175.00, 5.000, 2, true),   -- Food Safe Sanitizer
(10, 109, 'CLN-FLOOR-INDUS', 215.00, 3.000, 2, true),     -- Floor Cleaner
(10, 110, 'CLN-DISH-SOAP', 92.00, 8.000, 2, false),       -- Dish Soap
(10, 111, 'CLN-HAND-SOAP', 115.00, 6.000, 2, false),      -- Hand Soap
(10, 112, 'CLN-GLOVES-DISP', 345.00, 10.000, 3, true),    -- Disposable Gloves
(10, 125, 'CLN-CLEANER-IND', 445.00, 5.000, 3, true);     -- Industrial Cleaner

-- CHOCOLATE PRODUCTS (Category 16) -> Choco Masters Inc (SUP011), Premium Imports (SUP022)
INSERT INTO supplier_items (supplier_id, item_id, supplier_item_code, unit_price, minimum_order_quantity, lead_time_days, is_preferred) VALUES
(11, 51, 'CHC-COA-POWDER', 260.00, 3.000, 3, true),   -- Cocoa Powder
(11, 52, 'CHC-DRK-70PCT', 295.00, 4.000, 3, true),    -- Dark Chocolate
(11, 53, 'CHC-MLK-001', 275.00, 4.000, 3, false),     -- Milk Chocolate
(11, 54, 'CHC-WHT-001', 305.00, 3.000, 3, false),     -- White Chocolate
(11, 55, 'CHC-CHIPS-SS', 345.00, 5.000, 3, true),     -- Chocolate Chips
(11, 56, 'CHC-SYRUP-001', 175.00, 8.000, 2, false),   -- Chocolate Syrup
(11, 57, 'CHC-BUTTER-001', 415.00, 2.000, 4, true),   -- Cocoa Butter
(11, 58, 'CHC-SPRINKLES', 235.00, 6.000, 2, false),   -- Chocolate Sprinkles
(22, 102, 'CHC-ORIGIN-SNG', 845.00, 1.000, 6, true);  -- Single Origin Chocolate

-- EGGS & EGG PRODUCTS (Category 17) -> Eggcellent Farms (SUP012)
INSERT INTO supplier_items (supplier_id, item_id, supplier_item_code, unit_price, minimum_order_quantity, lead_time_days, is_preferred) VALUES
(12, 59, 'EGG-FRESH-LRG', 10.50, 30.000, 1, true),    -- Fresh Eggs
(12, 60, 'EGG-WHITES-PAS', 148.00, 5.000, 2, true),   -- Egg Whites
(12, 61, 'EGG-YOLKS-PAS', 178.00, 4.000, 2, false),   -- Egg Yolks
(12, 62, 'EGG-POWDER-WHOLE', 375.00, 3.000, 3, false), -- Whole Egg Powder
(12, 63, 'EGG-POWDER-WHTE', 445.00, 2.000, 3, true),  -- Egg White Powder
(12, 64, 'EGG-LIQUID-001', 118.00, 8.000, 1, false),  -- Liquid Eggs
(12, 125, 'EGG-DUCK-001', 24.00, 20.000, 1, true);    -- Duck Eggs

-- NUTS & DRIED FRUITS (Category 18) -> Nutty Delights (SUP007), Nutty World (SUP013)
INSERT INTO supplier_items (supplier_id, item_id, supplier_item_code, unit_price, minimum_order_quantity, lead_time_days, is_preferred) VALUES
(7, 65, 'NUT-ALMNDS-WHOLE', 450.00, 3.000, 4, true),  -- Almonds Whole
(7, 66, 'NUT-WLNUTS-PIECES', 400.00, 3.000, 4, true), -- Walnuts Pieces
(7, 67, 'NUT-CASHEWS-RAW', 510.00, 2.000, 4, false),  -- Cashews Raw
(7, 68, 'NUT-PECANS-HVS', 575.00, 2.000, 5, false),   -- Pecans Halves
(7, 69, 'NUT-HAZELNUTS', 455.00, 2.000, 4, false),    -- Hazelnuts
(7, 70, 'NUT-RAISINS-THO', 115.00, 8.000, 3, true),   -- Raisins
(7, 71, 'NUT-CRNBERRY-SWT', 175.00, 6.000, 3, false), -- Dried Cranberries
(7, 72, 'NUT-DATES-MDJL', 315.00, 4.000, 4, true),    -- Dates
(7, 73, 'NUT-APRCOTS-DRY', 235.00, 5.000, 3, false),  -- Dried Apricots
(7, 74, 'NUT-PRUNES-PIT', 185.00, 6.000, 3, false),   -- Prunes
(13, 103, 'NUT-MACADAMIA', 1195.00, 1.000, 6, true);   -- Macadamia Nuts

-- FOOD COLORS & FLAVORS (Category 19) -> Flavor Fusion (SUP014), Spice Masters (SUP006)
INSERT INTO supplier_items (supplier_id, item_id, supplier_item_code, unit_price, minimum_order_quantity, lead_time_days, is_preferred) VALUES
(14, 75, 'FCF-VANILLA-PURE', 800.00, 1.000, 3, true),  -- Vanilla Extract
(14, 76, 'FCF-ALMOND-PURE', 775.00, 1.000, 3, false),  -- Almond Extract
(14, 77, 'FCF-LEMON-NAT', 715.00, 1.000, 3, false),    -- Lemon Extract
(14, 78, 'FCF-COLOR-RED', 145.00, 3.000, 2, true),     -- Red Food Color
(14, 79, 'FCF-COLOR-BLU', 145.00, 3.000, 2, false),    -- Blue Food Color
(14, 80, 'FCF-COLOR-GRN', 145.00, 3.000, 2, false),    -- Green Food Color
(14, 81, 'FCF-COLOR-YEL', 145.00, 3.000, 2, false),    -- Yellow Food Color
(14, 82, 'FCF-BUTTER-FLAV', 375.00, 2.000, 3, true),   -- Butter Flavor
(6, 108, 'FCF-TRUFFLE-OIL', 1795.00, 0.500, 7, true);  -- Truffle Oil

-- DECORATIONS & TOPPINGS (Category 20) -> Decor Delights (SUP015), Premium Imports (SUP022)
INSERT INTO supplier_items (supplier_id, item_id, supplier_item_code, unit_price, minimum_order_quantity, lead_time_days, is_preferred) VALUES
(15, 83, 'DEC-SPRINKLES-RNBW', 115.00, 6.000, 2, true),  -- Rainbow Sprinkles
(15, 84, 'DEC-SPRINKLES-CHC', 105.00, 6.000, 2, false),  -- Chocolate Sprinkles
(15, 85, 'DEC-FONDANT-ROL', 275.00, 4.000, 3, true),     -- Fondant
(15, 86, 'DEC-ICING-ROYAL', 315.00, 3.000, 3, false),    -- Royal Icing
(15, 87, 'DEC-GLITTER-GLD', 445.00, 1.000, 4, true),     -- Edible Glitter
(15, 88, 'DEC-PEARL-DUST', 515.00, 1.000, 4, false),     -- Pearl Dust
(15, 89, 'DEC-CANDY-EYES', 82.00, 10.000, 2, true),      -- Candy Eyes
(15, 90, 'DEC-SUGAR-FLOWERS', 175.00, 5.000, 3, false),  -- Sugar Flowers
(22, 104, 'DEC-GOLD-LEAF', 2495.00, 0.100, 10, true),    -- Gold Leaf
(22, 111, 'DEC-CAVIAR-PRLS', 3195.00, 0.200, 8, false);  -- Caviar Pearls

-- YEAST & FERMENTATION (Category 21) -> Yeast Experts (SUP016)
INSERT INTO supplier_items (supplier_id, item_id, supplier_item_code, unit_price, minimum_order_quantity, lead_time_days, is_preferred) VALUES
(16, 87, 'YST-DRY-ACTIVE', 44.00, 8.000, 2, true),      -- Active Dry Yeast
(16, 88, 'YST-INSTANT-001', 49.00, 6.000, 2, true),      -- Instant Yeast
(16, 89, 'YST-FRESH-001', 34.00, 5.000, 1, false),       -- Fresh Yeast
(16, 90, 'YST-SOUR-STARTR', 24.00, 2.000, 1, true),      -- Sourdough Starter
(16, 91, 'YST-NUTRIENT-001', 118.00, 1.000, 3, false),   -- Yeast Nutrient
(16, 110, 'YST-SOUR-CULTUR', 148.00, 0.500, 2, true);    -- Sourdough Culture

-- SALT & SEASONINGS (Category 22) -> Salt & Spice Co (SUP017)
INSERT INTO supplier_items (supplier_id, item_id, supplier_item_code, unit_price, minimum_order_quantity, lead_time_days, is_preferred) VALUES
(17, 93, 'SLT-TABLE-FINE', 14.00, 20.000, 2, true),     -- Table Salt
(17, 94, 'SLT-SEA-COARSE', 24.00, 10.000, 2, true),     -- Sea Salt
(17, 95, 'SLT-HIMALAYAN-PNK', 44.00, 5.000, 3, false),  -- Himalayan Salt
(17, 96, 'SLT-CINNAMON-GRD', 175.00, 3.000, 3, true),    -- Cinnamon Ground
(17, 97, 'SLT-NUTMEG-GRD', 215.00, 2.000, 4, false),   -- Nutmeg Ground
(17, 98, 'SLT-ALLSPICE-GRD', 185.00, 2.000, 4, false), -- Allspice
(17, 124, 'SLT-ROCK-INDUS', 7.50, 50.000, 2, true);     -- Rock Salt

-- BEVERAGES (Category 14) -> Beverage Source (SUP019)
INSERT INTO supplier_items (supplier_id, item_id, supplier_item_code, unit_price, minimum_order_quantity, lead_time_days, is_preferred) VALUES
(19, 115, 'BEV-COFFEE-ARAB', 675.00, 5.000, 5, true),     -- Coffee Beans
(19, 116, 'BEV-TEA-PREM', 415.00, 3.000, 4, false),       -- Tea Selection
(19, 117, 'BEV-JUICE-CONC', 275.00, 4.000, 3, true);      -- Juice Concentrate

-- FROZEN GOODS (Category 15) -> Frozen Goods Ltd (SUP020)
INSERT INTO supplier_items (supplier_id, item_id, supplier_item_code, unit_price, minimum_order_quantity, lead_time_days, is_preferred) VALUES
(20, 118, 'FRZ-BERRIES-MIX', 175.00, 8.000, 2, true),     -- Frozen Berries
(20, 119, 'FRZ-DOUGH-PREM', 92.00, 10.000, 2, true),      -- Frozen Dough
(20, 120, 'FRZ-VEG-MIX', 115.00, 6.000, 2, false);        -- Frozen Vegetables

-- EQUIPMENT & TOOLS (Category 13) -> Equipment Supplier (SUP032)
INSERT INTO supplier_items (supplier_id, item_id, supplier_item_code, unit_price, minimum_order_quantity, lead_time_days, is_preferred) VALUES
(32, 121, 'EQP-SCALE-DIGIT', 2485.00, 1.000, 10, true),   -- Digital Scale
(32, 122, 'EQP-BOWL-SET-SS', 835.00, 2.000, 7, true),     -- Mixing Bowl Set
(32, 123, 'EQP-SHEETS-BAKE', 175.00, 5.000, 5, false);    -- Baking Sheets

-- FRUITS (Category 8) -> Fruit Paradise (SUP008), Local Organic Farm (SUP023)
INSERT INTO supplier_items (supplier_id, item_id, supplier_item_code, unit_price, minimum_order_quantity, lead_time_days, is_preferred) VALUES
(8, 122, 'FRT-STRAWBERRY', 445.00, 2.000, 1, true),       -- Fresh Strawberries
(23, 123, 'FRT-BASIL-FRESH', 795.00, 1.000, 1, true);     -- Fresh Basil

-- ============================================================================
-- ADD SECONDARY SUPPLIERS FOR COMPETITIVE PRICING
-- ============================================================================

-- Secondary suppliers for price competition
INSERT INTO supplier_items (supplier_id, item_id, supplier_item_code, unit_price, minimum_order_quantity, lead_time_days, is_preferred) VALUES
-- Secondary flour supplier
(30, 1, 'FLR-AP-ALT', 43.50, 50.000, 4, false),
(30, 2, 'FLR-BR-ALT', 50.50, 50.000, 4, false),
-- Secondary dairy supplier
(20, 11, 'DRY-MLK-ALT', 62.00, 25.000, 2, false),
-- Secondary sugar supplier
(30, 19, 'SWT-SUGAR-ALT', 53.00, 100.000, 5, false),
-- Secondary egg supplier
(35, 59, 'EGG-FRESH-ALT', 11.00, 50.000, 2, false),
-- Secondary packaging supplier
(35, 102, 'PKG-BAG-ALT', 0.52, 500.000, 7, false);

-- ============================================================================
-- VALIDATION QUERIES
-- ============================================================================

-- Check connection statistics
SELECT 
    'Total Active Items' as metric,
    COUNT(*) as count
FROM items 
WHERE is_active = true
UNION ALL
SELECT 
    'Total Supplier-Item Connections' as metric,
    COUNT(*) as count
FROM supplier_items 
WHERE is_active = true
UNION ALL
SELECT 
    'Preferred Suppliers' as metric,
    COUNT(*) as count
FROM supplier_items 
WHERE is_preferred = true AND is_active = true
UNION ALL
SELECT 
    'Items Without Suppliers' as metric,
    COUNT(*) as count
FROM items i 
WHERE i.is_active = true 
AND i.id NOT IN (SELECT item_id FROM supplier_items WHERE is_active = true);

-- Show coverage by category
SELECT 
    c.name as category,
    COUNT(DISTINCT i.id) as total_items,
    COUNT(DISTINCT si.item_id) as items_with_suppliers,
    CASE 
        WHEN COUNT(DISTINCT i.id) > 0 THEN 
            ROUND(CAST(COUNT(DISTINCT si.item_id) AS DECIMAL) / COUNT(DISTINCT i.id) * 100, 1)
        ELSE 0 
    END as coverage_percent
FROM categories c
LEFT JOIN items i ON c.id = i.category_id AND i.is_active = true
LEFT JOIN supplier_items si ON i.id = si.item_id AND si.is_active = true
GROUP BY c.id, c.name
ORDER BY c.name;