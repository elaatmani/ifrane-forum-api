<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mail Tester</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .container { max-width: 520px; margin: 0 auto; }
        .card { border: 1px solid #ddd; border-radius: 8px; padding: 16px; }
        .field { margin-bottom: 12px; }
        label { display: block; margin-bottom: 4px; font-weight: 600; }
        input, select, button { width: 100%; padding: 8px; }
        button { background: #0d6efd; color: #fff; border: none; border-radius: 4px; cursor: pointer; }
        button:hover { background: #0b5ed7; }
        .status { margin-bottom: 12px; padding: 10px; border-radius: 4px; background: #e7f5ff; color: #0c5460; border: 1px solid #b6effb; }
        .error { background: #fdecea; color: #842029; border: 1px solid #f5c2c7; }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <h2>Mail Tester</h2>
            @if(session('status'))
                <div class="status">{{ session('status') }}</div>
            @endif
            @if($errors->any())
                <div class="status error">{{ $errors->first() }}</div>
            @endif
            <form method="POST" action="{{ route('debug.mail-tester.send') }}">
                @csrf
                <div class="field">
                    <label for="email">Target Email</label>
                    <input id="email" type="email" name="email" required placeholder="user@example.com" value="{{ old('email') }}">
                </div>
                <div class="field">
                    <label for="type">Email Type</label>
                    <select id="type" name="type">
                        <option value="welcome">Welcome</option>
                        <option value="password_reset">Password Reset</option>
                        <option value="meeting_invitation">Meeting Invitation</option>
                        <option value="meeting_reminder">Meeting Reminder</option>
                    </select>
                </div>
                <button type="submit">Send Test Email</button>
            </form>
            <p style="margin-top:12px; font-size: 12px; color:#666;">Only available when app debug is enabled.</p>
        </div>
    </div>
</body>
</html>

