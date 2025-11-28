<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\Requisition;
use App\Models\Notification;
use App\Models\Recipe;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function home()
    {
        $user = Auth::user();

        $profile = $user->profile;
        $activeRequisitions = $this->getActiveRequisitions();
        $incomingDeliveries = $this->getIncomingDeliveries();
        $notifications = $this->getNotifications();
        $recipeOfTheDay = $this->getRecipeOfTheDay();

        return view('Employee.home', compact(
            'user', 'profile', 'activeRequisitions', 'incomingDeliveries', 'notifications', 'recipeOfTheDay'
        ));
    }

    private function getActiveRequisitions()
    {
        return Requisition::where('requested_by', Auth::id())
            ->whereIn('status', ['pending', 'approved'])
            ->with(['requisitionItems' => function($query) {
                $query->with('item');
            }])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
    }

    private function getIncomingDeliveries()
    {
        return Requisition::where('requested_by', Auth::id())
            ->where('status', 'approved')
            ->with(['requisitionItems' => function($query) {
                $query->with('item');
            }])
            ->orderBy('approved_at', 'desc')
            ->limit(3)
            ->get();
    }

    private function getNotifications($limit = 5)
    {
        return Notification::forCurrentUser()
            ->where('priority', '!=', 'low')
            ->limit($limit)
            ->get();
    }

    private function getRecipeOfTheDay()
    {
        return Recipe::where('is_active', true)
            ->with('finishedItem', 'ingredients.item')
            ->orderBy('created_at', 'desc')
            ->first();
    }
}
