@extends('adminlte::page')

@section('content')
<div class="container">
    <h1>Panel de Envío de Correos</h1>
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    <form action="{{ route('admin.correos.send') }}" method="POST" enctype="multipart/form-data" class="mb-4">
        @csrf
        <div class="form-group mb-2">
            <label for="to">Destinatario</label>
            <input type="email" name="to" id="to" class="form-control" required value="{{ old('to') }}">
            @error('to')<span class="text-danger">{{ $message }}</span>@enderror
        </div>
        <div class="form-group mb-2">
            <label for="subject">Asunto</label>
            <input type="text" name="subject" id="subject" class="form-control" required value="{{ old('subject') }}">
            @error('subject')<span class="text-danger">{{ $message }}</span>@enderror
        </div>
        <div class="form-group mb-2">
            <label for="body">Mensaje</label>
            <textarea name="body" id="body" class="form-control" rows="5" required>{{ old('body') }}</textarea>
            @error('body')<span class="text-danger">{{ $message }}</span>@enderror
        </div>
        <div class="form-group mb-2">
            <label for="attachments">Adjuntos (opcional, puedes seleccionar varios)</label>
            <input type="file" name="attachments[]" id="attachments" class="form-control" multiple>
            @error('attachments.*')<span class="text-danger">{{ $message }}</span>@enderror
        </div>
        <button type="submit" class="btn btn-primary">Enviar correo</button>
    </form>
    <h2>Historial de Correos Enviados</h2>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Destinatario</th>
                <th>Asunto</th>
                <th>Enviado por</th>
                <th>Fecha</th>
                <th>Adjuntos</th>
            </tr>
        </thead>
        <tbody>
            @forelse($mails as $mail)
                <tr>
                    <td>{{ $mail->id }}</td>
                    <td>{{ $mail->to }}</td>
                    <td>{{ $mail->subject }}</td>
                    <td>{{ $mail->sender->name ?? 'N/A' }}</td>
                    <td>{{ $mail->created_at->format('d/m/Y H:i') }}</td>
                    <td>
                        @if(is_array($mail->attachments) && count($mail->attachments))
                            @foreach($mail->attachments as $file)
                                <a href="{{ asset('storage/'.$file) }}" target="_blank">Ver adjunto</a><br>
                            @endforeach
                        @else
                            -
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="6">No hay correos enviados.</td></tr>
            @endforelse
        </tbody>
    </table>
    {{ $mails->links() }}
</div>
@endsection
