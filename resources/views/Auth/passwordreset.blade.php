{{-- @extends('components.loginLayout')

@section('content')
    <div class="modal">
        <h2>Forgot Password</h2>
        <p>Enter your email to receive a password reset link.</p>
        <form action="{{ route('passwordReset') }}" method="POST">
            @csrf
            <div class="input-group">
                <input type="text" name="userId" placeholder="Enter your StudentID or EmpID" required>
            </div>
            <div class="input-group">
                <input type="email" name="email" placeholder="Enter your email" required>
            </div>
            <div class="btn-group">
                <a href="{{ route('login') }}" class="btn cancel-btn">Cancel</a>
                <button type="submit" class="btn reset-btn">Request Reset</button>
            </div>
        </form>
    </div>
@endsection --}}
