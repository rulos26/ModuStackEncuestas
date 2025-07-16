<div class="form-group">
    <label for="name">Nombre</label>
    <input type="text" name="name" id="name" class="form-control" value="{{ old('name', $user->name ?? '') }}" required maxlength="255">
</div>
<div class="form-group">
    <label for="email">Correo electrónico</label>
    <input type="email" name="email" id="email" class="form-control" value="{{ old('email', $user->email ?? '') }}" required maxlength="255">
</div>
<div class="form-group">
    <label for="password">Contraseña {{ $edit ? '(dejar en blanco para no cambiar)' : '' }}</label>
    <input type="password" name="password" id="password" class="form-control" {{ $edit ? '' : 'required' }} minlength="6">
</div>
<div class="form-group">
    <label for="password_confirmation">Confirmar contraseña</label>
    <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" {{ $edit ? '' : 'required' }} minlength="6">
</div>
<div class="form-group">
    <label for="role">Rol</label>
    <select name="role" id="role" class="form-control" required>
        <option value="usuario" {{ old('role', $user->role ?? '') == 'usuario' ? 'selected' : '' }}>Usuario</option>
        <option value="admin" {{ old('role', $user->role ?? '') == 'admin' ? 'selected' : '' }}>Administrador</option>
    </select>
</div>
