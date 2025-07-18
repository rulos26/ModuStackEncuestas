<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\SendMailRequest;
use App\Models\SentMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class MailPanelController extends Controller
{
    public function index()
    {
        $mails = SentMail::orderByDesc('created_at')->paginate(10);
        return view('admin.mail_panel', compact('mails'));
    }

    public function send(SendMailRequest $request)
    {
        $data = $request->validated();
        $attachments = [];
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $attachments[] = $file->store('mail_attachments', 'public');
            }
        }
        // Enviar correo
        Mail::send([], [], function ($message) use ($data, $attachments, $request) {
            $message->to($data['to'])
                ->subject($data['subject'])
                ->html($data['body']); // Cambiado de setBody a html
            foreach ($attachments as $path) {
                $message->attach(Storage::disk('public')->path($path));
            }
        });
        // Guardar registro
        $mail = SentMail::create([
            'to' => $data['to'],
            'subject' => $data['subject'],
            'body' => $data['body'],
            'attachments' => json_encode($attachments),
            'sent_by' => $request->user()->id,
        ]);
        return redirect()->route('admin.correos.index')->with('success', 'Correo enviado correctamente.');
    }
}
