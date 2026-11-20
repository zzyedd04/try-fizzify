<?php

namespace App\Http\Controllers;

use App\Models\ContactMessage;
use Illuminate\Http\Request;
use Throwable;

class ContactController extends Controller
{
    /**
     * Simpan pesan ke database, lalu buka WhatsApp admin dengan isi pesan yang sudah terisi.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:60'],
            'last_name' => ['nullable', 'string', 'max:60'],
            'phone' => ['required', 'string', 'max:30'],
            'email' => ['required', 'email', 'max:120'],
            'message' => ['required', 'string', 'max:1000'],
        ], [], [
            'first_name' => 'First Name',
            'phone' => 'Phone Number',
            'email' => 'Email',
            'message' => 'Project Description',
        ]);

        // Gagal simpan ke database (mis. tabel belum dibuat) tidak boleh membuat form macet:
        // error dicatat di storage/logs, lalu pesan tetap dikirim lewat WhatsApp.
        try {
            ContactMessage::create($data);
        } catch (Throwable $e) {
            report($e);
        }

        $name = trim($data['first_name'].' '.($data['last_name'] ?? ''));
        $text = "Halo Fizzify! Saya {$name}.\n\n"
            ."{$data['message']}\n\n"
            ."No. HP: {$data['phone']}\n"
            ."Email: {$data['email']}";

        $number = preg_replace('/\D+/', '', config('fizzify.whatsapp'));

        return redirect()->away('https://wa.me/'.$number.'?text='.rawurlencode($text));
    }
}
