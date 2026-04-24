<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function index(): \Illuminate\View\View
    {
        return view('shop.contact');
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|min:3',
            'email' => 'required|email',
            'subject' => 'required|string',
            'message' => 'required|string|min:10',
            'honeypot' => 'nullable|max:0',
        ]);

        // Honeypot protection: reject if honeypot field is filled
        if (! empty($request->input('honeypot'))) {
            return response()->json(['success' => false, 'message' => 'Invalid submission.'], 422);
        }

        // Store contact message
        ContactMessage::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'subject' => $validated['subject'],
            'message' => $validated['message'],
            'ip_address' => $request->ip(),
        ]);

        return response()->json(['success' => true]);
    }
}
