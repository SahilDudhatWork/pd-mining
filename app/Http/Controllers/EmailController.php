<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Mail\ContactMail;

class EmailController extends Controller
{
    public function sahilEnail(Request $request)
    {
        try {
            $data = [
            'name' => $request->input('name'),
            'subject' => $request->input('subject'),
            'email' => $request->input('email'),
            'message' => $request->input('message')
        ];

            Mail::to('sahildudhat03@gmail.com')->send(new ContactMail($data));
            return response()->json(['message' => 'Email sent successfully'], 200);
        } catch (\Exception $e) {
            return $this->responseError($e->getMessage(), 500);
        } 
    }
    
    public function rutvikEnail(Request $request)
    {
        try {
            $data = [
            'name' => $request->input('name'),
            'subject' => $request->input('subject'),
            'email' => $request->input('email'),
            'message' => $request->input('message')
        ];

            Mail::to('rutvikkarad123@gmail.com')->send(new ContactMail($data));
            return response()->json(['message' => 'Email sent successfully'], 200);
        } catch (\Exception $e) {
            return $this->responseError($e->getMessage(), 500);
        } 
    }
    
}
