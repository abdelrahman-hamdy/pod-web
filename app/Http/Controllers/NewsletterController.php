<?php

namespace App\Http\Controllers;

use App\Models\NewsletterSubscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class NewsletterController extends Controller
{
    public function subscribe(Request $request)
    {
        $request->validate([
            'email' => 'required|email|unique:newsletter_subscriptions,email',
        ]);

        $subscription = NewsletterSubscription::create([
            'email' => $request->email,
            'user_id' => Auth::id(),
            'token' => Str::random(32),
        ]);

        return back()->with('success', 'Thank you for subscribing to our newsletter!');
    }
}
