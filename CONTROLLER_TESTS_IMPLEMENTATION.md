# Controller Tests Implementation Plan

## Overview
This document outlines the test strategy for the newly refactored admin controllers.

## Test Files to Create

### 1. UserController Tests
**File**: `tests/Feature/Admin/UserManagement/UserControllerTest.php`

**Test Cases to Implement**:
- `test_admin_can_view_users_index` - Verify admin can access user listing
- `test_admin_can_create_new_user` - Test user creation functionality
- `test_admin_can_update_user` - Test user update functionality
- `test_admin_can_delete_user` - Test user deletion
- `test_admin_can_toggle_user_status` - Test status toggling
- `test_admin_can_reset_user_password` - Test password reset
- `test_admin_can_perform_bulk_user_operations` - Test bulk operations
- `test_admin_can_search_users` - Test user search
- `test_guest_cannot_access_user_management` - Test unauthorized access
- `test_non_admin_cannot_access_user_management` - Test role-based access

### 2. RoleController Tests
**File**: `tests/Feature/Admin/UserManagement/RoleControllerTest.php`

**Test Cases to Implement**:
- `test_admin_can_view_role_overview` - Test role listing
- `test_admin_can_create_new_role` - Test role creation
- `test_admin_can_get_role_details` - Test role details modal
- `test_admin_can_update_role_permissions` - Test permission updates

### 3. ItemController Tests
**File**: `tests/Feature/Admin/MasterData/ItemControllerTest.php`

**Test Cases to Implement**:
- `test_admin_can_view_items_index` - Test item listing
- `test_admin_can_create_new_item` - Test item creation
- `test_admin_can_update_item` - Test item updates
- `test_admin_can_delete_item` - Test item deletion
- `test_admin_can_search_items` - Test item search
- `test_admin_can_filter_items_by_category` - Test category filtering

### 4. CategoryController Tests
**File**: `tests/Feature/Admin/MasterData/CategoryControllerTest.php`

**Test Cases to Implement**:
- `test_admin_can_view_categories_index` - Test category listing
- `test_admin_can_create_new_category` - Test category creation
- `test_admin_can_update_category` - Test category updates
- `test_admin_can_delete_category` - Test category deletion
- `test_admin_can_toggle_category_status` - Test status toggling

### 5. UnitController Tests
**File**: `tests/Feature/Admin/MasterData/UnitControllerTest.php`

**Test Cases to Implement**:
- `test_admin_can_view_units_index` - Test unit listing
- `test_admin_can_create_new_unit` - Test unit creation
- `test_admin_can_update_unit` - Test unit updates
- `test_admin_can_delete_unit` - Test unit deletion

### 6. AuditLogController Tests
**File**: `tests/Feature/Admin/System/AuditLogControllerTest.php`

**Test Cases to Implement**:
- `test_admin_can_view_audit_logs` - Test audit log listing
- `test_admin_can_filter_audit_logs` - Test log filtering
- `test_admin_can_export_audit_logs` - Test log export
- `test_admin_can_export_audit_proof` - Test proof export

### 7. SettingController Tests
**File**: `tests/Feature/Admin/System/SettingControllerTest.php`

**Test Cases to Implement**:
- `test_admin_can_view_settings` - Test settings page
- `test_admin_can_update_settings` - Test settings updates
- `test_admin_can_get_system_health` - Test health check
- `test_admin_can_clear_cache` - Test cache clearing

### 8. DashboardController Tests
**File**: `tests/Feature/Admin/DashboardControllerTest.php`

**Test Cases to Implement**:
- `test_admin_can_view_dashboard` - Test dashboard access
- `test_dashboard_shows_correct_statistics` - Test data accuracy
- `test_dashboard_shows_greeting` - Test dynamic greeting

## Test Infrastructure Setup

### Database Testing
- Use Laravel's RefreshDatabase trait
- Create test factories for all models
- Use SQLite in-memory database for faster tests

### Authentication Testing
- Create admin user factory
- Mock authentication for tests
- Test unauthorized access scenarios

### Test Data Setup
- Create realistic test data
- Use factories to generate test records
- Ensure test isolation and cleanup

## Running Tests

### Individual Test Files
```bash
# Test specific controller
php artisan test tests/Feature/Admin/UserManagement/UserControllerTest.php

# Test all admin tests
php artisan test tests/Feature/Admin/
```

### Test Coverage
```bash
# Generate coverage report
php artisan test --coverage
```

## Performance Considerations

### Controller Instantiation
- Monitor controller instantiation time
- Ensure dependency injection works correctly
- Check for any performance regressions

### Memory Usage
- Monitor memory usage during tests
- Ensure no memory leaks in new controllers
- Clean up test data properly

## Integration Testing

### Route Testing
- Verify all routes work with new controllers
- Test route parameter binding
- Ensure proper middleware execution

### Service Integration
- Test service layer integration
- Verify service method calls
- Test error handling scenarios

## Success Criteria

- [ ] All admin routes accessible
- [ ] CRUD operations work correctly
- [ ] Authorization and authentication working
- [ ] Service layer integration functional
- [ ] No performance regressions
- [ ] Test coverage > 80%

## Next Steps

1. Create test files for each controller
2. Implement comprehensive test cases
3. Run tests to verify functionality
4. Create integration test suite
5. Set up continuous integration

## Test Examples

### UserController Test Template
```php
<?php

namespace Tests\Feature\Admin\UserManagement;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->adminUser = User::factory()->create(['role' => 'admin']);
    }

    public function test_admin_can_view_users()
    {
        $this->actingAs($this->adminUser);
        $response = $this->get('/admin/users');
        $response->assertStatus(200);
    }
}
```

### Integration Test Template
```php
<?php

namespace Tests\Feature\Admin;

use Tests\TestCase;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;

class UserControllerIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_creation_service_integration()
    {
        // Mock service layer
        $userService = Mockery::mock(UserService::class);
        $this->app->instance(UserService::class, $userService);
        
        $userService->shouldReceive('createUser')
            ->once()
            ->andReturn(new User());
            
        // Test controller integration
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        $response = $this->post('/admin/users', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'role' => 'employee'
        ]);
        
        $response->assertStatus(200);
    }
}
```
