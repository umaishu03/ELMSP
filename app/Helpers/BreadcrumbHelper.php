<?php

namespace App\Helpers;

class BreadcrumbHelper
{
    /**
     * Generate breadcrumbs based on current route
     */
    public static function generate()
    {
        $routeName = request()->route()->getName();
        $breadcrumbs = [];
        
        // Always start with Dashboard
        $breadcrumbs[] = [
            'name' => 'Dashboard',
            'url' => self::getDashboardUrl(),
            'active' => false
        ];
        
        // Generate breadcrumbs based on route
        switch ($routeName) {
            case 'admin.dashboard':
                // Dashboard is already added, make it active
                $breadcrumbs[0]['active'] = true;
                break;
                
            case 'staff.dashboard':
                // Dashboard is already added, make it active
                $breadcrumbs[0]['active'] = true;
                break;
                
            case 'profile.show':
            case 'profile.update':
                $breadcrumbs[] = [
                    'name' => 'Profile',
                    'url' => route('profile.show'),
                    'active' => true
                ];
                break;
                
            case 'admin.manage-staff':
                $breadcrumbs[] = [
                    'name' => 'Staff',
                    'url' => '#',
                    'active' => false
                ];
                $breadcrumbs[] = [
                    'name' => 'Staff Management',
                    'url' => route('admin.manage-staff'),
                    'active' => true
                ];
                break;
            case 'admin.staff-timetable':
                $breadcrumbs[] = [
                    'name' => 'Staff',
                    'url' => '#',
                    'active' => false
                ];
                $breadcrumbs[] = [
                    'name' => 'Staff Timetable',
                    'url' => route('admin.staff-timetable'),
                    'active' => true
                ];
                break;

            case 'admin.staff-leave-status':
                $breadcrumbs[] = [
                    'name' => 'Staff',
                    'url' => '#',
                    'active' => false
                ];
                $breadcrumbs[] = [
                    'name' => 'Staff Leave Status',
                    'url' => route('admin.staff-leave-status'),
                    'active' => true
                ];
                break;
            
            case 'admin.payroll':
                $breadcrumbs[] = [
                    'name' => 'Payroll',
                    'url' => '#',
                    'active' => false
                ];
                $breadcrumbs[] = [
                    'name' => 'Calculation',
                    'url' => route('admin.payroll'),
                    'active' => true
                ];
                break;

            case 'admin.payslip':
                $breadcrumbs[] = [
                    'name' => 'Payroll',
                    'url' => '#',
                    'active' => false
                ];
                $breadcrumbs[] = [
                    'name' => 'Payslip Staff',
                    'url' => route('admin.payslip'),
                    'active' => true
                ];
                break;
            
            case 'staff.timetable':
                $breadcrumbs[] = [
                    'name' => 'My Timetable',
                    'url' => route('staff.timetable'),
                    'active' => true
                ];
                break;

            case 'staff.claimOt':
                $breadcrumbs[] = [
                    'name' => 'Overtime',
                    'url' => '#',
                    'active' => false
                ];
                $breadcrumbs[] = [
                    'name' => 'Claim',
                    'url' => route('staff.claimOt'),
                    'active' => true
                ];
                break;

            case 'staff.applyOt':
                $breadcrumbs[] = [
                    'name' => 'Overtime',
                    'url' => '#',
                    'active' => false
                ];
                $breadcrumbs[] = [
                    'name' => 'Apply',
                    'url' => route('staff.applyOt'),
                    'active' => true
                ];
                break;

            case 'staff.statusOt':
                $breadcrumbs[] = [
                    'name' => 'Overtime',
                    'url' => '#',
                    'active' => false
                ];
                $breadcrumbs[] = [
                    'name' => 'Status',
                    'url' => route('staff.statusOt'),
                    'active' => true
                ];
                break;

            case 'staff.leave-status':
                $breadcrumbs[] = [
                    'name' => 'Leave',
                    'url' => '#',
                    'active' => false
                ];
                
            default:
                // For unknown routes, try to extract from route name
                $segments = explode('.', $routeName);
                if (count($segments) > 1) {
                    $lastSegment = end($segments);
                    $breadcrumbs[] = [
                        'name' => ucfirst(str_replace('-', ' ', $lastSegment)),
                        'url' => '#',
                        'active' => true
                    ];
                }
                break;
        }
        
        return $breadcrumbs;
    }
    
    /**
     * Get the appropriate dashboard URL based on user role
     */
    private static function getDashboardUrl()
    {
        if (auth()->check()) {
            $user = auth()->user();
            if ($user->isAdmin()) {
                return route('admin.dashboard');
            } elseif ($user->isStaff()) {
                return route('staff.dashboard');
            }
        }
        
        return route('login');
    }
    
    /**
     * Render breadcrumbs HTML
     */
    public static function render()
    {
        $breadcrumbs = self::generate();
        $html = '<nav class="text-sm">';
        
        foreach ($breadcrumbs as $index => $breadcrumb) {
            if ($index > 0) {
                $html .= '<span class="text-gray-400 mx-2">/</span>';
            }
            
            if ($breadcrumb['active']) {
                $html .= '<span class="text-gray-700 font-medium">' . $breadcrumb['name'] . '</span>';
            } else {
                $html .= '<a href="' . $breadcrumb['url'] . '" class="text-gray-500 hover:text-gray-700">' . $breadcrumb['name'] . '</a>';
            }
        }
        
        $html .= '</nav>';
        
        return $html;
    }
}
