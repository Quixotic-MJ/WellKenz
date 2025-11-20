<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserProfile;
use App\Models\AuditLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Exception;

class UserService
{
    /**
     * Get paginated users with filters.
     *
     * @param array $filters
     * @param int $perPage
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getPaginatedUsers(array $filters = [], int $perPage = 10)
    {
        $query = User::with('profile');

        // Search functionality
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                  ->orWhere('email', 'ilike', "%{$search}%")
                  ->orWhereHas('profile', function ($profileQuery) use ($search) {
                      $profileQuery->where('employee_id', 'ilike', "%{$search}%");
                  });
            });
        }

        // Role filter
        if (!empty($filters['role'])) {
            $query->where('role', $filters['role']);
        }

        // Status filter
        if (!empty($filters['status'])) {
            $isActive = $filters['status'] === 'active';
            $query->where('is_active', $isActive);
        }

        return $query->orderBy('name')->paginate($perPage);
    }

    /**
     * Get role statistics.
     *
     * @return array
     */
    public function getRoleStatistics(): array
    {
        return User::selectRaw('role, COUNT(*) as count')
            ->groupBy('role')
            ->pluck('count', 'role')
            ->toArray();
    }

    /**
     * Create a new user.
     *
     * @param array $data
     * @return User
     * @throws Exception
     */
    public function createUser(array $data): User
    {
        try {
            DB::beginTransaction();

            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $data['password'],
                'role' => $data['role'],
                'is_active' => true
            ]);

            // Create user profile
            UserProfile::create([
                'user_id' => $user->id,
                'employee_id' => $data['employee_id'] ?? null,
                'phone' => $data['phone'] ?? null,
                'department' => $data['department'] ?? null,
                'position' => $data['position'] ?? null,
                'hire_date' => now()
            ]);

            // Log the user creation
            $this->logAudit('users', $user->id, 'CREATE', null, [
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role
            ]);

            DB::commit();

            return $user;

        } catch (Exception $e) {
            DB::rollback();
            Log::error('Error creating user: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Update user information.
     *
     * @param User $user
     * @param array $data
     * @return User
     * @throws Exception
     */
    public function updateUser(User $user, array $data): User
    {
        try {
            DB::beginTransaction();

            $oldValues = $user->toArray();

            $user->update([
                'name' => $data['name'],
                'email' => $data['email'],
                'role' => $data['role']
            ]);

            // Update or create profile
            $profileData = [
                'phone' => $data['phone'] ?? null,
                'employee_id' => $data['employee_id'] ?? null,
                'department' => $data['department'] ?? null,
                'position' => $data['position'] ?? null
            ];

            if ($user->profile) {
                $user->profile->update($profileData);
            } else {
                $profileData['user_id'] = $user->id;
                UserProfile::create($profileData);
            }

            // Log the update
            $this->logAudit('users', $user->id, 'UPDATE', $oldValues, $user->fresh()->toArray());

            DB::commit();

            return $user->fresh();

        } catch (Exception $e) {
            DB::rollback();
            Log::error('Error updating user: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Toggle user active/inactive status.
     *
     * @param User $user
     * @return User
     * @throws Exception
     */
    public function toggleUserStatus(User $user): User
    {
        try {
            $user->update(['is_active' => !$user->is_active]);

            $this->logAudit('users', $user->id, 'UPDATE', null, [
                'is_active' => $user->is_active
            ]);

            return $user->fresh();

        } catch (Exception $e) {
            Log::error('Error toggling user status: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Delete a user.
     *
     * @param User $user
     * @return bool
     * @throws Exception
     */
    public function deleteUser(User $user): bool
    {
        try {
            DB::beginTransaction();

            $this->logAudit('users', $user->id, 'DELETE', $user->toArray(), null);

            $user->delete();

            DB::commit();

            return true;

        } catch (Exception $e) {
            DB::rollback();
            Log::error('Error deleting user: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Search users.
     *
     * @param string $query
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function searchUsers(string $query, int $limit = 10)
    {
        if (empty($query)) {
            return collect([]);
        }

        return User::with('profile')
            ->where(function ($q) use ($query) {
                $q->where('name', 'ilike', "%{$query}%")
                  ->orWhere('email', 'ilike', "%{$query}%")
                  ->orWhereHas('profile', function ($profileQuery) use ($query) {
                      $profileQuery->where('employee_id', 'ilike', "%{$query}%");
                  });
            })
            ->limit($limit)
            ->get(['id', 'name', 'email', 'role', 'is_active']);
    }

    /**
     * Get user with profile for editing.
     *
     * @param User $user
     * @return array
     */
    public function getUserForEdit(User $user): array
    {
        $user->load('profile');
        
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'employee_id' => $user->profile?->employee_id,
            'phone' => $user->profile?->phone,
            'department' => $user->profile?->department,
            'position' => $user->profile?->position,
        ];
    }

    /**
     * Reset user password.
     *
     * @param User $user
     * @return string The new temporary password
     * @throws Exception
     */
    public function resetUserPassword(User $user): string
    {
        try {
            $newPassword = $this->generateTemporaryPassword();
            
            $user->update([
                'password' => $newPassword,
                'password_reset_token' => null,
                'password_reset_expires' => null
            ]);

            $this->logAudit('users', $user->id, 'UPDATE', 
                ['password_reset' => false], 
                ['password_reset' => true]
            );

            return $newPassword;

        } catch (Exception $e) {
            Log::error('Error resetting password: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Change user password.
     *
     * @param User $user
     * @param string|null $currentPassword
     * @param string $newPassword
     * @param bool $isAdminChange
     * @return bool
     * @throws Exception
     */
    public function changeUserPassword(User $user, ?string $currentPassword, string $newPassword, bool $isAdminChange = false): bool
    {
        try {
            // For self-service password change, verify current password
            if (!$isAdminChange && $currentPassword) {
                if (!Hash::check($currentPassword, $user->password_hash)) {
                    throw new Exception('Current password is incorrect.');
                }
            }
            
            $user->update(['password' => $newPassword]);
            
            $this->logAudit('users', $user->id, 'UPDATE', null, [
                'password_changed_by_admin' => $isAdminChange
            ]);

            return true;

        } catch (Exception $e) {
            Log::error('Error changing password: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Perform bulk operations on users.
     *
     * @param array $userIds
     * @param string $operation
     * @return int Number of affected users
     * @throws Exception
     */
    public function bulkUserOperations(array $userIds, string $operation): int
    {
        try {
            DB::beginTransaction();
            
            $affectedCount = 0;
            
            switch ($operation) {
                case 'activate':
                case 'deactivate':
                    $isActive = $operation === 'activate';
                    $affectedCount = User::whereIn('id', $userIds)->update(['is_active' => $isActive]);
                    break;
                    
                case 'delete':
                    $affectedCount = User::whereIn('id', $userIds)->delete();
                    break;
            }
            
            // Log the bulk operation
            $this->logAudit('users', null, 'UPDATE', null, [
                'bulk_operation' => $operation,
                'affected_users' => $userIds
            ]);
            
            DB::commit();

            return $affectedCount;

        } catch (Exception $e) {
            DB::rollback();
            Log::error('Error performing bulk operation: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Generate a temporary password.
     *
     * @return string
     */
    private function generateTemporaryPassword(): string
    {
        return substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!@#$%^&*'), 0, 12);
    }

    /**
     * Log audit trail.
     *
     * @param string $tableName
     * @param int|null $recordId
     * @param string $action
     * @param array|null $oldValues
     * @param array|null $newValues
     * @return void
     */
    private function logAudit(string $tableName, ?int $recordId, string $action, ?array $oldValues, ?array $newValues): void
    {
        try {
            AuditLog::create([
                'table_name' => $tableName,
                'record_id' => $recordId,
                'action' => $action,
                'old_values' => $oldValues,
                'new_values' => $newValues,
                'user_id' => auth()->id()
            ]);
        } catch (Exception $e) {
            Log::error('Error logging audit: ' . $e->getMessage());
        }
    }
}
