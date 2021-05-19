@extends('layout.template')

@section('content')

<div id="login">
    <h1 class="title">Login</h1>
    <p class="{{session('message_type')}}">{{session('message')}}</p>
    <form action="{{route('login')}}" class="table_form" method="POST">
        @csrf

        <div>
            <label for="email">Email: </label>
            <input type="email" name="email" value="{{old('email') ?? ''}}" required>
        </div>
        @error('email')
        <div class="form_error_message">
            <label></label>
            <p><strong>{{$message}}</strong></p>
        </div>
        @enderror

        <div>
            <label for="password">Password: </label>
            <input type="password" name="password" required>
        </div>
        @error('password')
        <div class="form_error_message">
            <label></label>
            <p><strong>{{$message}}</strong></p>
        </div>
        @enderror

        @if (Route::has('password.request'))
        <div>
            <label></label>
            <p class="form_password_reset"><a href="{{ route('password.request') }}"> Forgot Your Password </a></p>
        </div>
        @endif

        <div>
            <label></label>
            <button type="submit">Sign In</button>
        </div>
    </form>
</div>

@endsection
