<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Upwork AI Tool</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>

<body class="bg-gray-50 text-gray-800 flex items-center justify-center min-h-screen">

<div class="max-w-md w-full px-4 py-8">

    <div class="bg-white rounded-xl shadow border p-8">
        <div class="text-center mb-8">
            <h1 class="text-2xl font-bold mb-2">Login</h1>
            <p class="text-gray-500 text-sm">Sign in with your Auth0 account to access the Upwork AI Proposal Tool.</p>
        </div>

        @if($errors->any())
            <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded text-red-700 text-sm">
                {{ $errors->first() }}
            </div>
        @endif

        <a href="{{ route('auth0.redirect') }}"
           class="flex items-center justify-center gap-3 w-full px-4 py-3 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 transition">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8z"/>
            </svg>
            Sign in with Auth0
        </a>

        <div class="mt-6 text-center text-xs text-gray-400">
            By signing in, you agree to the terms of service.
            Only verified users can access this application.
        </div>
    </div>

</div>

</body>
</html>
