<form action="{{ route('password.send.code') }}" method="POST">
    @csrf
    <label>Ingresa tu correo electrónico:</label>
    <input type="email" name="email" required>
    <button type="submit">Enviar código de recuperación</button>
</form>