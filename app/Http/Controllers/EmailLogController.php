<?php

namespace App\Http\Controllers;

use App\Models\EmailLog;
use Illuminate\Http\Request;

class EmailLogController extends Controller
{
    public function index(Request $request)
    {
        $query = EmailLog::orderBy('created_at', 'desc');

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('to_email', 'like', "%{$search}%")
                  ->orWhere('subject', 'like', "%{$search}%")
                  ->orWhere('from_email', 'like', "%{$search}%");
            });
        }

        if ($request->has('config_key') && $request->config_key) {
            $query->where('config_key', $request->config_key);
        }

        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        if ($request->has('date') && $request->date) {
            $query->whereDate('created_at', $request->date);
        }

        return response()->json($query->paginate(20));
    }

    public function destroy($id)
    {
        $log = EmailLog::findOrFail($id);
        $log->delete();

        return response()->json(['message' => 'Log deleted successfully']);
    }

    public function bulkDestroy(Request $request)
    {
        if ($request->has('all') && $request->all) {
            EmailLog::truncate();
            return response()->json(['message' => 'All logs cleared successfully']);
        }

        if ($request->has('config_key') && $request->config_key) {
            EmailLog::where('config_key', $request->config_key)->delete();
            return response()->json(['message' => 'Logs for configuration cleared successfully']);
        }

        if ($request->has('ids') && is_array($request->ids)) {
            EmailLog::whereIn('id', $request->ids)->delete();
            return response()->json(['message' => 'Selected logs deleted successfully']);
        }

        return response()->json(['message' => 'No logs selected for deletion'], 400);
    }
}
