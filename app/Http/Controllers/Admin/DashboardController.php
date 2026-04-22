<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\DeviceSession;
use App\Models\Channel;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_users' => User::count(),
            'active_users' => User::where('status', 'active')->count(),
            'suspended_users' => User::where('status', 'suspended')->count(),
            'active_sessions' => DeviceSession::count(),
            'total_channels' => Channel::count()
        ];

        return view('admin.dashboard', compact('stats'));
    }

    public function stats()
    {
        return response()->json([
            'total_users' => User::count(),
            'active_users' => User::where('status', 'active')->count(),
            'suspended_users' => User::where('status', 'suspended')->count(),
            'active_sessions' => DeviceSession::count(),
            'total_channels' => Channel::count()
        ]);
    }
}
