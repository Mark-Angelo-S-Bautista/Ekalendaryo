@extends('components.loginLayout')

@section('content')
    <form action="{{ route('password.reset.request') }}" method="POST">
        @csrf
        <div class="modal-input">
            <input type="text" name="userId" placeholder="Enter your ID" required>
        </div>
        <div class="modal-input">
            <input type="email" name="email" placeholder="Enter your email" required>
        </div>
        <div class="modal-buttons">
            <a href="{{ route('Auth.login') }}" class="modal-btn cancel-btn">Cancel</a>
            <button type="submit" class="modal-btn reset-btn">Send Link</button>
        </div>
    </form>
@endsection
