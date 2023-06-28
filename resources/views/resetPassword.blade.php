
@if ($errors->any())
    <ul>
        @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
@endif
<form action="" method="post">
    @csrf
    <input type="hidden" name="id" value="{{ $user[0]['id'] }}">
    <input type="password" name="password" placeholder="New Password">
    <br><br>
    <input type="password" name="password_confirmtion" placeholder="Confirm Password">
    <br><br>
    <input type="submit">
</form>