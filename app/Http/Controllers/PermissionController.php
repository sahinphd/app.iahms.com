<?php

namespace App\Http\Controllers;

use App\Models\RolePermission;
use App\Models\User;
use App\Models\UserPermission;
use Illuminate\Http\Request;

class PermissionController extends Controller
{
    // List of all system permissions
    protected $systemPermissions = [
        'manage_users' => 'Manage Student Approvals, Create Accounts, Edit Roles',
        'manage_role_permissions' => 'Manage Dynamic Role Permissions Grid',
        'manage_classes' => 'Create & Delete Classes, Assign Students/Teachers to Classes',
        'create_courses' => 'Create new courses',
        'edit_courses' => 'Edit courses',
        'delete_courses' => 'Delete courses',
        'publish_courses' => 'Publish and unpublish courses',
        'manage_syllabus' => 'Manage modules, upload lectures (videos) & study materials (PDFs)',
        'manage_live_classes' => 'Schedule and cancel live interactive classes',
        'manage_enrollments' => 'Approve or reject student course enrollments',
        'view_reports' => 'View usage statistics, video watch logs, download logs, and login history',
        'manage_teacher_profiles' => 'Create, Edit, and Manage Teacher Profiles & Approvals',
        'manage_student_profiles' => 'Create, Edit, and Manage Student Profiles & Approvals',
    ];

    /**
     * Display the permissions management matrix.
     */
    public function index()
    {
        $permissions = $this->systemPermissions;
        
        // Fetch all role permission records
        $dbPermissions = RolePermission::all();

        // Build a lookup map: $map[role][permission] = bool
        $permissionsMap = [];
        foreach ($dbPermissions as $record) {
            $permissionsMap[$record->role][$record->permission] = $record->is_allowed;
        }

        $roles = ['teacher', 'student'];

        // Fetch non-admin users to configure overrides
        $users = User::where('role', '!=', 'admin')->orderBy('name')->get();

        return view('admin.permissions.index', compact('permissions', 'permissionsMap', 'roles', 'users'));
    }

    /**
     * Update/toggle a permission for a role.
     */
    public function toggle(Request $request)
    {
        $request->validate([
            'role' => 'required|in:teacher,student',
            'permission' => 'required|string',
            'is_allowed' => 'required|boolean',
        ]);

        if (!array_key_exists($request->permission, $this->systemPermissions)) {
            return response()->json(['error' => 'Invalid permission'], 400);
        }

        RolePermission::updateOrCreate(
            ['role' => $request->role, 'permission' => $request->permission],
            ['is_allowed' => $request->is_allowed]
        );

        return response()->json(['success' => true]);
    }

    /**
     * Display permissions management for a specific user.
     */
    public function userPermissions(User $user)
    {
        // Admins have absolute bypass, no overrides needed
        if ($user->isAdmin()) {
            return redirect()->route('admin.permissions.index')
                ->with('error', 'Administrators have full bypass rights. Individual overrides cannot be applied.');
        }

        $permissions = $this->systemPermissions;

        // Fetch user explicit overrides
        $userOverrides = UserPermission::where('user_id', $user->id)->get()->keyBy('permission');

        // Fetch role permissions for this user's role to determine inherited state
        $rolePermissions = RolePermission::where('role', $user->role)->get()->keyBy('permission');

        return view('admin.permissions.user', compact('user', 'permissions', 'userOverrides', 'rolePermissions'));
    }

    /**
     * Update/toggle a permission override for a user.
     */
    public function userToggle(Request $request, User $user)
    {
        $request->validate([
            'permission' => 'required|string',
            'value' => 'required|in:inherit,allow,deny',
        ]);

        if (!array_key_exists($request->permission, $this->systemPermissions)) {
            return response()->json(['error' => 'Invalid permission'], 400);
        }

        if ($user->isAdmin()) {
            return response()->json(['error' => 'Cannot override admin permissions'], 400);
        }

        if ($request->value === 'inherit') {
            // Remove override
            UserPermission::where('user_id', $user->id)
                ->where('permission', $request->permission)
                ->delete();
        } else {
            // Update or create override
            UserPermission::updateOrCreate(
                ['user_id' => $user->id, 'permission' => $request->permission],
                ['is_allowed' => $request->value === 'allow']
            );
        }

        return response()->json(['success' => true]);
    }
}
