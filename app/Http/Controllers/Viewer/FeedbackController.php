<?php

namespace App\Http\Controllers\Viewer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Feedback;
use Illuminate\Support\Facades\Auth;

class FeedbackController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'event_id' => 'required|exists:events,id',
            'rating' => 'required|integer|min:1|max:5',
            'q_satisfaction' => 'required|string',
            'q_organization' => 'required|string',
            'q_relevance' => 'required|string',
            'comment' => 'required|string|max:1000',
        ]);

        $feedback = Feedback::create([
            'event_id' => $request->event_id,
            'user_id' => Auth::id(),
            'rating' => $request->rating,
            'q_satisfaction' => $request->q_satisfaction,
            'q_organization' => $request->q_organization,
            'q_relevance' => $request->q_relevance,
            'comment' => $request->comment,
        ]);

        return response()->json(['success' => true, 'message' => 'Feedback submitted successfully!']);
    }
}
